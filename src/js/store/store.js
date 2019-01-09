import Vue from 'vue';
import Vuex from 'vuex';

import item from './modules/item/';
import collection from './modules/collection/';
import metadata from './modules/metadata/';
import filter from './modules/filter/';
import search from './modules/search/';
import taxonomy from './modules/taxonomy/';
import activity from './modules/activity';
import importer from './modules/importer';
import bgprocess from './modules/bgprocess';
import bulkedition from './modules/bulk-edition';
import exporter from './modules/exporter';
import exposer from './modules/exposer';

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
        metadata,
        filter,
        search,
        taxonomy,
        activity,
        importer,
        bgprocess,
        bulkedition,
        exporter,
        exposer
    }
})