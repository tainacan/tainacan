import axios from '../../../axios/axios';

// Actions related to Item's field
export const sendField = ( { commit }, { item_id, field_id, values }) => {
   return new Promise( (resolve, reject) => {
        axios.tainacan.post('/item/'+item_id+'/metadata/'+field_id, {
            values: values
        })
        .then( res => {
            commit('setSingleMetadata', { item_id: item_id, field_id: field_id, values: values });
            resolve( res.data );
        })
        .catch(error => {
            reject( error);
        });
   });
};


export const updateMetadata = ({ commit }, { item_id, field_id, values }) => {
    return new Promise((resolve, reject) => {
        axios.tainacan.patch(`/item/${item_id}/metadata/${field_id}`, {
            values: values,
        })
            .then( res => {
                let field = res.data;
                commit('setSingleField', field);
                resolve(field)
            })
            .catch( error => {
                reject(error.response.data.errors);
            })
    });
};

export const fetchFields = ({ commit }, item_id) => {
    return new Promise((resolve, reject) => {
        axios.tainacan.get('/item/'+item_id+'/metadata')
        .then(res => {
            let items = res.data;
            commit('setFields', items);
            resolve( res.data );
        })
        .catch(error => {
            reject( error );
        });
    });
};

// Actions directly related to Item
export const fetchItem = ({ commit }, item_id) => {
    return new Promise((resolve, reject) => {
        axios.tainacan.get('/items/'+item_id)
        .then(res => {
            let item = res.data;
            commit('setItem', item);
            resolve( res.data );
        })
        .catch(error => {
            reject( error );
        });
    });
};

export const fetchItemTitle = ({ commit }, id) => {
    return new Promise((resolve, reject) =>{ 
        axios.tainacan.get('/items/' + id + '?fetch_only=title')
        .then(res => {
            let itemTitle = res.data;
            commit('setItemTitle', itemTitle.title);
            resolve( itemTitle.title );
        })
        .catch(error => {
            reject(error);
        })
    });
}

export const sendItem = ( { commit }, { collection_id, status }) => {
    return new Promise(( resolve, reject ) => {
        axios.tainacan.post('/collection/'+ collection_id + '/items/', {
            status: status
        })
            .then( res => {
                commit('setItem', { collection_id: collection_id, status: status });
                resolve( res.data );
            })
            .catch(error => {
                reject({ error_message: error['response']['data'].error_message, errors: error['response']['data'].errors });
            });
    });
};
 
export const updateItem = ({ commit }, { item_id, status }) => {
    return new Promise((resolve, reject) => {
        axios.tainacan.patch('/items/' + item_id, {
            status: status 
        }).then( res => {
            commit('setItem', { id: item_id, status: status });
            resolve( res.data );
        }).catch( error => { 
            reject({ error_message: error['response']['data'].error_message, errors: error['response']['data'].errors });
        });

    }); 
};

export const sendAttachment = ( { commit }, { item_id, url_path }) => {
    return new Promise(( resolve, reject ) => {
        axios.wp.post('/media/', {
            post: item_id,
        }, url_path)
            .then( res => {
                commit('setItem', { collection_id: collection_id, status: status });
                resolve( res.data );
            })
            .catch(error => {
                reject( error.response );
            });
    });
};