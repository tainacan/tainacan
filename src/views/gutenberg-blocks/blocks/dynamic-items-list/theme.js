import { createApp, h } from 'vue';

import DynamicItemsListTheme from './theme.vue';
import { ThumbnailHelperPlugin } from '../../../admin/js/utilities.js';
import VueBlurHash from 'another-vue3-blurhash';

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
                            blockId: block.id,
                            searchURL: block.attributes['search-url'] != undefined ? block.attributes['search-url'].value : undefined,
                            selectedItems: block.attributes['selected-items'] != undefined ? JSON.parse(block.attributes['selected-items'].value) : undefined,
                            loadStrategy: block.attributes['load-strategy'] != undefined ? block.attributes['load-strategy'].value : undefined,
                            collectionId: block.attributes['collection-id'] != undefined ? block.attributes['collection-id'].value : undefined,
                            showImage: block.attributes['show-image'] != undefined ? block.attributes['show-image'].value == 'true' : true,
                            showName: block.attributes['show-name'] != undefined ? block.attributes['show-name'].value == 'true' : true,
                            layout: block.attributes['layout'] != undefined ? block.attributes['layout'].value : undefined,
                            gridMargin: block.attributes['grid-margin'] != undefined ? Number(block.attributes['grid-margin'].value) : undefined,
                            mosaicDensity: block.attributes['mosaic-density'] != undefined ? Number(block.attributes['mosaic-density'].value) : undefined,
                            mosaicHeight: block.attributes['mosaic-height'] != undefined ? Number(block.attributes['mosaic-height'].value) : undefined,
                            mosaicGridRows: block.attributes['mosaic-grid-rows'] != undefined ? Number(block.attributes['mosaic-grid-rows'].value) : undefined,
                            mosaicGridColumns: block.attributes['mosaic-grid-columns'] != undefined ? Number(block.attributes['mosaic-grid-columns'].value) : undefined,
                            mosaicItemFocalPointX: block.attributes['mosaic-item-focal-point-x'] != undefined ? Number(block.attributes['mosaic-item-focal-point-x'].value) : undefined,
                            mosaicItemFocalPointY: block.attributes['mosaic-item-focal-point-y'] != undefined ? Number(block.attributes['mosaic-item-focal-point-y'].value) : undefined,
                            maxColumnsCount: block.attributes['max-columns-count'] != undefined ? block.attributes['max-columns-count'].value : 4,
                            imageSize: block.attributes['image-size'] != undefined ? block.attributes['image-size'].value : 'tainacan-medium',
                            maxItemsNumber: block.attributes['max-items-number'] != undefined ? block.attributes['max-items-number'].value : undefined,
                            order: block.attributes['order'] != undefined ? block.attributes['order'].value : undefined,
                            orderBy: block.attributes['order-by'] != undefined ? block.attributes['order-by'].value : undefined,
                            orderByMetaKey: block.attributes['order-by-meta-key'] != undefined ? block.attributes['order-by-meta-key'].value : undefined,
                            showSearchBar: block.attributes['show-search-bar'] != undefined ? block.attributes['show-search-bar'].value == 'true' : false,
                            showCollectionHeader: block.attributes['show-collection-header'] != undefined ? block.attributes['show-collection-header'].value == 'true' : false,
                            showCollectionLabel: block.attributes['show-collection-label'] != undefined ? block.attributes['show-collection-label'].value == 'true' : false,
                            collectionBackgroundColor: block.attributes['collection-background-color'] != undefined ? block.attributes['collection-background-color'].value : undefined,
                            collectionTextColor: block.attributes['collection-text-color'] != undefined ? block.attributes['collection-text-color'].value : undefined,
                            tainacanApiRoot: block.attributes['tainacan-api-root'] != undefined ? block.attributes['tainacan-api-root'].value : undefined,
                            tainacanBaseUrl: block.attributes['tainacan-base-url'] != undefined ? block.attributes['tainacan-base-url'].value : undefined
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