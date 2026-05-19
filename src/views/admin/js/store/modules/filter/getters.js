/**
 * Reads derived state from `filter/getPostQuery`.
 * @param {Object} state - Module state.
 * @returns {*} Getter result.
 */
export const getPostQuery = state => {
    return state.postquery;
}

/**
 * Reads derived state from `filter/getMetaQuery`.
 * @param {Object} state - Module state.
 * @returns {*} Getter result.
 */
export const getMetaQuery = state => {
    return state.metaquery;
}

/**
 * Reads derived state from `filter/getTaxQuery`.
 * @param {Object} state - Module state.
 * @returns {*} Getter result.
 */
export const getTaxQuery = state => {
    return state.taxquery;
} 

/**
 * Reads derived state from `filter/getFilters`.
 * @param {Object} state - Module state.
 * @returns {*} Getter result.
 */
export const getFilters = state => {  
    
    return state.filters;
}

/**
 * Reads derived state from `filter/getFilterTypes`.
 * @param {Object} state - Module state.
 * @returns {*} Getter result.
 */
export const getFilterTypes = state => {
    return state.filterTypes;
}

/**
 * Reads derived state from `filter/getRepositoryCollectionFilters`.
 * @param {Object} state - Module state.
 * @returns {*} Getter result.
 */
export const getRepositoryCollectionFilters = state => {
    return state.repositoryCollectionFilters;
}

/**
 * Reads derived state from `filter/getTaxonomyFilters`.
 * @param {Object} state - Module state.
 * @returns {*} Getter result.
 */
export const getTaxonomyFilters = state => {
    return state.taxonomyFilters;
}