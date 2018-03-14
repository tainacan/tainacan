import Vue from 'vue';

// METAQUERIES ----------------------------------------------------------------------------
export const setPostQueryAttribute = ( state, { attr, value }) => {
    Vue.set( state.postquery, attr , value );
};

export const setPostQuery = ( state, postquery ) => {
    state.postquery = postquery;
};
 
export const addMetaQuery = ( state, filter ) => {
    let index = state.postquery.metaquery.findIndex( item => item.key === filter.field_id);
    if ( index >= 0){
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

export const removeMetaQuery = ( state, filter ) => {
    let index = state.postquery.metaquery.findIndex( item => item.key === filter.field_id);
    if (index >= 0) {
        state.postquery.metaquery.splice(index, 1);
    }
}

// FILTERS ------------------------------------------------------------------------
export const deleteFilter = ( state, filter ) => {
    let index = state.filters.findIndex(deletedFilter => deletedFilter.id === filter.id);
    if (index >= 0) {
        state.filters.splice(index, 1);
    }
}

export const setSingleFilter = (state, filter) => {
    let index = state.filters.findIndex(newFilter => newFilter.id === filter.id);
    
    if ( index >= 0){
        Vue.set( state.filters, index, filter );
    } else {
        state.filters.push( filter );
    }
}

export const setFilters = (state, filters) => {
    state.filters = filters;
}

export const setFilterTypes = (state, filterTypes) => {
    state.filterTypes = filterTypes;
}