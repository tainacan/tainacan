<template>
    <div>
        <b-loading :active.sync="isLoadingMetadatumTypes"/>
        <tainacan-title v-if="!isRepositoryLevel"/>
        <p v-if="isRepositoryLevel">{{ $i18n.get('info_repository_filters_inheritance') }}</p>
        <br>
        <div class="columns">
            <div class="column">
                <section 
                        v-if="activeFilterList.length <= 0 && !isLoadingFilters"
                        class="field is-grouped-centered section">
                    <div class="content has-text-gray has-text-centered">
                        <p>
                            <b-icon
                                    icon="filter"
                                    size="is-large"/>
                        </p>
                        <p>{{ $i18n.get('info_there_is_no_filter' ) }}</p>  
                        <p>{{ $i18n.get('info_create_filters' ) }}</p>
                    </div>
                </section>         
                <draggable 
                        class="active-filters-area"
                        @change="handleChange"
                        :class="{'filters-area-receive': isDraggingFromAvailable}" 
                        v-model="activeFilterList" 
                        :options="{
                            group: { name:'filters', pull: false, put: true }, 
                            sort: (openedFilterId == '' || openedFilterId == undefined) && !isRepositoryLevel, 
                            //disabled: openedFilterId != '' && openedFilterId != undefined,
                            handle: '.handle', 
                            ghostClass: 'sortable-ghost',
                            filter: 'not-sortable-item', 
                            animation: '250'}">
                    <div  
                            class="active-filter-item" 
                            :class="{
                                'not-sortable-item': (filter.id == undefined || openedFilterId != '' || choosenMetadatum.name == filter.name || isUpdatingFiltersOrder == true || isRepositoryLevel),
                                'not-focusable-item': openedFilterId == filter.id, 
                                'disabled-filter': filter.enabled == false,
                                'inherited-filter': filter.collection_id != collectionId || isRepositoryLevel
                            }" 
                            v-for="(filter, index) in activeFilterList" 
                            :key="index">
                        <div class="handle">
                            <grip-icon/>
                            <span class="icon icon-level-identifier">
                                <i 
                                    :class="{ 'mdi-folder has-text-turquoise5': filter.collection_id == collectionId, 'mdi-folder-multiple has-text-blue5': filter.collection_id != collectionId }"
                                    class="mdi" />
                            </span> 
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
                                        v-if="!isRepositoryLevel"
                                        :disabled="isUpdatingFiltersOrder" 
                                        size="is-small" 
                                        :value="filter.enabled" 
                                        @input="onChangeEnable($event, index)"/>
                                <a 
                                        :style="{ visibility: filter.collection_id != collectionId && !isRepositoryLevel? 'hidden' : 'visible' }"
                                        @click.prevent="editFilter(filter)">
                                    <b-icon 
                                            type="is-gray" 
                                            icon="pencil"/>
                                </a>
                                <a 
                                        :style="{ visibility: filter.collection_id != collectionId && !isRepositoryLevel ? 'hidden' : 'visible' }"
                                        @click.prevent="removeFilter(filter)">
                                    <b-icon 
                                            type="is-gray" 
                                            icon="delete"/>
                                </a>
                            </span>
                        </div>
                        <div v-if="choosenMetadatum.id == filter.id && openedFilterId == ''">
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
                        <transition name="form-collapse">
                            <b-field v-if="openedFilterId == filter.id">
                                <filter-edition-form
                                        @onEditionFinished="onEditionFinished()"
                                        @onEditionCanceled="onEditionCanceled()"
                                        @onErrorFound="formWithErrors = filter.id"
                                        :index="index"
                                        :original-filter="filter"
                                        :edited-filter="editForms[openedFilterId]"/>
                            </b-field>
                        </transition>
                    </div>
                </draggable>
            </div>
            <div class="column available-metadata-area">
                <div class="field" >
                    <h3 class="label has-text-secondary"> {{ $i18n.get('label_available_metadata') }}</h3>
                    <draggable
                            v-if="availableMetadatumList.length > 0"
                            v-model="availableMetadatumList"
                            :options="{ 
                                sort: false, 
                                group: { name:'filters', pull: true, put: false, revertClone: true },
                                dragClass: 'sortable-drag'
                            }">
                        <div 
                                class="available-metadatum-item"
                                :class="{'inherited-metadatum': metadatum.collection_id != collectionId || isRepositoryLevel}"
                                v-if="metadatum.enabled"
                                v-for="(metadatum, index) in availableMetadatumList"
                                :key="index"
                                @click.prevent="addMetadatumViaButton(metadatum, index)">
                            <grip-icon/> 
                            <span class="icon icon-level-identifier">
                                <i 
                                    :class="{ 'mdi-folder has-text-turquoise5': metadatum.collection_id == collectionId, 'mdi-folder-multiple has-text-blue5': metadatum.collection_id != collectionId }"
                                    class="mdi" />
                            </span>  
                            <span class="metadatum-name">{{ metadatum.name }}</span>
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
                            <p>{{ $i18n.get('info_there_is_no_metadatum' ) }}</p>
                            <router-link
                                    id="button-create-metadatum"
                                    :to="isRepositoryLevel ? $routerHelper.getNewMetadatumPath() : $routerHelper.getNewCollectionMetadatumPath(collectionId)"
                                    tag="button" 
                                    class="button is-secondary is-centered">
                                {{ $i18n.getFrom('metadata', 'new_item') }}</router-link>
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
import CustomDialog from '../other/custom-dialog.vue';

export default {
    name: 'FiltersList',
    data(){           
        return {
            collectionId: '',
            isRepositoryLevel: false,
            isDraggingFromAvailable: false,
            isLoadingMetadatumTypes: true,
            isLoadingFilters: false,
            isLoadingFilterTypes: false,
            isLoadingFilter: false,
            isUpdatingFiltersOrder: false,
            openedFilterId: '',
            formWithErrors: '',
            editForms: {},
            allowedFilterTypes: [],
            selectedFilterType: {},
            choosenMetadatum: {},
            newIndex: 0,
            availableMetadatumList: [],
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
            this.$modal.open({
                parent: this,
                component: CustomDialog,
                props: {
                    icon: 'alert',
                    title: this.$i18n.get('label_warning'),
                    message: this.$i18n.get('info_warning_filters_not_saved'),
                    onConfirm: () => {
                        this.onEditionCanceled();
                        next();
                    },
                }
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
        ...mapActions('metadata', [
            'fetchMetadata',
        ]),
        ...mapGetters('metadata', [
            'getMetadata',
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
            this.isUpdatingFiltersOrder = true;
            this.updateCollectionFiltersOrder({ collectionId: this.collectionId, filtersOrder: filtersOrder })
                .then(() => { this.isUpdatingFiltersOrder = false; })
                .catch(() => { this.isUpdatingFiltersOrder = false });
        },
        updateListOfMetadata() {

            let availableMetadata = JSON.parse(JSON.stringify(this.getMetadata())) ;

            for (let activeFilter of this.activeFilterList) {
                for (let i = availableMetadata.length - 1; i >= 0 ; i--) {
                    if (activeFilter.metadatum != undefined) {
                        if (activeFilter.metadatum.metadatum_id == availableMetadata[i].id)
                            availableMetadata.splice(i, 1);
                    }
                }
            }

            this.availableMetadatumList = availableMetadata;
        },
        onChangeEnable($event, index) {
            let filtersOrder = [];
            for (let filter of this.activeFilterList) {
                filtersOrder.push({'id': filter.id, 'enabled': filter.enabled});
            }
            filtersOrder[index].enabled = $event;
            this.isUpdatingFiltersOrder = true;
            this.updateCollectionFiltersOrder({ collectionId: this.collectionId, filtersOrder: filtersOrder })
                .then(() => { this.isUpdatingFiltersOrder = false; })
                .catch(() => { this.isUpdatingFiltersOrder = false; });
        },
        addMetadatumViaButton(metadatumType, metadatumIndex) {
            this.availableMetadatumList.splice(metadatumIndex, 1);
            let lastIndex = this.activeFilterList.length;

            // Updates store with temporary Filter
            this.addTemporaryFilter(metadatumType);

            this.addNewFilter(metadatumType, lastIndex);
        },
        addNewFilter(choosenMetadatum, newIndex) {
            this.choosenMetadatum = choosenMetadatum;
            this.newIndex = newIndex;
            this.openedFilterId = '';
            this.allowedFilterTypes = [];
            this.selectedFilterType = {};

            for (let filter of this.filterTypes) {
                for (let supportedType of filter['supported_types']) {
                    if (choosenMetadatum.metadata_type_object.primitive_type == supportedType)
                        this.allowedFilterTypes.push(filter);
                }
            }
        },
        createChoosenFilter() {
            
            this.sendFilter({
                collectionId: this.collectionId, 
                metadatumId: this.choosenMetadatum.id,
                name: this.choosenMetadatum.name,
                filterType: this.selectedFilterType.name, 
                status: 'auto-draft', 
                isRepositoryLevel: this.isRepositoryLevel,
                newIndex: this.newIndex
            })
            .then((filter) => {

                if (!this.isRepositoryLevel)
                    this.updateFiltersOrder();

                this.newIndex = 0;
                this.choosenMetadatum = {};
                this.selectedFilterType = {}
                this.allowedFilterTypes = [];

                this.editFilter(filter);
            })
            .catch((error) => {
                this.$console.error(error);
                this.newIndex = 0;
                this.choosenMetadatum = {};
                this.selectedFilterType = {}
                this.allowedFilterTypes = [];
            });
        },
        removeFilter(removedFilter) {

            this.deleteFilter(removedFilter.id)
            .then(() => {
                // Reload Available Metadatum Types List
                this.updateListOfMetadata();
   
            })
            .catch((error) => { this.$console.log(error)});
        
            if (!this.isRepositoryLevel)
                this.updateFiltersOrder(); 
        },
        confirmSelectedFilterType() {
            this.createChoosenFilter();
        },
        cancelFilterTypeSelection() {
           this.availableMetadatumList.push(this.choosenMetadatum);
           this.choosenMetadatum = {};
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
                
                if (this.openedFilterId == '' && this.choosenMetadatum.id != undefined) {
                    this.availableMetadatumList.push(this.choosenMetadatum);
                    this.choosenMetadatum = {};
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

        this.isLoadingMetadatumTypes = true;
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

        this.fetchFilters({collectionId: this.collectionId, isRepositoryLevel: this.isRepositoryLevel, isContextEdit: true, includeDisabled: true })
            .then(() => {
                this.isLoadingFilters = false;
                // Needs to be done after activeFilterList exists to compare and remove chosen metadata.
                this.fetchMetadata({collectionId: this.collectionId, isRepositoryLevel: this.isRepositoryLevel, isContextEdit: true })
                    .then(() => {
                        this.isLoadingMetadatumTypes = false;
                        this.updateListOfMetadata();
                    })
                    .catch(() => {
                        this.isLoadingMetadatumTypes = false;
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
        
            &>.field, form {
                background-color: white !important;
            }

            .handle {
                padding-right: 6em;
            }
            .grip-icon { 
                fill: $gray3;
                top: 1px;
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
                color: $gray3;
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
                border-top: 1px solid $gray2;
                border-bottom: 1px solid $gray2;
                margin-top: 1.0em;
            }
            &.not-sortable-item, &.not-sortable-item:hover {
                cursor: default;
                background-color: white !important;

                .handle .label-details, .handle .icon {
                    color: $gray3 !important;
                }
            } 
            &.not-focusable-item, &.not-focusable-item:hover {
                cursor: default;
               
                .metadatum-name {
                    color: $secondary;
                }
                .handle .label-details, .handle .icon {
                    color: $gray3 !important;
                }
            }
            &.disabled-metadatum {
                color: $gray3;
            }    
        }
        .active-filter-item:hover:not(.not-sortable-item) {
            background-color: $secondary;
            border-color: $secondary;
            color: white !important;

            &>.field, form {
                background-color: white !important;
            }

            .grip-icon { 
                fill: $white;
            }

            .label-details, .icon, .icon-level-identifier>i {
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
            border: 1px dashed $gray2;
            display: block;
            padding: 0.7em 0.9em;
            margin: 4px;
            height: 40px;
            position: relative;

            .grip-icon { 
                fill: $gray3;
                top: 2px;
                position: relative;
            }
        }
    }

    .available-metadata-area {
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
            .available-metadatum-item::before,
            .available-metadatum-item::after {
                display: none !important;
            }
        }

        h3 {
            margin: 0.2em 0em 1em -1.2em;
            font-weight: 500;
        }

        .available-metadatum-item {
            padding: 0.7em;
            margin: 4px;
            background-color: white;
            cursor: pointer;
            left: 0;
            line-height: 1.3em;
            height: 40px;
            position: relative;
            border: 1px solid $gray2;
            border-radius: 1px;
            transition: left 0.2s ease;
            
            .grip-icon { 
                fill: $gray3;
                top: -3px;
                position: relative;
                display: inline-block;
            }
            .icon {
                position: relative;
                bottom: 3px;
            }
            .metadatum-name {
                text-overflow: ellipsis;
                overflow-x: hidden;
                white-space: nowrap;
                font-weight: bold;
                margin-left: 0.4em;
                display: inline-block;
                max-width: 180px;
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
                border-color: transparent $gray2 transparent transparent;
                border-right-width: 16px;
                border-top-width: 20px;
                border-bottom-width: 20px;
                left: -20px;
            }
        }
        .sortable-drag {
            opacity: 1 !important;
        }
        .available-metadatum-item:hover {
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
            .icon-level-identifier>i {
                color: white !important;
            }
            .grip-icon {
                fill: white !important;
            }
        }
    }

   .inherited-filter {
        &.active-filter-item:hover:not(.not-sortable-item) {
            background-color: $blue5;
            border-color: $blue5;
            
            .switch.is-small {
                input[type="checkbox"] + .check {
                    background-color: $blue5 !important;
                } 
                &:hover input[type="checkbox"] + .check {
                    background-color: $blue5 !important;
                }
            }
        }
    }
    .inherited-metadatum {

        &.available-metadatum-item:hover {
            background-color: $blue5 !important;
            border-color: $blue5 !important;
        
            &:after {
                border-color: transparent $blue5 transparent transparent !important;
            }
            &:before {
                border-color: transparent $blue5 transparent transparent !important;
            }

        }
    }

</style>

