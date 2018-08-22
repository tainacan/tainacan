// General Post Queries
export const set_postquery_attribute = ({ commit }, filter, value ) => {
    commit('setPostQueryAttribute', {  attr: filter, value: value } );
};

export const set_postquery = ({ commit }, postquery ) => {
    commit('setPostQuery', postquery );
};

export const set_advanced_query = ({commit}, advancedSearchQuery) => {
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
export const add_fetchonly = ( { commit }, metadatum ) => {
    commit('addFetchOnly', metadatum );
};
export const remove_fetchonly = ( { commit }, metadatum ) => {
    commit('removeFetchOnly', metadatum );
};

// Fetch Only for metadata limiting on results
export const add_fetchonly_meta = ( { commit }, metadatum ) => {
    commit('addFetchOnlyMeta', metadatum );
};
export const remove_fetchonly_meta = ( { commit }, metadatum ) => {
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

export const setPage = ({ commit },  page ) => {
    commit('setPostQueryAttribute', {  attr: 'paged', value: page } );
};

export const setItemsPerPage = ({ commit }, page ) => {
    commit('setPostQueryAttribute', {  attr: 'perpage', value: page } );
};

export const setStatus= ({ commit }, status ) => {
    if (status == undefined || status == '')
        commit('removePostQueryAttribute', 'status');
    else
        commit('setPostQueryAttribute', {  attr: 'status', value: status } );
};

// Sorting queries
export const setOrderBy = ({ state, commit }, orderBy ) => {
    commit('cleanPostQueryAttribute', {  attr: 'orderby' } );
    
    // Primitive Types: string, date, item, term, compound, float
    if (orderBy.slug == 'creation_date') {
        commit('setPostQueryAttribute', {  attr: 'orderby', value: 'date' } );
    } else if (orderBy.slug == 'author_name') {
        commit('setPostQueryAttribute', {  attr: 'orderby', value: 'author_name' } );
    } else if (orderBy.metadata_type_object.primitive_type == 'float' || orderBy.metadata_type_object.primitive_type == 'int') {
        commit('setPostQueryAttribute', {  attr: 'metakey', value: orderBy.id } );
        commit('setPostQueryAttribute', {  attr: 'orderby', value: 'meta_value_num' } );
    } else if (orderBy.metadata_type_object.primitive_type == 'date') {
        commit('setPostQueryAttribute', {  attr: 'metakey', value: orderBy.id } );
        commit('setPostQueryAttribute', {  attr: 'metatype', value: 'DATETIME' } );
        commit('setPostQueryAttribute', {  attr: 'orderby', value: 'meta_value' } );
    } else if (orderBy.metadata_type_object.core) {
        commit('setPostQueryAttribute', {  attr: 'orderby', value: orderBy.metadata_type_object.related_mapped_prop } );
    } else {
        commit('setPostQueryAttribute', {  attr: 'metakey', value: orderBy.id } );
        commit('setPostQueryAttribute', {  attr: 'orderby', value: 'meta_value' } );
    }

};

export const setOrder = ({ commit }, order ) => {
    commit('setPostQueryAttribute', {  attr: 'order', value: order } );
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