import Vue from 'vue';

export const setItem = ( state, item ) => {
    state.item = item;
}

export const setSingleAttachment = ( state, attachment ) => {
    let index = state.attachment.findIndex(newAttachment => newAttachment.id === attachment.id);
    if ( index >= 0){
        //state.field[index] = field;
        Vue.set( state.attachments, index, attachment );
    } else {
        state.attachments.push( attachment );
    }
}

export const setAttachments = ( state, attachments ) => {
    state.attachments = attachments;
}

export const setItemTitle = ( state, itemTitle ) => {
    state.itemTitle = itemTitle;
}

export const setFields = ( state, fields) => {
    state.fields = fields;
}

export const cleanFields = (state) => {
    state.fields = [];
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
