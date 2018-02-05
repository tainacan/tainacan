import Vue from 'vue'

// include vue-custom-element plugin to Vue
import VueCustomElement from 'vue-custom-element';
import ElementUI from 'element-ui'
import { eventBus } from './event-bus-web-components';

Vue.use(ElementUI);
Vue.use(VueCustomElement);

import Text from '../classes/field-types/text/Text.vue';
import Textarea from '../classes/field-types/textarea/Textarea.vue';
import Selectbox from '../classes/field-types/selectbox/Selectbox.vue';
import Checkbox from '../classes/field-types/checkbox/Checkbox.vue';
import Radio from '../classes/field-types/radio/Radio.vue';
import Numeric from '../classes/field-types/numeric/Numeric.vue';
import Date from '../classes/field-types/date/Date.vue';
import Relationship from '../classes/field-types/relationship/Relationship.vue';


Vue.customElement('tainacan-text', Text);
eventBus.registerComponent( 'tainacan-text' );

Vue.customElement('tainacan-textarea', Textarea);
eventBus.registerComponent( 'tainacan-textarea' );

Vue.customElement('tainacan-selectbox', Selectbox);
eventBus.registerComponent( 'tainacan-selectbox' );

Vue.customElement('tainacan-checkbox', Checkbox);
eventBus.registerComponent( 'tainacan-checkbox' );

Vue.customElement('tainacan-radio', Radio);
eventBus.registerComponent( 'tainacan-radio' );

Vue.customElement('tainacan-numeric', Numeric);
eventBus.registerComponent( 'tainacan-numeric' );

Vue.customElement('tainacan-date', Date);
eventBus.registerComponent( 'tainacan-date' );

Vue.customElement('tainacan-relationship', Relationship);
eventBus.registerComponent( 'tainacan-relationship' );

eventBus.listener();