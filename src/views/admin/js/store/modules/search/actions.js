// General Post Queries
export const set_postquery_attribute = ({ commit }, filter, value ) => {
    commit('setPostQueryAttribute', {  attr: filter, value: value } );
};

export const set_postquery = ({ commit }, postquery ) => {
    commit('setPostQuery', postquery );
};

export const set_advanced_query = ({commit}, advancedSearchQuery) => {
    commit('removePostQueryAttribute', 'search');
    commit('removePostQueryAttribute', 's');
    commit('setAdvancedSearchQuery', advancedSearchQuery);
};

// Meta Queries from filters
export const add_metaquery = ( { commit }, filter ) => {
    if (filter && (filter.value === undefined || filter.value === null || filter.value.length === 0 || filter.value === '')) {
        commit('removeMetaQuery', filter  );
    } else {
        commit('addMetaQuery', filter  );
    }
};

// Fetch Only for item attributes limiting on results
export const add_fetch_only = ( { commit }, metadatum ) => {
    commit('addFetchOnly', metadatum );
};
export const remove_fetch_only = ( { commit }, metadatum ) => {
    commit('removeFetchOnly', metadatum );
};

// Fetch Only for metadata limiting on results
export const add_fetch_only_meta = ( { commit }, metadatum ) => {
    commit('addFetchOnlyMeta', metadatum );
};
export const remove_fetch_only_meta = ( { commit }, metadatum ) => {
    commit('removeFetchOnlyMeta', metadatum );
};

// Tax Queries from filters
export const add_taxquery = ( { commit }, filter  ) => {
    if (filter && (filter.terms === undefined || filter.terms === null || filter.terms === '' || filter.terms.length === 0 )) {
        commit('removeTaxQuery', filter  );
    } else {
        commit('addTaxQuery', filter  );
    }
};

export const remove_metaquery = ( { commit }, filter  ) => {
    commit('removeMetaQuery', filter  );
};

export const remove_taxquery = ( { commit }, filter  ) => {
    commit('removeTaxQuery', filter  );
};

export const remove_postin = ( { commit }  ) => {
    commit('removePostIn');
};

// Pagination queries
export const setTotalItems = ({ commit }, total ) => {
    commit('setTotalItems', total);
};
export const setTotalPages = ({ commit }, totalPages ) => {
    commit('setTotalPages', totalPages);
};

export const setPage = ({ commit },  page ) => {
    commit('setPostQueryAttribute', {  attr: 'paged', value: page } );
};

export const setItemsPerPage = ({ commit }, perPage ) => {
    const maxItemsPerPage = tainacan_plugin.api_max_items_per_page;
    perPage = (Number(maxItemsPerPage) >= Number(perPage)) ? perPage : maxItemsPerPage;

    commit('setPostQueryAttribute', {  attr: 'perpage', value: perPage } );
    commit('setItemsPerPage', perPage );
};

export const setFacets = ({ commit }, facets) => {
    commit('setFacets', facets);
};

export const setStatus= ({ commit }, status ) => {
    if (status == undefined || status == '')
        commit('removePostQueryAttribute', 'status');
    else
        commit('setPostQueryAttribute', {  attr: 'status', value: status } );
};

// Sorting queries
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

export const setOrder = ({ commit }, order ) => {
    commit('setPostQueryAttribute', {  attr: 'order', value: order } );
};

// Set search query
export const setSearchQuery = ({ commit }, searchQuery ) => {
    commit('setSearchQuery', searchQuery );
};

// Set sentence mode
export const setSentenceMode = ({ commit }, sentenceMode ) => {
    commit('setSentenceMode', sentenceMode );
};

// Set ViewMode (view_mode)
export const setViewMode = ({ commit }, viewMode ) => {
    commit('setViewMode', viewMode );
};

// Set AdminViewMode (admin_view_mode)
export const setAdminViewMode = ({ commit }, adminViewMode ) => {
    commit('setAdminViewMode', adminViewMode );
};

// Remove filter tag
export const addFilterTag = ( { commit }, filterTag  ) => {
    if (filterTag && (filterTag.value === undefined || filterTag.value === null || filterTag.value === '' || filterTag.value.length === 0 ))
        commit('removeFilterTag', filterTag);
    else
        commit('addFilterTag', filterTag);
};

// Set filter tags
export const setFilterTags = ({ commit }, filterTags ) => {
    commit('setFilterTags', filterTags );
};

// Remove filter tag
export const removeFilterTag = ( { commit }, filterTag  ) => {
    commit('removeFilterTag', filterTag);
};

// Remove filter tag
export const cleanFilterTags = ( { commit } ) => {
    commit('cleanFilterTags');
};

export const cleanMetaQueries = ( { commit }, { keepCollections } ) => {
    commit('cleanMetaQueries', { keepCollections });
};

export const cleanTaxQueries = ({ commit }) => {
    commit('cleanTaxQueries');
};

export const cleanFetchOnly = ({ commit }) => {
    commit('cleanFetchOnly');
};

export const setSelectedItems = ({ commit }, selectedItems ) => {
    commit('setSelectedItems', selectedItems);
};

export const addSelectedItem = ({ commit }, selectedItem ) => {
    commit('addSelectedItem', selectedItem);
};

export const cleanSelectedItems = ({ commit }) => {
    commit('cleanSelectedItems');
};

export const removeSelectedItem = ({ commit }, selectedItem ) => {
    commit('removeSelectedItem', selectedItem);
};

export const highlightsItem = ({ commit }, itemId ) => {
    commit('setHighlightedItem', itemId);
};
