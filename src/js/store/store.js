import Vue from 'vue';
import Vuex from 'vuex';

import item from './modules/item';

Vue.use(Vuex);

export default new Vuex.Store({
    modules: {
        item
    }
})