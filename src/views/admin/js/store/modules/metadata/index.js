import * as actions from './actions';
import * as getters from './getters';
import * as mutations from './mutations';

/**
 * Vuex module `metadata`.
 * Namespace: `metadata`.
 * Purpose: stores metadata definitions, sections, and related type metadata.
 */
const state = {
    metadata: [],
    metadatumTypes: [],
    metadatumMappers: [],
    metadataSections: []
};

export default {
    namespaced: true,
    state,
    mutations,
    actions,
    getters
}