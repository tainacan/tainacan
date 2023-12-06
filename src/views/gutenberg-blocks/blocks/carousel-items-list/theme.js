import { createApp, h } from 'vue';

import CarouselItemsListTheme from './theme.vue';
import { ThumbnailHelperPlugin } from '../../../admin/js/utilities.js';
import VueBlurHash from 'another-vue3-blurhash';

export default (element) => {

    function renderTainacanItemCarouselBlocks() {

        // Gets all divs with content created by our block;
        let blocksElements = element ? [ element ] : document.getElementsByClassName('wp-block-tainacan-carousel-items-list');
        
        if (blocksElements) {
            let blocks = Object.values(blocksElements);

            // Checks if this carousel isn't already mounted
            blocks = blocks.filter((block) => block.classList && !block.classList.contains('has-mounted'));
            
            // Creates a new Vue Instance to manage each block isolatelly
            blocks.forEach((block) => {
                
                const VueCarouselItemsList = createApp( {
                    render() { 
                        return h(CarouselItemsListTheme, {   
                            blockId: block.id,
                            searchURL: block.attributes['search-url'] != undefined ? block.attributes['search-url'].value : undefined,
                            selectedItems: block.attributes['selected-items'] != undefined ? JSON.parse(block.attributes['selected-items'].value) : [],
                            loadStrategy: block.attributes['load-strategy'] != undefined ? block.attributes['load-strategy'].value : 'search',
                            collectionId: block.attributes['collection-id'] != undefined ? block.attributes['collection-id'].value : undefined,
                            maxItemsNumber: block.attributes['max-items-number'] != undefined ? Number(block.attributes['max-items-number'].value) : 12,
                            maxItemsPerScreen: block.attributes['max-items-per-screen'] != undefined ? Number(block.attributes['max-items-per-screen'].value) : 7,
                            spaceBetweenItems: block.attributes['space-between-items'] != undefined ? Number(block.attributes['space-between-items'].value) : 32,
                            spaceAroundCarousel: block.attributes['space-around-carousel'] != undefined ? Number(block.attributes['space-around-carousel'].value) : 50,
                            arrowsPosition: block.attributes['arrows-position'] != undefined ? block.attributes['arrows-position'].value : 'around',
                            largeArrows: block.attributes['large-arrows'] != undefined ? block.attributes['large-arrows'].value == 'true' : false,
                            arrowsStyle: block.attributes['arrows-style'] != undefined ? block.attributes['arrows-style'].value : 'type-1',
                            autoPlay: block.attributes['auto-play'] != undefined ? block.attributes['auto-play'].value == 'true' : false,
                            autoPlaySpeed: block.attributes['auto-play-speed'] != undefined ? Number(block.attributes['auto-play-speed'].value) : 3,
                            loopSlides: block.attributes['loop-slides'] != undefined ? block.attributes['loop-slides'].value == 'true' : false,
                            hideTitle: block.attributes['hide-title'] != undefined ? block.attributes['hide-title'].value == 'true' : false,
                            imageSize: block.attributes['image-size'] != undefined ? block.attributes['image-size'].value : 'tainacan-medium',
                            showCollectionHeader: block.attributes['show-collection-header'] != undefined ? block.attributes['show-collection-header'].value == 'true' : false,
                            showCollectionLabel: block.attributes['show-collection-label'] != undefined ? block.attributes['show-collection-label'].value == 'true' : false,
                            collectionBackgroundColor: block.attributes['collection-background-color'] != undefined ? block.attributes['collection-background-color'].value : '#373839',
                            collectionTextColor: block.attributes['collection-text-color'] != undefined ? block.attributes['collection-text-color'].value : '#ffffff',
                            tainacanApiRoot: block.attributes['tainacan-api-root'] != undefined ? block.attributes['tainacan-api-root'].value : undefined,
                            tainacanBaseUrl: block.attributes['tainacan-base-url'] != undefined ? block.attributes['tainacan-base-url'].value : undefined
                        });
                    },
                    mounted() {
                        block.classList.add('has-mounted');
                    }
                });

                VueCarouselItemsList.use(ThumbnailHelperPlugin);
                VueCarouselItemsList.use(VueBlurHash);

                VueCarouselItemsList.mount('#' + block.id);
            });
        }
    }

    // This is rendered on the theme side.
    renderTainacanItemCarouselBlocks();

    // Also if a theme or plugin requested a reset...
    document.addEventListener("TainacanReloadCarouselItemsListBlock", () => {
        renderTainacanItemCarouselBlocks();
    });
};