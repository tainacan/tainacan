

export const setRepositoryTotalCollections = (state, repositoryTotalCollections) => {
    state.repositoryTotalCollections = repositoryTotalCollections;
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

export const setCollectionTaxonomies = (state, collectionTaxonomies) => {
    state.collectionTaxonomies = collectionTaxonomies;
}

export const setCollectionTaxonomiesTerms = (state, { taxonomy, terms }) => {
    Object.assign(state.collectionTaxonomies[taxonomy], { 'terms': terms });
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

export const setCollectionTaxonomyValues = (state, { collection, taxonomyValues }) => {
    if ( collection.id == state.collection.id ) {
        state.collection.collection_taxonomies = state.collection.collection_taxonomies || {};
        state.collection.collection_taxonomies[taxonomyValues.slug] = taxonomyValues;
    }
}

export const setSingleAttachment = ( state, attachment ) => {
    let index = state.attachments.findIndex(newAttachment => newAttachment.id === attachment.id);
    if ( index >= 0){
        //state.metadatum[index] = metadatum;
        Object.assign(state.attachments, { [index]: attachment });
    } else {
        state.attachments.push( attachment );
    }
}

export const setSingleFile = ( state, file ) => {
    let index = state.files.findIndex(newfile => newfile.id === file.id);
    if ( index >= 0){
        //state.metadatum[index] = metadatum;
        Object.assign(state.files, { [index]: file });
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

export const setFilterTags = ( state, filterTags ) => {
    state.filter_tags = filterTags;
}