import { createRouter, createWebHistory } from 'vue-router';
import ThemeSearch from '../../theme.vue';

const themeRoutes = [
    // Catch-all route to handle any path
    {
        path: '/:catchAll(.*)*',
        component: ThemeSearch, // The component where you want to respond to changes
    }
];

export default createRouter ({
    history: createWebHistory(window.document.location.pathname),
    routes: themeRoutes
});