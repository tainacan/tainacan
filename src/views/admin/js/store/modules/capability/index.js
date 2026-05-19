import * as actions from './actions';
import * as getters from './getters';
import * as mutations from './mutations';

/**
 * Vuex module `capability`.
 * Namespace: `capability`.
 * Purpose: stores capabilities, roles, and admin UI options.
 */
const state = {
    capabilities: [],
    capability: {},
    roles: [],
    role: {},
    adminUIOptions: {}
};

export default {
    namespaced: true,
    state,
    mutations,
    actions,
    getters
}