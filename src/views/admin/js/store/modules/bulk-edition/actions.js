import axios from '../../../axios/axios';

/**
 * Builds a REST path from a template. Each inserted value must be hexadecimal.
 * Collection ids and sequence indexes are decimal, which is a subset of that.
 * Group and sequence ids are the hexadecimal ids those routes accept.
 * The value is encoded before it is joined to the path. Returns null when one is not.
 * @param {TemplateStringsArray} strings
 * @param {...unknown} values
 * @returns {string|null}
 */
function hexPath(strings, ...values) {
    let path = strings[0];

    for (let index = 0; index < values.length; index++) {
        const id = String(values[index] == null ? '' : values[index]);
        if (!/^[0-9a-f]+$/.test(id))
            return null;
        path += encodeURIComponent(id) + strings[index + 1];
    }

    return path;
}

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
        const endpoint = hexPath`/collection/${collectionId}/bulk-edit/${groupId}`;
        if (endpoint == null) {
            reject(new Error('Invalid group id.'));
            return;
        }

        axios.tainacanApi.get(endpoint)
            .then(response => {
                commit('setGroup', response.data);
                resolve(response.data);
            })
            .catch(error => {
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
        const endpoint = hexPath`/collection/${collectionId}/sequence-edit/${groupId}`;
        if (endpoint == null) {
            reject(new Error('Invalid sequence id.'));
            return;
        }

        axios.tainacanApi.get(endpoint)
            .then(response => {
                commit('setGroup', response.data);
                resolve(response.data);
            })
            .catch(error => {
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

    const endpoint = hexPath`/collection/${collectionId}/bulk-edit/${groupId}/set`;
    if (endpoint == null)
        return Promise.reject(new Error('Invalid group id.'));

    return axios.tainacanApi.post(endpoint, bodyParams)
        .then(response => {
            commit('setLastUpdated');
            return response;
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
export const addValueInBulk = ({}, parameters) => {
    let groupId = parameters.groupId;
    let collectionId = parameters.collectionId;
    let bodyParams = parameters.bodyParams;

    const endpoint = hexPath`/collection/${collectionId}/bulk-edit/${groupId}/add`;
    if (endpoint == null)
        return Promise.reject(new Error('Invalid group id.'));

    return axios.tainacanApi.post(endpoint, bodyParams)
        .then(response => {
            return response;
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
export const removeValueInBulk = ({}, parameters) => {
    let groupId = parameters.groupId;
    let collectionId = parameters.collectionId;
    let bodyParams = parameters.bodyParams;

    const endpoint = hexPath`/collection/${collectionId}/bulk-edit/${groupId}/remove`;
    if (endpoint == null)
        return Promise.reject(new Error('Invalid group id.'));

    return axios.tainacanApi.post(endpoint, bodyParams);
};

/**
 * Dispatches `bulkedition/clearValuesInBulk`.
 * @param {Object} parameters - Bulk operation parameters.
 * @param {*} parameters.collectionId - Collection identifier.
 * @param {*} parameters.groupId - Bulk edit group identifier.
 * @param {*} parameters.bodyParams - Request body payload for the operation.
 * @returns {*} Action result.
 */
export const clearValuesInBulk = ({}, parameters) => {
    let groupId = parameters.groupId;
    let collectionId = parameters.collectionId;
    let bodyParams = parameters.bodyParams;

    const endpoint = hexPath`/collection/${collectionId}/bulk-edit/${groupId}/clear`;
    if (endpoint == null)
        return Promise.reject(new Error('Invalid group id.'));

    return axios.tainacanApi.post(endpoint, bodyParams);
};

/**
 * Dispatches `bulkedition/replaceValueInBulk`.
 * @param {Object} parameters - Bulk operation parameters.
 * @param {*} parameters.collectionId - Collection identifier.
 * @param {*} parameters.groupId - Bulk edit group identifier.
 * @param {*} parameters.bodyParams - Request body payload for the operation.
 * @returns {*} Action result.
 */
export const replaceValueInBulk = ({}, parameters) => {
    let groupId = parameters.groupId;
    let collectionId = parameters.collectionId;
    let bodyParams = parameters.bodyParams;

    const endpoint = hexPath`/collection/${collectionId}/bulk-edit/${groupId}/replace`;
    if (endpoint == null)
        return Promise.reject(new Error('Invalid group id.'));

    return axios.tainacanApi.post(endpoint, bodyParams)
        .then(response => {
            return response;
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

    const endpoint = hexPath`/collection/${collectionId}/bulk-edit/${groupId}/set_status`;
    if (endpoint == null)
        return Promise.reject(new Error('Invalid group id.'));

    return axios.tainacanApi.post(endpoint, bodyParams)
        .then(response => {
            commit('setLastUpdated');
            return response;
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

    const endpoint = hexPath`/collection/${collectionId}/bulk-edit/${groupId}/set_comment_status`;
    if (endpoint == null)
        return Promise.reject(new Error('Invalid group id.'));

    return axios.tainacanApi.post(endpoint, bodyParams)
        .then(response => {
            commit('setLastUpdated');
            return response;
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

    const endpoint = hexPath`/collection/${collectionId}/bulk-edit/${groupId}/set_author_id`;
    if (endpoint == null)
        return Promise.reject(new Error('Invalid group id.'));

    return axios.tainacanApi.post(endpoint, bodyParams)
        .then(response => {
            commit('setLastUpdated');
            return response;
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

    const endpoint = hexPath`/collection/${collectionId}/bulk-edit/${groupId}/trash`;
    if (endpoint == null)
        return Promise.reject(new Error('Invalid group id.'));

    return axios.tainacanApi.post(endpoint)
        .then(response => {
            commit('setLastUpdated');
            return response;
        });
};

/**
 * Dispatches `bulkedition/untrashItemsInBulk`.
 * @param {Object} parameters - Bulk operation parameters.
 * @param {*} parameters.collectionId - Collection identifier.
 * @param {*} parameters.groupId - Bulk edit group identifier.
 * @returns {*} Action result.
 */
export const untrashItemsInBulk = ({}, parameters) => {
    let groupId = parameters.groupId;
    let collectionId = parameters.collectionId;

    const endpoint = hexPath`/collection/${collectionId}/bulk-edit/${groupId}/untrash`;
    if (endpoint == null)
        return Promise.reject(new Error('Invalid group id.'));

    return axios.tainacanApi.post(endpoint)
        .then(response => {
            return response;
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

    const endpoint = hexPath`/collection/${collectionId}/bulk-edit/${groupId}/delete_items`;
    if (endpoint == null)
        return Promise.reject(new Error('Invalid group id.'));

    return axios.tainacanApi.post(endpoint)
        .then(response => {
            commit('setLastUpdated');
            return response;
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

    const endpoint = hexPath`/collection/${collectionId}/bulk-edit/${groupId}/copy_value`;
    if (endpoint == null)
        return Promise.reject(new Error('Invalid group id.'));

    return axios.tainacanApi.post(endpoint, bodyParams)
        .then(response => {
            commit('setLastUpdated');
            return response;
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
        const endpoint = hexPath`/collection/${collectionId}/sequence-edit/${sequenceId}/${itemPosition}`;
        if (endpoint == null) {
            reject(new Error('Invalid sequence id.'));
            return;
        }

        axios.tainacanApi.get(endpoint)
            .then(response => {
                commit('setItemIdInSequence', response.data);
                resolve(response.data);
            })
            .catch(error => {
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
