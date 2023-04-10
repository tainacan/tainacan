<template>
    <div 
            :class="{ 
                'repository-level-page': isRepositoryLevel,
                'is-filters-menu-open': isFiltersModalActive && !openAdvancedSearch
            }"
            aria-live="polite"
            ref="items-page-container">

        <!-- PAGE TITLE --------------------- -->
        <tainacan-title
                v-if="!$adminOptions.hideItemsListPageTitle" 
                :bread-crumb-items="[{ path: '', label: $i18n.get('items') }]"/>

        <!-- SEARCH CONTROL ------------------------- -->
        <div
                aria-labelledby="search-control-landmark"
                role="region"
                ref="search-control"
                class="search-control"  
                :style="( $adminOptions.itemsSingleSelectionMode || $adminOptions.itemsMultipleSelectionMode || $adminOptions.itemsSearchSelectionMode ) ? '--tainacan-container-padding: 6px;' : ''">
            <h3 
                    id="search-control-landmark"
                    class="sr-only">
                {{ $i18n.get('label_sort_visualization') }}
            </h3>
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
                            shown: 500,
                            hide: 300,
                        },
                        content: !isFiltersModalActive ? $i18n.get('label_show_filters') : $i18n.get('label_hide_filters'),
                        autoHide: false,
                        placement: 'auto-start',
                        popperClass: ['tainacan-tooltip', 'tooltip', isRepositoryLevel ? 'tainacan-repository-tooltip' : '']
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
                    <b-dropdown
                            ref="tainacan-textual-search-input"
                            class="tainacan-textual-search-input"
                            aria-role="dialog"
                            :mobile-modal="false"
                            :disabled="openAdvancedSearch"
                            :triggers="hasSearchByMoreThanOneWord ? ['click','contextmenu','focus'] : []">
                        <b-input
                                slot="trigger"
                                size="is-small"
                                :placeholder="$i18n.get('instruction_search')"
                                type="search"
                                :aria-label="$i18n.get('instruction_search') + ' ' + $i18n.get('items')"
                                :value="searchQuery"
                                @input.native="typeFutureSearch"
                                @keyup.enter.native="updateSearch()"
                                icon-right="magnify"
                                icon-right-clickable
                                @icon-right-click="updateSearch()"
                                :disabled="openAdvancedSearch" />
                        <b-dropdown-item 
                                @click="updateSearch()"
                                :focusable="false">
                            <span v-html="$i18n.get('instruction_press_enter_to_search_for')"/>&nbsp;
                            <em>{{ sentenceMode == true ? futureSearchQuery : ('"' + futureSearchQuery + '"') }}.</em>
                        </b-dropdown-item>
                        <b-dropdown-item
                                custom
                                :focusable="false">
                            <b-checkbox 
                                    :value="sentenceMode"
                                    @input="$eventBusSearch.setSentenceMode($event)">
                                {{ $i18n.get('label_use_search_separated_words') }}
                            </b-checkbox>
                            <small class="is-small help">{{ $i18n.get('info_use_search_separated_words') }}</small>
                        </b-dropdown-item>
                        <b-dropdown-item
                                v-if="!$adminOptions.hideItemsListAdvancedSearch"
                                :focusable="false"
                                @click="openAdvancedSearch = !openAdvancedSearch; $eventBusSearch.clearAllFilters();">
                            {{ $i18n.get('info_for_more_metadata_search_options_use') }}&nbsp; 
                            <a 
                                    @click="openAdvancedSearch = !openAdvancedSearch; $eventBusSearch.clearAllFilters();"
                                    class="has-text-secondary">
                                {{ $i18n.get('advanced_search') }}
                            </a>
                        </b-dropdown-item>
                    </b-dropdown>
                    <a
                            v-if="!$adminOptions.hideItemsListAdvancedSearch"
                            @click="openAdvancedSearch = !openAdvancedSearch; $eventBusSearch.clearAllFilters();"
                            class="advanced-search-toggle has-text-secondary"
                            :class="openAdvancedSearch ? 'is-open' : 'is-closed'">
                        {{ $i18n.get('advanced_search') }}
                        <span class="icon">
                            <i class="tainacan-icon tainacan-icon-search" />
                        </span>
                    </a>
                </div>
            </div>

            <!-- Item Creation Dropdown (or button, if few options are available) -->
            <div 
                    v-if="!$adminOptions.hideItemsListCreationDropdown &&
                            collection && 
                            collection.current_user_can_edit_items"
                    class="search-control-item">
                <router-link
                        id="item-creation-options-dropdown"
                        v-if="$adminOptions.hideItemsListCreationDropdownBulkAdd && $adminOptions.hideItemsListCreationDropdownImport"
                        class="button is-secondary"
                        tag="button"
                        :to="{ path: $routerHelper.getNewItemPath(collectionId) }">
                    <span class="is-hidden-touch">{{ $i18n.getFrom('items','add_new') }}</span>
                    <span class="is-hidden-desktop">{{ $i18n.get('add') }}</span>
                    <span class="icon">
                        <i class="tainacan-icon tainacan-icon-1-125em tainacan-icon-add" />
                    </span>
                </router-link>
                <b-dropdown
                        v-else
                        :mobile-modal="true"
                        id="item-creation-options-dropdown"
                        aria-role="list"
                        trap-focus>
                    <button
                            class="button is-secondary"
                            slot="trigger">
                        <span class="is-hidden-touch">{{ $i18n.getFrom('items','add_new') }}</span>
                        <span class="is-hidden-desktop">{{ $i18n.get('add') }}</span>
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
                            v-if="!isRepositoryLevel && !$adminOptions.hideItemsListCreationDropdownBulkAdd"
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
                    <b-dropdown-item 
                            v-if="!$adminOptions.hideItemsListCreationDropdownImport"
                            aria-role="listitem">
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
                                shown: 500,
                                hide: 300,
                            },
                            content: (totalItems <= 0 || adminViewMode == 'grid'|| adminViewMode == 'cards' || adminViewMode == 'masonry') ? (adminViewMode == 'grid'|| adminViewMode == 'cards' || adminViewMode == 'masonry') ? $i18n.get('info_current_view_mode_metadata_not_allowed') : $i18n.get('info_cant_select_metadata_without_items') : '',
                            autoHide: false,
                            placement: 'auto-start',
                            popperClass: ['tainacan-tooltip', 'tooltip', isRepositoryLevel ? 'tainacan-repository-tooltip' : '']
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
                    <label class="label">{{ $i18n.get('label_sort') }}&nbsp;</label>
                    <b-dropdown
                            :mobile-modal="true"
                            @input="onChangeOrder"
                            aria-role="list"
                            trap-focus>
                        <button
                                :aria-label="$i18n.get('label_sorting_direction')"
                                class="button is-white"
                                slot="trigger"
                                style="padding-right: 3px !important;">
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
                            <span class="view-mode-icon icon is-small gray-icon">
                                <i 
                                        v-if="adminViewMode !== 'map'"
                                        :class="{'tainacan-icon-viewtable' : ( adminViewMode == 'table' || adminViewMode == undefined),
                                                'tainacan-icon-viewcards' : adminViewMode == 'cards',
                                                'tainacan-icon-viewminiature' : adminViewMode == 'grid',
                                                'tainacan-icon-viewrecords' : adminViewMode == 'records',
                                                'tainacan-icon-viewlist' : adminViewMode == 'list',
                                                'tainacan-icon-viewmasonry' : adminViewMode == 'masonry' }"
                                        class="tainacan-icon tainacan-icon-1-25em"/>
                                <svg
                                        v-else
                                        xmlns="http://www.w3.org/2000/svg"
                                        viewBox="0 0 24 24"
                                        style="fill: var(--tainacan-info-color)">
                                    <path d="M15,19L9,16.89V5L15,7.11M20.5,3C20.44,3 20.39,3 20.34,3L15,5.1L9,3L3.36,4.9C3.15,4.97 3,5.15 3,5.38V20.5A0.5,0.5 0 0,0 3.5,21C3.55,21 3.61,21 3.66,20.97L9,18.9L15,21L20.64,19.1C20.85,19 21,18.85 21,18.62V3.5A0.5,0.5 0 0,0 20.5,3Z" />
                                </svg>
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
                                v-if="!collection || (collection && collection.hide_items_thumbnail_on_lists != 'yes')" 
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
                                v-if="!collection || (collection && collection.hide_items_thumbnail_on_lists != 'yes')"
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
                        <b-dropdown-item 
                                aria-controls="items-list-results"
                                role="button"
                                :class="{ 'is-active': adminViewMode == 'list' }"
                                :value="'list'"
                                aria-role="listitem">
                            <span class="icon gray-icon">
                                <i class="tainacan-icon tainacan-icon-viewlist"/>
                            </span>
                            <span>{{ $i18n.get('label_list') }}</span>
                        </b-dropdown-item>
                        <b-dropdown-item 
                                aria-controls="items-list-results"
                                role="button"
                                :class="{ 'is-active': adminViewMode == 'map' }"
                                :value="'map'"
                                aria-role="listitem">
                            <span 
                                    style="width: 2em; margin-left: -0.45em; padding-right: 6px;"
                                    class="icon gray-icon">
                                <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        viewBox="0 0 24 24"
                                        style="fill: var(--tainacan-info-color)">
                                    <path d="M15,19L9,16.89V5L15,7.11M20.5,3C20.44,3 20.39,3 20.34,3L15,5.1L9,3L3.36,4.9C3.15,4.97 3,5.15 3,5.38V20.5A0.5,0.5 0 0,0 3.5,21C3.55,21 3.61,21 3.66,20.97L9,18.9L15,21L20.64,19.1C20.85,19 21,18.85 21,18.62V3.5A0.5,0.5 0 0,0 20.5,3Z" />
                                </svg>
                            </span>
                            <span>{{ $i18n.get('label_map') }}</span>
                        </b-dropdown-item>
                    </b-dropdown>
                </b-field>
            </div>

            <!-- Exposers or alternative links modal button -->
            <div 
                    v-if="!$adminOptions.hideItemsListExposersButton"
                    class="search-control-item">
                <button 
                        class="button is-white"
                        :aria-label="$i18n.get('label_view_as')"
                        :disabled="totalItems == undefined || totalItems <= 0"
                        @click="openExposersModal()">
                    <span class="gray-icon">
                        <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-viewas"/>
                    </span>
                    <span class="is-hidden-tablet-only is-hidden-desktop-only">{{ $i18n.get('label_view_as') }}</span>
                </button>
            </div>

        </div>

         <!-- SIDEBAR WITH FILTERS -->
        <b-modal
                role="region"
                id="filters-modal"     
                ref="filters-modal"       
                :active.sync="isFiltersModalActive"
                :width="736"
                animation="slide-menu"
                trap-focus
                aria-modal
                aria-role="dialog"
                custom-class="tainacan-modal tainacan-form filters-menu"
                :close-button-aria-label="$i18n.get('close')">
            <filters-items-list
                    :is-loading-items="isLoadingItems"
                    @updateIsLoadingItemsState="(state) => isLoadingItems = state"
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
                class="items-list-area"
                @mousemove="handleMouseMoveOverList">

            <!-- ADVANCED SEARCH -->
            <transition name="filter-item">
                <div
                        id="advanced-search-container"
                        role="search"
                        v-if="openAdvancedSearch">
                    
                    <advanced-search
                            :collection-id="collectionId"
                            :is-repository-level="isRepositoryLevel"
                            @close="openAdvancedSearch = false" />
                </div>
            </transition>

            <!-- STATUS TABS, only on Admin -------- -->
            <items-status-tabs 
                    v-if="!$adminOptions.hideItemsListStatusTabs"
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
                            !showLoading"
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
                        v-if="!showLoading && totalItems > 0"
                        :collection-id="collectionId"
                        :displayed-metadata="displayedMetadata"
                        :items="items"
                        :total-items="totalItems"
                        :is-loading="showLoading"
                        :is-on-trash="status == 'trash'"
                        :view-mode="adminViewMode"
                        :is-repository-level="isRepositoryLevel"
                        @updateIsLoading="(newIsLoadingState) => isLoadingItems = newIsLoadingState"/>

                <!-- Empty Placeholder -->
                <section
                        v-if="!isLoadingItems && totalItems == 0"
                        class="section">
                    <div class="content has-text-grey has-text-centered">
                        <p>
                            <span class="icon is-large">
                                <i class="tainacan-icon tainacan-icon-30px tainacan-icon-items" />
                            </span>
                        </p>
                        <p v-if="status == undefined || status == '' || status == 'publish,private,draft'">
                            {{ (hasFiltered || openAdvancedSearch || searchQuery) ? $i18n.get('info_no_item_found_filter') : (isSortingByCustomMetadata ? $i18n.get('info_no_item_found') : $i18n.get('info_no_item_created')) }}
                        </p>
                        <p
                                v-for="(statusOption, index) of $statusHelper.getStatuses()"
                                :key="index"
                                v-if="status == statusOption.slug">
                            {{ $i18n.get('info_no_items_' + statusOption.slug) }}
                        </p>

                        <router-link
                                v-if="!isRepositoryLevel && !isSortingByCustomMetadata && !hasFiltered && (status == undefined || status == '') && !$adminOptions.hideItemsListCreationDropdown"
                                id="button-create-item"
                                tag="button"
                                class="button is-secondary"
                                :to="{ path: $routerHelper.getNewItemPath(collectionId) }">
                            {{ $i18n.getFrom('items', 'add_new') }}
                        </router-link> 
                        <button
                                v-else-if="isRepositoryLevel && !isSortingByCustomMetadata && !hasFiltered && (status == undefined || status == '') && !$adminOptions.hideItemsListCreationDropdown"
                                id="button-create-item"
                                class="button is-secondary"
                                @click="onOpenCollectionsModal">
                            {{ $i18n.get('add_one_item') }}
                        </button>

                        <p v-if="searchQuery">
                            <template v-if="!sentenceMode">
                                <span v-html="searchedForSentence" />. {{ $i18n.get('info_try_enabling_search_by_word') }}
                                <br>
                                {{ $i18n.get('info_details_about_search_by_word') }}
                            </template>
                            <template v-else>
                                <span v-html="searchedForSentence" />. {{ $i18n.get('info_try_disabling_search_by_word') }}
                            </template>
                            <br>
                            <b-checkbox 
                                    :value="sentenceMode"
                                    @input="$eventBusSearch.setSentenceMode($event); updateSearch();">
                                {{ $i18n.get('label_use_search_separated_words') }}
                            </b-checkbox>
                        </p>
                    </div>
                </section>

                <!-- Pagination -->
                <div ref="items-pagination">
                    <pagination
                            :is-sorting-by-custom-metadata="isSortingByCustomMetadata"
                            v-if="totalItems > 0"/>
                </div>
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
            AdvancedSearch
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
                sortingMetadata: [],
                isFiltersModalActive: false,
                hasAnOpenModal: false,
                hasAnOpenAlert: true,
                metadataSearchCancel: undefined,
                isMobileScreen: false,
                windowWidth: null
            }
        },
        computed: {
            isSortingByCustomMetadata() {
                return (this.orderBy != undefined && this.orderBy != '' && this.orderBy != 'title' && this.orderBy != 'date' && this.orderBy != 'modified');
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
            sentenceMode() {
                return this.getSentenceMode();
            },
            adminViewMode() {
                const currentAdminViewMode = this.getAdminViewMode();
                return ['table', 'cards', 'records', 'grid', 'masonry', 'list', 'map'].indexOf(currentAdminViewMode) >= 0 ? currentAdminViewMode : 'table';
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
                const metadatumName =  this.$orderByHelper.getOrderByMetadatumName({
                    orderby: this.$route.query.orderby,
                    metakey: this.$route.query.metakey
                }, this.sortingMetadata);
                return this.$route.query.metakey ? metadatumName : this.$i18n.get(metadatumName);
            },
            hasSearchByMoreThanOneWord() {
                return this.futureSearchQuery && this.futureSearchQuery.split(' ').length > 1;
            },
            searchedForSentence() {
                if (this.searchQuery)
                    return this.$i18n.getWithVariables('info_you_searched_for_%s', ['<em>"' + this.searchQuery + '"</em>']);
                return '';
            }
        },
        watch: {
            displayedMetadata() {
                this.localDisplayedMetadata = JSON.parse(JSON.stringify(this.displayedMetadata));
            },
            openAdvancedSearch(newValue) {
                if (newValue == false){
                    this.$eventBusSearch.$emit('closeAdvancedSearch');
                    this.futureSearchQuery = '';
                    this.isFiltersModalActive = true;
                } else {
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
                
                if (isLoadingItems != this.isLoadingItems && this.$refs['items-page-container'] && this.$refs['search-control']) {

                    if ((this.$refs['search-control'].classList.contains('floating-search-control')))
                        this.$refs['search-control'].classList.remove('floating-search-control');
                    
                    this.$refs['items-page-container'].scrollTo({ top: this.$refs['search-control'].offsetTop - ((this.$adminOptions.hideCollectionSubheader || this.isRepositoryLevel) ? 94 : 42), behavior: 'smooth'});
                }

                this.isLoadingItems = isLoadingItems;
            });

            this.$eventBusSearch.$on('hasFiltered', hasFiltered => {
                this.hasFiltered = hasFiltered;
            });
            
            if (this.$route.query && this.$route.query.advancedSearch) {
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
                    existingViewMode == 'list' || 
                    existingViewMode == 'grid' || 
                    existingViewMode == 'masonry'|| 
                    existingViewMode == 'map')
                        this.$eventBusSearch.setInitialAdminViewMode(this.$userPrefs.get(prefsAdminViewMode));
                else
                    this.$eventBusSearch.setInitialAdminViewMode('table');
            }
            
            this.showItemsHiddingDueSortingDialog();

            this.$eventBusSearch.cleanSelectedItems();

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
                'fetchFilters'
            ]),
            ...mapGetters('filter', [
                'getFilters',
                'getRepositoryCollectionFilters'
            ]),
            ...mapGetters('search', [
                'getSearchQuery',
                'getSentenceMode',
                'getStatus',
                'getOrderBy',
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
                    trapFocus: true,
                    customClass: 'tainacan-modal',
                    closeButtonAriaLabel: this.$i18n.get('close')
                });
            },
            openExposersModal(selectedItems) {
                this.$buefy.modal.open({
                    parent: this,
                    component: ExposersModal,
                    hasModalCard: true,
                    props: { 
                        collectionId: this.collectionId,
                        totalItems: this.totalItems,
                        selectedItems: selectedItems
                    },
                    trapFocus: true,
                    customClass: 'tainacan-modal',
                    closeButtonAriaLabel: this.$i18n.get('close')
                })
            },
            onOpenCollectionsModal() {
                this.$buefy.modal.open({
                    parent: this,
                    component: CollectionsModal,
                    hasModalCard: true,
                    trapFocus: true,
                    customClass: 'tainacan-modal',
                    closeButtonAriaLabel: this.$i18n.get('close')
                });
            },
            updateSearch() {
                this.$eventBusSearch.setSearchQuery(this.futureSearchQuery);
            },  
            onChangeOrderBy(metadatum) {
                this.$eventBusSearch.setOrderBy(this.$orderByHelper.getOrderByForMetadatum(metadatum));
                this.showItemsHiddingDueSortingDialog();
            },
            onChangeOrder(newOrder) {
                if (newOrder != this.order)
                    this.$eventBusSearch.setOrder(newOrder);
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
                let modificationDateMetadatum = this.localDisplayedMetadata.find(metadatum => metadatum.slug == 'modification_date');
                let creationDateMetadatum = this.localDisplayedMetadata.find(metadatum => metadatum.slug == 'creation_date');
                let authorNameMetadatum = this.localDisplayedMetadata.find(metadatum => metadatum.slug == 'author_name');
                
                let descriptionMetadatum = this.localDisplayedMetadata.find(metadatum => metadatum.metadata_type_object != undefined ? metadatum.metadata_type_object.related_mapped_prop == 'description' : false);

                // Updates Search
                let fetchOnlyArray = [
                    ((thumbnailMetadatum != undefined && thumbnailMetadatum.display) ? 'thumbnail' : null),
                    ((modificationDateMetadatum != undefined && modificationDateMetadatum.display) ? 'modification_date' : null),
                    ((creationDateMetadatum != undefined && creationDateMetadatum.display) ? 'creation_date' : null),
                    ((authorNameMetadatum != undefined && authorNameMetadatum.display) ? 'author_name': null),
                    (this.isRepositoryLevel ? 'title' : null),
                    (this.isRepositoryLevel && descriptionMetadatum.display ? 'description' : null)
                ];
                this.$eventBusSearch.addFetchOnly(fetchOnlyArray.filter((fetchOnly) => fetchOnly != null).toString() , false, fetchOnlyMetadatumIds.toString());

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
                    isContextEdit: false,
                    includeControlMetadataTypes: true
                })
                    .then((resp) => {
                        resp.request
                            .then(() => {
                                this.sortingMetadata = [];

                                // Decides if custom meta will be loaded with item.
                                let shouldLoadMeta = this.adminViewMode == 'table' || this.adminViewMode == 'records' || this.adminViewMode == 'list' || this.adminViewMode == 'map' || this.adminViewMode == undefined;
                                
                                if (shouldLoadMeta) {
                                    
                                    // Loads user prefs object as we'll need to check if there's something configured by user 
                                    let prefsFetchOnly = !this.isRepositoryLevel ? `fetch_only_${this.collectionId}` : 'fetch_only';
                                    let prefsFetchOnlyMeta = !this.isRepositoryLevel ? `fetch_only_meta_${this.collectionId}` : 'fetch_only_meta';
                                    let prefsFetchOnlyObject = this.$userPrefs.get(prefsFetchOnly) ? (typeof this.$userPrefs.get(prefsFetchOnly) != 'string' ? this.$userPrefs.get(prefsFetchOnly) : this.$userPrefs.get(prefsFetchOnly).split(',')) : ['thumbnail'];
                                    let prefsFetchOnlyMetaObject = this.$userPrefs.get(prefsFetchOnlyMeta) ? this.$userPrefs.get(prefsFetchOnlyMeta).split(',') : [];

                                    let thumbnailMetadatumDisplay = (!this.isRepositoryLevel && this.collection.hide_items_thumbnail_on_lists == 'yes') ? null : (prefsFetchOnlyObject && Array.isArray(prefsFetchOnlyObject) ? ((prefsFetchOnlyObject.indexOf('thumbnail') >= 0)) : true);

                                    if (this.isRepositoryLevel || this.collection.hide_items_thumbnail_on_lists != 'yes') {
                                        metadata.push({
                                            name: this.$i18n.get('label_thumbnail'),
                                            metadatum: 'row_thumbnail',
                                            metadata_type: undefined,
                                            slug: 'thumbnail',
                                            id: undefined,
                                            display: thumbnailMetadatumDisplay
                                        });
                                    }
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
                                        if (metadatum.display !== 'never' || metadatum.metadata_type == 'Tainacan\\Metadata_Types\\Control') {

                                            if (metadatum.metadata_type != 'Tainacan\\Metadata_Types\\Control') {
                                                let display;

                                                // Deciding display based on collection settings
                                                if (metadatum.display == 'no')
                                                    display = false;
                                                else if (metadatum.display == 'yes')
                                                    display = true;

                                                // Deciding display based on user prefs
                                                if (prefsFetchOnlyMetaObject.length && metadatum.display != 'yes') {
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
                                            }

                                            if ( metadatum.metadata_type_object.sortable )
                                                this.sortingMetadata.push(metadatum);
                                            
                                        }
                                    }
                                    
                                    let modificationDateMetadatumDisplay = (prefsFetchOnlyObject && Array.isArray(prefsFetchOnlyObject) ) ? (prefsFetchOnlyObject.indexOf('modification_date') >= 0) : true;
                                    let creationDateMetadatumDisplay = (prefsFetchOnlyObject && Array.isArray(prefsFetchOnlyObject) ) ? (prefsFetchOnlyObject.indexOf('creation_date') >= 0) : true;
                                    let authorNameMetadatumDisplay = (prefsFetchOnlyObject && Array.isArray(prefsFetchOnlyObject) ) ? (prefsFetchOnlyObject.indexOf('author_name') >= 0) : true;
           
                                    // Creation date and author name should appear only on admin.
                                    metadata.push({
                                        name: this.$i18n.get('label_modification_date'),
                                        metadatum: 'row_modification',
                                        metadata_type: undefined,
                                        slug: 'modification_date',
                                        id: undefined,
                                        display: modificationDateMetadatumDisplay
                                    });
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
                                    
                                    let fetchOnlyArray = [
                                        (thumbnailMetadatumDisplay ? 'thumbnail' : null),
                                        (modificationDateMetadatumDisplay ? 'modification_date' : null),
                                        (creationDateMetadatumDisplay ? 'creation_date' : null),
                                        (authorNameMetadatumDisplay ? 'author_name' : null),
                                        (this.isRepositoryLevel ? 'title' : null),
                                        (this.isRepositoryLevel ? 'description' : null)
                                    ];
                                    this.$eventBusSearch.addFetchOnly(fetchOnlyArray.filter((fetchOnly) => fetchOnly != null).toString() , false, fetchOnlyMetadatumIds.toString());

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
                                        name: this.$i18n.get('label_modification_date'),
                                        metadatum: 'row_modification',
                                        metadata_type: undefined,
                                        slug: 'modification_date',
                                        id: undefined,
                                        display: modificationDateMetadatumDisplay
                                    });
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

                                    const basicAttributes = (!this.isRepositoryLevel && this.collection.hide_items_thumbnail_on_lists == 'yes') ? 'modification_date,creation_date,author_name,title,description' : 'thumbnail,modification_date,creation_date,author_name,title,description';
                                    this.$eventBusSearch.addFetchOnly(basicAttributes, true, '');

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
                                        if ( (metadatum.display !== 'never' || metadatum.metadata_type == 'Tainacan\\Metadata_Types\\Control') && metadatum.metadata_type_object.sortable )
                                            this.sortingMetadata.push(metadatum);
                                    }

                                    this.sortingMetadata.push({
                                        name: this.$i18n.get('label_modification_date'),
                                        metadatum: 'row_modification',
                                        metadata_type: undefined,
                                        slug: 'modification_date',
                                        id: undefined,
                                        display: true
                                    })

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
            updateCollectionInfo() {
                // Only needed for displaying totalItems on tabs.
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
            typeFutureSearch($event) {
                this.futureSearchQuery = $event.target.value;
                
                // If we have more than one word and the dropdown is not active, open it
                if ( this.hasSearchByMoreThanOneWord && this.$refs['tainacan-textual-search-input'] && !this.$refs['tainacan-textual-search-input'].isActive && typeof this.$refs['tainacan-textual-search-input'].toggle === 'function' )
                    this.$refs['tainacan-textual-search-input'].toggle();

                // If we don't have more than one word any more and the dropdown is still active, close it
                if ( !this.hasSearchByMoreThanOneWord && this.$refs['tainacan-textual-search-input'] && this.$refs['tainacan-textual-search-input'].isActive && typeof this.$refs['tainacan-textual-search-input'].toggle === 'function' )
                    this.$refs['tainacan-textual-search-input'].toggle();
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
                            showNeverShowAgainOption: offerCheckbox && tainacan_plugin.user_caps != undefined && Object.keys(tainacan_plugin.user_caps).length != undefined && Object.keys(tainacan_plugin.user_caps).length > 0,
                            messageKeyForUserPrefs: 'ItemsHiddenDueSorting'
                        },
                        trapFocus: true,
                        customClass: 'tainacan-modal',
                        closeButtonAriaLabel: this.$i18n.get('close')
                    });
            },
            hideFiltersOnMobile: _.debounce( function() {
                this.$nextTick(() => {
                    if (window.innerWidth || document.documentElement.clientWidth || document.body.clientWidth) {
                        const previousMobileScreen = this.isMobileScreen;
                        const previousWindowWidth = this.windowWidth;

                        this.windowWidth = (window.innerWidth || document.documentElement.clientWidth || document.body.clientWidth);
                        this.isMobileScreen = this.windowWidth <= 768;

                        if (                                                    // We DO NOT want to open the filters due to this resize event IF:
                            (!previousMobileScreen && this.isMobileScreen) ||   // We're coming from a non-mobile screen to a mobile screen, or
                            (previousWindowWidth == this.windowWidth) ||        // The window size didn't changed (the resize event is triggered by scrolls on mobile), or
                            this.openAdvancedSearch                             // Advanced search is opened
                        ) {
                            this.isFiltersModalActive = false;
                        } else {
                            this.isFiltersModalActive = true;
                            document.documentElement.classList.remove('is-clipped');
                        }
                    }
                });
            }, 500),
            handleMouseMoveOverList: _.debounce( function($event) {

                // Handles search control bar
                if (this.$refs['search-control']) {
                    const bounding = this.$refs['search-control'].getBoundingClientRect();
                    const isHidden = !(bounding.top >= 0 && bounding.bottom <= ((window.innerHeight || document.documentElement.clientHeight) + 136));
                    
                    if (isHidden && ($event.screenY <= 286)) {
                        if (!(this.$refs['search-control'].classList.contains('floating-search-control')))
                            this.$refs['search-control'].classList.add('floating-search-control');                    
                    } else {
                        if ((this.$refs['search-control'].classList.contains('floating-search-control')))
                            this.$refs['search-control'].classList.remove('floating-search-control');
                    }
                }

                // Handles pagination bar
                if (this.$refs['items-pagination']) {
                    const bounding = this.$refs['items-pagination'].getBoundingClientRect();
                    const isHidden = !(bounding.top >= 0 && bounding.bottom <= (window.innerHeight || document.documentElement.clientHeight));
                      
                    if (isHidden && ($event.screenY + 100 >= window.screen.height)) {
                        if (!(this.$refs['items-pagination'].classList.contains('floating-pagination'))) {
                            this.$refs['items-pagination'].classList.add('floating-pagination');
                            if (this.$refs['items-pagination'].children[0]) {
                                this.$refs['items-pagination'].children[0].style.setProperty('width', 'calc(' + this.$refs['items-pagination'].clientWidth + 'px + 0.75em)');
                                this.$refs['items-pagination'].children[0].style.setProperty('margin-left', '0px');
                            }
                        }
                    } else {
                        this.$refs['items-pagination'].classList.remove('floating-pagination')
                        if (this.$refs['items-pagination'].children[0]) {
                            this.$refs['items-pagination'].children[0].style.removeProperty('width');
                            this.$refs['items-pagination'].children[0].style.removeProperty('margin');
                        }
                    }
                }
                
            }, 750),
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
            }
        }
    }
</script>

<style lang="scss" scoped>

    @import '../../scss/_variables.scss';

    .tainacan-page-title {
        padding: var(--tainacan-container-padding) var(--tainacan-one-column);
        margin: 0;
    }

    .repository-level-page {
        overflow-y: auto;
    }

    .advanced-search-form-submit {
        display: flex;
        justify-content: flex-end;
        padding-right: var(--tainacan-one-column);
        padding-left: var(--tainacan-one-column);
        margin-bottom: 1em;

        p { margin-left: 0.75em; }
    }

    .tnc-advanced-search-close {
        padding-top: var(--tainacan-container-padding);
        padding-right: var(--tainacan-one-column);
        padding-left: var(--tainacan-one-column);

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
        max-height: calc(100% - 5.875em);
        max-height: calc(100vh - 5.875em);
        float: left;
        overflow-y: auto;
        overflow-x: hidden;
        visibility: visible;
        display: block;

        /deep/ .filters-components-list {
            margin-left: 3px;
        }
        @media screen and (max-width: 768px) {
            width: 100%;
            padding: 0;
            
            #filters-items-list {
                padding: var(--tainacan-container-padding);
            }
        }
        @media screen and (min-width: 769px) {
            top: 1px !important;
            position: relative;
            position: sticky;
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
        min-height: 42px;
        height: auto;
        position: relative;
        padding: var(--tainacan-container-padding) var(--tainacan-one-column);
        display: flex;
        justify-content: space-between;
        flex-wrap: wrap;
        transition: top 0.3s, opacity 0.3s, padding 0.3s, height 0.3s, position 0.3s;

        &.floating-search-control {
            position: sticky;
            top: 0;
            z-index: 99999999;
            background: var(--tainacan-background-color);
            animation: appear-from-top 0.2s;
            opacity: 0.85;
            border-bottom: 1px solid var(--tainacan-gray2);
            padding: 20px var(--tainacan-one-column) 2px var(--tainacan-one-column);

            &:hover {
                opacity: 1;
            }

            .search-area .advanced-search-toggle {
                display: none;
            }
        }

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
                }
                .label {
                    display: flex;
                    align-items: center;
                }
            }

            .label {
                color: var(--tainacan-label-color);
                font-size: 0.875em;
                line-height: 1.75em;
                font-weight: normal;
                margin-top: 2px;
                margin-bottom: 2px;
                cursor: default;
            }

            .button {
                display: flex;
                align-items: center;
            }
            .button.is-white {
                padding: 2px 10px !important;
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
                max-width: 1.25em;
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

                .tainacan-textual-search-input {
                    width: 100%;
                    
                    /deep/ .dropdown-trigger {
                        width: 100%;
                    }
                    /deep/ .dropdown-menu {
                        z-index: 99999991;

                        .dropdown-item:last-child {
                            line-height: 2.25em;
                            background: var(--tainacan-item-hover-background-color);
                        }
                    }
                }

                .control {
                    width: 100%;
                    margin-bottom: 5px;
                }
                a.advanced-search-toggle {
                    margin-left: 12px;
                    white-space: nowrap; 
                    position: absolute;
                    font-size: 0.75em;
                    right: 15px;
                    left: unset;
                    top: 100%;
                    transition: font-size 0.2s ease, right 0.3s ease, left 0.3s ease, top 0.4s ease;
                    
                    .icon {
                        display: 0;
                        opacity: 0.0;
                        max-width: 0;
                        transition: opacity 0.2s ease, max-width 0.2s ease;                            
                    }

                    &.is-open {
                        font-size: 0;
                    }
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
        padding-left: var(--tainacan-one-column);
        padding-right: var(--tainacan-one-column);

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
        margin: 6px var(--tainacan-one-column);
        border-radius: 3px;
        padding: 4px 12px;
        color: var(--tainacan-yellow2);
        background: var(--tainacan-yellow1);
        animation-name: appear;
        animation-duration: 0.5s;

        p {
            margin: 0 auto !important;
            font-size: 0.885em;
        }
        
        &>div {
            display: flex;
            
            .button,
            .button:hover,
            .button:active,
            .button:focus {
                background: none !important;
                color: var(--tainacan-yellow2) !important;
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
        padding-left: var(--tainacan-one-column);
        padding-right: var(--tainacan-one-column);
        min-height: 50vh;
    }

    .pagination-area {
        margin-left: var(--tainacan-one-column);
        margin-right: var(--tainacan-one-column);
    }

    .floating-pagination {
        min-height: 42px;

        .pagination-area {
            opacity: 0.85;
            background: var(--tainacan-background-color);
            position: fixed;
            z-index: 99999;
            padding-left: calc(var(--tainacan-one-column) + 0.75em);
            padding-right: calc(var(--tainacan-one-column) + 0.75em);
            padding-bottom: 6px;
            bottom: -12px;
            animation: appear-from-bottom 0.2s;
            transition: bottom 0.3s, opacity 0.3s, position 0.3s;

            &:hover {
                opacity: 1;
            }
        }
    }

</style>


