<template>
    <div>
        <b-loading :active.sync="isLoadingFieldTypes"></b-loading>
        <div class="page-title">
            <h2>{{ isRepositoryLevel ? $i18n.get('instruction_dragndrop_filters_collection') : $i18n.get('instruction_dragndrop_filters_collection') }}</h2>
        </div>
        <div class="columns">
            <div class="column">         
                <draggable 
                    class="active-filters-area"
                    @change="handleChange"
                    :class="{'filters-area-receive': isDraggingFromAvailable}" 
                    :list="activeFilterList" 
                    :options="{
                        group: { name:'filters', pull: false, put: true }, 
                        handle: '.handle', 
                        ghostClass: 'sortable-ghost',
                        filter: 'not-sortable-item', 
                        animation: '250'}">
                    <div  
                        class="active-filter-item" 
                        :class="{'not-sortable-item': filter.id == undefined || openedFilterId == filter.id, 'not-focusable-item': openedFilterId == filter.id, 'disabled-filter': filter.enabled == false}" 
                        v-for="(filter, index) in activeFilterList" :key="index">
                            <div class="handle">
                                <b-icon type="is-gray" class="is-pulled-left" icon="drag"></b-icon>
                                <span v-if="filter.id !== undefined" class="filter-name">{{ filter.name }}</span>
                                <span v-if="filter.id !== undefined" class="label-details"><span class="loading-spinner" v-if="filter.id == undefined"></span>
                                <span class="controls" v-if="filter.id != undefined">
                                    <b-switch size="is-small" v-model="filter.enabled" @input="onChangeEnable($event, index)"></b-switch>
                                    <a :style="{ visibility: filter.collection_id != collectionId ? 'hidden' : 'visible' }" 
                                        @click.prevent="removeFilter(filter)">
                                        <b-icon icon="delete"></b-icon>
                                    </a>
                                    <a  :style="{ visibility: filter.collection_id != collectionId ? 'hidden' : 'visible' }" 
                                        @click.prevent="editFilter(filter)">
                                        <b-icon icon="pencil"></b-icon>
                                    </a>
                                </span>
                                    <b-modal :active.sync="isModalOpened" :width="320" scroll="keep">
                                    <div class="filter-selection-modal">
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
                                        <div class="field is-grouped is-grouped-centered">
                                            <div class="control">
                                                <button 
                                                    class="button is-secondary" 
                                                    type="submit" 
                                                    :disabled="Object.keys(selectedFilterType).length == 0"
                                                    @click.prevent="confirmSelectedFilterType()">Submit</button>
                                            </div>
                                            <div class="control">
                                                <button 
                                                    class="button is-text" 
                                                    @click.prevent="cancelFilterTypeSelection()" 
                                                    slot="trigger">Cancel</button>
                                            </div>
                                        </div>
                                    </div>
                                    </b-modal>
                            </div>
                            <b-field v-if="openedFilterId == filter.id">
                                <filter-edition-form
                                    @onEditionFinished="onEditionFinished()"
                                    @onEditionCanceled="onEditionCanceled()"
                                    :filter="editForm"></filter-edition-form>
                            </b-field>
                        </div>
                </draggable> 
            </div>
            <div class="column available-fields-area">
                <div class="field">
                    <h3 class="label"> {{ $i18n.get('label_available_field_types') }}</h3>
                    <draggable 
                        :list="availableFieldList" 
                        :options="{ 
                            sort: false, 
                            group: { name:'filters', pull: true, put: false, revertClone: true },
                            dragClass: 'sortable-drag'
                        }">
                        <div 
                            class="available-field-item" 
                            :class="{ 'hightlighted-field' : hightlightedField == field.name }" 
                            v-for="(field, index) in availableFieldList" 
                            :key="index"
                            @click.prevent="addFieldViaButton(field)">  
                            <b-icon type="is-gray" class="is-pulled-left" icon="drag"></b-icon> <span class="field-name">{{ field.name }}</span>
                        </div>
                    </draggable>   
                </div>
            </div>
        </div>
    </div> 
</template>

<script>
import { mapActions, mapGetters } from 'vuex';
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
            isModalOpened: false,
            hightlightedField: '',
            editForm: {},
            allowedFilterTypes: [],
            selectedFilterType: {},
            choosenField: {},
            newIndex: 0
        }
    },
    components: {
        FilterEditionForm
    },
    methods: {
        ...mapActions('filter', [
            'fetchFilterTypes',
            'fetchFilters',
            'sendFilter',
            'deleteFilter',
            'updateCollectionFiltersOrder'
        ]),
        ...mapGetters('filter',[
            'getFilters',
            'getFilterTypes'
        ]),
        ...mapActions('fields', [
            'fetchFields'
        ]),
        ...mapGetters('fields',[
            'getFields'
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
        onChangeEnable($event, index) {
            this.activeFilterList[index].enabled = $event;
            let filtersOrder = [];
            for (let filter of this.activeFilterList) {
                filtersOrder.push({'id': filter.id, 'enabled': filter.enabled});
            }
            this.updateCollectionFiltersOrder({ collectionId: this.collectionId, filtersOrder: filtersOrder });

        },
        addFieldViaButton(fieldType) {
            let lastIndex = this.activeFilterList.length;
            this.activeFilterList.push(fieldType);
            this.addNewFilter(fieldType, lastIndex);

            // Higlights the clicker field
            this.hightlightedField = fieldType.name;
        },
        addNewFilter(choosenField, newIndex) {
            this.choosenField = choosenField;
            this.newIndex = newIndex;
            this.allowedFilterTypes = [];
            this.selectedFilterType = {};

            for (let filter of this.filterTypes) {
                for (let supportedType of filter['supported_types']) {
                    if (choosenField.field_type_object.primitive_type == supportedType)
                        this.allowedFilterTypes.push(filter);
                }
            }
            this.isModalOpened = true;

            this.hightlightedField = '';
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

                this.editFilter(filter);

                this.newIndex = 0;
                this.selectedFilterType = {}
                this.allowedFilterTypes = [];
            })
            .catch((error) => {
                console.log(error);
                this.newIndex = 0;
                this.selectedFilterType = {}
                this.allowedFilterTypes = [];
            });
        },
        removeFilter(removedFilter) {
            this.deleteFilter(removedFilter.id)
            .then((filter) => {
                let index = this.activeFilterList.findIndex(deletedFilter => deletedFilter.id === filter.id);
                if (index >= 0) 
                    
                    this.activeFilterList.splice(index, 1);
                    this.isLoadingFieldTypes = true;
                    this.fetchFields({collectionId: this.collectionId, isRepositoryLevel: this.isRepositoryLevel})
                    .then((res) => {
                        this.isLoadingFieldTypes = false;
                    })
                    .catch((error) => {
                        this.isLoadingFieldTypes = false;
                    });
                
                if (!this.isRepositoryLevel)
                    this.updateFiltersOrder(); 
            })
            .catch((error) => {
            });
        },
        confirmSelectedFilterType() {
            this.isModalOpened = false;
            this.createChoosenFilter();
        },
        cancelFilterTypeSelection() {
           this.isModalOpened = false;
           this.choosenField = '';
           this.allowedFilterTypes = [];
           this.selectedFilterType = {};
           this.activeFilterList.splice(this.newIndex, 1);
           this.newIndex = 0;
        },
        editFilter(filter) {
            if (this.openedFilterId == filter.id) {
                this.editForm = {};
                this.openedFilterId = '';
            } else {
                this.editForm = JSON.parse(JSON.stringify(filter));
                this.editForm.status = 'publish';
                this.openedFilterId = filter.id;
            }     
        },
         onEditionFinished() {
            this.openedFilterId = '';
            this.fetchFilters({collectionId: this.collectionId, isRepositoryLevel: this.isRepositoryLevel});
        },
        onEditionCanceled() {
            this.openedFilterId = '';
        }

    },
    computed: {
        availableFieldList() {
            let availableFields = this.getFields();  
            for (let activeFilter of this.activeFilterList) {
                for (let i = availableFields.length - 1; i >= 0 ; i--) {
                    if (activeFilter.field != undefined) {
                        if (activeFilter.field.field_id == availableFields[i].id) 
                            availableFields.splice(i, 1);
                    }
                }
            }
            return availableFields;
        },
        activeFilterList() {
            return this.getFilters();
        },
        filterTypes() { 
            return this.getFilterTypes();
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
            .then((res) => {
                this.isLoadingFilterTypes = false;
            })
            .catch((error) => {
                this.isLoadingFilterTypes = false;
            });        

        this.fetchFilters({collectionId: this.collectionId, isRepositoryLevel: this.isRepositoryLevel})
            .then((res) => {
                this.isLoadingFilters = false;
                // Needs to be done after activeFilterList exists to compare and remove chosen fields.
                this.fetchFields({collectionId: this.collectionId, isRepositoryLevel: this.isRepositoryLevel})
                    .then((res) => {
                        this.isLoadingFieldTypes = false;
                    })
                    .catch((error) => {
                        this.isLoadingFieldTypes = false;
                    });
            })
            .catch((error) => {
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
        margin: 1em 0em 2.0em -1em;

        @media screen and (max-width: 769px) {
            margin: 1em 0em 2.0em 0em;
        }
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

        .filter-selection-modal{
            padding: 20px;
            background-color: white;
            border-radius: 3px;
        }

        .active-filter-item {
            background-color: white;
            padding: 0.8em;
            margin: 4px;
            top: 0;
            position: relative;
            display: block; 
            transition: top 0.1s ease;
            cursor: grab;
        
            .filter-name {
                text-overflow: ellipsis;
                overflow-x: hidden;
                white-space: nowrap;
                font-weight: bold;
                margin-left: 0.4em;
                margin-right: 0.4em;
            }
            .label-details {
                font-weight: normal;
                color: $gray;
            }
            .controls { 
                float: right; 
                .switch {
                    position: relative;
                    bottom: 3px;
                }
            }
            .icon {
                bottom: 1px;   
                position: relative;
                font-size: 18px;
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
            &.not-sortable-item, &.not-sortable-item:hover, &.not-focusable-item, &.not-focusable-item:hover  {
                box-shadow: none !important;
                top: 0px !important;
                background-color: white !important;
                cursor: default;

                .field-name {
                    color: $primary !important;
                }
                .label-details, .icon {
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
            top: -2px;

            .label-details, .icon {
                color: white !important;
            }

            .switch.is-small {
                input[type="checkbox"] + .check {
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
            padding: 0.8em;
            margin: 4px;
            min-height: 42px;
            position: relative;
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
            height: 42px;
            position: relative;
            border: 1px solid $draggable-border-color;
            border-radius: 1px;
            transition: left 0.2s ease;
            
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
                border-top-width: 21px;
                border-bottom-width: 21px;
                left: -19px;
            }
            &:before {
                top: -1px;
                border-color: transparent $draggable-border-color transparent transparent;
                border-right-width: 16px;
                border-top-width: 21px;
                border-bottom-width: 21px;
                left: -20px;
            }
        }
        .sortable-drag {
            opacity: 1 !important;
        }

        @keyframes hightlighten {
            0%   {
                color: #222;             
                background-color: white;
                border-color: white;
            }
            25%  {
                color: white;            
                background-color: #2cb4c1; 
                border-color: #2cb4c1;
            }
            75%  {
                color: white;            
                background-color: #2cb4c1; 
                border-color: #2cb4c1;
            }
            100% {
                color: #222;             
                background-color: white;
                border-color: white;
            }
        }
        @keyframes hightlighten-icon {
            0%   { color: #b1b1b1; }
            25%  { color: white; }
            75%  { color: white; }
            100% { color: #b1b1b1; }
        }
        @keyframes hightlighten-arrow {
            0%   {
                border-color: transparent white transparent transparent;
                border-color: transparent white transparent transparent; 
            }
            25%  {
                border-color: transparent #2cb4c1 transparent transparent;
                border-color: transparent #2cb4c1 transparent transparent; 
            }
            75%  {
                border-color: transparent #2cb4c1 transparent transparent;
                border-color: transparent #2cb4c1 transparent transparent; 
            }
            100% {
                border-color: transparent white transparent transparent;
                border-color: transparent white transparent transparent;  
            }
        }
        .hightlighted-field {
            background-color: white;
            position: relative;
            left: 0px;
            animation-name: hightlighten;
            animation-duration: 1.0s;
            animation-iteration-count: 2;
            
            .icon{
                animation-name: hightlighten-icon;
                animation-duration: 1.0s;
                animation-iteration-count: 2; 
            }

            &::before,
            &::after {
                animation-name: hightlighten-arrow;
                animation-duration: 1.0s;
                animation-iteration-count: 2;
            }
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
            .icon {
                color: white !important;
            }
        }
    }

</style>

