import Vue from 'vue';

export const setRepositoryTotalCollections = (state, repositoryTotalCollections) => {
    state.repositoryTotalCollections = repositoryTotalCollections;
}

export const setCollectionTotalItems = (state, collectionTotalItems) => {
    state.collectionTotalItems = collectionTotalItems;
}

export const setItems = ( state, items ) => {
    state.items = items;
}
export const setItemsListTemplate = ( state, items ) => {
    state.itemsListTemplate = items;
}

export const cleanItems = (state) => {
    state.items = [];
}

export const deleteItem = ( state, item ) => {
    let index = state.items.findIndex(deletedItem => deletedItem.id === item.id);
    if (index >= 0) {
        state.items.splice(index, 1);
    }
}

export const deleteCollection = ( state, collection ) => {
    let index = state.collections.findIndex(deletedCollection => deletedCollection.id === collection.id);
    if (index >= 0) {
        state.collections.splice(index, 1);
    }
}

export const setCollections = (state, collections) => {
    state.collections = collections;
}

export const cleanCollections = (state) => {
    state.collections = [];
}

export const setCollection = (state, collection) => {
    state.collection = collection;
}

export const cleanCollection = (state) => {
    state.collection = [];
}

export const setCollectionName = (state, collectionName) => {
    state.collectionName = collectionName;
}

export const setCollectionURL = (state, collectionURL) => {
    state.collectionURL = collectionURL;
}

export const cleanCollectionName = (state) => {
    state.collectionName = '';
}

export const setSingleAttachment = ( state, attachment ) => {
    let index = state.attachments.findIndex(newAttachment => newAttachment.id === attachment.id);
    if ( index >= 0){
        //state.metadatum[index] = metadatum;
        Vue.set( state.attachments, index, attachment );
    } else {
        state.attachments.push( attachment );
    }
}

export const setSingleFile = ( state, file ) => {
    let index = state.files.findIndex(newfile => newfile.id === file.id);
    if ( index >= 0){
        //state.metadatum[index] = metadatum;
        Vue.set( state.files, index, file );
    } else {
        state.files.push( file );
    }
}

export const cleanFiles = (state) => {
    state.files = [];
}

export const setAttachments = ( state, attachments ) => {
    state.attachments = attachments;
}

export const cleanAttachments = (state) => {
    state.attachments = [];
}

export const setCollectionCommentStatus = (state, collectionCommentStatus) => {
    state.collectionCommentStatus = collectionCommentStatus;
}

export const cleanCollectionCommentStatus = (state) => {
    state.collectionCommentStatus = '';
}

export const setCollectionAllowComments = (state, collectionAllowComments) => {
    state.collectionAllowComments = collectionAllowComments;
}

export const cleanCollectionAllowComments = (state) => {
    state.collectionAllowComments = '';
}