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
    return state.postquery.paged;
}

export const getItemsPerPage = state => {
    return state.postquery.perpage;
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

<<<<<<< HEAD
export const getStatus = state => {
    return state.postquery.status;
=======
export const getFecthOnly = state => {
    return state.postquery.fetchonly;
>>>>>>> Begins implementation of FetchOnly for displayed fields in item page.
}