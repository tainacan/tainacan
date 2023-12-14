import { createApp, h } from 'vue';

import DynamicItemsListTheme from './theme.vue';
import { ThumbnailHelperPlugin } from '../../../admin/js/utilities.js';
import VueBlurHash from 'another-vue3-blurhash';
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
    
                // Configure Vue logic before passing it to constructor:
                const VueDynamicItemsList = createApp( {
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
                            tainacanApiRoot: getDataAttribute(block, 'tainacan-api-root')
                        });
                    },
                    mounted() {
                        block.classList.add('has-mounted');
                    }
                });

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