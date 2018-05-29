export const getPostQuery = state => {
    return state.postquery;
}

export const getMetaQuery = state => {
    return state.metaquery;
}

export const getTaxQuery = state => {
    return state.taxquery;
}

export const getTotalItems = state => {
    return state.totalItems;
}

export const getPage = state => {
    if (state.postquery.paged == undefined)
        return 1;
    else
        return Number(state.postquery.paged);
}

export const getItemsPerPage = state => {
    if (state.postquery.paged == undefined)
        return 12;
    else
        return Number(state.postquery.perpage);
};

export const getOrder = state => {
    return state.postquery.order;
}

export const getOrderBy = state => {
    return state.postquery.orderby;
};

export const getSearchQuery = state => {
    return state.postquery.search;
}

export const getStatus = state => {
    return state.postquery.status;
}

export const getViewMode = state => {
    return state.postquery.format;
}

export const getFetchOnly = state => {
    return state.postquery.fetch_only;
}

export const getFetchOnlyMeta = state => {
    return ( ! state.postquery.fetch_only['meta'] ) ? [] : state.postquery.fetch_only['meta'];
}

export const getViewMode = state => {
    return state.postquery.format;
}