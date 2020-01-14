import axios from '../../../axios';

export const createEditGroup = ({commit}, parameters) => {
    let object = parameters.object;
    let collectionID = parameters.collectionID;

    let bulkEditParams = null;

    if(object.constructor.name === 'Array'){
        bulkEditParams = {
            items_ids: object,
        };
        if (parameters.order != undefined && parameters.order != undefined)
            bulkEditParams['options'] = {
                order: parameters.order,
                orderby: parameters.orderBy
            };

    } else if(object.constructor.name === 'Object'){
        bulkEditParams = {
            use_query: object,
        };
    }

    return new Promise ((resolve, reject) => {
        axios.tainacan.post(`/collection/${collectionID}/bulk-edit`, bulkEditParams)
            .then(response => {
                commit('setGroup', response.data);
                resolve(response.data);
            })
            .catch(error => {
                console.error(error);
                reject(error);
            });
        });
};

export const fetchGroup = ({commit}, { collectionId, groupId }) => {

    return new Promise ((resolve, reject) => {
        axios.tainacan.get(`/collection/${collectionId}/bulk-edit/${groupId}`)
            .then(response => {
                commit('setGroup', response.data);
                resolve(response.data);
            })
            .catch(error => {
                console.log(error);
                reject(error);
            });
    });
};

export const fetchSequenceGroup = ({commit}, { collectionId, groupId }) => {

    return new Promise ((resolve, reject) => {
        axios.tainacan.get(`/collection/${collectionId}/sequence-edit/${groupId}`)
            .then(response => {
                commit('setGroup', response.data);
                resolve(response.data);
            })
            .catch(error => {
                console.log(error);
                reject(error);
            });
    });
};

export const setValueInBulk = ({commit}, parameters) => {
    let groupID = parameters.groupID;
    let collectionID = parameters.collectionID;

    /**
     * @var bodyParams { metadatum_id, new_value } Object
     * */
    let bodyParams = parameters.bodyParams;

    return axios.tainacan.post(`/collection/${collectionID}/bulk-edit/${groupID}/set`, bodyParams)
        .then(response => {
            commit('setLastUpdated');
            return response;
        })
        .catch(error => {
            console.error(error);
        });
};

export const addValueInBulk = ({commit}, parameters) => {
    let groupID = parameters.groupID;
    let collectionID = parameters.collectionID;

    /**
     * @var bodyParams { metadatum_id, new_value } Object
     * */
    let bodyParams = parameters.bodyParams;

    return axios.tainacan.post(`/collection/${collectionID}/bulk-edit/${groupID}/add`, bodyParams)
        .then(response => {
            return response;
        })
        .catch(error => {
            console.error(error);
        });
};

export const removeValueInBulk = ({commit}, parameters) => {
    let groupID = parameters.groupID;
    let collectionID = parameters.collectionID;

    /**
     * @var bodyParams { metadatum_id, new_value } Object
     * */
    let bodyParams = parameters.bodyParams;

    return axios.tainacan.post(`/collection/${collectionID}/bulk-edit/${groupID}/remove`, bodyParams)
        .catch(error => {
            console.error(error);
        });
};

export const clearValuesInBulk = ({commit}, parameters) => {
    let groupID = parameters.groupID;
    let collectionID = parameters.collectionID;

    /**
     * @var bodyParams { metadatum_id, new_value } Object
     * */
    let bodyParams = parameters.bodyParams;

    return axios.tainacan.post(`/collection/${collectionID}/bulk-edit/${groupID}/clear`, bodyParams)
        .catch(error => {
            console.error(error);
        });
};

export const replaceValueInBulk = ({commit}, parameters) => {
    let groupID = parameters.groupID;
    let collectionID = parameters.collectionID;

    /**
     * @var bodyParams { metadatum_id, old_value, new_value } Object
     * */
    let bodyParams = parameters.bodyParams;

    return axios.tainacan.post(`/collection/${collectionID}/bulk-edit/${groupID}/replace`, bodyParams)
        .then(response => {
            return response;
        })
        .catch(error => {
            console.error(error);
        });
};

export const setStatusInBulk = ({commit}, parameters) => {
    let groupID = parameters.groupID;
    let collectionID = parameters.collectionID;

    /**
     * The new status value (draft, publish or private)
     * @var bodyParams String
     * */
    let bodyParams = parameters.bodyParams;

    return axios.tainacan.post(`/collection/${collectionID}/bulk-edit/${groupID}/set_status`, bodyParams)
        .then(response => {
            commit('setLastUpdated');
            return response;
        })
        .catch(error => {
            console.error(error);
        });
};

export const setCommentStatusInBulk = ({commit}, parameters) => {
    let groupID = parameters.groupID;
    let collectionID = parameters.collectionID;

    /**
     * The new status value (draft, publish or private)
     * @var bodyParams String
     * */
    let bodyParams = parameters.bodyParams;

    return axios.tainacan.post(`/collection/${collectionID}/bulk-edit/${groupID}/set_comment_status`, bodyParams)
        .then(response => {
            commit('setLastUpdated');
            return response;
        })
        .catch(error => {
            console.error(error);
        });
};

export const trashItemsInBulk = ({commit}, parameters) => {
    let groupID = parameters.groupID;
    let collectionID = parameters.collectionID;

    return axios.tainacan.post(`/collection/${collectionID}/bulk-edit/${groupID}/trash`)
        .then(response => {
            commit('setLastUpdated');
            return response;
        })
        .catch(error => {
            console.log(error);
        });
};

export const untrashItemsInBulk = ({commit}, parameters) => {
    let groupID = parameters.groupID;
    let collectionID = parameters.collectionID;

    return axios.tainacan.post(`/collection/${collectionID}/bulk-edit/${groupID}/untrash`)
        .then(response => {
            return response;
        })
        .catch(error => {
            console.log(error);
        });
};

export const deleteItemsInBulk = ({commit}, parameters) => {
    let groupID = parameters.groupID;
    let collectionID = parameters.collectionID;

    return axios.tainacan.post(`/collection/${collectionID}/bulk-edit/${groupID}/delete_items`)
        .then(response => {
            commit('setLastUpdated');
            return response;
        })
        .catch(error => {
            console.log(error);
        });
};

// SEQUENCE EDIT SPECIFIC
export const fetchItemIdInSequence = ({commit}, { collectionId, sequenceId, itemPosition }) => {

    return new Promise ((resolve, reject) => {
        axios.tainacan.get(`/collection/${collectionId}/sequence-edit/${sequenceId}/${itemPosition}`)
            .then(response => {
                commit('setItemIdInSequence', response.data);
                resolve(response.data);
            })
            .catch(error => {
                console.log(error);
                reject(error);
            });
    });
};

export const createSequenceEditGroup = ({commit}, parameters) => {
    let object = parameters.object;
    let collectionID = parameters.collectionID;

    let sequenceEditParams = null;

    if(object.constructor.name === 'Array'){
        sequenceEditParams = {
            items_ids: object,
        };

    } else if(object.constructor.name === 'Object'){
        sequenceEditParams = {
            use_query: object,
        };
    }

    return new Promise ((resolve, reject) => {
        axios.tainacan.post(`/collection/${collectionID}/sequence-edit`, sequenceEditParams)
            .then(response => {
                commit('setGroup', response.data);
                resolve(response.data);
            })
            .catch(error => {
                console.error(error);
                reject(error);
            });
        });
};
