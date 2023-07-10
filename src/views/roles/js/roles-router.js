import { createRouter } from 'vue-router';
import qs from 'qs';

import RolesList from '../pages/roles-list.vue';
import RoleEditionForm from '../pages/role-edition-form.vue'

const { __ } = wp.i18n;

const routes = [
    { path: '/', redirect:'/roles' },
    { path: '/roles', name: 'RolesList', component: RolesList, meta: { title: __('Tainacan User Roles') } },
    { path: '/roles/:roleSlug', name: 'RoleEditionForm', component: RoleEditionForm, meta: { title: __('Editing User Role') } },
];

export default createRouter({
    routes,
    // set custom query resolver
    parseQuery(query) {
        return qs.parse(query);
    },
    stringifyQuery(query) {
        let result = qs.stringify(query);

        return result ? ('?' + result) : '';
    }
});