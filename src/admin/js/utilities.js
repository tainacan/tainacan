import qs from 'qs';
import axios from 'axios';

const wpApi = axios.create({
    baseURL: tainacan_plugin.wp_api_url
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
};

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

        /**
         * Parsed strings created with variables according to WordPress Logic.
         * Check https://developer.wordpress.org/themes/functionality/internationalization/#variables
         * An example: ('This sentence has %s letters', [nLetters]) 
         * or ('This one has %1$s letters and %2$s words', [nLetters, nWords]). 
         */
        getWithVariables(key, variables) { // TRY WITH regex: \%((\d)\$)*s 
            let rawString = tainacan_plugin.i18n[key];
            if (rawString != undefined && rawString != null && rawString != '' ) {
                // let splits = rawString.match(/\%((\d)\$)*s*/gm); // An array with all the %s, %1$s, %2$s, etc
                // let parsedString = '';
                // for (let i = 0; i < splits.length; i++) {
                //     parsedString += rawString.split(splits[i]).join(variables[i]);
                // }
                // return parsedString;

                const regex = /\%(\d\$)*s/m;
                for (let variable of variables)
                    rawString = rawString.replace(regex, variable);
                
                return rawString;
            } else {
                "Invalid i18n key: " + tainacan_plugin.i18n[key];
            }
        }
    }

};

// USER PREFERENCES - Used to save key-value information for user settings of plugin
export const UserPrefsPlugin = {};
UserPrefsPlugin.install = function (Vue, options = {}) {

    Vue.prototype.$userPrefs = {
        
        tainacanPrefs: {
            'items_per_page': 12,
            'collections_per_page': 12,
            'taxonomies_per_page': 12,
            'activities_per_page': 12,
            'order': 'DESC',
            'order_by': { 
                slug: 'creation_date',
                name: 'Creation Date'
            },
            'view_mode': undefined,
            'admin_view_mode': 'cards',
            'fetch_only': 'thumbnail,creation_date,author_name',
            'fetch_only_meta': '',
            'taxonomies_order': 'asc',
            'taxonomies_order_by': 'title',
            'collections_order': 'desc',
            'collections_order_by': 'date'
        },
        init() {
            if (tainacan_plugin.user_prefs == undefined || tainacan_plugin.user_prefs == '') {
                let data = {'meta': {'tainacan_prefs': JSON.stringify(this.tainacanPrefs)} };

                wpApi.post('/users/me/', qs.stringify(data))
                    .then( updatedRes => {
                        let prefs = JSON.parse(updatedRes.data.meta['tainacan_prefs']);
                        this.tainacanPrefs = prefs;
                    })
                    .catch( () => console.log("Request to /users/me failed. Maybe you're not logged in.") );
            } else {
                let prefs = JSON.parse(tainacan_plugin.user_prefs);
                this.tainacanPrefs = prefs;
            }
        },
        get(key) {
            return this.tainacanPrefs[key] ? this.tainacanPrefs[key] : undefined;
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
                            this.tainacanPrefs[key] = value;
                        }
                    })
                    .catch( () => console.log("Request to /users/me failed. Maybe you're not logged in.") );
            }); 
        },
        clean() {
            let data = {'meta': {'tainacan_prefs': ''} };
            wpApi.post('/users/me/', qs.stringify(data))
        }
    }

};

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
        getCollectionSequenceEditPath(collectionId, sequenceId, itemPosition) {
            return '/collections/'+ collectionId + '/sequence/' + sequenceId + '/' + itemPosition;
        },
        getCollectionMetadataPath(collectionId) {
            return '/collections/'+ collectionId + '/metadata/';
        },
        getCollectionFiltersPath(collectionId) {
            return '/collections/'+ collectionId + '/filters/';
        },
        getCollectionActivitiesPath(collectionId) {
            return '/collections/'+ collectionId + '/activities/';
        },
        getItemsPath(query) {
            return '/items/?' + qs.stringify(query);
        },
        getTaxonomiesPath() {
            return '/taxonomies/'
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
        getActivitiesPath(query) {
            return '/activities/?' + qs.stringify(query);
        },
        getAvailableImportersPath() {
            return '/importers';
        },
        getProcessesPage(highlightedProcess) {
            if (highlightedProcess)
                return '/activities?tab=processes&highlight=' + highlightedProcess;
            else 
                return '/activities?tab=processes';
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
        getImporterPath(importerType, sessionId) {
            return '/importers/' + importerType + '/' + sessionId;
        },
        getCollectionActivityPath(collectionId, activityId) {
            return '/collections/' + collectionId + '/activities/' + activityId;
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
        getNewItemBulkAddPath(collectionId) {
            return '/collections/' + collectionId + '/bulk-add';
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
        getImporterEditionPath(importerType) {
            return '/importers/' + importerType;
        },   
        getImporterMappingPath(importerType, sessionId, collectionId) {
            return '/importers/' + importerType + '/' + sessionId + '/mapping/' +  collectionId;
        },
        getItemMetadataBulkAddPath(collectionId, groupId) {
            return '/collections/' + collectionId + '/bulk-add/' + groupId;
        },
        getExporterEditionPath(exporterType) {
            return '/exporters/' + exporterType;
        },
        getAvailableExportersPath(){
            return '/exporters';
        },
    }
};

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
};


// STATUS ICONS PLUGIN - Sets icon for status option
export const StatusHelperPlugin = {};
StatusHelperPlugin.install = function (Vue, options = {}) {
    
    Vue.prototype.$statusHelper = {
        statuses: [
            { name: tainacan_plugin.i18n['status_publish'], slug: 'publish' },
            // { name: tainacan_plugin.i18n['status_private'], slug: 'private' },
            { name: tainacan_plugin.i18n['status_draft'], slug: 'draft' },
            { name: tainacan_plugin.i18n['status_trash'], slug: 'trash' }
        ],
        getIcon(status) {
            switch (status) {
                case 'publish': return 'tainacan-icon-public';
                case 'private': return 'tainacan-icon-private';
                case 'draft': return 'tainacan-icon-draft';
                case 'trash': return 'tainacan-icon-delete';
                default: '';
            }
        },
        hasIcon(status) {
            return ['publish', 'private', 'draft', 'trash'].includes(status);
        },
        getStatuses() {
            return  this.statuses;
        },
        loadStatuses() {
            wpApi.get('/statuses/')
                    .then( res => {
                        let loadedStatus = res.data;
                        this.statuses = [];

                        if (loadedStatus['publish'] != undefined)
                            this.statuses.push(loadedStatus['publish']);
                        
                        if (loadedStatus['private'] != undefined)
                            this.statuses.push(loadedStatus['private']);
                        
                        this.statuses.concat(Object.values(loadedStatus).filter((status) => {
                            return !['publish','private', 'draft', 'trash'].includes(status.slug); 
                        }));

                        // We always show draft and trash
                        this.statuses.push({
                            name: tainacan_plugin.i18n['status_draft'],
                            slug: 'draft'
                        });
                        this.statuses.push({
                            name: tainacan_plugin.i18n['status_trash'],
                            slug: 'trash'}
                        );
                    })
                    .catch(error => {
                        console.error( error );
                    });
        }
    }

};