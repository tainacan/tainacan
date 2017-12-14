import axios from '../../../axios/axios';

export const sendMetadata = ( { commit }, { item_id, metadata_id, values }) => {
    axios.post('/metadata/item/'+item_id, {
        metadata_id: metadata_id,
        values: values
    })
        .then( res => {
            console.log( 'success',res.data );
            commit('setSingleMetadata', { item_id: item_id, metadata_id: metadata_id, values: values });
            commit('removeError', { metadata_id: metadata_id });
        })
        .catch(error => {
            console.log( 'error',error.response.data[1] );
            commit('setSingleMetadata', { item_id: item_id, metadata_id: metadata_id, values: values });
            commit('setError', { item_id: item_id, metadata_id: metadata_id, value: values, error: error.response.data[0].errors  });
        });
};


export const updateMetadata = ({ commit }, { item_id, metadata_id, values }) => {
    commit('setSingleMetadata', { item_id: item_id, metadata_id: metadata_id, values: values });
};