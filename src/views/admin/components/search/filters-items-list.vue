<template>
    <aside 
            aria-labelledby="filters-label-landmark"
            :aria-busy="isLoadingFilters">

        <b-loading
                :is-full-page="false"
                :active.sync="isLoadingFilters"/>

        <h3 
                id="filters-label-landmark"
                class="has-text-weight-semibold">
            <span class="gray-icon is-hidden">
                <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-filters"/>
            </span>
            <span>{{ $i18n.get('filters') }}</span>
        </h3>

        <button
                aria-controls="filters-items-list"
                :aria-expanded="!collapseAll"
                v-if="!isLoadingFilters &&
                    ((filters.length >= 0 &&
                    isRepositoryLevel) || filters.length > 0)"
                class="link-style collapse-all"
                @click="collapseAll = !collapseAll">
            <span class="icon">
                <i 
                        :class="{ 'tainacan-icon-arrowdown' : !collapseAll, 'tainacan-icon-arrowright' : collapseAll }"
                        class="has-text-secondary tainacan-icon tainacan-icon-1-125em"/>
            </span>
            <span class="collapse-all__text">
                {{ !collapseAll ? $i18n.get('label_collapse_all') : $i18n.get('label_expand_all') }}
            </span>
        </button>

        <br>

        <filters-tags-list
                style="padding: 1em 0;"
                class="filter-tags-list"
                v-if="filtersAsModal && hasFiltered" />

        <br v-if="filtersAsModal && hasFiltered">
        <div
                v-if="!isLoadingFilters &&
                    ((filters.length >= 0 && isRepositoryLevel) || filters.length > 0)"
                class="filters-components-list">

            <!--  TAXONOMY TERM ITEMS FILTERS -->
            <template v-if="taxonomy && taxonomyFilters">
                <div 
                        v-if="key == 'repository-filters'"
                        :key="index"
                        v-for="(taxonomyFilter, key, index) of taxonomyFilters">
                    <div 
                            v-tooltip="{
                                delay: {
                                    shown: 500,
                                    hide: 300,
                                },
                                content: $i18n.get('label_filters_from') + ' ' + taxonomyFiltersCollectionNames[key] + ': ',
                                autoHide: false,
                                popperClass: ['tainacan-tooltip', 'tooltip', isRepositoryLevel ? 'tainacan-repository-tooltip' : ''],
                                placement: 'auto-start'
                            }" 
                            v-if="taxonomyFilter.length > 0 && taxonomyFiltersCollectionNames != undefined && taxonomyFiltersCollectionNames[key] != undefined"
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
                                :is-loading-items="isLoadingItems"
                                v-show="!isMenuCompressed"        
                                :query="getQuery"
                                v-for="(filter, filterIndex) in taxonomyFilter"
                                :key="filterIndex"
                                :filter="filter"
                                :expand-all="!collapseAll"
                                :is-repository-level="key == 'repository-filters'"
                                :filters-as-modal="filtersAsModal"
                                :is-mobile-screen="isMobileScreen"/>
                    </template>
                    <!-- <p   
                            class="has-text-gray"
                            style="font-size: 0.75em;"
                            v-if="taxonomyFilter.length <= 0">
                        {{ $i18n.get('info_there_is_no_filter') }}    
                    </p> -->
                    <hr v-if="taxonomyFilter.length > 1">
                </div>
                <div 
                        v-if="key != 'repository-filters'"
                        :key="index"
                        v-for="(taxonomyFilter, key, index) of taxonomyFilters">
                    <div 
                            v-tooltip="{
                                delay: {
                                    shown: 500,
                                    hide: 300,
                                },
                                content: $i18n.get('label_filters_from') + ' ' + taxonomyFiltersCollectionNames[key] + ': ',
                                autoHide: false,
                                popperClass: ['tainacan-tooltip', 'tooltip', isRepositoryLevel ? 'tainacan-repository-tooltip' : ''],
                                placement: 'auto-start'
                            }" 
                            v-if="taxonomyFilter.length > 0 && taxonomyFiltersCollectionNames != undefined && taxonomyFiltersCollectionNames[key] != undefined"
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
                                :is-loading-items="isLoadingItems"
                                v-show="!isMenuCompressed"        
                                :query="getQuery"
                                v-for="(filter, filterIndex) in taxonomyFilter"
                                :key="filterIndex"
                                :filter="filter"
                                :expand-all="!collapseAll"
                                :is-repository-level="key == 'repository-filters'"
                                :filters-as-modal="filtersAsModal"
                                :is-mobile-screen="isMobileScreen" />
                    </template>
                    <!-- <p   
                            class="has-text-gray"
                            style="font-size: 0.75em;"
                            v-if="taxonomyFilter.length <= 0">
                        {{ $i18n.get('info_there_is_no_filter') }}    
                    </p> -->
                    <hr v-if="taxonomyFilter.length > 1">
                </div>
            </template>

            <!-- REPOSITORY ITEMS FILTERS -->
            <template v-else-if="isRepositoryLevel && !taxonomy">
                <div 
                        v-if="key == 'repository-filters'"
                        :key="index"
                        v-for="(repositoryCollectionFilter, key, index) of repositoryCollectionFilters">
                    <div 
                            v-tooltip="{
                                delay: {
                                    shown: 500,
                                    hide: 300,
                                },
                                content: $i18n.get('label_filters_from') + ' ' + repositoryCollectionNames[key] + ': ',
                                autoHide: false,
                                popperClass: ['tainacan-tooltip', 'tooltip', isRepositoryLevel ? 'tainacan-repository-tooltip' : ''],
                                placement: 'auto-start'
                            }" 
                            v-if="repositoryCollectionFilter.length > 0 && repositoryCollectionNames != undefined && repositoryCollectionNames[key] != undefined"
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
                                :is-loading-items="isLoadingItems"
                                v-show="!isMenuCompressed"        
                                :query="getQuery"
                                v-for="(filter, filterIndex) in repositoryCollectionFilter"
                                :key="filterIndex"
                                :filter="filter"
                                :expand-all="!collapseAll"
                                :is-repository-level="key == 'repository-filters'"
                                :filters-as-modal="filtersAsModal"
                                :is-mobile-screen="isMobileScreen" />
                    </template>
                    <!-- <p   
                            class="has-text-gray"
                            style="font-size: 0.75em;"
                            v-if="taxonomyFilter.length <= 0">
                        {{ $i18n.get('info_there_is_no_filter') }}    
                    </p> -->
                    <hr v-if="repositoryCollectionFilters.length > 1">
                </div>
                <div 
                        v-if="key != 'repository-filters'"
                        :key="index"
                        v-for="(repositoryCollectionFilter, key, index) of repositoryCollectionFilters">
                    <div 
                            v-tooltip="{
                                delay: {
                                    shown: 500,
                                    hide: 300,
                                },
                                content: $i18n.get('label_filters_from') + ' ' + repositoryCollectionNames[key] + ': ',
                                autoHide: false,
                                popperClass: ['tainacan-tooltip', 'tooltip', isRepositoryLevel ? 'tainacan-repository-tooltip' : ''],
                                placement: 'auto-start'
                            }" 
                            v-if="repositoryCollectionFilter.length > 0 && repositoryCollectionNames != undefined && repositoryCollectionNames[key] != undefined"
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
                                :is-loading-items="isLoadingItems"
                                v-show="!isMenuCompressed"        
                                :query="getQuery"
                                v-for="(filter, filterIndex) in repositoryCollectionFilter"
                                :key="filterIndex"
                                :filter="filter"
                                :expand-all="!collapseAll"
                                :is-repository-level="key == 'repository-filters'"
                                :filters-as-modal="filtersAsModal"
                                :is-mobile-screen="isMobileScreen" />
                    </template>
                    <!-- <p   
                            class="has-text-gray"
                            style="font-size: 0.75em;"
                            v-if="taxonomyFilter.length <= 0">
                        {{ $i18n.get('info_there_is_no_filter') }}    
                    </p> -->
                    <hr v-if="repositoryCollectionFilters.length > 1">
                </div>
            </template>

            <!-- COLLECTION ITEMS FILTERS -->
            <template v-else>
                <tainacan-filter-item
                        :is-loading-items="isLoadingItems"
                        v-show="!isMenuCompressed"        
                        :query="getQuery"
                        v-for="(filter, index) in filters"
                        :key="index"
                        :filter="filter"
                        :expand-all="!collapseAll"
                        :is-repository-level="isRepositoryLevel"
                        :filters-as-modal="filtersAsModal"
                        :is-mobile-screen="isMobileScreen" />
            </template>
        </div>
        <section
                v-if="!isLoadingFilters &&
                    !((filters.length >= 0 && isRepositoryLevel) || filters.length > 0)"
                class="is-grouped-centered section">
            <div class="content has-text-gray has-text-centered">
                <p>
                    <span class="icon is-large">
                        <i class="tainacan-icon tainacan-icon-36px tainacan-icon-filters" />
                    </span>
                </p>
                <p>{{ $i18n.get('info_there_is_no_filter' ) }}</p>
                <router-link
                        v-if="!$adminOptions.hideItemsListFilterCreationButton && $route.name != null && ((isRepositoryLevel && $userCaps.hasCapability('tnc_rep_edit_filters')) || (!isRepositoryLevel && collection && collection.current_user_can_edit_filters))"
                        id="button-create-filter"
                        :to="isRepositoryLevel && $routerHelper ? $routerHelper.getNewFilterPath() : $routerHelper.getNewCollectionFilterPath(collectionId)"
                        tag="button"
                        class="button is-secondary is-centered">
                    {{ $i18n.getFrom('filters', 'new_item') }}
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
            collectionId: String,
            isRepositoryLevel: Boolean,
            taxonomy: String,
            filtersAsModal: Boolean,
            hasFiltered: Boolean,
            isLoadingItems: Boolean,
            isMobileScreen: false
        },
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
            filters() {
                return this.getFilters();
            },
            repositoryCollectionFilters() {
                return this.getRepositoryCollectionFilters();
            },
            taxonomyFilters() {
                return this.getTaxonomyFilters();
            },
            getQuery() {
                return this.getPostQuery();
            },
            taxonomyId () {
                const taxonomyArray = this.taxonomy.split("_");
                return taxonomyArray[taxonomyArray.length - 1];
            },
            collection() {
                return this.getCollection();
            }
        },
        watch: {
            taxonomyFilters() {
                if ( this.taxonomyFilters != undefined && Object.keys(this.taxonomyFilters).length ) {
                    
                    this.$set(this.taxonomyFiltersCollectionNames, 'repository-filters', this.$i18n.get('repository'));
                                                    
                    // Cancels previous collection name Request
                    if (this.collectionNameSearchCancel != undefined)
                        this.collectionNameSearchCancel.cancel('Collection name search Canceled.');
                    let collectionIds = JSON.parse(JSON.stringify(Object.keys(this.taxonomyFilters)));
    
                    this.fetchAllCollectionNames( collectionIds.filter(aCollectionId => aCollectionId !== 'repository-filters') )
                        .then((resp) => {
                            resp.request
                                .then((collections) => {
                                    for (let collection of collections)
                                        this.$set(this.taxonomyFiltersCollectionNames, '' + collection.id, collection.name);
                                });
                            // Search Request Token for cancelling
                            this.collectionNameSearchCancel = resp.source;     
                        });
                }
            },
            repositoryCollectionFilters() {
                if ( this.repositoryCollectionFilters != undefined && Object.keys(this.repositoryCollectionFilters).length ) {
                    
                    this.$set(this.repositoryCollectionNames, 'repository-filters', this.$i18n.get('repository'));
                    
                    for ( let collection of this.getCollections() )
                        this.$set(this.repositoryCollectionNames, '' + collection.id, collection.name);
                }                
            }
        },
        mounted() {
            this.prepareFilters();

            if (this.isUsingElasticSearch)
                this.$eventBusSearch.$on('isLoadingItems', this.updateIsLoadingItems);
        },
        beforeDestroy() {
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
                this.$eventBusSearch.$off('isLoadingItems', this.updateIsLoadingItems);
     
        },
        methods: {
            ...mapGetters('search',[
                'getPostQuery'
            ]),
            ...mapGetters('collection',[
                'getCollection',
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
            ...mapGetters('filter', [
                'getFilters',
                'getTaxonomyFilters',
                'getRepositoryCollectionFilters'
            ]),
            prepareFilters() {
                // Cancels previous Request
                if (this.filtersSearchCancel != undefined)
                    this.filtersSearchCancel.cancel('Filters search Canceled.');

                this.isLoadingFilters = true;
            
                // Normal filter loading, only collection ones
                if (!this.taxonomy) {
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
                } else {

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
                        
                }

                // On repository level we also fetch collection filters
                if ( !this.taxonomy && this.isRepositoryLevel ) {
                    
                    // Cancels previous Request
                    if (this.repositoryFiltersSearchCancel != undefined)
                        this.repositoryFiltersSearchCancel.cancel('Repository Collection Filters search Canceled.');
     
                    this.fetchRepositoryCollectionFilters()
                        .then((source) => {
                            this.repositoryFiltersSearchCancel = source;
                        });
                }
            },
            updateIsLoadingItems(isLoadingItems) {
                this.$emit('updateIsLoadingItemsState', isLoadingItems); 
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
