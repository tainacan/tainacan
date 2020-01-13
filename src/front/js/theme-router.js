import Vue from 'vue';
import VueRouter from 'vue-router'
import qs from 'qs';

Vue.use(VueRouter);

const themeRoutes = [];

export default new VueRouter ({
    themeRoutes,
    // set custom query resolver
    parseQuery(query) {
        return qs.parse(query);
    },
    stringifyQuery(query) {
        let result = qs.stringify(query);

        return result ? ('?' + result) : '';
    }
});