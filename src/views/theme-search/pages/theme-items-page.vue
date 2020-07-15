<template>
    <div 
            :class="{
                'is-filters-menu-open': !hideFilters && isFiltersModalActive && !openAdvancedSearch,
                'repository-level-page': isRepositoryLevel,
                'is-fullscreen': registeredViewModes[viewMode] != undefined && registeredViewModes[viewMode].full_screen
            }"
            aria-live="polite">
        
        <!-- ADVANCED SEARCH -->
        <div 
                id="advanced-search-container"
                role="search"
                v-if="openAdvancedSearch && !hideAdvancedSearch">

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

        <!-- SEARCH CONTROL ------------------------- -->
        <h3 
                id="search-control-landmark"
                class="sr-only">
            {{ $i18n.get('label_sort_visualization') }}
        </h3>
        <div
                :aria-label="$i18n.get('label_sort_visualization')"
                role="region"
                ref="search-control"
                v-if="!(registeredViewModes[viewMode] != undefined && registeredViewModes[viewMode].full_screen) && ((openAdvancedSearch && advancedSearchResults) || !openAdvancedSearch)"
                class="search-control">

            <!-- <b-loading
                    :is-full-page="false"
                    :active.sync="isLoadingMetadata"/> --> 

            <!-- Button for hiding filters -->
            <button 
                    aria-controls="filters-modal"
                    :aria-expanded="isFiltersModalActive"
                    :class="hideHideFiltersButton ? 'is-hidden-tablet' : ''"
                    v-if="!showFiltersButtonInsideSearchControl && !hideFilters && !openAdvancedSearch && !(registeredViewModes[viewMode] != undefined && registeredViewModes[viewMode].full_screen)"
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
            <div 
                    v-if="!hideSearch && !openAdvancedSearch"
                    class="search-control-item">
                <div 
                        role="search" 
                        class="search-area">
                    <b-input
                        size="is-small"
                        :placeholder="$i18n.get('instruction_search')"
                        type="search"
                        :aria-label="$i18n.get('instruction_search') + ' ' + $i18n.get('items')"
                        :value="searchQuery"
                        @input.native="futureSearchQuery = $event.target.value"
                        @keyup.enter.native="updateSearch()"
                        icon-right="magnify"
                        icon-right-clickable
                        @icon-right-click="updateSearch()" />
                    <a
                            v-if="!hideAdvancedSearch"
                            @click="openAdvancedSearch = !openAdvancedSearch; $eventBusSearch.clearAllFilters();"
                            style="font-size: 0.75em;"
                            class="has-text-secondary is-pulled-right">
                        {{ $i18n.get('advanced_search') }}
                    </a>
                </div>
            </div>

            <!-- Another option of the Button for hiding filters -->
            <div 
                    v-if="showFiltersButtonInsideSearchControl && !hideHideFiltersButton && !hideFilters && !openAdvancedSearch"
                    class="search-control-item"
                    id="tainacanFiltersButton">
                <button 
                        class="button is-white"
                        :aria-label="$i18n.get('filters')"
                        @click="isFiltersModalActive = !isFiltersModalActive">
                    <span class="gray-icon">
                            <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-filters"/>
                    </span>
                    <span class="is-hidden-touch">{{ $i18n.get('filters') }}</span>
                </button>
            </div>

            <!-- Displayed Metadata Dropdown -->
            <div    
                    v-if="!hideDisplayedMetadataButton && (registeredViewModes[viewMode] != undefined && registeredViewModes[viewMode].dynamic_metadata)"
                    class="search-control-item">
                <b-dropdown
                        v-tooltip="{
                            delay: {
                                show: 500,
                                hide: 300,
                            },
                            content: totalItems <= 0 ? $i18n.get('info_cant_select_metadata_without_items') : '',
                            autoHide: false,
                            placement: 'auto-start',
                            classes: ['tooltip', isRepositoryLevel ? 'repository-tooltip' : '']
                        }" 
                        ref="displayedMetadataDropdown"
                        :mobile-modal="true"
                        :disabled="totalItems <= 0"
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
                            <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-arrowdown"/>
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
            <div 
                    v-show="!hideSortingArea"
                    class="search-control-item sorting-area">
                <b-field>
                    <label class="label">{{ $i18n.get('label_sort') }}</label>
                    <b-dropdown
                            :mobile-modal="true"
                            @input="onChangeOrder"
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
                    <template v-if="!hideSortByButton">
                        <span
                                class="label"
                                style="padding-left: 2px !important;">
                            {{ $i18n.get('info_by_inner') }}
                        </span>
                        <b-dropdown
                                id="tainacanSortByDropdown"
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
                    </template>
                </b-field>
            </div>

            <!-- View Modes Dropdown -->
            <div 
                    v-if="enabledViewModes.length > 1"
                    class="search-control-item"
                    id="tainacanViewModesSection">
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
                            @change="onChangeViewMode($event)"
                            :inline="showInlineViewModeOptions"
                            :mobile-modal="true"
                            position="is-bottom-left"
                            :aria-label="$i18n.get('label_view_mode')"
                            aria-role="list"
                            trap-focus>
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
                                <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-arrowdown" />
                            </span>
                        </button>
                        <b-dropdown-item 
                                aria-controls="items-list-results"
                                role="button"
                                :class="{ 'is-active': viewModeOption == viewMode }"
                                v-for="(viewModeOption, index) of enabledViewModes"
                                :key="index"
                                :value="viewModeOption"
                                v-if="(registeredViewModes[viewModeOption] != undefined && registeredViewModes[viewModeOption].full_screen == false) || (showFullscreenWithViewModes && registeredViewModes[viewModeOption] != undefined)"
                                aria-role="listitem">
                            <span 
                                    v-if="!showInlineViewModeOptions"
                                    class="gray-icon"
                                    v-html="registeredViewModes[viewModeOption].icon"/>
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
                                        classes: ['tooltip', isRepositoryLevel ? 'repository-tooltip' : '']
                                    }"
                                    class="gray-icon"
                                    v-html="registeredViewModes[viewModeOption].icon"/>
                            <span v-if="!showInlineViewModeOptions">{{ registeredViewModes[viewModeOption].label }}</span>
                        </b-dropdown-item>
                    </b-dropdown>
                </b-field>
            </div>

            <!-- Theme Full Screen mode, it's just a special view mode -->
            <div 
                    id="tainacanFullScreenViewMode"
                    class="search-control-item">
                <button 
                        class="button is-white"
                        :aria-label="$i18n.get('label_slideshow')"
                        @click="onChangeViewMode(viewModeOption)"
                        v-for="(viewModeOption, index) of enabledViewModes"
                        :key="index"
                        :value="viewModeOption"
                        v-if="!showFullscreenWithViewModes && registeredViewModes[viewModeOption] != undefined && registeredViewModes[viewModeOption].full_screen == true ">
                    <span 
                            class="gray-icon view-mode-icon"
                            v-html="registeredViewModes[viewModeOption].icon"/>
                    <span class="is-hidden-tablet-only">{{ registeredViewModes[viewModeOption].label }}</span>
                </button>
            </div>

            <!-- Exposers or alternative links modal button -->
            <div 
                    id="tainacanExposersButton"
                    v-if="!hideExposersButton"
                    class="search-control-item">
                <button 
                        class="button is-white"
                        :aria-label="$i18n.get('label_view_as')"
                        :disabled="totalItems == undefined || totalItems <= 0"
                        @click="openExposersModal()">
                    <span class="gray-icon">
                            <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-viewas"/>
                    </span>
                    <span class="is-hidden-tablet-only is-hidden-desktop-only ">{{ $i18n.get('label_view_as') }}</span>
                </button>
            </div>
        </div>

        <!-- SIDEBAR WITH FILTERS -->
        <b-modal
                v-if="!hideFilters"
                role="region"
                aria-labelledby="filters-label-landmark"
                id="filters-modal"     
                ref="filters-modal"       
                :active.sync="isFiltersModalActive"
                :width="736"
                animation="slide-menu"
                :trap-focus="filtersAsModal"
                full-screen
                :custom-class="'tainacan-form filters-menu' + (filtersAsModal ? ' filters-menu-modal' : '')"
                :can-cancel="hideHideFiltersButton ? ['x', 'outside'] : ['x', 'escape', 'outside']">
            <filters-items-list
                    :is-loading-items="isLoadingItems"
                    :autofocus="filtersAsModal"
                    :tabindex="filtersAsModal ? -1 : 0"
                    :aria-modal="filtersAsModal"
                    :role="filtersAsModal ? 'dialog' : ''"
                    id="filters-items-list"
                    :taxonomy="taxonomy"
                    :collection-id="collectionId"
                    :is-repository-level="isRepositoryLevel"/>
        </b-modal>

        <!-- ITEMS LIST AREA (ASIDE THE ASIDE) ------------------------- -->
        <div 
                id="items-list-area"
                class="items-list-area">

            <!-- FILTERS TAG LIST-->
            <filters-tags-list
                    class="filter-tags-list"
                    v-if="!hideFilters &&
                        hasFiltered && 
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
                       
                <!-- Theme View Modes -->
                <div 
                        v-if="((openAdvancedSearch && advancedSearchResults) || !openAdvancedSearch) &&
                              !isLoadingItems &&
                              registeredViewModes[viewMode] != undefined &&
                              registeredViewModes[viewMode].type == 'template'"
                        v-html="itemsListTemplate"/>

                <component
                        v-if="registeredViewModes[viewMode] != undefined &&
                              registeredViewModes[viewMode].type == 'component' &&
                              ((openAdvancedSearch && advancedSearchResults) || !openAdvancedSearch)"
                        :collection-id="collectionId"
                        :displayed-metadata="displayedMetadata" 
                        :items="items"
                        :is-filters-menu-compressed="!hideFilters && !isFiltersModalActive"
                        :total-items="totalItems"
                        :is-loading="showLoading"
                        :is="registeredViewModes[viewMode] != undefined ? registeredViewModes[viewMode].component : ''"/>     
        
                <!-- Pagination -->
                <pagination
                        v-show="!hidePaginationArea"
                        :is-sorting-by-custom-metadata="isSortingByCustomMetadata"
                        v-if="totalItems > 0 &&
                            ((registeredViewModes[viewMode] != undefined && registeredViewModes[viewMode].show_pagination)) &&
                            (advancedSearchResults || !openAdvancedSearch)"
                        :hide-items-per-page-button="hideItemsPerPageButton"
                        :hide-go-to-page-button="hideGoToPageButton"/>
            </div>
        </div>
       
    </div>
</template>

<script>
    import FiltersTagsList from '../../admin/components/search/filters-tags-list.vue';
    import FiltersItemsList from '../../admin/components/search/filters-items-list.vue';
    import Pagination from '../../admin/components/search/pagination.vue'
    import AdvancedSearch from '../../admin/components/search/advanced-search.vue';
    import ExposersModal from '../../admin/components/modals/exposers-modal.vue';
    import CustomDialog from '../../admin/components/other/custom-dialog.vue';
    import { mapActions, mapGetters } from 'vuex';

    export default {
        name: 'ThemeItemsPage',
        components: {
            FiltersTagsList,
            FiltersItemsList,
            Pagination,
            AdvancedSearch,
            ExposersModal
        },
        props: {
            // Source settings
            collectionId: Number,
            termId: Number,
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
            hideExposersButton: false,
            hideItemsPerPageButton: false,
            hideGoToPageButton: false,
            hidePaginationArea: false,
            // Other Tweaks
            defaultItemsPerPage: Number,
            showFiltersButtonInsideSearchControl: false,
            startWithFiltersHidden: false,
            filtersAsModal: false,
            showInlineViewModeOptions: false,
            showFullscreenWithViewModes: false
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
                metadataSearchCancel: undefined,
                isMobile: false
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
                return this.getTotalItems();
            },
            metadata() {
                return this.getMetadata();
            },
            searchQuery() {
                return this.getSearchQuery();
            },
            viewMode() {
                return this.getViewMode();
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
                return '';
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
                if (this.isFiltersModalActive) {
                    setTimeout(() => {
                        if (this.filtersAsModal && this.$refs['filters-modal'] && this.$refs['filters-modal'].focus)
                            this.$refs['filters-modal'].focus();
                            
                        if (!this.filtersAsModal && !this.isMobile && document.documentElement)
                            document.documentElement.classList.remove('is-clipped');
                    }, 800);
                    
                }
            }
        },
        created() {

            this.isRepositoryLevel = (this.collectionId == undefined || this.collectionId == '' || this.collectionId == null);

            if (this.collectionId != undefined)
                this.$eventBusSearch.setCollectionId(this.collectionId);

            if (this.termId != undefined && this.termId != null)
                this.$eventBusSearch.setTerm(this.termId, this.taxonomy);
            
            this.$eventBusSearch.updateStoreFromURL();

            this.$eventBusSearch.$on('isLoadingItems', isLoadingItems => {
                this.isLoadingItems = isLoadingItems;
            });

            this.$eventBusSearch.$on('hasFiltered', hasFiltered => {
                this.hasFiltered = hasFiltered;
            });
            
            if (!this.hideAdvancedSearch) {
                this.$eventBusSearch.$on('advancedSearchResults', advancedSearchResults => {
                    this.advancedSearchResults = advancedSearchResults;
                });

                if (this.$route.query && this.$route.query.advancedSearch) {
                    this.openAdvancedSearch = this.$route.query.advancedSearch;
                }

                this.$root.$on('openAdvancedSearch', (openAdvancedSearch) => {
                    this.openAdvancedSearch = openAdvancedSearch;
                });
            }

            this.$eventBusSearch.$on('hasToPrepareMetadataAndFilters', () => {
                /* This condition is to prevent an incorrect fetch by filter or metadata when we come from items
                 * at collection level to items page at repository level
                 */
                this.prepareMetadata();
            });
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
                let enabledViewModeIndex = this.enabledViewModes.findIndex((viewMode) => viewMode == userPrefViewMode);
                if (existingViewModeIndex >= 0 && enabledViewModeIndex >= 0)
                    this.$eventBusSearch.setInitialViewMode(userPrefViewMode);
                else   
                    this.$eventBusSearch.setInitialViewMode(this.defaultViewMode);
            }
            // For view modes such as slides, we force pagination to request only 12 per page
            let existingViewModeIndex = Object.keys(this.registeredViewModes).findIndex(viewMode => viewMode == this.$userPrefs.get(prefsViewMode));
            if (existingViewModeIndex >= 0) {
                if (!this.registeredViewModes[Object.keys(this.registeredViewModes)[existingViewModeIndex]].show_pagination) {
                    this.$eventBusSearch.setItemsPerPage(12, true);
                }
            }
            
            // If any default items per page is set, apply it
            if (this.defaultItemsPerPage)
                this.$eventBusSearch.setItemsPerPage(this.defaultItemsPerPage, true); 

            this.showItemsHiddingDueSortingDialog();

            // Watches window resize to adjust filter's top position and compression on mobile
            if (!this.hideFilters) {            
                this.hideFiltersOnMobile();
                window.addEventListener('resize', this.hideFiltersOnMobile);
            }
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
                'getItemsListTemplate'
            ]),
            ...mapActions('metadata', [
                'fetchMetadata'
            ]),
            ...mapGetters('metadata', [
                'getMetadata'
            ]),
            ...mapGetters('search', [
                'getSearchQuery',
                'getOrderBy',
                'getOrderByName',
                'getOrder',
                'getViewMode',
                'getTotalItems',
                'getMetaKey'
            ]),
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
                });
            },
            updateSearch() {
                this.$eventBusSearch.setSearchQuery(this.futureSearchQuery);
            },  
            onChangeOrderBy(metadatum) {
                this.$eventBusSearch.setOrderBy(metadatum);
                this.showItemsHiddingDueSortingDialog();
            },
            onChangeOrder(newOrder) {
                if (newOrder != this.order)
                    this.$eventBusSearch.setOrder(newOrder);
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
                this.$eventBusSearch.addFetchOnly(
                    ((thumbnailMetadatum != undefined && thumbnailMetadatum.display) ? 'thumbnail' : null) + ',' +
                    ((creationDateMetadatum != undefined && creationDateMetadatum.display) ? 'creation_date' : null) + ',' +
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
                                                metadatum.metadata_type != 'Tainacan\\Metadata_Types\\Relationship' &&
                                                metadatum.metadata_type != 'Tainacan\\Metadata_Types\\Compound' &&
                                                metadatum.metadata_type != 'Tainacan\\Metadata_Types\\User'
                                            ) {
                                                this.sortingMetadata.push(metadatum);
                                            }

                                        }
                                        
                                    }

                                    let creationDateMetadatumDisplay = prefsFetchOnlyObject ? (prefsFetchOnlyObject[1] != null) : true;
                                
                                    this.$eventBusSearch.addFetchOnly(
                                        (thumbnailMetadatumDisplay ? 'thumbnail' : null) +','+
                                        (creationDateMetadatumDisplay ? 'creation_date' : null) +','+
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
                                    
                                // Loads only basic attributes necessary to view modes that do not allow custom meta
                                } else {
                            
                                    this.$eventBusSearch.addFetchOnly('thumbnail,creation_date,title,description', true, '');
                                    
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
                                            metadatum.metadata_type != 'Tainacan\\Metadata_Types\\Relationship' &&
                                            metadatum.metadata_type != 'Tainacan\\Metadata_Types\\Compound' &&
                                            metadatum.metadata_type != 'Tainacan\\Metadata_Types\\User'
                                            ) {
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
                            showNeverShowAgainOption: offerCheckbox && tainacan_plugin.user_caps != undefined && Object.keys(tainacan_plugin.user_caps).length != undefined && Object.keys(tainacan_plugin.user_caps).length > 0,
                            messageKeyForUserPrefs: 'ItemsHiddenDueSorting'
                        },
                        trapFocus: true
                    });
            },
            hideFiltersOnMobile: _.debounce( function() {
                this.$nextTick(() => {
 
                    if (window.innerWidth || document.documentElement.clientWidth || document.body.clientWidth) {
                        this.isMobile = (window.innerWidth || document.documentElement.clientWidth || document.body.clientWidth) <= 768;
                        
                        if (this.isMobile || this.startWithFiltersHidden || this.openAdvancedSearch)
                            this.isFiltersModalActive = false;
                        else
                            this.isFiltersModalActive = true;
                    }
                });
            }, 500),
            removeEventListeners() {
                // Component
                this.$off();
                // Window
                if (!this.hideFilters)
                    window.removeEventListener('resize', this.hideFiltersOnMobile);
                // $root
                if (!this.hideAdvancedSearch)
                    this.$root.$off('openAdvancedSearch');
                // $eventBusSearch
                this.$eventBusSearch.$off('isLoadingItems');
                this.$eventBusSearch.$off('hasFiltered');
                if (!this.hideAdvancedSearch)
                    this.$eventBusSearch.$off('advancedSearchResults');
                this.$eventBusSearch.$off('hasToPrepareMetadataAndFilters');

            },
        }
    }
</script>

<style lang="scss" scoped>

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
        background-color: var(--tainacan-black);
        transition: background-color 0.3s ease, width 0.3s ease, height 0.3s ease;
        animation: open-full-screen 0.4s ease;

        .filters-menu {
            display: none;
        }
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
        margin: 0 var(--tainacan-one-column) 42px var(--tainacan-one-column);

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
        padding-right: var(--tainacan-one-column);
        padding-left: var(--tainacan-one-column);
        margin-bottom: 1em;

        p { margin-left: 0.75em; }
    }

    .tnc-advanced-search-close {
        padding-top: 47px;
        padding-right: var(--tainacan-one-column);
        padding-left: var(--tainacan-one-column);

        .column {
            padding: 0 0.3em 0.3em !important;
        }
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
                margin-right: auto;
            }

            .label {
                color: var(--tainacan-label-color);
                font-size: 0.875em;
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

                .control {
                    width: 100%;
                    margin: -2px 0 5px 0;
                }
                .is-pulled-right {
                    position: absolute;
                    right: 0;
                    top: 100%;
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
                width: 60px;
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
        //height: calc(100% - 82px);
    }

    .pagination-area {
        margin-left: var(--tainacan-one-column);
        margin-right: var(--tainacan-one-column);
    }

</style>


