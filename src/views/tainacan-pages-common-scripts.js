import * as conditioner from 'conditioner-core/conditioner-core.esm';
const { __ } = wp.i18n;

// Updates Webpack public path based on plugin folder URL, using variable obtained from server side.
__webpack_public_path__ = tainacan_plugin.plugin_dir_url + 'assets/js/';

// Checks if document is loaded
const performWhenDocumentIsLoaded = callback => {
    if (/comp|inter|loaded/.test(document.readyState))
        callback();
    else
        document.addEventListener('DOMContentLoaded', callback, false);
}

performWhenDocumentIsLoaded(() => {
    
    conditioner.addPlugin({
        // converts module aliases to paths
        moduleSetName: name => `./${name}/js/${name}-main.js`,

        // use default exports as constructor
        moduleGetConstructor: module => module.default,

        // override the import (this makes webpack bundle all the dynamically included files as well)
        // https://webpack.js.org/api/module-methods/#import-
        // - set to "eager" to create a single chunk for all modules
        // - set to "lazy" to create a separate chunk for each module
        moduleImport: name => import(
            /* webpackMode: "lazy" */
            /* webpackInclude: /main\.js$/ */
            /* webpackChunkName: "tainacan-chunks-[request]" */
            `${name}`
        )
            .catch((error) => {
                let moduleElements = document.querySelectorAll('[data-module]');
                moduleElements.forEach((moduleElement) => {
                    moduleElement.innerHTML = `<div style="padding: 2rem; margin: 1rem auto; text-align: center;">
                        <p style="font-size: 1.25rem;"><strong>` + __( 'An error ocurred while loading Tainacan. Please reload your page (CTRL+SHIFT+R).', 'tainacan') + `</strong></p>
                        <p style="font-size: 1rem">` + __( 'Clearing the cache may help. It is possible that the browser or server are retaining old information that are preventing the page to be loading correctly.', 'tainacan') + `</p>
                    </div>`;
                });
                console.warn(__( 'An error ocurred while loading Tainacan. Please reload your page (CTRL+SHIFT+R).', 'tainacan'));
                console.warn(__( 'Clearing the cache may help. It is possible that the browser or server are retaining old information that are preventing the page to be loading correctly.', 'tainacan'));
                console.error(error);
            })
    });

    // lets go!
    conditioner.hydrate(document.documentElement);
});