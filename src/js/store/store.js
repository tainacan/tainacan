import Vue from 'vue';
import Vuex from 'vuex';

import item from './modules/item/';
import collection from './modules/collection/';
import fields from './modules/fields/';
import filter from './modules/filter/';
import search from './modules/search/';
import category from './modules/category/';
import event from './modules/event';

Vue.use(Vuex);

export default new Vuex.Store({
    /*
        In strict mode, whenever Vuex state is mutated outside of mutation handlers, an error will be thrown

        Do not enable strict mode when deploying for production! Strict mode runs a synchronous deep watcher
        on the state tree for detecting inappropriate mutations, and it can be quite expensive when you make
        large amount of mutations to the state. Make sure to turn it off in production to avoid the performance cost.
    */
    strict: process.env.NODE_ENV !== 'production',
    modules: {
        item,
        collection,
        fields,
        filter,
        search,
        category,
        event,
    }
})