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
                                    <span class="filter-name">{{ filter.name }}</span>
                                    <span v-if="filter.id !== undefined" class="label-details">{{ $i18n.get(filter.filter_type_object.component)}}</span><span class="loading-spinner" v-if="filter.id == undefined"></span>
                                    <span class="controls" v-if="filter.id != undefined">
                                        <b-switch size="is-small" v-model="filter.disabled" @input="onChangeEnable($event, index)">{{ filter.disabled ? $i18n.get('label_disabled') : $i18n.get('label_enabled') }}</b-switch>
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
                                                        
                                                        :placeholder="$i18n.get('instruction_select_a_filter_type')">
                                                    <option value="publish" selected>{{ $i18n.get('publish')}}</option>
                                                    <option value="private">{{ $i18n.get('private')}}</option>
                                                </b-select>
                                            </b-field>
                                            <div class="field is-grouped is-grouped-centered">
                                                <div class="control">
                                                    <button 
                                                        class="button is-secondary" 
                                                        type="submit" 
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
                                <b-filter v-if="openedFilterId == filter.id">
                                    <!-- <filter-edition-form 
                                        :collectionId="collectionId"
                                        :isRepositoryLevel="isRepositoryLevel"
                                        @onEditionFinished="onEditionFinished()"
                                        @onEditionCanceled="onEditionCanceled()"
                                        :filter="editForm"></filter-edition-form> -->
                                </b-filter>
                            </div>
                                             
                        <!-- <div class="not-sortable-item" slot="footer">{{ $i18n.get('instruction_dragndrop_filters_collection') }}</div> -->
                    </draggable> 
                </b-filter>
            </div>
            <div class="column">
                <b-filter :label="$i18n.get('label_available_field_types')">
                    <div class="columns box available-fields-area" >
                        <draggable class="column" :list="availableFieldList" :options="{ sort: false, group: { name:'filters', pull: 'clone', put: false, revertClone: true }}">
                            <div class="available-field-item" v-if="index % 2 == 0" v-for="(field, index) in availableFieldList" :key="index">
                                {{ field.name }}  <b-icon type="is-gray" class="is-pulled-left" icon="drag"></b-icon>
                            </div>
                        </draggable>
                        <draggable class="column" :list="availableFieldList" :options="{ sort: false, group: { name:'filters', pull: 'clone', put: false, revertClone: true }}">
                            <div class="available-field-item" v-if="index % 2 != 0" v-for="(field, index) in availableFieldList" :key="index">
                                {{ field.name }}  <b-iiltercon type="is-gray" class="is-pulled-left" icon="drag"></b-iiltercon>
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
            editForm: {}
        }
    },
    components: {
        //FilterEditionForm
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
            'fetchFieldTypes'
        ]),
        ...mapGetters('fields',[
            'getFieldTypes'
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
                filtersOrder.push({'id': filter.id, 'enabled': !filter.disabled});
            }
            this.updateCollectionFiltersOrder({ collectionId: this.collectionId, filtersOrder: filtersOrder });
        },
        onChangeEnable($event, index) {
            this.activeFilterList[index].disabled = $event;
            let filtersOrder = [];
            for (let filter of this.activeFilterList) {
                filtersOrder.push({'id': filter.id, 'enabled': !filter.disabled});
            }
            this.updateCollectionFiltersOrder({ collectionId: this.collectionId, filtersOrder: filtersOrder });

        },
        addNewFilter(newFilter, newIndex) {
            this.isModalOpened = true;
        },
        createChoosenFilter(field, newFilter, newIndex) {
            this.sendFilter({collectionId: this.collectionId, fieldId: field.id, name: newFilter.name, filterType: newFilter.className, status: 'auto-draft', isRepositoryLevel: this.isRepositoryLevel})
            .then((filter) => {

                if (newIndex < 0) {
                    this.activeFilterList.pop();
                    this.activeFilterList.push(filter);
                } else {
                   this.activeFilterList.splice(newIndex, 1, filter);  
                }

                if (!this.isRepositoryLevel)
                    this.updateFiltersOrder();

                this.editFilter(filter);
            })
            .catch((error) => {
                console.log(error);
            });
        },
        removeFilter(removedFilter) {
            this.deleteFilter({ collectionId: this.collectionId, filterId: removedFilter.id, isRepositoryLevel: this.isRepositoryLevel})
            .then((filter) => {
                let index = this.activeFilterList.findIndex(deletedFilter => deletedFilter.id === filter.id);
                if (index >= 0) 
                    this.activeFilterList.splice(index, 1);
                
                if (!this.isRepositoryLevel)
                    this.updateFiltersOrder(); 
            })
            .catch((error) => {
            });
        },
        confirmSelectedFilterType() {
           // this.createChoosenFilter();
        },
        cancelFilterTypeSelection() {
           // this.createChoosenFilter();
           this.$modal.close();
        },
        editFilter(filter) {
            if (this.openedFilterId == filter.id) {
                this.openedFilterId = '';
                this.editForm = {};
            } else {
                this.openedFilterId = filter.id;
                this.editForm = JSON.parse(JSON.stringify(filter));
                this.editForm.status = 'publish';
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
            return this.getFieldTypes();
        },
        activeFilterList() {
            return this.getFilters();
        },
        filterTypes() {
            return this.getFilterTypes();
        }
    },
    created() {
        this.isLoadingFieldTypes = true;
        this.isLoadingFilters = true;
        this.isLoadingFilterTypes = true;

        this.fetchFieldTypes()
            .then((res) => {
                this.isLoadingFieldTypes = false;
            })
            .catch((error) => {
                this.isLoadingFieldTypes = false;
            });

        this.fetchFilterTypes()
            .then((res) => {
                console.log(res);
                this.isLoadingFilterTypes = false;
            })
            .catch((error) => {
                this.isLoadingFilterTypes = false;
            });

        this.isRepositoryLevel = this.$route.name == 'FiltersPage' ? true : false;
        if (this.isRepositoryLevel)
            this.collectionId = 'default';
        else
            this.collectionId = this.$route.params.collectionId;
        

        this.fetchFilters({collectionId: this.collectionId, isRepositoryLevel: this.isRepositoryLevel})
            .then((res) => {
                this.isLoadingFilters = false;
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

