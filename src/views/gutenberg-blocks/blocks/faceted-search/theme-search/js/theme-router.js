import { createRouter, createWebHistory } from 'vue-router';
import ThemeItemsPage from '../theme-items-page.vue';

const themeRoutes = [
    // Catch-all route to handle any path
    {
        path: '/:catchAll(.*)*',
        component: ThemeItemsPage, // The component where you want to respond to changes
    }
];

export default createRouter ({
    history: createWebHistory(window.document.location.pathname),
    routes: themeRoutes
});