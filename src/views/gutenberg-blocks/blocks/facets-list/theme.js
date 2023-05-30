import Vue from 'vue';
import FacetsListTheme from './theme.vue';
import FacetsListThemeUnit from './facet-unit.vue';

export default (element) => {
    
    // Vue Dev Tools!
    Vue.config.devtools = TAINACAN_ENV === 'development';
    Vue.component('facets-list-theme-unit', FacetsListThemeUnit);

    function renderTainacanFacetsListBlock() {

        // Gets all divs with content created by our block;
        let blocksElements = element ? [ element ] : document.getElementsByClassName('wp-block-tainacan-facets-list');
        
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
                        metadatumId: '',
                        metadatumType: '', 
                        collectionId: '',
                        collectionSlug: '',
                        parentTermId: null,  
                        showImage: true,
                        showItemsCount: true,
                        showSearchBar: false,
                        showLoadMore: false,
                        nameInsideImage: false,
                        linkTermFacetsToTermPage: true,
                        appendChildTerms: false,
                        imageSize: 'tainacan-medium',
                        layout: 'grid',
                        itemsCountStyle: 'default',
                        cloudRate: 1,
                        gridMargin: 24,
                        maxFacetsNumber: 12,
                        maxColumnsCount: 5,
                        tainacanApiRoot: '',
                        tainacanBaseUrl: '',
                        tainacanSiteUrl: '',
                        className: '',
                        style: ''
                    },
                    render(h){ 
                        return h(FacetsListTheme, {
                            props: {
                                metadatumId: this.metadatumId,
                                metadatumType: this.metadatumType, 
                                collectionId: this.collectionId,
                                collectionSlug: this.collectionSlug,
                                parentTermId: this.parentTermId,  
                                showImage: this.showImage,
                                nameInsideImage: this.nameInsideImage,
                                showItemsCount: this.showItemsCount,
                                showSearchBar: this.showSearchBar,
                                showLoadMore: this.showLoadMore,
                                layout: this.layout,
                                itemsCountStyle: this.itemsCountStyle,
                                cloudRate: this.cloudRate,
                                gridMargin: this.gridMargin,
                                imageSize: this.imageSize,
                                linkTermFacetsToTermPage: this.linkTermFacetsToTermPage,
                                appendChildTerms: this.appendChildTerms,
                                maxFacetsNumber: this.maxFacetsNumber,
                                maxColumnsCount: this.maxColumnsCount,
                                tainacanApiRoot: this.tainacanApiRoot,
                                tainacanBaseUrl: this.tainacanBaseUrl,
                                tainacanSiteUrl: this.tainacanSiteUrl,
                                className: this.className,
                                customStyle: this.style
                            }
                        });
                    },
                    beforeMount () {
                        this.metadatumId = this.$el.attributes['metadatum-id'] != undefined ? this.$el.attributes['metadatum-id'].value : undefined;
                        this.metadatumType = this.$el.attributes['metadatum-type'] != undefined ? this.$el.attributes['metadatum-type'].value : undefined;
                        this.collectionId = this.$el.attributes['collection-id'] != undefined ? this.$el.attributes['collection-id'].value : undefined;
                        this.collectionSlug = this.$el.attributes['collection-slug'] != undefined ? this.$el.attributes['collection-slug'].value : undefined;
                        this.appendChildTerms = this.$el.attributes['append-child-terms'] != undefined ? this.$el.attributes['append-child-terms'].value == 'true' : false;
                        this.parentTermId = this.$el.attributes['parent-term-id'] != undefined ? this.$el.attributes['parent-term-id'].value : undefined;
                        this.showImage = this.$el.attributes['show-image'] != undefined ? this.$el.attributes['show-image'].value == 'true' : true;
                        this.nameInsideImage = this.$el.attributes['name-inside-image'] != undefined ? this.$el.attributes['name-inside-image'].value == 'true' : false;
                        this.showItemsCount = this.$el.attributes['show-items-count'] != undefined ? this.$el.attributes['show-items-count'].value == 'true' : true;
                        this.showSearchBar = this.$el.attributes['show-search-bar'] != undefined ? this.$el.attributes['show-search-bar'].value == 'true' : false;
                        this.showLoadMore = this.$el.attributes['show-load-more'] != undefined ? this.$el.attributes['show-load-more'].value == 'true' : false;
                        this.layout = this.$el.attributes['layout'] != undefined ? this.$el.attributes['layout'].value : undefined;
                        this.itemsCountStyle = this.$el.attributes['items-count-style'] != undefined ? this.$el.attributes['items-count-style'].value : undefined;
                        this.cloudRate = this.$el.attributes['cloud-rate'] != undefined ? Number(this.$el.attributes['cloud-rate'].value) : undefined;
                        this.gridMargin = this.$el.attributes['grid-margin'] != undefined ? Number(this.$el.attributes['grid-margin'].value) : undefined;
                        this.imageSize = this.$el.attributes['image-size'] != undefined ? this.$el.attributes['image-size'].value : 'tainacan-medium';
                        this.linkTermFacetsToTermPage = this.$el.attributes['link-term-facets-to-term-page'] != undefined ? this.$el.attributes['link-term-facets-to-term-page'].value == 'true' : true;
                        this.maxFacetsNumber = this.$el.attributes['max-facets-number'] != undefined ? this.$el.attributes['max-facets-number'].value : undefined;
                        this.maxColumnsCount = this.$el.attributes['max-columns-count'] != undefined ? this.$el.attributes['max-columns-count'].value : 5;
                        this.tainacanApiRoot = this.$el.attributes['tainacan-api-root'] != undefined ? this.$el.attributes['tainacan-api-root'].value : undefined;
                        this.tainacanBaseUrl = this.$el.attributes['tainacan-base-url'] != undefined ? this.$el.attributes['tainacan-base-url'].value : undefined;
                        this.tainacanSiteUrl = this.$el.attributes['tainacan-site-url'] != undefined ? this.$el.attributes['tainacan-site-url'].value : undefined;
                        this.className = this.$el.attributes.class != undefined ? this.$el.attributes.class.value : undefined;
                        this.style = this.$el.attributes.style != undefined ? this.$el.attributes.style.value : undefined;
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
    }

    // This is rendered on the theme side.
    renderTainacanFacetsListBlock();

    // Also if a theme or plugin requested a reset...
    document.addEventListener("DOMContentLoaded", () => {
        renderTainacanFacetsListBlock();
    });

}