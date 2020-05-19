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

export const updateFiltersOrderFromCollection = (state, filtersOrder) => {
    for (let i = 0; i < state.filters.length; i++) {
        let updatedFilterIndex = filtersOrder.findIndex(aFilter => aFilter.id == state.filters[i].id);
        if (updatedFilterIndex >= 0)
            state.filters[i].enabled = filtersOrder[updatedFilterIndex].enabled;  
    }
}

export const setFilterTypes = (state, filterTypes) => {
    state.filterTypes = filterTypes;
}

// export const setRepositoryCollectionFilters = (state, repositoryCollectionFilters) => {
//     state.repositoryCollectionFilters = repositoryCollectionFilters;
// }

export const setRepositoryCollectionFilters = (state, { collectionName, repositoryCollectionFilters }) => {
    if (collectionName != undefined)
        Vue.set(state.repositoryCollectionFilters, collectionName, repositoryCollectionFilters);
    else
        Vue.set(state.repositoryCollectionFilters, 'repository-filters', repositoryCollectionFilters);
}

export const clearRepositoryCollectionFilters = (state) => {
    state.repositoryCollectionFilters = {};
}

export const setTaxonomyFilters = (state, taxonomyFilters) => {
    state.taxonomyFilters = taxonomyFilters;
}

export const setTaxonomyFiltersForCollection = (state, { collectionName, taxonomyFilters }) => {
    if (collectionName != undefined)
        Vue.set(state.taxonomyFilters, collectionName, taxonomyFilters);
    else
        Vue.set(state.taxonomyFilters, 'repository-filters', taxonomyFilters);
}

export const clearTaxonomyFilters = (state) => {
    state.taxonomyFilters = {};
}