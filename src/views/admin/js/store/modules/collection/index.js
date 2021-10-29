import * as actions from './actions';
import * as getters from './getters';
import * as mutations from './mutations';

const state = {
    items: [],
    isFromAdvancedSearch: false,
    itemsListTemplate: '',
    collections: [],
    collection: null,
    attachments: [],
    files: [],
    repositoryTotalCollections: '',
    collectionTaxonomies: {}
};

export default {
    namespaced: true,
    state,
    mutations,
    actions,
    getters
}