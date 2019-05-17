import Vue from 'vue';
import DynamicItemsListTheme from './dynamic-items-list-theme.vue';

// This is rendered on the theme side.
document.addEventListener("DOMContentLoaded", () => {

    // Configure Vue logic before passing it to constructor:
    let vueOptions = {
        data: {
            collectionId: '',  
            showImage: true,
            showName: true,
            layout: 'grid',
            gridMargin: 0,
            searchURL: '',
            maxItemsNumber: 12,
            order: 'asc',
            showSearchBar: false,
            showCollectionHeader: false,
            showCollectionLabel: false,
            collectionBackgroundColor: '#454647',
            collectionTextColor: '#ffffff',
            tainacanApiRoot: '',
            tainacanBaseUrl: '',
            className: ''
        },
        render(h){ 
            return h(DynamicItemsListTheme, {
                props: {
                    collectionId: this.collectionId,  
                    showImage: this.showImage,
                    showName: this.showName,
                    layout: this.layout,
                    gridMargin: this.gridMargin,
                    searchURL: this.searchURL,
                    maxItemsNumber: this.maxItemsNumber,
                    order: this.order,
                    showSearchBar: this.showSearchBar,
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
            this.collectionId = this.$el.attributes['collection-id'] != undefined ? this.$el.attributes['collection-id'].value : undefined;
            this.showImage = this.$el.attributes['show-image'] != undefined ? this.$el.attributes['show-image'].value == 'true' : true;
            this.showName = this.$el.attributes['show-name'] != undefined ? this.$el.attributes['show-name'].value == 'true' : true;
            this.layout = this.$el.attributes['layout'] != undefined ? this.$el.attributes['layout'].value : undefined;
            this.gridMargin = this.$el.attributes['grid-margin'] != undefined ? Number(this.$el.attributes['grid-margin'].value) : undefined;
            this.maxItemsNumber = this.$el.attributes['max-items-number'] != undefined ? this.$el.attributes['max-items-number'].value : undefined;
            this.order = this.$el.attributes['order'] != undefined ? this.$el.attributes['order'].value : undefined;
            this.showSearchBar = this.$el.attributes['show-search-bar'] != undefined ? this.$el.attributes['show-search-bar'].value == 'true' : false;
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

    // Gets all divs with content created by our block;
    let blocks = document.getElementsByClassName('wp-block-tainacan-dynamic-items-list');
    
    if (blocks) {
        let blockIds = Object.values(blocks).map((block) => block.id);

        // Creates a new Vue Instance to manage each block isolatelly
        for (let blockId of blockIds) {
            new Vue( Object.assign({ el: '#' + blockId }, jQuery.extend(true, {}, vueOptions)) );
        }
    }
});