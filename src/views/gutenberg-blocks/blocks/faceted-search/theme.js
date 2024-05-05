// Main imports
import { createApp, h, defineAsyncComponent } from 'vue';
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
    Icon,
    Loading,
    Dropdown,
    Modal,
    Dialog,
    Snackbar,
    Toast,
    Pagination,
    Numberinput
} from '@ntohq/buefy-next';
import VTooltip from 'floating-vue';
import cssVars from 'css-vars-ponyfill';
import VueBlurHash from 'another-vue3-blurhash';

import getDataAttribute from '../../js/compatibility/tainacan-blocks-compat-data-attributes.js';
import ThemeSearch from './theme.vue';

// Remaining imports
import store from '../../../admin/js/store/store';
import routerTheme from './theme-search/js/theme-router.js';
import eventBusSearch from '../../../admin/js/event-bus-search';
import { 
    I18NPlugin,
    UserPrefsPlugin,
    ConsolePlugin,
    AdminOptionsHelperPlugin,
    AxiosErrorHandlerPlugin
} from '../../../admin/js/admin-utilities';
import { 
    ThumbnailHelperPlugin,
    OrderByHelperPlugin
} from '../../../admin/js/utilities';
import mitt from 'mitt';

const isParameterTrue = function(value) {
    return (value == true || value == 'true' || value == '1' || value == 1) ? true : false;
}

const maybeConvertFromJSON = function(someString) {
    try {
        return JSON.parse(someString);
    } catch(error) {
        return someString;
    }
}

export default (element) => {

    function renderTainacanItemsListComponent() {

        // Gets the div with the content of the block
        let blockElement = element ? element : document.getElementById('tainacan-items-page');

        // Mount only if the div exists and it is not already mounted
        if ( blockElement && blockElement.classList && !blockElement.classList.contains('has-mounted') ) {

            // Filters logic
            const possibleHideFilters = isParameterTrue(getDataAttribute(blockElement,'hide-filters'));

            // View Modes Logic
            const registeredViewModes =
                ( tainacan_plugin && tainacan_plugin.registered_view_modes && tainacan_plugin.registered_view_modes.length ) ?
                tainacan_plugin.registered_view_modes :
                [ 'table', 'cards', 'records', 'masonry', 'slideshow', 'list', 'map' ];

            // At first, we consider that all registered view modes are included.
            let possibleViewModes = registeredViewModes;
            if ( getDataAttribute(blockElement, 'enabled-view-modes') != undefined )
                possibleViewModes = getDataAttribute(blockElement, 'enabled-view-modes').split(',');

            // View Mode settings
            let possibleDefaultViewMode = 'masonry';
            if ( getDataAttribute(blockElement, 'default-view-mode') != undefined )
                possibleDefaultViewMode = getDataAttribute(blockElement, 'default-view-mode');
        
            if ( possibleViewModes.indexOf(possibleDefaultViewMode) < 0 )
                possibleViewModes.push(possibleDefaultViewMode);

            const VueItemsList = createApp({
                created() {
                    blockElement.setAttribute('aria-live', 'polite');
                    blockElement.classList.add('theme-items-list'); // This used to be on the component, but as Vue now do not renders the component inside a div...
                },
                mounted() {
                    blockElement.classList.add('has-mounted');
                },
                render: () => h(ThemeSearch, {
                    collectionId: getDataAttribute(blockElement, 'collection-id'),
                    termId: getDataAttribute(blockElement, 'term-id'),
                    taxonomy: getDataAttribute(blockElement, 'taxonomy'),
                    defaultOrder: getDataAttribute(blockElement, 'default-order', 'ASC'),
                    defaultOrderBy: (() => {
                        const defaultOrderByValue = maybeConvertFromJSON(getDataAttribute(blockElement, 'default-orderby', 'date'));
                        return defaultOrderByValue === 'creation_date' ? 'date' : defaultOrderByValue;
                    })(),
                    defaultOrderByMeta: getDataAttribute(blockElement, 'default-orderby-meta'),
                    defaultOrderByType: maybeConvertFromJSON(getDataAttribute(blockElement, 'default-orderby-type')),
                    defaultViewMode: possibleDefaultViewMode,
                    enabledViewModes: possibleViewModes,
                    isForcedViewMode: new Boolean(getDataAttribute(blockElement, 'is-forced-view-mode')),
                    hideFilters: possibleHideFilters,
                    hideHideFiltersButton: isParameterTrue(getDataAttribute(blockElement, 'hide-hide-filters-button')),
                    hideSearch: isParameterTrue(getDataAttribute(blockElement, 'hide-search')),
                    hideAdvancedSearch: isParameterTrue(getDataAttribute(blockElement, 'hide-advanced-search')),
                    hideDisplayedMetadataButton: isParameterTrue(getDataAttribute(blockElement, 'hide-displayed-metadata-button')),
                    hideSortingArea: isParameterTrue(getDataAttribute(blockElement, 'hide-sorting-area')),
                    hideItemsThumbnail: isParameterTrue(getDataAttribute(blockElement, 'hide-items-thumbnail')),
                    hideSortByButton: isParameterTrue(getDataAttribute(blockElement, 'hide-sort-by-button')),
                    hideExposersButton: isParameterTrue(getDataAttribute(blockElement, 'hide-exposers-button')),
                    hideItemsPerPageButton: isParameterTrue(getDataAttribute(blockElement, 'hide-items-per-page-button')),
                    hideGoToPageButton: isParameterTrue(getDataAttribute(blockElement, 'hide-go-to-page-button')),
                    hidePaginationArea: isParameterTrue(getDataAttribute(blockElement, 'hide-pagination-area')),
                    defaultItemsPerPage: new Number(getDataAttribute(blockElement, 'default-items-per-page', 12)),
                    showFiltersButtonInsideSearchControl: isParameterTrue(getDataAttribute(blockElement, 'show-filters-button-inside-search-control')),
                    startWithFiltersHidden: isParameterTrue(getDataAttribute(blockElement, 'start-with-filters-hidden')),
                    filtersAsModal: isParameterTrue(getDataAttribute(blockElement, 'filters-as-modal')),
                    showInlineViewModeOptions: isParameterTrue(getDataAttribute(blockElement, 'show-inline-view-mode-options')),
                    showFullscreenWithViewModes: isParameterTrue(getDataAttribute(blockElement, 'show-fullscreen-with-view-modes'))
                }),
            });

            VueItemsList.use(store);
            VueItemsList.use(routerTheme);

            if ( !possibleHideFilters )
                VueItemsList.component('filters-items-list', defineAsyncComponent(() => import('../../../admin/components/search/filters-items-list.vue')));

            /* Registers Extra Vue Plugins passed to the window.tainacan_extra_plugins  */
            if (typeof window.tainacan_extra_plugins != "undefined") {
                for (let [extraVuePluginName, extraVuePluginObject] of Object.entries(window.tainacan_extra_plugins)) {
                    VueItemsList.use(extraVuePluginName, extraVuePluginObject);
                }
            }

            // Configure and Register Plugins
            VueItemsList.use(Field);
            VueItemsList.use(Input);
            VueItemsList.use(Autocomplete);
            VueItemsList.use(Taginput);
            VueItemsList.use(Collapse);
            VueItemsList.use(Button); 
            VueItemsList.use(Datepicker);
            VueItemsList.use(Select);
            VueItemsList.use(Checkbox);
            VueItemsList.use(Radio);
            VueItemsList.use(Tag);
            VueItemsList.use(Icon);
            VueItemsList.use(Tabs);
            VueItemsList.use(Loading);
            VueItemsList.use(Dropdown);
            VueItemsList.use(Modal);
            VueItemsList.use(Dialog);
            VueItemsList.use(Snackbar);
            VueItemsList.use(Toast);
            VueItemsList.use(Pagination);
            VueItemsList.use(Numberinput);
            VueItemsList.use(VTooltip, {
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
            VueItemsList.use(VueBlurHash);
            VueItemsList.use(I18NPlugin);
            VueItemsList.use(UserPrefsPlugin);
            VueItemsList.use(ThumbnailHelperPlugin);
            VueItemsList.use(OrderByHelperPlugin);
            VueItemsList.use(AxiosErrorHandlerPlugin);
            VueItemsList.use(ConsolePlugin, {visual: false});
            VueItemsList.use(AdminOptionsHelperPlugin, blockElement.dataset['options']);

            /* Registers Extra Vue Components passed to the window.tainacan_extra_components  */
            if (typeof window.tainacan_extra_components != "undefined") {
                for (let [extraVueComponentName, extraVueComponentObject] of Object.entries(window.tainacan_extra_components)) {
                    VueItemsList.component(extraVueComponentName, extraVueComponentObject);
                }
            }

            // Logic for dynamic importing Tainacan oficial view modes only if they are necessary
            possibleViewModes.forEach(viewModeSlug => {
                if ( registeredViewModes.indexOf(viewModeSlug) >= 0 )
                    VueItemsList.component('view-mode-' + viewModeSlug, defineAsyncComponent(() => import('./theme-search/components/view-mode-' + viewModeSlug + '.vue')));
            });

            const emitter = mitt();
            VueItemsList.config.globalProperties.$emitter = emitter;
            
            VueItemsList.use(eventBusSearch);

            VueItemsList.mount('#' + blockElement.id);

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
