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
            tainacanApiRoot: '',
            tainacanBaseUrl: '',
            className: ''
        },
        render: h => h(DynamicItemsListTheme),
        beforeMount () {
            this.className = this.$el.attributes.class != undefined ? this.$el.attributes.class.value : undefined;
            this.searchURL = this.$el.attributes['search-url'] != undefined ? this.$el.attributes['search-url'].value : undefined;
            this.collectionId = this.$el.attributes['collection-id'] != undefined ? this.$el.attributes['collection-id'].value : undefined;
            this.showImage = this.$el.attributes['show-image'] != undefined ? Boolean(this.$el.attributes['show-image'].value) : true;
            this.showName = this.$el.attributes['show-name'] != undefined ? Boolean(this.$el.attributes['show-name'].value) : true;
            this.layout = this.$el.attributes['layout'] != undefined ? this.$el.attributes['layout'].value : undefined;
            this.gridMargin = this.$el.attributes['grid-margin'] != undefined ? this.$el.attributes['grid-margin'].value : undefined;
            this.maxItemsNumber = this.$el.attributes['max-items-number'] != undefined ? this.$el.attributes['max-items-number'].value : undefined;
            this.order = this.$el.attributes['order'] != undefined ? this.$el.attributes['order'].value : undefined;
            this.showSearchBar = this.$el.attributes['show-search-bar'] != undefined ? Boolean(this.$el.attributes['show-search-bar'].value) : false;
            this.tainacanApiRoot = this.$el.attributes['tainacan-api-root'] != undefined ? this.$el.attributes['tainacan-api-root'].value : undefined;
            this.tainacanBaseUrl = this.$el.attributes['tainacan-base-url'] != undefined ? this.$el.attributes['tainacan-base-url'].value : undefined;
        }
    };

    // Gets all divs with content created by our block;
    let blocks = document.getElementsByClassName('wp-block-tainacan-dynamic-items-list');

    if (blocks) {
        let blockIds = Object.values(blocks).map((block) => block.id);
    
        // Creates a new Vue Instance to manage each block isolatelly·
        for (let blockId of blockIds)
            new Vue( Object.assign({ el: '#' + blockId }, vueOptions) );
    }
});