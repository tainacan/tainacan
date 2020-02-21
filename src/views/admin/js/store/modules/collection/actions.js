import axios from '../../../axios';
import qs from 'qs';

// THE ITEMS SEARCH
export const fetchItems = ({ rootGetters, dispatch, commit }, { collectionId, isOnTheme, termId, taxonomy }) => {
    commit('cleanItems');

    const source = axios.CancelToken.source();

    return new Object({ 
        request: new Promise ((resolve, reject) => {

            // Sets term query in case it's on a term items page
            if (termId != undefined && taxonomy != undefined) {

                dispatch('search/add_taxquery', {
                    taxonomy: taxonomy,
                    terms:[ termId ],
                    compare: 'IN'
                }, { root: true });
            }
                
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
            
            let query = qs.stringify(postQueries);

            // Guarantees at least empty fetch_only are passed in case none is found
            if (postQueries.fetch_only == ''){
                dispatch('search/add_fetch_only', '', { root: true });
            }
                    
            if (postQueries.fetch_only_meta == ''){
                dispatch('search/add_fetch_only_meta', '', { root: true });
            }

            // Differentiates between repository level and collection level queries
            let endpoint = '/collection/'+ collectionId +'/items?';

            if (collectionId == undefined || collectionId == '' || collectionId == null){
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
            axios.tainacan.get(endpoint+query, {
                cancelToken: source.token
            })
                .then(res => {
                    
                    let items = res.data.items;
                    let viewModeObject = tainacan_plugin.registered_view_modes[postQueries.view_mode];
                    
                    if (isOnTheme && viewModeObject != undefined && viewModeObject.type == 'template') {
                        commit('setItemsListTemplate', res.data.template);
                        resolve({
                            'itemsListTemplate': res.data.template, 
                            'total': res.headers['x-wp-total'], 
                            hasFiltered: hasFiltered, 
                            advancedSearchResults:  advancedSearchResults,
                            itemsPerPage: res.headers['x-wp-itemperpage']});
                    } else {
                        commit('setItems', items);
                        resolve({
                            'items': items, 
                            'total': res.headers['x-wp-total'],
                            totalPages: res.headers['x-wp-totalpages'], 
                            hasFiltered: hasFiltered, 
                            advancedSearchResults: advancedSearchResults ,
                            itemsPerPage: res.headers['x-wp-itemperpage'] });                            
                    }
                    dispatch('search/setTotalItems', res.headers['x-wp-total'], { root: true } );
                    dispatch('search/setTotalPages', res.headers['x-wp-totalpages'], { root: true } );
                    dispatch('search/setItemsPerPage', res.headers['x-wp-itemsperpage'], { root: true } );
                    
                    if (res.data.filters && Object.values(res.data.filters) && Object.values(res.data.filters).length > 0)
                        dispatch('search/setFacets', res.data.filters, { root: true } );
                    else
                        dispatch('search/setFacets', {}, { root: true } );

                })
                .catch((thrown) => {
                    if (axios.isCancel(thrown)) {
                        console.log('Request canceled: ', thrown.message);
                    } else {
                        reject(thrown);
                    }
                }); 
        }),
        source: source
    })
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
 
export const fetchCollections = ({commit} , { page, collectionsPerPage, status, contextEdit, order, orderby, search }) => {
    
    return new Promise((resolve, reject) => {
        let endpoint = '/collections?paged='+page+'&perpage='+collectionsPerPage;

        if (contextEdit)
            endpoint = endpoint  + '&context=edit';

        if (status != '' && status != undefined)
            endpoint = endpoint + '&status=' + status;
                    
        if (order != undefined && order != '' && orderby != undefined && orderby != '')
            endpoint = endpoint + '&order=' + order + '&orderby=' + orderby;
        
        if (search != undefined && search != '')
            endpoint = endpoint + '&search=' + search;

        axios.tainacan.get(endpoint)
            .then(res => {
                let collections = res.data;
                commit('setCollections', collections);

                commit('setRepositoryTotalCollections', {
                    draft: res.headers['x-tainacan-total-collections-draft'],
                    trash: res.headers['x-tainacan-total-collections-trash'],
                    publish: res.headers['x-tainacan-total-collections-publish'],
                    private: res.headers['x-tainacan-total-collections-private'],
                });

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

export const cleanItems = ({ commit }) => {
    commit('cleanItems');
};

export const cleanCollection = ({ commit }) => {
    commit('cleanCollection');
};

export const fetchCollection = ({ commit, }, id) => {
    return new Promise((resolve, reject) => { 
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

export const fetchCollectionBasics = ({ commit }, {collectionId, isContextEdit }) => {
    return new Promise((resolve, reject) => { 
        let endpoint = '/collections/' + collectionId + '?fetch_only=name,url,allow_comments';
        if (isContextEdit)
            endpoint += '&context=edit';
        
        axios.tainacan.get(endpoint)
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

export const fetchCollectionForExposer = ({ commit }, collectionId) => {
    return new Promise((resolve, reject) => { 
        let endpoint = '/collections/' + collectionId + '?fetch_only=name,url';
        axios.tainacan.get(endpoint)
        .then(res => {
            resolve( res.data );
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
        axios.tainacan.patch('/collections/' + collection_id + '?context=edit', collection).then( res => {
            commit('setCollection', collection);
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
        axios.tainacan.post('/collections/?context=edit', param)
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
        axios.tainacan.patch('/collections/' + collectionId + '?context=edit', {
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
        axios.tainacan.patch('/collections/' + collectionId + '?context=edit', {
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

// Fetch Collections for listing repository filters, parent collection selection, importer destiny...
export const fetchAllCollectionNames = ({ commit }, collectionsIds) => {

    let endpoint = '/collections/?context=edit&nopaging=1&fetch_only=name,id';

    if (collectionsIds != undefined && collectionsIds.length > 0) {
        const postin = { 'postin': collectionsIds };
        endpoint += '&' + qs.stringify(postin);
    }

    const source = axios.CancelToken.source();

    return new Object({ 
        request: new Promise((resolve, reject) => {
            axios.tainacan.get(endpoint, { cancelToken: source.token })
            .then(res => {
                const collections = res.data;
                commit('setCollections', collections);
                resolve( collections );
            })
            .catch((error) => {
                if (axios.isCancel(error)) {
                    console.log('Request canceled: ', error.message);
                } else {
                    reject(error);
                }
            });
        }),
        source: source
    })
};

// Send Files to Item Bulk Addition
export const sendFile = ( { commit }, file ) => {
    return new Promise(( resolve, reject ) => {
        axios.wp.post('/media/', file, {
            headers: { 'Content-Disposition': 'attachment; filename=' + file.name },
        })
            .then( res => {
                let file = res.data;
                commit('setSingleFile', file);
                resolve( file );
            })
            .catch(error => {
                reject( error.response );
            });
    });
};

export const cleanFiles = ({ commit }) => {
    commit('cleanFiles');
};