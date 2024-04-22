import { createApp, h } from 'vue';
import CarouselItemsListTheme from './theme.vue';
import { ThumbnailHelperPlugin } from '../../../admin/js/utilities.js';
import VueBlurHash from 'another-vue3-blurhash';
import getDataAttribute from '../../js/compatibility/tainacan-blocks-compat-data-attributes.js';

export default (element) => {

    function renderTainacanItemCarouselBlocks() {

        // Gets all divs with content created by our block;
        let blocksElements = element ? [element] : document.getElementsByClassName('wp-block-tainacan-carousel-items-list');

        if (blocksElements) {
            let blocks = Object.values(blocksElements);

            // Checks if this carousel isn't already mounted
            blocks = blocks.filter((block) => block.classList && !block.classList.contains('has-mounted'));

            // Creates a new Vue Instance to manage each block isolatelly
            blocks.forEach((block) => {

                const VueCarouselItemsList = createApp({
                    render() {
                        return h(CarouselItemsListTheme, {
                            blockId: block.id,
                            searchURL: getDataAttribute(block, 'search-url'),
                            selectedItems: JSON.parse(getDataAttribute(block, 'selected-items', '[]')),
                            loadStrategy: getDataAttribute(block, 'load-strategy', 'search'),
                            collectionId: getDataAttribute(block, 'collection-id', undefined),
                            maxItemsNumber: Number(getDataAttribute(block, 'max-items-number', 12)),
                            maxItemsPerScreen: Number(getDataAttribute(block, 'max-items-per-screen', 7)),
                            spaceBetweenItems: Number(getDataAttribute(block, 'space-between-items', 32)),
                            spaceAroundCarousel: Number(getDataAttribute(block, 'space-around-carousel', 50)),
                            arrowsPosition: getDataAttribute(block, 'arrows-position', 'around'),
                            largeArrows: getDataAttribute(block, 'large-arrows', false) == 'true',
                            arrowsStyle: getDataAttribute(block, 'arrows-style', 'type-1'),
                            autoPlay: getDataAttribute(block, 'auto-play', false) == 'true',
                            autoPlaySpeed: Number(getDataAttribute(block, 'auto-play-speed', 3)),
                            loopSlides: getDataAttribute(block, 'loop-slides', false) == 'true',
                            hideTitle: getDataAttribute(block, 'hide-title', false) == 'true',
                            imageSize: getDataAttribute(block, 'image-size', 'tainacan-medium'),
                            showCollectionHeader: getDataAttribute(block, 'show-collection-header', false) == 'true',
                            showCollectionLabel: getDataAttribute(block, 'show-collection-label', false) == 'true',
                            collectionBackgroundColor: getDataAttribute(block, 'collection-background-color', '#373839'),
                            collectionTextColor: getDataAttribute(block, 'collection-text-color', '#ffffff'),
                            tainacanApiRoot: getDataAttribute(block, 'tainacan-api-root')
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