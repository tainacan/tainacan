import Vue from 'vue'

// include vue-custom-element plugin to Vue
import VueCustomElement from 'vue-custom-element';
import { eventBus } from './event-bus-web-components';
import { eventFilterBus } from './event-bus-filters';
import Buefy from 'buefy'


Vue.use(Buefy)

Vue.use(VueCustomElement);

import Text from '../classes/field-types/text/Text.vue';
import Textarea from '../classes/field-types/textarea/Textarea.vue';
import Selectbox from '../classes/field-types/selectbox/Selectbox.vue';
import Checkbox from '../classes/field-types/checkbox/Checkbox.vue';
import Radio from '../classes/field-types/radio/Radio.vue';
import Numeric from '../classes/field-types/numeric/Numeric.vue';
import Date from '../classes/field-types/date/Date.vue';
import Relationship from '../classes/field-types/relationship/Relationship.vue';

import FormRelationship from '../classes/field-types/relationship/FormRelationship.vue';

import FilterCustomInterval from '../classes/filter-types/custom-interval/CustomInterval.vue';
import FilterSelectbox from '../classes/filter-types/selectbox/Selectbox.vue';
import FilterAutocomplete from '../classes/filter-types/autocomplete/Autocomplete.vue';
import FilterCheckbox from '../classes/filter-types/checkbox/Checkbox.vue';
import FilterTaginput from '../classes/filter-types/taginput/Taginput.vue';

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

/* Form */

Vue.customElement('tainacan-form-relationship', FormRelationship);
eventBus.registerComponent( 'tainacan-form-relationship' );

/* Filters */

Vue.customElement('tainacan-filter-custom-interval', FilterCustomInterval);
eventFilterBus.registerComponent( 'tainacan-filter-custom-interval' );

Vue.customElement('tainacan-filter-selectbox', FilterSelectbox);
eventFilterBus.registerComponent( 'tainacan-filter-selectbox' );


Vue.customElement('tainacan-filter-autocomplete', FilterAutocomplete);
eventFilterBus.registerComponent( 'tainacan-filter-autocomplete' );

Vue.customElement('tainacan-filter-checkbox', FilterCheckbox);
eventFilterBus.registerComponent( 'tainacan-filter-checkbox' );

Vue.customElement('tainacan-filter-taginput', FilterTaginput);
eventFilterBus.registerComponent( 'tainacan-filter-taginput' );

eventFilterBus.listener();
