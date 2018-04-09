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

// Sorting queries
export const setOrderBy = ({ commit }, orderBy ) => {
    commit('setPostQueryAttribute', {  attr: 'orderby', value: orderBy } );
};

export const setOrder = ({ commit }, isOrderAsc ) => {
    if (isOrderAsc)
        commit('setPostQueryAttribute', {  attr: 'order', value: 'ASC' } );
    else
        commit('setPostQueryAttribute', {  attr: 'order', value: 'DESC' } );
};


