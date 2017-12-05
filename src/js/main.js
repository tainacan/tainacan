import Vue from 'vue'
// include vue-custom-element plugin to Vue
import VueCustomElement from 'vue-custom-element';

Vue.use(VueCustomElement);

import Text from '../classes/field-types/text/Text.vue';
import Textarea from '../classes/field-types/textarea/Textarea.vue';
import Selectbox from '../classes/field-types/selectbox/Selectbox.vue';

Vue.customElement('tainacan-text', Text);
Vue.customElement('tainacan-textarea', Textarea);
Vue.customElement('tainacan-selectbox', Selectbox);