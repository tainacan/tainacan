import { createApp, h } from 'vue';
import CarouselTermsListTheme from './theme.vue';
import { ThumbnailHelperPlugin } from '../../../admin/js/utilities.js';
import VueBlurHash from 'another-vue3-blurhash';
import getDataAttribute from '../../js/compatibility/tainacan-blocks-compat-data-attributes.js';

export default (element) => {

    function renderTainacanTermsCarouselBlocks() {
        
        // Gets all divs with content created by our block;
        let blocksElements = element ? [ element ] : document.getElementsByClassName('wp-block-tainacan-carousel-terms-list');
                
        if (blocksElements) {
            let blocks = Object.values(blocksElements);

            // Checks if this carousel isn't already mounted
            blocks = blocks.filter((block) => block.classList && !block.classList.contains('has-mounted'));

            // Creates a new Vue Instance to manage each block isolatelly
            blocks.forEach((block) => {

                const VueCarouselTermsList = createApp({
                    render() { 
                        return h(CarouselTermsListTheme, {
                            blockId: block.id,
                            selectedTerms: JSON.parse(getDataAttribute(block, 'selected-terms', '[]')),
                            maxItemsNumber: Number(getDataAttribute(block, 'max-terms-number', 12)),
                            maxTermsPerScreen: Number(getDataAttribute(block, 'max-terms-per-screen', 6)),
                            arrowsPosition: getDataAttribute(block, 'arrows-position', undefined),
                            autoPlay: getDataAttribute(block, 'auto-play', 'false') == 'true',
                            autoPlaySpeed: Number(getDataAttribute(block, 'auto-play-speed', 3)),
                            spaceBetweenTerms: Number(getDataAttribute(block, 'space-between-terms', 32)),
                            spaceAroundCarousel: Number(getDataAttribute(block, 'space-around-carousel', 50)),
                            largeArrows: getDataAttribute(block, 'large-arrows', 'false') == 'true',
                            arrowsStyle: getDataAttribute(block, 'arrows-style', 'type-1'),
                            loopSlides: getDataAttribute(block, 'loop-slides', 'false') == 'true',
                            imageSize: getDataAttribute(block, 'image-size', 'tainacan-medium'),
                            hideName: getDataAttribute(block, 'hide-name', 'false') == 'true',
                            taxonomyId: getDataAttribute(block, 'taxonomy-id', undefined),
                            showTermThumbnail: getDataAttribute(block, 'show-term-thumbnail', 'false') == 'true',
                            tainacanApiRoot: getDataAttribute(block, 'tainacan-api-root', undefined),
                        });
                    },
                    mounted() {
                        block.classList.add('has-mounted');
                    }
                });

                VueCarouselTermsList.use(VueBlurHash);
                VueCarouselTermsList.use(ThumbnailHelperPlugin);

                VueCarouselTermsList.mount('#' + block.id);
            });
        }
    }

    // This is rendered on the theme side.
    renderTainacanTermsCarouselBlocks();

    // Also if a theme or plugin requested a reset...
    document.addEventListener("TainacanReloadCarouselTermsListBlock", () => {
        renderTainacanTermsCarouselBlocks();
    });
}