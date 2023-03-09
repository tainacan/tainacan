import * as conditioner from 'conditioner-core/conditioner-core.esm';
const { __ } = wp.i18n;

// Updates Webpack public path based on plugin folder URL, using variable obtained from server side.
__webpack_public_path__ = tainacan_plugin.plugin_dir_url + 'assets/js/';

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
        'items-list',
        'collections-list',
        'terms-list',
        'search-bar',
        'facets-list',
        'dynamic-items-list',
        'carousel-items-list',
        'carousel-terms-list',
        'related-items-list',
        'carousel-collections-list',
        'item-gallery'
    ];
    
    // Looks for Tainacan Blocks based on their classes.
    tainacanBlocks.forEach((tainacanBlockSlug) => {
        let existingBlocksOnPage = document.getElementsByClassName(`wp-block-tainacan-${tainacanBlockSlug}`);
        [...existingBlocksOnPage].forEach((blockElement) => {
            if ( !blockElement.getAttribute('data-module') )
                blockElement.setAttribute('data-module', tainacanBlockSlug);
        });
    });

    // Extra case for the items list, item gallery and item submission, as their
    // theme wrapper does not uses gutenberg classes, but the div ID
    let existingItemListOnPage = document.getElementById('tainacan-items-page');
    if ( existingItemListOnPage && !existingItemListOnPage.getAttribute('data-module') )
        existingItemListOnPage.setAttribute('data-module', 'faceted-search');

    let existingItemSubmissionFormOnPage = document.getElementById('tainacan-item-submission-form');
    if ( existingItemSubmissionFormOnPage && !existingItemSubmissionFormOnPage.getAttribute('data-module') )
        existingItemSubmissionFormOnPage.setAttribute('data-module', 'item-submission-form');

}

performWhenDocumentIsLoaded(() => {

    addDataModuleToOldBlocks();
    
    conditioner.addPlugin({
        // converts module aliases to paths
        moduleSetName: name => `./blocks/${name}/theme.js`,

        // use default exports as constructor
        moduleGetConstructor: module => module.default,

        // override the import (this makes webpack bundle all the dynamically included files as well)
        // https://webpack.js.org/api/module-methods/#import-
        // - set to "eager" to create a single chunk for all modules
        // - set to "lazy" to create a separate chunk for each module
        moduleImport: name => import(
            /* webpackMode: "lazy" */
            /* webpackInclude: /theme\.js$/ */
            /* webpackChunkName: "tainacan-chunks-[request]" */
            `${name}`
        )
            .catch((error) => {
                let moduleElements = document.querySelectorAll('[data-module]');
                moduleElements.forEach((moduleElement) => {
                    moduleElement.innerHTML = `<p style="font-size: 1rem;"><strong>` + __( 'An error ocurred while loading this Tainacan component. Please reload your page (CTRL+SHIFT+R).', 'tainacan') + `</strong></p>`;
                });
                console.warn( __( 'An error ocurred while loading some Tainacan components. Please reload your page (CTRL+SHIFT+R).', 'tainacan') );
                console.warn( __( 'Clearing the cache may help. It is possible that the browser or server are retaining old information that are preventing Tainacan components to be loading correctly.', 'tainacan') );
                console.error(error);
            })
    });

    // lets go!
    conditioner.hydrate(document.documentElement);
});