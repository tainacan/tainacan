import Vue from 'vue';
import CarouselTermsListTheme from './carousel-terms-list-theme.vue';

// This is rendered on the theme side.
document.addEventListener("DOMContentLoaded", () => {

    // Gets all divs with content created by our block;
    let blocks = document.getElementsByClassName('wp-block-tainacan-carousel-terms-list');
    
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
                    loopSlides: false,
                    hideName: true,
                    showTermThumbnail: false,
                    tainacanApiRoot: '',
                    tainacanBaseUrl: '',
                    className: '',
                    taxonomyId: ''
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
                            hideName: this.hideName,
                            showTermThumbnail: this.showTermThumbnail,
                            tainacanApiRoot: this.tainacanApiRoot,
                            tainacanBaseUrl: this.tainacanBaseUrl,
                            className: this.className,
                            taxonomyId: this.taxonomyId
                        }
                    });
                },
                beforeMount () {
                    this.className = this.$el.attributes.class != undefined ? this.$el.attributes.class.value : undefined;
                    this.selectedTerms = this.$el.attributes['selected-terms'] != undefined ? JSON.parse(this.$el.attributes['selected-terms'].value) : undefined;
                    this.maxItemsNumber = this.$el.attributes['max-terms-number'] != undefined ? this.$el.attributes['max-terms-number'].value : undefined;
                    this.arrowsPosition = this.$el.attributes['arrows-position'] != undefined ? this.$el.attributes['arrows-position'].value : undefined;
                    this.autoPlay = this.$el.attributes['auto-play'] != undefined ? this.$el.attributes['auto-play'].value == 'true' : false;
                    this.autoPlaySpeed = this.$el.attributes['auto-play-speed'] != undefined ? this.$el.attributes['auto-play-speed'].value : 3;
                    this.loopSlides = this.$el.attributes['loop-slides'] != undefined ? this.$el.attributes['loop-slides'].value == 'true' : false;
                    this.hideName = this.$el.attributes['hide-name'] != undefined ? this.$el.attributes['hide-name'].value == 'true' : false;
                    this.taxonomyId = this.$el.attributes['taxonomy-id'] != undefined ? this.$el.attributes['taxonomy-id'].value : undefined;
                    this.showTermThumbnail = this.$el.attributes['show-term-thumbnail'] != undefined ? this.$el.attributes['show-term-thumbnail'].value == 'true' : false;
                    this.tainacanApiRoot = this.$el.attributes['tainacan-api-root'] != undefined ? this.$el.attributes['tainacan-api-root'].value : undefined;
                    this.tainacanBaseUrl = this.$el.attributes['tainacan-base-url'] != undefined ? this.$el.attributes['tainacan-base-url'].value : undefined;
                },
                methods: {
                    __(text, domain) {
                        return wp.i18n.__(text, domain);
                    }
                }
            };

            new Vue( Object.assign({ el: '#' + blockId }, vueOptions) );
        }
    }
});