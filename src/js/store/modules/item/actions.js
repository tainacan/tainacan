import axios from '../../../axios/axios';

export const sendMetadata = ( { commit }, { item_id, metadata_id, values }) => {
    axios.post('/metadata/item/'+item_id, {
        metadata_id: metadata_id,
        values: values
    })
        .then( res => {
            console.log( res );
            commit('setSingleMetadata', { item_id: item_id, metadata_id: metadata_id, values: values });
        })
        .catch(error => console.log( error ));
}


export const setSingleMetadata = ({ commit }, { item_id, metadata_id, values }) => {
    commit('setSingleMetadata', { item_id: item_id, metadata_id: metadata_id, values: values });
};