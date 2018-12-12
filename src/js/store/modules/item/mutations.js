import Vue from 'vue';

export const setItem = ( state, item ) => {
    state.item = item;
}

export const cleanItem = ( state ) => {
    state.item = [];
}

export const cleanLastUpdated = ( state ) => {
    state.lastUpdated = '';
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

export const setAttachments = ( state, attachments ) => {
    state.attachments = attachments;
}

export const  removeAttatchmentFromItem = (state, attachmentId) => {
    let indexOfRemoved = state.attachments.findIndex((anAttachment) => anAttachment.id == attachmentId);

    if (indexOfRemoved >= 0)
        state.attachments.splice(indexOfRemoved, 1);
}


export const cleanAttachment = (state) => {
    state.attachment = {};
}

export const cleanAttachments = (state) => {
    state.attachments = [];
}

export const setItemTitle = ( state, itemTitle ) => {
    state.itemTitle = itemTitle;
}

export const cleanItemTitle = ( state ) => {
    state.itemTitle = '';
}

export const setMetadata = ( state, metadata) => {
    state.metadata = metadata;
}

export const cleanMetadata = (state) => {
    state.metadata = [];
}

export const setSingleMetadatum = ( state, metadatum) => {
    let index = state.metadata.findIndex(itemMetadata => itemMetadata.metadatum.id === metadatum.metadatum.id);
    if ( index >= 0){
        //state.metadatum[index] = metadatum;
        Vue.set( state.metadata, index, metadatum );
    } else {
        state.metadata.push( metadatum );
    }
}

export const setLastUpdated = (state, value) => {
    if (value != undefined)
        state.lastUpdated = value;
    else {
        let now = new Date();
        state.lastUpdated = now.toLocaleString();
    }
}

