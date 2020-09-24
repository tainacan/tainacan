import * as actions from './actions';
import * as getters from './getters';
import * as mutations from './mutations';

const state = {
    item: [],
    itemMetadata: [],
    error: [],
    itemTitle: '',
    attachment: {},
    attachments: [],
    lastUpdated: '',
    comment_status: '',
    totalAttachments: 0,
    itemSubmission: {},
    itemSubmissionMetadata: []
};


export default {
    namespaced: true,
    state,
    mutations,
    actions,
    getters
}