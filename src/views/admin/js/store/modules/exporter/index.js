import * as actions from './actions.js';
import * as mutations from './mutations.js';
import * as getters from './getters.js';

/**
 * Vuex module `exporter`.
 * Namespace: `exporter`.
 * Purpose: stores exporter session configuration and background process tracking.
 */
const state = {
    exporterSession: {},
    backGroundProcessID: ''
};

export default {
    namespaced: true,
    state,
    actions,
    mutations,
    getters,
}
