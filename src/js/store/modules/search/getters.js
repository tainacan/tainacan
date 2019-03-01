export const getPostQuery = state => {
    return state.postquery;
};

export const getAdvancedSearchQuery = state => {
    return state.advancedSearchQuery;
};

export const getMetaQuery = state => {
    return state.metaquery;
};

export const getTaxQuery = state => {
    return state.taxquery;
};

export const getTotalItems = state => {
    return state.totalItems;
};

export const getTotalPages = state => {
    return state.totalPages;
};

export const getPage = state => {
    if (state.postquery.paged == undefined)
        return 1;
    else
        return Number(state.postquery.paged);
};

export const getItemsPerPage = state => {
    if (state.itemsPerPage == undefined)
        return 12;
    else {
        return Number(state.itemsPerPage);
    }
};

export const getOrder = state => {
    return state.postquery.order;
};

export const getOrderBy = state => {
    return state.postquery.orderby;
};

export const getSearchQuery = state => {
    return state.postquery.search;
};

export const getStatus = state => {
    return state.postquery.status;
};

export const getViewMode = state => {
    return state.postquery.view_mode;
};

export const getAdminViewMode = state => {
    return state.postquery.admin_view_mode;
};
export const getFetchOnly = state => {
    return state.postquery.fetch_only;
};

export const getOrderByName = state => {
    return state.orderByName;
};

export const getMetaKey = state => {
    return state.postquery.metakey;
};

export const getFetchOnlyMeta = state => {
    return ( ! state.postquery.fetch_only_meta ) ? '' : state.postquery.fetch_only_meta;
};

export const getFilterTags = state => {
    return state.filter_tags;
};

export const getFacets = state => {
    return state.facets;
};
