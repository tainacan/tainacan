import Vue from 'vue';
import store from '../../js/store/store';
import router from './roles-router';

import { I18NPlugin } from './wp-i18n-plugin';

import RolesPage from '../roles.vue';

Vue.use(I18NPlugin);

new Vue({
    el: '#tainacan-roles-app',
    store,
    router,
    render: h => h(RolesPage)
});