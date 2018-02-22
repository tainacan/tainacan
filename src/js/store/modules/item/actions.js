import axios from '../../../axios/axios';

// Actions related to Item's field
export const sendField = ( { commit }, { item_id, field_id, values }) => {
   return new Promise( (resolve, reject) => {
       axios.post('/item/'+item_id+'/metadata/'+field_id, {
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
        axios.patch(`/item/${item_id}/metadata/${field_id}`, {
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
        axios.get('/item/'+item_id+'/metadata')
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
        axios.get('/items/'+item_id)
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

export const sendItem = ( { commit }, { collection_id, status }) => {
    return new Promise(( resolve, reject ) => {
        axios.post('/collection/'+ collection_id + '/items/', {
            status: status
        })
            .then( res => {
                commit('setItem', { collection_id: collection_id, status: status });
                resolve( res.data );
            })
            .catch(error => {
                reject( error.response );
            });
    });
 };
 
 
export const updateItem = ({ commit }, { item_id, status }) => {
    return new Promise((resolve, reject) => {
        axios.patch('/items/' + item_id, {
            status: status 
        }).then( res => {
            commit('setItem', { id: item_id, status: status });
            resolve( res.data );
        }).catch( error => { 
            reject( error.response );
        });

    }); 
};
