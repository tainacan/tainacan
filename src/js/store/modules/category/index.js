import * as actions from './actions';
import * as getters from './getters';
import * as mutations from './mutations';

const state = {
    categories: [],
    category: {},
    categoryName: String,
};

export default {
    namespaced: true,
    state,
    mutations,
    actions,
    getters
}