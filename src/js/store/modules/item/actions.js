import axios from '../../../axios/axios';

export const sendMetadata = ( { commit }, { item_id, metadata_id, values }) => {
   return new Promise( ( resolve, reject ) => {
       axios.post('/item/'+item_id+'/metadata/', {
           metadata_id: metadata_id,
           values: values
       })
           .then( res => {
               console.log( 'success',res.data );
               commit('setSingleMetadata', { item_id: item_id, metadata_id: metadata_id, values: values });
               commit('removeError', { metadata_id: metadata_id });
               resolve( res.data );
           })
           .catch(error => {
               console.log( 'error',error.response );
               commit('setSingleMetadata', { item_id: item_id, metadata_id: metadata_id, values: values });
               commit('setError', { item_id: item_id, metadata_id: metadata_id, value: values, error: error.response.data.errors  });
               reject( error.response );
           });
   });
};


export const updateMetadata = ({ commit }, { item_id, metadata_id, values }) => {
    commit('setSingleMetadata', { item_id: item_id, metadata_id: metadata_id, values: values });
};

export const sendItem= ( { commit }, { collection_id, title, description, status }) => {
    return new Promise(( resolve, reject ) => {
        axios.post('/collection/'+ collection_id + '/items/', {
            title: title,
            description: description,
            status: status
        })
            .then( res => {
                console.log( 'success',res.data );
                commit('setSingleItem', { collection_id: collection_id, title: title, description: description, status: status });
                commit('removeError', { collection_id });
                resolve( res.data );
            })
            .catch(error => {
                console.log( 'error',error.response );
                commit('setSingleItem', { collection_id: collection_id, title: title, description: description, status: status });
                commit('setError', { collection_id: collection_id, title: title, description: description, status: status, error: error.response.data.errors  });
                reject( error.response );
            });
    });
 };
 
 
 export const updateItem = ({ commit }, { item_id, metadata_id, values }) => {
     commit('setSingleItem', { collection_id: collection_id, title: title, description: description, status: status });
 };