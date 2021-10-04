import * as conditioner from 'conditioner-core/conditioner-core.esm';

// Updates Webpack public path based on plugin folder URL, using variable obtained from server side.
__webpack_public_path__ = tainacan_plugin.plugin_dir_url + 'assets/js/';

// Checks if document is loaded
const performWhenDocumentIsLoaded = callback => {
    if (/comp|inter|loaded/.test(document.readyState))
        cb();
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
            /* webpackChunkName: "tainacan-chunks-" */
            `${name}`)
    });

    // lets go!
    conditioner.hydrate(document.documentElement);
});