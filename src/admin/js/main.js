import Vue from 'vue'

import ElementUI from 'element-ui'
import 'element-ui/lib/theme-chalk/index.css'

import AdminPage from '../html/admin.vue'
//import { eventBus } from '../../js/event-bus-web-components'
import store from '../../js/store/store'

Vue.use(ElementUI);

// eventBus.listener();

new Vue({
    el: '#tainacan-admin-app',
    store,
    render: h => h(AdminPage)
});