import * as actions from './actions.js';
import * as mutations from './mutations.js';
import * as getters from './getters.js';

/**
 * Vuex module `exposer`.
 * Namespace: `exposer`.
 * Purpose: stores available exposer integrations.
 */
const state = {
    availableExposers: []
};

export default {
    namespaced: true,
    state,
    actions,
    mutations,
    getters,
}
