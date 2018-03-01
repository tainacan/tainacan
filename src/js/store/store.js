import Vue from 'vue';
import Vuex from 'vuex';

import item from './modules/item/';
import collection from './modules/collection/';
import filter from './modules/filter/';

Vue.use(Vuex);

export default new Vuex.Store({
    modules: {
        item,
        collection,
        filter
    }
})