// General Post Queries
export const set_postquery_attribute = ({ commit }, filter, value ) => {
    commit('setPostQueryAttribute', {  attr: filter, value: value } );
};

export const set_postquery = ({ commit }, postquery ) => {
    commit('setPostQuery', postquery );
};

export const set_advanced_query = ({commit}, advancedSearchQuery) => {
    commit('removePostQueryAttribute', 'search');
    commit('setAdvancedSearchQuery', advancedSearchQuery);
};

// Meta Queries from filters
export const add_metaquery = ( { commit }, filter  ) => {
    if (filter && (filter.value == undefined || filter.value == null || filter.value.length === 0 || filter.value == '')) {
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
    if (filter && (filter.terms == undefined || filter.terms == null || filter.terms == '' || filter.terms.length == 0 )) {
        commit('removeTaxQuery', filter  );
    } else {
        commit('addTaxQuery', filter  );
    }
};

export const remove_metaquery = ( { commit }, filter  ) => {
    commit('removeMetaQuery', filter  );
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
    
    // Primitive Types: string, date, item, term, compound, float
    if (orderBy.slug == 'creation_date') {
        commit('setPostQueryAttribute', {  attr: 'orderby', value: 'date' } );
        commit('removePostQueryAttribute', 'metakey');
        commit('removePostQueryAttribute', 'metatype');
    } else if (orderBy.slug == 'author_name') {
        commit('setPostQueryAttribute', {  attr: 'orderby', value: 'author_name' } );
        commit('removePostQueryAttribute', 'metakey');
        commit('removePostQueryAttribute', 'metatype');
    } else if (orderBy.metadata_type_object.primitive_type == 'float' || orderBy.metadata_type_object.primitive_type == 'int') {
        commit('setPostQueryAttribute', {  attr: 'orderby', value: 'meta_value_num' } );
        commit('setPostQueryAttribute', {  attr: 'metakey', value: orderBy.id } );
        commit('removePostQueryAttribute', 'metatype');
    } else if (orderBy.metadata_type_object.primitive_type == 'date') {
        commit('setPostQueryAttribute', {  attr: 'orderby', value: 'meta_value' } );
        commit('setPostQueryAttribute', {  attr: 'metakey', value: orderBy.id } );
        commit('setPostQueryAttribute', {  attr: 'metatype', value: 'DATETIME' } );
    } else if (orderBy.metadata_type_object.core) {
        commit('setPostQueryAttribute', {  attr: 'orderby', value: orderBy.metadata_type_object.related_mapped_prop } );
        commit('removePostQueryAttribute', 'metakey');
        commit('removePostQueryAttribute', 'metatype');
    } else {
        commit('setPostQueryAttribute', {  attr: 'orderby', value: 'meta_value' } );
        commit('setPostQueryAttribute', {  attr: 'metakey', value: orderBy.id } );
        commit('removePostQueryAttribute', 'metatype');
    }
    
    commit('setOrderByName', orderBy.name);

};

export const setOrder = ({ commit }, order ) => {
    commit('setPostQueryAttribute', {  attr: 'order', value: order } );
};

// Set orderByName
export const setOrderByName = ({ commit }, orderByName ) => {
    commit('setOrderByName', orderByName );
};

// Set search query
export const setSearchQuery = ({ commit }, searchQuery ) => {
    commit('setSearchQuery', searchQuery );
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
    commit('addFilterTag', filterTag  );
};

// Remove filter tag
export const removeFilterTag = ( { commit }, filterTag  ) => {
    commit('removeFilterTag', filterTag  );
};

// Remove filter tag
export const cleanFilterTags = ( { commit } ) => {
    commit('cleanFilterTags');
};

export const cleanMetaQueries = ( { commit } ) => {
    commit('cleanMetaQueries');
};

export const cleanTaxQueries = ({ commit }) => {
    commit('cleanTaxQueries');
};

export const cleanFetchOnly = ({ commit }) => {
    commit('cleanFetchOnly');
};