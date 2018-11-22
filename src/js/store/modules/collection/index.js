import * as actions from './actions';
import * as getters from './getters';
import * as mutations from './mutations';

const state = {
    items: [],
    isFromAdvancedSearch: false,
    itemsListTemplate: '',
    collections: [],
    collection: null,
    collectionName: '',
    collectionURL: '',
    attachments: [],
    collectionCommentStatus: '',
    collectionAllowComments: '',
    files: [],
    collectionTotalItems: {},
    repositoryTotalCollections: '',
};

export default {
    namespaced: true,
    state,
    mutations,
    actions,
    getters
}