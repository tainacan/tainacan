
import Vue from 'vue';
import CarouselItemsListTheme from './theme.vue';
import { ThumbnailHelperPlugin } from '../../../admin/js/utilities.js';
import VueBlurHash from 'vue-blurhash';

export default (element) => {

    // Vue Dev Tools!
    Vue.config.devtools = process && process.env && process.env.NODE_ENV === 'development';

    function renderTainacanItemCarouselBlocks() {

        // Gets all divs with content created by our block;
        let blocksElements = element ? [ element ] : document.getElementsByClassName('wp-block-tainacan-carousel-items-list');
        
        if (blocksElements) {
            let blocks = Object.values(blocksElements);

            // Checks if this carousel isn't already mounted
            blocks = blocks.filter((block) => block.classList && !block.classList.contains('has-mounted'));
            const blockIds = blocks.map((block) => block.id);

            // Creates a new Vue Instance to manage each block isolatelly
            for (let blockId of blockIds) {
                // Configure Vue logic before passing it to constructor:
                let vueOptions = {
                    data: {
                        collectionId: '',  
                        searchURL: '',
                        selectedItems: [],
                        loadStrategy: 'search',
                        maxItemsNumber: 12,
                        maxItemsPerScreen: 7,
                        arrowsPosition: 'around',
                        largeArrows: false,
                        autoPlay: false,
                        autoPlaySpeed: 3,
                        loopSlides: false,
                        hideTitle: true,
                        cropImagesToSquare: true,
                        showCollectionHeader: false,
                        showCollectionLabel: false,
                        collectionBackgroundColor: '#454647',
                        collectionTextColor: '#ffffff',
                        tainacanApiRoot: '',
                        tainacanBaseUrl: '',
                        className: ''
                    },
                    render(h){ 
                        return h(CarouselItemsListTheme, {
                            props: {
                                blockId: blockId,
                                collectionId: this.collectionId,  
                                searchURL: this.searchURL,
                                selectedItems: this.selectedItems,
                                loadStrategy: this.loadStrategy,
                                maxItemsNumber: this.maxItemsNumber,
                                maxItemsPerScreen: this.maxItemsPerScreen,
                                arrowsPosition: this.arrowsPosition,
                                largeArrows: this.largeArrows,
                                autoPlay: this.autoPlay,
                                autoPlaySpeed: this.autoPlaySpeed,
                                loopSlides: this.loopSlides,
                                hideTitle: this.hideTitle,
                                cropImagesToSquare: this.cropImagesToSquare,
                                showCollectionHeader: this.showCollectionHeader,
                                showCollectionLabel: this.showCollectionLabel,
                                collectionBackgroundColor: this.collectionBackgroundColor,
                                collectionTextColor: this.collectionTextColor,
                                tainacanApiRoot: this.tainacanApiRoot,
                                tainacanBaseUrl: this.tainacanBaseUrl,
                                className: this.className
                            }
                        });
                    },
                    beforeMount () {
                        this.className = this.$el.attributes.class != undefined ? this.$el.attributes.class.value : undefined;
                        this.searchURL = this.$el.attributes['search-url'] != undefined ? this.$el.attributes['search-url'].value : undefined;
                        this.selectedItems = this.$el.attributes['selected-items'] != undefined ? JSON.parse(this.$el.attributes['selected-items'].value) : undefined;
                        this.loadStrategy = this.$el.attributes['load-strategy'] != undefined ? this.$el.attributes['load-strategy'].value : undefined;
                        this.collectionId = this.$el.attributes['collection-id'] != undefined ? this.$el.attributes['collection-id'].value : undefined;
                        this.maxItemsNumber = this.$el.attributes['max-items-number'] != undefined ? this.$el.attributes['max-items-number'].value : undefined;
                        this.maxItemsPerScreen = this.$el.attributes['max-items-per-screen'] != undefined ? this.$el.attributes['max-items-per-screen'].value : 7;
                        this.arrowsPosition = this.$el.attributes['arrows-position'] != undefined ? this.$el.attributes['arrows-position'].value : undefined;
                        this.largeArrows = this.$el.attributes['large-arrows'] != undefined ? this.$el.attributes['large-arrows'].value == 'true' : false;
                        this.autoPlay = this.$el.attributes['auto-play'] != undefined ? this.$el.attributes['auto-play'].value == 'true' : false;
                        this.autoPlaySpeed = this.$el.attributes['auto-play-speed'] != undefined ? this.$el.attributes['auto-play-speed'].value : 3;
                        this.loopSlides = this.$el.attributes['loop-slides'] != undefined ? this.$el.attributes['loop-slides'].value == 'true' : false;
                        this.hideTitle = this.$el.attributes['hide-title'] != undefined ? this.$el.attributes['hide-title'].value == 'true' : false;
                        this.cropImagesToSquare = this.$el.attributes['crop-images-to-square'] != undefined ? this.$el.attributes['crop-images-to-square'].value == 'true' : true;
                        this.showCollectionHeader = this.$el.attributes['show-collection-header'] != undefined ? this.$el.attributes['show-collection-header'].value == 'true' : false;
                        this.showCollectionLabel = this.$el.attributes['show-collection-label'] != undefined ? this.$el.attributes['show-collection-label'].value == 'true' : false;
                        this.collectionBackgroundColor = this.$el.attributes['collection-background-color'] != undefined ? this.$el.attributes['collection-background-color'].value : undefined;
                        this.collectionTextColor = this.$el.attributes['collection-text-color'] != undefined ? this.$el.attributes['collection-text-color'].value : undefined;
                        this.tainacanApiRoot = this.$el.attributes['tainacan-api-root'] != undefined ? this.$el.attributes['tainacan-api-root'].value : undefined;
                        this.tainacanBaseUrl = this.$el.attributes['tainacan-base-url'] != undefined ? this.$el.attributes['tainacan-base-url'].value : undefined;
                    },
                    methods: {
                        __(text, domain) {
                            return wp.i18n.__(text, domain);
                        }
                    }
                };

                Vue.use(ThumbnailHelperPlugin);
                Vue.use(VueBlurHash);
                new Vue( Object.assign({ el: '#' + blockId }, vueOptions) );
            }
        }
    }

    // This is rendered on the theme side.
    renderTainacanItemCarouselBlocks();

    // Also if a theme or plugin requested a reset...
    document.addEventListener("TainacanReloadCarouselItemsListBlock", () => {
        renderTainacanItemCarouselBlocks();
    });
};