import Vue from 'vue';
import CarouselTermsListTheme from './theme.vue';
import { ThumbnailHelperPlugin } from '../../../admin/js/utilities.js';
import VueBlurHash from 'vue-blurhash';

export default (element) => {

    // Vue Dev Tools!
    Vue.config.devtools = TAINACAN_ENV === 'development';

    function renderTainacanTermsCarouselBlocks() {
        
        // Gets all divs with content created by our block;
        let blocksElements = element ? [ element ] : document.getElementsByClassName('wp-block-tainacan-carousel-terms-list');
                
        if (blocksElements) {
            let blocks = Object.values(blocksElements);

            // Checks if this carousel isn't already mounted
            blocks = blocks.filter((block) => block.classList && !block.classList.contains('has-mounted'));
            const blockIds = Object.values(blocks).map((block) => block.id);

            // Creates a new Vue Instance to manage each block isolatelly
            for (let blockId of blockIds) {

                // Configure Vue logic before passing it to constructor:
                let vueOptions = {
                    data: {
                        selectedItem: [],
                        maxItemsNumber: 12,
                        arrowsPosition: 'around',
                        autoPlay: false,
                        autoPlaySpeed: 3,
                        largeArrows: false,
                        arrowsStyle: 'type-1',
                        maxTermsPerScreen: 6,
                        spaceBetweenTerms: 32,
                        spaceAroundCarousel: 50,
                        imageSize: 'tainacan-medium',
                        loopSlides: false,
                        hideName: true,
                        showTermThumbnail: false,
                        tainacanApiRoot: '',
                        tainacanBaseUrl: '',
                        className: '',
                        taxonomyId: '',
                        style: ''
                    },
                    render(h){ 
                        return h(CarouselTermsListTheme, {
                            props: {
                                blockId: blockId,
                                selectedTerms: this.selectedTerms,
                                maxItemsNumber: this.maxItemsNumber,
                                arrowsPosition: this.arrowsPosition,
                                autoPlay: this.autoPlay,
                                autoPlaySpeed: this.autoPlaySpeed,
                                loopSlides: this.loopSlides,
                                largeArrows: this.largeArrows,
                                arrowsStyle: this.arrowsStyle,
                                imageSize: this.imageSize,
                                maxTermsPerScreen: this.maxTermsPerScreen,
                                spaceBetweenTerms: this.spaceBetweenTerms,
                                spaceAroundCarousel: this.spaceAroundCarousel,
                                hideName: this.hideName,
                                showTermThumbnail: this.showTermThumbnail,
                                tainacanApiRoot: this.tainacanApiRoot,
                                tainacanBaseUrl: this.tainacanBaseUrl,
                                className: this.className,
                                taxonomyId: this.taxonomyId,
                                customStyle: this.style
                            }
                        });
                    },
                    beforeMount () {
                        this.className = this.$el.attributes.class != undefined ? this.$el.attributes.class.value : undefined;
                        this.selectedTerms = this.$el.attributes['selected-terms'] != undefined ? JSON.parse(this.$el.attributes['selected-terms'].value) : undefined;
                        this.maxItemsNumber = this.$el.attributes['max-terms-number'] != undefined ? this.$el.attributes['max-terms-number'].value : undefined;
                        this.maxTermsPerScreen = this.$el.attributes['max-terms-per-screen'] != undefined ? this.$el.attributes['max-terms-per-screen'].value : undefined;
                        this.arrowsPosition = this.$el.attributes['arrows-position'] != undefined ? this.$el.attributes['arrows-position'].value : undefined;
                        this.autoPlay = this.$el.attributes['auto-play'] != undefined ? this.$el.attributes['auto-play'].value == 'true' : false;
                        this.autoPlaySpeed = this.$el.attributes['auto-play-speed'] != undefined ? this.$el.attributes['auto-play-speed'].value : 3;
                        this.spaceBetweenTerms = this.$el.attributes['space-between-terms'] != undefined ? this.$el.attributes['space-between-terms'].value : 32;
                        this.spaceAroundCarousel = this.$el.attributes['space-around-carousel'] != undefined ? this.$el.attributes['space-around-carousel'].value : 50;
                        this.largeArrows = this.$el.attributes['large-arrows'] != undefined ? this.$el.attributes['large-arrows'].value == 'true' : false;
                        this.arrowsStyle = this.$el.attributes['arrows-style'] != undefined ? this.$el.attributes['arrows-style'].value : undefined;
                        this.loopSlides = this.$el.attributes['loop-slides'] != undefined ? this.$el.attributes['loop-slides'].value == 'true' : false;
                        this.imageSize = this.$el.attributes['image-size'] != undefined ? this.$el.attributes['image-size'].value : 'tainacan-medium';
                        this.hideName = this.$el.attributes['hide-name'] != undefined ? this.$el.attributes['hide-name'].value == 'true' : false;
                        this.taxonomyId = this.$el.attributes['taxonomy-id'] != undefined ? this.$el.attributes['taxonomy-id'].value : undefined;
                        this.showTermThumbnail = this.$el.attributes['show-term-thumbnail'] != undefined ? this.$el.attributes['show-term-thumbnail'].value == 'true' : false;
                        this.tainacanApiRoot = this.$el.attributes['tainacan-api-root'] != undefined ? this.$el.attributes['tainacan-api-root'].value : undefined;
                        this.tainacanBaseUrl = this.$el.attributes['tainacan-base-url'] != undefined ? this.$el.attributes['tainacan-base-url'].value : undefined;
                        this.style = this.$el.attributes.style != undefined ? this.$el.attributes.style.value : undefined;
                    },
                    methods: {
                        __(text, domain) {
                            return wp.i18n.__(text, domain);
                        }
                    }
                };
                
                Vue.use(VueBlurHash);
                Vue.use(ThumbnailHelperPlugin);
                new Vue( Object.assign({ el: '#' + blockId }, vueOptions) );
            }
        }
    }

    // This is rendered on the theme side.
    renderTainacanTermsCarouselBlocks();

    // Also if a theme or plugin requested a reset...
    document.addEventListener("TainacanReloadCarouselTermsListBlock", () => {
        renderTainacanTermsCarouselBlocks();
    });
}