<template>
    <div>
        <b-loading :active.sync="isLoadingFilterTypes"></b-loading>
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
                <b-filter :label="$i18n.get('label_available_filter_types')">
                    <div class="columns box available-filters-area" >
                        <draggable class="column" :list="availableFilterList" :options="{ sort: false, group: { name:'filters', pull: 'clone', put: false, revertClone: true }}">
                            <div class="available-filter-item" v-if="index % 2 == 0" v-for="(filter, index) in availableFilterList" :key="index">
                                {{ filter.name }}  <b-icon type="is-gray" class="is-pulled-left" icon="drag"></b-icon>
                            </div>
                        </draggable>
                        <draggable class="column" :list="availableFilterList" :options="{ sort: false, group: { name:'filters', pull: 'clone', put: false, revertClone: true }}">
                            <div class="available-filter-item" v-if="index % 2 != 0" v-for="(filter, index) in availableFilterList" :key="index">
                                {{ filter.name }}  <b-icon type="is-gray" class="is-pulled-left" icon="drag"></b-icon>
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
            isLoadingFilterTypes: true,
            isLoadingFilters: false,
            isLoadingFilter: false,
            openedFilterId: '',
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
            'getFilterTypes',
            'getFilters'
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
            this.sendFilter({collectionId: this.collectionId, name: newFilter.name, filterType: newFilter.className, status: 'auto-draft', isRepositoryLevel: this.isRepositoryLevel})
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
        availableFilterList() {
            return this.getFilterTypes();
        },
        activeFilterList() {
            return this.getFilters();
        }
    },
    created() {
        this.isLoadingFilterTypes = true;
        this.isLoadingFilters = true;

        this.fetchFilterTypes()
            .then((res) => {
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

    .available-filters-area {
        padding: 0 10px;
        margin: 0;
        background-color: whitesmoke;

        .available-filter-item {
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
        .available-filter-item:hover {
            border: 1px solid lightgrey;
            box-shadow: 2px 3px 4px rgba(0,0,0,.25);
            position: relative;
            top: -2px;
            left: -2px;
        }
    }

</style>

