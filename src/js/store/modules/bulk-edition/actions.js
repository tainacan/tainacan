import axios from '../../../axios/axios';

export const createEditGroup = ({commit}, parameters) => {
    let object = parameters.object;
    let collectionID = parameters.collectionID;

    let bulkEditParams = null;

    if(object.constructor.name === 'Array'){
        bulkEditParams = {
            items_ids: object,
        };
    } else if(object.constructor.name === 'Object'){
        bulkEditParams = {
            use_query: object,
        };
    }

    return axios.tainacan.post(`/collection/${collectionID}/bulk-edit`, bulkEditParams)
        .then(response => {
            commit('setGroup', response.data);
        })
        .catch(error => {
            console.error(error);
        })
};

export const setValueInBulk = ({commit}, parameters) => {
    let groupID = parameters.groupID;
    let collectionID = parameters.collectionID;
    let bodyParams = parameters.bodyParams;

    return axios.tainacan.post(`/collection/${collectionID}/bulk-edit/${groupID}/set`, bodyParams)
        .then(response => {
            commit('setActionResult', response.data)
        })
        .catch(error => {
            console.error(error)
        });
};

export const addValueInBulk = ({commit}, parameters) => {

};