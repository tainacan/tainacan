

/**
 * Commits `collection/setRepositoryTotalCollections` state changes.
 * @param {Object} state - Module state.
 * @param {*} repositoryTotalCollections - Mutation payload.
 * @returns {void} No return value.
 */
export const setRepositoryTotalCollections = (state, repositoryTotalCollections) => {
    state.repositoryTotalCollections = repositoryTotalCollections;
}

/**
 * Commits `collection/setItems` state changes.
 * @param {Object} state - Module state.
 * @param {*} items - Mutation payload.
 * @returns {void} No return value.
 */
export const setItems = ( state, items ) => {
    state.items = items;
}
/**
 * Commits `collection/setItemsListTemplate` state changes.
 * @param {Object} state - Module state.
 * @param {*} items - Mutation payload.
 * @returns {void} No return value.
 */
export const setItemsListTemplate = ( state, items ) => {
    state.itemsListTemplate = items;
}

/**
 * Commits `collection/cleanItems` state changes.
 * @param {Object} state - Module state.
 * @returns {void} No return value.
 */
export const cleanItems = (state) => {
    state.items = [];
}

/**
 * Commits `collection/deleteItem` state changes.
 * @param {Object} state - Module state.
 * @param {*} item - Mutation payload.
 * @returns {void} No return value.
 */
export const deleteItem = ( state, item ) => {
    let index = state.items.findIndex(deletedItem => deletedItem.id === item.id);
    if (index >= 0) {
        state.items.splice(index, 1);
    }
}

/**
 * Commits `collection/deleteCollection` state changes.
 * @param {Object} state - Module state.
 * @param {*} collection - Mutation payload.
 * @returns {void} No return value.
 */
export const deleteCollection = ( state, collection ) => {
    let index = state.collections.findIndex(deletedCollection => deletedCollection.id === collection.id);
    if (index >= 0) {
        state.collections.splice(index, 1);
    }
}

/**
 * Commits `collection/setCollections` state changes.
 * @param {Object} state - Module state.
 * @param {*} collections - Mutation payload.
 * @returns {void} No return value.
 */
export const setCollections = (state, collections) => {
    state.collections = collections;
}

/**
 * Commits `collection/setCollectionTaxonomies` state changes.
 * @param {Object} state - Module state.
 * @param {*} collectionTaxonomies - Mutation payload.
 * @returns {void} No return value.
 */
export const setCollectionTaxonomies = (state, collectionTaxonomies) => {
    state.collectionTaxonomies = collectionTaxonomies;
}

/**
 * Commits `collection/setCollectionTaxonomiesTerms` state changes.
 * @param {Object} state - Module state.
 * @returns {void} No return value.
 */
export const setCollectionTaxonomiesTerms = (state, { taxonomy, terms }) => {
    Object.assign(state.collectionTaxonomies[taxonomy], { 'terms': terms });
}

/**
 * Commits `collection/cleanCollections` state changes.
 * @param {Object} state - Module state.
 * @returns {void} No return value.
 */
export const cleanCollections = (state) => {
    state.collections = [];
}

/**
 * Commits `collection/setCollection` state changes.
 * @param {Object} state - Module state.
 * @param {*} collection - Mutation payload.
 * @returns {void} No return value.
 */
export const setCollection = (state, collection) => {
    state.collection = collection;
}

/**
 * Commits `collection/cleanCollection` state changes.
 * @param {Object} state - Module state.
 * @returns {void} No return value.
 */
export const cleanCollection = (state) => {
    state.collection = [];
}

/**
 * Commits `collection/setSingleAttachment` state changes.
 * @param {Object} state - Module state.
 * @param {*} attachment - Mutation payload.
 * @returns {void} No return value.
 */
export const setSingleAttachment = ( state, attachment ) => {
    let index = state.attachments.findIndex(newAttachment => newAttachment.id === attachment.id);
    if ( index >= 0){
        //state.metadatum[index] = metadatum;
        Object.assign(state.attachments, { [index]: attachment });
    } else {
        state.attachments.push( attachment );
    }
}

/**
 * Commits `collection/setSingleFile` state changes.
 * @param {Object} state - Module state.
 * @param {*} file - Mutation payload.
 * @returns {void} No return value.
 */
export const setSingleFile = ( state, file ) => {
    let index = state.files.findIndex(newfile => newfile.id === file.id);
    if ( index >= 0){
        //state.metadatum[index] = metadatum;
        Object.assign(state.files, { [index]: file });
    } else {
        state.files.push( file );
    }
}

/**
 * Commits `collection/cleanFiles` state changes.
 * @param {Object} state - Module state.
 * @returns {void} No return value.
 */
export const cleanFiles = (state) => {
    state.files = [];
}

/**
 * Commits `collection/setAttachments` state changes.
 * @param {Object} state - Module state.
 * @param {*} attachments - Mutation payload.
 * @returns {void} No return value.
 */
export const setAttachments = ( state, attachments ) => {
    state.attachments = attachments;
}

/**
 * Commits `collection/cleanAttachments` state changes.
 * @param {Object} state - Module state.
 * @returns {void} No return value.
 */
export const cleanAttachments = (state) => {
    state.attachments = [];
}

/**
 * Commits `collection/setFilterTags` state changes.
 * @param {Object} state - Module state.
 * @param {*} filterTags - Mutation payload.
 * @returns {void} No return value.
 */
export const setFilterTags = ( state, filterTags ) => {
    state.filter_tags = filterTags;
}
