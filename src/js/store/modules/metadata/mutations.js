import Vue from 'vue';

export const deleteMetadatum = ( state, metadatum ) => {
    let index = state.metadata.findIndex(deletedMetadatum => deletedMetadatum.id == metadatum.id);
    if (index >= 0) {
        state.metadata.splice(index, 1);
    }
}

export const setSingleMetadatum = (state, {metadatum, index}) => {
    Vue.set( state.metadata, index, metadatum);
}

export const setMetadata = (state, metadata) => {
    state.metadata = metadata;
}

export const setMetadatumTypes = (state, metadatumTypes) => {
    state.metadatumTypes = metadatumTypes;
}

export const setMetadatumMappers = (state, metadatumMappers) => {
    state.metadatumMappers = metadatumMappers;
}
