import Vue from 'vue';
import CarouselCollectionsListTheme from './carousel-collections-list-theme.vue';
import { ThumbnailHelperPlugin } from '../../../admin/js/utilities.js';
import VueBlurHash from 'vue-blurhash';

// Vue Dev Tools!
Vue.config.devtools = process && process.env && process.env.NODE_ENV === 'development';

// This is rendered on the theme side.
document.addEventListener("DOMContentLoaded", () => {

    // Gets all divs with content created by our block;
    let blocks = document.getElementsByClassName('wp-block-tainacan-carousel-collections-list');
    
    if (blocks) {
        let blockIds = Object.values(blocks).map((block) => block.id);

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
                    maxCollectionsPerScreen: 6,
                    cropImagesToSquare: true,
                    loopSlides: false,
                    hideName: true,
                    showCollectionThumbnail: false,
                    tainacanApiRoot: '',
                    tainacanBaseUrl: '',
                    className: ''
                },
                render(h){ 
                    return h(CarouselCollectionsListTheme, {
                        props: {
                            blockId: blockId,
                            selectedCollections: this.selectedCollections,
                            maxItemsNumber: this.maxItemsNumber,
                            arrowsPosition: this.arrowsPosition,
                            autoPlay: this.autoPlay,
                            autoPlaySpeed: this.autoPlaySpeed,
                            loopSlides: this.loopSlides,
                            largeArrows: this.largeArrows,
                            cropImagesToSquare: this.cropImagesToSquare,
                            maxCollectionsPerScreen: this.maxCollectionsPerScreen,
                            hideName: this.hideName,
                            showCollectionThumbnail: this.showCollectionThumbnail,
                            tainacanApiRoot: this.tainacanApiRoot,
                            tainacanBaseUrl: this.tainacanBaseUrl,
                            className: this.className,
                        }
                    });
                },
                beforeMount () {
                    this.className = this.$el.attributes.class != undefined ? this.$el.attributes.class.value : undefined;
                    this.selectedCollections = this.$el.attributes['selected-collections'] != undefined ? JSON.parse(this.$el.attributes['selected-collections'].value) : undefined;
                    this.maxItemsNumber = this.$el.attributes['max-collections-number'] != undefined ? this.$el.attributes['max-collections-number'].value : undefined;
                    this.maxCollectionsPerScreen = this.$el.attributes['max-collections-per-screen'] != undefined ? this.$el.attributes['max-collections-per-screen'].value : 6;
                    this.arrowsPosition = this.$el.attributes['arrows-position'] != undefined ? this.$el.attributes['arrows-position'].value : undefined;
                    this.autoPlay = this.$el.attributes['auto-play'] != undefined ? this.$el.attributes['auto-play'].value == 'true' : false;
                    this.largeArrows = this.$el.attributes['large-arrows'] != undefined ? this.$el.attributes['large-arrows'].value == 'true' : false;
                    this.autoPlaySpeed = this.$el.attributes['auto-play-speed'] != undefined ? this.$el.attributes['auto-play-speed'].value : 3;
                    this.loopSlides = this.$el.attributes['loop-slides'] != undefined ? this.$el.attributes['loop-slides'].value == 'true' : false;
                    this.cropImagesToSquare = this.$el.attributes['crop-images-to-square'] != undefined ? this.$el.attributes['crop-images-to-square'].value == 'true' : false;
                    this.hideName = this.$el.attributes['hide-name'] != undefined ? this.$el.attributes['hide-name'].value == 'true' : false;
                    this.showCollectionThumbnail = this.$el.attributes['show-collection-thumbnail'] != undefined ? this.$el.attributes['show-collection-thumbnail'].value == 'true' : false;
                    this.tainacanApiRoot = this.$el.attributes['tainacan-api-root'] != undefined ? this.$el.attributes['tainacan-api-root'].value : undefined;
                    this.tainacanBaseUrl = this.$el.attributes['tainacan-base-url'] != undefined ? this.$el.attributes['tainacan-base-url'].value : undefined;
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
});