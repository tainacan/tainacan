// Main imports
import Vue from 'vue';
import {
    Field,
    Input,
    Collapse,
    Autocomplete,
    Taginput,
    Tabs,
    Select,
    Button,
    Datepicker,
    Checkbox,
    Radio,
    Tag,
    Loading,
    Dropdown,
    Modal,
    Dialog,
    Snackbar,
    Toast,
    Pagination,
    Numberinput
} from 'buefy';
import VTooltip from 'floating-vue';
import cssVars from 'css-vars-ponyfill';
import qs from 'qs';
import VueBlurHash from 'vue-blurhash';

import ThemeItemsPage from './theme-search/theme-items-page.vue';
import ThemeSearch from './theme.vue';

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
    Vue.config.devtools = TAINACAN_ENV === 'development';

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
            Vue.use(Field);
            Vue.use(Input);
            Vue.use(Autocomplete);
            Vue.use(Taginput);
            Vue.use(Collapse);
            Vue.use(Button); 
            Vue.use(Datepicker);
            Vue.use(Select);
            Vue.use(Checkbox);
            Vue.use(Radio);
            Vue.use(Tag);
            Vue.use(Tabs);
            Vue.use(Loading);
            Vue.use(Dropdown);
            Vue.use(Modal);
            Vue.use(Dialog);
            Vue.use(Snackbar);
            Vue.use(Toast);
            Vue.use(Pagination);
            Vue.use(Numberinput);
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

            // Filters logic
            let possibleHideFilters = false;
            if ( blockElement.attributes['hide-filters'] != undefined ) {
                const hideFiltersValue = blockElement.attributes['hide-filters'].value;
                possibleHideFilters = ( hideFiltersValue == true || hideFiltersValue == 'true' || hideFiltersValue == '1' || hideFiltersValue == 1 ) ? true : false;
            }

            if ( !possibleHideFilters ) {
                import('../../../admin/components/search/filters-items-list.vue')
                    .then(importedModule => Vue.component('filters-items-list', importedModule.default))
                    .catch(error => console.log(error));
            }

            /* Main page component */
            Vue.component('theme-items-page', ThemeItemsPage);
            Vue.component('theme-search', ThemeSearch);

            // View Modes Logic
            const registeredViewModes =
                ( tainacan_plugin && tainacan_plugin.registered_view_modes && tainacan_plugin.registered_view_modes.length ) ?
                tainacan_plugin.registered_view_modes :
                [ 'table', 'cards', 'records', 'masonry', 'slideshow', 'list', 'map' ];

            // At first, we consider that all registered view modes are included.
            let possibleViewModes = registeredViewModes;
            if ( blockElement.attributes['enabled-view-modes'] != undefined )
                possibleViewModes = blockElement.attributes['enabled-view-modes'].value.split(',');

            // View Mode settings
            let possibleDefaultViewMode = 'masonry';
            if ( blockElement.attributes['default-view-mode'] != undefined)
                possibleDefaultViewMode = blockElement.attributes['default-view-mode'].value;
        
            if ( possibleViewModes.indexOf(possibleDefaultViewMode) < 0 )
                possibleViewModes.push(possibleDefaultViewMode);

            // Logic for dynamic importing Tainacan oficial view modes only if they are necessary
            possibleViewModes.forEach(viewModeSlug => {
                if ( registeredViewModes.indexOf(viewModeSlug) >= 0 )
                    import('./theme-search/components/view-mode-' + viewModeSlug + '.vue')
                        .then(importedModule => Vue.component('view-mode-' + viewModeSlug, importedModule.default) )
                        .catch(error => console.log(error)); 
            });

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
                    defaultOrderByMeta: '',
                    defaultOrderByType: '',
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

                    // Sorting options
                    if (this.$el.attributes['default-order'] != undefined)
                        this.defaultOrder = this.$el.attributes['default-order'].value;
                    if (this.$el.attributes['default-orderby'] != undefined) {
                        this.defaultOrderBy = this.maybeConvertFromJSON(this.$el.attributes['default-orderby'].value);
                        this.defaultOrderBy === 'creation_date' ? 'date' : this.defaultOrderBy;
                    }
                    if (this.$el.attributes['default-orderby-meta'] != undefined)
                        this.defaultOrderByMeta = this.$el.attributes['default-orderby-meta'].value;
                    if (this.$el.attributes['default-orderby-type'] != undefined)
                        this.defaultOrderByType = this.maybeConvertFromJSON(this.$el.attributes['default-orderby-type'].value);

                    // View modes settings
                    if (this.$el.attributes['is-forced-view-mode'] != undefined)
                        this.isForcedViewMode = new Boolean(this.$el.attributes['is-forced-view-mode'].value);
                    
                    this.defaultViewMode = possibleDefaultViewMode;
                    this.enabledViewModes = possibleViewModes;

                    // Options related to hidding elements
                    this.hideFilters = possibleHideFilters;
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
