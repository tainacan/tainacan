import { createRouter, createWebHistory } from 'vue-router';
import ThemeSearch from '../../theme.vue';
import qs from 'qs';

const themeRoutes = [
    // Catch-all route to handle any path
    {
        path: '/:catchAll(.*)*',
        component: ThemeSearch, // The component where you want to respond to changes
        props: true,
        meta: { isOnTheme: true }
    }
];

export default createRouter ({
    history: createWebHistory(window.document.location.pathname),
    routes: themeRoutes,
    // Set custom query resolver. Important for dealing with nested query params such as taxquery objects.
    parseQuery(query) {
        return qs.parse(query);
    },
    stringifyQuery(query) {
        let result = qs.stringify(query);
        return result ? result : '';
    }
});