import { createApp, h } from 'vue';
import CarouselTermsListTheme from './theme.vue';
import { ThumbnailHelperPlugin } from '../../../admin/js/utilities.js';
import VueBlurHash from 'another-vue3-blurhash';

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
                            selectedTerms: block.attributes['selected-terms'] != undefined ? JSON.parse(block.attributes['selected-terms'].value) : undefined,
                            maxItemsNumber: block.attributes['max-terms-number'] != undefined ? block.attributes['max-terms-number'].value : 12,
                            maxTermsPerScreen: block.attributes['max-terms-per-screen'] != undefined ? Number(block.attributes['max-terms-per-screen'].value) : 6,
                            arrowsPosition: block.attributes['arrows-position'] != undefined ? block.attributes['arrows-position'].value : undefined,
                            autoPlay: block.attributes['auto-play'] != undefined ? block.attributes['auto-play'].value == 'true' : false,
                            autoPlaySpeed: block.attributes['auto-play-speed'] != undefined ? Number(block.attributes['auto-play-speed'].value) : 3,
                            spaceBetweenTerms: block.attributes['space-between-terms'] != undefined ? Number(block.attributes['space-between-terms'].value) : 32,
                            spaceAroundCarousel: block.attributes['space-around-carousel'] != undefined ? Number(block.attributes['space-around-carousel'].value) : 50,
                            largeArrows: block.attributes['large-arrows'] != undefined ? block.attributes['large-arrows'].value == 'true' : false,
                            arrowsStyle: block.attributes['arrows-style'] != undefined ? block.attributes['arrows-style'].value : 'type-1',
                            loopSlides: block.attributes['loop-slides'] != undefined ? block.attributes['loop-slides'].value == 'true' : false,
                            imageSize: block.attributes['image-size'] != undefined ? block.attributes['image-size'].value : 'tainacan-medium',
                            hideName: block.attributes['hide-name'] != undefined ? block.attributes['hide-name'].value == 'true' : false,
                            taxonomyId: block.attributes['taxonomy-id'] != undefined ? block.attributes['taxonomy-id'].value : undefined,
                            showTermThumbnail: block.attributes['show-term-thumbnail'] != undefined ? block.attributes['show-term-thumbnail'].value == 'true' : false,
                            tainacanApiRoot: block.attributes['tainacan-api-root'] != undefined ? block.attributes['tainacan-api-root'].value : undefined,
                            tainacanBaseUrl: block.attributes['tainacan-base-url'] != undefined ? block.attributes['tainacan-base-url'].value : undefined
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