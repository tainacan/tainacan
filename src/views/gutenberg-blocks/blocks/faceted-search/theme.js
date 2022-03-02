// Main imports
import Vue from 'vue';
import Buefy from 'buefy';
import VTooltip from 'floating-vue';
import VueMasonry from 'vue-masonry-css';
import cssVars from 'css-vars-ponyfill';
import qs from 'qs';
import VueBlurHash from 'vue-blurhash';

// Filters
import FilterNumeric from '../../../admin/components/filter-types/numeric/Numeric.vue';
import FilterDate from '../../../admin/components/filter-types/date/Date.vue';
import FilterSelectbox from '../../../admin/components/filter-types/selectbox/Selectbox.vue';
import FilterAutocomplete from '../../../admin/components/filter-types/autocomplete/Autocomplete.vue';
import FilterCheckbox from '../../../admin/components/filter-types/checkbox/Checkbox.vue';
import FilterTaginput from '../../../admin/components/filter-types/taginput/Taginput.vue';
import FilterTaxonomyCheckbox from '../../../admin/components/filter-types/taxonomy/Checkbox.vue';
import FilterTaxonomyTaginput from '../../../admin/components/filter-types/taxonomy/Taginput.vue';
import FilterDateInterval from '../../../admin/components/filter-types/date-interval/DateInterval.vue';
import FilterNumericInterval from '../../../admin/components/filter-types/numeric-interval/NumericInterval.vue';
import FilterNumericListInterval from '../../../admin/components/filter-types/numeric-list-interval/NumericListInterval.vue';

import TaincanFiltersList from '../../../admin/components/filter-types/tainacan-filter-item.vue';
import ThemeItemsPage from './theme-search/theme-items-page.vue';
import ThemeSearch from './theme.vue';

// View Modes
import ViewModeTable from './theme-search/components/view-mode-table.vue';
import ViewModeCards from './theme-search/components/view-mode-cards.vue';
import ViewModeRecords from './theme-search/components/view-mode-records.vue';
import ViewModeMasonry from './theme-search/components/view-mode-masonry.vue';
import ViewModeSlideshow from './theme-search/components/view-mode-slideshow.vue';
import ViewModeList from './theme-search/components/view-mode-list.vue';

// Remaining imports
import store from '../../../admin/js/store/store';
import routerTheme from './theme-search/js/theme-router.js';
import eventBusSearch from '../../../admin/js/event-bus-search';
import { 
    I18NPlugin,
    UserPrefsPlugin,
    ConsolePlugin,
    AdminOptionsHelperPlugin
} from '../../../admin/js/admin-utilities';
import { 
    ThumbnailHelperPlugin,
    OrderByHelperPlugin
} from '../../../admin/js/utilities';

export default (element) => {

    // Vue Dev Tools!
    Vue.config.devtools = process && process.env && process.env.NODE_ENV === 'development';

    function renderTainacanItemsListComponent() {

        // Gets the div with the content of the block
        let blockElement = element ? element : document.getElementById('tainacan-items-page');

        // Mount only if the div exists and it is not already mounted
        if ( blockElement && blockElement.classList && !blockElement.classList.contains('has-mounted') ) {

            /* Registers Extra Vue Plugins passed to the window.tainacan_extra_plugins  */
            if (typeof window.tainacan_extra_plugins != "undefined") {
                for (let [extraVuePluginName, extraVuePluginObject] of Object.entries(window.tainacan_extra_plugins)) {
                    Vue.component(extraVuePluginName, extraVuePluginObject);
                }
            }

            // Configure and Register Plugins
            Vue.use(Buefy, {
                defaultTooltipAnimated: true
            });
            Vue.use(VTooltip, {
                popperTriggers: ['hover'],
                themes: {
                    'taianacan-tooltip': {
                        '$extend': 'tooltip',
                        triggers: ['hover', 'focus', 'touch'],
                        autoHide: true,
                        html: true,
                    }
                }
            });
            Vue.use(VueMasonry);
            Vue.use(VueBlurHash);
            Vue.use(I18NPlugin);
            Vue.use(UserPrefsPlugin);
            Vue.use(ThumbnailHelperPlugin);
            Vue.use(OrderByHelperPlugin);
            Vue.use(ConsolePlugin, {visual: false});
            Vue.use(AdminOptionsHelperPlugin, blockElement.dataset['options']);

            /* Registers Extra Vue Components passed to the window.tainacan_extra_components  */
            if (typeof window.tainacan_extra_components != "undefined") {
                for (let [extraVueComponentName, extraVueComponentObject] of Object.entries(window.tainacan_extra_components)) {
                    Vue.component(extraVueComponentName, extraVueComponentObject);
                }
            }

            Vue.component('tainacan-filter-item', TaincanFiltersList);

            /* Filters */
            Vue.component('tainacan-filter-numeric', FilterNumeric);
            Vue.component('tainacan-filter-date', FilterDate);
            Vue.component('tainacan-filter-selectbox', FilterSelectbox);
            Vue.component('tainacan-filter-autocomplete', FilterAutocomplete);
            Vue.component('tainacan-filter-checkbox', FilterCheckbox);
            Vue.component('tainacan-filter-taginput', FilterTaginput);
            Vue.component('tainacan-filter-taxonomy-checkbox', FilterTaxonomyCheckbox);
            Vue.component('tainacan-filter-taxonomy-taginput', FilterTaxonomyTaginput);
            Vue.component('tainacan-filter-date-interval', FilterDateInterval);
            Vue.component('tainacan-filter-numeric-interval', FilterNumericInterval);
            Vue.component('tainacan-filter-numeric-list-interval', FilterNumericListInterval);

            /* Main page component */
            Vue.component('theme-items-page', ThemeItemsPage);
            Vue.component('theme-search', ThemeSearch);

            // Oficial view modes
            Vue.component('view-mode-table', ViewModeTable);
            Vue.component('view-mode-cards', ViewModeCards);
            Vue.component('view-mode-records', ViewModeRecords);
            Vue.component('view-mode-masonry', ViewModeMasonry);
            Vue.component('view-mode-slideshow', ViewModeSlideshow);
            Vue.component('view-mode-list', ViewModeList);

            Vue.use(eventBusSearch, { store: store, router: routerTheme});
                
            const VueItemsList = new Vue({
                store,
                router: routerTheme,
                data: {
                    termId: '',
                    taxonomy: '',
                    collectionId: '',
                    defaultViewMode: '',
                    defaultOrder: 'ASC',
                    defaultOrderBy: 'date',
                    isForcedViewMode: false,
                    enabledViewModes: {},
                    defaultItemsPerPage: '',
                    hideFilters: false,
                    hideHideFiltersButton: false,
                    hideSearch: false,
                    hideAdvancedSearch: false,
                    hideDisplayedMetadataButton: false,
                    hideSortByButton: false,
                    hideSortingArea: false,
                    hideItemsThumbnail: false,
                    hideItemsPerPageButton: false,
                    hideGoToPageButton: false,
                    hidePaginationArea: false,
                    showFiltersButtonInsideSearchControl: false,
                    startWithFiltersHidden: false,
                    filtersAsModal: false,
                    showInlineViewModeOptions: false,
                    showFullscreenWithViewModes: false
                },
                beforeMount() {
                    
                    // Loads params if passed previously 
                    if (this.$route.hash && this.$route.hash.split('#/?') && this.$route.hash.split('#/?')[1]) {
                        const existingQueries = qs.parse(this.$route.hash.split('#/?')[1]); 

                        for (let key of Object.keys(existingQueries))
                            this.$route.query[key] = existingQueries[key];
                    }

                    // Collection or Term source settings
                    if (this.$el.attributes['collection-id'] != undefined)
                        this.collectionId = this.$el.attributes['collection-id'].value;
                    if (this.$el.attributes['term-id'] != undefined)
                        this.termId = this.$el.attributes['term-id'].value;
                    if (this.$el.attributes['taxonomy'] != undefined)
                        this.taxonomy = this.$el.attributes['taxonomy'].value;

                    // View Mode settings
                    if (this.$el.attributes['default-view-mode'] != undefined)
                        this.defaultViewMode = this.$el.attributes['default-view-mode'].value;
                    else
                        this.defaultViewMode = 'cards';
                        
                    if (this.$el.attributes['is-forced-view-mode'] != undefined)
                        this.isForcedViewMode = new Boolean(this.$el.attributes['is-forced-view-mode'].value);

                    if (this.$el.attributes['enabled-view-modes'] != undefined)
                        this.enabledViewModes = this.$el.attributes['enabled-view-modes'].value.split(',');
                
                    // Sorting options
                    if (this.$el.attributes['default-order'] != undefined)
                        this.defaultOrder = this.$el.attributes['default-order'].value;
                    if (this.$el.attributes['default-orderby'] != undefined)
                        this.defaultOrderBy = this.maybeConvertFromJSON(this.$el.attributes['default-orderby'].value);
                    
                    // Options related to hidding elements
                    if (this.$el.attributes['hide-filters'] != undefined)
                        this.hideFilters = this.isParameterTrue('hide-filters');
                    if (this.$el.attributes['hide-hide-filters-button'] != undefined)
                        this.hideHideFiltersButton = this.isParameterTrue('hide-hide-filters-button');
                    if (this.$el.attributes['hide-search'] != undefined)
                        this.hideSearch = this.isParameterTrue('hide-search');
                    if (this.$el.attributes['hide-advanced-search'] != undefined)
                        this.hideAdvancedSearch = this.isParameterTrue('hide-advanced-search');
                    if (this.$el.attributes['hide-displayed-metadata-button'] != undefined)
                        this.hideDisplayedMetadataButton = this.isParameterTrue('hide-displayed-metadata-button');
                    if (this.$el.attributes['hide-sorting-area'] != undefined)
                        this.hideSortingArea = this.isParameterTrue('hide-sorting-area');
                    if (this.$el.attributes['hide-items-thumbnail'] != undefined)
                        this.hideItemsThumbnail = this.isParameterTrue('hide-items-thumbnail');
                    if (this.$el.attributes['hide-sort-by-button'] != undefined)
                        this.hideSortByButton = this.isParameterTrue('hide-sort-by-button');
                    if (this.$el.attributes['hide-exposers-button'] != undefined)
                        this.hideExposersButton = this.isParameterTrue('hide-exposers-button');
                    if (this.$el.attributes['hide-items-per-page-button'] != undefined)
                        this.hideItemsPerPageButton = this.isParameterTrue('hide-items-per-page-button');
                    if (this.$el.attributes['hide-go-to-page-button'] != undefined)
                        this.hideGoToPageButton = this.isParameterTrue('hide-go-to-page-button');
                    if (this.$el.attributes['hide-pagination-area'] != undefined)
                        this.hidePaginationArea = this.isParameterTrue('hide-pagination-area');

                    // Other Tweaks
                    if (this.$el.attributes['default-items-per-page'] != undefined)
                        this.defaultItemsPerPage = this.$el.attributes['default-items-per-page'].value;
                    if (this.$el.attributes['show-filters-button-inside-search-control'] != undefined)
                        this.showFiltersButtonInsideSearchControl = this.isParameterTrue('show-filters-button-inside-search-control');
                    if (this.$el.attributes['start-with-filters-hidden'] != undefined)
                        this.startWithFiltersHidden = this.isParameterTrue('start-with-filters-hidden');
                    if (this.$el.attributes['filters-as-modal'] != undefined)
                        this.filtersAsModal = this.isParameterTrue('filters-as-modal');
                    if (this.$el.attributes['show-inline-view-mode-options'] != undefined)
                        this.showInlineViewModeOptions = this.isParameterTrue('show-inline-view-mode-options');
                    if (this.$el.attributes['show-fullscreen-with-view-modes'] != undefined)
                        this.showFullscreenWithViewModes = this.isParameterTrue('show-fullscreen-with-view-modes');
                },
                methods: {
                    isParameterTrue(parameter) {
                        const value = this.$el.attributes[parameter].value;
                        return (value == true || value == 'true' || value == '1' || value == 1) ? true : false;
                    },
                    maybeConvertFromJSON(someString) {
                        try {
                            return JSON.parse(someString);
                        } catch(error) {
                            return someString;
                        }
                    }
                },
                render: h => h(ThemeSearch)
            });

            VueItemsList.$mount('#tainacan-items-page');

            // Initialize Ponyfill for Custom CSS properties
            cssVars({
                // Options...
            });
        }
    }

    // This is rendered on the theme side.
    renderTainacanItemsListComponent();

    // Also if a theme or plugin requested a reset...
    document.addEventListener("TainacanReloadItemsListComponent", () => {
        renderTainacanItemsListComponent();
    });
}
