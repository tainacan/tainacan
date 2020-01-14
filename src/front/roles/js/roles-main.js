import Vue from 'vue';
import store from '../../admin/js/store/store';
import router from './roles-router';
import VTooltip from 'v-tooltip';

import { I18NPlugin } from './wp-i18n-plugin';

import RolesPage from '../roles.vue';

Vue.use(I18NPlugin);
Vue.use(VTooltip);

// Changing title of pages
router.beforeEach((to, from, next) => {
    document.title = to.meta.title;
    if (next() != undefined)
        next();
});

new Vue({
    el: '#tainacan-roles-app',
    store,
    router,
    render: h => h(RolesPage)
});