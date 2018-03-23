import axios from '../../../axios/axios';

export const fetchItems = ({ commit, dispatch }, { collectionId, page, itemsPerPage }) => {
    return new Promise ((resolve, reject) => {
        axios.tainacan.get('/collection/'+collectionId+'/items?paged='+page+'&perpage='+itemsPerPage)
        .then(res => {
            let items = res.data;
            commit('setItems', items);
            dispatch('search/setTotalItems', res.headers['x-wp-total'], { root: true } );
            resolve({'items': items, 'total': res.headers['x-wp-total'] });
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

export const fetchCollections = ({commit} , { page, collectionsPerPage }) => {
    return new Promise((resolve, reject) => {
        axios.tainacan.get('/collections?paged='+page+'&perpage='+collectionsPerPage)
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
    return new Promise((resolve, reject) =>{ 
        axios.tainacan.get('/collections/' + id)
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

export const updateCollection = ({ commit }, { collection_id, name, description, slug, status }) => {
    return new Promise((resolve, reject) => {
        axios.tainacan.patch('/collections/' + collection_id, {
            name: name,
            description: description,
            status: status,
            slug: slug
        }).then( res => {
            commit('setCollection', { id: collection_id, name: name, description: description, slug: slug, status: status });
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
            featured_img_id: thumbnailId 
        }).then( res => {
            let collection = res.data
            commit('setCollection', collection);
            resolve( collection );
        }).catch( error => { 
            reject({ error_message: error['response']['data'].error_message, errors: error['response']['data'].errors });
        });

    }); 
};