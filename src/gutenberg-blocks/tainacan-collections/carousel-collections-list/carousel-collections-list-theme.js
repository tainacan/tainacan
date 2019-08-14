import Vue from 'vue';
import CarouselCollectionsListTheme from './carousel-collections-list-theme.vue';

// This is rendered on the theme side.
document.addEventListener("DOMContentLoaded", () => {

    // Configure Vue logic before passing it to constructor:
    let vueOptions = {
        data: {
            collectionId: '',  
            selectedItem: [],
            maxItemsNumber: 12,
            arrowsPosition: 'around',
            autoPlay: false,
            autoPlaySpeed: 3,
            loopSlides: false,
            hideTitle: true,
            tainacanApiRoot: '',
            tainacanBaseUrl: '',
            className: ''
        },
        render(h){ 
            return h(CarouselCollectionsListTheme, {
                props: {
                    collectionId: this.collectionId,  
                    selectedCollections: this.selectedCollections,
                    maxItemsNumber: this.maxItemsNumber,
                    arrowsPosition: this.arrowsPosition,
                    autoPlay: this.autoPlay,
                    autoPlaySpeed: this.autoPlaySpeed,
                    loopSlides: this.loopSlides,
                    hideTitle: this.hideTitle,
                    tainacanApiRoot: this.tainacanApiRoot,
                    tainacanBaseUrl: this.tainacanBaseUrl,
                    className: this.className,
                }
            });
        },
        beforeMount () {
            this.className = this.$el.attributes.class != undefined ? this.$el.attributes.class.value : undefined;
            this.selectedCollections = this.$el.attributes['selected-collections'] != undefined ? JSON.parse(this.$el.attributes['selected-collections'].value) : undefined;
            this.collectionId = this.$el.attributes['collection-id'] != undefined ? this.$el.attributes['collection-id'].value : undefined;
            this.maxItemsNumber = this.$el.attributes['max-collections-number'] != undefined ? this.$el.attributes['max-collections-number'].value : undefined;
            this.arrowsPosition = this.$el.attributes['arrows-position'] != undefined ? this.$el.attributes['arrows-position'].value : undefined;
            this.autoPlay = this.$el.attributes['auto-play'] != undefined ? this.$el.attributes['auto-play'].value == 'true' : false;
            this.autoPlaySpeed = this.$el.attributes['auto-play-speed'] != undefined ? this.$el.attributes['auto-play-speed'].value : 3;
            this.loopSlides = this.$el.attributes['loop-slides'] != undefined ? this.$el.attributes['loop-slides'].value == 'true' : false;
            this.hideTitle = this.$el.attributes['hide-title'] != undefined ? this.$el.attributes['hide-title'].value == 'true' : false;
            this.tainacanApiRoot = this.$el.attributes['tainacan-api-root'] != undefined ? this.$el.attributes['tainacan-api-root'].value : undefined;
            this.tainacanBaseUrl = this.$el.attributes['tainacan-base-url'] != undefined ? this.$el.attributes['tainacan-base-url'].value : undefined;
        },
        methods: {
            __(text, domain) {
                return wp.i18n.__(text, domain);
            }
        }
    };

    // Gets all divs with content created by our block;
    let blocks = document.getElementsByClassName('wp-block-tainacan-carousel-collections-list');
    
    if (blocks) {
        let blockIds = Object.values(blocks).map((block) => block.id);

        // Creates a new Vue Instance to manage each block isolatelly
        for (let blockId of blockIds) {
            new Vue( Object.assign({ el: '#' + blockId }, jQuery.extend(true, {}, vueOptions)) );
        }
    }
});