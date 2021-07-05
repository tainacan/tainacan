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

export const setOnlyRelatedItemsToItem = (state, { itemId, relatedItems }) => {
    if (state.item && state.item.id && state.item.id == itemId) {
        state.item.related_items = relatedItems;
    }
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
            let currentParent = JSON.parse(JSON.stringify(state.itemMetadata[parentIndex]));
            let currentParentValues = currentParent.value;
            let childMetadatumValue = {
                metadatum_id: itemMetadatum.metadatum.id,
                value: itemMetadatum.value,
                value_as_html: itemMetadatum.value_as_html,
                value_as_string: itemMetadatum.value_as_string,
                parent_meta_id: itemMetadatum.parent_meta_id
            };

            if (currentParent.metadatum.multiple == 'yes') {
                
                let currentChildMetadataGroupIndex = currentParentValues.findIndex((metadataGroup) => {
                    return metadataGroup.findIndex((metadatumValue) => {
                        return metadatumValue.parent_meta_id == itemMetadatum.parent_meta_id;
                    }) >= 0;
                });
                
                if (currentChildMetadataGroupIndex >= 0) {
                    let currentChildMetadatumIndex = currentParentValues[currentChildMetadataGroupIndex].findIndex((metadatumValue) => metadatumValue.parent_meta_id == itemMetadatum.parent_meta_id && metadatumValue.metadatum_id == itemMetadatum.metadatum.id);
                    if (currentChildMetadatumIndex >= 0)
                        currentParentValues[currentChildMetadataGroupIndex].splice(currentChildMetadatumIndex, 1, childMetadatumValue);
                    else
                        currentParentValues[currentChildMetadataGroupIndex].push(childMetadatumValue);
                } else {
                    currentParentValues.push([childMetadatumValue])
                }
                
            } else {
                let currentChildMetadatumIndex = currentParentValues.findIndex((metadatumValue) => metadatumValue.parent_meta_id == itemMetadatum.parent_meta_id && metadatumValue.metadatum_id == itemMetadatum.metadatum.id);
                
                if (currentChildMetadatumIndex >= 0)
                    currentParentValues.splice(currentChildMetadatumIndex, 1, childMetadatumValue);
                else
                    currentParentValues.push(childMetadatumValue);
            }
            
            currentParent.value = currentParentValues;
            Vue.set(state.itemMetadata, parentIndex, currentParent);
        }
    }
}

export const deleteChildItemMetadata = (state, { parentMetadatumId, parentMetaId }) => {
    
    let parentIndex = state.itemMetadata.findIndex(anItemMetadatum => anItemMetadatum.metadatum.id == parentMetadatumId);
        
    if (parentIndex >= 0) {
        let currentParent = JSON.parse(JSON.stringify(state.itemMetadata[parentIndex]));
        let currentParentValues = currentParent.value;

        let currentChildMetadataGroupIndex = currentParentValues.findIndex((metadataGroup) => {
            return metadataGroup.findIndex((metadatumValue) => metadatumValue.parent_meta_id == parentMetaId) >= 0;
        });

        if (currentChildMetadataGroupIndex >= 0)
            currentParentValues.splice(currentChildMetadataGroupIndex, 1);
            
        currentParent.value = JSON.parse(JSON.stringify(currentParentValues));
        Vue.set(state.itemMetadata, parentIndex, currentParent);
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

export const clearItemSubmission = (state) => {
    state.itemSubmission = {};
}

export const setItemSubmission = (state, value) => {
    state.itemSubmission = value;
}

export const setItemSubmissionMetadata = (state, value) => {
    state.itemSubmissionMetadata = value;
}

export const updateItemSubmission = (state, { key, value }) => {
    Vue.set(state.itemSubmission, key, value);
}

export const updateItemSubmissionMetadatum = (state, { metadatum_id, values, child_group_index, parent_id }) => {

    let metadata = Array.isArray(state.itemSubmissionMetadata) ? state.itemSubmissionMetadata : [];

    if (parent_id && parent_id > 0) {
        let existingParentMetadatumIndex = metadata.findIndex((metadatum) => metadatum.metadatum_id == parent_id);

        if (existingParentMetadatumIndex >= 0) {
            if (metadata[existingParentMetadatumIndex].value && metadata[existingParentMetadatumIndex].value[child_group_index]) {
                let existingMetadatumIndex = metadata[existingParentMetadatumIndex].value[child_group_index].findIndex((metadatum) => metadatum.metadatum_id == metadatum_id);
                
                if (existingMetadatumIndex >= 0)
                    metadata[existingParentMetadatumIndex].value[child_group_index][existingMetadatumIndex].value = values;
                else
                    metadata[existingParentMetadatumIndex].value[child_group_index].push({ metadatum_id: metadatum_id, value: values });
            } else {
                metadata[existingParentMetadatumIndex].value = (metadata[existingParentMetadatumIndex].value ? metadata[existingParentMetadatumIndex].value : []);
                metadata[existingParentMetadatumIndex].value.push([ { metadatum_id: metadatum_id, value: values } ])
            }
        } else {
            metadata.push({ metadatum_id: parent_id, value: [ [ { metadatum_id: metadatum_id, value: values } ] ] });
        }
    } else {
        let existingMetadatumIndex = metadata.findIndex((metadatum) => metadatum.metadatum_id == metadatum_id);

        if (existingMetadatumIndex >= 0)
            metadata[existingMetadatumIndex].value = values;
        else
            metadata.push({ metadatum_id: metadatum_id, value: values });
    }
    Vue.set(state, 'itemSubmissionMetadata', metadata);
}

export const deleteGroupFromItemSubmissionMetadatum = (state, { metadatum_id, child_group_index }) => {

    let existingMetadatumIndex = state.itemSubmissionMetadata.findIndex((metadatum) => metadatum.metadatum_id == metadatum_id);
    
    if (existingMetadatumIndex >= 0) {
        if (state.itemSubmissionMetadata[existingMetadatumIndex].value[child_group_index]) {
            let existingMetadatum = state.itemSubmissionMetadata[existingMetadatumIndex];
            let existingMetadatumValue = existingMetadatum.value;
            existingMetadatumValue.splice(child_group_index, 1);
            existingMetadatum.value = existingMetadatumValue;
            Vue.set(state.itemSubmissionMetadata, existingMetadatumIndex, existingMetadatum);
        }
    }
}
