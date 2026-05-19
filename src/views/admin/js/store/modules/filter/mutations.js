

// FILTERS ------------------------------------------------------------------------
/**
 * Commits `filter/deleteFilter` state changes.
 * @param {Object} state - Module state.
 * @param {*} filter - Mutation payload.
 * @returns {void} No return value.
 */
export const deleteFilter = ( state, filter ) => {
    let index = state.filters.findIndex(deletedFilter => deletedFilter.id === filter.id);
    if (index >= 0) {
        state.filters.splice(index, 1);
    }
}

/**
 * Commits `filter/addTemporaryFilter` state changes.
 * @param {Object} state - Module state.
 * @param {*} filter - Mutation payload.
 * @returns {void} No return value.
 */
export const addTemporaryFilter = ( state, filter) => {
    state.filters.push(filter);
}

/**
 * Commits `filter/deleteTemporaryFilter` state changes.
 * @param {Object} state - Module state.
 * @param {*} index - Mutation payload.
 * @returns {void} No return value.
 */
export const deleteTemporaryFilter = ( state, index) => {
    state.filters.splice(index, 1);
}

/**
 * Commits `filter/setSingleFilter` state changes.
 * @param {Object} state - Module state.
 * @returns {void} No return value.
 */
export const setSingleFilter = (state, { filter, index}) => {
    Object.assign(state.filters, { [index]: filter });
}

/**
 * Commits `filter/addSingleFilter` state changes.
 * @param {Object} state - Module state.
 * @returns {void} No return value.
 */
export const addSingleFilter = (state, { filter, index}) => {
    const at = state.filters[index];
    if (at !== undefined && at.id === undefined) {
        state.filters.splice(index, 1, filter);
    } else {
        state.filters.splice(index, 0, filter);
    }
}

/**
 * Commits `filter/setFilters` state changes.
 * @param {Object} state - Module state.
 * @param {*} filters - Mutation payload.
 * @returns {void} No return value.
 */
export const setFilters = (state, filters) => {
    state.filters = filters;
}

/**
 * Commits `filter/updateFiltersOrderFromCollection` state changes.
 * @param {Object} state - Module state.
 * @param {*} filtersOrder - Mutation payload.
 * @returns {void} No return value.
 */
export const updateFiltersOrderFromCollection = (state, filtersOrder) => {
    for (let i = 0; i < state.filters.length; i++) {
        let updatedFilterIndex = filtersOrder.findIndex(aFilter => aFilter.id == state.filters[i].id);
        if (updatedFilterIndex >= 0)
            state.filters[i].enabled = filtersOrder[updatedFilterIndex].enabled;  
    }
}

/**
 * Commits `filter/setFilterTypes` state changes.
 * @param {Object} state - Module state.
 * @param {*} filterTypes - Mutation payload.
 * @returns {void} No return value.
 */
export const setFilterTypes = (state, filterTypes) => {
    state.filterTypes = filterTypes;
}

/**
 * Commits `filter/setRepositoryCollectionFilters` state changes.
 * @param {Object} state - Module state.
 * @param {*} repositoryCollectionFilters - Mutation payload.
 * @returns {void} No return value.
 */
export const setRepositoryCollectionFilters = (state, repositoryCollectionFilters) => {
    state.repositoryCollectionFilters = repositoryCollectionFilters;
}

/**
 * Commits `filter/clearRepositoryCollectionFilters` state changes.
 * @param {Object} state - Module state.
 * @returns {void} No return value.
 */
export const clearRepositoryCollectionFilters = (state) => {
    state.repositoryCollectionFilters = {};
}

/**
 * Commits `filter/setTaxonomyFilters` state changes.
 * @param {Object} state - Module state.
 * @param {*} taxonomyFilters - Mutation payload.
 * @returns {void} No return value.
 */
export const setTaxonomyFilters = (state, taxonomyFilters) => {
    state.taxonomyFilters = taxonomyFilters;
}

/**
 * Commits `filter/clearTaxonomyFilters` state changes.
 * @param {Object} state - Module state.
 * @returns {void} No return value.
 */
export const clearTaxonomyFilters = (state) => {
    state.taxonomyFilters = {};
}

/**
 * Commits `filter/moveFilterUp` state changes.
 * @param {Object} state - Module state.
 * @param {*} index - Mutation payload.
 * @returns {void} No return value.
 */
export const moveFilterUp = (state, index) => {
    state.filters.splice(index - 1, 0, state.filters.splice(index, 1)[0]);   
}


/**
 * Commits `filter/moveFilterDown` state changes.
 * @param {Object} state - Module state.
 * @param {*} index - Mutation payload.
 * @returns {void} No return value.
 */
export const moveFilterDown = (state, index) => {
    state.filters.splice(index + 1, 0, state.filters.splice(index, 1)[0]);
}
