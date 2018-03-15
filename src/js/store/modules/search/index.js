import * as actions from './actions';
import * as getters from './getters';
import * as mutations from './mutations';

const state = {
    postquery: {
        post_type: [],
        metaquery: [],
        taxquery: []
    },
    totalItems: 0
};

export default {
    namespaced: true,
    state,
    mutations,
    actions,
    getters
}