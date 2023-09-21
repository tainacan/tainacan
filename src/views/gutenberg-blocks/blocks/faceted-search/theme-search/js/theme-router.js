import { createRouter, createWebHistory } from 'vue-router';
import qs from 'qs';

const themeRoutes = [];

export default createRouter ({
    history: createWebHistory(window.document.location.pathname),
    routes: themeRoutes,
    // set custom query resolver
    parseQuery(query) {
        return qs.parse(query);
    },
    stringifyQuery(query) {
        let result = qs.stringify(query);
        return result ? result : '';
    }
});