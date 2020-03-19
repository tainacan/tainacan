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

export const removeAttatchmentFromItem = (state, attachmentId) => {
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

export const setTotalAttachments = ( state, total) => {
    state.totalAttachments = total;
}

export const setItemTitle = ( state, itemTitle ) => {
    state.itemTitle = itemTitle;
}

export const cleanItemTitle = ( state ) => {
    state.itemTitle = '';
}

export const setItemMetadata = ( state, itemMetadata) => {
    state.itemMetadata = itemMetadata;
}

export const cleanItemMetadata = (state) => {
    state.itemMetadata = [];
}

export const setSingleMetadatum = (state, itemMetadatum) => {

    if (itemMetadatum.metadatum.parent <= 0) {
        let index = state.itemMetadata.findIndex(anItemMetadatum => anItemMetadatum.metadatum.id == itemMetadatum.metadatum.id);
        if (index >= 0)
            Vue.set( state.itemMetadata, index, itemMetadatum );
        else
            state.itemMetadata.push( itemMetadatum );
    } else {

        let parentIndex = state.itemMetadata.findIndex(anItemMetadatum => anItemMetadatum.metadatum.id == itemMetadatum.metadatum.parent);
        
        if (parentIndex >= 0) {
            let currentParent = state.itemMetadata[parentIndex];
            let currentParentValues = currentParent.value;
            let childMetadatumValue = {
                metadatum_id: itemMetadatum.metadatum.id,
                value: itemMetadatum.value,
                value_as_html: itemMetadatum.value_as_html,
                value_as_string: itemMetadatum.value_as_string,
                parent_meta_id: itemMetadatum.parent_meta_id
            };
            let currrentChildMetadatumIndex = currentParentValues.findIndex((metadatumValue) => metadatumValue.parent_meta_id == itemMetadatum.parent_meta_id && metadatumValue.metadatum_id == itemMetadatum.metadatum.id);
            if (currrentChildMetadatumIndex >= 0)
                currentParentValues[currrentChildMetadatumIndex] = childMetadatumValue;
            else
                currentParentValues.push(childMetadatumValue);

            currentParent.value = currentParentValues;
            console.log(JSON.parse(JSON.stringify(currentParent)));
            Vue.set(state.itemMetadata, parentIndex, currentParent);
        }
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

