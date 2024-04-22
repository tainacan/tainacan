import { createApp, h } from 'vue';
import FacetsListTheme from './theme.vue';
import FacetsListThemeUnit from './facet-unit.vue';
import { ThumbnailHelperPlugin } from '../../../admin/js/utilities.js';
import getDataAttribute from '../../js/compatibility/tainacan-blocks-compat-data-attributes.js';

export default (element) => {

    function renderTainacanFacetsListBlock() {

        // Gets all divs with content created by our block;
        let blocksElements = element ? [ element ] : document.getElementsByClassName('wp-block-tainacan-facets-list');
        
        if (blocksElements) {
            let blocks = Object.values(blocksElements);

            // Checks if this carousel isn't already mounted
            blocks = blocks.filter((block) => block.classList && !block.classList.contains('has-mounted'));
            
            // Creates a new Vue Instance to manage each block isolatelly
            blocks.forEach((block) => {
                
                // Configure Vue logic before passing it to constructor:
                const VueFacetsList = createApp({
                    render() { 
                        return h(FacetsListTheme, {
                            metadatumId: getDataAttribute(block, 'metadatum-id'),
                            metadatumType: getDataAttribute(block, 'metadatum-type'),
                            collectionId: getDataAttribute(block, 'collection-id'),
                            collectionSlug: getDataAttribute(block, 'collection-slug'),
                            appendChildTerms: getDataAttribute(block, 'append-child-terms', false) == 'true',
                            parentTermId: getDataAttribute(block, 'parent-term-id'),
                            showImage: getDataAttribute(block, 'show-image', true) == 'true',
                            nameInsideImage: getDataAttribute(block, 'name-inside-image', false) == 'true',
                            showItemsCount: getDataAttribute(block, 'show-items-count', true) == 'true',
                            showSearchBar: getDataAttribute(block, 'show-search-bar', false) == 'true',
                            showLoadMore: getDataAttribute(block, 'show-load-more', false) == 'true',
                            layout: getDataAttribute(block, 'layout', 'grid'),
                            itemsCountStyle: getDataAttribute(block, 'items-count-style', 'default'),
                            cloudRate: Number(getDataAttribute(block, 'cloud-rate', 1)),
                            gridMargin: Number(getDataAttribute(block, 'grid-margin', 24)),
                            imageSize: getDataAttribute(block, 'image-size', 'tainacan-medium'),
                            linkTermFacetsToTermPage: getDataAttribute(block, 'link-term-facets-to-term-page', true) == 'true',
                            maxFacetsNumber: Number(getDataAttribute(block, 'max-facets-number', 12)),
                            maxColumnsCount: Number(getDataAttribute(block, 'max-columns-count', 5)),
                            tainacanApiRoot: getDataAttribute(block, 'tainacan-api-root'),
                            tainacanSiteUrl: getDataAttribute(block, 'tainacan-site-url')
                        });
                    },
                    mounted() {
                        block.classList.add('has-mounted');
                    }
                });
                
                VueFacetsList.use(ThumbnailHelperPlugin);
                VueFacetsList.component('facets-list-theme-unit', FacetsListThemeUnit);

                VueFacetsList.mount('#' + block.id);
            });
        }
    }

    // This is rendered on the theme side.
    renderTainacanFacetsListBlock();

    // Also if a theme or plugin requested a reset...
    document.addEventListener("DOMContentLoaded", () => {
        renderTainacanFacetsListBlock();
    });

}