import Vue from 'vue'

// include vue-custom-element plugin to Vue
import VueCustomElement from 'vue-custom-element';
import { eventBus } from './event-bus';

Vue.use(VueCustomElement);

import Text from '../classes/field-types/text/Text.vue';
import Textarea from '../classes/field-types/textarea/Textarea.vue';
import Selectbox from '../classes/field-types/selectbox/Selectbox.vue';
import Checkbox from '../classes/field-types/checkbox/Checkbox.vue';
import Radio from '../classes/field-types/radio/Radio.vue';
import Numeric from '../classes/field-types/numeric/Numeric.vue';
import Date from '../classes/field-types/date/Date.vue';


Vue.customElement('tainacan-text', Text);
Vue.customElement('tainacan-textarea', Textarea);
Vue.customElement('tainacan-selectbox', Selectbox);
Vue.customElement('tainacan-checkbox', Checkbox);
Vue.customElement('tainacan-radio', Radio);
Vue.customElement('tainacan-numeric', Numeric);
eventBus.registerComponent( 'tainacan-numeric' );

Vue.customElement('tainacan-date', Date);

eventBus.listen();