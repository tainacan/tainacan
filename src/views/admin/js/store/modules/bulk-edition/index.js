import * as actions from './actions.js'
import * as mutations from './mutations.js'
import * as getters from './getters.js'

/**
 * Vuex module `bulk-edition`.
 * Namespace: `bulkedition`.
 * Purpose: stores bulk and sequence edit group state and update timestamps.
 */
const state = {
    group: null,
    itemIdInSequence: null,
    lastUpdated: ''
};

export default {
    namespaced: true,
    state,
    mutations,
    actions,
    getters,
};