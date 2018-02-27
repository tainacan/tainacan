// I18N PLUGIN - Allows access to Wordpress translation file.
export const I18NPlugin = {};
I18NPlugin.install = function (Vue, options = {}) {
    
    Vue.prototype.$i18n = {
        get(key) {
            let string = wp_settings.i18n[key];
            return (string != undefined && string != null && string != '' ) ? string : "ERROR: Invalid i18n key!";
        }
    }

}

// ROUTER HELPER PLUGIN - Allows easy access to URL paths for entities
export const RouterHelperPlugin = {};
RouterHelperPlugin.install = function (Vue, options = {}) {
    
    Vue.prototype.$routerHelper = {
        // Lists

        // Singles
        getCollectionPath(id) {
            return '/collections/' + id;
        },
        getItemPath(collectionId, itemId) {
            return '/collections/' + collectionId + '/items/' + itemId;
        },
        getItemPath(collectionId, itemId) {
            return '/collections/' + collectionId + '/items/' + itemId;
        },
        getFilterPath(id) {
            return '/filters/' + id;
        },
        getCategoryPath(id) {
            return '/categories/' + id;
        },
        getTermPath(categoryId, termId) {
            return '/categories/' + categoryId + '/terms/' + termId;
        },
        getEventPath(id) {
            return '/events/' + id;
        }
    }

}