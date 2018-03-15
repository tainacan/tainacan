<template>
    <div>
        <b-loading :active.sync="isLoadingFieldTypes"></b-loading>
        <div class="columns">
            <div class="column">
                <b-filter :label="$i18n.get('label_active_filters')" is-grouped>
                    <draggable 
                        class="box active-filters-area"
                        @change="handleChange"
                        :class="{'filters-area-receive': isDraggingFromAvailable}" 
                        :list="activeFilterList" 
                        :options="{group: { name:'filters', pull: false, put: true }, 'handle': '.handle', chosenClass: 'sortable-chosen', filter: '.not-sortable-item'}">
                        <div  
                            class="active-filter-item" 
                            :class="{'not-sortable-item': filter.id == undefined, 'not-focusable-item': openedFilterId == filter.id, 'inherited-filter': filter.collection_id != collectionId}" 
                            v-for="(filter, index) in activeFilterList" :key="index">
                                <div class="handle">
                                    <b-icon type="is-gray" class="is-pulled-left" icon="drag"></b-icon>
                                    <span v-if="filter.id !== undefined" class="filter-name">{{ filter.name }}</span>
                                    <span v-if="filter.id !== undefined" class="label-details"><span class="loading-spinner" v-if="filter.id == undefined"></span>
                                    <span class="controls" v-if="filter.id != undefined">
                                        <b-switch size="is-small" v-model="filter.enabled" @input="onChangeEnable($event, index)">{{ filter.enabled ? $i18n.get('label_enabled') : $i18n.get('label_disabled') }}</b-switch>
                                        <a @click.prevent="removeFilter(filter)">
                                            <b-icon icon="delete"></b-icon>
                                        </a>
                                        <a @click.prevent="editFilter(filter)">
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
                                             
                        <!-- <div class="not-sortable-item" slot="footer">{{ $i18n.get('instruction_dragndrop_filters_collection') }}</div> -->
                    </draggable> 
                </b-filter>
            </div>
            <div class="column">
                <b-filter :label="$i18n.get('label_available_field_types')">
                    <div class="columns box available-fields-area" >
                        <draggable class="column" :list="availableFieldList" :options="{ sort: false, group: { name:'filters', pull: true, put: false, revertClone: true }}">
                            <div class="available-field-item" v-if="index % 2 == 0" v-for="(field, index) in availableFieldList" :key="index">
                                <a @click.prevent="addFieldViaButton(field)">
                                    <b-icon type="is-gray" class="is-pulled-left" icon="arrow-left-bold"></b-icon>
                                </a>  {{ field.name }}  <b-icon type="is-gray" class="is-pulled-left" icon="drag"></b-icon>
                            </div>
                        </draggable>
                        <draggable class="column" :list="availableFieldList" :options="{ sort: false, group: { name:'filters', pull: true, put: false, revertClone: true }}">
                            <div class="available-field-item" v-if="index % 2 != 0" v-for="(field, index) in availableFieldList" :key="index">
                                <a @click.prevent="addFieldViaButton(field)">
                                    <b-icon type="is-gray" class="is-pulled-left" icon="arrow-left-bold"></b-icon>
                                </a>  {{ field.name }}  <b-icon type="is-gray" class="is-pulled-left" icon="drag"></b-icon>
                            </div>       
                        </draggable> 
                   </div>
                </b-filter>
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
        addFieldViaButton(field) {
            let lastIndex = this.activeFilterList.length;
            this.activeFilterList.push(field);
            this.addNewFilter(field, lastIndex);
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

<style lang="scss" scoped>

    @import "../../scss/_variables.scss";

    .active-filters-area {
        min-height: 40px;
        padding: 10px;

        &.filters-area-receive {
            background-color: whitesmoke;
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
            padding: 0.4em;
            margin: 10px;
            border-radius: 5px;
            border: 1px solid gainsboro;
            display: block; 
            transition: top 0.2s ease;
            cursor: grab;
        
            .filter-name {
                text-overflow: ellipsis;
                overflow-x: hidden;
                white-space: nowrap;
            }
            .label-details {
                font-weight: normal;
                font-style: italic;
                color: gray;
            }
            .controls { float: right }
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
                color: gray;
                cursor: default;
            }
            &.inherited-filter {
                color: gray;
            }
        }
        .active-filter-item:hover {
            box-shadow: 0 3px 4px rgba(0,0,0,0.25);
            position: relative;
            top: -2px;
        }

        .sortable-chosen {
            background-color: $primary-lighter;
            margin: 10px;
            border-radius: 5px;
            border: 1px dashed $primary-light;
            display: block; 
        }
    }

    .available-fields-area {
        padding: 0 10px;
        margin: 0;
        background-color: whitesmoke;

        .available-field-item {
            padding: 0.4em;
            margin: 10px 10% 10px 0px;
            border-radius: 5px;
            background-color: white;
            border: 1px solid gainsboro;
            width: 100%;
            cursor: grab;
            top: 0;
            transition: top 0.2s ease;
        }
        .available-field-item:hover {
            border: 1px solid lightgrey;
            box-shadow: 2px 3px 4px rgba(0,0,0,.25);
            position: relative;
            top: -2px;
            left: -2px;
        }
    }

</style>

