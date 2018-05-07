import Vue from 'vue';

export const setPostQueryAttribute = ( state, { attr, value }) => {
    Vue.set( state.postquery, attr , value );
};

export const setPostQuery = ( state, postquery ) => {
    state.postquery = postquery;
};

export const addMetaQuery = ( state, filter ) => {
    state.postquery.metaquery = ( ! state.postquery.metaquery ) ? [] : state.postquery.metaquery;
    let index = state.postquery.metaquery.findIndex( item => item.key === filter.field_id);
    if ( index >= 0 ){
        Vue.set( state.postquery.metaquery, index, {
            key: filter.field_id,
            value: filter.value,
            compare: filter.compare,
            type: filter.type
        } );
    }else{
        state.postquery.metaquery.push({
            key: filter.field_id,
            value: filter.value,
            compare: filter.compare,
            type: filter.type
        });
    }
};

export const addTaxQuery = ( state, filter ) => {
    state.postquery.taxquery = ( ! state.postquery.taxquery ) ? [] : state.postquery.taxquery;
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

export const addFetchOnly = ( state, field ) => {
    state.postquery.fechonly = ( ! state.postquery.fechonly ) ? [{'meta': []}] : state.postquery.fechonly;
    let index = state.postquery.fechonly.findIndex( item => item === field);
    if ( index >= 0 ){
        Vue.set( state.postquery.fechonly, index, field);
    } else {
        state.postquery.fechonly.push(field);
    }
};
export const addFetchOnlyMeta = ( state, field ) => {
    state.postquery.fechonly['meta'] = ( ! state.postquery.fechonly['meta'] ) ? [] : state.postquery.fechonly['meta'];
    let index = state.postquery.fechonly['meta'].findIndex( item => item === field);
    if ( index >= 0 ){
        Vue.set( state.postquery.fechonly['meta'], index, field);
    } else {
        state.postquery.fechonly['meta'].push(field);
    }
};

export const removeMetaQuery = ( state, filter ) => {
    let index = state.postquery.metaquery.findIndex( item => item.key === filter.field_id);
    if (index >= 0) {
        state.postquery.metaquery.splice(index, 1);
    }
};

export const removeTaxQuery = ( state, filter ) => {
    let index = state.postquery.taxquery.findIndex( item => item.taxonomy === filter.taxonomy);
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

export const setSearchQuery = ( state, searchQuery ) => {
    state.postquery.search = searchQuery;
};

export const setStatus = ( state, status ) => {
    state.status = status;
};