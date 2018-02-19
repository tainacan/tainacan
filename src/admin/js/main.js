import Vue from 'vue'
import Buefy from 'buefy'

Vue.use(Buefy);

import AdminPage from '../admin.vue'
import store from '../../js/store/store'
import router from './router'

router.beforeEach((to, from, next) => {
    document.title = to.meta.title
    next()
});

//------------------------------------------------
// FROM DEV 

// include vue-custom-element plugin to Vue
import Text from '../../classes/field-types/text/Text.vue';
import Textarea from '../../classes/field-types/textarea/Textarea.vue';
import Selectbox from '../../classes/field-types/selectbox/Selectbox.vue';
import Checkbox from '../../classes/field-types/checkbox/Checkbox.vue';
import Radio from '../../classes/field-types/radio/Radio.vue';
import Numeric from '../../classes/field-types/numeric/Numeric.vue';
import Date from '../../classes/field-types/date/Date.vue';
import Relationship from '../../classes/field-types/relationship/Relationship.vue';
import TaincanFormItem from '../../classes/field-types/tainacan-form-item.vue';

Vue.component('tainacan-text', Text);
Vue.component('tainacan-textarea', Textarea);
Vue.component('tainacan-selectbox', Selectbox);
Vue.component('tainacan-checkbox', Checkbox);
Vue.component('tainacan-radio', Radio);
Vue.component('tainacan-numeric', Numeric);
Vue.component('tainacan-date', Date);
Vue.component('tainacan-relationship', Relationship);

Vue.component('tainacan-form-item', TaincanFormItem);

//------------------------------------------------
// I18N DIRECTIVE
const I18NPlugin = {};
I18NPlugin.install = function (Vue, options = {}) {
    
    Vue.prototype.$i18n = {
        getString: function (component, key) {
            if (wp_settings.i18n[component] == null || wp_settings.i18n[component] == undefined)
                return "ERROR: Invalid i18n component!"

            let string = wp_settings.i18n[component][key];
            return (string != undefined && string != null && string != '' ) ? string : "ERROR: Invalid i18n key!";
        }
    }

}  

Vue.use(I18NPlugin);

new Vue({
    el: '#tainacan-admin-app',
    store,
    router,
    render: h => h(AdminPage)
});