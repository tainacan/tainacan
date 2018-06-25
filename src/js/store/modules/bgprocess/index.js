import * as actions from './actions';
import * as getters from './getters';
import * as mutations from './mutations';

const state = {
    bg_processes: [],
    bg_process: {},
    log: {},
    error_log: {}
};


export default {
    namespaced: true,
    state,
    mutations,
    actions,
    getters
}