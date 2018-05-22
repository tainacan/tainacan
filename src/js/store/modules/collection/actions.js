import axios from '../../../axios/axios';
import qs from 'qs';

export const fetchItems = ({ rootGetters, dispatch, commit }, collectionId) => {
    commit('cleanItems');
    return new Promise ((resolve, reject) => {
        
        // Adds queries for filtering
        let postQueries = rootGetters['search/getPostQuery'];
        
        // Sets a flag to inform components that an empty sate is or not due to filtering
        let hasFiltered = false;
        if (postQueries.metaquery != undefined && postQueries.metaquery.length > 0)
            hasFiltered = true;

        // Differentiates between repository level and collection level queries
        let endpoint = '/collection/'+collectionId+'/items?context=edit&'

        if (collectionId == undefined)
            endpoint = '/items?context=edit&'

        axios.tainacan.get(endpoint + qs.stringify(postQueries) )
        .then(res => {
            let items = res.data;
            commit('setItems', items );
            dispatch('search/setTotalItems', res.headers['x-wp-total'], { root: true } );
            resolve({'items': items, 'total': res.headers['x-wp-total'], hasFiltered: hasFiltered});
        })
        .catch(error => reject(error));
    });
}

export const deleteItem = ({ commit }, item_id ) => {
    return new Promise((resolve, reject) => {
        axios.tainacan.delete('/items/' + item_id)
        .then( res => {
            commit('deleteItem', { id: item_id });
            resolve( res );
        }).catch((error) => { 
            reject( error );
        });

    });
};

export const fetchCollections = ({commit} , { page, collectionsPerPage, status }) => {
    commit('cleanCollections');
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
}

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
}

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
}

export const deleteCollection = ({ commit }, id) => {
    return new Promise((resolve, reject) =>{ 
        axios.tainacan.delete('/collections/' + id)
        .then(res => {
            let collection = res.data;
            commit('deleteCollection', collection);
            resolve( res.data );
        })
        .catch(error => {
            reject(error);
        })
    });
}

export const updateCollection = ({ commit }, { collection_id, name, description, slug, status, enable_cover_page, cover_page_id, moderators_ids, parent }) => {
    return new Promise((resolve, reject) => {
        axios.tainacan.patch('/collections/' + collection_id, {
            name: name,
            description: description,
            status: status,
            slug: slug,
            cover_page_id: "" + cover_page_id,
            enable_cover_page: enable_cover_page,
            moderators_ids: moderators_ids,
            parent: parent
        }).then( res => {
            commit('setCollection', { 
                id: collection_id, 
                name: name, 
                description: description, 
                slug: slug, 
                status: status, 
                enable_cover_page: enable_cover_page, 
                cover_page_id: cover_page_id,
                moderators_ids: moderators_ids,
                parent: parent
            });
            resolve( res.data );
        }).catch( error => { 
            reject({ error_message: error['response']['data'].error_message, errors: error['response']['data'].errors });
        });

    });
}

export const sendCollection = ( { commit }, { name, description, status }) => {
    return new Promise(( resolve, reject ) => {
        axios.tainacan.post('/collections/', {
            name: name,
            description: description,
            status: status
        })
            .then( res => {
                commit('setCollection', { name: name, description: description, status: status });
                resolve( res.data );
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
    return new Promise((resolve, reject) =>{ 
        axios.tainacan.get('/collections/?fetch_only[0]=name&fetch_only[1]=id')
        .then(res => {
            let collections = res.data;
            resolve( collections );
        })
        .catch(error => {
            reject(error);
        })
    });
}