<template>
    <h2 
            id="items-list-container-landmark"
            class="sr-only">
        {{ $i18n.get('label_items_list') }}
    </h2>

    <!-- SEARCH CONTROL ------------------------- -->
    <div
            v-if="!(registeredViewModes[viewMode] != undefined && registeredViewModes[viewMode].full_screen)"
            ref="search-control"
            :aria-label="$i18n.get('label_sort_visualization')"
            role="region"
            class="search-control">
        
        <h3 
                id="search-control-landmark"
                class="sr-only">
            {{ $i18n.get('label_sort_visualization') }}
        </h3>

        <!-- JS-side hook for extra form content -->
        <div 
                v-if="hooks['search_control_before']"
                class="faceted-search-hook faceted-search-hook-search-control-before"
                v-html="hooks['search_control_before']" />

        <!-- Button for hiding filters -->
        <button 
                v-if="!showFiltersButtonInsideSearchControl && !hideFilters && !(registeredViewModes[viewMode] != undefined && registeredViewModes[viewMode].full_screen)"
                id="filter-menu-compress-button"
                v-tooltip="{
                    delay: {
                        show: 500,
                        hide: 300,
                    },
                    content: !isFiltersModalActive ? $i18n.get('label_show_filters') : $i18n.get('label_hide_filters'),
                    autoHide: false,
                    placement: 'auto-start',
                    popperClass: ['tainacan-tooltip', 'tooltip', isRepositoryLevel ? 'tainacan-repository-tooltip' : '']
                }"
                aria-controls="filters-modal"
                :aria-expanded="isFiltersModalActive"
                :class="{
                    'is-hidden-tablet': !shouldNotHideFiltersOnMobile && hideHideFiltersButton,
                    'is-hidden': shouldNotHideFiltersOnMobile && hideHideFiltersButton
                }"
                :aria-label="!isFiltersModalActive ? $i18n.get('label_show_filters') : $i18n.get('label_hide_filters')"
                @click="isFiltersModalActive = !isFiltersModalActive">
            <span class="icon">
                <i 
                        :class="{
                            'tainacan-icon-arrowdown': isFiltersModalActive && displayFiltersHorizontally,
                            'tainacan-icon-arrowleft': isFiltersModalActive && !displayFiltersHorizontally,
                            'tainacan-icon-arrowright' : !isFiltersModalActive
                        }"
                        class="tainacan-icon tainacan-icon-1-25em" />
            </span>
            <span class="text is-hidden-tablet">{{ $i18n.get('filters') }}</span>
        </button>

        <!-- Text simple search -->
        <div 
                v-if="!hideSearch"
                class="search-control-item search-control-item--search">
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
                    <template #trigger>
                        <b-input
                                size="is-small"
                                :placeholder="$i18n.get('instruction_search')"
                                type="search"
                                :aria-label="$i18n.get('instruction_search') + ' ' + $i18n.get('items')"
                                :model-value="searchQuery"
                                icon-right="magnify"
                                icon-right-clickable
                                :disabled="openAdvancedSearch"
                                @update:model-value="typeFutureSearch"
                                @keyup.enter="updateSearch()"
                                @icon-right-click="updateSearch()" />
                    </template>
                    <b-dropdown-item 
                            :focusable="false"
                            @click="updateSearch()">
                        <span v-html="$i18n.get('instruction_press_enter_to_search_for')" />&nbsp;
                        <em>{{ sentenceMode == true ? futureSearchQuery : ('"' + futureSearchQuery + '"') }}.</em>
                    </b-dropdown-item>
                    <b-dropdown-item
                            custom
                            :focusable="false">
                        <b-checkbox 
                                :model-value="sentenceMode"
                                :true-value="false"
                                :false-value="true"
                                @update:model-value="$eventBusSearch.setSentenceMode($event)">
                            {{ $i18n.get('label_use_search_separated_words') }}
                        </b-checkbox>
                        <small class="is-small help">{{ $i18n.get('info_use_search_separated_words') }}</small>
                    </b-dropdown-item>
                    <b-dropdown-item
                            v-if="!hideAdvancedSearch"
                            :focusable="false"
                            @click="openAdvancedSearch = !openAdvancedSearch; $eventBusSearch.clearAllFilters();">
                        {{ $i18n.get('info_for_more_metadata_search_options_use') }}&nbsp; 
                        <a 
                                class="has-text-secondary"
                                @click="openAdvancedSearch = !openAdvancedSearch; $eventBusSearch.clearAllFilters();">
                            {{ $i18n.get('advanced_search') }}
                        </a>
                    </b-dropdown-item>
                </b-dropdown>
                <a
                        v-if="!hideAdvancedSearch"
                        class="advanced-search-toggle has-text-secondary is-pulled-right"
                        :class="openAdvancedSearch ? 'is-open' : 'is-closed'"
                        @click="openAdvancedSearch = !openAdvancedSearch; $eventBusSearch.clearAllFilters();">
                    {{ $i18n.get('advanced_search') }}
                    <span class="icon">
                        <i class="tainacan-icon tainacan-icon-search" />
                    </span>
                </a>
            </div>
        </div>

        <!-- Another option of the Button for hiding filters -->
        <div 
                v-if="showFiltersButtonInsideSearchControl && !hideHideFiltersButton && !hideFilters && !openAdvancedSearch"
                id="tainacanFiltersButton"
                :class="'search-control-item search-control-item--filters-button' + (isFiltersModalActive ? ' is-filters-modal-active' : '')">
            <button 
                    class="button is-white"
                    :aria-label="$i18n.get('filters')"
                    @click="isFiltersModalActive = !isFiltersModalActive">
                <span 
                        :class="{ 'has-text-secondary': hasFiltered }"
                        class="gray-icon">
                    <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-filters" />
                </span>
                <span class="is-hidden-touch">{{ $i18n.get('filters') }}</span>
            </button>
        </div>

        <!-- Displayed Metadata Dropdown -->
        <div    
                v-if="!hideDisplayedMetadataButton && (registeredViewModes[viewMode] != undefined && registeredViewModes[viewMode].dynamic_metadata)"
                class="search-control-item search-control-item--displayed-metadata-dropdown">
            <b-dropdown
                    ref="displayedMetadataDropdown" 
                    v-tooltip="{
                        delay: {
                            show: 500,
                            hide: 300,
                        },
                        content: totalItems <= 0 ? $i18n.get('info_cant_select_metadata_without_items') : '',
                        autoHide: false,
                        placement: 'auto-start',
                        popperClass: ['tainacan-tooltip', 'tooltip', isRepositoryLevel ? 'tainacan-repository-tooltip' : '']
                    }"
                    :mobile-modal="true"
                    :disabled="totalItems <= 0"
                    class="show metadata-options-dropdown"
                    aria-role="list"
                    trap-focus>
                <template #trigger>
                    <button
                            :aria-label="$i18n.get('label_displayed_metadata')"
                            class="button is-white">
                        <span class="is-hidden-touch is-hidden-desktop-only">{{ $i18n.get('label_displayed_metadata') }}</span>
                        <span class="is-hidden-widescreen">{{ $i18n.get('metadata') }}</span>
                        <span class="icon">
                            <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-arrowdown" />
                        </span>
                    </button>
                </template>
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
                            class="button is-success"
                            @click="onChangeDisplayedMetadata()">
                        {{ $i18n.get('label_apply_changes') }}
                    </button>
                </div>  
            </b-dropdown>
        </div>

        <!-- Change OrderBy Select and Order Button-->
        <div 
                v-show="!hideSortingArea"
                class="search-control-item search-control-item--sorting-area sorting-area">
            <b-field>
                <label class="label">{{ $i18n.get('label_sort') }}</label>
                <b-dropdown
                        :mobile-modal="true"
                        aria-role="list"
                        trap-focus
                        @update:model-value="onChangeOrder">
                    <template #trigger>
                        <button
                                :aria-label="$i18n.get('label_sorting_direction')"
                                class="button is-white">
                            <span class="icon is-small gray-icon">
                                <i 
                                        :class="order == 'DESC' ? 'tainacan-icon-sortdescending' : 'tainacan-icon-sortascending'"
                                        class="tainacan-icon" />
                            </span>
                            <span class="icon">
                                <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-arrowdown" />
                            </span>
                        </button>
                    </template>
                    <b-dropdown-item
                            aria-controls="items-list-results"
                            role="button"
                            :class="{ 'is-active': order == 'DESC' }"
                            :value="'DESC'"
                            aria-role="listitem">
                        <span class="icon gray-icon">
                            <i class="tainacan-icon tainacan-icon-18px tainacan-icon-sortdescending" />
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
                            <i class="tainacan-icon tainacan-icon-18px tainacan-icon-sortascending" />
                        </span>
                        <span>{{ $i18n.get('label_ascending') }}</span>
                    </b-dropdown-item>
                </b-dropdown>
                <template v-if="!hideSortByButton">
                    <span
                            class="label"
                            style="padding-left: 2px !important;">
                        {{ $i18n.get('info_by_inner') }}
                    </span>
                    <b-dropdown
                            id="tainacanSortByDropdown"
                            :mobile-modal="true"
                            aria-role="list"
                            trap-focus
                            @update:model-value="onChangeOrderBy($event)">
                        <template #trigger>
                            <button
                                    :aria-label="$i18n.get('label_sorting')"
                                    class="button is-white">
                                <span>{{ orderByName }}</span>
                                <span class="icon">
                                    <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-arrowdown" />
                                </span>
                            </button>
                        </template>
                        <template 
                                v-for="metadatum of sortingMetadata"
                                :key="metadatum.slug">
                            <b-dropdown-item
                                    v-if="metadatum != undefined"
                                    aria-controls="items-list-results"
                                    role="button"
                                    :class="{ 'is-active': (orderBy != 'meta_value' && orderBy != 'meta_value_num' && orderBy == metadatum.slug) || ((orderBy == 'meta_value' || orderBy == 'meta_value_num') && metaKey == metadatum.id) }"
                                    :value="metadatum"
                                    aria-role="listitem">
                                {{ metadatum.name }}
                            </b-dropdown-item>
                        </template>
                    </b-dropdown>
                </template>
            </b-field>
        </div>

        <!-- View Modes Dropdown -->
        <div 
                v-if="enabledViewModes.length > 1"
                id="tainacanViewModesSection"
                class="search-control-item search-control-item--view-modes-dropdown">
            <b-field>
                <label 
                        class="label is-hidden-touch is-hidden-desktop-only"
                        :style="{ marginRight: showInlineViewModeOptions ? '' : '-10px'}">
                    {{ $i18n.get('label_visualization') + ':&nbsp; ' }}
                </label>
                <label 
                        class="label is-hidden-widescreen"
                        :style="{ marginRight: showInlineViewModeOptions ? '' : '-10px'}">
                    {{ $i18n.get('label_view_on') + ':&nbsp; ' }}
                </label>
                <b-dropdown
                        :inline="showInlineViewModeOptions"
                        :mobile-modal="true"
                        position="is-bottom-left"
                        aria-role="list"
                        trap-focus
                        @change="onChangeViewMode($event)">
                    <template #trigger>
                        <button 
                                class="button is-white" 
                                :aria-label="$i18n.get('label_view_mode') + (registeredViewModes[viewMode] != undefined ? registeredViewModes[viewMode].label : '')">
                            <span 
                                    v-if="registeredViewModes[viewMode] != undefined"
                                    class="gray-icon view-mode-icon"
                                    v-html="registeredViewModes[viewMode].icon" />
                            <span class="is-hidden-touch">&nbsp;&nbsp;&nbsp;{{ registeredViewModes[viewMode] != undefined ? registeredViewModes[viewMode].label : $i18n.get('label_visualization') }}</span>
                            <span class="icon">
                                <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-arrowdown" />
                            </span>
                        </button>
                    </template>
                    <template 
                            v-for="(viewModeOption, index) of enabledViewModes"
                            :key="index">
                        <b-dropdown-item 
                                v-if="(registeredViewModes[viewModeOption] != undefined && registeredViewModes[viewModeOption].full_screen == false) || (showFullscreenWithViewModes && registeredViewModes[viewModeOption] != undefined)"
                                aria-controls="items-list-results"
                                role="button"
                                :class="{ 'is-active': viewModeOption == viewMode }"
                                :value="viewModeOption"
                                aria-role="listitem">
                            <span 
                                    v-if="!showInlineViewModeOptions"
                                    class="gray-icon"
                                    v-html="registeredViewModes[viewModeOption].icon" />
                            <span 
                                    v-else 
                                    v-tooltip="{
                                        delay: {
                                            show: 500,
                                            hide: 300,
                                        },
                                        content: registeredViewModes[viewModeOption].label,
                                        autoHide: false,
                                        placement: 'auto-start',
                                        popperClass: ['tainacan-tooltip', 'tooltip', isRepositoryLevel ? 'tainacan-repository-tooltip' : '']
                                    }"
                                    class="gray-icon"
                                    v-html="registeredViewModes[viewModeOption].icon" />
                            <span v-if="!showInlineViewModeOptions">{{ registeredViewModes[viewModeOption].label }}</span>
                        </b-dropdown-item>
                    </template>
                </b-dropdown>
            </b-field>
        </div>

        <!-- Theme Full Screen mode, it's just a special view mode -->
        <div 
                id="tainacanFullScreenViewMode"
                class="search-control-item search-control-item--full-screen-view-mode">
            <template 
                    v-for="(viewModeOption, index) of enabledViewModes"
                    :key="index">
                <button 
                        v-if="!showFullscreenWithViewModes && registeredViewModes[viewModeOption] != undefined && registeredViewModes[viewModeOption].full_screen == true"
                        class="button is-white"
                        :aria-label="$i18n.get('label_slides')"
                        :value="viewModeOption"
                        @click="onChangeViewMode(viewModeOption)">
                    <span 
                            class="gray-icon view-mode-icon"
                            v-html="registeredViewModes[viewModeOption].icon" />
                    <span class="is-hidden-tablet-only">{{ registeredViewModes[viewModeOption].label }}</span>
                </button>
            </template>
        </div>

        <!-- Exposers or alternative links modal button -->
        <div 
                v-if="!hideExposersButton"
                id="tainacanExposersButton"
                class="search-control-item search-control-item--exposers-button">
            <button 
                    class="button is-white"
                    :aria-label="$i18n.get('label_view_as')"
                    :disabled="totalItems == undefined || totalItems <= 0"
                    @click="openExposersModal()">
                <span class="gray-icon">
                    <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-viewas" />
                </span>
                <span class="is-hidden-tablet-only is-hidden-desktop-only ">{{ $i18n.get('label_view_as') }}</span>
            </button>
        </div>

        <!-- JS-side hook for extra form content -->
        <div 
                v-if="hooks['search_control_after']"
                class="faceted-search-hook faceted-search-hook-search-control-after"
                v-html="hooks['search_control_after']" />
                
    </div>

    <!-- SIDEBAR WITH FILTERS -->
    <template v-if="!hideFilters">
        <template v-if="!filtersAsModal && shouldNotHideFiltersOnMobile">
            <div 
                    v-if="isFiltersModalActive"
                    id="filters-modal"
                    ref="filters-modal"    
                    role="region" 
                    :class="'tainacan-modal tainacan-form filters-menu' + (displayFiltersHorizontally ? ' horizontal-filters' : '')">
                
                <div class="animation-content modal-content">

                    <!-- JS-side hook for extra form content -->
                    <div 
                            v-if="hooks['filters_before']"
                            class="faceted-search-hook faceted-search-hook-filters-before"
                            v-html="hooks['filters_before']" />

                    <filters-items-list
                            id="filters-items-list"
                            :is-loading-items="isLoadingItems"
                            :taxonomy="taxonomy"
                            :collection-id="collectionId + ''"
                            :is-repository-level="isRepositoryLevel"
                            :filters-as-modal="false"
                            :has-filtered="hasFiltered"
                            :is-mobile-screen="isMobileScreen"
                            :hide-filter-collapses="hideFilterCollapses"
                            @update-is-loading-items-state="(state) => isLoadingItems = state" />

                    <!-- JS-side hook for extra form content -->
                    <div 
                            v-if="hooks['filters_after']"
                            class="faceted-search-hook faceted-search-hook-filters-after"
                            v-html="hooks['filters_after']" />
                
                </div>
            </div>
        </template>
        <b-modal
                v-else
                id="filters-modal"
                ref="filters-modal"     
                v-model="isFiltersModalActive"       
                role="region"
                :width="736"
                :auto-focus="filtersAsModal"
                :trap-focus="filtersAsModal"
                full-screen
                :custom-class="'tainacan-modal tainacan-form filters-menu' + (filtersAsModal ? ' filters-menu-modal' : '') + (displayFiltersHorizontally ? ' horizontal-filters' : '')"
                :can-cancel="hideHideFiltersButton || !filtersAsModal ? ['x', 'outside'] : ['x', 'escape', 'outside']"
                :close-button-aria-label="$i18n.get('close')">
                
            <!-- JS-side hook for extra form content -->
            <div 
                    v-if="hooks['filters_before']"
                    class="faceted-search-hook faceted-search-hook-filters-before"
                    v-html="hooks['filters_before']" />

            <filters-items-list
                    id="filters-items-list"
                    :is-loading-items="isLoadingItems"
                    :autofocus="filtersAsModal"
                    :tabindex="filtersAsModal ? -1 : 0"
                    :aria-modal="filtersAsModal"
                    :role="filtersAsModal ? 'dialog' : ''"
                    :taxonomy="taxonomy"
                    :collection-id="collectionId + ''"
                    :is-repository-level="isRepositoryLevel"
                    :filters-as-modal="filtersAsModal"
                    :has-filtered="hasFiltered"
                    :is-mobile-screen="isMobileScreen"
                    :hide-filter-collapses="hideFilterCollapses"
                    @update-is-loading-items-state="(state) => isLoadingItems = state" />

            <!-- JS-side hook for extra form content -->
            <div 
                    v-if="hooks['filters_after']"
                    class="faceted-search-hook faceted-search-hook-filters-after"
                    v-html="hooks['filters_after']" />

        </b-modal>
    </template>

    <!-- ITEMS LIST AREA (ASIDE THE ASIDE) ------------------------- -->
    <div 
            id="items-list-area"
            ref="items-list-area"
            class="items-list-area">

        <!-- JS-side hook for extra form content -->
        <div 
                v-if="hooks['items_list_area_before']"
                class="faceted-search-hook faceted-search-hook-items-list-area-before"
                v-html="hooks['items_list_area_before']" />

        <!-- ADVANCED SEARCH -->
        <transition name="filter-item">
            <div 
                    v-if="openAdvancedSearch && !hideAdvancedSearch"
                    id="advanced-search-container"
                    role="search">

                <!-- JS-side hook for extra form content -->
                <div 
                        v-if="hooks['advanced_search_before']"
                        class="faceted-search-hook faceted-search-hook-advanced-search-before"
                        v-html="hooks['advanced_search_before']" />

                <advanced-search
                        :is-repository-level="isRepositoryLevel"
                        :collection-id="collectionId"
                        @close="openAdvancedSearch = false" />

                <!-- JS-side hook for extra form content -->
                <div 
                        v-if="hooks['advanced_search_after']"
                        class="faceted-search-hook faceted-search-hook-advanced-search-after"
                        v-html="hooks['advanced_search_after']" />
            </div>
        </transition>

        <!-- FILTERS TAG LIST-->
        <template
                v-if="!filtersAsModal &&
                    !hideFilters &&
                    hasFiltered && 
                    !openAdvancedSearch &&
                    !(registeredViewModes[viewMode] != undefined && registeredViewModes[viewMode].full_screen)">

            <!-- JS-side hook for extra form content -->
            <div 
                    v-if="hooks['filter_tags_before']"
                    class="faceted-search-hook faceted-search-hook-filter-tags-before"
                    v-html="hooks['filter_tags_before']" />

            <filters-tags-list
                    class="filter-tags-list"
                    :is-inside-modal="filtersAsModal"
                />

            <!-- JS-side hook for extra form content -->
            <div 
                    v-if="hooks['filter_tags_after']"
                    class="faceted-search-hook faceted-search-hook-filter-tags-after"
                    v-html="hooks['filter_tags_after']" />

        </template>

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
                {{ $i18n.get('label_items_list_results') }}
            </h3>
            
            <!-- This is used by intersection observers to set filters menu as fixed -->
            <div 
                    id="items-list-results-top"
                    ref="items-list-results-top"
                    class="sr-only" />

            <div 
                    v-show="(showLoading && 
                        !(registeredViewModes[viewMode] != undefined && (registeredViewModes[viewMode].full_screen == true || registeredViewModes[viewMode].implements_skeleton == true)))"
                    class="loading-container">

                <!--  Default loading, to be used view modes without any skeleton-->
                <b-loading 
                        v-if="registeredViewModes[viewMode] != undefined && !registeredViewModes[viewMode].implements_skeleton && !registeredViewModes[viewMode].skeleton_template" 
                        v-model="showLoading"
                        :is-full-page="false" />

                <!-- Custom skeleton templates used by some view modes --> 
                <div
                        v-if="registeredViewModes[viewMode] != undefined && registeredViewModes[viewMode].implements_skeleton && registeredViewModes[viewMode].skeleton_template"
                        v-html="registeredViewModes[viewMode].skeleton_template" />
            </div>  
            
            <!-- Alert if custom metadata is being used for sorting -->
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
                            class="button"
                            @click="openMetatadaSortingWarningDialog({ offerCheckbox: false })">
                        {{ $i18n.get('label_view_more') }}
                    </button>
                    <button 
                            class="button icon"
                            @click="hasAnOpenAlert = false">
                        <i class="tainacan-icon tainacan-icon-close" />
                    </button>
                </div>
            </div>

            <!-- JS-side hook for extra form content -->
            <div 
                    v-if="hooks['items_list_before']"
                    class="faceted-search-hook faceted-search-hook-items-list-before"
                    v-html="hooks['items_list_before']" />
                    
            <!-- Theme View Modes -->
            <div 
                    v-if="!showLoading &&
                        registeredViewModes[viewMode] != undefined &&
                        registeredViewModes[viewMode].type == 'template'"
                    v-html="itemsListTemplate" />

            <component
                    :is="registeredViewModes[viewMode] != undefined ? registeredViewModes[viewMode].component : ''"
                    v-if="registeredViewModes[viewMode] != undefined &&
                        registeredViewModes[viewMode].type == 'component'"
                    :collection-id="collectionId"
                    :term-id="termId"
                    :displayed-metadata="displayedMetadata"
                    :should-hide-items-thumbnail="hideItemsThumbnail"
                    :items="items"
                    :is-filters-menu-compressed="!hideFilters && !isFiltersModalActive"
                    :total-items="totalItems"
                    :is-loading="showLoading"
                    :enabled-view-modes="enabledViewModes"
                    :initial-item-position="initialItemPosition"
                    :is-repository-level="isRepositoryLevel">
                
                
                <!-- Empty Placeholder, rendered in a slot inside the view modes -->
                <section
                        v-if="!showLoading && totalItems == 0"
                        class="section">
                    <div class="content has-text-grey has-text-centered">
                        <p>
                            <span class="icon is-large">
                                <i class="tainacan-icon tainacan-icon-30px tainacan-icon-items" />
                            </span>
                        </p>
                        <p>
                            {{ (hasFiltered || openAdvancedSearch || searchQuery) ? $i18n.get('info_no_item_found_filter') : $i18n.get('info_no_item_found') }}
                        </p>

                        <p v-if="searchQuery">
                            <template v-if="!sentenceMode">
                                <span v-if="searchQuery">{{ $i18n.getWithVariables('info_you_searched_for_%s', ['"' + searchQuery + '"']) }}</span>. {{ $i18n.get('info_try_enabling_search_by_word') }}
                                <br>
                                {{ $i18n.get('info_details_about_search_by_word') }}
                            </template>
                            <template v-else>
                                <span v-if="searchQuery">{{ $i18n.getWithVariables('info_you_searched_for_%s', ['"' + searchQuery + '"']) }}</span>. {{ $i18n.get('info_try_disabling_search_by_word') }}
                            </template>
                            <br>
                            <b-checkbox 
                                    :model-value="sentenceMode"
                                    :true-value="false"
                                    :false-value="true"
                                    @update:model-value="$eventBusSearch.setSentenceMode($event); updateSearch();">
                                {{ $i18n.get('label_use_search_separated_words') }}
                            </b-checkbox>
                        </p>
                    </div>
                </section>

            </component>   

            <!-- JS-side hook for extra form content -->
            <div 
                    v-if="hooks['items_list_after']"
                    class="faceted-search-hook faceted-search-hook-items-list-after"
                    v-html="hooks['items_list_after']" /> 

            <template
                    v-if="totalItems > 0 &&
                        ((registeredViewModes[viewMode] != undefined && registeredViewModes[viewMode].show_pagination))">

                <!-- JS-side hook for extra form content -->
                <div 
                        v-if="hooks['pagination_before']"
                        class="faceted-search-hook faceted-search-hook-pagination-before"
                        v-html="hooks['pagination_before']" /> 
        
                <!-- Pagination -->
                <items-pagination
                        v-show="!hidePaginationArea"
                        :is-sorting-by-custom-metadata="isSortingByCustomMetadata"
                        :hide-items-per-page-button="hideItemsPerPageButton"
                        :hide-go-to-page-button="hideGoToPageButton" />

                <!-- JS-side hook for extra form content -->
                <div 
                        v-if="hooks['pagination_after']"
                        class="faceted-search-hook faceted-search-hook-pagination-after"
                        v-html="hooks['pagination_after']" />
            </template>

            <!-- JS-side hook for extra form content -->
            <div 
                    v-if="hooks['items_list_area_after']"
                    class="faceted-search-hook faceted-search-hook-items-list-area-after"
                    v-html="hooks['items_list_area_after']" />

            <!-- This is used by intersection observers to set filters menu as fixed on the bottom -->
            <div 
                    id="items-list-results-bottom"
                    ref="items-list-results-bottom"
                    class="sr-only"
                    style="bottom: 0px" />
        </div>
        
    </div>
</template>

<script>
    import { nextTick, defineAsyncComponent } from 'vue';
    import { mapActions, mapGetters } from 'vuex';
    import qs from 'qs';
    import ItemsPagination from '../../../admin/components/search/items-pagination.vue'

    export default {
        name: 'ThemeSearch',
        components: {
            FiltersTagsList: defineAsyncComponent(() => import('../../../admin/components/search/filters-tags-list.vue')),
            ItemsPagination,
            AdvancedSearch: defineAsyncComponent(() => import('../../../admin/components/search/advanced-search.vue')),
        },
        props: {
            // Source settings
            collectionId: [String,Number],
            termId: [String,Number],
            taxonomy: String,
            // View Mode settings
            isForcedViewMode: Boolean,
            defaultViewMode: String,
            enabledViewModes: Object,
            // Hidding elements
            hideFilters: false,
            hideHideFiltersButton: false,
            hideSearch: false,
            hideAdvancedSearch: false,
            hideDisplayedMetadataButton: false,
            hideSortingArea: false,
            hideSortByButton: false,
            hideItemsThumbnail: false,
            hideExposersButton: false,
            hideItemsPerPageButton: false,
            hideGoToPageButton: false,
            hidePaginationArea: false,
            // Other Tweaks
            defaultOrder: 'ASC',
            defaultOrderBy: 'date',
            defaultOrderByMeta: '',
            defaultOrderByType: '',
            defaultItemsPerPage: '',
            showFiltersButtonInsideSearchControl: false,
            startWithFiltersHidden: false,
            filtersAsModal: false,
            showInlineViewModeOptions: false,
            showFullscreenWithViewModes: false,
            shouldNotHideFiltersOnMobile: false,
            displayFiltersHorizontally: false,
            hideFilterCollapses: false,
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
                latestNonFullscreenViewMode: '',
                isMobileScreen: false,
                windowWidth: null,
                initialItemPosition: null,
                isFiltersListFixedAtTop: false,
                isFiltersListFixedAtBottom: false,
                itemsListTopIntersectionObserver: null,
                itemsListBottomIntersectionObserver: null,
                latestPerPageAfterViewModeWithoutPagination: 12,
                latestPageAfterViewModeWithoutPagination: 1,
                hooks: {}
            }
        },
        computed: {
            ...mapGetters('collection', {
                'items': 'getItems',
                'itemsListTemplate': 'getItemsListTemplate'
            }),
            ...mapGetters('metadata', {
                'metadata': 'getMetadata'
            }),
            ...mapGetters('search', {
                'searchQuery': 'getSearchQuery',
                'orderBy': 'getOrderBy',
                'order': 'getOrder',
                'viewMode': 'getViewMode',
                'totalItems': 'getTotalItems',
                'sentenceMode': 'getSentenceMode',
                'metaKey': 'getMetaKey',
                'page': 'getPage',
                'itemsPerPage': 'getItemsPerPage'
            }),
            wrapperClasses() {
                return {
                    'has-horizontal-filters': this.displayFiltersHorizontally,
                    'is-filters-menu-open': !this.hideFilters && this.isFiltersModalActive && !this.openAdvancedSearch,
                    'is-filters-menu-fixed-at-top': this.isFiltersListFixedAtTop,
                    'is-filters-menu-fixed-at-bottom': this.isFiltersListFixedAtBottom,
                    'repository-level-page': this.isRepositoryLevel,
                    'is-fullscreen': this.registeredViewModes[this.viewMode] != undefined && this.registeredViewModes[this.viewMode].full_screen   
                }
            },
            isSortingByCustomMetadata() {
                return (this.orderBy != undefined && this.orderBy != '' && this.orderBy != 'title' && this.orderBy != 'creation_date' && this.orderBy != 'date' && this.orderBy != 'modified'); 
            },
            showLoading() {
                return this.isLoadingItems || this.isLoadingMetadata;
            },
            orderByName() {
                const metadatumName =  this.$orderByHelper.getOrderByMetadatumName({
                    orderby: this.$route.query.orderby,
                    metakey: this.$route.query.metakey
                }, this.sortingMetadata);
                return this.$route.query.metakey ? metadatumName : (metadatumName ? this.$i18n.get(metadatumName) : '');
            },
            hasSearchByMoreThanOneWord() {
                return this.futureSearchQuery && this.futureSearchQuery.split(' ').length > 1;
            }
        },
        watch: {
            '$route': {
                handler(to, from) {
                    
                    // Should set Collection ID from URL only when in admin.
                    if (this.$route.name == 'CollectionItemsPage' || this.$route.name == 'ItemsPage')
                        this.$eventBusSearch.setCollectionId( !this.$route.params.collectionId ? this.$route.params.collectionId : parseInt(this.$route.params.collectionId) );
                    
                    // Fills the URL with appropriate default values in case a query is not passed
                    if (this.$route.name == null || this.$route.name == undefined || this.$route.name == 'CollectionItemsPage' || this.$route.name == 'ItemsPage') {
                        
                        // Items Per Page
                        if (this.$route.query.perpage == undefined || to.params.collectionId != from.params.collectionId) {
                            let perPageKey = (this.collectionId != undefined ? 'items_per_page_' + this.collectionId : 'items_per_page');
                            let perPageValue = this.$userPrefs.get(perPageKey);

                            if (perPageValue)
                                this.$route.query.perpage = perPageValue;
                            else {
                                this.$route.query.perpage = 12;
                                this.$userPrefs.set(perPageKey, 12);
                            }
                        }    
                        
                        // Page
                        if (this.$route.query.paged == undefined || to.params.collectionId != from.params.collectionId)
                            this.$route.query.paged = 1;
                    
                        // Order (ASC, DESC)
                        if (this.$route.query.order == undefined || to.params.collectionId != from.params.collectionId) {
                            let orderKey = (this.collectionId != undefined ? 'order_' + this.collectionId : 'order');
                            let orderValue = this.$userPrefs.get(orderKey) ? this.$userPrefs.get(orderKey) : this.defaultOrder;
                            
                            if (orderValue)
                                this.$route.query.order = orderValue;
                            else {
                                this.$route.query.order = 'DESC';
                                this.$userPrefs.set(orderKey, 'DESC');
                            }
                        }
                        
                        // Order By (required extra work to deal with custom metadata ordering)
                        if (this.$route.query.orderby == undefined || (to.params.collectionId != from.params.collectionId)) {
                            let orderByKey = (this.collectionId != undefined ? 'order_by_' + this.collectionId : 'order_by');
                            let orderBy = this.$userPrefs.get(orderByKey) ? this.$userPrefs.get(orderByKey) : this.defaultOrderBy;
                        
                            if (orderBy && orderBy != 'name') {
                            
                                // Previously was stored as a metadata object, now it is a orderby object
                                if (orderBy.slug || typeof orderBy == 'string')
                                    orderBy = this.$orderByHelper.getOrderByForMetadatum(orderBy);

                                if (orderBy.orderby)
                                    Object.keys(orderBy).forEach((paramKey) => {
                                        this.$route.query[paramKey] = orderBy[paramKey];
                                    });
                                else
                                    this.$route.query.orderby = 'date'
                                
                            } else {
                                this.$route.query.orderby = 'date';
                                this.$userPrefs.set(orderByKey, { 
                                    slug: 'creation_date',
                                    name: this.$i18n.get('label_creation_date')
                                }).catch(() => { });
                            }
                        } else if ( this.$route.query.orderby == 'creation_date' ) { // Fixes old usage of creation_date
                            this.$route.query.orderby = 'date'
                        }
                        
                        // Theme View Modes
                        if ((this.$route.name == null || this.$route.name == undefined ) && 
                            this.$route.name != 'CollectionItemsPage' && this.$route.name != 'ItemsPage' &&
                            (this.$route.query.view_mode == undefined || to.params.collectionId != from.params.collectionId)
                        ) {
                            
                            let viewModeKey = (this.collectionId != undefined ? 'view_mode_' + this.collectionId : 'view_mode');
                            let viewModeValue = this.$userPrefs.get(viewModeKey);

                            if (viewModeValue)
                                this.$route.query.view_mode = viewModeValue;
                            else {
                                this.$route.query.view_mode = 'table';
                                this.$userPrefs.set(viewModeKey, 'table');
                            }
                        }

                        // Emit slideshow-from to start this view mode from index
                        if (this.$route.query.view_mode != 'slideshow' && this.$route.query['slideshow-from'] !== null && this.$route.query['slideshow-from'] !== undefined && this.$route.query['slideshow-from'] !== false)
                            this.$eventBusSearchEmitter.emit('startSlideshowFromItem', this.$route.query['slideshow-from']);
                        
                        // Advanced Search
                        if (this.$route.query && this.$route.query.advancedSearch)
                           this.$store.dispatch('search/setAdvancedQuery', JSON.parse(JSON.stringify(this.$route.query)));
                        else
                           this.$store.dispatch('search/setPostQuery', JSON.parse(JSON.stringify(this.$route.query)));
                        
                         // Finally, loads items even berfore facets so they won't stuck them
                         if (to.fullPath != from.fullPath)
                            this.$eventBusSearch.loadItems();

                        // Checks current metaqueries and taxqueries to alert filters that should reload
                        // For some reason, this process is not working accessing to.query, so we need to check the path string. 
                        const oldQueryString = from.fullPath.replace(from.path + '?', '');
                        const newQueryString = to.fullPath.replace(from.path + '?', '');
                        
                        const oldQueryArray = oldQueryString.split('&');
                        const newQueryArray = newQueryString.split('&');

                        const oldMetaQueryArray = oldQueryArray.filter(queryItem => queryItem.startsWith('metaquery'));
                        const newMetaQueryArray = newQueryArray.filter(queryItem => queryItem.startsWith('metaquery'));
                        const oldTaxQueryArray  = oldQueryArray.filter(queryItem => queryItem.startsWith('taxquery'));
                        const newTaxQueryArray  = newQueryArray.filter(queryItem => queryItem.startsWith('taxquery'));
                        const oldSearchQuery    = oldQueryArray.filter(queryItem => queryItem.startsWith('search'));
                        const newSearchQuery    = newQueryArray.filter(queryItem => queryItem.startsWith('search'));

                        if (
                            JSON.stringify(oldMetaQueryArray) != JSON.stringify(newMetaQueryArray) ||
                            JSON.stringify(oldTaxQueryArray)  != JSON.stringify(newTaxQueryArray) ||
                            JSON.stringify(oldSearchQuery)    != JSON.stringify(newSearchQuery)
                        ) {
                            this.$eventBusSearchEmitter.emit('hasToReloadFacets', true);
                        }
                    }
                },
                deep: true
            },
            wrapperClasses: {
                handler(newWrapperClasses) {
                    const classesAsString = Object.keys(newWrapperClasses)
                        .filter(key => newWrapperClasses[key])
                        .join(' ');

                    if ( this.$el && this.$el.parentElement && this.$el.parentElement.className !==  "theme-items-list has-mounted " + classesAsString )
                        this.$el.parentElement.className = "theme-items-list has-mounted " + classesAsString;
                },
                immediate: true,
            },
            displayedMetadata: {
                handler() {
                    this.localDisplayedMetadata = JSON.parse(JSON.stringify(this.displayedMetadata));
                },
                deep: true
            },
            openAdvancedSearch(newValue){
                if (newValue == false){
                    this.$eventBusSearchEmitter.emit('closeAdvancedSearch');

                    if ( !this.isMobileScreen )
                        this.isFiltersModalActive = !this.startWithFiltersHidden;
                } else {
                    this.isFiltersModalActive = false;
                }
            },
            orderByName() {
                if (this.isSortingByCustomMetadata)
                    this.hasAnOpenAlert = true;
            },
            isFiltersModalActive() {
                if ( this.isFiltersModalActive ) {
                    setTimeout(() => {
                        if (this.filtersAsModal && this.$refs['filters-modal'] && this.$refs['filters-modal'].focus)
                            this.$refs['filters-modal'].focus();
                            
                        if (!this.filtersAsModal && !this.isMobileScreen && document.documentElement && (this.registeredViewModes[this.viewMode] == undefined || !this.registeredViewModes[this.viewMode].full_screen))
                            document.documentElement.classList.remove('is-clipped');
                    }, 800);
                    
                }
            }
        },
        created() {
            this.$userPrefs.init();
            
            this.isRepositoryLevel = (this.collectionId == undefined || this.collectionId == '' || this.collectionId == null);

            // Loads params if passed previously 
            let currentQuery = qs.parse(location.search.split('?')[1]);

            // Sets initial variables important to searchbus
            if (this.collectionId != undefined)
                this.$eventBusSearch.setCollectionId(this.collectionId);
            if (this.termId != undefined && this.termId != null)
                this.$eventBusSearch.setTerm(this.termId, this.taxonomy);
            if (this.defaultOrder != undefined) {
                this.$eventBusSearch.setDefaultOrder(this.defaultOrder);
                if (!currentQuery.order)
                    this.$eventBusSearch.setOrder(this.defaultOrder);
            }
            if (this.defaultOrderBy != undefined) {
                
                if (this.defaultOrderByMeta || this.defaultOrderByType) {
                    
                    let orderByObject = { orderby: this.defaultOrderBy }
                    
                    if (this.defaultOrderByMeta)
                        orderByObject['metakey'] = this.defaultOrderByMeta;
                    
                    if (this.defaultOrderByType)
                        orderByObject['metatype'] = this.defaultOrderByType;
                    
                    this.$eventBusSearch.setDefaultOrderBy(orderByObject);

                    if (!currentQuery.orderby)
                        this.$eventBusSearch.setOrderBy(orderByObject);
                
                } else {                    
                    this.$eventBusSearch.setDefaultOrderBy(this.defaultOrderBy);

                    if (!currentQuery.orderby)
                        this.$eventBusSearch.setOrderBy(this.defaultOrderBy);
                }
            }

            for (let key in currentQuery) {
                if (currentQuery[key] == 'true')
                    currentQuery[key] = true;
                if (currentQuery[key] == 'false')
                    currentQuery[key] = false;
            }

            if ( currentQuery.advancedSearch )
                this.$store.dispatch('search/setAdvancedQuery', currentQuery);
            else
                this.$store.dispatch('search/setPostQuery', currentQuery )

            if (!this.hideAdvancedSearch) {
                if (currentQuery && currentQuery.advancedSearch)
                    this.openAdvancedSearch = !!currentQuery.advancedSearch;

                this.$emitter.on('openAdvancedSearch', (openAdvancedSearch) => {
                    this.openAdvancedSearch = openAdvancedSearch;
                });
            }

            // If any default items per page is set, apply it
            // 12 is already the default value, se we don't set this value
            if (this.defaultItemsPerPage != undefined && this.defaultItemsPerPage !== 12 ) {
                this.$eventBusSearch.setItemsPerPage(this.defaultItemsPerPage, true); 
            }

            this.$eventBusSearchEmitter.on('isLoadingItems', isLoadingItems => {

                this.isLoadingItems = isLoadingItems;
                
                document.dispatchEvent(new CustomEvent('tainacan-items-list-is-loading-items', {
                    detail: { 
                        isLoading: this.isLoadingItems,
                        collectionId: this.collectionId,
                        termId: this.termId,
                        taxonomy: this.taxonomy
                    }
                }));
            });

            this.$eventBusSearchEmitter.on('hasFiltered', hasFiltered => {
                this.hasFiltered = hasFiltered;
            });

            this.$eventBusSearchEmitter.on('exitViewModeWithoutPagination', () => {
                let currentQuery = JSON.parse(JSON.stringify(this.$route.query));
                delete currentQuery['slideshow-from'];
                this.$router.replace({ query: currentQuery });
    
                // Sets the perpage and paged from previous configuration
                this.$eventBusSearch.setItemsPerPage(this.latestPerPageAfterViewModeWithoutPagination, true);
                this.$eventBusSearch.setPage(this.latestPageAfterViewModeWithoutPagination);
                this.onChangeViewMode(this.latestNonFullscreenViewMode ? this.latestNonFullscreenViewMode : this.defaultViewMode);
            });

            this.$eventBusSearchEmitter.on('startSlideshowFromItem', (index) => {
                let currentQuery = JSON.parse(JSON.stringify(this.$route.query));
                delete currentQuery['slideshow-from'];
                this.$router.replace({ query: currentQuery }).catch((error) => this.$console.log(error));

                this.latestNonFullscreenViewMode = JSON.parse(JSON.stringify(this.viewMode));
                this.onChangeViewMode('slideshow');
                this.initialItemPosition = index;
            });

            // Parse js-side hooks
            this.parseHooks();
        },
        mounted() {

            this.prepareMetadata();
            this.localDisplayedMetadata = JSON.parse(JSON.stringify(this.displayedMetadata));
            
            // Setting initial view mode on Theme
            let prefsViewMode = !this.isRepositoryLevel ? 'view_mode_' + this.collectionId : 'view_mode';
           
            if (this.$userPrefs.get(prefsViewMode) == undefined || this.isForcedViewMode == true) {
                this.$eventBusSearch.setInitialViewMode(this.defaultViewMode);
            } else {
                const userPrefViewMode = this.$userPrefs.get(prefsViewMode);

                let existingViewModeIndex = Object.keys(this.registeredViewModes).findIndex(viewMode => viewMode == userPrefViewMode);
                let enabledViewModeIndex = (this.enabledViewModes && Array.isArray(this.enabledViewModes)) ? this.enabledViewModes.findIndex((viewMode) => viewMode == userPrefViewMode) : -1;
                if (existingViewModeIndex >= 0 && enabledViewModeIndex >= 0)
                    this.$eventBusSearch.setInitialViewMode(userPrefViewMode);
                else   
                    this.$eventBusSearch.setInitialViewMode(this.defaultViewMode);
            }

            // For view modes such as slides, we force pagination to request only 24 per page
            let existingViewModeIndex = Object.keys(this.registeredViewModes).findIndex(viewMode => viewMode == this.$userPrefs.get(prefsViewMode));
            if (existingViewModeIndex >= 0) {
                if (!this.registeredViewModes[Object.keys(this.registeredViewModes)[existingViewModeIndex]].show_pagination) {
                    this.latestPerPageAfterViewModeWithoutPagination = this.itemsPerPage;
                    this.latestPageAfterViewModeWithoutPagination = this.page;

                    this.$eventBusSearch.setItemsPerPage(24, true);
                }
            }

            // Watches window resize to adjust filter's top position and compression on mobile
            if ( !this.hideFilters && !this.shouldNotHideFiltersOnMobile ) {            
                this.hideFiltersOnMobile();
                window.addEventListener('resize', this.hideFiltersOnMobile);
            } else {
                this.isFiltersModalActive = !this.startWithFiltersHidden;
            }
            
            // Uses Intersection Observer o see if the top of the list is on screen and fix filters list position
            if (!this.filtersAsModal &&
                !this.hideFilters &&
                this.$refs['items-list-results-top'] &&
                this.$refs['items-list-results-bottom'] &&
                "IntersectionObserver" in window &&
                "IntersectionObserverEntry" in window &&
                "isIntersecting" in window.IntersectionObserverEntry.prototype &&
                "boundingClientRect" in window.IntersectionObserverEntry.prototype) {

                this.itemsListTopIntersectionObserver = new IntersectionObserver(entries => {
                    const itemsListAreaHeight = this.$refs['items-list-area'] ? this.$refs['items-list-area'].clientHeight : 0;
                    if (itemsListAreaHeight > window.innerHeight)
                        this.isFiltersListFixedAtTop = entries[0] && (!entries[0].isIntersecting) && (entries[0].boundingClientRect.y < 0);
                });
                this.itemsListTopIntersectionObserver.observe(this.$refs['items-list-results-top']);
    
                this.itemsListBottomIntersectionObserver = new IntersectionObserver(entries => {
                    if (entries[0].isIntersecting)
                        this.isFiltersListFixedAtBottom = true;
                    else
                        this.isFiltersListFixedAtBottom = false;
                });
                this.itemsListBottomIntersectionObserver.observe(this.$refs['items-list-results-bottom']);
            }
        },
        beforeUnmount() {
            this.removeEventListeners();
            
            // Removes intersection listener, if it was set up
            if (this.itemsListTopIntersectionObserver)
                this.itemsListTopIntersectionObserver.disconnect();
            if (this.itemsListBottomIntersectionObserver)
                this.itemsListBottomIntersectionObserver.disconnect();

            // Cancels previous Metadata Request
            if (this.metadataSearchCancel != undefined)
                this.metadataSearchCancel.cancel('Metadata search Canceled.');
     
            // Cancels previous Items Request
            if (this.$eventBusSearch.searchCancel != undefined)
                this.$eventBusSearch.searchCancel.cancel('Item search Canceled.');

        },
        methods: {
            ...mapActions('metadata', [
                'fetchMetadata'
            ]),
            parseHooks() {
                if (wp !== undefined && wp.hooks !== undefined) {

                    const searchControlBeforeFilters = wp.hooks.hasFilter(`tainacan_faceted_search_search_control_before`) && wp.hooks.applyFilters(`tainacan_faceted_search_search_control_before`, '');
                    const searchControlBeforeFiltersCollection = (wp.hooks.hasFilter(`tainacan_faceted_search_collection_${this.collectionId}_search_control_before`) || searchControlBeforeFilters) && wp.hooks.applyFilters(`tainacan_faceted_search_collection_${this.collectionId}_search_control_before`, searchControlBeforeFilters);
                    if (searchControlBeforeFiltersCollection)
                        this.hooks['search_control_before'] = searchControlBeforeFiltersCollection;

                    const searchControlAfterFilters = wp.hooks.hasFilter(`tainacan_faceted_search_search_control_after`) && wp.hooks.applyFilters(`tainacan_faceted_search_search_control_after`, ''); 
                    const searchControlAfterFiltersCollection = (wp.hooks.hasFilter(`tainacan_faceted_search_collection_${this.collectionId}_search_control_after`) || searchControlAfterFilters) && wp.hooks.applyFilters(`tainacan_faceted_search_collection_${this.collectionId}_search_control_after`, searchControlAfterFilters); 
                    if (searchControlAfterFiltersCollection)
                        this.hooks['search_control_after'] = searchControlAfterFiltersCollection; 

                    const advancedSearchBeforeFilters = wp.hooks.hasFilter(`tainacan_faceted_search_advanced_search_before`) && wp.hooks.applyFilters(`tainacan_faceted_search_advanced_search_before`, '');
                    const advancedSearchBeforeFiltersCollection = (wp.hooks.hasFilter(`tainacan_faceted_search_collection_${this.collectionId}_advanced_search_before`) || advancedSearchBeforeFilters) && wp.hooks.applyFilters(`tainacan_faceted_search_collection_${this.collectionId}_advanced_search_before`, advancedSearchBeforeFilters);
                    if (advancedSearchBeforeFiltersCollection)
                        this.hooks['advanced_search_before'] = advancedSearchBeforeFiltersCollection;

                    const advancedSearchAfterFilters = wp.hooks.hasFilter(`tainacan_faceted_search_advanced_search_after`) && wp.hooks.applyFilters(`tainacan_faceted_search_advanced_search_after`, ''); 
                    const advancedSearchAfterFiltersCollection = (wp.hooks.hasFilter(`tainacan_faceted_search_collection_${this.collectionId}_advanced_search_after`) || advancedSearchAfterFilters) && wp.hooks.applyFilters(`tainacan_faceted_search_collection_${this.collectionId}_advanced_search_after`, advancedSearchAfterFilters); 
                    if (advancedSearchAfterFiltersCollection)
                        this.hooks['advanced_search_after'] = advancedSearchAfterFiltersCollection; 

                    const filtersBeforeFilters = wp.hooks.hasFilter(`tainacan_faceted_search_filters_before`) && wp.hooks.applyFilters(`tainacan_faceted_search_filters_before`, '');
                    const filtersBeforeFiltersCollection = (wp.hooks.hasFilter(`tainacan_faceted_search_collection_${this.collectionId}_filters_before`) || filtersBeforeFilters) && wp.hooks.applyFilters(`tainacan_faceted_search_collection_${this.collectionId}_filters_before`, filtersBeforeFilters);
                    if (filtersBeforeFiltersCollection)
                        this.hooks['filters_before'] = filtersBeforeFiltersCollection;

                    const filtersAfterFilters = wp.hooks.hasFilter(`tainacan_faceted_search_filters_after`) && wp.hooks.applyFilters(`tainacan_faceted_search_filters_after`, ''); 
                    const filtersAfterFiltersCollection = (wp.hooks.hasFilter(`tainacan_faceted_search_collection_${this.collectionId}_filters_after`) || filtersAfterFilters) && wp.hooks.applyFilters(`tainacan_faceted_search_collection_${this.collectionId}_filters_after`, filtersAfterFilters); 
                    if (filtersAfterFiltersCollection)
                        this.hooks['filters_after'] = filtersAfterFiltersCollection; 

                    const filterTagsBeforeFilters = wp.hooks.hasFilter(`tainacan_faceted_search_filter_tags_before`) && wp.hooks.applyFilters(`tainacan_faceted_search_filter_tags_before`, '');
                    const filterTagsBeforeFiltersCollection = (wp.hooks.hasFilter(`tainacan_faceted_search_collection_${this.collectionId}_filter_tags_before`) || filterTagsBeforeFilters) && wp.hooks.applyFilters(`tainacan_faceted_search_collection_${this.collectionId}_filter_tags_before`, filterTagsBeforeFilters);
                    if (filterTagsBeforeFiltersCollection)
                        this.hooks['filte_tags_before'] = filterTagsBeforeFiltersCollection;

                    const filterTagsAfterFilters = wp.hooks.hasFilter(`tainacan_faceted_search_filter_tags_after`) && wp.hooks.applyFilters(`tainacan_faceted_search_filter_tags_after`, ''); 
                    const filterTagsAfterFiltersCollection = (wp.hooks.hasFilter(`tainacan_faceted_search_collection_${this.collectionId}_filter_tags_after`) || filterTagsAfterFilters) && wp.hooks.applyFilters(`tainacan_faceted_search_collection_${this.collectionId}_filter_tags_after`, filterTagsAfterFilters); 
                    if (filterTagsAfterFiltersCollection)
                        this.hooks['filter_tags_after'] = filterTagsAfterFiltersCollection;

                    const itemsListAreaBeforeFilters = wp.hooks.hasFilter(`tainacan_faceted_search_items_list_area_before`) && wp.hooks.applyFilters(`tainacan_faceted_search_items_list_area_before`, '');
                    const itemsListAreaBeforeFiltersCollection = (wp.hooks.hasFilter(`tainacan_faceted_search_collection_${this.collectionId}_items_list_area_before`) || itemsListAreaBeforeFilters) && wp.hooks.applyFilters(`tainacan_faceted_search_collection_${this.collectionId}_items_list_area_before`, itemsListAreaBeforeFilters);
                    if (itemsListAreaBeforeFiltersCollection)
                        this.hooks['items_list_area_before'] = itemsListAreaBeforeFiltersCollection;

                    const itemsListBeforeFilters = wp.hooks.hasFilter(`tainacan_faceted_search_items_list_before`) && wp.hooks.applyFilters(`tainacan_faceted_search_items_list_before`, '');
                    const itemsListBeforeFiltersCollection = (wp.hooks.hasFilter(`tainacan_faceted_search_collection_${this.collectionId}_items_list_before`) || itemsListBeforeFilters) && wp.hooks.applyFilters(`tainacan_faceted_search_collection_${this.collectionId}_items_list_before`, itemsListBeforeFilters);
                    if (itemsListBeforeFiltersCollection)
                        this.hooks['items_list_before'] = itemsListBeforeFiltersCollection;

                    const itemsListAfterFilters = wp.hooks.hasFilter(`tainacan_faceted_search_items_list_after`) && wp.hooks.applyFilters(`tainacan_faceted_search_items_list_after`, ''); 
                    const itemsListAfterFiltersCollection = (wp.hooks.hasFilter(`tainacan_faceted_search_collection_${this.collectionId}_items_list_after`) || itemsListAfterFilters) && wp.hooks.applyFilters(`tainacan_faceted_search_collection_${this.collectionId}_items_list_after`, itemsListAfterFilters); 
                    if (itemsListAfterFiltersCollection)
                        this.hooks['items_list_after'] = itemsListAfterFiltersCollection; 

                    const paginationBeforeFilters = wp.hooks.hasFilter(`tainacan_faceted_search_pagination_before`) && wp.hooks.applyFilters(`tainacan_faceted_search_pagination_before`, '');
                    const paginationBeforeFiltersCollection = (wp.hooks.hasFilter(`tainacan_faceted_search_collection_${this.collectionId}_pagination_before`) || paginationBeforeFilters) && wp.hooks.applyFilters(`tainacan_faceted_search_collection_${this.collectionId}_pagination_before`, paginationBeforeFilters);
                    if (paginationBeforeFiltersCollection)
                        this.hooks['pagination_before'] = paginationBeforeFiltersCollection;

                    const paginationAfterFilters = wp.hooks.hasFilter(`tainacan_faceted_search_pagination_after`) && wp.hooks.applyFilters(`tainacan_faceted_search_pagination_after`, ''); 
                    const paginationAfterFiltersCollection = (wp.hooks.hasFilter(`tainacan_faceted_search_collection_${this.collectionId}_pagination_after`) || paginationAfterFilters) && wp.hooks.applyFilters(`tainacan_faceted_search_collection_${this.collectionId}_pagination_after`, paginationAfterFilters); 
                    if (paginationAfterFiltersCollection)
                        this.hooks['pagination_after'] = paginationAfterFiltersCollection; 

                    const itemsListAreaAfterFilters = wp.hooks.hasFilter(`tainacan_faceted_search_items_list_area_after`) && wp.hooks.applyFilters(`tainacan_faceted_search_items_list_area_after`, '');
                    const itemsListAreaAfterFiltersCollection = (wp.hooks.hasFilter(`tainacan_faceted_search_collection_${this.collectionId}_items_list_area_after`) || itemsListAreaAfterFilters) && wp.hooks.applyFilters(`tainacan_faceted_search_collection_${this.collectionId}_items_list_area_after`, itemsListAreaAfterFilters);
                    if (itemsListAreaAfterFiltersCollection)
                        this.hooks['items_list_area_after'] = itemsListAreaAfterFiltersCollection;
                }
            },
            openExposersModal() {
                this.$buefy.modal.open({
                    parent: this,
                    component: defineAsyncComponent(() => import('../../../admin/components/modals/exposers-modal.vue')),
                    hasModalCard: true,
                    props: { 
                        collectionId: this.collectionId,
                        totalItems: this.totalItems
                    },
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
            onChangeViewMode(viewMode) {

                // Resets inital position in case it was defined before
                this.initialItemPosition = null;

                // We need to load metadata again as fetch_only might change from view mode
                this.prepareMetadata();

                // For view modes such as slides, we force pagination to request only 24 per page
                let existingViewModeIndex = Object.keys(this.registeredViewModes).findIndex(aViewMode => aViewMode == viewMode);
                if (existingViewModeIndex >= 0) {
                    if (!this.registeredViewModes[Object.keys(this.registeredViewModes)[existingViewModeIndex]].show_pagination) {
                        this.latestPerPageAfterViewModeWithoutPagination = this.itemsPerPage;
                        this.latestPageAfterViewModeWithoutPagination = this.page;

                        this.$eventBusSearch.setItemsPerPage(24, true);
                    }
                }

                // Finally sets the new view mode
                this.$eventBusSearch.setViewMode(viewMode);
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
                
                let descriptionMetadatum = this.localDisplayedMetadata.find(metadatum => metadatum.metadata_type_object != undefined ? metadatum.metadata_type_object.related_mapped_prop == 'description' : false);
              
                // Updates Search
                let fetchOnlyArray = [
                    ((thumbnailMetadatum != undefined && thumbnailMetadatum.display) ? 'thumbnail' : null),
                    ((creationDateMetadatum != undefined && creationDateMetadatum.display) ? 'creation_date' : null),
                    (this.isRepositoryLevel ? 'title' : null),
                    (this.isRepositoryLevel && descriptionMetadatum.display ? 'description' : null)
                ];
                this.$eventBusSearch.addFetchOnly(fetchOnlyArray.filter((fetchOnly) => fetchOnly != null).toString(), false, fetchOnlyMetadatumIds.toString());

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
                }).then((resp) => {
                        resp.request
                            .then(() => {
                                this.sortingMetadata = [];

                                // Decides if custom meta will be loaded with item.
                                let shouldLoadMeta = this.registeredViewModes[this.viewMode].dynamic_metadata;

                                if (shouldLoadMeta) {

                                    // Loads user prefs object as we'll need to check if there's something configured by user 
                                    let prefsFetchOnly = !this.isRepositoryLevel ? `fetch_only_${this.collectionId}` : 'fetch_only';
                                    let prefsFetchOnlyMeta = !this.isRepositoryLevel ? `fetch_only_meta_${this.collectionId}` : 'fetch_only_meta';

                                    let prefsFetchOnlyObject = this.$userPrefs.get(prefsFetchOnly) ? (typeof this.$userPrefs.get(prefsFetchOnly) != 'string' ? this.$userPrefs.get(prefsFetchOnly) : this.$userPrefs.get(prefsFetchOnly).split(',')) : ['thumbnail'];
                                    let prefsFetchOnlyMetaObject = this.$userPrefs.get(prefsFetchOnlyMeta) ? this.$userPrefs.get(prefsFetchOnlyMeta).split(',') : [];

                                    let thumbnailMetadatumDisplay = this.hideItemsThumbnail ? null : (prefsFetchOnlyObject && Array.isArray(prefsFetchOnlyObject) ? ((prefsFetchOnlyObject.indexOf('thumbnail') >= 0)) : true);

                                    if (this.hideItemsThumbnail != true) {
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

                                    let creationDateMetadatumDisplay = (prefsFetchOnlyObject && Array.isArray(prefsFetchOnlyObject)) ? (prefsFetchOnlyObject.indexOf('creation_date') >= 0) : true;
                                
                                    let fetchOnlyArray = [
                                        (thumbnailMetadatumDisplay ? 'thumbnail' : null),
                                        (creationDateMetadatumDisplay ? 'creation_date' : null),
                                        (this.isRepositoryLevel ? 'title' : null),
                                        (this.isRepositoryLevel ? 'description' : null)
                                    ];
                                    this.$eventBusSearch.addFetchOnly(fetchOnlyArray.filter((fetchOnly) => fetchOnly != null).toString(), false, fetchOnlyMetadatumIds.toString());

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
                                    
                                // Loads only basic attributes necessary to view modes that do not allow custom meta
                                } else {
                            
                                    const basicAttributes = this.hideItemsThumbnail ? 'creation_date,title,description' : 'thumbnail,creation_date,title,description';
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
            showItemsHiddingDueSortingDialog() {

                if (this.isSortingByCustomMetadata &&
                    this.$userPrefs.get('neverShowItemsHiddenDueSortingDialog') != true) {     

                    this.hasAnOpenModal = true;

                    this.openMetatadaSortingWarningDialog({ offerCheckbox: true });
                }
            },
            typeFutureSearch(value) {
                this.futureSearchQuery = value;
                
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
                        component: defineAsyncComponent(() => import('../../../admin/components/other/custom-dialog.vue')),
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
                nextTick(() => {
                    if (window.innerWidth || document.documentElement.clientWidth || document.body.clientWidth) {
                        const previousMobileScreen = this.isMobileScreen;
                        const previousWindowWidth = this.windowWidth;
                        
                        this.windowWidth = (window.innerWidth || document.documentElement.clientWidth || document.body.clientWidth);
                        this.isMobileScreen = this.windowWidth <= 768;

                        // The window size didn't changed (the resize event is triggered by scrolls on mobile)
                        // Also if we're on advanced search we should not open the filters
                        if (previousWindowWidth == this.windowWidth || this.openAdvancedSearch)
                            return;

                        if (                                                    // We DO NOT want to open the filters due to this resize event IF:
                            (!previousMobileScreen && this.isMobileScreen) ||   // We're coming from a non-mobile screen to a mobile screen, or
                            this.startWithFiltersHidden                         // Filters should begin disabled
                        )
                            this.isFiltersModalActive = false;
                        else
                            this.isFiltersModalActive = true;
                    }
                });
            }, 500),
            removeEventListeners() {
                // Component
                this.$emitter.off();
                // Window
                if ( !this.hideFilters && !this.shouldNotHideFiltersOnMobile )
                    window.removeEventListener('resize', this.hideFiltersOnMobile);
                // $root
                if (!this.hideAdvancedSearch)
                    this.$emitter.off('openAdvancedSearch');
                // $eventBusSearch
                this.$eventBusSearchEmitter.off('isLoadingItems');
                this.$eventBusSearchEmitter.off('hasFiltered');

            },
        }
    }
</script>

<style lang="scss">

    // TAINACAN Variables
    @import "../../../admin/scss/_variables.scss";

    //Vue Tooltip
    @import "../../../../../node_modules/floating-vue/dist/style.css";

    // Bulma imports
    @import "./theme-search/scss/theme-basics.sass";

    // Buefy imports
    @import "../../../../../node_modules/@ntohq/buefy-next/src/scss/utils/_all.scss";
    @import "../../../../../node_modules/@ntohq/buefy-next/src/scss/components/_form.scss";
    @import "../../../../../node_modules/@ntohq/buefy-next/src/scss/components/_datepicker.scss";
    @import "../../../../../node_modules/@ntohq/buefy-next/src/scss/components/_checkbox.scss";
    @import "../../../../../node_modules/@ntohq/buefy-next/src/scss/components/_radio.scss";
    @import "../../../../../node_modules/@ntohq/buefy-next/src/scss/components/_tag.scss";
    @import "../../../../../node_modules/@ntohq/buefy-next/src/scss/components/_loading.scss";
    @import "../../../../../node_modules/@ntohq/buefy-next/src/scss/components/_dropdown.scss";
    @import "../../../../../node_modules/@ntohq/buefy-next/src/scss/components/_modal.scss";
    @import "../../../../../node_modules/@ntohq/buefy-next/src/scss/components/_dialog.scss";
    @import "../../../../../node_modules/@ntohq/buefy-next/src/scss/components/_notices.scss";
    @import "../../../../../node_modules/@ntohq/buefy-next/src/scss/components/_numberinput.scss";

    // Block level custom variables
    @import "../../../admin/scss/_custom_variables.scss";

    // These have to be outside of the scoped context
    @import "./theme-search/scss/_layout.scss";
    @import "../../../admin/scss/_tooltips.scss";
    @import "../../../admin/scss/_notices.scss";
    @import "../../../admin/scss/_modals.scss";

    // Scoped, to avoid conflicts with theme's css 
    .tainacan-modal,
    .theme-items-list {

        // Vue Blurhash transtition effect
        @import '../../../../../node_modules/another-vue3-blurhash/dist/style.css';
        canvas.child {
            max-width: 100%;
        }

        // Tainacan imports
        @import "../../../admin/scss/_modals.scss";
        @import "../../../admin/scss/_buttons.scss"; 
        @import "../../../admin/scss/_inputs.scss";
        @import "../../../admin/scss/_checkboxes.scss";
        @import "../../../admin/scss/_pagination.scss";
        @import "../../../admin/scss/_tags.scss";
        @import "../../../admin/scss/_tabs.scss";
        @import "../../../admin/scss/_selects.scss";
        @import "../../../admin/scss/_dropdown-and-autocomplete.scss";
        @import "../../../admin/scss/_control.scss";
        @import "../../../admin/scss/_tainacan-form.scss";
        @import "../../../admin/scss/_filters-menu-modal.scss";

        &:not(.tainacan-modal) {
            background: var(--tainacan-background-color, inherit);
            position: relative;
        }
        font-size: var(--tainacan-base-font-size, inherit);
        font-family: var(--tainacan-font-family, inherit);
        -webkit-overflow-scrolling: touch;

        * {
            // For Firefox
            scrollbar-color: var(--tainacan-gray3) transparent;
            scrollbar-width: thin;

            // For Chromium and related
            &::-webkit-scrollbar {
                width: 0.55em;
                opacity: 0.8;
            }
            &::-webkit-scrollbar-thumb {
                background-color: var(--tainacan-gray3);
            }
        }

        h1,
        h2,
        h3,
        h4,
        h5,
        h6 {
            margin: 0;
        }
        
        a, a:not([href]) { 
            color: var(--tainacan-secondary);
        }
        a:hover, 
        a:hover:not([href]) {
            cursor: pointer;
            color: var(--tainacan-secondary);
            text-decoration: underline;
        }
        ul {
            list-style: none;
        }

        /* WordPress customize shortcut edit buttons are not appearing properly */
        .customize-partial-edit-shortcut button,
        .widget .customize-partial-edit-shortcut button {
            opacity: 1;
            animation: none;
            left: 30px;
            top: 2px;
            pointer-events: auto;
        }

        // We need this because bootstrap messes up with this class
        .dropdown-menu {
            display: block;
        }
        .dropdown.is-inline .dropdown-content {
            display: flex;
            border: none;

            .dropdown-item {
                padding: 0.125em 0.5em !important;

                .gray-icon {
                    padding: 0;
                }
            }
        }

        // Some themes set a low max-width or marigns to inputs, this also messes up with Bulma inputs such as taginput.
        input[type="text"],
        input[type="password"],
        input[type="search"],
        input[type="email"],
        input[type="url"],
        input[type="tel"],
        input[type="number"],
        input[type="range"], input[type="date"],
        textarea {
            max-width: 100%;
            margin-top: initial;
            margin-bottom: initial;
        }

        .date-filter-container,
        .numeric-filter-container {
            @media screen and (min-width: 1366px) {
                flex-wrap: nowrap !important;
                height: auto !important;
                align-items: stretch;
            }
        }

        .metadata-value {
            .tainacan-compound-group {
                margin-left: 2px;
                padding-left: 0.875em;
                border-left: 1px solid var(--tainacan-gray3);

                .tainacan-compound-metadatum .label {
                    margin-bottom: 0.25em;
                    font-size: 1em !important;
                    color: var(--tainacan-info-color);
                }
                .tainacan-compound-metadatum p {
                    margin-bottom: 0.75em;
                    font-size: 1em;
                }
                .tainacan-metadatum .label {
                    font-size: 1em;
                }
                .multivalue-separator {
                    display: block;
                    max-height: 1px;
                    width: 80px;
                    background: var(--tainacan-gray3);
                    content: none;
                    color: transparent;
                    margin: 1em auto 1em -0.875em;
                }
            }
            .tainacan-relationship-group {
                .tainacan-relationship-metadatum {
                    .tainacan-relationship-metadatum-header {
                        display: flex;
                        align-items: center;
                        img {
                            margin-right: 12px;
                            max-width: 22px;
                            max-height: 22px;
                        }
                        .label {
                            font-weight: normal;
                            font-size: 1em !important;
                            margin-top: 0;
                            margin-left: 0;
                            margin-bottom: 0;
                            margin-right: 0;
                        }
                    }
                    .tainacan-metadatum {
                        margin-left: 40px;
                        .label {
                            color: var(--tainacan-gray4);
                            font-size: 1em !important;
                            line-height: 1em;
                            margin-top: 8px;
                            margin-bottom: 2px;
                        }
                    }
                }
                &>.multivalue-separator {
                    display: block;
                    max-height: 1px;
                    width: calc(100% - 40px);
                    background: var(--tainacan-gray2);
                    content: none;
                    color: transparent;
                    margin: 0.5em 0 0.5em 40px;
                }
            }
        }
    }
    .loading-overlay {
        min-height: auto !important;
    }
</style>

<style lang="scss" scoped>

    .advanced-search-form-submit {
        display: flex;
        justify-content: flex-end;
        padding-right: var(--tainacan-one-column);
        padding-left: var(--tainacan-one-column);
        margin-bottom: 1em;

        p { margin-left: 0.75em; }
    }

    .page-container {
        padding: 0;
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
        display: flex;
        align-items: center;

        &:focus {
            outline: none !important;
        }

        @media screen and (max-width: 768px) {
            max-width: 100%;
            width: auto;
            padding: 3px 6px 3px 0px;
            height: 1.625em;
        }
    }

    .search-control {
        position: relative;
        display: flex;
        flex-wrap: wrap;
        justify-content: space-between !important;
        min-height: 42px;
        height: auto;
        padding: var(--tainacan-container-padding) var(--tainacan-one-column) 20px var(--tainacan-one-column);

        .dropdown-item {
            padding: 0.25em 1.35em 0.25em 0.25em;
        }
        .view-mode-icon {
            margin-right: 0px !important;
            margin-top: -2px;
            margin-left: 4px;
            width: 1.25em;

            &.icon i::before, 
            .gray-icon i::before {
                font-size: 1.1875px !important;
            }
        }
        .control {
            font-size: 1em;
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
                        max-width: 100% !important;
                    }
                }
            }

            &:last-child {
                margin-right: 0;
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

            .button:not(.is-success),
            .button:hover:not(.is-success),
            .button:focus:not(.is-success) {
                display: flex;
                align-items: center;
                color: var(--tainacan-input-color) !important;
                background: transparent;
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
                justify-content: space-between;
                &.is-small {
                    width: 1em;
                    height: 1em;
                }
            }
            .gray-icon .icon i::before, 
            .gray-icon i::before {
                font-size: 1.3125em !important;
                color: var(--tainacan-info-color) !important;
                max-width: 1.25em;
                svg {
                    color: var(--tainacan-info-color) !important;
                    overflow: hidden;
                    vertical-align: middle;
                }
            }
            .has-text-secondary.gray-icon .icon i::before, 
            .has-text-secondary.gray-icon i::before {
                color: var(--tainacan-secondary) !important;
                svg {
                    fill: var(--tainacan-secondary) !important;
                    overflow: hidden;
                    vertical-align: middle;
                }
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
                max-width: 16.66667vw;

                .tainacan-textual-search-input {
                    width: 100%;

                    :deep(.dropdown-trigger) {
                        width: 100%;
                    }
                    :deep(.dropdown-menu) {
                        z-index: 99999991;

                        .dropdown-item:last-child {
                            line-height: 2.25em;
                            background: var(--tainacan-item-hover-background-color);

                            @media screen and (max-width: 768px) {
                                white-space: initial;
                                line-height: 2.125em;
                            }
                        }
                    }
                }

                .control {
                    width: 100%;
                    margin: -2px 0 5px 0;
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

            &#tainacanFiltersButton.is-filters-modal-active .gray-icon i::before {
                color: var(--tainacan-secondary) !important;
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

    .filter-tags-list {
        padding-top: 0.5em;
        padding-bottom: 1.0em;
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

    #items-list-area {
        position: relative;
        height: 100%;
        overflow-y: hidden;
        overflow-x: hidden;
        -webkit-overflow-scrolling: touch;
        margin-left: 0;

        // Metadata type textarea has different separators in different spots on interface
        .multivalue-separator {
            color: var(--tainacan-gray3);
            margin: 0 8px;    
        }
        .metadata-type-textarea {
            .multivalue-separator {
                display: block;
                max-height: 1px;
                width: 80px;
                background: var(--tainacan-gray3);
                content: none;
                color: transparent;
                margin: 1em auto;
            }
        }
    }

    .table-container {
        padding-left: var(--tainacan-one-column);
        padding-right: var(--tainacan-one-column);
        min-height: 50vh;
        margin-top: 12px;
        //height: calc(100% - 82px);
    }

    .pagination-area {
        margin-left: var(--tainacan-one-column);
        margin-right: var(--tainacan-one-column);
    }

</style>
