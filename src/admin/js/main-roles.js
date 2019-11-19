import Vue from 'vue';
import store from '../../js/store/store';
import router from './router';

import RolesPage from '../roles.vue';
new Vue({
    el: '#tainacan-roles-app',
    store,
    router,
    render: h => h(RolesPage)
});