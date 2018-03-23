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