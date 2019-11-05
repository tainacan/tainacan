import * as actions from './actions';
import * as getters from './getters';
import * as mutations from './mutations';

const state = {
    capabilities: [],
    capability: {},
    capabilityName: String,
    roles: []
};

export default {
    namespaced: true,
    state,
    mutations,
    actions,
    getters
}