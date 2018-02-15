export const setItems = ( state, items ) => {
    state.items = items;
}

export const deleteItem = ( state, item ) => {
    let index = state.items.findIndex(deletedItem => deletedItem.id === item.id);
    if (index >= 0) {
        state.items.splice(index, 1);
    }
}

export const setCollections = (state, collections) => {
    state.collections = collections;
}

export const setCollection = (state, collection) => {
    state.collection = collection;
}