export const getMetadata = state => {
    return state.metadata;
}

export const getMetadataSections = state => {
    return state.metadataSections;
}

export const getMetadatumTypes = state => {
    return state.metadatumTypes;
}

export const getMappers = state => {
    return state.mappers;
}

export const getMapper = (state, { mapperSlug }) => {
    return state.mapper[0];
}