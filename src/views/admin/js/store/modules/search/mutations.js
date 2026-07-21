

/**
 * Commits `search/setPostQueryAttribute` state changes.
 * @param {Object} state - Module state.
 * @returns {void} No return value.
 */
export const setPostQueryAttribute = ( state, { attr, value }) => {
    Object.assign(state.postquery, { [attr]: value });
};

/**
 * Commits `search/removePostQueryAttribute` state changes.
 * @param {Object} state - Module state.
 * @param {*} attr - Mutation payload.
 * @returns {void} No return value.
 */
export const removePostQueryAttribute = ( state, attr) => {
    delete state.postquery[`${attr}`];
};

/**
 * Commits `search/setPostQuery` state changes.
 * @param {Object} state - Module state.
 * @param {*} postquery - Mutation payload.
 * @returns {void} No return value.
 */
export const setPostQuery = ( state, postquery ) => {
    state.postquery = postquery;
};

/**
 * Commits `search/setAdvancedSearchQuery` state changes.
 * @param {Object} state - Module state.
 * @param {*} advancedSearchQuery - Mutation payload.
 * @returns {void} No return value.
 */
export const setAdvancedSearchQuery = (state, advancedSearchQuery) => {
    state.postquery.advancedSearch = advancedSearchQuery.advancedSearch;
    state.postquery.metaquery = Object.assign({}, advancedSearchQuery.metaquery);
    state.postquery.taxquery = Object.assign({}, advancedSearchQuery.taxquery);
};

/**
 * Commits `search/addMetaQuery` state changes.
 * @param {Object} state - Module state.
 * @param {*} filter - Mutation payload.
 * @returns {void} No return value.
 */
export const addMetaQuery = ( state, filter ) => {
    state.postquery.metaquery = ( ! state.postquery.metaquery  || state.postquery.metaquery.length == undefined ) ? [] : state.postquery.metaquery;

    let metaquery = {
        key: filter.metadatum_id,
        value: filter.value
    }
    if ( filter.compare )
        metaquery.compare = filter.compare;
    if ( filter.type )
        metaquery.type = filter.type;
    if ( filter.secondary )
        metaquery.secondary = filter.secondary;

    let index = state.postquery.metaquery.findIndex( item => item.key === filter.metadatum_id);
    if ( index >= 0 )
        Object.assign( state.postquery.metaquery, { [index]: metaquery } );
    else
        state.postquery.metaquery.push(metaquery);
};

/**
 * Commits `search/addTaxQuery` state changes.
 * @param {Object} state - Module state.
 * @param {*} filter - Mutation payload.
 * @returns {void} No return value.
 */
export const addTaxQuery = ( state, filter ) => {
    state.postquery.taxquery = ( ! state.postquery.taxquery || state.postquery.taxquery.length == undefined ) ? [] : state.postquery.taxquery;

    let taxquery = {
        taxonomy: filter.taxonomy,
        terms: filter.terms
    }
    if ( filter.compare )
        taxquery.compare = filter.compare;

    let index = state.postquery.taxquery.findIndex( item => item.taxonomy === filter.taxonomy);
    if ( index >= 0 )
        Object.assign( state.postquery.taxquery, { [index]: taxquery } );
    else
        state.postquery.taxquery.push(taxquery);
};

/**
 * Commits `search/addFetchOnly` state changes.
 * @param {Object} state - Module state.
 * @param {*} metadatum - Mutation payload.
 * @returns {void} No return value.
 */
export const addFetchOnly = ( state, metadatum ) => {
    state.postquery.fetch_only = metadatum
};
/**
 * Commits `search/addFetchOnlyMeta` state changes.
 * @param {Object} state - Module state.
 * @param {*} metadatum - Mutation payload.
 * @returns {void} No return value.
 */
export const addFetchOnlyMeta = ( state, metadatum ) => {
    state.postquery.fetch_only_meta = metadatum;
};

/**
 * Commits `search/removeFetchOnly` state changes.
 * @param {Object} state - Module state.
 * @param {*} metadatum - Mutation payload.
 * @returns {void} No return value.
 */
export const removeFetchOnly = ( state, metadatum ) => {

    let fetch = state.postquery.fetch_only.split(',');

    for (let key in metadatum) {
        fetch.splice(key, 1);
    }

    state.postquery.fetch_only = fetch.toString();
};

/**
 * Commits `search/removeFetchOnlyMeta` state changes.
 * @param {Object} state - Module state.
 * @param {*} metadatum - Mutation payload.
 * @returns {void} No return value.
 */
export const removeFetchOnlyMeta = ( state, metadatum ) => {
    if(state.postquery.fetch_only_meta != undefined) {
        let fetch_meta = state.postquery.fetch_only_meta.split(',');

        let index = fetch_meta.findIndex((item) => item == metadatum);

        fetch_meta.splice(index, 1);

        state.postquery.fetch_only_meta = fetch_meta.toString();
    }
};

/**
 * Commits `search/removeMetaQuery` state changes.
 * @param {Object} state - Module state.
 * @param {*} filter - Mutation payload.
 * @returns {void} No return value.
 */
export const removeMetaQuery = ( state, filter ) => {
    state.postquery.metaquery = ( ! state.postquery.metaquery ) ? [] : state.postquery.metaquery;

    let index = state.postquery.metaquery.findIndex( item => item.key == filter.metadatum_id);

    if ( index >= 0 ) {
        if (!filter.isMultiValue && Array.isArray(state.postquery.metaquery[index].value) && state.postquery.metaquery[index].value.length > 1) {
            let otherIndex = state.postquery.metaquery[index].value.findIndex(item => item == filter.value);
            if ( otherIndex >= 0 )
                state.postquery.metaquery[index].value.splice(otherIndex, 1)
        } else
            state.postquery.metaquery.splice(index, 1);
        
        // Handles removing metaqueries from secondary filter metadata
        if ( filter.secondaryMetadatumId ) {
            let secondaryIndex = state.postquery.metaquery.findIndex( item => item.key == filter.secondaryMetadatumId);

            if ( secondaryIndex >= 0 ) {
                if ( !filter.isMultiValue && Array.isArray(state.postquery.metaquery[secondaryIndex].value) && state.postquery.metaquery[secondaryIndex].value.length > 1 ) {
                    let otherSecondaryIndex = state.postquery.metaquery[secondaryIndex].value.findIndex(item => item == filter.value);
                    if ( otherSecondaryIndex >= 0 )
                        state.postquery.metaquery[secondaryIndex].value.splice(otherSecondaryIndex, 1)
                } else
                    state.postquery.metaquery.splice(secondaryIndex, 1);
            }
        }
    }
};

/**
 * Commits `search/removeTaxQuery` state changes.
 * @param {Object} state - Module state.
 * @param {*} filter - Mutation payload.
 * @returns {void} No return value.
 */
export const removeTaxQuery = ( state, filter ) => {
    state.postquery.taxquery = ( ! state.postquery.taxquery ) ? [] : state.postquery.taxquery;

    let index = state.postquery.taxquery.findIndex( item => item.taxonomy == filter.taxonomy);
    
    if (index >= 0) {
        if (Array.isArray(state.postquery.taxquery[index].terms) && state.postquery.taxquery[index].terms.length > 1) {
            let otherIndex = state.postquery.taxquery[index].terms.findIndex(item => item == filter.value);

            if (otherIndex >= 0)
                state.postquery.taxquery[index].terms.splice(otherIndex, 1)
        } else
            state.postquery.taxquery.splice(index, 1);
    }
};

/**
 * Commits `search/removePostIn` state changes.
 * @param {Object} state - Module state.
 * @returns {void} No return value.
 */
export const removePostIn = ( state ) => {
    delete state.postquery.postin;
};

/**
 * Commits `search/setTotalItems` state changes.
 * @param {Object} state - Module state.
 * @param {*} total - Mutation payload.
 * @returns {void} No return value.
 */
export const setTotalItems = ( state, total ) => {
    state.totalItems = total;
};

/**
 * Commits `search/setTotalPages` state changes.
 * @param {Object} state - Module state.
 * @param {*} totalPages - Mutation payload.
 * @returns {void} No return value.
 */
export const setTotalPages = ( state, totalPages ) => {
    state.totalPages = totalPages;
};

/**
 * Commits `search/setItemsPerPage` state changes.
 * @param {Object} state - Module state.
 * @param {*} itemsPerPage - Mutation payload.
 * @returns {void} No return value.
 */
export const setItemsPerPage = ( state, itemsPerPage ) => {
    state.itemsPerPage = itemsPerPage;
};

/**
 * Commits `search/setSearchQuery` state changes.
 * @param {Object} state - Module state.
 * @param {*} searchQuery - Mutation payload.
 * @returns {void} No return value.
 */
export const setSearchQuery = ( state, searchQuery ) => {
    
    if (searchQuery != '')
        state.postquery.search = searchQuery;
    else
        delete state.postquery.search;

    // In case a s parameter was passed
    delete state.postquery.s;
};

/**
 * Commits `search/setPerWordSearchMode` state changes.
 * @param {Object} state - Module state.
 * @param {boolean} perWordSearchMode - Whether to search each word separately.
 * @returns {void} No return value.
 */
export const setPerWordSearchMode = ( state, perWordSearchMode ) => {
    Object.assign(state.postquery, { 'sentence': !perWordSearchMode });
};

/**
 * Commits `search/setStatus` state changes.
 * @param {Object} state - Module state.
 * @param {*} status - Mutation payload.
 * @returns {void} No return value.
 */
export const setStatus = ( state, status ) => {
    state.postquery.status = status;
};

/**
 * Commits `search/setViewMode` state changes.
 * @param {Object} state - Module state.
 * @param {*} viewMode - Mutation payload.
 * @returns {void} No return value.
 */
export const setViewMode = ( state, viewMode ) => {
    state.postquery.view_mode = viewMode;
};

/**
 * Commits `search/setAdminViewMode` state changes.
 * @param {Object} state - Module state.
 * @param {*} adminViewMode - Mutation payload.
 * @returns {void} No return value.
 */
export const setAdminViewMode = ( state, adminViewMode ) => {
    state.postquery.admin_view_mode = adminViewMode;
};

/**
 * Commits `search/addFilterTag` state changes.
 * @param {Object} state - Module state.
 * @param {*} filterTag - Mutation payload.
 * @returns {void} No return value.
 */
export const addFilterTag = ( state, filterTag ) => {
    state.filter_tags = ( ! state.filter_tags) ? [] : state.filter_tags;
    let index = state.filter_tags.findIndex( tag => tag.filterId == filterTag.filterId);

    if (index >= 0)
        Object.assign(state.filter_tags, { [index]: filterTag });
    else
        state.filter_tags.push(filterTag);
};

/**
 * Commits `search/setFilterTags` state changes.
 * @param {Object} state - Module state.
 * @param {*} filterArguments - Mutation payload.
 * @returns {void} No return value.
 */
export const setFilterTags = ( state, filterArguments ) => {
    let filterTags = filterArguments.map((aFilterArgument) => {
        return {
            filterId: aFilterArgument.filter ? aFilterArgument.filter.id : null,
            label: aFilterArgument.label,
            value:  aFilterArgument.value,
            taxonomy: (aFilterArgument.metadatum &&
                        aFilterArgument.metadatum.metadata_type_object &&
                        aFilterArgument.metadatum.metadata_type_object.options &&
                        aFilterArgument.metadatum.metadata_type_object.options.taxonomy
                    ) ? aFilterArgument.metadatum.metadata_type_object.options.taxonomy : '',
            argType: aFilterArgument.arg_type ? aFilterArgument.arg_type : '',
            metadatumId: (aFilterArgument.filter && aFilterArgument.metadatum.metadatum_id) ? aFilterArgument.metadatum.metadatum_id : (aFilterArgument.metadatum.id || ''),
            metadatumName: (aFilterArgument.filter && aFilterArgument.filter.name) ? aFilterArgument.filter.name : (aFilterArgument.metadatum.name || ''),
            secondaryMetadatumId: (aFilterArgument.filter && aFilterArgument.filter.filter_type_options && aFilterArgument.filter.filter_type_options.secondary_filter_metadatum_id) ? aFilterArgument.filter.filter_type_options.secondary_filter_metadatum_id : '',
        }
    });
    state.filter_tags = filterTags;
};

/**
 * Commits `search/removeFilterTag` state changes.
 * @param {Object} state - Module state.
 * @param {*} filterTag - Mutation payload.
 * @returns {void} No return value.
 */
export const removeFilterTag = ( state, filterTag ) => {
    state.filter_tags = ( ! state.filter_tags ) ? [] : state.filter_tags;
    let index = state.filter_tags.findIndex( tag => tag.filterId == filterTag.filterId);

    if (index >= 0)
        state.filter_tags.splice(index, 1);
};

/**
 * Commits `search/cleanFilterTags` state changes.
 * @param {Object} state - Module state.
 * @returns {void} No return value.
 */
export const cleanFilterTags = ( state ) => {
    state.filter_tags = [];
};

/**
 * Commits `search/cleanMetaQueries` state changes.
 * @param {Object} state - Module state.
 * @returns {void} No return value.
 */
export const cleanMetaQueries = (state, { keepCollections }) => {
    if (keepCollections === true && Array.isArray(state.postquery.metaquery) )
        state.postquery.metaquery = state.postquery.metaquery.filter(aMetaQuery => aMetaQuery.key === 'collection_id');
    else
        state.postquery.metaquery = [];
};

/**
 * Commits `search/cleanTaxQueries` state changes.
 * @param {Object} state - Module state.
 * @returns {void} No return value.
 */
export const cleanTaxQueries = (state) => {
    state.postquery.taxquery = [];
};

/**
 * Commits `search/cleanFetchOnly` state changes.
 * @param {Object} state - Module state.
 * @returns {void} No return value.
 */
export const cleanFetchOnly = (state) => {
    delete state.postquery.fetch_only;
};

/**
 * Commits `search/setFacets` state changes.
 * @param {Object} state - Module state.
 * @param {*} facets - Mutation payload.
 * @returns {void} No return value.
 */
export const setFacets = (state, facets) => {
    state.facets = facets;
}

/**
 * Commits `search/setSelectedItems` state changes.
 * @param {Object} state - Module state.
 * @param {*} selectedItems - Mutation payload.
 * @returns {void} No return value.
 */
export const setSelectedItems = (state, selectedItems) => {
    for (let selecteditem of selectedItems) {
        let index = state.selecteditems.findIndex( item => item == selecteditem);
        if ( index < 0 )
            state.selecteditems.push(selecteditem);
    }
}

/**
 * Commits `search/cleanSelectedItems` state changes.
 * @param {Object} state - Module state.
 * @returns {void} No return value.
 */
export const cleanSelectedItems = (state) => {
    state.selecteditems = [];
}

/**
 * Commits `search/addSelectedItem` state changes.
 * @param {Object} state - Module state.
 * @param {*} selectedItem - Mutation payload.
 * @returns {void} No return value.
 */
export const addSelectedItem = (state, selectedItem) => {
    let index = state.selecteditems.findIndex( item => item == selectedItem);
    if ( index < 0 )
        state.selecteditems.push(selectedItem);
}

/**
 * Commits `search/removeSelectedItem` state changes.
 * @param {Object} state - Module state.
 * @param {*} selectedItem - Mutation payload.
 * @returns {void} No return value.
 */
export const removeSelectedItem = (state, selectedItem) => {
    let index = state.selecteditems.findIndex( item => item == selectedItem);
    if ( index >= 0 )
        state.selecteditems.splice(index, 1);
}

/**
 * Commits `search/setHighlightedItem` state changes.
 * @param {Object} state - Module state.
 * @param {*} itemId - Mutation payload.
 * @returns {void} No return value.
 */
export const setHighlightedItem = (state, itemId) => {
    state.highlightedItem = itemId;
}
