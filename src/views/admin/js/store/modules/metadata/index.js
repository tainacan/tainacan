import * as actions from './actions';
import * as getters from './getters';
import * as mutations from './mutations';

const state = {
    metadata: [],
    metadatumTypes: [],
    metadatumMappers: [],
};

export default {
    namespaced: true,
    state,
    mutations,
    actions,
    getters
}