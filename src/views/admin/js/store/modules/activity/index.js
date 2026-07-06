import * as actions from './actions';
import * as getters from './getters';
import * as mutations from './mutations';

/**
 * Vuex module `activity`.
 * Namespace: `activity`.
 * Purpose: stores activity logs, selected activity details, and activity users.
 */
const state = {
    activities: [],
    activity: {},
    eventTitle: String,
};

export default {
    namespaced: true,
    state,
    mutations,
    actions,
    getters
}