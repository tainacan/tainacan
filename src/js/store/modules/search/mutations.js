import Vue from 'vue';

export const setPostQueryAttribute = ( state, { attr, value }) => {
    Vue.set( state.postquery, attr , value );
};

export const cleanPostQueryAttribute = ( state, { attr }) => {
    Vue.set( state.postquery, attr , null );
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
    } else{
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
    }else{
        state.postquery.taxquery.push({
            taxonomy: filter.taxonomy,
            terms: filter.terms,
            compare: filter.compare
        });
    }
};

export const addFetchOnly = ( state, metadatum ) => {
    state.postquery.fetch_only = ( ! state.postquery.fetch_only ) ? { '0': 'thumbnail', 'meta': [], '1': 'creation_date', '2': 'author_name' } : state.postquery.fetch_only;

    for (let key in metadatum) {
        Vue.set(state.postquery.fetch_only, `${key}`, metadatum[key]);
    }

};
export const addFetchOnlyMeta = ( state, metadatum ) => {
    state.postquery.fetch_only = ( ! state.postquery.fetch_only ) ? { '0': 'thumbnail', 'meta': [], '1': 'creation_date', '2': 'author_name' } : state.postquery.fetch_only;
   // console.log(state.postquery.fetch_only);
    //console.log(state.postquery.fetch_only['meta']);
    state.postquery.fetch_only['meta'] = ( ! state.postquery.fetch_only['meta'] ) ? [] : state.postquery.fetch_only['meta'];
    let index = state.postquery.fetch_only['meta'].findIndex( item => item == metadatum);
    if ( index >= 0 ){    
        state.postquery.fetch_only['meta'][index] = metadatum;
    } else {
        state.postquery.fetch_only['meta'].push(metadatum);
    }
    //console.log(state.postquery.fetch_only['meta']);
    //console.log("----------------------------");
};

export const removeFetchOnly = ( state, metadatum ) => {
    for (let key in metadatum) {
        delete state.postquery.fetch_only[key];
    }
};

export const removeFetchOnlyMeta = ( state, metadatum ) => {
    if(state.postquery.fetch_only['meta'] != undefined) {
        let index = state.postquery.fetch_only['meta'].findIndex( item => item == metadatum);
        if (index >= 0) {
            state.postquery.fetch_only['meta'].splice(index, 1);
        }
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

export const removePostQueryAttribute = ( state, attribute) => {
    Vue.set( state.postquery, attribute , '');  
};

export const setTotalItems = ( state, total ) => {
    state.totalItems = total;
};

export const setTotalPages = ( state, totalPages ) => {
    state.totalPages = totalPages;
};

export const setSearchQuery = ( state, searchQuery ) => {
    
    if (searchQuery != '')
        state.postquery.search = searchQuery;
    else    
        state.postquery.search = undefined;
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
    if ( index >= 0 )
        Vue.set( state.filter_tags, index, filterTag );
    else
        state.filter_tags.push(filterTag);
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
    state.postquery.fetch_only = undefined;
};