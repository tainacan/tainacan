// General Post Queries
/**
 * Dispatches `search/setPostQueryAttribute`.
 * @returns {*} Action result.
 */
export const setPostQueryAttribute = ({ commit }, filter, value ) => {
    commit('setPostQueryAttribute', {  attr: filter, value: value } );
};

/**
 * Dispatches `search/setPostQuery`.
 * @returns {*} Action result.
 */
export const setPostQuery = ({ commit }, postquery ) => {
    commit('setPostQuery', postquery );
};

/**
 * Dispatches `search/setAdvancedQuery`.
 * @returns {*} Action result.
 */
export const setAdvancedQuery = ({commit}, advancedSearchQuery) => {
    commit('removePostQueryAttribute', 'search');
    commit('removePostQueryAttribute', 's');
    commit('setAdvancedSearchQuery', advancedSearchQuery);
};

// Meta Queries from filters
/**
 * Dispatches `search/addMetaquery`.
 * @returns {*} Action result.
 */
export const addMetaquery = ( { commit }, filter ) => {
    if (filter && (filter.value === undefined || filter.value === null || filter.value.length === 0 || filter.value === '')) {
        commit('removeMetaQuery', filter  );
    } else {
        commit('addMetaQuery', filter  );
    }
};

// Fetch Only for item attributes limiting on results
/**
 * Dispatches `search/addFetchOnly`.
 * @returns {*} Action result.
 */
export const addFetchOnly = ( { commit }, metadatum ) => {
    commit('addFetchOnly', metadatum );
};
/**
 * Dispatches `search/removeFetchOnly`.
 * @returns {*} Action result.
 */
export const removeFetchOnly = ( { commit }, metadatum ) => {
    commit('removeFetchOnly', metadatum );
};

// Fetch Only for metadata limiting on results
/**
 * Dispatches `search/addFetchOnlyMeta`.
 * @returns {*} Action result.
 */
export const addFetchOnlyMeta = ( { commit }, metadatum ) => {
    commit('addFetchOnlyMeta', metadatum );
};
/**
 * Dispatches `search/removeFetchOnlyMeta`.
 * @returns {*} Action result.
 */
export const removeFetchOnlyMeta = ( { commit }, metadatum ) => {
    commit('removeFetchOnlyMeta', metadatum );
};

// Tax Queries from filters
/**
 * Dispatches `search/addTaxquery`.
 * @returns {*} Action result.
 */
export const addTaxquery = ( { commit }, filter  ) => {
    if (filter && (filter.terms === undefined || filter.terms === null || filter.terms === '' || filter.terms.length === 0 )) {
        commit('removeTaxQuery', filter  );
    } else {
        commit('addTaxQuery', filter  );
    }
};

/**
 * Dispatches `search/removeMetaQuery`.
 * @returns {*} Action result.
 */
export const removeMetaQuery = ( { commit }, filter  ) => {
    commit('removeMetaQuery', filter  );
};

/**
 * Dispatches `search/removeTaxQuery`.
 * @returns {*} Action result.
 */
export const removeTaxQuery = ( { commit }, filter  ) => {
    commit('removeTaxQuery', filter  );
};

/**
 * Dispatches `search/removePostIn`.
 * @returns {*} Action result.
 */
export const removePostIn = ( { commit }  ) => {
    commit('removePostIn');
};

// Pagination queries
/**
 * Dispatches `search/setTotalItems`.
 * @returns {*} Action result.
 */
export const setTotalItems = ({ commit }, total ) => {
    commit('setTotalItems', total);
};
/**
 * Dispatches `search/setTotalPages`.
 * @returns {*} Action result.
 */
export const setTotalPages = ({ commit }, totalPages ) => {
    commit('setTotalPages', totalPages);
};

/**
 * Dispatches `search/setPage`.
 * @returns {*} Action result.
 */
export const setPage = ({ commit },  page ) => {
    commit('setPostQueryAttribute', {  attr: 'paged', value: page } );
};

/**
 * Dispatches `search/setItemsPerPage`.
 * @returns {*} Action result.
 */
export const setItemsPerPage = ({ commit }, perPage ) => {
    const maxItemsPerPage = tainacan_plugin.api_max_items_per_page;
    perPage = (Number(maxItemsPerPage) >= Number(perPage)) ? perPage : maxItemsPerPage;

    commit('setPostQueryAttribute', {  attr: 'perpage', value: perPage } );
    commit('setItemsPerPage', perPage );
};

/**
 * Dispatches `search/setFacets`.
 * @returns {*} Action result.
 */
export const setFacets = ({ commit }, facets) => {
    commit('setFacets', facets);
};

/**
 * Dispatches `search/setStatus`.
 * @returns {*} Action result.
 */
export const setStatus= ({ commit }, status ) => {
    if (status == undefined || status == '')
        commit('removePostQueryAttribute', 'status');
    else
        commit('setPostQueryAttribute', {  attr: 'status', value: status } );
};

// Sorting queries
/**
 * Dispatches `search/setOrderBy`.
 * @returns {*} Action result.
 */
export const setOrderBy = ({ state, commit }, orderBy ) => {
    commit('removePostQueryAttribute', 'orderby');
    commit('removePostQueryAttribute', 'metakey');
    commit('removePostQueryAttribute', 'metatype');
    
    // This first if is to handle situations where a collection was created
    // with the invalid default of 'name'
    if (orderBy == 'name' || (orderBy.metakey && orderBy.metakey == 'name') ) {
        commit('setPostQueryAttribute', {  attr: 'orderby', value: 'date' } );
    } else if (orderBy.metakey) {
        Object.keys(orderBy).forEach((paramKey) => {
            commit('setPostQueryAttribute', {  attr: paramKey, value: orderBy[paramKey] });
        });
    } else {
        commit('setPostQueryAttribute', {  attr: 'orderby', value: orderBy } );
    }
};

/**
 * Dispatches `search/setOrder`.
 * @returns {*} Action result.
 */
export const setOrder = ({ commit }, order ) => {
    commit('setPostQueryAttribute', {  attr: 'order', value: order } );
};

// Set search query
/**
 * Dispatches `search/setSearchQuery`.
 * @returns {*} Action result.
 */
export const setSearchQuery = ({ commit }, searchQuery ) => {
    commit('setSearchQuery', searchQuery );
};

// Set per-word search mode
/**
 * Dispatches `search/setPerWordSearchMode`.
 * @returns {*} Action result.
 */
export const setPerWordSearchMode = ({ commit }, perWordSearchMode ) => {
    commit('setPerWordSearchMode', perWordSearchMode );
};

// Set ViewMode (view_mode)
/**
 * Dispatches `search/setViewMode`.
 * @returns {*} Action result.
 */
export const setViewMode = ({ commit }, viewMode ) => {
    commit('setViewMode', viewMode );
};

// Set AdminViewMode (admin_view_mode)
/**
 * Dispatches `search/setAdminViewMode`.
 * @returns {*} Action result.
 */
export const setAdminViewMode = ({ commit }, adminViewMode ) => {
    commit('setAdminViewMode', adminViewMode );
};

// Remove filter tag
/**
 * Dispatches `search/addFilterTag`.
 * @returns {*} Action result.
 */
export const addFilterTag = ( { commit }, filterTag  ) => {
    if (filterTag && (filterTag.value === undefined || filterTag.value === null || filterTag.value === '' || filterTag.value.length === 0 ))
        commit('removeFilterTag', filterTag);
    else
        commit('addFilterTag', filterTag);
};

// Set filter tags
/**
 * Dispatches `search/setFilterTags`.
 * @returns {*} Action result.
 */
export const setFilterTags = ({ commit }, filterTags ) => {
    commit('setFilterTags', filterTags );
};

// Remove filter tag
/**
 * Dispatches `search/removeFilterTag`.
 * @returns {*} Action result.
 */
export const removeFilterTag = ( { commit }, filterTag  ) => {
    commit('removeFilterTag', filterTag);
};

// Remove filter tag
/**
 * Dispatches `search/cleanFilterTags`.
 * @returns {*} Action result.
 */
export const cleanFilterTags = ( { commit } ) => {
    commit('cleanFilterTags');
};

/**
 * Dispatches `search/cleanMetaQueries`.
 * @returns {*} Action result.
 */
export const cleanMetaQueries = ( { commit }, { keepCollections } ) => {
    commit('cleanMetaQueries', { keepCollections });
};

/**
 * Dispatches `search/cleanTaxQueries`.
 * @returns {*} Action result.
 */
export const cleanTaxQueries = ({ commit }) => {
    commit('cleanTaxQueries');
};

/**
 * Dispatches `search/cleanFetchOnly`.
 * @returns {*} Action result.
 */
export const cleanFetchOnly = ({ commit }) => {
    commit('cleanFetchOnly');
};

/**
 * Dispatches `search/setSelectedItems`.
 * @returns {*} Action result.
 */
export const setSelectedItems = ({ commit }, selectedItems ) => {
    commit('setSelectedItems', selectedItems);
};

/**
 * Dispatches `search/addSelectedItem`.
 * @returns {*} Action result.
 */
export const addSelectedItem = ({ commit }, selectedItem ) => {
    commit('addSelectedItem', selectedItem);
};

/**
 * Dispatches `search/cleanSelectedItems`.
 * @returns {*} Action result.
 */
export const cleanSelectedItems = ({ commit }) => {
    commit('cleanSelectedItems');
};

/**
 * Dispatches `search/removeSelectedItem`.
 * @returns {*} Action result.
 */
export const removeSelectedItem = ({ commit }, selectedItem ) => {
    commit('removeSelectedItem', selectedItem);
};
