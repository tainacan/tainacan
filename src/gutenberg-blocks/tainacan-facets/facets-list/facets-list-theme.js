import Vue from 'vue';
import FacetsListTheme from './facets-list-theme.vue';

// This is rendered on the theme side.
document.addEventListener("DOMContentLoaded", () => {

    // Configure Vue logic before passing it to constructor:
    let vueOptions = {
        data: {
            metadatumId: '',
            metadatumType: '', 
            collectionId: '',
            collectionSlug: '',  
            showImage: true,
            showItemsCount: true,
            showSearchBar: false,
            showLoadMore: false,
            layout: 'grid',
            cloudRate: 1,
            gridMargin: 0,
            maxFacetsNumber: 12,
            tainacanApiRoot: '',
            tainacanBaseUrl: '',
            tainacanSiteUrl: '',
            className: ''
        },
        render(h){ 
            return h(FacetsListTheme, {
                props: {
                    metadatumId: this.metadatumId,
                    metadatumType: this.metadatumType, 
                    collectionId: this.collectionId,
                    collectionSlug: this.collectionSlug,  
                    showImage: this.showImage,
                    showItemsCount: this.showItemsCount,
                    showSearchBar: this.showSearchBar,
                    showLoadMore: this.showLoadMore,
                    layout: this.layout,
                    cloudRate: this.cloudRate,
                    gridMargin: this.gridMargin,
                    maxFacetsNumber: this.maxFacetsNumber,
                    tainacanApiRoot: this.tainacanApiRoot,
                    tainacanBaseUrl: this.tainacanBaseUrl,
                    tainacanSiteUrl: this.tainacanSiteUrl,
                    className: this.className    
                }
            });
        },
        beforeMount () {
            this.metadatumId = this.$el.attributes['metadatum-id'] != undefined ? this.$el.attributes['metadatum-id'].value : undefined;
            this.metadatumType = this.$el.attributes['metadatum-type'] != undefined ? this.$el.attributes['metadatum-type'].value : undefined;
            this.collectionId = this.$el.attributes['collection-id'] != undefined ? this.$el.attributes['collection-id'].value : undefined;
            this.collectionSlug = this.$el.attributes['collection-slug'] != undefined ? this.$el.attributes['collection-slug'].value : undefined;
            this.showImage = this.$el.attributes['show-image'] != undefined ? this.$el.attributes['show-image'].value == 'true' : true;
            this.showItemsCount = this.$el.attributes['show-items-count'] != undefined ? this.$el.attributes['show-items-count'].value == 'true' : true;
            this.showSearchBar = this.$el.attributes['show-search-bar'] != undefined ? this.$el.attributes['show-search-bar'].value == 'true' : false;
            this.showLoadMore = this.$el.attributes['show-load-more'] != undefined ? this.$el.attributes['show-load-more'].value == 'true' : false;
            this.layout = this.$el.attributes['layout'] != undefined ? this.$el.attributes['layout'].value : undefined;
            this.cloudRate = this.$el.attributes['cloud-rate'] != undefined ? Number(this.$el.attributes['cloud-rate'].value) : undefined;
            this.gridMargin = this.$el.attributes['grid-margin'] != undefined ? Number(this.$el.attributes['grid-margin'].value) : undefined;
            this.maxFacetsNumber = this.$el.attributes['max-facets-number'] != undefined ? this.$el.attributes['max-facets-number'].value : undefined;
            this.tainacanApiRoot = this.$el.attributes['tainacan-api-root'] != undefined ? this.$el.attributes['tainacan-api-root'].value : undefined;
            this.tainacanBaseUrl = this.$el.attributes['tainacan-base-url'] != undefined ? this.$el.attributes['tainacan-base-url'].value : undefined;
            this.tainacanSiteUrl = this.$el.attributes['tainacan-site-url'] != undefined ? this.$el.attributes['tainacan-site-url'].value : undefined;
            this.className = this.$el.attributes.class != undefined ? this.$el.attributes.class.value : undefined;
        },
        methods: {
            __(text, domain) {
                return wp.i18n.__(text, domain);
            }
        }
    };
    // Gets all divs with content created by our block;
    let blocks = document.getElementsByClassName('wp-block-tainacan-facets-list');
    
    if (blocks) {
        let blockIds = Object.values(blocks).map((block) => block.id);

        // Creates a new Vue Instance to manage each block isolatelly
        for (let blockId of blockIds) {
            new Vue( Object.assign({ el: '#' + blockId }, jQuery.extend(true, {}, vueOptions)) );
        }
    }
});