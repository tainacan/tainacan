import axios from '../../../axios';

/**
 * Dispatches `bulkedition/createEditGroup`.
 * @param {Object} parameters - Group creation parameters.
 * @param {*} parameters.object - Item id array or query object used to build the group.
 * @param {*} parameters.collectionId - Collection identifier.
 * @param {*} [parameters.order] - Optional ordering direction.
 * @param {*} [parameters.orderBy] - Optional ordering field.
 * @returns {*} Action result.
 */
export const createEditGroup = ({commit}, parameters) => {
    let object = parameters.object;
    let collectionId = parameters.collectionId;

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
        axios.tainacanApi.post(`/collection/${collectionId}/bulk-edit`, bulkEditParams)
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

/**
 * Dispatches `bulkedition/fetchGroup`.
 * @param {Object} param1 - Group identifiers.
 * @param {*} param1.collectionId - Collection identifier.
 * @param {*} param1.groupId - Bulk edit group identifier.
 * @returns {*} Action result.
 */
export const fetchGroup = ({commit}, { collectionId, groupId }) => {

    return new Promise ((resolve, reject) => {
        axios.tainacanApi.get(`/collection/${collectionId}/bulk-edit/${groupId}`)
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

/**
 * Dispatches `bulkedition/fetchSequenceGroup`.
 * @param {Object} param1 - Sequence group identifiers.
 * @param {*} param1.collectionId - Collection identifier.
 * @param {*} param1.groupId - Sequence edit group identifier.
 * @returns {*} Action result.
 */
export const fetchSequenceGroup = ({commit}, { collectionId, groupId }) => {

    return new Promise ((resolve, reject) => {
        axios.tainacanApi.get(`/collection/${collectionId}/sequence-edit/${groupId}`)
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

/**
 * Dispatches `bulkedition/setValueInBulk`.
 * @param {Object} parameters - Bulk operation parameters.
 * @param {*} parameters.collectionId - Collection identifier.
 * @param {*} parameters.groupId - Bulk edit group identifier.
 * @param {*} parameters.bodyParams - Request body payload for the operation.
 * @returns {*} Action result.
 */
export const setValueInBulk = ({commit}, parameters) => {
    let groupId = parameters.groupId;
    let collectionId = parameters.collectionId;
    let bodyParams = parameters.bodyParams;

    return axios.tainacanApi.post(`/collection/${collectionId}/bulk-edit/${groupId}/set`, bodyParams)
        .then(response => {
            commit('setLastUpdated');
            return response;
        })
        .catch(error => {
            console.error(error);
        });
};

/**
 * Dispatches `bulkedition/addValueInBulk`.
 * @param {Object} parameters - Bulk operation parameters.
 * @param {*} parameters.collectionId - Collection identifier.
 * @param {*} parameters.groupId - Bulk edit group identifier.
 * @param {*} parameters.bodyParams - Request body payload for the operation.
 * @returns {*} Action result.
 */
export const addValueInBulk = ({commit}, parameters) => {
    let groupId = parameters.groupId;
    let collectionId = parameters.collectionId;
    let bodyParams = parameters.bodyParams;

    return axios.tainacanApi.post(`/collection/${collectionId}/bulk-edit/${groupId}/add`, bodyParams)
        .then(response => {
            return response;
        })
        .catch(error => {
            console.error(error);
        });
};

/**
 * Dispatches `bulkedition/removeValueInBulk`.
 * @param {Object} parameters - Bulk operation parameters.
 * @param {*} parameters.collectionId - Collection identifier.
 * @param {*} parameters.groupId - Bulk edit group identifier.
 * @param {*} parameters.bodyParams - Request body payload for the operation.
 * @returns {*} Action result.
 */
export const removeValueInBulk = ({commit}, parameters) => {
    let groupId = parameters.groupId;
    let collectionId = parameters.collectionId;
    let bodyParams = parameters.bodyParams;

    return axios.tainacanApi.post(`/collection/${collectionId}/bulk-edit/${groupId}/remove`, bodyParams)
        .catch(error => {
            console.error(error);
        });
};

/**
 * Dispatches `bulkedition/clearValuesInBulk`.
 * @param {Object} parameters - Bulk operation parameters.
 * @param {*} parameters.collectionId - Collection identifier.
 * @param {*} parameters.groupId - Bulk edit group identifier.
 * @param {*} parameters.bodyParams - Request body payload for the operation.
 * @returns {*} Action result.
 */
export const clearValuesInBulk = ({commit}, parameters) => {
    let groupId = parameters.groupId;
    let collectionId = parameters.collectionId;
    let bodyParams = parameters.bodyParams;

    return axios.tainacanApi.post(`/collection/${collectionId}/bulk-edit/${groupId}/clear`, bodyParams)
        .catch(error => {
            console.error(error);
        });
};

/**
 * Dispatches `bulkedition/replaceValueInBulk`.
 * @param {Object} parameters - Bulk operation parameters.
 * @param {*} parameters.collectionId - Collection identifier.
 * @param {*} parameters.groupId - Bulk edit group identifier.
 * @param {*} parameters.bodyParams - Request body payload for the operation.
 * @returns {*} Action result.
 */
export const replaceValueInBulk = ({commit}, parameters) => {
    let groupId = parameters.groupId;
    let collectionId = parameters.collectionId;
    let bodyParams = parameters.bodyParams;

    return axios.tainacanApi.post(`/collection/${collectionId}/bulk-edit/${groupId}/replace`, bodyParams)
        .then(response => {
            return response;
        })
        .catch(error => {
            console.error(error);
        });
};

/**
 * Dispatches `bulkedition/setStatusInBulk`.
 * @param {Object} parameters - Bulk operation parameters.
 * @param {*} parameters.collectionId - Collection identifier.
 * @param {*} parameters.groupId - Bulk edit group identifier.
 * @param {*} parameters.bodyParams - Status payload sent to the API.
 * @returns {*} Action result.
 */
export const setStatusInBulk = ({commit}, parameters) => {
    let groupId = parameters.groupId;
    let collectionId = parameters.collectionId;
    let bodyParams = parameters.bodyParams;

    return axios.tainacanApi.post(`/collection/${collectionId}/bulk-edit/${groupId}/set_status`, bodyParams)
        .then(response => {
            commit('setLastUpdated');
            return response;
        })
        .catch(error => {
            console.error(error);
        });
};

/**
 * Dispatches `bulkedition/setCommentStatusInBulk`.
 * @param {Object} parameters - Bulk operation parameters.
 * @param {*} parameters.collectionId - Collection identifier.
 * @param {*} parameters.groupId - Bulk edit group identifier.
 * @param {*} parameters.bodyParams - Comment status payload sent to the API.
 * @returns {*} Action result.
 */
export const setCommentStatusInBulk = ({commit}, parameters) => {
    let groupId = parameters.groupId;
    let collectionId = parameters.collectionId;
    let bodyParams = parameters.bodyParams;

    return axios.tainacanApi.post(`/collection/${collectionId}/bulk-edit/${groupId}/set_comment_status`, bodyParams)
        .then(response => {
            commit('setLastUpdated');
            return response;
        })
        .catch(error => {
            console.error(error);
        });
};

/**
 * Dispatches `bulkedition/setAuthorIdInBulk`.
 * @param {Object} parameters - Bulk operation parameters.
 * @param {*} parameters.collectionId - Collection identifier.
 * @param {*} parameters.groupId - Bulk edit group identifier.
 * @param {*} parameters.bodyParams - Author payload sent to the API.
 * @returns {*} Action result.
 */
export const setAuthorIdInBulk = ({commit}, parameters) => {
    let groupId = parameters.groupId;
    let collectionId = parameters.collectionId;
    let bodyParams = parameters.bodyParams;

    return axios.tainacanApi.post(`/collection/${collectionId}/bulk-edit/${groupId}/set_author_id`, bodyParams)
        .then(response => {
            commit('setLastUpdated');
            return response;
        })
        .catch(error => {
            console.error(error);
        });
};

/**
 * Dispatches `bulkedition/trashItemsInBulk`.
 * @param {Object} parameters - Bulk operation parameters.
 * @param {*} parameters.collectionId - Collection identifier.
 * @param {*} parameters.groupId - Bulk edit group identifier.
 * @returns {*} Action result.
 */
export const trashItemsInBulk = ({commit}, parameters) => {
    let groupId = parameters.groupId;
    let collectionId = parameters.collectionId;

    return axios.tainacanApi.post(`/collection/${collectionId}/bulk-edit/${groupId}/trash`)
        .then(response => {
            commit('setLastUpdated');
            return response;
        })
        .catch(error => {
            console.log(error);
        });
};

/**
 * Dispatches `bulkedition/untrashItemsInBulk`.
 * @param {Object} parameters - Bulk operation parameters.
 * @param {*} parameters.collectionId - Collection identifier.
 * @param {*} parameters.groupId - Bulk edit group identifier.
 * @returns {*} Action result.
 */
export const untrashItemsInBulk = ({commit}, parameters) => {
    let groupId = parameters.groupId;
    let collectionId = parameters.collectionId;

    return axios.tainacanApi.post(`/collection/${collectionId}/bulk-edit/${groupId}/untrash`)
        .then(response => {
            return response;
        })
        .catch(error => {
            console.log(error);
        });
};

/**
 * Dispatches `bulkedition/deleteItemsInBulk`.
 * @param {Object} parameters - Bulk operation parameters.
 * @param {*} parameters.collectionId - Collection identifier.
 * @param {*} parameters.groupId - Bulk edit group identifier.
 * @returns {*} Action result.
 */
export const deleteItemsInBulk = ({commit}, parameters) => {
    let groupId = parameters.groupId;
    let collectionId = parameters.collectionId;

    return axios.tainacanApi.post(`/collection/${collectionId}/bulk-edit/${groupId}/delete_items`)
        .then(response => {
            commit('setLastUpdated');
            return response;
        })
        .catch(error => {
            console.log(error);
        });
};


/**
 * Dispatches `bulkedition/copyValuesInBulk`.
 * @param {Object} parameters - Bulk operation parameters.
 * @param {*} parameters.collectionId - Collection identifier.
 * @param {*} parameters.groupId - Bulk edit group identifier.
 * @param {*} parameters.bodyParams - Request body payload for the operation.
 * @returns {*} Action result.
 */
export const copyValuesInBulk = ({commit}, parameters) => {
    let groupId = parameters.groupId;
    let collectionId = parameters.collectionId;
    let bodyParams = parameters.bodyParams;

    return axios.tainacanApi.post(`/collection/${collectionId}/bulk-edit/${groupId}/copy_value`, bodyParams)
        .then(response => {
            commit('setLastUpdated');
            return response;
        })
        .catch(error => {
            console.error(error);
        });
};

// SEQUENCE EDIT SPECIFIC
/**
 * Dispatches `bulkedition/fetchItemIdInSequence`.
 * @param {Object} param1 - Sequence lookup identifiers.
 * @param {*} param1.collectionId - Collection identifier.
 * @param {*} param1.sequenceId - Sequence edit group identifier.
 * @param {*} param1.itemPosition - Item index in the sequence.
 * @returns {*} Action result.
 */
export const fetchItemIdInSequence = ({commit}, { collectionId, sequenceId, itemPosition }) => {

    return new Promise ((resolve, reject) => {
        axios.tainacanApi.get(`/collection/${collectionId}/sequence-edit/${sequenceId}/${itemPosition}`)
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

/**
 * Dispatches `bulkedition/createSequenceEditGroup`.
 * @param {Object} parameters - Sequence group creation parameters.
 * @param {*} parameters.object - Item id array or query object used to build the sequence group.
 * @param {*} parameters.collectionId - Collection identifier.
 * @returns {*} Action result.
 */
export const createSequenceEditGroup = ({commit}, parameters) => {
    let object = parameters.object;
    let collectionId = parameters.collectionId;

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
        axios.tainacanApi.post(`/collection/${collectionId}/sequence-edit`, sequenceEditParams)
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
