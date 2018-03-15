import Vue from 'vue';
import Vuex from 'vuex';

import item from './modules/item/';
import collection from './modules/collection/';
import fields from './modules/fields/';
import filter from './modules/filter/';
import search from './modules/search/';

Vue.use(Vuex);

export default new Vuex.Store({
    modules: {
        item,
        collection,
        fields,
        filter,
        search
    }
})