<template>
    <div 
            :class="{ 
                'repository-level-page': isRepositoryLevel,
                'is-filters-menu-open': isFiltersModalActive && !openAdvancedSearch
            }"
            aria-live="polite">

        <!-- PAGE TITLE --------------------- -->
        <tainacan-title
                v-if="!$route.query.iframemode && !$route.query.readmode && !openAdvancedSearch" 
                :bread-crumb-items="[{ path: '', label: this.$i18n.get('items') }]"/>
        <div 
                v-else-if="openAdvancedSearch"
                class="tnc-advanced-search-close"> 
            <div class="advanced-search-criteria-title">
                <div class="is-flex">
                    <h1>{{ $i18n.get('info_search_criteria') }}</h1>
                    <div
                            :style="{'margin-bottom': 'auto'}"
                            class="field is-grouped">
                        <a 
                                class="back-link"
                                @click="openAdvancedSearch = false">
                            {{ $i18n.get('back') }}
                        </a>
                    </div>
                </div>
                <hr>
            </div>
        </div>

        <!-- SEARCH CONTROL ------------------------- -->
        <h3 
                id="search-control-landmark"
                class="sr-only">
            {{ $i18n.get('label_sort_visualization') }}
        </h3>
        <div
                aria-labelledby="search-control-landmark"
                role="region"
                ref="search-control"
                v-if="((openAdvancedSearch && advancedSearchResults) || !openAdvancedSearch)"
                class="search-control">

            <!-- <b-loading
                    :is-full-page="false"
                    :active.sync="isLoadingMetadata"/> -->
                        <!-- Button for hiding filters -->
            <button 
                    aria-controls="filters-modal"
                    :aria-expanded="isFiltersModalActive"
                    v-if="!openAdvancedSearch && !(registeredViewModes[viewMode] != undefined && registeredViewModes[viewMode].full_screen)"
                    id="filter-menu-compress-button"
                    :aria-label="!isFiltersModalActive ? $i18n.get('label_show_filters') : $i18n.get('label_hide_filters')"
                    @click="isFiltersModalActive = !isFiltersModalActive"
                    v-tooltip="{
                        delay: {
                            show: 500,
                            hide: 300,
                        },
                        content: !isFiltersModalActive ? $i18n.get('label_show_filters') : $i18n.get('label_hide_filters'),
                        autoHide: false,
                        placement: 'auto-start',
                        classes: ['tooltip', isRepositoryLevel ? 'repository-tooltip' : '']
                    }">
                <span class="icon">
                    <i 
                            :class="{ 'tainacan-icon-arrowleft' : isFiltersModalActive, 'tainacan-icon-arrowright' : !isFiltersModalActive }"
                            class="tainacan-icon tainacan-icon-1-25em"/>
                </span>
                <span class="text is-hidden-tablet">{{ $i18n.get('filters') }}</span>
            </button>

            <!-- Text simple search -->
            <div class="search-control-item">
                <div 
                        role="search"
                        class="search-area">
                    <div class="control has-icons-right  is-small is-clearfix">
                        <input
                                class="input is-small"
                                :placeholder="$i18n.get('instruction_search')"
                                type="search"
                                :aria-label="$i18n.get('instruction_search') + ' ' + $i18n.get('items')"
                                :value="searchQuery"
                                @input="futureSearchQuery = $event.target.value"
                                @keyup.enter="updateSearch()">
                        <span 
                                aria-controls="items-list-results"
                                @click="updateSearch()"
                                class="icon is-right">
                            <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-search"/>
                        </span>
                    </div>
                    <a
                            @click="openAdvancedSearch = !openAdvancedSearch"
                            style="font-size: 0.75em;"
                            class="has-text-secondary is-pulled-right">{{ $i18n.get('advanced_search') }}</a>
                </div>
            </div>

            <!-- Item Creation Dropdown, only on Admin -->
            <div 
                    class="search-control-item"
                    v-if="!$route.query.iframemode &&
                            !openAdvancedSearch &&
                            collection && 
                            collection.current_user_can_edit_items">
                <b-dropdown
                        :mobile-modal="true"
                        id="item-creation-options-dropdown"
                        aria-role="list"
                        trap-focus>
                    <button
                            class="button is-secondary"
                            slot="trigger">
                        <span>{{ $i18n.getFrom('items','add_new') }}</span>
                        <span class="icon">
                            <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-arrowdown" />
                        </span>
                    </button>

                    <b-dropdown-item 
                            v-if="!isRepositoryLevel"
                            aria-role="listitem">
                        <router-link
                                id="a-create-item"
                                tag="div"
                                :to="{ path: $routerHelper.getNewItemPath(collectionId) }">
                            {{ $i18n.get('add_one_item') }}
                        </router-link>
                    </b-dropdown-item>
                    <b-dropdown-item 
                            v-if="isRepositoryLevel"
                            aria-role="listitem">
                        <div
                                id="a-create-item"
                                tag="div"
                                @click="onOpenCollectionsModal">
                            {{ $i18n.get('add_one_item') }}
                        </div>
                    </b-dropdown-item>
                    <b-dropdown-item 
                            v-if="!isRepositoryLevel"
                            aria-role="listitem">
                        <router-link
                                id="a-item-add-bulk"
                                tag="div"
                                :to="{ path: $routerHelper.getNewItemBulkAddPath(collectionId) }">
                            {{ $i18n.get('add_items_bulk') }}
                            <br> 
                            <small class="is-small">{{ $i18n.get('info_bulk_add_items') }}</small>
                        </router-link>
                    </b-dropdown-item>
                    <b-dropdown-item aria-role="listitem">
                        <div
                                id="a-import-items"
                                tag="div"
                                @click="onOpenImportersModal">
                            {{ $i18n.get('label_import_items') }}
                            <br>
                            <small class="is-small">{{ $i18n.get('info_import_items') }}</small>
                        </div>
                    </b-dropdown-item>
                </b-dropdown>
            </div>

            <!-- Displayed Metadata Dropdown -->
            <div class="search-control-item">
                <b-dropdown
                        v-tooltip="{
                            delay: {
                                show: 500,
                                hide: 300,
                            },
                            content: (totalItems <= 0 || adminViewMode == 'grid'|| adminViewMode == 'cards' || adminViewMode == 'masonry') ? (adminViewMode == 'grid'|| adminViewMode == 'cards' || adminViewMode == 'masonry') ? $i18n.get('info_current_view_mode_metadata_not_allowed') : $i18n.get('info_cant_select_metadata_without_items') : '',
                            autoHide: false,
                            placement: 'auto-start',
                            classes: ['tooltip', isRepositoryLevel ? 'repository-tooltip' : '']
                        }" 
                        ref="displayedMetadataDropdown"
                        :mobile-modal="true"
                        :disabled="totalItems <= 0 || adminViewMode == 'grid'|| adminViewMode == 'cards' || adminViewMode == 'masonry'"
                        class="show metadata-options-dropdown"
                        aria-role="list"
                        trap-focus>
                    <button
                            :aria-label="$i18n.get('label_displayed_metadata')"
                            class="button is-white"
                            slot="trigger">
                        <span class="is-hidden-touch is-hidden-desktop-only">{{ $i18n.get('label_displayed_metadata') }}</span>
                        <span class="is-hidden-widescreen">{{ $i18n.get('metadata') }}</span>
                        <span class="icon">
                            <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-arrowdown" />
                        </span>
                    </button>
                    <div class="metadata-options-container">
                        <b-dropdown-item
                                v-for="(column, index) in localDisplayedMetadata"
                                :key="index"
                                class="control"
                                custom
                                aria-role="listitem">
                            <b-checkbox
                                    v-model="column.display"
                                    :native-value="column.display">
                                {{ column.name }}
                            </b-checkbox>
                        </b-dropdown-item>   
                    </div>
                    <div class="dropdown-item-apply">
                        <button 
                                aria-controls="items-list-results"
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
                    <label class="label">{{ $i18n.get('label_sort') }}</label>
                    <b-dropdown
                            :mobile-modal="true"
                            @input="onChangeOrder()"
                            aria-role="list"
                            trap-focus>
                        <button
                                :aria-label="$i18n.get('label_sorting_direction')"
                                class="button is-white"
                                slot="trigger">
                            <span class="icon is-small gray-icon">
                                <i 
                                        :class="order == 'DESC' ? 'tainacan-icon-sortdescending' : 'tainacan-icon-sortascending'"
                                        class="tainacan-icon"/>
                            </span>
                            <span class="icon">
                                <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-arrowdown" />
                            </span>
                        </button>
                        <b-dropdown-item
                                aria-controls="items-list-results"
                                role="button"
                                :class="{ 'is-active': order == 'DESC' }"
                                :value="'DESC'"
                                aria-role="listitem">
                            <span class="icon gray-icon">
                                <i class="tainacan-icon tainacan-icon-18px tainacan-icon-sortdescending"/>
                            </span>
                            <span>{{ $i18n.get('label_descending') }}</span>
                        </b-dropdown-item>
                        <b-dropdown-item
                                aria-controls="items-list-results"
                                role="button"
                                :class="{ 'is-active': order == 'ASC' }"
                                :value="'ASC'"
                                aria-role="listitem">
                            <span class="icon gray-icon">
                                <i class="tainacan-icon tainacan-icon-18px tainacan-icon-sortascending"/>
                            </span>
                            <span>{{ $i18n.get('label_ascending') }}</span>
                        </b-dropdown-item>
                    </b-dropdown>
                    <span
                            class="label"
                            style="padding-left: 0.65em;">
                        {{ $i18n.get('info_by_inner') }}
                    </span>
                    <b-dropdown
                            :mobile-modal="true"
                            @input="onChangeOrderBy($event)"
                            aria-role="list"
                            trap-focus>
                        <button
                                :aria-label="$i18n.get('label_sorting')"
                                class="button is-white"
                                slot="trigger">
                            <span>{{ orderByName }}</span>
                            <span class="icon">
                                <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-arrowdown" />
                            </span>
                        </button>
                        <b-dropdown-item
                                aria-controls="items-list-results"
                                role="button"
                                :class="{ 'is-active': (orderBy != 'meta_value' && orderBy != 'meta_value_num' && orderBy == metadatum.slug) || ((orderBy == 'meta_value' || orderBy == 'meta_value_num') && metaKey == metadatum.id) }"
                                v-for="metadatum of sortingMetadata"
                                v-if="metadatum != undefined"
                                :value="metadatum"
                                :key="metadatum.slug"
                                aria-role="listitem">
                            {{ metadatum.name }}
                        </b-dropdown-item>
                    </b-dropdown>
                </b-field>
            </div>

            <div class="search-control-item">
                <b-field>
                    <label 
                            class="label is-hidden-touch is-hidden-desktop-only"
                            style="margin-right: -10px;">
                        {{ $i18n.get('label_visualization') + ':&nbsp; ' }}
                    </label>
                    <label 
                            class="label is-hidden-widescreen is-hidden-mobile"
                            style="margin-right: -10px;">
                        {{ $i18n.get('label_view_on') + ':&nbsp; ' }}
                    </label>
                    <b-dropdown
                            @change="onChangeAdminViewMode($event)"
                            :mobile-modal="true"
                            position="is-bottom-left"
                            :aria-label="$i18n.get('label_view_mode')"
                            aria-role="list"
                            trap-focus>
                        <button
                                :aria-label="$i18n.get('label_view_mode')"
                                class="button is-white"
                                slot="trigger">
                            <span>
                                <span class="view-mode-icon icon is-small gray-icon">
                                    <i 
                                            :class="{'tainacan-icon-viewtable' : ( adminViewMode == 'table' || adminViewMode == undefined),
                                                    'tainacan-icon-viewcards' : adminViewMode == 'cards',
                                                    'tainacan-icon-viewminiature' : adminViewMode == 'grid',
                                                    'tainacan-icon-viewrecords' : adminViewMode == 'records',
                                                    'tainacan-icon-viewmasonry' : adminViewMode == 'masonry'}"
                                            class="tainacan-icon tainacan-icon-1-25em"/>
                                </span>
                            </span>
                            &nbsp;&nbsp;&nbsp;{{ adminViewMode != undefined ? $i18n.get('label_' + adminViewMode) : $i18n.get('label_table') }}
                            <span class="icon">
                                <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-arrowdown" />
                            </span>
                        </button>
                        <b-dropdown-item 
                                aria-controls="items-list-results"
                                role="button"
                                :class="{ 'is-active': adminViewMode == 'table' }"
                                :value="'table'"
                                aria-role="listitem">
                            <span class="icon gray-icon">
                                <i class="tainacan-icon tainacan-icon-viewtable"/>
                            </span>
                            <span>{{ $i18n.get('label_table') }}</span>
                        </b-dropdown-item>
                        <b-dropdown-item 
                                aria-controls="items-list-results"
                                role="button"
                                :class="{ 'is-active': adminViewMode == 'cards' }"
                                :value="'cards'"
                                aria-role="listitem">
                            <span class="icon gray-icon">
                                <i class="tainacan-icon tainacan-icon-viewcards"/>
                            </span>
                            <span>{{ $i18n.get('label_cards') }}</span>
                        </b-dropdown-item>
                        <b-dropdown-item 
                                aria-controls="items-list-results"
                                role="button"
                                :class="{ 'is-active': adminViewMode == 'grid' }"
                                :value="'grid'"
                                aria-role="listitem">
                            <span class="icon gray-icon">
                                <i class="tainacan-icon tainacan-icon-viewminiature"/>
                            </span>
                            <span>{{ $i18n.get('label_thumbnails') }}</span>
                        </b-dropdown-item>
                        <b-dropdown-item 
                                aria-controls="items-list-results"
                                role="button"
                                :class="{ 'is-active': adminViewMode == 'records' }"
                                :value="'records'"
                                aria-role="listitem">
                            <span class="icon gray-icon">
                                <i class="tainacan-icon tainacan-icon-viewrecords"/>
                            </span>
                            <span>{{ $i18n.get('label_records') }}</span>
                        </b-dropdown-item>
                        <b-dropdown-item 
                                aria-controls="items-list-results"
                                role="button"
                                :class="{ 'is-active': adminViewMode == 'masonry' }"
                                :value="'masonry'"
                                aria-role="listitem">
                            <span class="icon gray-icon">
                                <i class="tainacan-icon tainacan-icon-viewmasonry"/>
                            </span>
                            <span>{{ $i18n.get('label_masonry') }}</span>
                        </b-dropdown-item>
                    </b-dropdown>
                </b-field>
            </div>

            <!-- Exposers or alternative links modal button -->
            <div 
                    v-if="!$route.query.iframemode"
                    class="search-control-item">
                <button 
                        class="button is-white"
                        :aria-label="$i18n.get('label_view_as')"
                        :disabled="totalItems == undefined || totalItems <= 0"
                        @click="openExposersModal()">
                    <span class="gray-icon">
                            <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-url"/>
                    </span>
                    <span class="is-hidden-touch is-hidden-desktop-only">{{ $i18n.get('label_view_as') }}</span>
                </button>
            </div>

        </div>

         <!-- SIDEBAR WITH FILTERS -->
        <b-modal
                role="region"
                aria-labelledby="filters-label-landmark"
                id="filters-modal"     
                ref="filters-modal"       
                :active.sync="isFiltersModalActive"
                :width="736"
                animation="slide-menu"
                trap-focus
                aria-modal
                aria-role="dialog"
                custom-class="tainacan-form filters-menu">
            <filters-items-list
                    autofocus="true"
                    tabindex="-1"
                    aria-modal
                    role="dialog"
                    id="filters-items-list"
                    :collection-id="collectionId"
                    :is-repository-level="isRepositoryLevel"/>
        </b-modal>

        <!-- ITEMS LIST AREA (ASIDE THE ASIDE) ------------------------- -->
        <div 
                id="items-list-area"
                class="items-list-area">

            <!-- ADVANCED SEARCH -->
            <div
                    id="advanced-search-container"
                    role="search"
                    v-if="openAdvancedSearch">
                
                <advanced-search
                        :collection-id="collectionId"
                        :is-repository-level="isRepositoryLevel"
                        :advanced-search-results="advancedSearchResults"
                        :open-form-advanced-search="openFormAdvancedSearch"
                        :is-do-search="isDoSearch"/>

                <div class="advanced-search-form-submit">
                    <p
                            v-if="advancedSearchResults"
                            class="control">
                        <button
                                aria-controls="items-list-results"
                                @click="advancedSearchResults = !advancedSearchResults"
                                class="button is-outlined">{{ $i18n.get('edit_search') }}</button>
                    </p>
                    <p
                            v-if="advancedSearchResults"
                            class="control">
                        <button
                                aria-controls="items-list-results"
                                @click="isDoSearch = !isDoSearch"
                                class="button is-success">{{ $i18n.get('search') }}</button>
                    </p>
                </div>
            </div>

            <!-- STATUS TABS, only on Admin -------- -->
            <items-status-tabs 
                    v-if="!openAdvancedSearch"
                    :is-repository-level="isRepositoryLevel"/>

            <!-- FILTERS TAG LIST-->
            <filters-tags-list 
                    class="filter-tags-list"
                    :filters="filters"
                    v-if="hasFiltered && !openAdvancedSearch" />
            
            <!-- ITEMS LISTING RESULTS ------------------------- -->
            <div 
                    id="items-list-results"
                    :aria-busy="isLoadingItems"
                    aria-labelledby="items-list-landmark"
                    role="region"
                    class="above-search-control">

                <h3 
                        id="items-list-landmark"
                        class="sr-only">
                    {{ $i18n.get('label_items_list') }}
                </h3>

                <div 
                        v-show="(showLoading && 
                                !(registeredViewModes[viewMode] != undefined && registeredViewModes[viewMode].implements_skeleton == true))"
                        class="loading-container">

                    <!--  Default loading, to be used view modes without any skeleton-->
                    <b-loading
                            v-if="!(registeredViewModes[viewMode] != undefined && registeredViewModes[viewMode].skeleton_template != undefined)" 
                            :is-full-page="false"
                            :active="showLoading"/>

                    <!-- Custom skeleton templates used by some view modes -->
                    <div
                            v-if="(registeredViewModes[viewMode] != undefined && registeredViewModes[viewMode].skeleton_template != undefined)"
                            v-html="registeredViewModes[viewMode].skeleton_template"/>
                </div>  

               <!-- Alert if custom metada is being used for sorting -->
                <div 
                        v-if="hasAnOpenAlert &&
                            isSortingByCustomMetadata &&
                            !showLoading &&
                            ((openAdvancedSearch && advancedSearchResults) || !openAdvancedSearch)"
                        class="metadata-alert">
                    <p class="text">
                        {{ 
                            totalItems > 0 ? 
                                $i18n.getWithVariables('info_sorting_by_metadata_value_%s', [orderByName]) :
                                $i18n.getWithVariables('info_sorting_by_metadata_value_%s_empty_list', [orderByName])
                        }}
                    </p> 
                    <div>
                        <button
                                @click="openMetatadaSortingWarningDialog({ offerCheckbox: false })"
                                class="button">
                            {{ $i18n.get('label_view_more') }}
                        </button>
                        <button 
                                @click="hasAnOpenAlert = false"
                                class="button icon">
                            <i class="tainacan-icon tainacan-icon-close"/>
                        </button>
                    </div>
                </div>

                <!-- Admin View Modes-->
                <items-list
                        v-if="!showLoading &&
                              totalItems > 0 &&
                              ((openAdvancedSearch && advancedSearchResults) || !openAdvancedSearch)"
                        :collection-id="collectionId"
                        :table-metadata="displayedMetadata"
                        :items="items"
                        :total-items="totalItems"
                        :is-loading="showLoading"
                        :is-on-trash="status == 'trash'"
                        :view-mode="adminViewMode"
                        @updateIsLoading="newIsLoading => isLoadingItems = newIsLoading"/>

                <!-- Empty Placeholder (only used in Admin) -->
                <section
                        v-if="!isLoadingItems && totalItems == 0"
                        class="section">
                    <div class="content has-text-grey has-text-centered">
                        <p>
                            <span class="icon is-large">
                                <i class="tainacan-icon tainacan-icon-30px tainacan-icon-items" />
                            </span>
                        </p>
                        <p v-if="status == undefined || status == ''">{{ hasFiltered ? $i18n.get('info_no_item_found_filter') : (isSortingByCustomMetadata ? $i18n.get('info_no_item_found') : $i18n.get('info_no_item_created')) }}</p>
                        <p
                                v-for="(statusOption, index) of $statusHelper.getStatuses()"
                                :key="index"
                                v-if="status == statusOption.slug">
                            {{ $i18n.get('info_no_items_' + statusOption.slug) }}
                        </p>

                        <router-link
                                v-if="!isRepositoryLevel && !isSortingByCustomMetadata && !hasFiltered && (status == undefined || status == '') && !$route.query.iframemode"
                                id="button-create-item"
                                tag="button"
                                class="button is-secondary"
                                :to="{ path: $routerHelper.getNewItemPath(collectionId) }">
                            {{ $i18n.getFrom('items', 'add_new') }}
                        </router-link> 
                        <button
                                v-else-if="isRepositoryLevel && !isSortingByCustomMetadata && !hasFiltered && (status == undefined || status == '') && !$route.query.iframemode"
                                id="button-create-item"
                                class="button is-secondary"
                                @click="onOpenCollectionsModal">
                            {{ $i18n.get('add_one_item') }}
                        </button>
                    </div>
                </section>

                <!-- Pagination -->
                <pagination
                        :is-sorting-by-custom-metadata="isSortingByCustomMetadata"
                        v-if="totalItems > 0 && (advancedSearchResults || !openAdvancedSearch)"/>
            </div>
        </div>
    </div>
</template>

<script>
    import ItemsList from '../../components/lists/items-list.vue';
    import FiltersTagsList from '../../components/search/filters-tags-list.vue';
    import FiltersItemsList from '../../components/search/filters-items-list.vue';
    import ItemsStatusTabs from '../../components/other/items-status-tabs.vue';
    import Pagination from '../../components/search/pagination.vue'
    import AdvancedSearch from '../../components/search/advanced-search.vue';
    import AvailableImportersModal from '../../components/modals/available-importers-modal.vue';
    import ExposersModal from '../../components/modals/exposers-modal.vue';
    import CollectionsModal from '../../components/modals/collections-modal.vue';
    import CustomDialog from '../../components/other/custom-dialog.vue';
    import { mapActions, mapGetters } from 'vuex';

    export default {
        name: 'ItemsPage',
        components: {
            ItemsList,
            FiltersTagsList,
            FiltersItemsList,
            ItemsStatusTabs,
            Pagination,
            AdvancedSearch,
            ExposersModal
        },
        props: {
            collectionId: Number
        },
        data() {
            return {
                isRepositoryLevel: false,
                displayedMetadata: [],
                prefDisplayedMetadata: [],
                isLoadingItems: false,
                isLoadingMetadata: false,
                hasFiltered: false,
                futureSearchQuery: '',
                localDisplayedMetadata: [],
                registeredViewModes: tainacan_plugin.registered_view_modes,
                openAdvancedSearch: false,
                openFormAdvancedSearch: false,
                advancedSearchResults: false,
                isDoSearch: false,
                sortingMetadata: [],
                isFiltersModalActive: false,
                hasAnOpenModal: false,
                hasAnOpenAlert: true,
                metadataSearchCancel: undefined
            }
        },
        computed: {
            isSortingByCustomMetadata() {
                return (this.orderBy != undefined && this.orderBy != '' && this.orderBy != 'title' && this.orderBy != 'date');
            },
            items() {
                return this.getItems();
            },
            itemsListTemplate() {
                return this.getItemsListTemplate();
            },
            totalItems() {
                this.updateCollectionInfo();
                return this.getTotalItems();
            },
            metadata() {
                return this.getMetadata();
            },
            collection() {
                return this.getCollection();
            },
            searchQuery() {
                return this.getSearchQuery();
            },
            status() {
                return this.getStatus();
            },
            adminViewMode() {
                return this.getAdminViewMode();
            },
            orderBy() {
                return this.getOrderBy();
            },
            order() {
                return this.getOrder();
            },
            showLoading() {
                return this.isLoadingItems || this.isLoadingMetadata;
            },
            metaKey() {
                return this.getMetaKey();
            },
            orderByName() {

                if (this.getOrderByName() != null && this.getOrderByName() != undefined && this.getOrderByName() != '') {
                    return this.getOrderByName();
                } else {

                    for (let metadatum of this.sortingMetadata) {

                        if (
                            ((this.orderBy != 'meta_value' && this.orderBy != 'meta_value_num' && metadatum.slug == 'creation_date' && (!metadatum.metadata_type_object || !metadatum.metadata_type_object.core)) && this.orderBy == 'date') ||
                            ((this.orderBy != 'meta_value' && this.orderBy != 'meta_value_num' && metadatum.slug != 'creation_date' && (metadatum.metadata_type_object != undefined && metadatum.metadata_type_object.core)) && this.orderBy == metadatum.metadata_type_object.related_mapped_prop) ||
                            ((this.orderBy != 'meta_value' && this.orderBy != 'meta_value_num' && metadatum.slug != 'creation_date' && (!metadatum.metadata_type_object || !metadatum.metadata_type_object.core)) && this.orderBy == metadatum.slug) ||
                            ((this.orderBy == 'meta_value' || this.orderBy == 'meta_value_num') && this.getMetaKey() == metadatum.id)
                           )     
                            return metadatum.name;
                    }
                }
            }
        },
        watch: {
            displayedMetadata() {
                this.localDisplayedMetadata = JSON.parse(JSON.stringify(this.displayedMetadata));
            },
            openAdvancedSearch(newValue){
                if (newValue == false){
                    this.$eventBusSearch.$emit('closeAdvancedSearch');
                    this.advancedSearchResults = false;
                    this.isFiltersModalActive = true;
                } else {
                    this.$eventBusSearch.clearAllFilters();
                    this.isFiltersModalActive = false;
                }
            },
            orderByName() {
                if (this.isSortingByCustomMetadata)
                    this.hasAnOpenAlert = true;
            },
            isFiltersModalActive() {
                if (this.isFiltersModalActive) {
                    setTimeout(() => {
                        if (this.$refs['filters-modal'] && this.$refs['filters-modal'].focus)
                            this.$refs['filters-modal'].focus();
                    }, 800);
                }
            }
        },
        created() {

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
                /* This condition is to prevent an incorrect fetch by filter or metadata when we coming from items
                 * at collection level to items page at repository level
                 */
                if (this.collectionId == to.params.collectionId || to.query.fromBreadcrumb) {
                    this.prepareMetadata();
                }
            });

            if(this.$route.query && this.$route.query.advancedSearch) {
                this.openAdvancedSearch = this.$route.query.advancedSearch;
            }

            this.$root.$on('openAdvancedSearch', (openAdvancedSearch) => {
                this.openAdvancedSearch = openAdvancedSearch;
            });

        },
        mounted() {
            
            this.prepareMetadata();
            this.localDisplayedMetadata = JSON.parse(JSON.stringify(this.displayedMetadata));

            // Updates Collection Header Breadcrumb
            this.$root.$emit('onCollectionBreadCrumbUpdate', [{ path: '', label: this.$i18n.get('items') }]);

            // Setting initial view mode
            let prefsAdminViewMode = !this.isRepositoryLevel ? 'admin_view_mode_' + this.collectionId : 'admin_view_mode';
            if (this.$userPrefs.get(prefsAdminViewMode) == undefined)
                this.$eventBusSearch.setInitialAdminViewMode('table');
            else {
                let existingViewMode = this.$userPrefs.get(prefsAdminViewMode);
                if (existingViewMode == 'cards' || 
                    existingViewMode == 'table' || 
                    existingViewMode == 'records' || 
                    existingViewMode == 'grid' || 
                    existingViewMode == 'masonry')
                    this.$eventBusSearch.setInitialAdminViewMode(this.$userPrefs.get(prefsAdminViewMode));
                else
                    this.$eventBusSearch.setInitialAdminViewMode('table');
            }
            

            this.showItemsHiddingDueSortingDialog();

            // Watches window resize to adjust filter's top position and compression on mobile 
            this.hideFiltersOnMobile();
            window.addEventListener('resize', this.hideFiltersOnMobile);
        },
        beforeDestroy() {
            this.removeEventListeners();

            // Cancels previous Metadata Request
            if (this.metadataSearchCancel != undefined)
                this.metadataSearchCancel.cancel('Metadata search Canceled.');

            // Cancels previous Items Request
            if (this.$eventBusSearch.searchCancel != undefined)
                this.$eventBusSearch.searchCancel.cancel('Item search Canceled.');

        },
        methods: {
            ...mapGetters('collection', [
                'getItems',
                'getItemsListTemplate',
                'getCollection'
            ]),
            ...mapActions('collection', [
                'fetchCollectionBasics'
            ]),
            ...mapActions('metadata', [
                'fetchMetadata'
            ]),
            ...mapGetters('metadata', [
                'getMetadata'
            ]),
            ...mapActions('filter', [
                'fetchFilters',
                'fetchRepositoryCollectionFilters'
            ]),
            ...mapGetters('filter', [
                'getFilters',
                'getRepositoryCollectionFilters'
            ]),
            ...mapGetters('search', [
                'getSearchQuery',
                'getStatus',
                'getOrderBy',
                'getOrderByName',
                'getOrder',
                'getTotalItems',
                'getAdminViewMode',
                'getMetaKey'
            ]),
            onOpenImportersModal() {
                this.$buefy.modal.open({
                    parent: this,
                    component: AvailableImportersModal,
                    hasModalCard: true,
                    props: { 
                        targetCollection: this.collectionId,
                        hideWhenManualCollection: true
                    },
                    trapFocus: true
                });
            },
            openExposersModal() {
                this.$buefy.modal.open({
                    parent: this,
                    component: ExposersModal,
                    hasModalCard: true,
                    props: { 
                        collectionId: this.collectionId,
                        totalItems: this.totalItems
                    },
                    trapFocus: true
                })
            },
            onOpenCollectionsModal() {
                this.$buefy.modal.open({
                    parent: this,
                    component: CollectionsModal,
                    hasModalCard: true,
                    trapFocus: true
                });
            },
            updateSearch() {
                this.$eventBusSearch.setSearchQuery(this.futureSearchQuery);
            },  
            onChangeOrderBy(metadatum) {
                this.$eventBusSearch.setOrderBy(metadatum);
                this.showItemsHiddingDueSortingDialog();
            },
            onChangeOrder() {
                this.order == 'DESC' ? this.$eventBusSearch.setOrder('ASC') : this.$eventBusSearch.setOrder('DESC');
            },
            onChangeAdminViewMode(adminViewMode) {
                 // We need to load metadata again as fetch_only might change from view mode
                this.prepareMetadata();
                this.$eventBusSearch.setAdminViewMode(adminViewMode);
            },
            onChangeDisplayedMetadata() {
                let fetchOnlyMetadatumIds = [];

                for (let i = 0; i < this.localDisplayedMetadata.length; i++) {

                    this.displayedMetadata[i].display = this.localDisplayedMetadata[i].display;
                    if (this.displayedMetadata[i].id != undefined) {
                        if (this.displayedMetadata[i].display) {
                            fetchOnlyMetadatumIds.push(this.displayedMetadata[i].id);
                        }
                    }
                }
                let thumbnailMetadatum = this.localDisplayedMetadata.find(metadatum => metadatum.slug == 'thumbnail');
                let creationDateMetadatum = this.localDisplayedMetadata.find(metadatum => metadatum.slug == 'creation_date');
                let authorNameMetadatum = this.localDisplayedMetadata.find(metadatum => metadatum.slug == 'author_name');
                
                let descriptionMetadatum = this.localDisplayedMetadata.find(metadatum => metadatum.metadata_type_object != undefined ? metadatum.metadata_type_object.related_mapped_prop == 'description' : false);

                // Updates Search
                this.$eventBusSearch.addFetchOnly(
                    ((thumbnailMetadatum != undefined && thumbnailMetadatum.display) ? 'thumbnail' : null) + ',' +
                    ((creationDateMetadatum != undefined && creationDateMetadatum.display) ? 'creation_date' : null) + ',' +
                    ((authorNameMetadatum != undefined && authorNameMetadatum.display) ? 'author_name': null) + ',' +
                    (this.isRepositoryLevel ? 'title' : null) + ',' +
                    (this.isRepositoryLevel && descriptionMetadatum.display ? 'description' : null), false, fetchOnlyMetadatumIds.toString());

                // Closes dropdown
                this.$refs.displayedMetadataDropdown.toggle();
            },
            prepareMetadata() {

                // Cancels previous Request
                if (this.metadataSearchCancel != undefined)
                    this.metadataSearchCancel.cancel('Metadata search Canceled.');

                this.$eventBusSearch.cleanFetchOnly();
                this.isLoadingMetadata = true;
               
                // Processing is done inside a local variable
                let metadata = [];
                this.fetchMetadata({
                    collectionId: this.collectionId,
                    isRepositoryLevel: this.isRepositoryLevel,
                    isContextEdit: false
                })
                    .then((resp) => {
                        resp.request
                            .then(() => {
                                this.sortingMetadata = [];

                                // Decides if custom meta will be loaded with item.
                                let shouldLoadMeta = this.adminViewMode == 'table' || this.adminViewMode == 'records' || this.adminViewMode == undefined;
                                
                                if (shouldLoadMeta) {
                                    
                                    // Loads user prefs object as we'll need to check if there's something configured by user 
                                    let prefsFetchOnly = !this.isRepositoryLevel ? `fetch_only_${this.collectionId}` : 'fetch_only';
                                    let prefsFetchOnlyMeta = !this.isRepositoryLevel ? `fetch_only_meta_${this.collectionId}` : 'fetch_only_meta';

                                    let prefsFetchOnlyObject = this.$userPrefs.get(prefsFetchOnly) ? typeof this.$userPrefs.get(prefsFetchOnly) != 'string' ? this.$userPrefs.get(prefsFetchOnly) : this.$userPrefs.get(prefsFetchOnly).replace(/,null/g, '').split(',') : [];
                                    let prefsFetchOnlyMetaObject = this.$userPrefs.get(prefsFetchOnlyMeta) ? this.$userPrefs.get(prefsFetchOnlyMeta).split(',') : [];

                                    let thumbnailMetadatumDisplay = prefsFetchOnlyObject ? (prefsFetchOnlyObject[0] != null) : true;
                                    
                                    metadata.push({
                                        name: this.$i18n.get('label_thumbnail'),
                                        metadatum: 'row_thumbnail',
                                        metadata_type: undefined,
                                        slug: 'thumbnail',
                                        id: undefined,
                                        display: thumbnailMetadatumDisplay
                                    });

                                    // Repository Level always shows core metadata
                                    if (this.isRepositoryLevel) {
                                        metadata.push({
                                            name: this.$i18n.get('label_title'),
                                            metadatum: 'row_title',
                                            metadata_type_object: {core: true, related_mapped_prop: 'title'},
                                            metadata_type: undefined,
                                            slug: 'title',
                                            id: undefined,
                                            display: true
                                        }); 
                                        metadata.push({
                                            name: this.$i18n.get('label_description'),
                                            metadatum: 'row_description',
                                            metadata_type_object: {core: true, related_mapped_prop: 'description'},
                                            metadata_type: undefined,
                                            slug: 'description',
                                            id: undefined,
                                            display: true
                                        }); 
                                    }

                                    let fetchOnlyMetadatumIds = [];

                                    for (let metadatum of this.metadata) {
                                        if (metadatum.display !== 'never') {

                                            let display;

                                            // Deciding display based on collection settings
                                            if (metadatum.display == 'no')
                                                display = false;
                                            else if (metadatum.display == 'yes')
                                                display = true;

                                            // Deciding display based on user prefs
                                            if (prefsFetchOnlyMetaObject.length) {
                                                let index = prefsFetchOnlyMetaObject.findIndex(metadatumId => metadatumId == metadatum.id);

                                                display = index >= 0;
                                            }

                                            metadata.push({
                                                    name: metadatum.name,
                                                    metadatum: metadatum.description,
                                                    slug: metadatum.slug,
                                                    metadata_type: metadatum.metadata_type,
                                                    metadata_type_object: metadatum.metadata_type_object,
                                                    metadata_type_options: metadatum.metadata_type_options,
                                                    id: metadatum.id,
                                                    display: display,
                                                    collection_id: metadatum.collection_id,
                                                    multiple: metadatum.multiple,
                                            });

                                            if (display)
                                                fetchOnlyMetadatumIds.push(metadatum.id);
                                            
                                            if (
                                                metadatum.metadata_type != 'Tainacan\\Metadata_Types\\Core_Description' &&
                                                metadatum.metadata_type != 'Tainacan\\Metadata_Types\\Taxonomy' &&
                                                metadatum.metadata_type != 'Tainacan\\Metadata_Types\\Relationship'
                                            ) {
                                                this.sortingMetadata.push(metadatum);
                                            }
                                            
                                        }
                                    }

                                    let creationDateMetadatumDisplay = prefsFetchOnlyObject ? (prefsFetchOnlyObject[1] != null) : true;
                                    let authorNameMetadatumDisplay = prefsFetchOnlyObject ? (prefsFetchOnlyObject[2] != null) : true;

                                    // Creation date and author name should appear only on admin.
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
                                    
                                    this.$eventBusSearch.addFetchOnly(
                                        (thumbnailMetadatumDisplay ? 'thumbnail' : null) +','+
                                        (creationDateMetadatumDisplay ? 'creation_date' : null) +','+
                                        (authorNameMetadatumDisplay ? 'author_name' : null) +','+
                                        (this.isRepositoryLevel ? 'title' : null) +','+
                                        (this.isRepositoryLevel ? 'description' : null)
                                    , false, fetchOnlyMetadatumIds.toString());

                                    // Sorting metadata
                                    if (this.isRepositoryLevel) {
                                        this.sortingMetadata.push({
                                            name: this.$i18n.get('label_title'),
                                            metadatum: 'row_title',
                                            metadata_type_object: {core: true, related_mapped_prop: 'title'},
                                            metadata_type: undefined,
                                            slug: 'title',
                                            id: undefined,
                                            display: true
                                        });
                                    }
                                    this.sortingMetadata.push({
                                        name: this.$i18n.get('label_creation_date'),
                                        metadatum: 'row_creation',
                                        metadata_type: undefined,
                                        slug: 'creation_date',
                                        id: undefined,
                                        display: creationDateMetadatumDisplay
                                    });
                                    if (authorNameMetadatumDisplay) {
                                        this.sortingMetadata.push({
                                            name: this.$i18n.get('label_created_by'),
                                            metadatum: 'row_author',
                                            metadata_type: undefined,
                                            slug: 'author_name',
                                            id: undefined,
                                            display: authorNameMetadatumDisplay
                                        });
                                    }

                                // Loads only basic attributes necessary to view modes that do not allow custom meta
                                } else {
                            
                                    this.$eventBusSearch.addFetchOnly('thumbnail,creation_date,author_name,title,description', true, '');

                                    if (this.isRepositoryLevel) {
                                        this.sortingMetadata.push({
                                            name: this.$i18n.get('label_title'),
                                            metadatum: 'row_title',
                                            metadata_type_object: {core: true, related_mapped_prop: 'title'},
                                            metadata_type: undefined,
                                            slug: 'title',
                                            id: undefined,
                                            display: true
                                        });
                                    }

                                    for (let metadatum of this.metadata) {
                                        if (metadatum.display !== 'never' &&
                                            metadatum.metadata_type != 'Tainacan\\Metadata_Types\\Core_Description' &&
                                            metadatum.metadata_type != 'Tainacan\\Metadata_Types\\Taxonomy' &&
                                            metadatum.metadata_type != 'Tainacan\\Metadata_Types\\Relationship') {
                                                this.sortingMetadata.push(metadatum);
                                        }
                                    }

                                    this.sortingMetadata.push({
                                        name: this.$i18n.get('label_creation_date'),
                                        metadatum: 'row_creation',
                                        metadata_type: undefined,
                                        slug: 'creation_date',
                                        id: undefined,
                                        display: true
                                    })

                                }

                                this.isLoadingMetadata = false;
                                this.displayedMetadata = metadata;
                            })
                            .catch(() => {
                                this.isLoadingMetadata = false;
                            })

                        // Search Request Token for cancelling
                        this.metadataSearchCancel = resp.source;
                    })
                    .catch(() => this.isLoadingMetadata = false);     
            },
            updateCollectionInfo () {
                // Only needed for displayting totalItems on tabs.
                if (this.collectionId)
                    this.fetchCollectionBasics({ collectionId: this.collectionId, isContextEdit: true });
            },
            showItemsHiddingDueSortingDialog() {

                if (this.isSortingByCustomMetadata &&
                    this.$userPrefs.get('neverShowItemsHiddenDueSortingDialog') != true) {     

                    this.hasAnOpenModal = true;

                    this.openMetatadaSortingWarningDialog({ offerCheckbox: true });
                }
            },
            openMetatadaSortingWarningDialog({ offerCheckbox }) {
                this.$buefy.modal.open({
                        parent: this,
                        component: CustomDialog,
                        props: {
                            icon: 'alert',
                            title: this.$i18n.get('label_warning'),
                            message: this.$i18n.get('info_items_hidden_due_sorting'),
                            onConfirm: () => {
                                this.hasAnOpenModal = false;
                            },
                            hideCancel: true,
                            showNeverShowAgainOption: offerCheckbox && tainacan_plugin.user_caps != undefined && tainacan_plugin.user_caps.length != undefined && tainacan_plugin.user_caps.length > 0,
                            messageKeyForUserPrefs: 'ItemsHiddenDueSorting'
                        },
                        trapFocus: true
                    });
            },
            hideFiltersOnMobile: _.debounce( function() {
                this.$nextTick(() => {
 
                    if (window.innerWidth || document.documentElement.clientWidth || document.body.clientWidth) {
                        const isMobile = (window.innerWidth || document.documentElement.clientWidth || document.body.clientWidth) <= 768;
                        
                        if (isMobile) {
                            this.isFiltersModalActive = false;
                        } else {
                            this.isFiltersModalActive = true;
                            document.documentElement.classList.remove('is-clipped');
                        }
                    }
                });
            }, 500),
            removeEventListeners() {
                // Component
                this.$off();
                // Window
                window.removeEventListener('resize', this.hideFiltersOnMobile);
                // $root
                this.$root.$off('openAdvancedSearch');
                // $eventBusSearch
                this.$eventBusSearch.$off('isLoadingItems');
                this.$eventBusSearch.$off('hasFiltered');
                this.$eventBusSearch.$off('advancedSearchResults');
                this.$eventBusSearch.$off('hasToPrepareMetadataAndFilters');
            }
        }
    }
</script>

<style lang="scss" scoped>

    @import '../../scss/_variables.scss';

    .tainacan-page-title {
        padding: 25px $page-side-padding;
        margin: 0;
    }

    .repository-level-page {
        overflow-y: auto;
    }

    .advanced-search-criteria-title {
        margin-bottom: 40px;

        h1, h2 {
            font-size: 1.25em;
            font-weight: 500;
            color: var(--tainacan-heading-color);
            display: inline-block;
            margin-bottom: 0;
        }
        .field.is-grouped {
            margin-left: auto;
        }
        a.back-link{
            font-weight: 500;
            float: right;
            margin-top: 5px;
        }
        hr{
            margin: 3px 0px 4px 0px; 
            height: 2px;
            background-color: var(--tainacan-secondary);
            border: none;
        }
    }

    .advanced-search-results-title {
        margin-bottom: 40px;
        margin: 0 $page-side-padding 42px $page-side-padding;

        h1, h2 {
            font-size: 1.25em;
            font-weight: 500;
            color: var(--tainacan-heading-color);
            display: inline-block;
            margin-bottom: 0;
        }
        .field.is-grouped {
            margin-left: auto;
        }
        a.back-link{
            font-weight: 500;
            float: right;
            margin-top: 5px;
        }
        hr{
            margin: 3px 0px 4px 0px; 
            height: 1px;
            background-color: var(--tainacan-secondary);
        }
    }

    .advanced-search-form-submit {
        display: flex;
        justify-content: flex-end;
        padding-right: $page-side-padding;
        padding-left: $page-side-padding;
        margin-bottom: 1em;

        p { margin-left: 0.75em; }
    }

    .tnc-advanced-search-close {
        padding-top: 25px;
        padding-right: $page-side-padding;
        padding-left: $page-side-padding;

        .column {
            padding: 0 0.3em 0.3em !important;
        }
    }

    .page-container {
        padding: 0;
    }

    .filters-menu {
        width: $filter-menu-width;
        min-width: 180px;
        min-height: 100%;
        height: auto;
        max-height: calc(100% - 94px);
        max-height: calc(100vh - 94px);
        float: left;
        overflow-y: auto;
        overflow-x: hidden;
        visibility: visible;
        display: block;

        @media screen and (max-width: 768px) {
            width: 100%;
            padding: 0;
            
            #filters-items-list {
                padding: $page-small-side-padding;
            }
        }
        @media screen and (min-width: 769px) {
            top: 1px !important;
            position: relative;
            position: sticky;
        }

        .label {
            font-size: 0.75em;
            font-weight: normal;
        }

        .checkbox {
            margin-bottom: 5px;
            align-items: baseline;
        }

    }
    #filter-menu-compress-button {
        position: absolute;
        z-index: 99;
        bottom: 0px;
        left: 0;
        max-width: 1.625em;
        height: 1.625em;
        width: 1.625em;
        border: none;
        background-color: var(--tainacan-primary);
        color: var(--tainacan-secondary);
        padding: 0;
        border-top-right-radius: 2px;
        border-bottom-right-radius: 2px;
        cursor: pointer;
        transition: top 0.3s;

        &:focus {
            outline: none !important;
        }

        .icon {
            margin-top: -1px;
        }

        @media screen and (max-width: 768px) {
            max-width: 100%;
            width: auto;
            padding: 3px 6px 3px 0px;
            height: 1.625em;

            .icon {
                position: relative;
                top: -3px;
            }
            .text {
                position: relative;
                top: -2px;
            }
        }
    }
        
    .search-control {
        min-height: $subheader-height;
        height: auto;
        position: relative;
        padding-top: $page-small-top-padding;
        padding-bottom: $page-small-top-padding;
        padding-left: $page-side-padding;
        padding-right: $page-side-padding;
        display: flex;
        justify-content: space-between;
        flex-wrap: wrap;

        .search-control-item {
            display: inline-block;
            margin-bottom: 12px;
            margin-right: auto;
            padding-right: 10px;

            @media screen and (max-width: 769px) {            
                margin-right: 0;
                padding-right: 0;

                &:first-of-type {
                    min-width: 100%;

                    .search-area {
                        padding-right: 0;
                        max-width: 100% !important;
                    }
                    .is-pulled-right {
                        position: relative;
                        right: 0px !important;
                    }
                }
            }

            .label {
                color: var(--tainacan-label-color);
                font-size: 0.875em;
                font-weight: normal;
                margin-top: 3px;
                margin-bottom: 2px;
                cursor: default;
            }

            .button {
                display: flex;
                align-items: center;
            }
            
            .field {
                align-items: center;
            }

            .gray-icon, 
            .gray-icon .icon {
                color: var(--tainacan-info-color) !important;
                padding-right: 10px;
            }
            .gray-icon .icon i::before, 
            .gray-icon i::before {
                font-size: 1.3125em !important;
                color: var(--tainacan-info-color) !important;
                max-width: 26px;
            }
            
            .view-mode-icon {
                margin-right: 0px !important;
                margin-top: -2px;
                margin-left: 4px !important;
                width: 1.25em;
            }

            .dropdown-menu {
                display: block;

                div.dropdown-content {
                    padding: 0;

                    .metadata-options-container {
                        max-height: 288px;
                        overflow: auto;
                    }
                    .dropdown-item {
                        padding: 0.25em 1.0em 0.25em 0.75em; 
                    }
                    .dropdown-item span{
                        vertical-align: middle;
                    }      
                    .dropdown-item-apply {
                        width: 100%;
                        border-top: 1px solid var(--tainacan-skeleton-color);
                        padding: 8px 12px;
                        text-align: right;
                    }
                    .dropdown-item-apply .button {
                        width: 100%;
                    }
                }
            }

            .search-area {
                position: relative;
                display: flex;
                align-items: center;
                width: 100%;
                min-width: 120px;
                max-width: calc(16.66667vw - 60px);
                padding-right: 15px;

                .control {
                    width: 100%;
                    .icon {
                        pointer-events: all;
                        cursor: pointer;
                        color: var(--tainacan-label-color);
                    }
                    margin-bottom: 5px;
                }
                .is-pulled-right {
                    position: absolute;
                    right: 15px;
                    top: 100%;
                }
                .input {
                    border: 1px solid var(--tainacan-input-border-color);
                    min-height: 30px !important;
                }
                a {
                    margin-left: 12px;
                    white-space: nowrap; 
                }
            }

        }

    }

    .above-search-control {
        margin-bottom: 0;
        margin-top: 0;
        height: calc(100% - 184px);
        min-height: 200px;

        .loading-container {
            position: relative;
            min-height: 50vh;
            height: auto;
        }
    }

    .tabs {
        padding-top: 6px;
        margin-bottom: 20px;
        padding-left: $page-side-padding;
        padding-right: $page-side-padding;

        @media screen and (min-width: 1024px) {
            overflow: visible;
        }

        li {
            cursor: pointer;
        }
    }

    .metadata-alert {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin: 6px $page-side-padding;
        border-radius: 3px;
        padding: 4px 12px;
        color: var(--tainacan-yellow2);
        background: var(--tainacan-yellow1);
        animation-name: appear;
        animation-duration: 0.5s;

        p {
            margin: 0 auto;
        }
        
        &>div {
            display: flex;
            
            .button,
            .button:hover,
            .button:active,
            .button:focus {
                background: none;
                color:var(--tainacan-yellow2);
                font-weight: bold;
                border: none;
                cursor: pointer;
            }
        }
    }

    .items-list-area {
        margin-left: 0;
        overflow-y: auto;
        overflow-x: hidden;
        position: relative;
    }

    .table-container {
        padding-left: $page-side-padding;
        padding-right: $page-side-padding;
        min-height: 50vh;
    }

    .pagination-area {
        margin-left: $page-side-padding;
        margin-right: $page-side-padding;
    }



</style>


