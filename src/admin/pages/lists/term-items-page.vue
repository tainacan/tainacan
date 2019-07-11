<template>

    <div 
            :class="{
                'repository-level-page': isRepositoryLevel,
                'is-fullscreen': registeredViewModes[viewMode] != undefined && registeredViewModes[viewMode].full_screen
            }"
            aria-live="polite">

        <!-- SEARCH AND FILTERS --------------------- -->
        <!-- Filter menu compress button -->
        <button
                aria-controls="filters-desktop-aside"
                :aria-expanded="!isFiltersMenuCompressed"
                v-tooltip="{
                    delay: {
                        show: 500,
                        hide: 300,
                    },
                    content: isFiltersMenuCompressed ? $i18n.get('label_show_filters') : $i18n.get('label_hide_filters'),
                    autoHide: false,
                    placement: 'auto-start',
                    classes: ['tooltip', isRepositoryLevel ? 'repository-tooltip' : '']
                }"  
                v-if="!openAdvancedSearch && !(registeredViewModes[viewMode] != undefined && registeredViewModes[viewMode].full_screen)"
                class="is-hidden-mobile"
                id="filter-menu-compress-button"
                :aria-label="isFiltersMenuCompressed ? $i18n.get('label_show_filters') : $i18n.get('label_hide_filters')"
                :style="{ top: !isOnTheme ? (isRepositoryLevel ? '172px' : '120px') : '76px' }"
                @click="isFiltersMenuCompressed = !isFiltersMenuCompressed">
            <span class="icon">
                <i 
                        :class="{ 'tainacan-icon-arrowleft' : !isFiltersMenuCompressed, 'tainacan-icon-arrowright' : isFiltersMenuCompressed }"
                        class="tainacan-icon tainacan-icon-20px"/>
            </span>
        </button>
        <!-- Filters mobile modal button -->
        <button 
                aria-controls="filters-mobile-modal"
                :aria-expanded="!isFiltersMenuCompressed"
                v-if="!openAdvancedSearch && !(registeredViewModes[viewMode] != undefined && registeredViewModes[viewMode].full_screen)"
                class="is-hidden-tablet"
                id="filter-menu-compress-button-mobile"
                :aria-label="isFiltersMenuCompressed ? $i18n.get('label_show_filters') : $i18n.get('label_hide_filters')"
                :style="{ top: !isOnTheme ? (isRepositoryLevel ? (searchControlHeight + 100) : (searchControlHeight + 70) + 'px') : (searchControlHeight - 25) + 'px' }"
                @click="isFilterModalActive = !isFilterModalActive">
            <span class="icon">
                <i 
                        :class="{ 'tainacan-icon-arrowleft' : !isFiltersMenuCompressed, 'tainacan-icon-arrowright' : isFiltersMenuCompressed }"
                        class="tainacan-icon tainacan-icon-20px"/>
            </span>
            <span class="text">{{ $i18n.get('filters') }}</span>
        </button>

        <!-- Side bar with search and filters -->
        <aside
                :aria-busy="isLoadingFilters"
                id="filters-desktop-aside"
                role="region"
                aria-labelledby="filters-label-landmark"
                :style="{ top: searchControlHeight + 'px' }"
                v-show="!isFiltersMenuCompressed && 
                        !openAdvancedSearch && 
                        !(registeredViewModes[viewMode] != undefined && registeredViewModes[viewMode].full_screen)"
                class="filters-menu tainacan-form is-hidden-mobile">
            <!-- <b-loading
                    :is-full-page="false"
                    :active.sync="isLoadingFilters"/> -->

            <div class="search-area is-hidden-mobile">
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
                                @click="updateSearch()"
                                class="icon is-right">
                            <i class="tainacan-icon tainacan-icon-20px tainacan-icon-search"/>
                        </span>
                </div>
            </div>
            <!-- <button
                    @click="openAdvancedSearch = !openAdvancedSearch"
                    arial-controls="advanced-search-container"
                    :aria-expanded="openAdvancedSearch"
                    class="link-style is-size-7 is-pulled-right is-hidden-mobile">
                {{ $i18n.get('advanced_search') }}
            </button> -->

            <h3 
                    id="filters-label-landmark"
                    class="has-text-weight-semibold">
                {{ $i18n.get('filters') }}
            </h3>
            <button
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

            <filters-items-list
                    v-if="!isLoadingFilters &&
                    ((filters.length >= 0 &&
                    isRepositoryLevel) || filters.length > 0)"
                    :filters="filters"
                    :taxonomy-filters="taxonomyFilters"
                    :taxonomy="taxonomy"
                    :collapsed="collapseAll"
                    :is-repository-level="isRepositoryLevel"/>
            <section
                    v-else
                    class="is-grouped-centered section">
                <div class="content has-text-gray has-text-centered">
                    <p>
                        <span class="icon is-large">
                            <i class="tainacan-icon tainacan-icon-36px tainacan-icon-filters" />
                        </span>
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
                :class="{ 'spaced-to-right': !isFiltersMenuCompressed && !openAdvancedSearch && !(registeredViewModes[viewMode] != undefined && registeredViewModes[viewMode].full_screen)}">

            <!-- SEARCH CONTROL ------------------------- -->
            <div
                    :aria-label="$i18n.get('label_sort_visualization')"
                    role="region"
                    ref="search-control"
                    v-if="!openAdvancedSearch && !(registeredViewModes[viewMode] != undefined && registeredViewModes[viewMode].full_screen)"
                    class="search-control">
                <!-- <b-loading
                        :is-full-page="false"
                        :active.sync="isLoadingMetadata"/> -->
                <!-- Item Creation Dropdown, only on Admin -->
                <div 
                        class="search-control-item"
                        v-if="!isOnTheme && !$route.query.iframemode">
                    <b-dropdown 
                            :mobile-modal="true"
                            id="item-creation-options-dropdown"
                            aria-role="list">
                        <button
                                class="button is-secondary"
                                slot="trigger">
                            <span>{{ $i18n.getFrom('items','add_new') }}</span>
                            <span class="icon">
                                <i class="has-text-secondary tainacan-icon tainacan-icon-20px tainacan-icon-arrowdown"/>
                            </span>
                        </button>

                        <b-dropdown-item aria-role="listitem">
                            <router-link
                                    id="a-create-item"
                                    tag="div"
                                    :to="{ path: $routerHelper.getNewItemPath(collectionId) }">
                                {{ $i18n.get('add_one_item') }}
                            </router-link>
                        </b-dropdown-item>
                        <b-dropdown-item 
                                aria-role="listitem"
                                disabled>
                            {{ $i18n.get('add_items_bulk') + ' (Not ready)' }}
                        </b-dropdown-item>
                        <b-dropdown-item 
                                aria-role="listitem"
                                disabled>
                            {{ $i18n.get('add_items_external_source') + ' (Not ready)' }}
                        </b-dropdown-item>
                        <b-dropdown-item aria-role="listitem">
                            <div
                                    id="a-import-collection"
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
                <div    
                        v-if="!isOnTheme || (registeredViewModes[viewMode] != undefined && registeredViewModes[viewMode].dynamic_metadata)"
                        class="search-control-item">
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
                            aria-role="list">
                        <button
                                :aria-label="$i18n.get('label_displayed_metadata')"
                                class="button is-white"
                                slot="trigger">
                            <span>{{ $i18n.get('label_displayed_metadata') }}</span>
                            <span class="icon">
                                <i class="tainacan-icon tainacan-icon-20px tainacan-icon-arrowdown"/>
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
                        <label class="label">{{ $i18n.get('label_sorting_direction') }}</label>
                        <b-dropdown
                                :mobile-modal="true"
                                @input="onChangeOrder()"
                                aria-role="list">
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
                                    <i class="tainacan-icon tainacan-icon-20px tainacan-icon-arrowdown" />
                                </span>
                            </button>
                            <b-dropdown-item
                                    aria-controls="items-list-results"
                                    role="button"
                                    :class="{ 'is-active': order == 'DESC' }"
                                    :value="'DESC'"
                                    aria-role="listitem"
                                    style="padding-bottom: 0.45rem">
                                <span class="icon is-small gray-icon">
                                    <i class="tainacan-icon tainacan-icon-18px tainacan-icon-sortdescending"/>
                                </span>
                                {{ $i18n.get('label_descending') }}
                            </b-dropdown-item>
                            <b-dropdown-item
                                    aria-controls="items-list-results"
                                    role="button"
                                    :class="{ 'is-active': order == 'ASC' }"
                                    :value="'ASC'"
                                    aria-role="listitem"
                                    style="padding-bottom: 0.45rem">
                                <span class="icon is-small gray-icon">
                                    <i class="tainacan-icon tainacan-icon-18px tainacan-icon-sortascending"/>
                                </span>
                                {{ $i18n.get('label_ascending') }}
                            </b-dropdown-item>
                        </b-dropdown>
                        <span
                                class="label"
                                style="padding-left: 0.65rem;">
                            {{ $i18n.get('info_by_inner') }}
                        </span>
                        <b-dropdown
                                :mobile-modal="true"
                                @input="onChangeOrderBy($event)"
                                aria-role="list">
                            <button
                                    :aria-label="$i18n.get('label_sorting')"
                                    class="button is-white"
                                    slot="trigger">
                                <span>{{ orderByName }}</span>
                                <span class="icon">
                                    <i class="tainacan-icon tainacan-icon-20px tainacan-icon-arrowdown" />
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

                <!-- View Modes Dropdown -->
                <div 
                        v-if="isOnTheme"
                        class="search-control-item">
                    <b-field>
                        <label class="label is-hidden-mobile">{{ $i18n.get('label_visualization') + ':&nbsp; ' }}</label>
                        <b-dropdown
                                @change="onChangeViewMode($event)"
                                :mobile-modal="true"
                                position="is-bottom-left"
                                :aria-label="$i18n.get('label_view_mode')"
                                aria-role="list">
                            <button 
                                    class="button is-white" 
                                    :aria-label="registeredViewModes[viewMode] != undefined ? registeredViewModes[viewMode].label : $i18n.get('label_visualization')"
                                    slot="trigger">
                                <span 
                                        class="gray-icon view-mode-icon"
                                        v-if="registeredViewModes[viewMode] != undefined"
                                        v-html="registeredViewModes[viewMode].icon"/>
                                <span class="is-hidden-touch">&nbsp;&nbsp;&nbsp;{{ registeredViewModes[viewMode] != undefined ? registeredViewModes[viewMode].label : $i18n.get('label_visualization') }}</span>
                                <span class="icon">
                                    <i class="tainacan-icon tainacan-icon-20px tainacan-icon-arrowdown" />
                                </span>
                            </button>
                            <b-dropdown-item 
                                    aria-controls="items-list-results"
                                    role="button"
                                    :class="{ 'is-active': viewModeOption == viewMode }"
                                    v-for="(viewModeOption, index) of enabledViewModes"
                                    :key="index"
                                    :value="viewModeOption"
                                    v-if="registeredViewModes[viewModeOption] != undefined && registeredViewModes[viewModeOption].full_screen == false"
                                    aria-role="listitem">
                                <span 
                                        class="gray-icon"
                                        v-html="registeredViewModes[viewModeOption].icon"/>
                                <span>{{ registeredViewModes[viewModeOption].label }}</span>
                            </b-dropdown-item>
                        </b-dropdown>
                    </b-field>
                </div>
                <div
                        v-if="!isOnTheme"
                        class="search-control-item">
                    <b-field>
                        <label class="label is-hidden-mobile">{{ $i18n.get('label_visualization') + ':' }}</label>
                        <b-dropdown
                                @change="onChangeAdminViewMode($event)"
                                :mobile-modal="true"
                                position="is-bottom-left"
                                :aria-label="$i18n.get('label_view_mode')"
                                aria-role="list">
                            <button
                                    class="button is-white"
                                    :aria-label="$i18n.get('label_view_mode')"
                                    slot="trigger">
                                <span>
                                        <span class="view-mode-icon icon is-small gray-icon">
                                        <i 
                                                :class="{'tainacan-icon-viewtable' : ( adminViewMode == 'table' || adminViewMode == undefined),
                                                        'tainacan-icon-viewcards' : adminViewMode == 'cards',
                                                        'tainacan-icon-viewminiature' : adminViewMode == 'grid',
                                                        'tainacan-icon-viewrecords' : adminViewMode == 'records',
                                                        'tainacan-icon-viewmasonry' : adminViewMode == 'masonry'}"
                                                class="tainacan-icon"/>
                                    </span>
                                </span>
                                &nbsp;&nbsp;&nbsp;{{ adminViewMode != undefined ? $i18n.get('label_' + adminViewMode) : $i18n.get('label_table') }}
                                <span class="icon">
                                    <i class="tainacan-icon tainacan-icon-20px tainacan-icon-arrowdown"/>
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

                <!-- Theme Full Screen mode, it's just a special view mode -->
                <div 
                        v-if="isOnTheme"
                        class="search-control-item">
                    <button 
                            class="button is-white"
                            :aria-label="$i18n.get('label_slideshow')"
                            @click="onChangeViewMode(viewModeOption)"
                            v-for="(viewModeOption, index) of enabledViewModes"
                            :key="index"
                            :value="viewModeOption"
                            v-if="registeredViewModes[viewModeOption] != undefined && registeredViewModes[viewModeOption].full_screen == true ">
                        <span 
                                class="gray-icon"
                                v-html="registeredViewModes[viewModeOption].icon"/>
                        <span class="is-hidden-touch">{{ registeredViewModes[viewModeOption].label }}</span>
                    </button>
                </div>

                <!-- Exposers or alternative links modal button -->
                <div
                        v-if="!$route.query.iframemode"
                        class="search-control-item">
                    <button 
                            class="button is-white"
                            :aria-label="$i18n.get('label_urls')"
                            :disabled="totalItems == undefined || totalItems <= 0"
                            @click="openExposersModal()">
                        <span class="gray-icon">
                                <i class="tainacan-icon tainacan-icon-20px tainacan-icon-url"/>
                        </span>
                        <span class="is-hidden-touch">{{ $i18n.get('label_urls') }}</span>
                    </button>
                </div>

                <!-- Text simple search (used on mobile, instead of the one from filter list)-->
                <div class="is-hidden-tablet search-control-item">
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
                                    <i class="tainacan-icon tainacan-icon-20px tainacan-icon-search"/>
                                </span>
                        </div>
                        <!-- <a
                                @click="openAdvancedSearch = !openAdvancedSearch"
                                class="is-size-7 has-text-secondary is-pulled-right">{{ $i18n.get('advanced_search') }}</a> -->
                    </div>
                </div>
            </div>

            <!-- ADVANCED SEARCH -->
            <div 
                    id="advanced-search-container"
                    role="search"
                    v-if="openAdvancedSearch">

                <div class="tnc-advanced-search-close"> 
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

                <advanced-search
                        :is-repository-level="isRepositoryLevel"
                        :collection-id="collectionId"
                        :advanced-search-results="advancedSearchResults"
                        :open-form-advanced-search="openFormAdvancedSearch"
                        :is-do-search="isDoSearch"
                        :metadata="metadata"/>

                <div class="advanced-searh-form-submit">
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
            <div 
                    v-if="!isOnTheme && !openAdvancedSearch"
                    class="tabs">
                <ul>
                    <li 
                            @click="onChangeTab('')"
                            :class="{ 'is-active': status == undefined || status == ''}"
                            v-tooltip="{
                                content: $i18n.get('info_items_tab_all'),
                                autoHide: true,
                                placement: 'auto',
                            }">
                        <a :style="{ fontWeight: 'bold', color: '#454647 !important', lineHeight: '1.5rem' }">
                            {{ $i18n.get('label_all_published_items') }}
                            <span class="has-text-gray">&nbsp;{{ collection && collection.total_items ? ` (${Number(collection.total_items.private) + Number(collection.total_items.publish)})` : (isRepositoryLevel && repositoryTotalItems) ? ` (${ repositoryTotalItems.private + repositoryTotalItems.publish })` : '' }}</span>
                        </a>
                    </li>
                    <li 
                            v-for="(statusOption, index) of $statusHelper.getStatuses()"
                            v-if="(isRepositoryLevel || statusOption.slug != 'private') || (statusOption.slug == 'private' && $userCaps.hasCapability('read_private_tnc_col_' + collectionId + '_items'))"
                            :key="index"
                            @click="onChangeTab(statusOption.slug)"
                            :class="{ 'is-active': status == statusOption.slug}"
                            :style="{ marginRight: statusOption.slug == 'private' ? 'auto' : '', marginLeft: statusOption.slug == 'draft' ? 'auto' : '' }"
                            v-tooltip="{
                                content: $i18n.getWithVariables('info_%s_tab_' + statusOption.slug,[$i18n.get('items')]),
                                autoHide: true,
                                placement: 'auto',
                            }">
                        <a>
                            <span 
                                    v-if="$statusHelper.hasIcon(statusOption.slug)"
                                    class="icon has-text-gray">
                                <i 
                                        class="tainacan-icon tainacan-icon-18px"
                                        :class="$statusHelper.getIcon(statusOption.slug)"
                                        />
                            </span>
                            {{ statusOption.name }}
                            <span class="has-text-gray">&nbsp;{{ collection && collection.total_items ? ` (${collection.total_items[statusOption.slug]})` : (isRepositoryLevel && repositoryTotalItems) ? ` (${ repositoryTotalItems[statusOption.slug] })` : '' }}</span>
                        </a>
                    </li>
                </ul>
            </div>

            <!-- FILTERS TAG LIST-->
            <filters-tags-list 
                    class="filter-tags-list"
                    :filters="filters"
                    v-if="hasFiltered && 
                        !openAdvancedSearch &&
                        !(registeredViewModes[viewMode] != undefined && registeredViewModes[viewMode].full_screen)" />

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
                                !(registeredViewModes[viewMode] != undefined && (registeredViewModes[viewMode].full_screen == true || registeredViewModes[viewMode].implements_skeleton == true)))"
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
                            
                    <!-- Admin view modes skeleton -->
                    <!-- <skeleton-items-list v-if="!isOnTheme"/>          -->
                </div>  
                
                <!-- Admin View Modes-->
                <items-list
                        v-if="!isOnTheme && 
                              !isLoadingItems &&
                              totalItems > 0 &&
                              ((openAdvancedSearch && advancedSearchResults) || !openAdvancedSearch)"
                        :collection-id="collectionId"
                        :table-metadata="displayedMetadata"
                        :items="items"
                        :total-items="totalItems"
                        :is-loading="isLoadingItems"
                        :is-on-trash="status == 'trash'"
                        :view-mode="adminViewMode"
                        @updateIsLoading="newIsLoading => isLoadingItems = newIsLoading"/>
                
                <!-- Theme View Modes -->
                <div 
                        v-if="isOnTheme &&
                              ((openAdvancedSearch && advancedSearchResults) || !openAdvancedSearch) &&
                              !isLoadingItems &&
                              registeredViewModes[viewMode] != undefined &&
                              registeredViewModes[viewMode].type == 'template'"
                        v-html="itemsListTemplate"/>

                <component
                        v-if="isOnTheme && 
                              registeredViewModes[viewMode] != undefined &&
                              registeredViewModes[viewMode].type == 'component' &&
                              ((openAdvancedSearch && advancedSearchResults) || !openAdvancedSearch)"
                        :collection-id="collectionId"
                        :displayed-metadata="displayedMetadata" 
                        :items="items"
                        :is-filters-menu-compressed="isFiltersMenuCompressed"
                        :total-items="totalItems"
                        :is-loading="showLoading"
                        :is="registeredViewModes[viewMode] != undefined ? registeredViewModes[viewMode].component : ''"/>     

                <!-- Empty Placeholder (only used in Admin) -->
                <section
                        v-if="!isOnTheme && !isLoadingItems && totalItems == 0"
                        class="section">
                    <div class="content has-text-grey has-text-centered">
                        <p>
                            <span class="icon is-large">
                                <i class="tainacan-icon tainacan-icon-36px tainacan-icon-items" />
                            </span>
                        </p>
                        <p v-if="status == undefined || status == ''">{{ hasFiltered ? $i18n.get('info_no_item_found_filter') : $i18n.get('info_no_item_created') }}</p>
                        <p
                                v-for="(statusOption, index) of $statusHelper.getStatuses()"
                                :key="index"
                                v-if="status == statusOption.slug">
                            {{ $i18n.get('info_no_items_' + statusOption.slug) }}
                        </p>

                        <router-link
                                v-if="!hasFiltered && (status == undefined || status == '')"
                                id="button-create-item"
                                tag="button"
                                class="button is-secondary"
                                :to="{ path: $routerHelper.getNewItemPath(collectionId) }">
                            {{ $i18n.getFrom('items', 'add_new') }}
                        </router-link>
                    </div>
                </section>
        
                <!-- Pagination -->
                <pagination
                        :is-sorting-by-custom-metadata="isSortingByCustomMetadata"
                        v-if="totalItems > 0 &&
                         (!isOnTheme || (registeredViewModes[viewMode] != undefined && registeredViewModes[viewMode].show_pagination)) &&
                          (advancedSearchResults || !openAdvancedSearch)"/>
            </div>
        </div>
       
        <b-modal
                role="region"
                aria-labelledby="filters-label-landmark-modal"
                id="filters-mobile-modal"
                ref="filters-mobile-modal"
                class="tainacan-form is-hidden-tablet"                
                :active.sync="isFilterModalActive"
                :width="736"
                animation="slide-menu">
            <div class="modal-inner-content">
                <h3 
                        id="filters-label-landmark-modal"
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

                <filters-items-list
                        id="filters-items-list"
                        v-if="!isLoadingFilters &&
                        ((filters.length >= 0 &&
                        isRepositoryLevel) || filters.length > 0)"
                        :filters="filters"
                        :taxonomy-filters="taxonomyFilters"
                        :taxonomy="taxonomy"
                        :collapsed="collapseAll"
                        :is-repository-level="isRepositoryLevel"/>

                <section
                        v-else
                        class="is-grouped-centered section">
                    <div class="content has-text-gray has-text-centered">
                        <p>
                            <span class="icon is-large">
                                <i class="tainacan-icon tainacan-icon-36px tainacan-icon-filters" />
                            </span>
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
            </div>
        </b-modal>
    </div>
</template>

<script>
    import ItemsList from '../../components/lists/items-list.vue';
    import FiltersTagsList from '../../components/search/filters-tags-list.vue';
    import FiltersItemsList from '../../components/search/filters-items-list.vue';
    import Pagination from '../../components/search/pagination.vue'
    import AdvancedSearch from '../../components/advanced-search/advanced-search.vue';
    import ExposersModal from '../../components/other/exposers-modal.vue';
    import AvailableImportersModal from '../../components/other/available-importers-modal.vue';
    import CustomDialog from '../../components/other/custom-dialog.vue';
    import { mapActions, mapGetters } from 'vuex';

    export default {
        name: 'ItemsPage',
        data() {
            return {
                isRepositoryLevel: false,
                displayedMetadata: [],
                prefDisplayedMetadata: [],
                isLoadingItems: false,
                isLoadingFilters: false,
                isLoadingMetadata: false,
                hasFiltered: false,
                isFiltersMenuCompressed: false,
                collapseAll: true,
                isOnTheme: false,
                futureSearchQuery: '',
                localDisplayedMetadata: [],
                registeredViewModes: tainacan_plugin.registered_view_modes,
                openAdvancedSearch: false,
                openFormAdvancedSearch: false,
                advancedSearchResults: false,
                isDoSearch: false,
                searchControlHeight: 0,
                sortingMetadata: [],
                isFilterModalActive: false,
                customFilters: []
            }
        },
        props: {
            collectionId: Number,
            termId: Number,
            taxonomy: String,
            defaultViewMode: String, // Used only on theme
            enabledViewModes: Object // Used only on theme,
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
                return this.getTotalItems();
            },
            filters() {
                return this.getFilters();
            },
            taxonomyFilters() {
                return this.getTaxonomyFilters();
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
            },
            showLoading() {
                return this.isLoadingItems || this.isLoadingFilters || this.isLoadingMetadata;
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
        components: {
            ItemsList,
            FiltersTagsList,
            FiltersItemsList,
            Pagination,
            AdvancedSearch,
            ExposersModal
        },
        watch: {
            displayedMetadata() {
                this.localDisplayedMetadata = JSON.parse(JSON.stringify(this.displayedMetadata));
            },
            openAdvancedSearch(newValue){
                if (newValue == false){
                    this.$eventBusSearch.$emit('closeAdvancedSearch');
                    this.advancedSearchResults = false;
                } else {
                    this.$eventBusSearch.clearAllFilters();
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
                'fetchFilters',
                'fetchTaxonomyFilters'
            ]),
            ...mapGetters('filter', [
                'getFilters',
                'getTaxonomyFilters'
            ]),
            ...mapGetters('search', [
                'getSearchQuery',
                'getStatus',
                'getOrderBy',
                'getOrderByName',
                'getOrder',
                'getViewMode',
                'getTotalItems',
                'getAdminViewMode',
                'getMetaKey'
            ]),
            onSwipeFiltersMenu($event) {
                if (this.registeredViewModes[this.viewMode] == undefined || 
                    (this.registeredViewModes[this.viewMode] != undefined && 
                        (this.registeredViewModes[this.viewMode].full_screen == false || 
                        this.registeredViewModes[this.viewMode].full_screen == undefined)
                    )
                   ) {
                    let screenWidth = (window.innerWidth || document.documentElement.clientWidth || document.body.clientWidth);

                    if ($event.offsetDirection == 4 && screenWidth <= 768) {
                        if (!this.isFilterModalActive)
                            this.isFilterModalActive = true;
                    } else if ($event.offsetDirection == 2 && screenWidth <= 768) {
                        if (this.isFilterModalActive)
                            this.isFilterModalActive = false;
                    }
                }
            },
            onOpenImportersModal() {
                this.$modal.open({
                    parent: this,
                    component: AvailableImportersModal,
                    hasModalCard: true,
                    props: { 
                        targetCollection: this.collectionId,
                        hideWhenManualCollection: true
                    }
                });
            },
            openExposersModal() {
                this.$modal.open({
                    parent: this,
                    component: ExposersModal,
                    hasModalCard: true,
                    props: { 
                        collectionId: this.collectionId,
                        totalItems: this.totalItems
                    }
                })
            },
            updateSearch() {
                this.$eventBusSearch.setSearchQuery(this.futureSearchQuery);
            },  
            onChangeOrderBy(metadatum) {
                this.$eventBusSearch.setOrderBy(metadatum);
                this.showItemsHiddingDueSorting();
            },
            onChangeOrder() {
                this.order == 'DESC' ? this.$eventBusSearch.setOrder('ASC') : this.$eventBusSearch.setOrder('DESC');
            },
            onChangeTab(status) {
                this.$eventBusSearch.setStatus(status);
            },
            onChangeViewMode(viewMode) {
                // We need to load metadata again as fetch_only might change from view mode
                this.prepareMetadata();
                this.$eventBusSearch.setViewMode(viewMode);

                // For view modes such as slides, we force pagination to request only 12 per page
                let existingViewModeIndex = Object.keys(this.registeredViewModes).findIndex(aViewMode => aViewMode == viewMode);
                if (existingViewModeIndex >= 0) {
                    if (!this.registeredViewModes[Object.keys(this.registeredViewModes)[existingViewModeIndex]].show_pagination) {
                        this.$eventBusSearch.setItemsPerPage(12);
                    }
                }

                // Updates searchControlHeight before in case we need to adjust filters position on mobile
                setTimeout(() => {
                    if (this.$refs['search-control'] != undefined)
                        this.searchControlHeight = this.$refs['search-control'].clientHeight;
                }, 500);
            },
            onChangeAdminViewMode(adminViewMode) {
                 // We need to load metadata again as fetch_only might change from view mode
                this.prepareMetadata();
                this.$eventBusSearch.setAdminViewMode(adminViewMode);

                // Updates searchControlHeight before in case we need to adjust filters position on mobile
                setTimeout(() => {
                    if (this.$refs['search-control'] != undefined)
                        this.searchControlHeight = this.$refs['search-control'].clientHeight;
                }, 500);
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
            prepareFilters() {
                
                this.isLoadingFilters = true;

                // Normal filter loading, only collection ones
                if (this.taxonomy == undefined) {
                    this.fetchFilters({
                        collectionId: this.collectionId,
                        isRepositoryLevel: this.isRepositoryLevel,
                        isContextEdit: !this.isOnTheme,
                        includeDisabled: 'no',
                    })
                        .then(() => this.isLoadingFilters = false)
                        .catch(() => this.isLoadingFilters = false);
                
                // Custom filter loading, get's from collections that have items with that taxonomy
                } else {
                    let taxonomyId = this.taxonomy.split("_");
                    this.fetchTaxonomyFilters(taxonomyId[taxonomyId.length - 1])
                        .catch(() => this.isLoadingFilters = false);
                        
                }
            },
            prepareMetadata() {

                this.$eventBusSearch.cleanFetchOnly();
                this.isLoadingMetadata = true;
               
                // Processing is done inside a local variable
                let metadata = [];
                this.fetchMetadata({
                    collectionId: this.collectionId,
                    isRepositoryLevel: this.isRepositoryLevel,
                    isContextEdit: false
                })
                    .then(() => {
                        this.sortingMetadata = [];

                        // Decides if custom meta will be loaded with item.
                        let shouldLoadMeta = true;
                        
                        if (this.isOnTheme)
                            shouldLoadMeta = this.registeredViewModes[this.viewMode].dynamic_metadata;
                        else
                            shouldLoadMeta  = this.adminViewMode == 'table' || this.adminViewMode == 'records' || this.adminViewMode == undefined;
                    
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

                                    if (display) {
                                        fetchOnlyMetadatumIds.push(metadatum.id);     
                                    }

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
                            if (!this.isOnTheme) {
                             
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
                            }
                        
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
                    });
            },
            showItemsHiddingDueSorting() {

                if (this.isSortingByCustomMetadata &&
                    this.$userPrefs.get('neverShowItemsHiddenDueSortingDialog') != true) {     

                    this.hasAnOpenModal = true;

                    this.$modal.open({
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
                            showNeverShowAgainOption: tainacan_plugin.user_caps != undefined && tainacan_plugin.user_caps.length != undefined && tainacan_plugin.user_caps.length > 0,
                            messageKeyForUserPrefs: 'ItemsHiddenDueSorting'
                        }
                    });
                }
            },
            adjustSearchControlHeight: _.debounce( function() {
                this.$nextTick(() => {
                        if (this.$refs['search-control'] != undefined)
                        this.searchControlHeight = this.$refs['search-control'] ? this.$refs['search-control'].clientHeight + this.$refs['search-control'].offsetTop : 0;
                    this.isFiltersMenuCompressed = jQuery(window).width() <= 768;
                });
            }, 500),
            removeEventListeners() {
                // Component
                this.$off();
                // Window
                window.removeEventListener('resize', this.adjustSearchControlHeight);
                // $root
                this.$root.$off('openAdvancedSearch');
                // $eventBusSearch
                this.$eventBusSearch.$off('isLoadingItems');
                this.$eventBusSearch.$off('hasFiltered');
                this.$eventBusSearch.$off('advancedSearchResults');
                this.$eventBusSearch.$off('hasToPrepareMetadataAndFilters');

            },
        },
        created() {

            this.isOnTheme = (this.$route.name === null);

            this.isRepositoryLevel = (this.collectionId === undefined);

            if (this.collectionId != undefined)
                this.$eventBusSearch.setCollectionId(this.collectionId);

            if (this.termId != undefined && this.termId != null)
                this.$eventBusSearch.setTerm(this.termId, this.taxonomy);
            
            this.$eventBusSearch.updateStoreFromURL();

            this.$eventBusSearch.$on('isLoadingItems', isLoadingItems => {
                this.isLoadingItems = isLoadingItems;
            });

            this.$eventBusSearch.$on('hasFiltered', hasFiltered => {
                this.adjustSearchControlHeight();
                this.hasFiltered = hasFiltered;
            });

            this.$eventBusSearch.$on('advancedSearchResults', advancedSearchResults => {
                this.advancedSearchResults = advancedSearchResults;
            });

            this.$eventBusSearch.$on('hasToPrepareMetadataAndFilters', (to) => {
                /* This condition is to prevent an incorrect fetch by filter or metadata when we come from items
                 * at collection level to items page at repository level
                 */

                if (this.isOnTheme || this.collectionId == to.params.collectionId) {
                    this.prepareMetadata();
                    this.prepareFilters();
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
            
            this.prepareFilters();
            this.prepareMetadata();
            this.localDisplayedMetadata = JSON.parse(JSON.stringify(this.displayedMetadata));

            // Setting initial view mode on Theme
            if (this.isOnTheme) {
                let prefsViewMode = !this.isRepositoryLevel ? 'view_mode_' + this.collectionId : 'view_mode';
                if (this.$userPrefs.get(prefsViewMode) == undefined)
                    this.$eventBusSearch.setInitialViewMode(this.defaultViewMode);
                else {
                    let existingViewModeIndex = Object.keys(this.registeredViewModes).findIndex(viewMode => viewMode == this.$userPrefs.get(prefsViewMode));
                    if (existingViewModeIndex >= 0)
                        this.$eventBusSearch.setInitialViewMode(this.$userPrefs.get(prefsViewMode));
                    else   
                        this.$eventBusSearch.setInitialViewMode(this.defaultViewMode);
                }
                
                // For view modes such as slides, we force pagination to request only 12 per page
                let existingViewModeIndex = Object.keys(this.registeredViewModes).findIndex(viewMode => viewMode == this.$userPrefs.get(prefsViewMode));
                if (existingViewModeIndex >= 0) {
                    if (!this.registeredViewModes[Object.keys(this.registeredViewModes)[existingViewModeIndex]].show_pagination) {
                        this.$eventBusSearch.setItemsPerPage(12);
                    }
                }

            } else {
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
            }

            this.showItemsHiddingDueSorting();

            // Watches window resize to adjust filter's top position and compression on mobile 
            this.adjustSearchControlHeight();
            window.addEventListener('resize', this.adjustSearchControlHeight);
        },
        beforeDestroy() {
            this.removeEventListeners();
                        
            // Cancels previous Request
            if (this.$eventBusSearch.searchCancel != undefined)
                this.$eventBusSearch.searchCancel.cancel('Item search Canceled.');

        }
    }
</script>

<style lang="scss" scoped>

    @import '../../scss/_variables.scss';

    @keyframes open-full-screen {
        from {
            opacity: 0;
            transform: scale(0.6);
        }
        to {
            opacity: 1;
            transform: scale(1.0);
        }
    }

    .is-fullscreen {
        position: fixed;
        top: 0;
        bottom: 0;
        left: 0;
        right: 0;
        width: 100%;
        height: 100%;
        width: 100vw;
        height: 100vh;
        z-index: 999999999;
        background-color: black;
        transition: background-color 0.3s ease, width 0.3s ease, height 0.3s ease;
        animation: open-full-screen 0.4s ease;
    }

    .collapse-all {
        display: inline-flex;
        align-items: center;
        font-size: 0.75rem !important;
    }

    .advanced-search-criteria-title {
       margin-bottom: 40px;

        h1, h2 {
            font-size: 20px;
            font-weight: 500;
            color: $gray5;
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
            background-color: $secondary;
        }
    }

    .advanced-search-results-title {
       margin-bottom: 40px;
        margin: 0 $page-side-padding 42px $page-side-padding;

        h1, h2 {
            font-size: 20px;
            font-weight: 500;
            color: $gray5;
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
        padding: 0;
    }

    .filters-menu {
        position: relative;
        z-index: 10;
        background-color: white;
        width: $filter-menu-width;
        min-width: 180px;
        min-height: 100%;
        height: 100%;
        padding: $page-small-side-padding;
        float: left;
        overflow-y: auto;
        overflow-x: hidden;
        visibility: visible;
        display: block;

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
                    color: $blue5;
                    height: 27px;
                    font-size: 1.125rem !important;
                    height: 2rem !important;
                }
                margin-bottom: 5px;
            }
        }

        .label {
            font-size: 0.75rem;
            font-weight: normal;
        }

        .checkbox {
            margin-bottom: 5px;
            align-items: baseline;
        }

    }
    #filter-menu-compress-button,
    #filter-menu-compress-button-mobile {
        position: absolute;
        z-index: 99;
        top: 120px;
        left: 0;
        max-width: 23px;
        height: 26px;
        width: 23px;
        border: none;
        background-color: $turquoise1;
        color: $turquoise5;
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
            height: 26px;

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

    .spaced-to-right {
        margin-left: $filter-menu-width;

        @media screen and (max-width: 768px) {
            margin-left: 0px !important;
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

    }

    .search-control-item {
        display: inline-block;
        margin-bottom: 12px;

        &:last-child {
            flex-grow: 1;
            flex-basis: 100%;
        }

        .label {
            font-size: 0.875rem;
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

        .gray-icon, .gray-icon .icon {
            color: $gray4 !important;
            padding-right: 10px;
        }
        .gray-icon .icon i::before, 
        .gray-icon i::before {
            font-size: 1.3125rem !important;
            max-width: 26px;
        }
        
        .view-mode-icon {
            margin-right: 3px !important;
            margin-top: 1px;
            margin-left: 6px !important;
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
                    padding: 0.25rem 1.0rem 0.25rem 0.75rem;
                }
                .dropdown-item span{
                    vertical-align: middle;
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

        .search-area {
            display: flex;
            align-items: center;
            width: 100%;

            .input {
                border: 1px solid $gray2;
            }
            .control {
                width: 100%;
                .icon {
                    pointer-events: all;
                    cursor: pointer;
                    color: $blue5;
                    height: 27px;
                    font-size: 1.125rem !important;
                    height: 2rem !important;
                }
            }
            a {
                margin-left: 12px;
                white-space: nowrap; 
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
            min-height: 200px;
            height: auto;
        }
    }

    .tabs {
        padding-top: 6px;
        margin-bottom: 20px;
        padding-left: $page-side-padding;
        padding-right: $page-side-padding;
    }

    .items-list-area {
        margin-left: 0;
        height: 100%;
        overflow: auto;
        position: relative;
    }

    .table-container {
        padding-left: 4.166666667%;
        padding-right: 4.166666667%;
        min-height: 50vh;
        //height: calc(100% - 82px);
    }

    .pagination-area {
        margin-left: $page-side-padding;
        margin-right: $page-side-padding;
    }



</style>


