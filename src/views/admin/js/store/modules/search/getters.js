/**
 * Reads derived state from `search/getPostQuery`.
 * @param {Object} state - Module state.
 * @returns {*} Getter result.
 */
export const getPostQuery = state => {
    return state.postquery;
};

/**
 * Reads derived state from `search/getAdvancedSearchQuery`.
 * @param {Object} state - Module state.
 * @returns {*} Getter result.
 */
export const getAdvancedSearchQuery = state => {
    return state.advancedSearchQuery;
};

/**
 * Reads derived state from `search/getMetaQuery`.
 * @param {Object} state - Module state.
 * @returns {*} Getter result.
 */
export const getMetaQuery = state => {
    return state.metaquery;
};

/**
 * Reads derived state from `search/getTaxQuery`.
 * @param {Object} state - Module state.
 * @returns {*} Getter result.
 */
export const getTaxQuery = state => {
    return state.taxquery;
};

/**
 * Reads derived state from `search/getTotalItems`.
 * @param {Object} state - Module state.
 * @returns {*} Getter result.
 */
export const getTotalItems = state => {
    return state.totalItems;
};

/**
 * Reads derived state from `search/getTotalPages`.
 * @param {Object} state - Module state.
 * @returns {*} Getter result.
 */
export const getTotalPages = state => {
    return state.totalPages;
};

/**
 * Reads derived state from `search/getPage`.
 * @param {Object} state - Module state.
 * @returns {*} Getter result.
 */
export const getPage = state => {
    if (state.postquery.paged == undefined)
        return 1;
    else
        return Number(state.postquery.paged);
};

/**
 * Reads derived state from `search/getItemsPerPage`.
 * @param {Object} state - Module state.
 * @returns {*} Getter result.
 */
export const getItemsPerPage = state => {
    if (state.itemsPerPage == undefined)
        return 12;
    else {
        return Number(state.itemsPerPage);
    }
};

/**
 * Reads derived state from `search/getOrder`.
 * @param {Object} state - Module state.
 * @returns {*} Getter result.
 */
export const getOrder = state => {
    return state.postquery.order;
};

/**
 * Reads derived state from `search/getOrderBy`.
 * @param {Object} state - Module state.
 * @returns {*} Getter result.
 */
export const getOrderBy = state => {
    return state.postquery.orderby;
};

/**
 * Reads derived state from `search/getSearchQuery`.
 * @param {Object} state - Module state.
 * @returns {*} Getter result.
 */
export const getSearchQuery = state => {
    return state.postquery.search || state.postquery.s;
};

/**
 * Reads derived state from `search/getSentenceMode`.
 * @param {Object} state - Module state.
 * @returns {*} Getter result.
 */
export const getSentenceMode = state => {
    return state.postquery.sentence;
};

/**
 * Reads derived state from `search/getStatus`.
 * @param {Object} state - Module state.
 * @returns {*} Getter result.
 */
export const getStatus = state => {
    return state.postquery.status;
};

/**
 * Reads derived state from `search/getViewMode`.
 * @param {Object} state - Module state.
 * @returns {*} Getter result.
 */
export const getViewMode = state => {
    return state.postquery.view_mode;
};

/**
 * Reads derived state from `search/getAdminViewMode`.
 * @param {Object} state - Module state.
 * @returns {*} Getter result.
 */
export const getAdminViewMode = state => {
    return state.postquery.admin_view_mode;
};
/**
 * Reads derived state from `search/getFetchOnly`.
 * @param {Object} state - Module state.
 * @returns {*} Getter result.
 */
export const getFetchOnly = state => {
    return state.postquery.fetch_only;
};

/**
 * Reads derived state from `search/getMetaKey`.
 * @param {Object} state - Module state.
 * @returns {*} Getter result.
 */
export const getMetaKey = state => {
    return state.postquery.metakey;
};

/**
 * Reads derived state from `search/getFetchOnlyMeta`.
 * @param {Object} state - Module state.
 * @returns {*} Getter result.
 */
export const getFetchOnlyMeta = state => {
    return ( ! state.postquery.fetch_only_meta ) ? '' : state.postquery.fetch_only_meta;
};

/**
 * Reads derived state from `search/getFilterTags`.
 * @param {Object} state - Module state.
 * @returns {*} Getter result.
 */
export const getFilterTags = state => {
    return state.filter_tags;
};

/**
 * Reads derived state from `search/getFacets`.
 * @param {Object} state - Module state.
 * @returns {*} Getter result.
 */
export const getFacets = state => {
    return state.facets;
};

/**
 * Reads derived state from `search/getSelectedItems`.
 * @param {Object} state - Module state.
 * @returns {*} Getter result.
 */
export const getSelectedItems = state => {
    return state.selecteditems;
};

/**
 * Reads derived state from `search/getHighlightedItem`.
 * @param {Object} state - Module state.
 * @returns {*} Getter result.
 */
export const getHighlightedItem = state => {
    return state.highlightedItem;
};
