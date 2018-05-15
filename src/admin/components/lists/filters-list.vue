<template>
    <div>
        <b-loading :active.sync="isLoadingFieldTypes"/>
        <tainacan-title v-if="!isRepositoryLevel"/>
        <div class="columns">
            <div class="column">         
                <draggable 
                        class="active-filters-area"
                        @change="handleChange"
                        :class="{'filters-area-receive': isDraggingFromAvailable}" 
                        v-model="activeFilterList" 
                        :options="{
                            group: { name:'filters', pull: false, put: true }, 
                            sort: openedFilterId == '' || openedFilterId == undefined, 
                            disabled: openedFilterId != '' && openedFilterId != undefined,
                            handle: '.handle', 
                            ghostClass: 'sortable-ghost',
                            filter: 'not-sortable-item', 
                            animation: '250'}">
                    <div  
                            class="active-filter-item" 
                            :class="{
                                'not-sortable-item': filter.id == undefined || openedFilterId != '' || choosenField.name == filter.name, 
                                'not-focusable-item': openedFilterId == filter.id, 
                                'disabled-filter': filter.enabled == false
                            }" 
                            v-for="(filter, index) in activeFilterList" 
                            :key="index">
                        <div class="handle">
                            <grip-icon/>
                            <span 
                                    class="filter-name"
                                    :class="{'is-danger': formWithErrors == filter.id }">
                                    {{ filter.name }}
                            </span>
                            <span   
                                v-if="filter.filter_type_object != undefined"
                                class="label-details">  
                                ({{ $i18n.get(filter.filter_type_object.component) }})  
                                    <span 
                                            class="not-saved" 
                                            v-if="(editForms[filter.id] != undefined && editForms[filter.id].saved != true) ||filter.status == 'auto-draft'"> 
                                        {{ $i18n.get('info_not_saved') }}
                                    </span>
                            </span> 
                            <span 
                                    class="loading-spinner" 
                                    v-if="filter.id == undefined"/>
                            <span 
                                    class="controls" 
                                    v-if="filter.filter_type != undefined">
                                <b-switch 
                                        size="is-small" 
                                        v-model="filter.enabled" 
                                        @input="onChangeEnable($event, index)"/>
                                <a 
                                        :style="{ visibility: filter.collection_id != collectionId ? 'hidden' : 'visible' }" 
                                        @click.prevent="editFilter(filter)">
                                    <b-icon 
                                            type="is-gray" 
                                            icon="pencil"/>
                                </a>
                                <a 
                                        :style="{ visibility: filter.collection_id != collectionId ? 'hidden' : 'visible' }" 
                                        @click.prevent="removeFilter(filter)">
                                    <b-icon 
                                            type="is-gray" 
                                            icon="delete"/>
                                </a>
                            </span>
                        </div>
                        <div v-if="choosenField.id == filter.id && openedFilterId == ''">
                            <form class="tainacan-form">
                                <b-field :label="$i18n.get('label_filter_type')">
                                    <b-select
                                            v-model="selectedFilterType"
                                            :placeholder="$i18n.get('instruction_select_a_filter_type')">
                                        <option 
                                                v-for="(filterType, index) in allowedFilterTypes" 
                                                :key="index"
                                                :selected="index == 0"
                                                :value="filterType">
                                                {{ filterType.name }}</option>  
                                    </b-select>
                                </b-field>
                                <div class="field is-grouped form-submit">
                                    <div class="control"> 
                                        <button 
                                                class="button is-outlined" 
                                                @click.prevent="cancelFilterTypeSelection()" 
                                                slot="trigger">{{ $i18n.get('cancel') }}</button>
                                    </div>
                                    <div class="control">
                                        <button 
                                                class="button is-success" 
                                                type="submit" 
                                                :disabled="Object.keys(selectedFilterType).length == 0"
                                                @click.prevent="confirmSelectedFilterType()">{{ $i18n.get('next') }}</button>
                                    </div>
                                    
                                </div>
                            </form>
                        </div>
                        <b-field v-if="openedFilterId == filter.id">
                            <filter-edition-form
                                    @onEditionFinished="onEditionFinished()"
                                    @onEditionCanceled="onEditionCanceled()"
                                    @onErrorFound="formWithErrors = filter.id"
                                    :index="index"
                                    :original-filter="filter"
                                    :edited-filter="editForms[openedFilterId]"/>
                        </b-field>
                    </div>
                </draggable>
            </div>
            <div class="column available-fields-area">
                <div class="field" >
                    <h3 class="label"> {{ $i18n.get('label_available_field_types') }}</h3>
                    <draggable
                            v-if="availableFieldList.length > 0" 
                            v-model="availableFieldList" 
                            :options="{ 
                                sort: false, 
                                group: { name:'filters', pull: true, put: false, revertClone: true },
                                dragClass: 'sortable-drag'
                            }">
                        <div 
                                class="available-field-item"
                                v-for="(field, index) in availableFieldList" 
                                :key="index"
                                @click.prevent="addFieldViaButton(field, index)">  
                            <grip-icon/> 
                              <span class="field-name">{{ field.name }}</span>
                        </div>
                    </draggable>   
                
                    <section 
                            v-else
                            class="field is-grouped-centered section">
                        <div class="content has-text-gray has-text-centered">
                            <p>
                                <b-icon
                                        icon="format-list-checks"
                                        size="is-large"/>
                            </p>
                            <p>{{ $i18n.get('info_there_is_no_field' ) }}</p>  
                            <router-link
                                    id="button-create-field"
                                    :to="isRepositoryLevel ? $routerHelper.getNewFieldPath() : $routerHelper.getNewCollectionFieldPath(collectionId)"
                                    tag="button" 
                                    class="button is-secondary is-centered">
                                {{ $i18n.getFrom('fields', 'new_item') }}</router-link>
                        </div>
                    </section>
                </div>
            </div>
        </div>
    </div> 
</template>

<script>
import { mapActions, mapGetters } from 'vuex';
import GripIcon from '../other/grip-icon.vue';
import FilterEditionForm from './../edition/filter-edition-form.vue';

export default {
    name: 'FiltersList',
    data(){           
        return {
            collectionId: '',
            isRepositoryLevel: false,
            isDraggingFromAvailable: false,
            isLoadingFieldTypes: true,
            isLoadingFilters: false,
            isLoadingFilterTypes: false,
            isLoadingFilter: false,
            openedFilterId: '',
            formWithErrors: '',
            editForms: {},
            allowedFilterTypes: [],
            selectedFilterType: {},
            choosenField: {},
            newIndex: 0,
            availableFieldList: [],
            filterTypes: []        
        }
    },
    computed: {
        activeFilterList: {
            get() {
                return this.getFilters();
            },
            set(value) {
                this.updateFilters(value);
            }
        }
    },
    components: {
        FilterEditionForm,
        GripIcon
    },
    beforeRouteLeave ( to, from, next ) {
        let hasUnsavedForms = false;
        for (let editForm in this.editForms) {
            if (!this.editForms[editForm].saved) 
                hasUnsavedForms = true;
        }
        if ((this.openedFilterId != '' && this.openedFilterId != undefined) || hasUnsavedForms ) {
            this.$dialog.confirm({
                message: this.$i18n.get('info_warning_filters_not_saved'),
                    onConfirm: () => {
                        this.onEditionCanceled();
                        next();
                    },
                    cancelText: this.$i18n.get('cancel'),
                    confirmText: this.$i18n.get('continue'),
                    type: 'is-secondary'
                });  
        } else {
            next()
        }  
    },
    methods: {
        ...mapActions('filter', [
            'fetchFilterTypes',
            'fetchFilters',
            'sendFilter',
            'deleteFilter',
            'addTemporaryFilter',
            'deleteTemporaryFilter',
            'updateFilters',
            'updateCollectionFiltersOrder'
        ]),
        ...mapGetters('filter',[
            'getFilters',
            'getFilterTypes'
        ]),
        ...mapActions('fields', [
            'fetchFields',
        ]),
        ...mapGetters('fields', [
            'getFields',
        ]),
        handleChange($event) {     
            if ($event.added) {
                this.addNewFilter($event.added.element, $event.added.newIndex);
            } else if ($event.removed) {
                this.removeFilter($event.removed.element);
            } else if ($event.moved) {
                if (!this.isRepositoryLevel)
                    this.updateFiltersOrder(); 
            }
        },
        updateFiltersOrder() {
            let filtersOrder = [];
            for (let filter of this.activeFilterList) {
                filtersOrder.push({'id': filter.id, 'enabled': filter.enabled});
            }
            this.updateCollectionFiltersOrder({ collectionId: this.collectionId, filtersOrder: filtersOrder });
        },
        updateListOfFields() {

            let availableFields = JSON.parse(JSON.stringify(this.getFields())) ;

            for (let activeFilter of this.activeFilterList) {
                for (let i = availableFields.length - 1; i >= 0 ; i--) {
                    if (activeFilter.field != undefined) {
                        if (activeFilter.field.field_id == availableFields[i].id) 
                            availableFields.splice(i, 1);
                    }
                }
            }

            this.availableFieldList = availableFields;
        },
        onChangeEnable($event, index) {
            this.activeFilterList[index].enabled = $event;
            let filtersOrder = [];
            for (let filter of this.activeFilterList) {
                filtersOrder.push({'id': filter.id, 'enabled': filter.enabled});
            }
            this.updateCollectionFiltersOrder({ collectionId: this.collectionId, filtersOrder: filtersOrder });
        },
        addFieldViaButton(fieldType, fieldIndex) {
            this.availableFieldList.splice(fieldIndex, 1);
            let lastIndex = this.activeFilterList.length;

            // Updates store with temporary Filter
            this.addTemporaryFilter(fieldType);

            this.addNewFilter(fieldType, lastIndex);
        },
        addNewFilter(choosenField, newIndex) {
            this.choosenField = choosenField;
            this.newIndex = newIndex;
            this.openedFilterId = '';
            this.allowedFilterTypes = [];
            this.selectedFilterType = {};

            for (let filter of this.filterTypes) {
                for (let supportedType of filter['supported_types']) {
                    if (choosenField.field_type_object.primitive_type == supportedType)
                        this.allowedFilterTypes.push(filter);
                }
            }
        },
        createChoosenFilter() {
            
            this.sendFilter({
                collectionId: this.collectionId, 
                fieldId: this.choosenField.id, 
                name: this.choosenField.name, 
                filterType: this.selectedFilterType.name, 
                status: 'auto-draft', 
                isRepositoryLevel: this.isRepositoryLevel,
                newIndex: this.newIndex
            })
            .then((filter) => {

                if (!this.isRepositoryLevel)
                    this.updateFiltersOrder();

                this.newIndex = 0;
                this.choosenField = {};
                this.selectedFilterType = {}
                this.allowedFilterTypes = [];

                this.editFilter(filter);
            })
            .catch((error) => {
                this.$console.error(error);
                this.newIndex = 0;
                this.choosenField = {};
                this.selectedFilterType = {}
                this.allowedFilterTypes = [];
            });
        },
        removeFilter(removedFilter) {

            this.deleteFilter(removedFilter.id)
            .then(() => {
                // Reload Available Field Types List
                this.updateListOfFields();
   
            })
            .catch((error) => { this.$console.log(error)});
        
            if (!this.isRepositoryLevel)
                this.updateFiltersOrder(); 
        },
        confirmSelectedFilterType() {
            this.createChoosenFilter();
        },
        cancelFilterTypeSelection() {
           this.availableFieldList.push(this.choosenField);
           this.choosenField = {};
           this.allowedFilterTypes = [];
           this.selectedFilterType = {};
           this.deleteTemporaryFilter(this.newIndex);
           this.newIndex = 0;
        },
        editFilter(filter) {
            // Closing collapse
            if (this.openedFilterId == filter.id) {                
                this.openedFilterId = '';

            // Opening collapse
            } else {
                
                if (this.openedFilterId == '' && this.choosenField.id != undefined) {
                    this.availableFieldList.push(this.choosenField);
                    this.choosenField = {};
                    this.allowedFilterTypes = [];
                    this.selectedFilterType = {};
                    this.deleteTemporaryFilter(this.newIndex);
                    this.newIndex = 0;
                }
                this.openedFilterId = filter.id;
                // First time opening
                if (this.editForms[this.openedFilterId] == undefined) {
                    this.editForms[this.openedFilterId] = JSON.parse(JSON.stringify(filter));
                    this.editForms[this.openedFilterId].saved = true;
                    
                    // Filter inserted now
                    if (this.editForms[this.openedFilterId].status == 'auto-draft') {
                        this.editForms[this.openedFilterId].status = 'publish'; 
                        this.editForms[this.openedFilterId].saved = false;
                    }
                }      
            }   
        },
        onEditionFinished() {
            this.formWithErrors = '';
            delete this.editForms[this.openedFilterId];
            this.openedFilterId = '';
        },
        onEditionCanceled() {
            this.formWithErrors = '';
            delete this.editForms[this.openedFilterId];
            this.openedFilterId = '';
        }

    },
    created() {

        this.isRepositoryLevel = this.$route.name == 'FiltersPage' ? true : false;
        if (this.isRepositoryLevel)
            this.collectionId = 'default';
        else
            this.collectionId = this.$route.params.collectionId;

        this.isLoadingFieldTypes = true;
        this.isLoadingFilters = true;
        this.isLoadingFilterTypes = true;

        this.fetchFilterTypes()
            .then((filterTypes) => {
                this.isLoadingFilterTypes = false;
                this.filterTypes = filterTypes;
            })
            .catch(() => {
                this.isLoadingFilterTypes = false;
            });        

        this.fetchFilters({collectionId: this.collectionId, isRepositoryLevel: this.isRepositoryLevel, isContextEdit: true, includeDisabled: 'yes' })
            .then(() => {
                this.isLoadingFilters = false;
                // Needs to be done after activeFilterList exists to compare and remove chosen fields.
                this.fetchFields({collectionId: this.collectionId, isRepositoryLevel: this.isRepositoryLevel, isContextEdit: true })
                    .then(() => {
                        this.isLoadingFieldTypes = false;
                        this.updateListOfFields();
                    })
                    .catch(() => {
                        this.isLoadingFieldTypes = false;
                    });
            })
            .catch(() => {
                this.isLoadingFilters = false;
            });
    }
}
</script>

<style lang="scss">

    @import "../../scss/_variables.scss";

    .page-title {
        border-bottom: 1px solid $secondary;
        h2 {
            color: $tertiary;
            font-weight: 500;
        }
        margin: 1em 0em 2.0em 0em;
    }

    .loading-spinner {
        animation: spinAround 500ms infinite linear;
        border: 2px solid #dbdbdb;
        border-radius: 290486px;
        border-right-color: transparent;
        border-top-color: transparent;
        content: "";
        display: inline-block;
        height: 1em; 
        width: 1em;
    }

    .active-filters-area {
        font-size: 14px;
        margin-right: 0.8em;
        margin-left: -0.8em;
        padding-right: 6em;
        min-height: 330px;

        @media screen and (max-width: 769px) {
            min-height: 45px;
            margin: 0; 
            padding-right: 0em;
        }
        @media screen and (max-width: 1216px) {
            padding-right: 1em;
        }

        &.filters-area-receive {
            border: 1px dashed gray;
        }

        .collapse {
            display: initial;
        }

        .active-filter-item {
            background-color: white;
            padding: 0.7em 0.9em;
            margin: 4px;
            min-height: 40px;
            position: relative;
            display: block; 
            transition: top 0.1s ease;
            cursor: grab;
        
            .handle {
                padding-right: 6em;
            }
            .grip-icon { 
                fill: $gray;
                top: 2px;
                position: relative;
            }
            .filter-name {
                text-overflow: ellipsis;
                overflow-x: hidden;
                white-space: nowrap;
                font-weight: bold;
                margin-left: 0.4em;
                margin-right: 0.4em;

                &.is-danger {
                    color: $danger !important;
                }
            }
            .label-details {
                font-weight: normal;
                color: $gray;
            }
            .not-saved {
                font-style: italic;
                font-weight: bold;
                color: $danger;
            }
            .controls { 
                position: absolute;
                right: 5px; 
                top: 10px;
                .switch {
                    position: relative;
                    bottom: 3px;
                }
                .icon {
                    bottom: 1px;   
                    position: relative;
                    i, i:before { font-size: 20px; }
                }
            }

            form {
                padding: 1.0em 2.0em;
                border-top: 1px solid $draggable-border-color;
                border-bottom: 1px solid $draggable-border-color;
                margin-top: 1.0em;
            }
            &.not-sortable-item, &.not-sortable-item:hover {
                cursor: default;
                background-color: white !important;

                .handle .label-details, .handle .icon {
                    color: $gray !important;
                }
            } 
            &.not-focusable-item, &.not-focusable-item:hover {
                cursor: default;
               
                .field-name {
                    color: $secondary;
                }
                .handle .label-details, .handle .icon {
                    color: $gray !important;
                }
            }
            &.disabled-field {
                color: $gray;
            }    
        }
        .active-filter-item:hover:not(.not-sortable-item) {
            background-color: $secondary;
            border-color: $secondary;
            color: white !important;

            .grip-icon { 
                fill: $white;
            }

            .label-details, .icon {
                color: white !important;
            }

            .switch.is-small {
                input[type="checkbox"] + .check {
                    background-color: $secondary !important;
                    border: 1.5px solid white !important;
                    &::before { background-color: white !important; }
                } 
                input[type="checkbox"]:checked + .check {
                    border: 1.5px solid white !important;
                    &::before { background-color: white !important; }
                }
                &:hover input[type="checkbox"] + .check {
                    border: 1.5px solid white !important;
                    background-color: $secondary !important;
                }
            }
        }
        .sortable-ghost {
            border: 1px dashed $draggable-border-color;
            display: block;
            padding: 0.7em 0.9em;
            margin: 4px;
            height: 40px;
            position: relative;

            .grip-icon { 
                fill: $gray;
                top: 2px;
                position: relative;
            }
        }
    }

    .available-fields-area {
        padding: 10px 0px 10px 10px;
        margin: 0;
        max-width: 280px;
        font-size: 14px;

        @media screen and (max-width: 769px) {
            max-width: 100%;
            padding: 10px;
            h3 {
                margin: 1em 0em 1em 0em !important;
            }
            .available-field-item::before, 
            .available-field-item::after {
                display: none !important;
            }
        }

        h3 {
            color: $secondary;
            margin: 0.2em 0em 1em -1.2em;
            font-weight: 500;
        }

        .available-field-item {
            padding: 0.7em;
            margin: 4px;
            background-color: white;
            cursor: pointer;
            left: 0;
            line-height: 1.3em;
            height: 40px;
            position: relative;
            border: 1px solid $draggable-border-color;
            border-radius: 1px;
            transition: left 0.2s ease;
            
            .grip-icon { 
                fill: $gray;
                top: -3px;
                position: relative;
                display: inline-block;
            }
            .icon {
                position: relative;
                bottom: 3px;
            }
            .field-name {
                text-overflow: ellipsis;
                overflow-x: hidden;
                white-space: nowrap;
                font-weight: bold;
                margin-left: 0.4em;
                display: inline-block;
                max-width: 200px;
            }
            &:after,
            &:before {
                content: '';
                display: block;
                position: absolute;
                right: 100%;
                width: 0;
                height: 0;
                border-style: solid;
            }
            &:after {
                top: -1px;
                border-color: transparent white transparent transparent;
                border-right-width: 16px;
                border-top-width: 20px;
                border-bottom-width: 20px;
                left: -19px;
            }
            &:before {
                top: -1px;
                border-color: transparent $draggable-border-color transparent transparent;
                border-right-width: 16px;
                border-top-width: 20px;
                border-bottom-width: 20px;
                left: -20px;
            }
        }
        .sortable-drag {
            opacity: 1 !important;
        }
        .available-field-item:hover {
            background-color: $secondary;
            border-color: $secondary;
            color: white;
            position: relative;
            left: -4px;

            &:after {
                border-color: transparent $secondary transparent transparent;
            }
            &:before {
                border-color: transparent $secondary transparent transparent;
            }
            .grip-icon {
                fill: white !important;
            }
        }
    }

</style>

