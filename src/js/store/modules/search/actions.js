// General Post Queries
export const set_postquery_attribute = ({ commit }, filter, value ) => {
    commit('setPostQueryAttribute', {  attr: filter, value: value } );
};

export const set_postquery = ({ commit }, postquery ) => {
    commit('setPostQuery', postquery );
};

// Meta Queries from filters
export const add_metaquery = ( { commit }, filter  ) => {
    if( filter && filter.value.length === 0 ){
        commit('removeMetaQuery', filter  );
    } else {
        commit('addMetaQuery', filter  );
    }
};

// Fetch Only for item attributes limiting on results
export const add_fetchonly = ( { commit }, field ) => {
        commit('addFetchOnly', field );   
};
export const remove_fetchonly = ( { commit }, field ) => {
    commit('removeFetchOnly', field );
};

// Fetch Only for metadata limiting on results
export const add_fetchonly_meta = ( { commit }, field ) => {
    commit('addFetchOnlyMeta', field );
};
export const remove_fetchonly_meta = ( { commit }, field ) => {
    commit('removeFetchOnlyMeta', field );
};

// Tax Queries from filters
export const add_taxquery = ( { commit }, filter  ) => {
    if( filter && filter.terms.length === 0 ){
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
export const setOrderBy = ({ commit }, orderBy ) => {

    // Primitive Types: string, date, item, term, compound, float
    if (orderBy.id == 'date') {
        commit('setPostQueryAttribute', {  attr: 'orderby', value: 'date' } );
    } else if (orderBy.field_type_object.primitive_type == 'float' || orderBy.field_type_object.primitive_type == 'int') {
        commit('setPostQueryAttribute', {  attr: 'meta_key', value: orderBy.id } );
        commit('setPostQueryAttribute', {  attr: 'orderby', value: 'meta_value_num' } );
    } else if (orderBy.field_type_object.primitive_type == 'date') {
        commit('setPostQueryAttribute', {  attr: 'meta_key', value: orderBy.id } );
        commit('setPostQueryAttribute', {  attr: 'meta_type', value: 'DATETIME' } );
        commit('setPostQueryAttribute', {  attr: 'orderby', value: 'meta_value' } );
    } else if (orderBy.field_type_object.core) {
        commit('setPostQueryAttribute', {  attr: 'orderby', value: orderBy.field_type_object.related_mapped_prop } );
    } else {
        commit('setPostQueryAttribute', {  attr: 'meta_key', value: orderBy.id } );
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