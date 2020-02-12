import * as actions from './actions.js'
import * as mutations from './mutations.js'
import * as getters from './getters.js'

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