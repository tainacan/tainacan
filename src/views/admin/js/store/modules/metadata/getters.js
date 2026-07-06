/**
 * Reads derived state from `metadata/getMetadata`.
 * @param {Object} state - Module state.
 * @returns {*} Getter result.
 */
export const getMetadata = state => {
    return state.metadata;
}

/**
 * Reads derived state from `metadata/getMetadataSections`.
 * @param {Object} state - Module state.
 * @returns {*} Getter result.
 */
export const getMetadataSections = state => {
    return state.metadataSections;
}

/**
 * Reads derived state from `metadata/getMetadatumTypes`.
 * @param {Object} state - Module state.
 * @returns {*} Getter result.
 */
export const getMetadatumTypes = state => {
    return state.metadatumTypes;
}

/**
 * Reads derived state from `metadata/getMetadatumMappers`.
 * @param {Object} state - Module state.
 * @returns {*} Getter result.
 */
export const getMetadatumMappers = state => {
    return state.metadatumMappers;
}