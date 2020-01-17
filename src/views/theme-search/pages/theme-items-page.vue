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
                :style="{ top: '76px' }"
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
                :style="{ top: (searchControlHeight - 25) + 'px' }"
                @click="isFilterModalActive = !isFilterModalActive">
            <span class="icon">
                <i 
                        :class="{ 'tainacan-icon-arrowleft' : !isFiltersMenuCompressed, 'tainacan-icon-arrowright' : isFiltersMenuCompressed }"
                        class="tainacan-icon tainacan-icon-20px"/>
            </span>
            <span class="text">{{ $i18n.get('filters') }}</span>
        </button>

        <!-- Sidebar with search and filters -->
        <aside
                id="filters-desktop-aside"
                role="region"
                aria-labelledby="filters-label-landmark"
                :style="{ top: searchControlHeight + 'px' }"
                v-show="!isFiltersMenuCompressed && 
                        !openAdvancedSearch && 
                        !(registeredViewModes[viewMode] != undefined && registeredViewModes[viewMode].full_screen)"
                class="filters-menu tainacan-form is-hidden-mobile">

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

            <filters-items-list
                    :taxonomy="taxonomy"
                    :collection-id="collectionId"
                    :is-repository-level="isRepositoryLevel"/>

        </aside>
        
        <!-- ITEMS LIST AREA (ASIDE THE ASIDE) ------------------------- -->
        <div 
                id="items-list-area"
                class="items-list-area"
                :class="{ 'spaced-to-right': !isFiltersMenuCompressed && !openAdvancedSearch && !(registeredViewModes[viewMode] != undefined && registeredViewModes[viewMode].full_screen)}">

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
                        :is-do-search="isDoSearch"/>

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

            <!-- SEARCH CONTROL ------------------------- -->
            <div
                    :aria-label="$i18n.get('label_sort_visualization')"
                    role="region"
                    ref="search-control"
                    v-if="!(registeredViewModes[viewMode] != undefined && registeredViewModes[viewMode].full_screen) && ((openAdvancedSearch && advancedSearchResults) || !openAdvancedSearch)"
                    class="search-control">
                
                <h3 
                        id="search-control-landmark"
                        class="sr-only">
                    {{ $i18n.get('label_sort_visualization') }}
                </h3>

                <!-- <b-loading
                        :is-full-page="false"
                        :active.sync="isLoadingMetadata"/> -->    

                <!-- Displayed Metadata Dropdown -->
                <div    
                        v-if="(registeredViewModes[viewMode] != undefined && registeredViewModes[viewMode].dynamic_metadata)"
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
                                aria-role="list"
                                trap-focus>
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
                <div class="search-control-item">
                    <b-field>
                        <label class="label is-hidden-mobile">{{ $i18n.get('label_visualization') + ':&nbsp; ' }}</label>
                        <b-dropdown
                                @change="onChangeViewMode($event)"
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

                <!-- Theme Full Screen mode, it's just a special view mode -->
                <div class="search-control-item">
                    <button 
                            class="button is-white"
                            :aria-label="$i18n.get('label_slideshow')"
                            @click="onChangeViewMode(viewModeOption)"
                            v-for="(viewModeOption, index) of enabledViewModes"
                            :key="index"
                            :value="viewModeOption"
                            v-if="registeredViewModes[viewModeOption] != undefined && registeredViewModes[viewModeOption].full_screen == true ">
                        <span 
                                class="gray-icon view-mode-icon"
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
                            :aria-label="$i18n.get('label_view_as')"
                            :disabled="totalItems == undefined || totalItems <= 0"
                            @click="openExposersModal()">
                        <span class="gray-icon">
                                <i class="tainacan-icon tainacan-icon-20px tainacan-icon-url"/>
                        </span>
                        <span class="is-hidden-touch">{{ $i18n.get('label_view_as') }}</span>
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
                        :is-filters-menu-compressed="isFiltersMenuCompressed"
                        :total-items="totalItems"
                        :is-loading="showLoading"
                        :is="registeredViewModes[viewMode] != undefined ? registeredViewModes[viewMode].component : ''"/>     
        
                <!-- Pagination -->
                <pagination
                        :is-sorting-by-custom-metadata="isSortingByCustomMetadata"
                        v-if="totalItems > 0 &&
                         ((registeredViewModes[viewMode] != undefined && registeredViewModes[viewMode].show_pagination)) &&
                          (advancedSearchResults || !openAdvancedSearch)"/>
            </div>
        </div>
       
        <b-modal
                role="region"
                aria-labelledby="filters-label-landmark"
                id="filters-mobile-modal"
                class="tainacan-form is-hidden-tablet"                
                :active.sync="isFilterModalActive"
                :width="736"
                animation="slide-menu"
                trap-focus
                aria-modal
                aria-role="dialog">
            <div
                    ref="filters-mobile-modal"
                    class="modal-inner-content"
                    autofocus="true"
                    tabindex="-1"
                    aria-modal
                    role="dialog">

                <filters-items-list
                        id="filters-items-list"
                        :taxonomy="taxonomy"
                        :collection-id="collectionId"
                        :is-repository-level="isRepositoryLevel"/>

            </div>
        </b-modal>
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
            collectionId: Number,
            termId: Number,
            taxonomy: String,
            defaultViewMode: String,
            enabledViewModes: Object
        },
        data() {
            return {
                isRepositoryLevel: false,
                displayedMetadata: [],
                prefDisplayedMetadata: [],
                isLoadingItems: false,
                isLoadingMetadata: false,
                hasFiltered: false,
                isFiltersMenuCompressed: false,
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
                customFilters: [],
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
            },
            orderByName() {
                if (this.isSortingByCustomMetadata)
                    this.hasAnOpenAlert = true;
            },
            isFilterModalActive() {
                if (this.isFilterModalActive) {
                    setTimeout(() => {
                        if (this.$refs['filters-mobile-modal'])
                            this.$refs['filters-mobile-modal'].focus();
                    }, 800);
                }
            }
        },
        created() {

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

            this.$eventBusSearch.$on('hasToPrepareMetadataAndFilters', () => {
                /* This condition is to prevent an incorrect fetch by filter or metadata when we come from items
                 * at collection level to items page at repository level
                 */
                this.prepareMetadata();
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

            // Setting initial view mode on Theme
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

            this.showItemsHiddingDueSortingDialog();

            // Watches window resize to adjust filter's top position and compression on mobile 
            this.adjustSearchControlHeight();
            window.addEventListener('resize', this.adjustSearchControlHeight);
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
            onChangeOrder() {
                this.order == 'DESC' ? this.$eventBusSearch.setOrder('ASC') : this.$eventBusSearch.setOrder('DESC');
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
                                                metadatum.metadata_type != 'Tainacan\\Metadata_Types\\Relationship'
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
            adjustSearchControlHeight: _.debounce( function() {
                this.$nextTick(() => {
                    if (this.$refs['search-control'] != undefined)
                        this.searchControlHeight = this.$refs['search-control'] ? this.$refs['search-control'].clientHeight + this.$refs['search-control'].offsetTop : 0;
                    
                    if (jQuery && jQuery(window))
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
                    height: auto !important;
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
            margin-top: -4px;
            margin-left: 6px !important;
            width: 1.25rem;
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
                    height: auto !important;
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
            min-height: 50vh;
            height: auto;
        }
    }

    .items-list-area {
        margin-left: 0;
        height: 100%;
        overflow: auto;
        position: relative;
    }

    .table-container {
        padding-left: $page-side-padding;
        padding-right: $page-side-padding;
        min-height: 50vh;
        //height: calc(100% - 82px);
    }

    .pagination-area {
        margin-left: $page-side-padding;
        margin-right: $page-side-padding;
    }

</style>


