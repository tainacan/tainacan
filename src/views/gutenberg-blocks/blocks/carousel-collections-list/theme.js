import { createApp, h } from 'vue';
import CarouselCollectionsListTheme from './theme.vue';
import { ThumbnailHelperPlugin } from '../../../admin/js/utilities.js';
import VueBlurHash from 'another-vue3-blurhash';
import getDataAttribute from '../../js/compatibility/tainacan-blocks-compat-data-attributes.js';

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
                            selectedCollections: JSON.parse(getDataAttribute(block, 'selected-collections', '[]')),
                            maxItemsNumber: Number(getDataAttribute(block, 'max-collections-number', 12)),
                            maxCollectionsPerScreen: Number(getDataAttribute(block, 'max-collections-per-screen', 9)),
                            spaceBetweenCollections: Number(getDataAttribute(block, 'space-between-collections', 32)),
                            spaceAroundCarousel: Number(getDataAttribute(block, 'space-around-carousel', 50)),
                            arrowsPosition: getDataAttribute(block, 'arrows-position', 'around'),
                            autoPlay: getDataAttribute(block, 'auto-play', 'false') == 'true',
                            largeArrows: getDataAttribute(block, 'large-arrows', 'false') == 'true',
                            arrowsStyle: getDataAttribute(block, 'arrows-style', 'type-1'),
                            autoPlaySpeed: Number(getDataAttribute(block, 'auto-play-speed', 3)),
                            loopSlides: getDataAttribute(block, 'loop-slides', 'false') == 'true',
                            imageSize: getDataAttribute(block, 'image-size', 'tainacan-medium'),
                            hideName: getDataAttribute(block, 'hide-name', 'false') == 'true',
                            showCollectionThumbnail: getDataAttribute(block, 'show-collection-thumbnail', 'false') == 'true',
                            tainacanApiRoot: getDataAttribute(block, 'tainacan-api-root'),
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