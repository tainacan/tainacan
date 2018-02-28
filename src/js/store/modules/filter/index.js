import * as actions from './actions';
import * as getters from './getters';
import * as mutations from './mutations';

const state = {
    post_query: {
        post_status: 'publish',
        post_type: [],
        meta_query: null,
        tax_query: null
    },
    meta_query: [],
    tax_query: []
};

export default {
    namespaced: true,
    state,
    mutations,
    actions,
    getters
}