import * as actions from './actions';
import * as getters from './getters';
import * as mutations from './mutations';

const state = {
    postquery: {
        post_status: 'publish',
        post_type: [],
        metaquery: [],
        taxquery: []
    }
};

export default {
    namespaced: true,
    state,
    mutations,
    actions,
    getters
}