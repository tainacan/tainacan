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
        fetch_only: 'thumbnail,creation_date,author_name',
        fetch_only_meta: '',
        view_mode: 'table',
        admin_view_mode: 'table'
    },
    filter_tags: [],
    totalItems: 0,
    totalPages: 0,
    itemsPerPage: 12, // Not the same as postquery.perpage as API may have limited it's value
    facets: {},
    orderByName: ''
};

export default {
    namespaced: true,
    state,
    mutations,
    actions,
    getters
}