
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

export const setInit = ( state, init) => {
    state.isInit = init;
}