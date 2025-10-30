import * as actions from './actions';
import * as getters from './getters';
import * as mutations from './mutations';

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