import Vue from 'vue';

export const deleteMetadatum = ( state, metadatum ) => {
    let index = state.metadata.findIndex(deletedMetadatum => deletedMetadatum.id == metadatum.id);
    if (index >= 0) {
        state.metadata.splice(index, 1);
    }
}

export const deleteChildrenMetadatum = ( state, metadatum ) => {
    let index = state.childrenMetadata[metadatum.parent].findIndex(deletedMetadatum => deletedMetadatum.id == metadatum.id);
    if (index >= 0) {
        state.childrenMetadata[metadatum.parent].splice(index, 1);
    }
}

export const setSingleMetadatum = (state, {metadatum, index}) => {
    Vue.set( state.metadata, index, metadatum);
}

export const setSingleChildMetadatum = (state, {metadatum, index}) => {
    if (metadatum.id && metadatum.parent)
        Vue.set( state.childrenMetadata[metadatum.parent], index, metadatum);
}

export const setMetadata = (state, metadata) => {
    state.metadata = metadata;
}

export const setChildrenMetadata = (state, {metadata, parent}) => {
    const updatedChildrenMetadata = state.childrenMetadata;
    updatedChildrenMetadata[parent] = metadata;
    state.childrenMetadata = updatedChildrenMetadata;
}

export const updateMetadataOrderFromCollection = (state, metadataOrder) => {
    for (let i = 0; i < state.metadata.length; i++) {
        let updatedMetadatumIndex = metadataOrder.findIndex(aMetadatum => aMetadatum.id == state.metadata[i].id);
        if (updatedMetadatumIndex >= 0)
            state.metadata[i].enabled = metadataOrder[updatedMetadatumIndex].enabled;  
    }
}

export const setMetadatumTypes = (state, metadatumTypes) => {
    state.metadatumTypes = metadatumTypes;
}

export const setMetadatumMappers = (state, metadatumMappers) => {
    state.metadatumMappers = metadatumMappers;
}

export const cleanMetadata = (state) => {
    state.metadata = [];
}

export const cleanChildrenMetadata = (state, parent) => {
    state.childrenMetadata[parent] = [];
}