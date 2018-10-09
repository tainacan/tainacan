import * as actions from './actions';
import * as getters from './getters';
import * as mutations from './mutations';

const state = {
    postquery: {
        orderby: 'date',
        order: 'DESC',
        paged: 1,
        perpage: 12,
        status: '',
        search: '',
        post_type: [],
        metaquery: [],
        taxquery: [],
        fetch_only: {
            '0': 'thumbnail',
            'meta': [],
            '1': 'creation_date',
            '2': 'author_name' 
        },
        view_mode: 'table',
        admin_view_mode: 'table'
    },
    filter_tags: [],
    totalItems: 0,
    totalPages: 0
};

export default {
    namespaced: true,
    state,
    mutations,
    actions,
    getters
}