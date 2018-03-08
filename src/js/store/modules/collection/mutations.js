export const setItems = ( state, items ) => {
    state.items = items;
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

export const setFields = (state, fields) => {
    state.fields = fields;
}

export const setCollection = (state, collection) => {
    state.collection = collection;
}

export const setCollectionName = (state, collectionName) => {
    state.collectionName = collectionName;
}
