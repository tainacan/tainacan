import Vue from 'vue';

export const setItem = ( state, item ) => {
    state.item = item;
}

export const setItemTitle = ( state, itemTitle ) => {
    state.itemTitle = itemTitle;
}

export const setFields = ( state, field) => {
    state.fields = field;
}

export const setSingleField = ( state, field) => {
    let index = state.fields.findIndex(itemMetadata => itemMetadata.field.id === field.field.id);
    if ( index >= 0){
        //state.field[index] = field;
        Vue.set( state.fields, index, field );
    } else {
        state.fields.push( field );
    }
}
