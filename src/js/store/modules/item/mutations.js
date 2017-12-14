
export const setItem = ( state, item ) => {
    state.item = item;
}

export const setMetadata = ( state, metadata) => {
    state.metadata = metadata;
}

export const setSingleMetadata = ( state, metadata) => {
    let index = state.metadata.findIndex(itemMetadata => itemMetadata.metadata_id === metadata.metadata_id);
    if ( index >= 0){
        state.metadata[index] = metadata;
    }else{
        state.metadata.push( metadata );
    }
}

export const setError = ( state, metadata ) => {
    let index = state.error.findIndex(itemMetadata => itemMetadata.metadata_id === metadata.metadata_id);
    if ( index >= 0){
        state.error[index] = metadata;
    }else{
        state.error.push( metadata );
    }
};

export const removeError =  ( state, metadata ) => {
    let index = state.error.findIndex(itemMetadata => itemMetadata.metadata_id === metadata.metadata_id);
    if ( index >= 0){
        state.error.splice( index, 1);
    }
}