import axios from '../../../axios/axios';
import qs from 'qs';

export const fetchItems = ({ rootGetters, dispatch, commit }, { collectionId, isOnTheme, termId, taxonomy }) => {
    commit('cleanItems');

    return new Promise ((resolve, reject) => {
        
        // Adds queries for filtering
        let postQueries = JSON.parse(JSON.stringify(rootGetters['search/getPostQuery']));

        // Sets a flag to inform components that an empty sate is or not due to filtering
        let hasFiltered = false;
        let advancedSearchResults = false;

        if ( (postQueries.metaquery != undefined &&
             (Object.keys(postQueries.metaquery).length > 0 ||
              postQueries.metaquery.length > 0)) || (postQueries.taxquery != undefined &&
                (Object.keys(postQueries.taxquery).length > 0 ||
                 postQueries.taxquery.length > 0)) ) {
            
            hasFiltered = true;

            if(postQueries.advancedSearch){
                advancedSearchResults = postQueries.advancedSearch;
            }
        }
        
        // Sets term query in case it's on a term items page
        if (termId != undefined && taxonomy != undefined) {

            if (postQueries.taxquery == undefined) 
                postQueries.taxquery = [];

            postQueries.taxquery.push({
                taxonomy: taxonomy,
                terms:[ termId ],
                compare: 'IN'
            });
        }
        
        let query = qs.stringify(postQueries);

        // Guarantees at least empty fetch_only are passed in case none is found
        if (qs.stringify(postQueries.fetch_only) == ''){
            dispatch('search/add_fetchonly', {}, { root: true });
        }
                
        if (qs.stringify(postQueries.fetch_only['meta']) == ''){
            dispatch('search/add_fetchonly_meta', 0, { root: true });
        }

        // Differentiates between repository level and collection level queries
        let endpoint = '/collection/'+ collectionId +'/items?';

        if (collectionId == undefined){
            endpoint = '/items?';
        }

        if (!isOnTheme){
            if (postQueries.view_mode != undefined)
                postQueries.view_mode = null;
                
            endpoint = endpoint + 'context=edit&'
        } else {
            if (postQueries.admin_view_mode != undefined)
                postQueries.admin_view_mode = null;
        } 
        
        axios.tainacan.get(endpoint+query)
        .then(res => {
            
            let items = res.data;
            let viewModeObject = tainacan_plugin.registered_view_modes[postQueries.view_mode];

            if (isOnTheme && viewModeObject != undefined && viewModeObject.type == 'template') {
                commit('setItemsListTemplate', items);
                resolve({'itemsListTemplate': items, 'total': res.headers['x-wp-total'], hasFiltered: hasFiltered, advancedSearchResults:  advancedSearchResults});
            } else {
                commit('setItems', items);
                resolve({
                    'items': items, 
                    'total': res.headers['x-wp-total'],
                    totalPages: res.headers['x-wp-totalpages'], 
                    hasFiltered: hasFiltered, 
                    advancedSearchResults: advancedSearchResults });
            }
            dispatch('search/setTotalItems', res.headers['x-wp-total'], { root: true } );
            dispatch('search/setTotalPages', res.headers['x-wp-totalpages'], { root: true } );
        })
        .catch(error => reject(error));
        
    });
    
};

export const deleteItem = ({ commit }, { itemId, isPermanently }) => {
    return new Promise((resolve, reject) => {
        let endpoint = '/items/' + itemId;
        
        if (isPermanently){
            endpoint = endpoint + '?permanently=1';
        } else {
            endpoint = endpoint + '?permanently=0';
        }

        axios.tainacan.delete(endpoint)
        .then( res => {
            commit('deleteItem', { id: itemId });
            resolve( res );
        }).catch((error) => { 
            reject( error );
        });

    });
};
 
export const fetchCollections = ({commit} , { page, collectionsPerPage, status }) => {
    return new Promise((resolve, reject) => {
        let endpoint = '/collections?paged='+page+'&perpage='+collectionsPerPage+'&context=edit';

        if (status != '' && status != undefined)
            endpoint = endpoint + '&status=' + status;
            
        axios.tainacan.get(endpoint)
        .then(res => {
            let collections = res.data;
            commit('setCollections', collections);
            resolve({'collections': collections, 'total': res.headers['x-wp-total'] });
        }) 
        .catch(error => {
            console.log(error);
            reject(error);
        });
    });
};

export const cleanCollections = ({ commit }) => {
    commit('cleanCollections');
};

export const fetchCollection = ({ commit }, id) => {
    commit('cleanCollection');
    return new Promise((resolve, reject) =>{ 
        axios.tainacan.get('/collections/' + id + '?context=edit')
        .then(res => {
            let collection = res.data;
            commit('setCollection', collection);
            resolve( res.data );
        })
        .catch(error => {
            reject(error);
        })
    });
};

export const fetchCollectionName = ({ commit }, id) => {
    //commit('cleanCollectionName');
    return new Promise((resolve, reject) =>{ 
        axios.tainacan.get('/collections/' + id + '?fetch_only=name')
        .then(res => {
            let collectionName = res.data;
            commit('setCollectionName', collectionName.name);
            resolve( collectionName.name );
        })
        .catch(error => {
            reject(error);
        })
    });
};

export const fetchCollectionCommentStatus = ({ commit }, id) => {
    return new Promise((resolve, reject) =>{ 
        axios.tainacan.get('/collections/' + id + '?fetch_only=comment_status')
        .then(res => {
            let collectionCommentStatus = res.data;
            commit('setCollectionCommentStatus', collectionCommentStatus.comment_status);
            resolve( collectionCommentStatus.comment_status );
        })
        .catch(error => {
            reject(error);
        })
    });
};

export const fetchCollectionAllowComments = ({ commit }, id) => {
    return new Promise((resolve, reject) =>{ 
        axios.tainacan.get('/collections/' + id + '?fetch_only=allow_comments')
        .then(res => {
            let collectionAllowComments = res.data;
            commit('setCollectionAllowComments', collectionAllowComments.allow_comments);
            resolve( collectionAllowComments.allow_comments );
        })
        .catch(error => {
            reject(error);
        })
    });
};

export const fetchCollectionNameAndURL = ({ commit }, id) => {
    //commit('cleanCollectionName');
    return new Promise((resolve, reject) =>{ 
        axios.tainacan.get('/collections/' + id + '?fetch_only[0]=name&fetch_only[1]=url')
        .then(res => {
            let collection = res.data;
            commit('setCollectionName', collection.name);
            commit('setCollectionURL', collection.url);
            resolve( collection );
        })
        .catch(error => {
            reject(error);
        })
    });
};

export const deleteCollection = ({ commit }, { collectionId, isPermanently }) => {
    return new Promise((resolve, reject) => { 
        let endpoint = '/collections/' + collectionId;
        if (isPermanently){
            endpoint = endpoint +'?permanently=1';
        } else {
            endpoint = endpoint +'?permanently=0';
        }

        axios.tainacan.delete(endpoint)
        .then(res => {
            let collection = res.data;
            commit('deleteCollection', collection);
            resolve( res.data );
        })
        .catch(error => {
            reject(error);
        })
    });
};

export const updateCollection = ({ commit }, { 
        collection_id, 
        collection
    }) => {
    return new Promise((resolve, reject) => {
        axios.tainacan.patch('/collections/' + collection_id, collection).then( res => {
            commit('setCollection', collection);
            commit('setCollectionName', res.data.name);
            commit('setCollectionURL', res.data.url);
            resolve( res.data );
        }).catch( error => { 
            reject({ error_message: error['response']['data'].error_message, errors: error['response']['data'].errors });
        });

    });
};

export const sendCollection = ( { commit }, collection) => {
    return new Promise(( resolve, reject ) => {
        let param = collection;
        param[tainacan_plugin.exposer_mapper_param] = collection.mapper;
        axios.tainacan.post('/collections/', param)
            .then( res => {
                let collection = res.data;
                commit('setCollection', collection);
                resolve( collection );
            })
            .catch(error => {
                reject( error.response );
            });
    });
 };

export const setItems = ({ commit }, items ) => {
    commit('setItems', items);
};


// Attachments =======================================
export const sendAttachment = ( { commit }, { collection_id, file }) => {
    return new Promise(( resolve, reject ) => {
        axios.wp.post('/media/?post=' + collection_id, file, {
            headers: { 'Content-Disposition': 'attachment; filename=' + file.name },
            onUploadProgress: progressEvent => {
                console.log(progressEvent.loaded + '/' + progressEvent.total);
            }
        })
            .then( res => {
                let attachment = res.data;
                commit('setSingleAttachment', attachment);
                resolve( attachment );
            })
            .catch(error => {
                reject( error.response );
            });
    });
};

export const fetchAttachments = ({ commit }, collection_id) => {
    commit('cleanAttachments')
    return new Promise((resolve, reject) => {
        axios.wp.get('/media/?post=' + collection_id)
        .then(res => {
            let attachments = res.data;
            commit('setAttachments', attachments);
            resolve( attachments );
        })
        .catch(error => {
            reject( error );
        });
    });
};

export const updateThumbnail = ({ commit }, { collectionId, thumbnailId }) => {
    return new Promise((resolve, reject) => {
        axios.tainacan.patch('/collections/' + collectionId, {
            _thumbnail_id: thumbnailId
        }).then( res => {
            let collection = res.data
            commit('setCollection', collection);
            resolve( collection );
        }).catch( error => { 
            reject({ error_message: error['response']['data'].error_message, errors: error['response']['data'].errors });
        });

    }); 
};

export const updateHeaderImage = ({ commit }, { collectionId, headerImageId }) => {
    return new Promise((resolve, reject) => {
        axios.tainacan.patch('/collections/' + collectionId, {
            header_image_id: headerImageId + ''
        }).then( res => {
            let collection = res.data
            commit('setCollection', collection);
            resolve( collection );
        }).catch( error => { 
            reject({ error_message: error['response']['data'].error_message, errors: error['response']['data'].errors });
        });

    }); 
};

// Collection Cover Page
export const fetchPages = ({ commit }, search ) => {
    return new Promise((resolve, reject) => {
        axios.wp.get('/pages?search=' + search)
        .then(res => {
            let pages = res.data;
            resolve( pages );
        })
        .catch(error => {
            reject( error );
        });
    });
};

export const fetchPage = ({ commit }, pageId ) => {
    return new Promise((resolve, reject) => {
        axios.wp.get('/pages/' + pageId)
        .then(res => {
            let page = res.data;
            resolve( page );
        })
        .catch(error => {
            reject( error );
        });
    });
};

// Users for moderators configuration
export const fetchUsers = ({ commit }, { search, exceptions }) => {

    let endpoint = '/users?search=' + search;

    if (exceptions.length > 0) 
        endpoint += '&exclude=' + exceptions.toString();

    return new Promise((resolve, reject) => {
        axios.wp.get(endpoint)
        .then(res => {
            let users = res.data;
            resolve( users );
        })
        .catch(error => {
            reject( error );
        });
    });
};

// Fetch Collections for choosing Parent Collection
export const fetchCollectionsForParent = ({ commit }) => {
    return new Promise((resolve, reject) => { 
        axios.tainacan.get('/collections/?fetch_only[0]=name&fetch_only[1]=id')
        .then(res => {
            let collections = res.data;
            resolve( collections );
        })
        .catch(error => {
            reject(error);
        })
    });
};