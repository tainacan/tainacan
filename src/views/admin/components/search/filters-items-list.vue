<template>
    <aside 
            aria-labelledby="filters-label-landmark"
            :aria-busy="isLoadingFilters">
        <b-loading
                v-model="isLoadingFilters"
                :is-full-page="false" />

        <h3 
                id="filters-label-landmark"
                class="has-text-weight-semibold">
            <span class="gray-icon is-hidden">
                <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-filters" />
            </span>
            <span>{{ $i18n.get('filters') }}</span>
        </h3>

        <button
                v-if="!hideFilterCollapses && !isLoadingFilters && (
                    ( filters.length >= 0 && isRepositoryLevel ) || filters.length > 0
                )"
                aria-controls="filters-items-list"
                :aria-expanded="!collapseAll"
                class="link-style collapse-all"
                @click="collapseAll = !collapseAll">
            <span class="icon">
                <i 
                        :class="{ 'tainacan-icon-arrowdown' : !collapseAll, 'tainacan-icon-arrowright' : collapseAll }"
                        class="has-text-secondary tainacan-icon tainacan-icon-1-125em" />
            </span>
            <span class="collapse-all__text">
                {{ !collapseAll ? $i18n.get('label_collapse_all') : $i18n.get('label_expand_all') }}
            </span>
        </button>

        <br v-if="!hideFilterCollapses">

        <filters-tags-list
                v-if="filtersAsModal && hasFiltered"
                style="padding: 1em 0;"
                class="filter-tags-list" />

        <br v-if="filtersAsModal && hasFiltered">
        <div
                v-if="!isLoadingFilters &&
                    ((filters.length >= 0 && isRepositoryLevel) || filters.length > 0)"
                class="filters-components-list">

            <!--  TAXONOMY TERM ITEMS FILTERS -->
            <template v-if="taxonomy && taxonomyFilters">
                <template 
                        v-for="(taxonomyFilter, key, index) of taxonomyFilters"
                        :key="index">
                    <div v-if="key == 'repository-filters'">
                        <div 
                                v-if="taxonomyFilter.length > 0 && taxonomyFiltersCollectionNames != undefined && taxonomyFiltersCollectionNames[key] != undefined" 
                                v-tooltip="{
                                    delay: {
                                        show: 500,
                                        hide: 300,
                                    },
                                    content: $i18n.get('label_filters_from') + ' ' + taxonomyFiltersCollectionNames[key] + ': ',
                                    autoHide: false,
                                    popperClass: ['tainacan-tooltip', 'tooltip', isRepositoryLevel ? 'tainacan-repository-tooltip' : ''],
                                    placement: 'auto-start'
                                }"
                                class="collection-name">
                            {{ $i18n.get('label_filters_from') + " " + taxonomyFiltersCollectionNames[key] + ": " }}
                        </div>
                        <div    
                                v-if="taxonomyFilter.length > 0 && !(taxonomyFiltersCollectionNames != undefined && taxonomyFiltersCollectionNames[key] != undefined)"
                                class="collection-name">
                            <span 
                                    style="width: 100%; height: 54px;"
                                    class="icon has-text-centered loading-icon">
                                <div class="control has-icons-right is-loading is-clearfix" />
                            </span>
                        </div>
                        <template v-if="taxonomyFilter.length > 0">
                            <tainacan-filter-item
                                    v-for="(filter, filterIndex) in taxonomyFilter"
                                    :key="filterIndex"
                                    :is-loading-items="isLoadingItems"
                                    :query="getQuery"
                                    :filter="filter"
                                    :expand-all="!collapseAll"
                                    :is-repository-level="key == 'repository-filters'"
                                    :filters-as-modal="filtersAsModal"
                                    :is-mobile-screen="isMobileScreen"
                                    :hide-collapses="hideFilterCollapses" />
                        </template>
                        <hr v-if="taxonomyFilter.length > 1">
                    </div>
                </template>
                <template 
                        v-for="(taxonomyFilter, key, index) of taxonomyFilters"
                        :key="index">
                    <div v-if="key != 'repository-filters'">
                        <div 
                                v-if="taxonomyFilter.length > 0 && taxonomyFiltersCollectionNames != undefined && taxonomyFiltersCollectionNames[key] != undefined" 
                                v-tooltip="{
                                    delay: {
                                        show: 500,
                                        hide: 300,
                                    },
                                    content: $i18n.get('label_filters_from') + ' ' + taxonomyFiltersCollectionNames[key] + ': ',
                                    autoHide: false,
                                    popperClass: ['tainacan-tooltip', 'tooltip', isRepositoryLevel ? 'tainacan-repository-tooltip' : ''],
                                    placement: 'auto-start'
                                }"
                                class="collection-name">
                            {{ $i18n.get('label_filters_from') + " " + taxonomyFiltersCollectionNames[key] + ": " }}
                        </div>
                        <div    
                                v-if="taxonomyFilter.length > 0 && !(taxonomyFiltersCollectionNames != undefined && taxonomyFiltersCollectionNames[key] != undefined)"
                                class="collection-name">
                            <span 
                                    style="width: 100%; height: 54px;"
                                    class="icon has-text-centered loading-icon">
                                <div class="control has-icons-right is-loading is-clearfix" />
                            </span>
                        </div>
                        <template v-if="taxonomyFilter.length > 0">
                            <tainacan-filter-item
                                    v-for="(filter, filterIndex) in taxonomyFilter" 
                                    :key="filterIndex"
                                    :is-loading-items="isLoadingItems"
                                    :query="getQuery"
                                    :filter="filter"
                                    :expand-all="!collapseAll"
                                    :is-repository-level="key == 'repository-filters'"
                                    :filters-as-modal="filtersAsModal"
                                    :is-mobile-screen="isMobileScreen"
                                    :hide-collapses="hideFilterCollapses" />
                        </template>
                        <hr v-if="taxonomyFilter.length > 1">
                    </div>
                </template>
            </template>

            <!-- REPOSITORY ITEMS FILTERS -->
            <template v-else-if="isRepositoryLevel && !taxonomy">
                <template 
                        v-for="(repositoryCollectionFilter, key, index) of repositoryCollectionFilters"
                        :key="index">
                    <div v-if="key == 'repository-filters'">
                        <div 
                                v-if="repositoryCollectionFilter.length > 0 && repositoryCollectionNames != undefined && repositoryCollectionNames[key] != undefined" 
                                v-tooltip="{
                                    delay: {
                                        show: 500,
                                        hide: 300,
                                    },
                                    content: $i18n.get('label_filters_from') + ' ' + repositoryCollectionNames[key] + ': ',
                                    autoHide: false,
                                    popperClass: ['tainacan-tooltip', 'tooltip', isRepositoryLevel ? 'tainacan-repository-tooltip' : ''],
                                    placement: 'auto-start'
                                }"
                                class="collection-name">
                            {{ $i18n.get('label_filters_from') + " " + repositoryCollectionNames[key] + ": " }}
                        </div>
                        <div    
                                v-if="repositoryCollectionFilter.length > 0 && !(repositoryCollectionNames != undefined && repositoryCollectionNames[key] != undefined)"
                                class="collection-name">
                            <span 
                                    style="width: 100%; height: 54px;"
                                    class="icon has-text-centered loading-icon">
                                <div class="control has-icons-right is-loading is-clearfix" />
                            </span>
                        </div>
                        <template v-if="repositoryCollectionFilter.length > 0">
                            <tainacan-filter-item
                                    v-for="(filter, filterIndex) in repositoryCollectionFilter"   
                                    :key="filterIndex"
                                    :is-loading-items="isLoadingItems"
                                    :query="getQuery"
                                    :filter="filter"
                                    :expand-all="!collapseAll"
                                    :is-repository-level="key == 'repository-filters'"
                                    :filters-as-modal="filtersAsModal"
                                    :is-mobile-screen="isMobileScreen"
                                    :hide-collapses="hideFilterCollapses" />
                        </template>
                        <hr v-if="repositoryCollectionFilters.length > 1">
                    </div>
                </template>
                <template 
                        v-for="(repositoryCollectionFilter, key, index) of repositoryCollectionFilters"
                        :key="index">
                    <div v-if="key != 'repository-filters'">
                        <div 
                                v-if="repositoryCollectionFilter.length > 0 && repositoryCollectionNames != undefined && repositoryCollectionNames[key] != undefined" 
                                v-tooltip="{
                                    delay: {
                                        show: 500,
                                        hide: 300,
                                    },
                                    content: $i18n.get('label_filters_from') + ' ' + repositoryCollectionNames[key] + ': ',
                                    autoHide: false,
                                    popperClass: ['tainacan-tooltip', 'tooltip', isRepositoryLevel ? 'tainacan-repository-tooltip' : ''],
                                    placement: 'auto-start'
                                }"
                                class="collection-name">
                            {{ $i18n.get('label_filters_from') + " " + repositoryCollectionNames[key] + ": " }}
                        </div>
                        <div    
                                v-if="repositoryCollectionFilter.length > 0 && !(repositoryCollectionNames != undefined && repositoryCollectionNames[key] != undefined)"
                                class="collection-name">
                            <span 
                                    style="width: 100%; height: 54px;"
                                    class="icon has-text-centered loading-icon">
                                <div class="control has-icons-right is-loading is-clearfix" />
                            </span>
                        </div>
                        <template v-if="repositoryCollectionFilter.length > 0">
                            <tainacan-filter-item
                                    v-for="(filter, filterIndex) in repositoryCollectionFilter"       
                                    :key="filterIndex"
                                    :is-loading-items="isLoadingItems"
                                    :query="getQuery"
                                    :filter="filter"
                                    :expand-all="!collapseAll"
                                    :is-repository-level="key == 'repository-filters'"
                                    :filters-as-modal="filtersAsModal"
                                    :is-mobile-screen="isMobileScreen"
                                    :hide-collapses="hideFilterCollapses" />
                        </template>
                        <hr v-if="repositoryCollectionFilters.length > 1">
                    </div>
                </template>
            </template>

            <!-- COLLECTION ITEMS FILTERS -->
            <template v-else>
                <tainacan-filter-item
                        v-for="(filter, index) in filters"
                        :key="index"
                        :is-loading-items="isLoadingItems"
                        :query="getQuery"
                        :filter="filter"
                        :expand-all="!collapseAll"
                        :is-repository-level="isRepositoryLevel"
                        :filters-as-modal="filtersAsModal"
                        :is-mobile-screen="isMobileScreen"
                        :hide-collapses="hideFilterCollapses" />
            </template>
        </div>
        <section
                v-if="!isLoadingFilters && (
                    ( taxonomy && taxonomyFilters && Object.keys(taxonomyFilters).length <= 0 ) ||
                    ( isRepositoryLevel && !taxonomy && repositoryCollectionFilters && Object.keys(repositoryCollectionFilters).length <= 0 ) ||
                    ( !isRepositoryLevel && !taxonomy && filters && filters.length <= 0 )
                )"
                class="is-grouped-centered">
            <div class="content has-text-gray has-text-centered">
                <p>
                    <span class="icon is-large">
                        <i class="tainacan-icon tainacan-icon-36px tainacan-icon-filters" />
                    </span>
                </p>
                <p>{{ $i18n.get('info_there_is_no_filter' ) }}</p>
                <p v-if="isRepositoryLevel && $route.name != null">
                    {{ $i18n.get('info_collection_filter_on_repository_level') }}
                </p>
                <router-link
                        v-if="!$adminOptions.hideItemsListFilterCreationButton && $route.name != null && ((isRepositoryLevel && $userCaps.hasCapability('tnc_rep_edit_filters')) || (!isRepositoryLevel && collection && collection.current_user_can_edit_filters))"
                        v-slot="{ navigate }"
                        :to="isRepositoryLevel && $routerHelper ? $routerHelper.getNewFilterPath() : $routerHelper.getNewCollectionFilterPath(collectionId)"
                        custom>
                    <button
                            id="button-create-filter"
                            type="button"
                            role="button"
                            class="button is-secondary is-centered"
                            @click="navigate()">         
                        {{ $i18n.getFrom('filters', 'new_item') }}
                    </button>
                </router-link>
            </div>
        </section>
    </aside>    
</template> 

<script>
    import { mapGetters, mapActions } from 'vuex';
    import TainacanFilterItem from '../filter-types/tainacan-filter-item.vue';
    import FiltersTagsList from './filters-tags-list.vue';

    export default {
        components: {
            TainacanFilterItem,
            FiltersTagsList
        },
        props: {
            collectionId: [String, Number],
            isRepositoryLevel: Boolean,
            taxonomy: String,
            filtersAsModal: Boolean,
            hasFiltered: Boolean,
            isLoadingItems: Boolean,
            isMobileScreen: false,
            hideFilterCollapses: false
        },
        emits: [
            'update-is-loading-items-state'
        ],
        data() {
            return {
                isLoadingFilters: false,
                collapseAll: false,
                taxonomyFiltersCollectionNames: {},
                repositoryCollectionNames: {},
                collectionNameSearchCancel: undefined,
                filtersSearchCancel: undefined,
                repositoryFiltersSearchCancel: undefined,
                isUsingElasticSearch: tainacan_plugin.wp_elasticpress == "1" ? true : false
            }
        },
        computed: {
            ...mapGetters('collection', {
                'collection': 'getCollection'
            }),
            ...mapGetters('search', {
                'getQuery': 'getPostQuery'
            }),
            ...mapGetters('filter', {
                'filters': 'getFilters',
                'taxonomyFilters': 'getTaxonomyFilters',
                'repositoryCollectionFilters': 'getRepositoryCollectionFilters'
            }),
            taxonomyId () {
                const taxonomyArray = this.taxonomy.split("_");
                return taxonomyArray[taxonomyArray.length - 1];
            }
        },
        watch: {
            taxonomyFilters: {
                handler() {
                    if ( this.taxonomyFilters != undefined && Object.keys(this.taxonomyFilters).length ) {
                        
                        Object.assign( this.taxonomyFiltersCollectionNames, { 'repository-filters': this.$i18n.get('repository') });
                                                        
                        // Cancels previous collection name Request
                        if (this.collectionNameSearchCancel != undefined)
                            this.collectionNameSearchCancel.cancel('Collection name search Canceled.');
                        let collectionIds = JSON.parse(JSON.stringify(Object.keys(this.taxonomyFilters)));
        
                        this.fetchAllCollectionNames( collectionIds.filter(aCollectionId => aCollectionId !== 'repository-filters') )
                            .then((resp) => {
                                resp.request
                                    .then((collections) => {
                                        for (let collection of collections)
                                            Object.assign( this.taxonomyFiltersCollectionNames, { [collection.id]: collection.name });
                                    });
                                // Search Request Token for cancelling
                                this.collectionNameSearchCancel = resp.source;     
                            });
                    }
                },
                deep: true
            },
            repositoryCollectionFilters: {
                handler() {
                    if ( this.repositoryCollectionFilters != undefined && Object.keys(this.repositoryCollectionFilters).length ) {
                        
                        Object.assign( this.repositoryCollectionNames, { 'repository-filters': this.$i18n.get('repository') });
                        
                        for ( let collection of this.getCollections() )
                            Object.assign( this.repositoryCollectionNames, { [collection.id]: collection.name });
                    }
                },
                deep: true                
            }
        },
        mounted() {
            this.prepareFilters();

            if (this.isUsingElasticSearch)
                this.$eventBusSearchEmitter.on('isLoadingItems', this.updateIsLoadingItems);
        },
        beforeUnmount() {
            // Cancels previous collection name Request
            if (this.collectionNameSearchCancel != undefined)
                this.collectionNameSearchCancel.cancel('Collection name search Canceled.');
        
            // Cancels previous Repository Filters Request
            if (this.repositoryFiltersSearchCancel != undefined)
                this.repositoryFiltersSearchCancel.cancel('Repository Collection Filters search Canceled.');

            // Cancels previous Filters Request
            if (this.filtersSearchCancel != undefined)
                this.filtersSearchCancel.cancel('Filters search Canceled.');

            if (this.isUsingElasticSearch)
                this.$eventBusSearchEmitter.off('isLoadingItems', this.updateIsLoadingItems);
     
        },
        methods: {
            ...mapGetters('collection',[
                'getCollections'
            ]),
            ...mapActions('collection',[
                'fetchAllCollectionNames'
            ]),
            ...mapActions('filter', [
                'fetchFilters',
                'fetchTaxonomyFilters',
                'fetchRepositoryCollectionFilters'
            ]),
            prepareFilters() {
                // Cancels previous Request
                if (this.filtersSearchCancel != undefined)
                    this.filtersSearchCancel.cancel('Filters search Canceled.');

                this.isLoadingFilters = true;
            
                // Normal filter loading, only collection ones
                if ( !this.taxonomy && !this.isRepositoryLevel ) {
                    this.fetchFilters({
                        collectionId: this.collectionId,
                        isRepositoryLevel: this.isRepositoryLevel,
                        isContextEdit: false,
                        includeDisabled: false,
                    })
                        .then((resp) => {
                            resp.request
                                .then(() => this.isLoadingFilters = false)
                                .catch(() => this.isLoadingFilters = false);
    
                            // Search Request Token for cancelling
                            this.filtersSearchCancel = resp.source;
                        })
                        .catch(() => this.isLoadingFilters = false);
                
                // Custom filter loading, get's from collections that have items with that taxonomy
                } else if ( this.taxonomy ) {

                    let collectionsIds = [];
                    
                    if (
                        this.$route.query && 
                        this.$route.query.metaquery && 
                        Array.isArray(this.$route.query.metaquery) &&
                        this.$route.query.metaquery.find((aMetaQuery) => aMetaQuery.key == 'collection_id')
                    ) {
                        collectionsIds = this.$route.query.metaquery.find((aMetaQuery) => aMetaQuery.key == 'collection_id').value;
                    }

                    let taxonomyId = this.taxonomy.split("_");
                    this.fetchTaxonomyFilters({ taxonomyId: taxonomyId[taxonomyId.length - 1], collectionsIds: collectionsIds })
                        .catch(() => this.isLoadingFilters = false);
                        
                } else if ( this.isRepositoryLevel && !this.taxonomy ) {

                     // Cancels previous Request
                     if (this.repositoryFiltersSearchCancel != undefined)
                        this.repositoryFiltersSearchCancel.cancel('Repository Collection Filters search Canceled.');
                    
                    this.fetchRepositoryCollectionFilters()
                        .then((anotherResp) => {
                            anotherResp.request
                                .then(() => this.isLoadingFilters = false)
                                .catch(() => this.isLoadingFilters = false);

                            this.repositoryFiltersSearchCancel = anotherResp.source;
                        })
                        .catch(() => this.isLoadingFilters = false);
                
                }

            },
            updateIsLoadingItems(isLoadingItems) {
                this.$emit('update-is-loading-items-state', isLoadingItems); 
            }
        }
    }
</script>

<style scoped>

    h3 {
        font-size: 1em;
        color: var(--tainacan-heading-color);
    }

    .collapse-all {
        display: inline-flex;
        align-items: center;
        margin-left: -0.5em !important;
        margin-bottom: 12px !important;
    }
    .collapse-all__text {
        font-size: 0.75em !important;
    }
    .filters-components-list {
        margin-bottom: 64px;
    }
    @supports (contain: inline-size) {
        .filters-components-list {
            container-type: inline-size;
            container-name: filterscomponentslist; 
        }
    }
    .collection-name {
        color: var(--tainacan-heading-color);
        font-size: 0.875em;
        font-weight: 500;
        margin-bottom: 0.875em;
        margin-top: 1em;
        text-overflow: ellipsis;
        white-space: nowrap;
        overflow: hidden;
        width: 100%;
    }
    .is-loading:after {
        border: 2px solid white !important;
        border-top-color: var(--tainacan-gray2) !important;
        border-right-color: var(--tainacan-gray2) !important;
    }

</style>
