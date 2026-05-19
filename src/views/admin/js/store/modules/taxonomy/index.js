import * as actions from './actions';
import * as getters from './getters';
import * as mutations from './mutations';

/**
 * Vuex module `taxonomy`.
 * Namespace: `taxonomy`.
 * Purpose: stores taxonomy entities, term lists, and taxonomy names.
 */
const state = {
    taxonomies: [],
    taxonomy: {},
    taxonomyName: String,
    terms: [],
    repositoryTotalTaxonomies: ''
};

export default {
    namespaced: true,
    state,
    mutations,
    actions,
    getters
}