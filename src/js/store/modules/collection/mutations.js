export const setItems = ( state, items ) => {
    state.items = items;
}

export const deleteItem = ( state, item ) => {
    let index = state.items.findIndex(deletedItem => deletedItem.id === item.id);
    if (index >= 0) {
        state.items.splice(index, 1);
    }
}

export const deleteField = ( state, field ) => {
    let index = state.fields.findIndex(deletedField => deletedField.id === field.id);
    if (index >= 0) {
        state.fields.splice(index, 1);
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

export const setSingleField = (state, field) => {
    let index = state.fields.findIndex(newField => newField.id === field.id);
    if ( index >= 0){
        //state.field[index] = field;
        Vue.set( state.fields, index, field );
    } else {
        state.fields.push( field );
    }
}

export const setFields = (state, fields) => {
    state.fields = fields;
}

export const setCollection = (state, collection) => {
    state.collection = collection;
}

export const setFieldTypes = (state, fieldTypes) => {
    state.fieldTypes = fieldTypes;
}