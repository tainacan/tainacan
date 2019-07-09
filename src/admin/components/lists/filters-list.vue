<template>
    <div class="filters-list-page">
        <b-loading :active.sync="isLoadingMetadatumTypes"/>
        <div 
                v-if="!isRepositoryLevel"
                class="tainacan-page-title">
            <h1>
                {{ $i18n.get('title_collection_filters_edition') + ' ' }}
                <span style="font-weight: 600;">{{ collectionName }}</span>
            </h1>
            <a 
                    @click="$router.go(-1)"
                    class="back-link has-text-secondary">
                {{ $i18n.get('back') }}
            </a>
            <hr>
        </div>
        <p v-if="isRepositoryLevel">{{ $i18n.get('info_repository_filters_inheritance') }}</p>
        <br>
        <div
                :style="{ height: activeFilterList.length <= 0 && !isLoadingFilters ? 'auto' : 'calc(100vh - 6px - ' + columnsTopY + 'px)'}"
                class="columns"
                ref="filterEditionPageColumns">
            <div class="column">
                <section 
                        v-if="activeFilterList.length <= 0 && !isLoadingFilters"
                        class="field is-grouped-centered section">
                    <div class="content has-text-gray has-text-centered">
                        <p>
                            <span class="icon is-large">
                                <i class="tainacan-icon tainacan-icon-36px tainacan-icon-filters"/>
                            </span>
                        </p>
                        <p>{{ $i18n.get('info_there_is_no_filter' ) }}</p>  
                        <p>{{ $i18n.get('info_create_filters' ) }}</p>
                    </div>
                </section>        
                <draggable 
                        class="active-filters-area"
                        @change="handleChangeOnFilter"
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
                                'not-sortable-item': (isSelectingFilterType || filter.id == undefined || openedFilterId != '' || choosenMetadatum.name == filter.name || isUpdatingFiltersOrder == true || isRepositoryLevel),
                                'not-focusable-item': openedFilterId == filter.id, 
                                'disabled-filter': filter.enabled == false,
                                'inherited-filter': filter.collection_id != collectionId || isRepositoryLevel
                            }" 
                            v-for="(filter, index) in activeFilterList" 
                            :key="filter.id">
                        <div class="handle">
                            <span 
                                    v-if="!(isSelectingFilterType || filter.id == undefined || openedFilterId != '' || choosenMetadatum.name == filter.name || isUpdatingFiltersOrder == true || isRepositoryLevel)"
                                    v-tooltip="{
                                        content: $i18n.get('instruction_drag_and_drop_filter_sort'),
                                        autoHide: true,
                                        classes: ['tooltip', isRepositoryLevel ? 'repository-tooltip' : ''],
                                        placement: 'auto-start'
                                    }"
                                    class="icon grip-icon">
                                <i class="tainacan-icon tainacan-icon-18px tainacan-icon-drag"/>
                            </span>
                            <span 
                                    v-tooltip="{
                                        content: filter.collection_id != collectionId ? $i18n.get('label_repository_filter') : $i18n.get('label_collection_filter'),
                                        autoHide: true,
                                        classes: ['tooltip', isRepositoryLevel ? 'repository-tooltip' : ''],
                                        placement: 'auto-start'
                                    }"
                                    class="icon icon-level-identifier">
                                <i 
                                    :class="{ 
                                        'tainacan-icon-collections': filter.collection_id == collectionId, 
                                        'tainacan-icon-repository': filter.collection_id != collectionId,
                                        'has-text-turquoise5': filter.enabled && filter.collection_id == collectionId, 
                                        'has-text-blue5': filter.enabled && filter.collection_id != collectionId,
                                        'has-text-gray3': !filter.enabled  
                                    }"
                                    class="tainacan-icon" />
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
                                    <span 
                                            v-tooltip="{
                                                content: $i18n.get('edit'),
                                                autoHide: true,
                                                classes: ['tooltip', isRepositoryLevel ? 'repository-tooltip' : ''],
                                                placement: 'bottom'
                                            }"
                                            class="icon">
                                        <i class="tainacan-icon tainacan-icon-20px tainacan-icon-edit"/>
                                    </span>
                                </a>
                                <a 
                                        :style="{ visibility: filter.collection_id != collectionId && !isRepositoryLevel ? 'hidden' : 'visible' }"
                                        @click.prevent="removeFilter(filter)">
                                    <span
                                            v-tooltip="{
                                                content: $i18n.get('delete'),
                                                autoHide: true,
                                                classes: ['tooltip', isRepositoryLevel ? 'repository-tooltip' : ''],
                                                placement: 'bottom'
                                            }"
                                            class="icon">
                                        <i class="tainacan-icon tainacan-icon-20px tainacan-icon-delete"/>
                                    </span>
                                </a>
                            </span>
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
                            @change="handleChangeOnMetadata"
                            v-if="availableMetadata.length > 0 && !isLoadingMetadatumTypes"
                            v-model="availableMetadata"
                            :options="{ 
                                sort: false, 
                                group: { name:'filters', pull: !isSelectingFilterType, put: false, revertClone: true },
                                dragClass: 'sortable-drag'
                            }">
                        <div 
                                class="available-metadatum-item"
                                :class="{
                                    'inherited-metadatum': metadatum.collection_id != collectionId || isRepositoryLevel,
                                    'disabled-metadatum': isSelectingFilterType
                                }"
                                v-if="metadatum.enabled"
                                v-for="(metadatum, index) in availableMetadata"
                                :key="index"
                                @click.prevent="addMetadatumViaButton(metadatum, index)">
                            <span 
                                    v-tooltip="{
                                        content: $i18n.get('instruction_click_or_drag_filter_create'),
                                        autoHide: true,
                                        classes: ['tooltip', isRepositoryLevel ? 'repository-tooltip' : ''],
                                        placement: 'auto-start'
                                    }" 
                                    class="icon grip-icon">
                                <i class="tainacan-icon tainacan-icon-18px tainacan-icon-drag"/>
                            </span>
                            <span 
                                    v-tooltip="{
                                        content: isRepositoryLevel || metadatum.collection_id != collectionId ? $i18n.get('label_repository_filter') : $i18n.get('label_collection_filter'),
                                        autoHide: true,
                                        classes: ['tooltip', isRepositoryLevel ? 'repository-tooltip' : ''],
                                        placement: 'auto-start'
                                    }"
                                    class="icon icon-level-identifier">
                                <i 
                                    :class="{   
                                        'tainacan-icon-collections has-text-turquoise5': metadatum.collection_id == collectionId && !isRepositoryLevel, 
                                        'tainacan-icon-repository has-text-blue5': isRepositoryLevel || metadatum.collection_id != collectionId 
                                    }"
                                    class="tainacan-icon" />
                            </span>  
                            <span class="metadatum-name">{{ metadatum.name }}</span>
                        </div>
                    </draggable>   
                
                    <section 
                            v-if="availableMetadata.length <= 0 && !isLoadingMetadatumTypes"
                            class="field is-grouped-centered section">
                        <div class="content has-text-gray has-text-centered">
                            <p>
                                <span class="icon is-large">
                                    <i class="tainacan-icon tainacan-icon-36px tainacan-icon-metadata"/>
                                </span>
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
        <b-modal 
                ref="filterTypeModal"
                :width="680"
                :active.sync="isSelectingFilterType">
            <div 
                    class="tainacan-modal-content" 
                    style="width: auto">
                <header class="tainacan-modal-title">
                    <h2>{{ this.$i18n.get('label_available_filter_types') }}</h2>
                    <hr>
                </header>
                <section class="tainacan-form">
                    <form class="tainacan-form">
                        <div class="columns">
                            <div class="column">
                                <div 
                                        role="list"
                                        class="filter-types-container">
                                    <p>{{ $i18n.get('instruction_click_to_select_a_filter_type') }}</p>
                                    <br>
                                    <div
                                            role="listitem"
                                            class="filter-type"
                                            v-for="(filterType, index) in allowedFilterTypes"
                                            :key="index"
                                            @click="onFilterTypeSelected(filterType)"
                                            @mouseover="currentFilterTypePreview = { name: filterType.name, template: filterType.preview_template }"
                                            @mouseleave="currentFilterTypePreview = { name: filterType.name, template: filterType.preview_template }">
                                        <h4>{{ filterType.name }}</h4>          
                                    </div>
                                </div>
                            </div>
                            <div class="column">
                                <div 
                                        :style="{ 'min-height': getProperPreviewMinHeight() + 'px'}"
                                        class="filter-type-preview">
                                    <span class="filter-type-label">{{ $i18n.get('label_filter_type_preview') }}</span>
                                    <div 
                                            v-if="currentFilterTypePreview != undefined && currentFilterTypePreview.template != ''"
                                            class="field">
                                        <span class="collapse-handle">
                                            <span class="icon">
                                                <i class="has-text-secondary tainacan-icon tainacan-icon-20px tainacan-icon-arrowdown"/>
                                            </span> 
                                            <label class="label has-tooltip">
                                                {{ currentFilterTypePreview.name }}
                                            </label>
                                        </span>
                                        <div v-html="currentFilterTypePreview.template"/>
                                    </div>
                                    <span 
                                            v-else
                                            class="has-text-gray">
                                        {{ $i18n.get('instruction_hover_a_filter_type_to_preview') }}
                                    </span>
                                </div>
                            </div>                        
                        </div>
                    </form>

                <footer class="field is-grouped form-submit">
                        <div class="control">
                            <button 
                                    class="button is-outlined" 
                                    type="button" 
                                    @click="onCancelFilterTypeSelection()">{{ $i18n.get('cancel') }}</button>
                        </div>
                    </footer>
                </section>
            </div>
        </b-modal>
    </div> 
</template>

<script>
import { mapActions, mapGetters } from 'vuex';
import FilterEditionForm from './../edition/filter-edition-form.vue';
import CustomDialog from '../other/custom-dialog.vue';

export default {
    name: 'FiltersList',
    data(){           
        return {
            collectionId: '',
            collectionName: '',
            isRepositoryLevel: false,
            isDraggingFromAvailable: false,
            isLoadingMetadatumTypes: true,
            isLoadingFilters: false,
            isLoadingFilterTypes: false,
            isLoadingFilter: false,
            isSelectingFilterType: false,
            isUpdatingFiltersOrder: false,
            openedFilterId: '',
            formWithErrors: '',
            editForms: {},
            allowedFilterTypes: [], 
            selectedFilterType: {},
            choosenMetadatum: {},
            newFilterIndex: 0,
            availableMetadata: [],
            filterTypes: [],
            currentFilterTypePreview: undefined,
            columnsTopY: 0        
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
        FilterEditionForm
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
            // 'addTemporaryFilter',
            // 'deleteTemporaryFilter',
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
        ...mapActions('collection', [
            'fetchCollectionName'
        ]),
        handleChangeOnFilter($event) {     
            if ($event.added) {
                this.prepareFilterTypeSelection($event.added.element, $event.added.newIndex);    
            } else if ($event.removed) {
                this.removeFilter($event.removed.element);
            } else if ($event.moved) {
                if (!this.isRepositoryLevel)
                    this.updateFiltersOrder(); 
            }
        },
        prepareFilterTypeSelection(choosenMetadatum, newFilterIndex) {
            this.choosenMetadatum = choosenMetadatum;
            this.newFilterIndex = newFilterIndex;

            this.allowedFilterTypes = [];
            this.selectedFilterType = {};

            for (let filter of this.filterTypes) {
                for (let supportedType of filter['supported_types']) {
                    if (choosenMetadatum.metadata_type_object.primitive_type == supportedType)
                        this.allowedFilterTypes.push(filter);
                }
            }

            this.isSelectingFilterType = true;
        },
        addMetadatumViaButton(metadatum, metadatumIndex) {
            
            this.oldMetadatumIndex = metadatumIndex;

            // Removes element from metadata list, as from button this does not happens
            this.availableMetadata.splice(metadatumIndex, 1);
            
            // Inserts it at the end of the list
            let lastFilterIndex = this.activeFilterList.length;
            // // Updates store with temporary Filter
            // this.addTemporaryFilter(metadatumType);

            this.prepareFilterTypeSelection(metadatum, lastFilterIndex);
        
        },
        onFilterTypeSelected(filterType) {
            this.isSelectingFilterType = false;
            this.allowedFilterTypes = [];
            this.currentFilterTypePreview = undefined;

            this.selectedFilterType = filterType;
            this.createChoosenFilter();
        },
        onCancelFilterTypeSelection() {
            this.isSelectingFilterType = false;
            this.allowedFilterTypes = [];
            this.currentFilterTypePreview = undefined;
            this.selectedFilterType = {};

            // Puts element back to metadata list
            this.availableMetadata.splice(this.oldMetadatumIndex, 0, this.choosenMetadatum)
            this.choosenMetadatum = {};

            // Removes element from filters list
            this.activeFilterList.splice(this.newFilterIndex, 1);
        },
        handleChangeOnMetadata($event) {    
            if ($event.removed) {
                this.oldMetadatumIndex = $event.removed.oldIndex; 
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

            this.availableMetadata = availableMetadata;
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
        createChoosenFilter() {
            this.sendFilter({
                collectionId: this.collectionId, 
                metadatumId: this.choosenMetadatum.id,
                name: this.choosenMetadatum.name,
                filterType: this.selectedFilterType.name, 
                status: 'auto-draft', 
                isRepositoryLevel: this.isRepositoryLevel,
                newIndex: this.newFilterIndex
            })
            .then((filter) => {

                if (!this.isRepositoryLevel)
                    this.updateFiltersOrder();

                this.newFilterIndex = 0;
                this.choosenMetadatum = {};
                this.selectedFilterType = {}
                this.allowedFilterTypes = [];

                this.editFilter(filter);
            })
            .catch((error) => {
                this.$console.error(error);
                this.newFilterIndex = 0;
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
            this.isSelectingFilterType = false;
            this.createChoosenFilter();
        },
        cancelFilterTypeSelection() {
            this.isSelectingFilterType = false;
            this.availableMetadata.push(this.choosenMetadatum);
            this.choosenMetadatum = {};
            this.allowedFilterTypes = [];
            this.selectedFilterType = {};
            // this.deleteTemporaryFilter(this.newFilterIndex);
            this.newFilterIndex = 0;
        },
        editFilter(filter) {
            // Closing collapse
            if (this.openedFilterId == filter.id) {                
                this.openedFilterId = '';

            // Opening collapse
            } else {
                
                if (this.openedFilterId == '' && this.choosenMetadatum.id != undefined) {
                    this.availableMetadata.push(this.choosenMetadatum);
                    this.choosenMetadatum = {};
                    this.allowedFilterTypes = [];
                    this.selectedFilterType = {};
                    // this.deleteTemporaryFilter(this.newFilterIndex);
                    this.newFilterIndex = 0;
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
        },
        getProperPreviewMinHeight() {
            for (let filterType of this.allowedFilterTypes) {
                if (filterType.component == 'tainacan-filter-taginput' || 
                    filterType.component == 'tainacan-filter-checkbox' || 
                    filterType.component == 'tainacan-filter-taxonomy-taginput' || 
                    filterType.component == 'tainacan-filter-taxonomy-checkbox') {
                    return 330;
                }
            }
            return 190;
        }
    },
   mounted() {

        if (!this.isRepositoryLevel)
            this.$root.$emit('onCollectionBreadCrumbUpdate', [{ path: '', label: this.$i18n.get('filter') }]);

        this.$nextTick(() => { 
            this.columnsTopY = this.$refs.filterEditionPageColumns.getBoundingClientRect().top;
        });

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

        
        // Obtains collection name
        if (!this.isRepositoryLevel) {
            this.fetchCollectionName(this.collectionId).then((collectionName) => {
                this.collectionName = collectionName;
            });
        }
        // Sets modal callback function
        this.$refs.filterTypeModal.onCancel = () => {
            this.onCancelFilterTypeSelection();
        }
        
    }
}
</script>

<style lang="scss">

    @import "../../scss/_variables.scss";

    .filters-list-page {
        padding-bottom: 0;

        .tainacan-page-title {
            margin-bottom: 35px;
            display: flex;
            flex-wrap: wrap;
            align-items: flex-end;
            justify-content: space-between;

            h1, h2 {
                font-size: 20px;
                font-weight: 500;
                color: $gray5;
                display: inline-block;
                width: 80%;
                flex-shrink: 1;
                flex-grow: 1;
            }
            a.back-link{
                font-weight: 500;
                float: right;
                margin-top: 5px;
            }
            hr{
                margin: 3px 0px 4px 0px; 
                height: 1px;
                background-color: $secondary;
                width: 100%;
            }
        }
                    
        .column {
            overflow-x: hidden;
            overflow-y: auto;

            &:not(.available-metadata-area){
                margin-right: $page-side-padding;
                flex-grow: 2;

                @media screen and (max-width: 769px) {
                    margin-right: 0;
                }
            }
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
            margin-right: 0.8rem;
            margin-left: -0.8rem;
            padding-right: 3rem;
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

                form.tainacan-form {
                    padding: 1.0em 2.0em;
                    margin-top: 1.0em;
                    border-top: 1px solid $gray2;
                    border-bottom: 1px solid $gray2;
                }
            
                &>.field, form {
                    background-color: white !important;
                }

                .handle {
                    padding-right: 6em;
                    white-space: nowrap;
                    display: flex;
                }
                .grip-icon { 
                    color: $gray3;
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
                    margin-left: 0.5rem;
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

                &.not-sortable-item, &.not-sortable-item:hover {
                    cursor: default;
                    background-color: white !important;
                } 
                &.not-focusable-item, &.not-focusable-item:hover {
                    cursor: default;
                
                    .metadatum-name {
                        color: $secondary;
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
                    color: $white;
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
                    color: $gray3;
                    top: 2px;
                    position: relative;
                }
            }
        }

        .available-metadata-area {
            padding: 10px 0px 10px 10px;
            margin: 0;
            max-width: 500px;
            min-width: 20.8333333%;
            font-size: 0.875rem;

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
                margin: 0.2rem 0rem 1rem 0rem;
                font-weight: 500;
            }

            .available-metadatum-item {
                padding: 0.7em;
                margin: 4px 4px 4px 1.2rem;
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
                    color: $gray3;
                    top: -4px;
                    position: relative;
                    display: inline-block;
                }
                .icon {
                    position: relative;
                    bottom: 4px;
                }
                .metadatum-name {
                    text-overflow: ellipsis;
                    overflow-x: hidden;
                    white-space: nowrap;
                    font-weight: bold;
                    margin-left: 0.4em;
                    display: inline-block;
                    max-width: 180px;
                    width: 60%;
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
            .available-metadatum-item:not(.disabled-metadatum)  {
                &:hover{
                    background-color: $secondary;
                    border-color: $secondary;
                    color: white !important;
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
                        color: white !important;
                    }
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
        .tainacan-modal-content {

            .column {
                overflow: visible;
                margin: 0;
            }

            .filter-types-container {
                height: 100%;
                display: flex;
                flex-direction: column;
                justify-content: center;

                p { margin-bottom: 16px; }

                .filter-type {
                    border-bottom: 1px solid $gray2;
                    padding: 15px 8.3333333%;
                    cursor: pointer;
                
                    &:first-child {
                        margin-top: 15px;
                    }
                    &:last-child {
                        border-bottom: none;
                    }
                    &:hover {
                        background-color: $gray2;
                    }
                }
            }

            .filter-type-preview {
                background: $gray1;
                margin: 12px auto;
                padding: 12px 30px;
                border-radius: 3px;
                z-index: 9999999999999;
                width: 218px;
                display: flex;
                align-items: center;
                justify-content: center;
                pointer-events: none;
                cursor: none;
                flex-wrap: wrap;
                max-width: 260px;
                width: 100%;
                height: 100%;
                min-height: 290px;
                align-items: normal;

                @media screen and (max-width: 769px) {
                    max-width: 100%;
                }

                .filter-type-label {
                    font-weight: 600;
                    color: $gray4;
                    width: 100%;
                    font-size: 1rem;
                    margin-left: -16px;
                }

                input, select, textarea, 
                .input, .tags, .tag  {
                    pointer-events: none;
                    cursor: none;
                    background-color: rgba(255,255,255,0.60) !important;
                }
                .autocomplete>.control, .autocomplete>.control>input, .dropdown-content {
                    background-color: $gray0 !important;
                }
                .taginput {
                    margin-bottom: 80px;
                }
                input[type="checkbox"]:checked + .check {
                    background: rgba(255,255,255,0.60) url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 1 1'%3E%3Cpath style='fill:rgb(69,70,71)' d='M 0.04038059,0.6267767 0.14644661,0.52071068 0.42928932,0.80355339 0.3232233,0.90961941 z M 0.21715729,0.80355339 0.85355339,0.16715729 0.95961941,0.2732233 0.3232233,0.90961941 z'%3E%3C/path%3E%3C/svg%3E") no-repeat center center !important
                }
                textarea {
                    min-height: 70px;
                }
                .field {
                    width: 100%;
                    margin: 6px;
                    .label { 
                        color: $gray5;
                        font-weight: normal;
                    }
                }
                .add-new-term {
                    font-size: 0.75rem;
                    text-decoration: underline;
                    margin: 0.875rem 1.5rem;
                }

                .numeric-filter-container,
                .date-filter-container {
                    display: flex;

                    .field { margin: 0; }
                    .dropdown {
                        width: auto;

                        .dropdown-trigger button {
                            padding: 0 0.5rem !important;
                            height: 30px !important;

                            i:not(.tainacan-icon-arrowdown) {
                                margin-top: -3px;
                                font-size: 1.5rem;
                                font-style: normal;
                                color: #555758;
                            }
                        }
                        .dropdown-menu {
                            display: block !important;
                        }
                    }
                    .datepicker {
                        flex-shrink: 0;
                        max-width: 70%;
                    }
                }

            }

        }
    }
</style>

