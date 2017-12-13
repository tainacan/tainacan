import Vue from 'vue';
import Vuex from 'vuex';

import item from './modules/item/';
import collection from './modules/collection/';

Vue.use(Vuex);

export default new Vuex.Store({
    modules: {
        item,
        collection
    }
})