import Vue from 'vue';
import Vuex from 'vuex';

import item from './modules/item/item';
import collection from './modules/collection/collection';

Vue.use(Vuex);

export default new Vuex.Store({
    modules: {
        item,
        collection
    }
})