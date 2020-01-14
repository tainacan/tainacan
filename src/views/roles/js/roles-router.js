import Vue from 'vue';
import VueRouter from 'vue-router'
import qs from 'qs';

import RolesList from '../pages/roles-list.vue';
import RoleEditionForm from '../pages/role-edition-form.vue'

const { __ } = wp.i18n;

Vue.use(VueRouter);

const routes = [
    { path: '/', redirect:'/roles' },
    { path: '/roles', name: 'RolesList', component: RolesList, meta: { title: __('Tainacan User Roles') } },
    { path: '/roles/:roleSlug', name: 'RoleEditionForm', component: RoleEditionForm, meta: { title: __('Editing User Role') } },

    { path: '*', redirect: '/'}
];

export default new VueRouter ({
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