import Vue from 'vue';

export const deleteMetadatum = ( state, metadatum ) => {
    if (metadatum.parent && metadatum.parent >= 0) {
        const existingParentIndex = state.metadata.findIndex((aMetadatum) => aMetadatum.id == metadatum.parent);
        if (existingParentIndex >= 0) {
            let existingParent = JSON.parse(JSON.stringify(state.metadata[existingParentIndex]));
            let existingParentChildrenObject = existingParent.metadata_type_options.children_objects;
            
            const existingIndex = existingParentChildrenObject.findIndex((aMetadatum) => aMetadatum.id == metadatum.id);
            if (existingIndex >= 0)
                existingParentChildrenObject.splice(existingIndex, 1);
            
            existingParent.metadata_type_options.children_objects = existingParentChildrenObject;
            Vue.set(state.metadata, existingParentIndex, existingParent);
        }
        
    } else {
        let index = state.metadata.findIndex(deletedMetadatum => deletedMetadatum.id == metadatum.id);
        if (index >= 0) {
            state.metadata.splice(index, 1);
        }
    }
}

export const setSingleMetadatum = (state, {metadatum, index, isRepositoryLevel}) => {
    if (metadatum.parent && metadatum.parent >= 0) {
        const existingParentIndex = state.metadata.findIndex((aMetadatum) => aMetadatum.id == metadatum.parent);
        if (existingParentIndex >= 0) {
            let existingParent = JSON.parse(JSON.stringify(state.metadata[existingParentIndex]));
            let existingParentChildrenObject = existingParent.metadata_type_options.children_objects;
            const existingIndex = existingParentChildrenObject.findIndex((aMetadatum) => aMetadatum.id == metadatum.id);
            if (index != undefined && index != null) {
                if (existingIndex >= 0)
                    existingParentChildrenObject.splice(index, 1, metadatum);
                else
                    existingParentChildrenObject.splice(index, 0, metadatum);
            } else {
                if (existingIndex >= 0)
                    existingParentChildrenObject.splice(existingIndex, 1, metadatum);
                else
                    existingParentChildrenObject.push(metadatum);
            }
            existingParent.metadata_type_options.children_objects = existingParentChildrenObject;
            Vue.set(state.metadata, existingParentIndex, existingParent)
        }    
    } else {
        
        if (isRepositoryLevel) {
            const existingIndex = state.metadata.findIndex((aMetadatum) => aMetadatum.id == metadatum.id);
            if (existingIndex >= 0)
                Vue.set( state.metadata, existingIndex, metadatum)
            else
                state.metadata.unshift(metadatum)
        } else {        
            if (index != undefined && index != null)
                Vue.set( state.metadata, index, metadatum);
            else {
                const existingIndex = state.metadata.findIndex((aMetadatum) => aMetadatum.id == metadatum.id);
                
                if (existingIndex >= 0)
                    Vue.set( state.metadata, existingIndex, metadatum)
            } 
        }
    }
}

export const setMetadata = (state, metadata) => {
    state.metadata = metadata;
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

export const setMetadataSections = (state, metadataSections) => {
    state.metadataSections = metadataSections;
}

export const setMetadataSectionMetadata = (state, { metadataSectionId, metadata }) => {
    const existingIndex = state.metadataSections.findIndex((aMetadataSection) => aMetadataSection.id == metadataSectionId);

    if (existingIndex >= 0) {
        let metadataSection = state.metadataSections[existingIndex];
        metadataSection['metadata_list'] = metadata;
        Vue.set(state.metadataSections, existingIndex, metadataSection);
    }
}

export const setSingleMetadataSection = (state, { metadataSection, index }) => {   
    if (index !== undefined && index !== null)
        Vue.set( state.metadataSections, index, metadataSection);
    else {
        const existingIndex = state.metadataSections.findIndex((aMetadataSection) => aMetadataSection.id == metadataSection.id);
        
        if (existingIndex >= 0)
            Vue.set( state.metadataSections, existingIndex, metadataSection)
    } 
}

export const updateMetadataSectionsOrderFromCollection = (state, metadataSectionsOrder) => {
    for (let i = 0; i < state.metadataSections.length; i++) {
        let updatedMetadataSectionIndex = metadataSectionsOrder.findIndex(aMetadataSection => aMetadataSection.id == state.metadataSections[i].id);
        if (updatedMetadataSectionIndex >= 0)
            state.metadataSections[i].enabled = metadataSectionsOrder[updatedMetadataSectionIndex].enabled;  
    }
}

export const deleteMetadataSection = ( state, metadataSection ) => {
    let index = state.metadataSection.findIndex(deletedMetadataSection => deletedMetadataSection.id == metadataSection.id);
    if (index >= 0)
        state.metadataSection.splice(index, 1);
}

export const cleanMetadataSections = (state) => {
    state.metadataSections = [];
}
