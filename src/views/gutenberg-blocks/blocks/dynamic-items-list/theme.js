import { createApp, h, defineAsyncComponent } from 'vue';

import DynamicItemsListTheme from './theme.vue';
import { ThumbnailHelperPlugin } from '../../../admin/js/utilities.js';
import { I18NPlugin } from '../../../admin/js/admin-utilities';
import VueBlurHash from 'another-vue3-blurhash';
import VTooltip from 'floating-vue';

import getDataAttribute from '../../js/compatibility/tainacan-blocks-compat-data-attributes.js';

export default (element) => {

    function renderTainacanDynamicItemsBlocks() {
    
        // Gets all divs with content created by our block;
        let blocksElements = element ? [ element ] : document.getElementsByClassName('wp-block-tainacan-dynamic-items-list');
        
        if (blocksElements) {
            let blocks = Object.values(blocksElements);

            // Checks if this carousel isn't already mounted
            blocks = blocks.filter((block) => block.classList && !block.classList.contains('has-mounted'));
    
            // Creates a new Vue Instance to manage each block isolatelly
            blocks.forEach((block) => {

                // View Modes Logic
                let registeredViewModes =
                    ( tainacan_blocks && tainacan_blocks.registered_view_modes && tainacan_blocks.registered_view_modes.length ) ?
                    tainacan_blocks.registered_view_modes :
                    [ 'table', 'cards', 'records', 'masonry', 'mosaic', 'list', 'map'];

                // At first, we consider that all registered view modes are included.
                let possibleViewModes = registeredViewModes.filter((aViewMode) => aViewMode === 'slideshow');
                if ( getDataAttribute(block, 'enabled-view-modes') != undefined )
                    possibleViewModes = getDataAttribute(block, 'enabled-view-modes').split(',');

                // View Mode settings
                let possibleDefaultViewMode = tainacan_blocks.default_view_mode ? tainacan_blocks.default_view_mode : 'masonry';
                if ( getDataAttribute(block, 'tainacan-view-mode') != undefined )
                    possibleDefaultViewMode = getDataAttribute(block, 'tainacan-view-mode');
            
                if ( possibleViewModes.indexOf(possibleDefaultViewMode) < 0 )
                    possibleViewModes.push(possibleDefaultViewMode);
    
                // Configure Vue logic before passing it to constructor:
                const VueDynamicItemsList = createApp( {
                    mounted() {
                        block.classList.add('has-mounted');
                    },
                    render() { 
                        return h(DynamicItemsListTheme, {
                            searchURL: getDataAttribute(block, 'search-url'),
                            selectedItems: JSON.parse(getDataAttribute(block, 'selected-items', '[]')),
                            loadStrategy: getDataAttribute(block, 'load-strategy'),
                            collectionId: getDataAttribute(block, 'collection-id'),
                            showImage: getDataAttribute(block, 'show-image', 'true') == 'true',
                            showName: getDataAttribute(block, 'show-name', 'true') == 'true',
                            layout: getDataAttribute(block, 'layout'),
                            gridMargin: Number(getDataAttribute(block, 'grid-margin', 0)),
                            mosaicDensity: Number(getDataAttribute(block, 'mosaic-density', 5)),
                            mosaicHeight: Number(getDataAttribute(block, 'mosaic-height', 40)),
                            mosaicGridRows: Number(getDataAttribute(block, 'mosaic-grid-rows', 3)),
                            mosaicGridColumns: Number(getDataAttribute(block, 'mosaic-grid-columns', 3)),
                            mosaicItemFocalPointX: Number(getDataAttribute(block, 'mosaic-item-focal-point-x', 0.5)),
                            mosaicItemFocalPointY: Number(getDataAttribute(block, 'mosaic-item-focal-point-y', 0.5)),
                            maxColumnsCount: Number(getDataAttribute(block, 'max-columns-count', 4)),
                            imageSize: getDataAttribute(block, 'image-size', 'tainacan-medium'),
                            maxItemsNumber: Number(getDataAttribute(block, 'max-items-number', 12)),
                            order: getDataAttribute(block, 'order'),
                            orderBy: getDataAttribute(block, 'order-by'),
                            orderByMetaKey: getDataAttribute(block, 'order-by-meta-key'),
                            showSearchBar: getDataAttribute(block, 'show-search-bar', 'false') == 'true',
                            showCollectionHeader: getDataAttribute(block, 'show-collection-header', 'false') == 'true',
                            showCollectionLabel: getDataAttribute(block, 'show-collection-label', 'false') == 'true',
                            collectionBackgroundColor: getDataAttribute(block, 'collection-background-color'),
                            collectionTextColor: getDataAttribute(block, 'collection-text-color'),
                            tainacanApiRoot: getDataAttribute(block, 'tainacan-api-root'),
                            tainacanViewMode: possibleDefaultViewMode,
                            enabledViewModes: possibleViewModes,
                            displayedMetadata: JSON.parse(getDataAttribute(block, 'displayed-metadata', '[]')),
                            blockId: block.id
                        });
                    }
                });

                // Logic for dynamic importing Tainacan oficial view modes only if they are necessary
                possibleViewModes.forEach(viewModeSlug => {
                    if ( registeredViewModes.indexOf(viewModeSlug) >= 0 )
                        VueDynamicItemsList.component('view-mode-' + viewModeSlug, defineAsyncComponent(() => import('../faceted-search/theme-search/components/view-mode-' + viewModeSlug + '.vue')));
                });
                
               /* Registers Extra Vue Components passed to the window.tainacan_extra_components  */
                if (typeof window.tainacan_extra_components != "undefined") {
                    for (let [extraVueComponentName, extraVueComponentObject] of Object.entries(window.tainacan_extra_components)) {
                        VueDynamicItemsList.component(extraVueComponentName, extraVueComponentObject);
                    }
                }

                VueDynamicItemsList.use(VTooltip, {
                    popperTriggers: ['hover'],
                    themes: {
                        'tainacan-tooltip': {
                            '$extend': 'tooltip',
                            triggers: ['hover', 'focus', 'touch'],
                            autoHide: true,
                            html: true,
                        }
                    }
                });
                VueDynamicItemsList.use(I18NPlugin);
                VueDynamicItemsList.use(ThumbnailHelperPlugin);
                VueDynamicItemsList.use(VueBlurHash);

                VueDynamicItemsList.mount('#' + block.id);
            });
        }
    }

    // This is rendered on the theme side.
    renderTainacanDynamicItemsBlocks();

    // Also if a theme or plugin requested a reset...
    document.addEventListener("TainacanReloadDynamicItemsBlock", () => {
        renderTainacanDynamicItemsBlocks();
    });

}