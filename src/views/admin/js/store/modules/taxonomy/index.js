import * as actions from './actions';
import * as getters from './getters';
import * as mutations from './mutations';

const state = {
    taxonomies: [],
    taxonomy: {},
    taxonomyName: String,
    terms: [],
    repositoryTotalTaxonomies: ''
};

export default {
    namespaced: true,
    state,
    mutations,
    actions,
    getters
}