import Vue from 'vue'

import ElementUI from 'element-ui'

import AdminPage from '../admin.vue'
//import { eventBus } from '../../js/event-bus-web-components'
import store from '../../js/store/store'
import router from './router'

Vue.use(ElementUI);

// eventBus.listener();

new Vue({
    el: '#tainacan-admin-app',
    store,
    router,
    render: h => h(AdminPage)
});