import Vue from 'vue';

export const setPostQueryAttribute = ( state, { attr, value }) => {
    Vue.set( state.postquery, attr , value );
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

    let index = state.postquery.metaquery.findIndex( item => item.key === filter.metadatum_id);

    if ( index >= 0 ){
        Vue.set( state.postquery.metaquery, index, {
            key: filter.metadatum_id,
            value: filter.value,
            compare: filter.compare,
            type: filter.type
        } );
    } else {
        state.postquery.metaquery.push({
            key: filter.metadatum_id,
            value: filter.value,
            compare: filter.compare,
            type: filter.type
        });
    }
};

export const addTaxQuery = ( state, filter ) => {
    state.postquery.taxquery = ( ! state.postquery.taxquery || state.postquery.taxquery.length == undefined ) ? [] : state.postquery.taxquery;

    let index = state.postquery.taxquery.findIndex( item => item.taxonomy === filter.taxonomy);

    if ( index >= 0 ){
        Vue.set( state.postquery.taxquery, index, {
            taxonomy: filter.taxonomy,
            terms: filter.terms,
            compare: filter.compare
        } );
    } else {
        state.postquery.taxquery.push({
            taxonomy: filter.taxonomy,
            terms: filter.terms,
            compare: filter.compare
        });
    }
};

export const addFetchOnly = ( state, metadatum ) => {
    state.postquery.fetch_only = metadatum.replace(/,null/g, '');
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

        console.info(state.postquery.fetch_only_meta);
    }
};

export const removeMetaQuery = ( state, filter ) => {
    state.postquery.metaquery = ( ! state.postquery.metaquery ) ? [] : state.postquery.metaquery;

    let index = state.postquery.metaquery.findIndex( item => item.key == filter.metadatum_id);

    if (index >= 0) {
        state.postquery.metaquery.splice(index, 1);
    }
};

export const removeTaxQuery = ( state, filter ) => {
    let index = state.postquery.taxquery.findIndex( item => item.taxonomy == filter.taxonomy);

    if (index >= 0) {
        state.postquery.taxquery.splice(index, 1);
    }
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
    
    if (searchQuery != '') {
        state.postquery.search = searchQuery;
    } else {
        delete state.postquery.search;
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

export const setOrderByName = ( state, orderByName ) => {
    state.orderByName = orderByName;
};

export const addFilterTag = ( state, filterTag ) => {
    state.filter_tags = ( ! state.filter_tags) ? [] : state.filter_tags;

    let index = state.filter_tags.findIndex( tag => tag.filterId == filterTag.filterId);

    if ( index >= 0 ) {
        Vue.set(state.filter_tags, index, filterTag);
    } else {
        state.filter_tags.push(filterTag);
    }
};

export const removeFilterTag = ( state, filterTag ) => {
    state.filter_tags = ( ! state.filter_tags ) ? [] : state.filter_tags;

    let index = state.filter_tags.findIndex( tag => tag.filterId == filterTag.filterId);

    if (index >= 0) {
        state.filter_tags.splice(index, 1);
    }
};

export const cleanFilterTags = ( state ) => {
    state.filter_tags = [];
};

export const cleanMetaQueries = (state) => {
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