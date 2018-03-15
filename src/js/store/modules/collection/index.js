import * as actions from './actions';
import * as getters from './getters';
import * as mutations from './mutations';

const state = {
    items: [],
    totalItems: 0,
    collections: [],
    collection: null,
    collectionName: ''
};

export default {
    namespaced: true,
    state,
    mutations,
    actions,
    getters
}