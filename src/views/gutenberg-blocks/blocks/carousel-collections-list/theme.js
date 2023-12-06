import { createApp, h } from 'vue';
import CarouselCollectionsListTheme from './theme.vue';
import { ThumbnailHelperPlugin } from '../../../admin/js/utilities.js';
import VueBlurHash from 'another-vue3-blurhash';

export default (element) => {

    function renderTainacanCollectionsCarouselBlocks() {

        // Gets all divs with content created by our block;
        let blocksElements = element ? [ element ] : document.getElementsByClassName('wp-block-tainacan-carousel-collections-list');
        
        if (blocksElements) {
            let blocks = Object.values(blocksElements);

            // Checks if this carousel isn't already mounted
            blocks = blocks.filter((block) => block.classList && !block.classList.contains('has-mounted'));
       
            // Creates a new Vue Instance to manage each block isolatelly
            blocks.forEach((block) => {

                const VueCollectionsList = createApp( {
                    render() { 
                        return h(CarouselCollectionsListTheme, {
                            blockId: block.id,
                            selectedCollections: block.attributes['selected-collections'] != undefined ? JSON.parse(block.attributes['selected-collections'].value) : undefined,
                            maxItemsNumber: block.attributes['max-collections-number'] != undefined ? Number(block.attributes['max-collections-number'].value) : 12,
                            maxCollectionsPerScreen: block.attributes['max-collections-per-screen'] != undefined ? Number(block.attributes['max-collections-per-screen'].value) : 6,
                            spaceBetweenCollections: block.attributes['space-between-collections'] != undefined ? Number(block.attributes['space-between-collections'].value) : 32,
                            spaceAroundCarousel: block.attributes['space-around-carousel'] != undefined ? Number(block.attributes['space-around-carousel'].value) : 50,
                            arrowsPosition: block.attributes['arrows-position'] != undefined ? block.attributes['arrows-position'].value : undefined,
                            autoPlay: block.attributes['auto-play'] != undefined ? block.attributes['auto-play'].value == 'true' : false,
                            largeArrows: block.attributes['large-arrows'] != undefined ? block.attributes['large-arrows'].value == 'true' : false,
                            arrowsStyle: block.attributes['arrows-style'] != undefined ? block.attributes['arrows-style'].value : 'type-1',
                            autoPlaySpeed: block.attributes['auto-play-speed'] != undefined ? Number(block.attributes['auto-play-speed'].value) : 3,
                            loopSlides: block.attributes['loop-slides'] != undefined ? block.attributes['loop-slides'].value == 'true' : false,
                            imageSize: block.attributes['image-size'] != undefined ? block.attributes['image-size'].value : 'tainacan-medium',
                            hideName: block.attributes['hide-name'] != undefined ? block.attributes['hide-name'].value == 'true' : false,
                            showCollectionThumbnail: block.attributes['show-collection-thumbnail'] != undefined ? block.attributes['show-collection-thumbnail'].value == 'true' : false,
                            tainacanApiRoot: block.attributes['tainacan-api-root'] != undefined ? block.attributes['tainacan-api-root'].value : undefined,
                            tainacanBaseUrl: block.attributes['tainacan-base-url'] != undefined ? block.attributes['tainacan-base-url'].value : undefined,
                        });
                    },
                    mounted() {
                        block.classList.add('has-mounted');
                    }
                });

                VueCollectionsList.use(VueBlurHash);
                VueCollectionsList.use(ThumbnailHelperPlugin);

                VueCollectionsList.mount('#' + block.id);
            });
        }
    }

    // This is rendered on the theme side.
    renderTainacanCollectionsCarouselBlocks();

    // Also if a theme or plugin requested a reset...
    document.addEventListener("TainacanReloadCarouselCollectionsListBlock", () => {
        renderTainacanCollectionsCarouselBlocks();
    });
}