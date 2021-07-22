import * as conditioner from 'conditioner-core/conditioner-core.esm';

// Checks if document is loaded
const performWhenDocumentIsLoaded = callback => {
    if (/comp|inter|loaded/.test(document.readyState))
        cb();
    else
        document.addEventListener('DOMContentLoaded', callback, false);
}

// Adds data-module to blocks inserted previous to Tainacan 0.18.4
const addDataModuleToOldBlocks = () => {
    const tainacanBlocks = [
        'faceted-search',
        'item-submission-form',
        'items-list',
        'collections-list',
        'terms-list',
        'search-bar',
        'facets-list',
        'dynamic-items-list',
        'carousel-items-list',
        'carousel-terms-list',
        'carousel-collections-list'
    ];
    // Looks for Tainacan Blocks based on their classes.
    tainacanBlocks.forEach((tainacanBlockSlug) => {
        let existingBlocksOnPage = document.getElementsByClassName(`wp-block-tainacan-${tainacanBlockSlug}`);
        [...existingBlocksOnPage].forEach((blockElement) => {
            if ( !blockElement.getAttribute('data-module') )
                blockElement.setAttribute('data-module', tainacanBlockSlug);
        });
    });

    // Extra case for the items list, as the theme wrapper does not uses gutenberg classes
    let existingItemListOnPage = document.getElementById('tainacan-items-page');
    if ( existingItemListOnPage && !existingItemListOnPage.getAttribute('data-module') )
        existingItemListOnPage.setAttribute('data-module', 'faceted-search');
}

performWhenDocumentIsLoaded(() => {

    addDataModuleToOldBlocks();
    
    conditioner.addPlugin({
        // converts module aliases to paths
        moduleSetName: name => `./${name}/theme.js`,

        // use default exports as constructor
        moduleGetConstructor: module => module.default,

        // override the import (this makes webpack bundle all the dynamically included files as well)
        // https://webpack.js.org/api/module-methods/#import-
        // - set to "eager" to create a single chunk for all modules
        // - set to "lazy" to create a separate chunk for each module
        moduleImport: name => import(
            /* webpackMode: "lazy" */
            /* webpackInclude: /theme\.js$/ */
            /* webpackChunkName: "tainacan-chunks-" */
            `${name}`)
    });

    // lets go!
    conditioner.hydrate(document.documentElement);
});