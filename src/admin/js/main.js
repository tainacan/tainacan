import Vue from 'vue'

import ElementUI from 'element-ui'

import AdminPage from '../admin.vue'
//import { eventBus } from '../../js/event-bus-web-components'
import store from '../../js/store/store'
import router from './router'

//------------------------------------------------
// FROM DEV 

// include vue-custom-element plugin to Vue
import { eventBus } from '../../js/event-bus-web-components';


import Text from '../../classes/field-types/text/Text.vue';
import Textarea from '../../classes/field-types/textarea/Textarea.vue';
import Selectbox from '../../classes/field-types/selectbox/Selectbox.vue';
import Checkbox from '../../classes/field-types/checkbox/Checkbox.vue';
import Radio from '../../classes/field-types/radio/Radio.vue';
import Numeric from '../../classes/field-types/numeric/Numeric.vue';
import Date from '../../classes/field-types/date/Date.vue';


Vue.component('tainacan-text', Text);
Vue.component('tainacan-textarea', Textarea);
Vue.component('tainacan-selectbox', Selectbox);
Vue.component('tainacan-checkbox', Checkbox);
Vue.component('tainacan-radio', Radio);
Vue.component('tainacan-numeric', Numeric);
eventBus.registerComponent( 'tainacan-numeric' );


Vue.component('tainacan-date', Date);
eventBus.registerComponent( 'tainacan-date' );

eventBus.listener();

//------------------------------------------------

Vue.use(ElementUI);

// eventBus.listener();

new Vue({
    el: '#tainacan-admin-app',
    store,
    router,
    render: h => h(AdminPage)
});