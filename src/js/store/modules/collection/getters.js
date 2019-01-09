export const getItems = state => {
    return state.items;
}

export const getItemsListTemplate = state => {
    return state.itemsListTemplate;
}

export const getCollections = state => {
    return state.collections;
}

export const getCollection = state => {
    return state.collection;
}

export const getCollectionName = state => {
    return state.collectionName;
}

export const getCollectionURL = state => {
    return state.collectionURL;
}

export const getAttachments =  state => {
    return state.attachments;
}

export const getFiles =  state => {
    return state.files;
}

export const getCollectionCommentStatus = state => {
    return state.collectionCommentStatus;
}

export const getCollectionAllowComments = state => {
    return state.collectionAllowComments;
}

export const getCollectionTotalItems = state => {
    return state.collectionTotalItems;
}

export const getRepositoryTotalCollections = (state) => {
    return state.repositoryTotalCollections;
}