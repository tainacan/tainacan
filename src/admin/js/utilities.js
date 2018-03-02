import qs from 'qs';

// I18N PLUGIN - Allows access to Wordpress translation file.
export const I18NPlugin = {};
I18NPlugin.install = function (Vue, options = {}) {
    
    Vue.prototype.$i18n = {
        get(key) {
            let string = tainacan_plugin.i18n[key];
            return (string != undefined && string != null && string != '' ) ? string : "Invalid i18n key: " + tainacan_plugin.i18n[key];
        }
    }

}

// ROUTER HELPER PLUGIN - Allows easy access to URL paths for entities
export const RouterHelperPlugin = {};
RouterHelperPlugin.install = function (Vue, options = {}) {
    
    Vue.prototype.$routerHelper = {
        // Lists
        getCollectionsPath(query) {
            return '/collections/?' + qs.stringify(query);
        },
        getCollectionItemsPath(collectionId, query) {
            return '/collections/'+ collectionId + '/items/?' + qs.stringify(query);
        },
        getCollectionFieldsPath(collectionId) {
            return '/collections/'+ collectionId + '/fields/';
        },
        getCollectionFiltersPath(collectionId) {
            return '/collections/'+ collectionId + '/filters/';
        },
        getItemsPath(query) {
            return '/items/?' + qs.stringify(query);
        },
        getCategoriesPath(query) {
            return '/items/?' + qs.stringify(query);
        },
        getCategoryTermsPath(categoryId, query) {
            return '/categoryId/' + categoryId + 'terms/?' + qs.stringify(query);
        },
        getFiltersPath(query) {
            return '/filters/?' + qs.stringify(query);
        },
        getFieldsPath(query) {
            return '/fields/?' + qs.stringify(query);
        },
        getEventsPath(query) {
            return '/events/?' + qs.stringify(query);
        },
        // Singles
        getCollectionPath(id) {
            return '/collections/' + id;
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
        },
        // New
        getNewCollectionPath() {
            return '/collections/new';
        },
        getNewItemPath(collectionId) {
            return '/collections/' + collectionId + '/items/new';
        },
        getNewFilterPath() {
            return '/filters/new';
        },
        getNewCategoryPath() {
            return '/categories/new';
        },
        getNewTermPath() {
            return '/categories/' + categoryId + '/terms/new';
        },
        getNewEventPath() {
            return '/events/new';
        },
        // Edit
        getCollectionEditPath(id) {
            return '/collections/' + id + '/edit';
        },
        getItemEditPath(collectionId, itemId) {
            return '/collections/' + collectionId + '/items/' + itemId + '/edit';
        },
        getFilterEditPath(id) {
            return '/filters/' + id + '/edit';
        },
        getCategoryEditPath(id) {
            return '/categories/' + id + '/edit';
        },
        getTermEditPath(categoryId, termId) {
            return '/categories/' + categoryId + '/terms/' + termId + '/edit';
        },
        getEventEditPath(id) {
            return '/events/' + id + '/edit';
        }
        
    }

}