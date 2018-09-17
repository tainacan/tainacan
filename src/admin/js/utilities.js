import qs from 'qs';
import axios from 'axios';

const wpApi = axios.create({
    baseURL: tainacan_plugin.root_wp_api
});

wpApi.defaults.headers.common['X-WP-Nonce'] = tainacan_plugin.nonce;

// CONSOLE PLUGIN - Allows custom use of console functions and avoids eslint warnings.
export const ConsolePlugin = {};
ConsolePlugin.install = function (Vue, options = { visual: false }) {
    
    Vue.prototype.$console = {
        log(something) {
            if (options.visual) {
                Vue.prototype.$snackbar.open({
                    message: something,
                    type: 'is-secondary',
                    position: 'is-bottom-right',
                    indefinite: true,
                    queue: false
                });
            } else {
                console.log(something);
            }
        },
        info(someInfo) {
            if (options.visual) {
                Vue.prototype.$snackbar.open({
                    message: someInfo,
                    type: 'is-primary',
                    position: 'is-bottom-right',
                    duration: 5000,
                    queue: false
                });
            } else { 
                console.info(someInfo);
            }
        },
        error(someError) {
            if (options.visual) {
                Vue.prototype.$snackbar.open({
                    message: someError,
                    type: 'is-danger',
                    position: 'is-bottom-right',
                    indefinite: true,
                    queue: false
                });
            } else { 
                console.error(someError);
            }
        }
    }
}

// I18N PLUGIN - Allows access to Wordpress translation file.
export const I18NPlugin = {};
I18NPlugin.install = function (Vue, options = {}) {
    
    Vue.prototype.$i18n = {
        get(key) {
            let string = tainacan_plugin.i18n[key];
            return (string != undefined && string != null && string != '' ) ? string : "Invalid i18n key: " + tainacan_plugin.i18n[key];
        },
        getFrom(entity, key) {
            if (entity == 'taxonomies') // Temporary hack, while we decide this terminology...
                entity = 'taxonomies'
            if (tainacan_plugin.i18n['entities_labels'][entity] == undefined)
                return 'Invalid i18n entity: ' + entity;
            let string = tainacan_plugin.i18n['entities_labels'][entity][key];
            return (string != undefined && string != null && string != '' ) ? string : "Invalid i18n key: " + key;
        },
        getHelperTitle(entity, key) {
            if (entity == 'taxonomies') // Temporary hack, while we decide this terminology...
                entity = 'taxonomies'
            if (tainacan_plugin.i18n['helpers_label'][entity] == undefined)
                return 'Invalid i18n entity: ' + entity;
            if (tainacan_plugin.i18n['helpers_label'][entity][key] == undefined)
                return 'Invalid i18n key: ' + key;
            let string = tainacan_plugin.i18n['helpers_label'][entity][key].title;
            return (string != undefined && string != null && string != '' ) ? string : "Invalid i18n helper object.";
        },
        getHelperMessage(entity, key) {
            if (entity == 'taxonomies') // Temporary hack, while we decide this terminology...
                entity = 'taxonomies'
            if (tainacan_plugin.i18n['helpers_label'][entity] == undefined)
                return 'Invalid i18n entity: ' + entity;
            if (tainacan_plugin.i18n['helpers_label'][entity][key] == undefined)
                return 'Invalid i18n key: ' + key;
            let string = tainacan_plugin.i18n['helpers_label'][entity][key].description;
            return (string != undefined && string != null && string != '' ) ? string : "Invalid i18n helper object. ";
        },
    }

}

// USER PREFERENCES - Used to save key-value information for user settings of plugin
export const UserPrefsPlugin = {};
UserPrefsPlugin.install = function (Vue, options = {}) {

    Vue.prototype.$userPrefs = {
        
        tainacanPrefs: {
            'items_per_page': 12,
            'collections_per_page': 12,
            'taxonomies_per_page': 12,
            'events_per_page': 12,
            'order': 'DESC',
            'order_by': { 
                slug: 'creation_date',
                name: 'Creation Date'
            },
            'view_mode': undefined,
            'admin_view_mode': 'cards',
            'fetch_only': {
                0: 'thumbnail',
                1: 'creation_date',
                2: 'author_name',
                meta: []
            }
        },
        init() {
            if (tainacan_plugin.user_prefs == undefined || tainacan_plugin.user_prefs == '') {
                let data = {'meta': {'tainacan_prefs': JSON.stringify(this.tainacanPrefs)} };
                wpApi.post('/users/me/', qs.stringify(data))
                .then( updatedRes => {
                    let prefs = JSON.parse(updatedRes.data.meta['tainacan_prefs']);
                    this.tainacanPrefs = prefs;
                });
            } else {
                let prefs = JSON.parse(tainacan_plugin.user_prefs);
                this.tainacanPrefs = prefs;
            }
        },
        get(key) {
            return this.tainacanPrefs[key];
        },
        set(key, value) {
            this.tainacanPrefs[key] = value;
            let data = {'meta': {'tainacan_prefs': JSON.stringify(this.tainacanPrefs)} };
            return new Promise(( resolve, reject ) => {
                wpApi.post('/users/me/', qs.stringify(data))
                .then( res => {
                    let prefs = JSON.parse(res.data.meta['tainacan_prefs']);
                    this.tainacanPrefs[key] = prefs[key];
                    if (prefs[key]) { 
                        resolve( prefs[key] );  
                    } else {
                        reject('Key ' + key + ' does not exists in user preference.');
                    }
                })
                .catch(error => {
                    reject( error );
                });
            }); 
        },
        clean() {
            let data = {'meta': {'tainacan_prefs': ''} };
            wpApi.post('/users/me/', qs.stringify(data))
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
        getCollectionMetadataPath(collectionId) {
            return '/collections/'+ collectionId + '/metadata/';
        },
        getCollectionFiltersPath(collectionId) {
            return '/collections/'+ collectionId + '/filters/';
        },
        getCollectionEventsPath(collectionId) {
            return '/collections/'+ collectionId + '/events/';
        },
        getItemsPath(query) {
            return '/items/?' + qs.stringify(query);
        },
        getPath(query) {
            return '/taxonomies/?' + qs.stringify(query);
        },
        getTaxonomyTermsPath(taxonomyId, query) {
            return '/taxonomyId/' + taxonomyId + '/terms/?' + qs.stringify(query);
        },
        getFiltersPath(query) {
            return '/filters/?' + qs.stringify(query);
        },
        getMetadataPath(query) {
            return '/metadata/?' + qs.stringify(query);
        },
        getEventsPath(query) {
            return '/events/?' + qs.stringify(query);
        },
        getAvailableImportersPath() {
            return '/importers/new';
        },
        getProcessesPage(highlightedProcess) {
            if (highlightedProcess)
                return '/events?tab=processes&highlight=' + highlightedProcess;
            else 
                return '/events?tab=processes';
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
        getTaxonomyPath(id) {
            return '/taxonomies/' + id;
        },
        getTermPath(taxonomyId, termId) {
            return '/taxonomies/' + taxonomyId + '/terms/' + termId;
        },
        getEventPath(id) {
            return '/events/' + id;
        },
        getImporterPath(importerType, sessionId) {
            return '/importers/' + importerType + '/' + sessionId;
        },
        // New
        getNewCollectionPath() {
            return '/collections/new';
        },
        getNewMappedCollectionPath(mapperSlug) {
            return '/collections/new/' + mapperSlug;
        },
        getNewItemPath(collectionId) {
            return '/collections/' + collectionId + '/items/new';
        },
        getNewCollectionMetadatumPath(collectionId) {
            return '/collections/' + collectionId + '/metadata/';
        },
        getNewMetadatumPath() {
            return '/metadata';
        },
        getNewCollectionFilterPath(collectionId) {
            return '/collections/' + collectionId + '/filters/';
        },
        getNewFilterPath() {
            return '/filters/new';
        },
        getNewTaxonomyPath() {
            return '/taxonomies/new';
        },
        getNewTermPath(taxonomyId) {
            return '/taxonomies/' + taxonomyId + '/terms/new';
        },
        getNewEventPath() {
            return '/events/new';
        },
        // Edit
        getCollectionEditPath(id) {
            return '/collections/' + id + '/settings';
        },
        getItemEditPath(collectionId, itemId) {
            return '/collections/' + collectionId + '/items/' + itemId + '/edit';
        },
        getFilterEditPath(id) {
            return '/filters/' + id + '/edit';
        },
        getTaxonomyEditPath(id) {
            return '/taxonomies/' + id + '/edit';
        },
        getTermEditPath(taxonomyId, termId) {
            return '/taxonomies/' + taxonomyId + '/terms/' + termId + '/edit';
        },
        getEventEditPath(id) {
            return '/events/' + id + '/edit';
        },
        getImporterEditionPath(importerType) {
            return '/importers/' + importerType;
        },   
        getImporterMappingPath(importerType, sessionId, collectionId) {
            return '/importers/' + importerType + '/' + sessionId + '/mapping/' +  collectionId;
        },
    }
}

// USER CAPABILITIES PLUGIN - Allows easy checking of user capabilities.
export const UserCapabilitiesPlugin = {};
UserCapabilitiesPlugin.install = function (Vue, options = {}) {
    
    Vue.prototype.$userCaps = {
        hasCapability(key) {
            for (let i = 0; i < tainacan_plugin.user_caps.length; i++)
                if (tainacan_plugin.user_caps[i] == key)
                    return true;
            return false;
        }
    }
}