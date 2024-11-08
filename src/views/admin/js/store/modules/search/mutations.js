

export const setPostQueryAttribute = ( state, { attr, value }) => {
    Object.assign(state.postquery, { [attr]: value });
};

export const removePostQueryAttribute = ( state, attr) => {
    delete state.postquery[`${attr}`];
};

export const setPostQuery = ( state, postquery ) => {
    state.postquery = postquery;
};

export const setAdvancedSearchQuery = (state, advancedSearchQuery) => {
    state.postquery.advancedSearch = advancedSearchQuery.advancedSearch;
    state.postquery.metaquery = Object.assign({}, advancedSearchQuery.metaquery);
    state.postquery.taxquery = Object.assign({}, advancedSearchQuery.taxquery);
};

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

export const addFetchOnly = ( state, metadatum ) => {
    state.postquery.fetch_only = metadatum
};
export const addFetchOnlyMeta = ( state, metadatum ) => {
    state.postquery.fetch_only_meta = metadatum;
};

export const removeFetchOnly = ( state, metadatum ) => {

    let fetch = state.postquery.fetch_only.split(',');

    for (let key in metadatum) {
        fetch.splice(key, 1);
    }

    state.postquery.fetch_only = fetch.toString();
};

export const removeFetchOnlyMeta = ( state, metadatum ) => {
    if(state.postquery.fetch_only_meta != undefined) {
        let fetch_meta = state.postquery.fetch_only_meta.split(',');

        let index = fetch_meta.findIndex((item) => item == metadatum);

        fetch_meta.splice(index, 1);

        state.postquery.fetch_only_meta = fetch_meta.toString();
    }
};

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

export const removePostIn = ( state ) => {
    delete state.postquery.postin;
};

export const setTotalItems = ( state, total ) => {
    state.totalItems = total;
};

export const setTotalPages = ( state, totalPages ) => {
    state.totalPages = totalPages;
};

export const setItemsPerPage = ( state, itemsPerPage ) => {
    state.itemsPerPage = itemsPerPage;
};

export const setSearchQuery = ( state, searchQuery ) => {
    
    if (searchQuery != '')
        state.postquery.search = searchQuery;
    else
        delete state.postquery.search;

    // In case a s parameter was passed
    delete state.postquery.s;
};

export const setSentenceMode = ( state, sentenceMode ) => {
    if (sentenceMode != true)
        Object.assign(state.postquery, { 'sentence': sentenceMode });
    else {
        Object.assign(state.postquery, { 'sentence': sentenceMode }); // Needed to trigger getter
        delete state.postquery.sentence;
    }
};

export const setStatus = ( state, status ) => {
    state.postquery.status = status;
};

export const setViewMode = ( state, viewMode ) => {
    state.postquery.view_mode = viewMode;
};

export const setAdminViewMode = ( state, adminViewMode ) => {
    state.postquery.admin_view_mode = adminViewMode;
};

export const addFilterTag = ( state, filterTag ) => {
    state.filter_tags = ( ! state.filter_tags) ? [] : state.filter_tags;
    let index = state.filter_tags.findIndex( tag => tag.filterId == filterTag.filterId);

    if (index >= 0)
        Object.assign(state.filter_tags, { [index]: filterTag });
    else
        state.filter_tags.push(filterTag);
};

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

export const removeFilterTag = ( state, filterTag ) => {
    state.filter_tags = ( ! state.filter_tags ) ? [] : state.filter_tags;
    let index = state.filter_tags.findIndex( tag => tag.filterId == filterTag.filterId);

    if (index >= 0)
        state.filter_tags.splice(index, 1);
};

export const cleanFilterTags = ( state ) => {
    state.filter_tags = [];
};

export const cleanMetaQueries = (state, { keepCollections }) => {
    if (keepCollections === true && Array.isArray(state.postquery.metaquery) )
        state.postquery.metaquery = state.postquery.metaquery.filter(aMetaQuery => aMetaQuery.key === 'collection_id');
    else
        state.postquery.metaquery = [];
};

export const cleanTaxQueries = (state) => {
    state.postquery.taxquery = [];
};

export const cleanFetchOnly = (state) => {
    delete state.postquery.fetch_only;
};

export const setFacets = (state, facets) => {
    state.facets = facets;
}

export const setSelectedItems = (state, selectedItems) => {
    for (let selecteditem of selectedItems) {
        let index = state.selecteditems.findIndex( item => item == selecteditem);
        if ( index < 0 )
            state.selecteditems.push(selecteditem);
    }
}

export const cleanSelectedItems = (state) => {
    state.selecteditems = [];
}

export const addSelectedItem = (state, selectedItem) => {
    let index = state.selecteditems.findIndex( item => item == selectedItem);
    if ( index < 0 )
        state.selecteditems.push(selectedItem);
}

export const removeSelectedItem = (state, selectedItem) => {
    let index = state.selecteditems.findIndex( item => item == selectedItem);
    if ( index >= 0 )
        state.selecteditems.splice(index, 1);
}

export const setHighlightedItem = (state, itemId) => {
    state.highlightedItem = itemId;
}
