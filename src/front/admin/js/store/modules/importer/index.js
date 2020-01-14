import * as actions from './actions';
import * as getters from './getters';
import * as mutations from './mutations';

const state = {
    importer: {},
    available_importers: [],
    importer_file: {},
    importer_source_info: {},
    importer_mapping: []
};

export default {
    namespaced: true,
    state,
    mutations,
    actions,
    getters
}