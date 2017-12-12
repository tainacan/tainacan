import * as actions from './actions';
import * as getters from './getters';
import * as mutations from './mutations';

const state = {
    item: null,
    metadata:[],
    isInit:false,
};


export default {
    namespaced: true,
    state,
    mutations,
    actions,
    getters
}