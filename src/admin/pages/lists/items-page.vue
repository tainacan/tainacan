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
                :style="{ top: searchControlHeight + 'px' }"
                v-show="!isFiltersMenuCompressed && !openAdvancedSearch"
                class="filters-menu tainacan-form">
            <b-loading
                    :is-full-page="false"
                    :active.sync="isLoadingFilters"/>

            <div class="search-area is-hidden-mobile">
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
                    class="is-size-7 is-secondary is-pulled-right is-hidden-mobile">{{ $i18n.get('advanced_search') }}</a>

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

            <!-- FILTERS TAG LIST-->
            <filters-tags-list 
                    class="filter-tags-list"
                    :filters="filters"
                    v-if="hasFiltered">Teste</filters-tags-list>

            <!-- SEARCH CONTROL ------------------------- -->
            <div
                    ref="search-control"
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
                        v-if="!isOnTheme || (registeredViewModes[viewMode] != undefined && registeredViewModes[viewMode].dynamic_metadata)"
                        class="search-control-item">
                    <b-dropdown
                            ref="displayedMetadataDropdown"
                            :mobile-modal="false"
                            :disabled="totalItems <= 0 || adminViewMode == 'grid'|| adminViewMode == 'cards'"
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
                                        metadatum.slug === 'creation_date' || (
                                            metadatum.metadata_type_object && 
                                            metadatum.metadata_type_object.related_mapped_prop == 'title'
                                    )"
                                    :value="metadatum"
                                    :key="metadatum.slug">
                                {{ metadatum.name }}
                            </option>
                            <!-- Once we have sorting by metadata we can use this -->
                            <!-- <option 
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
                            </option> -->
                        </b-select>
                        <button
                                class="button is-white is-small"
                                :disabled="totalItems <= 0 || order == 'DESC'"
                                @click="onChangeOrder()">
                            <span class="icon is-small gray-icon">
                                <i class="mdi mdi-sort-ascending"/>
                            </span>
                        </button>
                        <button
                                :disabled="totalItems <= 0 || order == 'ASC'"
                                class="button is-white is-small"
                                @click="onChangeOrder()">
                            <span class="icon is-small gray-icon">
                                <i class="mdi mdi-sort-descending"/>
                            </span>
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
                                        class="gray-icon view-mode-icon"
                                        v-if="registeredViewModes[viewMode] != undefined"
                                        v-html="registeredViewModes[viewMode].icon"/>
                                    <span class="is-hidden-mobile">&nbsp;&nbsp;&nbsp;{{ $i18n.get('label_visualization') }}</span>
                                <b-icon icon="menu-down" />
                            </button>
                            <b-dropdown-item 
                                    :class="{ 'is-active': viewModeOption == viewMode }"
                                    v-for="(viewModeOption, index) of enabledViewModes"
                                    :key="index"
                                    :value="viewModeOption"
                                    v-if="registeredViewModes[viewModeOption] != undefined">
                                <span 
                                        class="gray-icon"
                                        v-html="registeredViewModes[viewModeOption].icon"/>
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
                                @change="onChangeAdminViewMode($event)"
                                :mobile-modal="false"
                                position="is-bottom-left"
                                :aria-label="$i18n.get('label_view_mode')">
                            <button
                                    class="button is-white"
                                    slot="trigger">
                                <span>
                                        <span class="icon is-small gray-icon">
                                        <i 
                                                :class="{'mdi-view-list' : ( adminViewMode == 'table' || adminViewMode == undefined),
                                                        'mdi-view-module' : adminViewMode == 'cards',
                                                        'mdi-apps' : adminViewMode == 'grid',
                                                        'mdi-view-column' : adminViewMode == 'records'}"
                                                class="mdi"/>
                                    </span>
                                </span>
                                &nbsp;&nbsp;&nbsp;{{ $i18n.get('label_visualization') }}
                                <b-icon icon="menu-down" />
                            </button>
                            <b-dropdown-item 
                                    :class="{ 'is-active': adminViewMode == 'table' }"
                                    :value="'table'">
                                <b-icon 
                                        class="gray-icon" 
                                        icon="view-list"/>
                                {{ $i18n.get('label_table') }}
                            </b-dropdown-item>
                            <b-dropdown-item 
                                    :class="{ 'is-active': adminViewMode == 'cards' }"
                                    :value="'cards'">
                                <b-icon 
                                        class="gray-icon" 
                                        icon="view-module"/>
                                {{ $i18n.get('label_cards') }}
                            </b-dropdown-item>
                            <b-dropdown-item 
                                    :class="{ 'is-active': adminViewMode == 'grid' }"
                                    :value="'grid'">
                                <b-icon 
                                        class="gray-icon" 
                                        icon="apps"/>
                                {{ $i18n.get('label_thumbnails') }}
                            </b-dropdown-item>
                            <b-dropdown-item 
                                    :class="{ 'is-active': adminViewMode == 'records' }"
                                    :value="'records'">
                                <b-icon 
                                        class="gray-icon" 
                                        icon="view-column"/>
                                {{ $i18n.get('label_records') }}
                            </b-dropdown-item>
                        </b-dropdown>
                    </b-field>
                </div>

                <!-- Text simple search (used on mobile, instead of the one from filter list)-->
                <div class="is-hidden-tablet search-control-item">
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
                    <a
                            @click="openAdvancedSearch = !openAdvancedSearch"
                            class="is-size-7 is-secondary is-pulled-right">{{ $i18n.get('advanced_search') }}</a>
                </div>
            </div>

            <!-- ADVANCED SEARCH -->
            <div
                    v-if="openAdvancedSearch">

                <div class="columns tnc-advanced-search-close">
                    <div class="column">
                        <div class="advanced-search-criteria-title">
                            <h1>{{ $i18n.get('info_search_criteria') }}</h1>

                            <div
                                    :style="{'margin-bottom': 'auto'}"
                                    class="field is-grouped is-pulled-right">
                                <p
                                        v-if="advancedSearchResults"
                                        class="control">
                                    <button
                                            @click="advancedSearchResults = !advancedSearchResults"
                                            class="button is-small is-outlined">{{ $i18n.get('edit_search') }}</button>
                                </p>
                                <p
                                        v-if="advancedSearchResults"
                                        class="control">
                                    <button
                                            @click="isDoSearch = !isDoSearch"
                                            class="button is-small is-secondary">{{ $i18n.get('search') }}</button>
                                </p>
                                <p class="control">
                                    <a @click="openAdvancedSearch = false">
                                        {{ $i18n.get('exit') }}
                                    </a>
                                </p>
                            </div>
                            <hr>
                        </div>
                    </div>

                </div>
                <advanced-search
                        :is-repository-level="isRepositoryLevel"
                        :advanced-search-results="advancedSearchResults"
                        :open-form-advanced-search="openFormAdvancedSearch"
                        :is-do-search="isDoSearch"
                        :metadata="metadata"/>
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
                
                <!-- When advanced search -->
                <!-- Theme View Modes -->
                <div 
                        v-if="isOnTheme &&
                              !isLoadingItems &&
                              openAdvancedSearch &&
                              advancedSearchResults &&
                              registeredViewModes[viewMode] != undefined &&
                              registeredViewModes[viewMode].type == 'template'"
                        v-html="itemsListTemplate"/>

                <component
                        v-if="isOnTheme && 
                              !isLoadingItems && 
                              registeredViewModes[viewMode] != undefined &&
                              registeredViewModes[viewMode].type == 'component' &&
                              openAdvancedSearch &&
                              advancedSearchResults"
                        :collection-id="collectionId"
                        :displayed-metadata="tableMetadata"
                        :items="items"
                        :is-loading="isLoadingItems"
                        :is="registeredViewModes[viewMode] != undefined ? registeredViewModes[viewMode].component : ''"/> 
                
                <!-- Regular -->
                <!-- Theme View Modes -->
                <div 
                        v-if="isOnTheme &&
                              !isLoadingItems &&
                              !openAdvancedSearch &&
                              registeredViewModes[viewMode] != undefined &&
                              registeredViewModes[viewMode].type == 'template'"
                        v-html="itemsListTemplate"/>

                <component
                        v-else-if="isOnTheme && 
                              !isLoadingItems && 
                              registeredViewModes[viewMode] != undefined &&
                              registeredViewModes[viewMode].type == 'component' &&
                              !openAdvancedSearch"
                        :collection-id="collectionId"
                        :displayed-metadata="tableMetadata"
                        :items="items"
                        :is-loading="isLoadingItems"
                        :is="registeredViewModes[viewMode] != undefined ? registeredViewModes[viewMode].component : ''"/>     

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
                         (!isOnTheme || (registeredViewModes[viewMode] != undefined && registeredViewModes[viewMode].show_pagination)) &&
                          advancedSearchResults"/>

                <!-- Regular -->
                <pagination
                        v-else-if="totalItems > 0 &&
                         (!isOnTheme || (registeredViewModes[viewMode] != undefined && registeredViewModes[viewMode].show_pagination)) &&
                          !openAdvancedSearch"/>
            </div>
        </div>
        
    </div>
</template>

<script>
    import ItemsList from '../../components/lists/items-list.vue';
    import FiltersTagsList from '../../components/search/filters-tags-list.vue';
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
                openAdvancedSearch: false,
                openFormAdvancedSearch: false,
                advancedSearchResults: false,
                isDoSearch: false,
                searchControlHeight: 0
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
            adminViewMode() {
                return this.getAdminViewMode();
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
            FiltersTagsList,
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
                    this.$eventBusSearch.$emit('closeAdvancedSearch');
                    this.advancedSearchResults = false;
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
                'getTotalItems',
                'getAdminViewMode'
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
            onChangeAdminViewMode(adminViewMode) {
                this.$eventBusSearch.setAdminViewMode(adminViewMode);
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

                // Updates Search
                this.$eventBusSearch.addFetchOnly({
                    '0': thumbnailMetadatum.display ? 'thumbnail' : null,
                    'meta': fetchOnlyMetadatumIds,
                    '1': creationDateMetadatum.display ? 'creation_date' : null,
                    '2': authorNameMetadatum.display ? 'author_name': null
                });

                // Closes dropdown
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
                    isContextEdit: false
                })
                    .then(() => {

                        // Loads user prefs object as we'll need to check if there's something configured by user 
                        let prefsFetchOnly = !this.isRepositoryLevel ? 'fetch_only_' + this.collectionId : 'fetch_only';
                        let prefsFetchOnlyObject = this.$userPrefs.get(prefsFetchOnly); 
                        let thumbnailMetadatumDisplay = prefsFetchOnlyObject != undefined ? (prefsFetchOnlyObject['0'] != null) : true;

                        metadata.push({
                            name: this.$i18n.get('label_thumbnail'),
                            metadatum: 'row_thumbnail',
                            metadata_type: undefined,
                            slug: 'thumbnail',
                            id: undefined,
                            display: thumbnailMetadatumDisplay
                        });

                        let fetchOnlyMetadatumIds = [];

                        for (let metadatum of this.metadata) {
                            if (metadatum.display !== 'never') {

                                let display;

                                // Deciding display based on collection settings
                                if (metadatum.display == 'no')
                                    display = false;
                                else if (metadatum.display == 'yes')
                                    display = true;

                                // // Deciding display based on user prefs
                                // if (prefsFetchOnlyObject != undefined && 
                                //     prefsFetchOnlyObject.meta != undefined) {
                                //     let index = prefsFetchOnlyObject.meta.findIndex(metadatumId => metadatumId == metadatum.id);
                                //     if (index >= 0)
                                //         display = true;
                                //     else
                                //         display = false;
                                // }

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

                        let creationDateMetadatumDisplay = prefsFetchOnlyObject != undefined ? (prefsFetchOnlyObject['1'] != null) : true;
                        let authorNameMetadatumDisplay = prefsFetchOnlyObject != undefined ? (prefsFetchOnlyObject['2'] != null) : true;

                        metadata.push({
                            name: this.$i18n.get('label_creation_date'),
                            metadatum: 'row_creation',
                            metadata_type: undefined,
                            slug: 'creation_date',
                            id: undefined,
                            display: creationDateMetadatumDisplay
                        });
                        metadata.push({
                            name: this.$i18n.get('label_created_by'),
                            metadatum: 'row_author',
                            metadata_type: undefined,
                            slug: 'author_name',
                            id: undefined,
                            display: authorNameMetadatumDisplay
                        });
                        
                        this.$eventBusSearch.addFetchOnly({
                            '0': (thumbnailMetadatumDisplay ? 'thumbnail' : null),
                            'meta': fetchOnlyMetadatumIds,
                            '1': (creationDateMetadatumDisplay ? 'creation_date' : null),
                            '2': (authorNameMetadatumDisplay ? 'author_name' : null)
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

                if (this.isOnTheme || this.collectionId === to.params.collectionId) {
                    this.prepareMetadataAndFilters();
                }
            });

            this.$eventBusSearch.setViewMode(this.defaultViewMode);

            if(this.$route.query && this.$route.query.advancedSearch) {
                this.openAdvancedSearch = this.$route.query.advancedSearch;
            }

            this.$root.$on('openAdvancedSearch', (openAdvancedSearch) => {
                this.openAdvancedSearch = openAdvancedSearch;
            });

        },
        mounted() {
            
            this.prepareMetadataAndFilters();
            this.localTableMetadata = JSON.parse(JSON.stringify(this.tableMetadata));

            // Setting initial view mode on Theme
            if (this.isOnTheme) {
                let prefsViewMode = !this.isRepositoryLevel ? 'view_mode_' + this.collectionId : 'view_mode';
                if (this.$userPrefs.get(prefsViewMode) == undefined)
                    this.$eventBusSearch.setInitialViewMode(this.defaultViewMode);
                else 
                    this.$eventBusSearch.setInitialViewMode(this.$userPrefs.get(prefsViewMode));
            } else {
                let prefsAdminViewMode = !this.isRepositoryLevel ? 'admin_view_mode_' + this.collectionId : 'admin_view_mode';
                if (this.$userPrefs.get(prefsAdminViewMode) == undefined)
                    this.$eventBusSearch.setInitialAdminViewMode('table');
                else 
                    this.$eventBusSearch.setInitialAdminViewMode(this.$userPrefs.get(prefsAdminViewMode));
            }
            
            // Watch Scroll for shrinking header, only on Admin at collection level
            if (!this.isRepositoryLevel && !this.isOnTheme) {
                document.getElementById('items-list-area').addEventListener('scroll', ($event) => {
                    this.isHeaderShrinked = ($event.target.scrollTop > 53);
                    this.$emit('onShrinkHeader', this.isHeaderShrinked); 
                });
            }

            // Watches window resize to adjust filter's height on mobile 
            this.$nextTick(() => {
                this.searchControlHeight = this.$refs['search-control'].clientHeight;
                window.addEventListener('resize', () => {
                    this.searchControlHeight = this.$refs['search-control'].clientHeight;
                });
            })
        }
    }
</script>

<style lang="scss" scoped>

    @import '../../scss/_variables.scss';

    .advanced-search-criteria-title {
        padding: 0;

        h1 {
            font-size: 20px;
            font-weight: 500;
            color: $tertiary;
            display: inline-block;
        }

        hr {
            margin: 3px 0px 4px 0px;
            height: 1px;
            background-color: $secondary;
        }
    }

    .advanced-search-results-title {
        padding: 0 $table-side-padding;

        h1 {
            font-size: 20px;
            font-weight: 500;
            color: $tertiary;
            display: inline-block;
        }

        hr {
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
        z-index: 10;
        background-color: white;
        width: $filter-menu-width;
        min-width: 180px;
        min-height: 100%;
        height: 100%;
        background-color: white;
        padding: $page-small-side-padding;
        float: left;
        overflow-y: auto;
        overflow-x: hidden;
        visibility: visible;
        display: block;
        transition: visibility ease 0.5s, display ease 0.5s;

        @media screen and (max-width: 768px) {
            width: 100%;
            padding: $page-small-side-padding !important;
            
            h3 {
                margin-top: 0 !important;
            }
        }
        @media screen and (min-width: 769px) {
            top: 0px !important;
        }

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
        z-index: 99;
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

        @media screen and (max-width: 768px) {
            margin-left: 0px !important;
        }
    }

    .search-control {
        min-height: $subheader-height;
        height: auto;
        padding-top: $page-small-top-padding;
        padding-left: $page-side-padding;
        padding-right: $page-side-padding;
        display: flex;
        justify-content: space-between;
        flex-wrap: wrap;
    }

    .search-control-item {
        display: inline-block;

        .button {
            align-items: flex-start;
        }
        
        .field {
            align-items: center;
        }

        .gray-icon, .gray-icon .icon {
            color: $tainacan-placeholder-color !important;
            padding-right: 10px;
        }
        .gray-icon .icon i::before, .gray-icon i::before {
            font-size: 21px !important;
        }
        
        .view-mode-icon {
            margin-right: 4px !important;
            margin-top: 1px;
        }

        .dropdown-menu {
            display: block;

            div.dropdown-content {
                padding: 0;

                .metadata-options-container {
                    max-height: 240px;
                    overflow: auto;
                }
                .dropdown-item {
                    padding: 0.25rem 1.0rem 0.25rem 0.75rem;
                }
                .dropdown-item span{
                    vertical-align: sub;
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
        padding-left: 4.166666667%;
        padding-right: 4.166666667%;
        min-height: 200px;
        //height: calc(100% - 82px);
    }

    .pagination-area {
        margin-left: $page-side-padding;
        margin-right: $page-side-padding;
    }


</style>


