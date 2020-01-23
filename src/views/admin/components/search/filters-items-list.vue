<template>
    <div>

        <b-loading
                :is-full-page="false"
                :active.sync="isLoadingFilters"/>

        <h3 
                id="filters-label-landmark"
                class="has-text-weight-semibold">
            {{ $i18n.get('filters') }}
        </h3>

        <button
                aria-controls="filters-items-list"
                :aria-expanded="!collapseAll"
                v-if="!isLoadingFilters &&
                    ((filters.length >= 0 &&
                    isRepositoryLevel) || filters.length > 0)"
                class="link-style collapse-all"
                @click="collapseAll = !collapseAll">
            {{ collapseAll ? $i18n.get('label_collapse_all') : $i18n.get('label_expand_all') }}
            <span class="icon">
                <i 
                        :class="{ 'tainacan-icon-arrowdown' : collapseAll, 'tainacan-icon-arrowright' : !collapseAll }"
                        class="has-text-secondary tainacan-icon tainacan-icon-20px"/>
            </span>
        </button>

        <br>
        <br>
        <div            
                v-if="!isLoadingFilters &&
                    ((filters.length >= 0 && isRepositoryLevel) || filters.length > 0)"
                class="extra-margin">

            <!-- TERM ITEMS PAGE FILTERS -->
            <template v-if="taxonomy && taxonomyFilters">
                <div 
                        v-if="key == 'repository-filters'"
                        :key="index"
                        v-for="(taxonomyFilter, key, index) of taxonomyFilters">
                    <div 
                            v-tooltip="{
                                delay: {
                                    show: 500,
                                    hide: 300,
                                },
                                content: $i18n.get('label_filters_from') + ' ' + taxonomyFiltersCollectionNames[key] + ': ',
                                autoHide: false,
                                classes: ['tooltip', isRepositoryLevel ? 'repository-tooltip' : ''],
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
                                v-show="!isMenuCompressed"        
                                :query="getQuery"
                                v-for="(filter, filterIndex) in taxonomyFilter"
                                :key="filterIndex"
                                :filter="filter"
                                :open="!collapseAll"
                                :is-repository-level="key == 'repository-filters'"/>
                    </template>
                    <!-- <p   
                            class="has-text-gray is-size-7"
                            v-if="taxonomyFilter.length <= 0">
                        {{ $i18n.get('info_there_is_no_filter') }}    
                    </p> -->
                    <hr v-if="taxonomyFilter.length > 0">
                </div>
                <div 
                        v-if="key != 'repository-filters'"
                        :key="index"
                        v-for="(taxonomyFilter, key, index) of taxonomyFilters">
                    <div 
                            v-tooltip="{
                                delay: {
                                    show: 500,
                                    hide: 300,
                                },
                                content: $i18n.get('label_filters_from') + ' ' + taxonomyFiltersCollectionNames[key] + ': ',
                                autoHide: false,
                                classes: ['tooltip', isRepositoryLevel ? 'repository-tooltip' : ''],
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
                                v-show="!isMenuCompressed"        
                                :query="getQuery"
                                v-for="(filter, filterIndex) in taxonomyFilter"
                                :key="filterIndex"
                                :filter="filter"
                                :open="!collapseAll"
                                :is-repository-level="key == 'repository-filters'"/>
                    </template>
                    <!-- <p   
                            class="has-text-gray is-size-7"
                            v-if="taxonomyFilter.length <= 0">
                        {{ $i18n.get('info_there_is_no_filter') }}    
                    </p> -->
                    <hr v-if="taxonomyFilter.length > 0">
                </div>
            </template>

            <!-- REPOSITORY ITEMS PAGE FILTERS -->
            <template v-else-if="isRepositoryLevel && !taxonomy">
                <collections-filter
                        :open="!collapseAll"
                        :query="getQuery"/>
                <div 
                        v-if="key == 'repository-filters'"
                        :key="index"
                        v-for="(repositoryCollectionFilter, key, index) of repositoryCollectionFilters">
                    <div 
                            v-tooltip="{
                                delay: {
                                    show: 500,
                                    hide: 300,
                                },
                                content: $i18n.get('label_filters_from') + ' ' + repositoryCollectionNames[key] + ': ',
                                autoHide: false,
                                classes: ['tooltip', isRepositoryLevel ? 'repository-tooltip' : ''],
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
                                v-show="!isMenuCompressed"        
                                :query="getQuery"
                                v-for="(filter, filterIndex) in repositoryCollectionFilter"
                                :key="filterIndex"
                                :filter="filter"
                                :open="!collapseAll"
                                :is-repository-level="key == 'repository-filters'"/>
                    </template>
                    <!-- <p   
                            class="has-text-gray is-size-7"
                            v-if="taxonomyFilter.length <= 0">
                        {{ $i18n.get('info_there_is_no_filter') }}    
                    </p> -->
                    <hr v-if="repositoryCollectionFilters.length > 0">
                </div>
                <div 
                        v-if="key != 'repository-filters'"
                        :key="index"
                        v-for="(repositoryCollectionFilter, key, index) of repositoryCollectionFilters">
                    <div 
                            v-tooltip="{
                                delay: {
                                    show: 500,
                                    hide: 300,
                                },
                                content: $i18n.get('label_filters_from') + ' ' + repositoryCollectionNames[key] + ': ',
                                autoHide: false,
                                classes: ['tooltip', isRepositoryLevel ? 'repository-tooltip' : ''],
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
                                v-show="!isMenuCompressed"        
                                :query="getQuery"
                                v-for="(filter, filterIndex) in repositoryCollectionFilter"
                                :key="filterIndex"
                                :filter="filter"
                                :open="!collapseAll"
                                :is-repository-level="key == 'repository-filters'"/>
                    </template>
                    <!-- <p   
                            class="has-text-gray is-size-7"
                            v-if="taxonomyFilter.length <= 0">
                        {{ $i18n.get('info_there_is_no_filter') }}    
                    </p> -->
                    <hr v-if="repositoryCollectionFilters.length > 0">
                </div>
            </template>

            <!-- COLLECTION ITEMS PAGE FILTERS -->
            <template v-else>
                <tainacan-filter-item
                        v-show="!isMenuCompressed"        
                        :query="getQuery"
                        v-for="(filter, index) in filters"
                        :key="index"
                        :filter="filter"
                        :open="!collapseAll"
                        :is-repository-level="isRepositoryLevel"/>
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
                        v-if="!$route.query.iframemode && this.$route.name != null"
                        id="button-create-filter"
                        :to="isRepositoryLevel && $routerHelper ? $routerHelper.getNewFilterPath() : $routerHelper.getNewCollectionFilterPath(collectionId)"
                        tag="button"
                        class="button is-secondary is-centered">
                    {{ $i18n.getFrom('filters', 'new_item') }}
                </router-link>
            </div>
        </section>
    </div>    
</template> 

<script>
    import { mapGetters, mapActions } from 'vuex';
    import CollectionsFilter from '../other/collection-filter.vue';

    export default {
        components: {
            CollectionsFilter
        },
        props: {
            collectionId: String,
            isRepositoryLevel: Boolean,
            taxonomy: String,
            isLoadingFilters: false,
        },
        data() {
            return {
                collapseAll: false,
                taxonomyFiltersCollectionNames: {},
                repositoryCollectionNames: {},
                collectionNameSearchCancel: undefined,
                filtersSearchCancel: undefined,
                repositoryFiltersSearchCancel: undefined,
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
            }
        },
        watch: {
            taxonomyFilters() {
                if (this.taxonomyFilters != undefined) {
                    
                    this.$set(this.taxonomyFiltersCollectionNames, 'repository-filters', this.$i18n.get('repository'));
                                                    
                    // Cancels previous collection name Request
                    if (this.collectionNameSearchCancel != undefined)
                        this.collectionNameSearchCancel.cancel('Collection name search Canceled.');
                    const collectionIds = JSON.parse(JSON.stringify(Object.keys(this.taxonomyFilters)));
                    delete collectionIds['repository-filters'];

                    this.fetchAllCollectionNames(collectionIds)
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
                if (this.repositoryCollectionFilters != undefined) {
                    
                    this.$set(this.repositoryCollectionNames, 'repository-filters', this.$i18n.get('repository'));
                    
                    // Cancels previous collection name Request
                    if (this.collectionNameSearchCancel != undefined)
                        this.collectionNameSearchCancel.cancel('Collection name search Canceled.');

                    this.fetchAllCollectionNames()
                        .then((resp) => {
                            resp.request
                                .then((collections) => {
                                    for (let collection of collections)
                                        this.$set(this.repositoryCollectionNames, '' + collection.id, collection.name);
                                });
                            // Search Request Token for cancelling
                            this.collectionNameSearchCancel = resp.source;
                        });
                }                
            }
        },
        mounted() {
            this.$eventBusSearch.$on('hasToPrepareMetadataAndFilters', () => {
                /* This condition is to prevent an incorrect fetch by filter or metadata when we come from items
                 * at collection level to items page at repository level
                 */
                this.prepareFilters();
            });
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

            this.$eventBusSearch.$off('hasToPrepareMetadataAndFilters');
     
        },
        methods: {
            ...mapGetters('search',[
                'getPostQuery'
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
                    let taxonomyId = this.taxonomy.split("_");
                    this.fetchTaxonomyFilters(taxonomyId[taxonomyId.length - 1])
                        .catch(() => this.isLoadingFilters = false);
                        
                }

                // On repository level we also fetch collection filters
                if (this.isRepositoryLevel) {
                    
                    // Cancels previous Request
                    if (this.repositoryFiltersSearchCancel != undefined)
                        this.repositoryFiltersSearchCancel.cancel('Repository Collection Filters search Canceled.');

                    this.fetchRepositoryCollectionFilters()
                        .then((source) => {
                            this.repositoryFiltersSearchCancel = source;
                        });
                }
            }
        }
    }
</script>

<style scoped>

    h3 {
        font-size: 100%;
        margin-top: 48px;
    }

    @media screen and (max-width: 768px) {

        h3 {
            margin-top: 0 !important;
        }
    }

    .collapse-all {
        display: inline-flex;
        align-items: center;
        font-size: 0.75rem !important;
    }
    .extra-margin {
        margin-bottom: 40px;
    }
    .collection-name {
        color: #454647;
        font-size: 0.875rem;
        font-weight: 500;
        margin-bottom: 0.875rem;
        margin-top: 1rem;
        text-overflow: ellipsis;
        white-space: nowrap;
        overflow: hidden;
        width: 100%;
    }
    .is-loading:after {
        border: 2px solid white !important;
        border-top-color: #dbdbdb !important;
        border-right-color: #dbdbdb !important;
    }

</style>
