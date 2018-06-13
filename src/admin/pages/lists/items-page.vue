<template>
    <div 
            :class="{'primary-page': isRepositoryLevel}">

        <!-- SEARCH AND FILTERS --------------------- -->
        <!-- Filter menu compress button -->
        <button
                v-if="!openAdvancedSearch"
                id="filter-menu-compress-button"
                :class="{'filter-menu-compress-button-top-repo': isRepositoryLevel}"
                :style="{ top: isHeaderShrinked ? '125px' : '152px'}"
                @click="isFiltersMenuCompressed = !isFiltersMenuCompressed">
            <b-icon :icon="isFiltersMenuCompressed ? 'menu-right' : 'menu-left'" />
        </button>
        <!-- Side bar with search and filters -->
        <aside
                v-show="!isFiltersMenuCompressed && !openAdvancedSearch"
                class="filters-menu"
                :class="{ 'tainacan-form': isOnTheme }">
            <b-loading
                    :is-full-page="false"
                    :active.sync="isLoadingFilters"/>

            <div class="search-area">
                <div class="control has-icons-right  is-small is-clearfix">
                    <input
                            class="input is-small"
                            :placeholder="$i18n.get('instruction_search')"
                            type="search"
                            autocomplete="on"
                            :value="searchQuery"
                            @input="futureSearchQuery = $event.target.value"
                            @keyup.enter="updateSearch()">
                        <span
                                @click="updateSearch()"
                                class="icon is-right">
                            <i class="mdi mdi-magnify" />
                        </span>
                </div>
            </div>
            <a
                    @click="openAdvancedSearch = !openAdvancedSearch"
                    class="is-size-7 is-secondary is-pulled-right">{{ $i18n.get('advanced_search') }}</a>

            <h3 class="has-text-weight-semibold">{{ $i18n.get('filters') }}</h3>
            <a
                    v-if="!isLoadingFilters &&
                    ((filters.length >= 0 &&
                     isRepositoryLevel) || filters.length > 0)"
                    class="collapse-all is-size-7"
                    @click="collapseAll = !collapseAll">
                {{ collapseAll ? $i18n.get('label_collapse_all') : $i18n.get('label_expand_all') }}
                <b-icon
                        type="is-secondary"
                        size="is-small"
                        :icon=" collapseAll ? 'menu-down' : 'menu-right'" />
            </a>

            <br>
            <br>

            <filters-items-list
                    v-if="!isLoadingFilters &&
                    ((filters.length >= 0 &&
                     isRepositoryLevel) || filters.length > 0)"
                    :filters="filters"
                    :collapsed="collapseAll"
                    :is-repository-level="isRepositoryLevel"/>

            <section
                    v-else
                    class="is-grouped-centered section">
                <div class="content has-text-gray has-text-centered">
                    <p>
                        <b-icon
                                icon="filter"
                                size="is-large"/>
                    </p>
                    <p>{{ $i18n.get('info_there_is_no_filter' ) }}</p>
                    <router-link
                            v-if="!isOnTheme"
                            id="button-create-filter"
                            :to="isRepositoryLevel ? $routerHelper.getNewFilterPath() : $routerHelper.getNewCollectionFilterPath(collectionId)"
                            tag="button"
                            class="button is-secondary is-centered">
                        {{ $i18n.getFrom('filters', 'new_item') }}
                    </router-link>
                </div>
            </section>
        </aside>
        
        <!-- ITEMS LIST AREA (ASIDE THE ASIDE) ------------------------- -->
        <div 
                id="items-list-area"
                class="items-list-area"
                :class="{ 'spaced-to-right': !isFiltersMenuCompressed }">
            <b-loading
                    :is-full-page="false"
                    :active.sync="isLoadingItems"/>

            <!-- SEARCH CONTROL ------------------------- -->
            <div
                    v-if="!openAdvancedSearch"
                    class="search-control">
                <b-loading
                        :is-full-page="false"
                        :active.sync="isLoadingMetadata"/>
                <!-- Item Creation Dropdown, only on Admin -->
                <div 
                        class="search-control-item"
                        v-if="!isOnTheme">
                    <b-dropdown 
                            :mobile-modal="false"
                            id="item-creation-options-dropdown">
                        <button
                                class="button is-secondary"
                                slot="trigger">
                            <span>{{ $i18n.getFrom('items','add_new') }}</span>
                            <b-icon icon="menu-down"/>
                        </button>

                        <b-dropdown-item>
                            <router-link
                                    id="a-create-item"
                                    tag="div"
                                    :to="{ path: $routerHelper.getNewItemPath(collectionId) }">
                                {{ $i18n.get('add_one_item') }}
                            </router-link>
                        </b-dropdown-item>
                        <b-dropdown-item disabled>
                            {{ $i18n.get('add_items_bulk') + ' (Not ready)' }}
                        </b-dropdown-item>
                        <b-dropdown-item disabled>
                            {{ $i18n.get('add_items_external_source') + ' (Not ready)' }}
                        </b-dropdown-item>
                    </b-dropdown>

                </div>
                <!-- Displayed Metadata Dropdown -->
                <div    
                        v-if="!isOnTheme || registeredViewModes[viewMode].dynamic_metadata"
                        class="search-control-item">
                    <b-dropdown
                            ref="displayedMetadataDropdown"
                            :mobile-modal="false"
                            :disabled="totalItems <= 0 || adminViewMode == 'grid'"
                            class="show">
                        <button
                                class="button is-white"
                                slot="trigger">
                            <span>{{ $i18n.get('label_displayed_metadata') }}</span>
                            <b-icon icon="menu-down"/>
                        </button>
                        <div class="metadata-options-container">
                        <b-dropdown-item
                                v-for="(column, index) in localTableMetadata"
                                :key="index"
                                class="control"
                                custom>
                            <b-checkbox
                                    v-model="column.display"
                                    :native-value="column.display">
                                {{ column.name }}
                            </b-checkbox>
                        </b-dropdown-item>   
                        </div>
                        <div class="dropdown-item-apply">
                            <button 
                                    @click="onChangeDisplayedMetadata()"
                                    class="button is-success">
                                {{ $i18n.get('label_apply_changes') }}
                            </button>
                        </div>  
                    </b-dropdown>
                </div>

                <!-- Change OrderBy Select and Order Button-->
                <div class="search-control-item">
                    <b-field>
                        <b-select
                                :disabled="totalItems <= 0"
                                @input="onChangeOrderBy($event)"
                                :placeholder="$i18n.get('label_sorting')">
                            <option
                                    v-for="metadatum in tableMetadata"
                                    v-if="
                                        metadatum.slug === 'creation_date' ||
                                        metadatum.slug === 'author_name' || (
                                            metadatum.id !== undefined &&
                                            metadatum.metadata_type_object && 
                                            metadatum.metadata_type_object.related_mapped_prop !== 'description' &&
                                            metadatum.metadata_type_object.primitive_type !== 'term' &&
                                            metadatum.metadata_type_object.primitive_type !== 'item' &&
                                            metadatum.metadata_type_object.primitive_type !== 'compound'
                                    )"
                                    :value="metadatum"
                                    :key="metadatum.slug">
                                {{ metadatum.name }}
                            </option>
                        </b-select>
                        <button
                                :disabled="totalItems <= 0"
                                class="button is-white is-small"
                                @click="onChangeOrder()">
                            <b-icon :icon="order === 'ASC' ? 'sort-ascending' : 'sort-descending'"/>
                        </button>
                    </b-field>
                </div>

                <!-- View Modes Dropdown -->
                <div 
                        v-if="isOnTheme"
                        class="search-control-item">
                    <b-field>
                        <b-dropdown
                                @change="onChangeViewMode($event)"
                                :mobile-modal="false"
                                position="is-bottom-left"
                                :aria-label="$i18n.get('label_view_mode')">
                            <button 
                                    class="button is-white" 
                                    slot="trigger">
                                <span 
                                        v-if="registeredViewModes[viewMode] != undefined"
                                        v-html="registeredViewModes[viewMode].icon"/>
                                    &nbsp;&nbsp;&nbsp;{{ $i18n.get('label_visualization') }}
                                <b-icon icon="menu-down" />
                            </button>
                            <b-dropdown-item 
                                    v-for="(viewModeOption, index) of enabledViewModes"
                                    :key="index"
                                    :value="viewModeOption"
                                    v-if="registeredViewModes[viewModeOption] != undefined">
                                <span v-html="registeredViewModes[viewModeOption].icon"/>
                                {{ registeredViewModes[viewModeOption].label }}
                            </b-dropdown-item>
                        </b-dropdown>
                    </b-field>
                </div>
                <div
                        v-if="!isOnTheme"
                        class="search-control-item">
                    <b-field>
                        <b-dropdown
                                v-model="adminViewMode"
                                :mobile-modal="false"
                                position="is-bottom-left"
                                :aria-label="$i18n.get('label_view_mode')">
                            <button
                                    class="button is-white"
                                    slot="trigger">
                                <span>
                                    <b-icon
                                            :icon="(adminViewMode == 'table' || adminViewMode == undefined) ?
                                                        'table' : (adminViewMode == 'cards' ?
                                                        'view-list' : 'view-grid')"/>
                                </span>
                                &nbsp;&nbsp;&nbsp;{{ $i18n.get('label_visualization') }}
                                <b-icon icon="menu-down" />
                            </button>
                            <b-dropdown-item :value="'table'">
                                <b-icon icon="table"/>
                                {{ $i18n.get('label_table') }}
                            </b-dropdown-item>
                            <b-dropdown-item :value="'cards'">
                                <b-icon icon="view-list"/>
                                {{ $i18n.get('label_cards') }}
                            </b-dropdown-item>
                            <b-dropdown-item :value="'grid'">
                                <b-icon icon="view-grid"/>
                                {{ $i18n.get('label_grid') }}
                            </b-dropdown-item>
                        </b-dropdown>
                    </b-field>
                </div>
            </div>


            <!-- ADVANCED SEARCH -->
            <div
                    v-if="openAdvancedSearch">
                <div class="columns tnc-advanced-search-close">
                    <div class="column">
                        <button
                                @click="openAdvancedSearch = false"
                                class="button is-white is-pulled-right">
                            <b-icon
                                    size="is-small"
                                    icon="close"/>
                        </button>
                    </div>
                </div>
                <advanced-search
                        :is-repository-level="isRepositoryLevel"
                        :metadata-list="metadata" />
            </div>


            <!-- --------------- -->

            <!-- STATUS TABS, only on Admin -------- -->
            <div 
                    v-if="!isOnTheme && !openAdvancedSearch"
                    class="tabs">
                <ul>
                    <li 
                            @click="onChangeTab('')"
                            :class="{ 'is-active': status == undefined || status == ''}"><a>{{ $i18n.get('label_all_items') }}</a></li>
                    <li 
                            @click="onChangeTab('draft')"
                            :class="{ 'is-active': status == 'draft'}"><a>{{ $i18n.get('label_draft_items') }}</a></li>
                    <li 
                            @click="onChangeTab('trash')"
                            :class="{ 'is-active': status == 'trash'}"><a>{{ $i18n.get('label_trash_items') }}</a></li>
                </ul>
            </div>

            <!-- ITEMS LISTING RESULTS ------------------------- -->
            <div class="above-search-control">

                <div
                        v-if="openAdvancedSearch && advancedSearchResults">
                    <div class="advanced-search-results-title">
                        <h1>{{ $i18n.get('info_search_results') }}</h1>
                        <hr>
                    </div>
                </div>

                <!-- When advanced search -->
                <items-list
                        v-if="!isOnTheme &&
                              !isLoadingItems &&
                              totalItems > 0 &&
                              openAdvancedSearch &&
                              advancedSearchResults"

                        :collection-id="collectionId"
                        :table-metadata="tableMetadata"
                        :items="items"
                        :is-loading="isLoadingItems"
                        :is-on-trash="status == 'trash'"
                        :view-mode="adminViewMode"/>

                <!-- Admin View Modes-->
                <items-list
                        v-else-if="!isOnTheme &&
                              !isLoadingItems && 
                              totalItems > 0 &&
                              !openAdvancedSearch"

                        :collection-id="collectionId"
                        :table-metadata="tableMetadata"
                        :items="items"
                        :is-loading="isLoadingItems"
                        :is-on-trash="status == 'trash'"
                        :view-mode="adminViewMode"/>
                
                <!-- Theme View Modes -->
                <div 
                        v-if="isOnTheme &&
                              !isLoadingItems &&
                              registeredViewModes[viewMode] != undefined &&
                              registeredViewModes[viewMode].type == 'template'"
                        v-html="itemsListTemplate"/>
                <component
                        v-if="isOnTheme && 
                              !isLoadingItems && 
                              registeredViewModes[viewMode] != undefined &&
                              registeredViewModes[viewMode].type == 'component'"
                        :collection-id="collectionId"
                        :displayed-metadata="tableMetadata"
                        :items="items"
                        :is-loading="isLoadingItems"
                        :is="registeredViewModes[viewMode].component"/>     

                <!-- Empty Placeholder (only used in Admin) -->
                <section
                        v-if="!isOnTheme && !isLoadingItems && totalItems <= 0"
                        class="section">
                    <div class="content has-text-grey has-text-centered">
                        <p>
                            <b-icon
                                    icon="inbox"
                                    size="is-large"/>
                        </p>
                        <p v-if="status == undefined || status == ''">{{ hasFiltered ? $i18n.get('info_no_item_found') : $i18n.get('info_no_item_created') }}</p>
                        <p v-if="status == 'draft'">{{ $i18n.get('info_no_item_draft') }}</p>
                        <p v-if="status == 'trash'">{{ $i18n.get('info_no_item_trash') }}</p>

                        <router-link
                                v-if="!hasFiltered && (status == undefined || status == '')"
                                id="button-create-item"
                                tag="button"
                                class="button is-primary"
                                :to="{ path: $routerHelper.getNewItemPath(collectionId) }">
                            {{ $i18n.getFrom('items', 'add_new') }}
                        </router-link>
                    </div>
                </section>

                <!-- Pagination -->

                <!-- When advanced search -->
                <pagination
                        v-if="totalItems > 0 &&
                         (!isOnTheme || registeredViewModes[viewMode].show_pagination) &&
                          advancedSearchResults"/>

                <!-- Regular -->
                <pagination
                        v-else-if="totalItems > 0 &&
                         (!isOnTheme || registeredViewModes[viewMode].show_pagination) &&
                          !openAdvancedSearch"/>
            </div>
        </div>
        
    </div>
</template>

<script>
    import ItemsList from '../../components/lists/items-list.vue';
    import FiltersItemsList from '../../components/search/filters-items-list.vue';
    import Pagination from '../../components/search/pagination.vue'
    import AdvancedSearch from '../../components/advanced-search/advanced-search.vue';
    import { mapActions, mapGetters } from 'vuex';

    export default {
        name: 'ItemsPage',
        data() {
            return {
                isRepositoryLevel: false,
                tableMetadata: [],
                prefTableMetadata: [],
                isLoadingItems: false,
                isLoadingFilters: false,
                isLoadingMetadata: false,
                hasFiltered: false,
                isFiltersMenuCompressed: false,
                collapseAll: true,
                isOnTheme: false,
                futureSearchQuery: '',
                isHeaderShrinked: false,
                localTableMetadata: [],
                registeredViewModes: tainacan_plugin.registered_view_modes,
                adminViewMode: 'table',
                openAdvancedSearch: false,
                advancedSearchResults: false,
            }
        },
        props: {
            collectionId: Number,
            defaultViewMode: String, // Used only on theme
            enabledViewModes: Object // Used only on theme
        },
        computed: {
            items() {
                return this.getItems();
            },
            itemsListTemplate() {
                return this.getItemsListTemplate();
            },
            totalItems() {
                return this.getTotalItems();
            },
            filters() {
                return this.getFilters();
            },
            metadata() {
                return this.getMetadata();
            },
            searchQuery() {
                return this.getSearchQuery();
            },
            status() {
                return this.getStatus();
            },
            viewMode() {
                return this.getViewMode();
            },
            orderBy() {
                return this.getOrderBy();
            },
            order() {
                return this.getOrder();
            }
        },
        components: {
            ItemsList,
            FiltersItemsList,
            Pagination,
            AdvancedSearch,
        },
        watch: {
            tableMetadata() {
                this.localTableMetadata = JSON.parse(JSON.stringify(this.tableMetadata));
            },
            openAdvancedSearch(newValue){
                if(newValue == false){
                    this.$router.push({query: {}});
                    location.reload();
                }
            }
        },
        methods: {
            ...mapGetters('collection', [
                'getItems',
                'getItemsListTemplate'
            ]),
            ...mapActions('metadata', [
                'fetchMetadata'
            ]),
            ...mapGetters('metadata', [
                'getMetadata'
            ]),
            ...mapActions('filter', [
                'fetchFilters'
            ]),
            ...mapGetters('filter', [
                'getFilters'
            ]),
            ...mapGetters('search', [
                'getSearchQuery',
                'getStatus',
                'getOrderBy',
                'getOrder',
                'getViewMode',
                'getTotalItems'
            ]),
            updateSearch() {
                this.$eventBusSearch.setSearchQuery(this.futureSearchQuery);
            },  
            onChangeOrderBy(metadatum) {
                this.$eventBusSearch.setOrderBy(metadatum);
            },
            onChangeOrder() {
                this.order == 'DESC' ? this.$eventBusSearch.setOrder('ASC') : this.$eventBusSearch.setOrder('DESC');
            },
            onChangeTab(status) {
                this.$eventBusSearch.setStatus(status);
            },
            onChangeViewMode(viewMode) {
                this.$eventBusSearch.setViewMode(viewMode);
            },
            onChangeDisplayedMetadata() {
                let fetchOnlyMetadatumIds = [];

                for (let i = 0; i < this.localTableMetadata.length; i++) {

                    this.tableMetadata[i].display = this.localTableMetadata[i].display;
                    if (this.tableMetadata[i].id != undefined) {
                        if (this.tableMetadata[i].display) {
                            fetchOnlyMetadatumIds.push(this.tableMetadata[i].id);
                        }
                    }
                }
                let thumbnailMetadatum = this.localTableMetadata.find(metadatum => metadatum.slug == 'thumbnail');
                let creationDateMetadatum = this.localTableMetadata.find(metadatum => metadatum.slug == 'creation_date');
                let authorNameMetadatum = this.localTableMetadata.find(metadatum => metadatum.slug == 'author_name');

                this.$eventBusSearch.addFetchOnly({
                    '0': thumbnailMetadatum.display ? 'thumbnail' : null,
                    'meta': fetchOnlyMetadatumIds,
                    '1': creationDateMetadatum.display ? 'creation_date' : null,
                    '2': authorNameMetadatum.display ? 'author_name': null
                });
                this.$refs.displayedMetadataDropdown.toggle();
            },
            prepareMetadataAndFilters() {

                this.isLoadingFilters = true;

                this.fetchFilters({
                    collectionId: this.collectionId,
                    isRepositoryLevel: this.isRepositoryLevel,
                    isContextEdit: !this.isOnTheme,
                    includeDisabled: 'no',
                })
                    .then(() => this.isLoadingFilters = false)
                    .catch(() => this.isLoadingFilters = false);

                this.isLoadingMetadata = true;
                // Processing is done inside a local variable
                let metadata = [];
                this.fetchMetadata({
                    collectionId: this.collectionId,
                    isRepositoryLevel: this.isRepositoryLevel,
                    isContextEdit: !this.isOnTheme
                })
                    .then(() => {

                        metadata.push({
                            name: this.$i18n.get('label_thumbnail'),
                            metadatum: 'row_thumbnail',
                            metadata_type: undefined,
                            slug: 'thumbnail',
                            id: undefined,
                            display: true
                        });

                        let fetchOnlyMetadatumIds = [];

                        for (let metadatum of this.metadata) {
                            if (metadatum.display !== 'never') {

                                let display;

                                if (metadatum.display == 'no')
                                    display = false;
                                else if (metadatum.display == 'yes')
                                    display = true;

                                metadata.push(
                                    {
                                        name: metadatum.name,
                                        metadatum: metadatum.description,
                                        slug: metadatum.slug,
                                        metadata_type: metadatum.metadata_type,
                                        metadata_type_object: metadatum.metadata_type_object,
                                        id: metadatum.id,
                                        display: display
                                    }
                                );    
                                if (display)
                                    fetchOnlyMetadatumIds.push(metadatum.id);                      
                            }
                        }

                        metadata.push({
                            name: this.$i18n.get('label_creation_date'),
                            metadatum: 'row_creation',
                            metadata_type: undefined,
                            slug: 'creation_date',
                            id: undefined,
                            display: true
                        });
                        metadata.push({
                            name: this.$i18n.get('label_created_by'),
                            metadatum: 'row_author',
                            metadata_type: undefined,
                            slug: 'author_name',
                            id: undefined,
                            display: true
                        });

                        // this.prefTableMetadata = this.tableMetadata;
                        // this.$userPrefs.get('table_columns_' + this.collectionId)
                        //     .then((value) => {
                        //         this.prefTableMetadata = value;
                        //     })
                        //     .catch((error) => {
                        //         this.$userPrefs.set('table_columns_' + this.collectionId, this.prefTableMetadata, null);
                        //     });
                        this.$eventBusSearch.addFetchOnly({
                            '0': 'thumbnail',
                            'meta': fetchOnlyMetadatumIds,
                            '1': 'creation_date',
                            '2': 'author_name'
                        });
                        this.isLoadingMetadata = false;
                        this.tableMetadata = metadata;
                    })
                    .catch(() => {
                        this.isLoadingMetadata = false;
                    });
            }
        },
        created() {

            this.isOnTheme = (this.$route.name === null);

            this.isRepositoryLevel = (this.collectionId === undefined);

            this.$eventBusSearch.setCollectionId(this.collectionId);
            this.$eventBusSearch.updateStoreFromURL();

            this.$eventBusSearch.$on('isLoadingItems', isLoadingItems => {
                this.isLoadingItems = isLoadingItems;
            });

            this.$eventBusSearch.$on('hasFiltered', hasFiltered => {
                this.hasFiltered = hasFiltered;
            });

            this.$eventBusSearch.$on('advancedSearchResults', advancedSearchResults => {
                this.advancedSearchResults = advancedSearchResults;
            });

            this.$eventBusSearch.$on('hasToPrepareMetadataAndFilters', (to) => {
                /* This condition is to prevent a incorrect fetch by filter or metadata when we come from items
                 * at collection level to items page at repository level
                 */
                if (this.collectionId === to.params.collectionId) {
                    this.prepareMetadataAndFilters();
                }
            });

            this.$eventBusSearch.setViewMode(this.defaultViewMode);

            if(this.$router.query && this.$router.query.metaquery && this.$router.query.metaquery.advancedSearch) {
                this.openAdvancedSearch = this.$router.query.advancedSearch;
            }
        },
        mounted() {
            
            if(this.$router.query && this.$router.query.metaquery && this.$router.query.metaquery.advancedSearch) {
                this.openAdvancedSearch = this.$router.query.advancedSearch;
            }

            this.prepareMetadataAndFilters();
            this.localTableMetadata = JSON.parse(JSON.stringify(this.tableMetadata));

            // Watch Scroll for shrinking header, only on Admin at collection level
            if (!this.isRepositoryLevel && !this.isOnTheme) {
                document.getElementById('items-list-area').addEventListener('scroll', ($event) => {
                    this.isHeaderShrinked = ($event.target.scrollTop > 53);
                    this.$emit('onShrinkHeader', this.isHeaderShrinked); 
                });
            }
        }
    }
</script>

<style lang="scss" scoped>

    @import '../../scss/_variables.scss';

    .advanced-search-results-title {
        padding: 0 $table-side-padding;

        h1 {
            font-size: 20px;
            font-weight: 500;
            color: $tertiary;
            display: inline-block;
        }

        hr{
            margin: 3px 0px 4px 0px;
            height: 1px;
            background-color: $secondary;
        }
    }

    .tnc-advanced-search-close {
        padding-top: 47px;
        padding-right: $page-side-padding;
        padding-left: $page-side-padding;

        .column {
            padding: 0 0.3rem 0.3rem !important;
        }
    }

    .page-container {
        padding: 0px;   
    }

    .filters-menu {
        position: relative;
        width: $filter-menu-width;
        max-width: $filter-menu-width;
        min-height: 100%;
        height: 100%;
        background-color: $tainacan-input-background;
        padding: $page-small-side-padding;
        float: left;
        overflow-y: auto;
        overflow-x: hidden;
        visibility: visible;
        display: block;
        transition: visibility ease 0.5s, display ease 0.5s;
        margin-bottom: -0.1rem;

        h3 {
            font-size: 100%;
            margin-top: 48px;
        }

        .search-area {
            display: flex;
            align-items: center;
            width: 100%;

            .control {
                width: 100%;
                .icon {
                    pointer-events: all;
                    cursor: pointer;
                    color: $tertiary;
                    height: 27px;
                    font-size: 18px !important;
                    height: 2rem !important;
                }
                margin-bottom: 5px;
            }
        }

        .label {
            font-size: 12px;
            font-weight: normal;
        }

        .checkbox {
            margin-bottom: 5px;
            align-items: baseline;
        }

    }
    .filter-menu-compress-button-top-repo {
         top: 123px !important;
    }
    #filter-menu-compress-button {
        position: absolute;
        z-index: 9;
        top: 152px;
        left: 0;
        max-width: 23px;
        height: 21px;
        width: 23px;
        border: none;
        background-color: $primary-lighter;
        color: $tertiary;
        padding: 0;
        border-top-right-radius: 2px;
        border-bottom-right-radius: 2px;
        cursor: pointer;
        transition: top 0.3s;

        .icon {
            margin-top: -1px;
        }
    }

    .spaced-to-right {
        margin-left: $filter-menu-width;
    }

    .search-control {
        min-height: $subheader-height;
        height: auto;
        padding-top: $page-small-top-padding;
        padding-left: $page-side-padding;
        padding-right: $page-side-padding;
        border-bottom: 0.5px solid #ddd;
        display: flex;
        justify-content: space-between;
        flex-wrap: wrap;
    }

    .search-control-item {
        display: inline-block;
        
        .field {
            align-items: center;
        }
        
        .dropdown-menu {
            display: block;

            div.dropdown-content {
                padding: 0;

                .metadata-options-container {
                    max-height: 240px;
                    overflow: auto;
                }
                            
                .dropdown-item-apply {
                    width: 100%;
                    border-top: 1px solid #efefef;
                    padding: 8px 12px;
                    text-align: right;
                }
                .dropdown-item-apply .button {
                    width: 100%;
                }
            }
        }
    }

    .above-search-control {
        margin-bottom: 0;
        margin-top: 0;
        height: calc(100% - 184px);
    }

    .tabs {
        padding-top: 20px;
        margin-bottom: 20px;
        padding-left: $page-side-padding;
        padding-right: $page-side-padding;
    }

    .items-list-area {
        margin-left: 0;
        transition: margin-left ease 0.5s;
        height: 100%;
        overflow: auto;
        position: relative;
    }

    .table-container {
        padding-left: 8.333333%;
        padding-right: 8.333333%;
        min-height: 200px;
        //height: calc(100% - 82px);
    }

    .pagination-area {
        margin-left: $page-side-padding;
        margin-right: $page-side-padding;
    }


</style>


