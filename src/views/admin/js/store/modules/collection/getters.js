/**
 * Reads derived state from `collection/getItems`.
 * @param {Object} state - Module state.
 * @returns {*} Getter result.
 */
export const getItems = state => {
    return state.items;
}

/**
 * Reads derived state from `collection/getItemsListTemplate`.
 * @param {Object} state - Module state.
 * @returns {*} Getter result.
 */
export const getItemsListTemplate = state => {
    return state.itemsListTemplate;
}

/**
 * Reads derived state from `collection/getCollections`.
 * @param {Object} state - Module state.
 * @returns {*} Getter result.
 */
export const getCollections = state => {
    return state.collections;
}

/**
 * Reads derived state from `collection/getCollectionTaxonomies`.
 * @param {Object} state - Module state.
 * @returns {*} Getter result.
 */
export const getCollectionTaxonomies = state => {
    return state.collectionTaxonomies;
}

/**
 * Reads derived state from `collection/getCollection`.
 * @param {Object} state - Module state.
 * @returns {*} Getter result.
 */
export const getCollection = state => {
    return state.collection;
}

/**
 * Reads derived state from `collection/getAttachments`.
 * @param {Object} state - Module state.
 * @returns {*} Getter result.
 */
export const getAttachments =  state => {
    return state.attachments;
}

/**
 * Reads derived state from `collection/getFiles`.
 * @param {Object} state - Module state.
 * @returns {*} Getter result.
 */
export const getFiles =  state => {
    return state.files;
}

/**
 * Reads derived state from `collection/getRepositoryTotalCollections`.
 * @param {Object} state - Module state.
 * @returns {*} Getter result.
 */
export const getRepositoryTotalCollections = (state) => {
    return state.repositoryTotalCollections;
}