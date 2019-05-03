import Vue from 'vue';
import DynamicItemsListTheme from './dynamic-items-list-theme.vue';

// This is rendered on the theme side.
document.addEventListener("DOMContentLoaded", () => {

    // Configure Vue logic before passing it to constructor:
    let vueOptions = {
        data: {
            searchURL: ''
        },
        render: h => h(DynamicItemsListTheme),
        beforeMount () {
            this.searchURL = this.$el.attributes['searchurl'] != undefined ? this.$el.attributes['searchurl'].value : undefined;
        },
        // All the Vue logic that we need.
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