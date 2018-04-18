import Vue from 'vue';

// FILTERS ------------------------------------------------------------------------
export const deleteFilter = ( state, filter ) => {
    let index = state.filters.findIndex(deletedFilter => deletedFilter.id === filter.id);
    if (index >= 0) {
        state.filters.splice(index, 1);
    }
}

export const addTemporaryFilter = ( state, filter) => {
    state.filters.push(filter);
}

export const deleteTemporaryFilter = ( state, index) => {
    state.filters.splice(index, 1);
}

export const setSingleFilter = (state, { filter, index}) => {
    Vue.set( state.filters, index, filter );
}

export const setFilters = (state, filters) => {
    state.filters = filters;
}

export const setFilterTypes = (state, filterTypes) => {
    state.filterTypes = filterTypes;
}