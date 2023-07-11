import { createRouter, createWebHashHistory } from 'vue-router';
import qs from 'qs';
import ReportsList from '../pages/reports-list.vue';

const { __ } = wp.i18n;

const routes = [
    { path: '/', redirect:'/reports' },
    { path: '/reports', name: 'ReportsList', component: ReportsList, meta: { title: __('Tainacan Reports') } },
];

export default createRouter({
    routes,
    history: createWebHashHistory(),
    // set custom query resolver
    parseQuery(query) {
        return qs.parse(query);
    },
    stringifyQuery(query) {
        let result = qs.stringify(query);

        return result ? ('?' + result) : '';
    }
});