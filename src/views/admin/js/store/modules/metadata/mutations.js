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

export const setSingleMetadatum = (state, {metadatum, index, isRepositoryLevel }) => {
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

export const updateCollectionMetadataOrder = (state, { metadataOrder, metadataSectionId }) => {

    for (let i = 0; i < metadataOrder.length; i++) {

        // First updates state.metadata
        let updatedMetadatumIndex = state.metadata.findIndex((aMetadatum) => aMetadatum.id == metadataOrder[i]['id']);
        if (updatedMetadatumIndex >= 0)
            state.metadata[i].enabled = metadataOrder[updateCollectionMetadataOrder].enabled;
    
        // Then updates state.metadataSections[x].metadata_object_list
        const existingSectionIndex = state.metadataSections.findIndex((aMetadataSection) => aMetadataSection.id == metadataSectionId);
        if (existingSectionIndex >= 0) {   
            const updatedMetadatumIndexInsideSection = state.metadataSections[existingSectionIndex]['metadata_object_list'].findIndex((aMetadatum) => { return !!aMetadatum['id'] && (aMetadatum.id == metadataOrder[i]['id']) });
            if (updatedMetadatumIndexInsideSection >= 0) {
                
                let metadataObjectList = state.metadataSections[existingSectionIndex]['metadata_object_list'];
                metadataObjectList[updatedMetadatumIndexInsideSection].enabled = metadataOrder[i].enabled;
                Vue.set(state.metadataSections[existingSectionIndex], 'metadata_object_list', metadataObjectList); 
            }
        }
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
        metadataSection['metadata_object_list'] = metadata;
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
        else 
            state.metadataSections.push(metadataSection);
    }
}

export const updateMetadatumInsideSectionMetadata = (state, { metadatum, index, sectionId }) => {
    const existingSectionIndex = state.metadataSections.findIndex((aMetadataSection) => aMetadataSection.id == sectionId);
    if (existingSectionIndex >= 0) {
        let metadataSection = state.metadataSections[existingSectionIndex];

        if (metadatum.parent && metadatum.parent >= 0) {
            const existingParentIndex = metadataSection['metadata_object_list'].findIndex((aMetadatum) => aMetadatum.id == metadatum.parent);
            if (existingParentIndex >= 0) {
                let existingParent = JSON.parse(JSON.stringify(metadataSection['metadata_object_list'][existingParentIndex]));
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

                metadataSection['metadata_object_list'].splice(existingParentIndex, 1, existingParent); 
            }    
        } else {
            const existingMetadatumIndex = metadataSection['metadata_object_list'].findIndex((aMetadatum) => { return !!aMetadatum['id'] && (aMetadatum.id == metadatum.id) });
            if (existingMetadatumIndex >= 0)
                metadataSection['metadata_object_list'].splice(existingMetadatumIndex, 1, metadatum);
            else {
                // Replaces placeholder (a metadata type object, not a metadatum)
                if (metadataSection['metadata_object_list'][index] && !metadataSection['metadata_object_list'][index]['id'])
                    metadataSection['metadata_object_list'].splice(index, 1, metadatum);
                else
                    metadataSection['metadata_object_list'].splice(index, 0, metadatum);
            }
        }  
        Vue.set(state.metadataSections, existingSectionIndex, metadataSection);
    }  
}

export const deleteMetadatumInsideMetadataSection = (state, metadatum) => {
    const existingSectionIndex = state.metadataSections.findIndex((aMetadataSection) => aMetadataSection.id == metadatum.metadata_section_id);
    if (existingSectionIndex >= 0) {
        let metadataSection = state.metadataSections[existingSectionIndex];

        if (metadatum.parent && metadatum.parent >= 0) {
            const existingParentIndex = metadataSection['metadata_object_list'].findIndex((aMetadatum) => aMetadatum.id == metadatum.parent);
            if (existingParentIndex >= 0) {
                let existingParent = JSON.parse(JSON.stringify(metadataSection['metadata_object_list'][existingParentIndex]));
                let existingParentChildrenObject = existingParent.metadata_type_options.children_objects;
                
                const existingIndex = existingParentChildrenObject.findIndex((aMetadatum) => aMetadatum.id == metadatum.id);
                if (existingIndex >= 0)
                    existingParentChildrenObject.splice(existingIndex, 1);
                
                existingParent.metadata_type_options.children_objects = existingParentChildrenObject;
                
                metadataSection['metadata_object_list'].splice(existingParentIndex, 1, existingParent); 
            }
            
        } else {
            const existingMetadatumIndex = metadataSection['metadata_object_list'].findIndex((aMetadatum) => { return !!aMetadatum['id'] && (aMetadatum.id == metadatum.id) });
            if (existingMetadatumIndex >= 0)
                metadataSection['metadata_object_list'].splice(existingMetadatumIndex, 1);   
        }
        Vue.set(state.metadataSections, existingSectionIndex, metadataSection);
    }
}

export const updateMetadataSectionsOrderFromCollection = (state, metadataSectionsOrder) => {
    for (let i = 0; i < state.metadataSections.length; i++) {
        let updatedMetadataSectionIndex = metadataSectionsOrder.findIndex(aMetadataSection => aMetadataSection.id == state.metadataSections[i].id);
        if (updatedMetadataSectionIndex >= 0)
            state.metadataSections[i].enabled = metadataSectionsOrder[updatedMetadataSectionIndex].enabled;  
    }
}

export const clearPlaceholderMetadataSection = (state) => {
    const existingPlaceholder = state.metadataSections.findIndex((aMetadataSection) => aMetadataSection.id == 'metadataSectionCreator');
    if (existingPlaceholder >= 0)
        state.metadataSections.splice(existingPlaceholder, 1);
}

export const deleteMetadataSection = ( state, metadataSection ) => {
    let index = state.metadataSections.findIndex(deletedMetadataSection => deletedMetadataSection.id == metadataSection.id);
    if (index >= 0)
        state.metadataSections.splice(index, 1);
}

export const cleanMetadataSections = (state) => {
    state.metadataSections = [];
}

export const moveMetadataSectionUp = (state, index) => {
    state.metadataSections.splice(index - 1, 0, state.metadataSections.splice(index, 1)[0]);   
}

export const moveMetadataSectionDown = (state, index) => {
    state.metadataSections.splice(index + 1, 0, state.metadataSections.splice(index, 1)[0]);
}

export const moveMetadatumUp = (state, { index, sectionIndex }) => {
    state.metadataSections[sectionIndex].metadata_object_list.splice(index - 1, 0, state.metadataSections[sectionIndex].metadata_object_list.splice(index, 1)[0]);
}

export const moveMetadatumDown = (state, { index, sectionIndex }) => {
    state.metadataSections[sectionIndex].metadata_object_list.splice(index + 1, 0, state.metadataSections[sectionIndex].metadata_object_list.splice(index, 1)[0]);
}