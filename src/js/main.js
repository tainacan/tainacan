import Vue from 'vue'
import Buefy from 'buefy'

// include vue-custom-element plugin to Vue
import VueCustomElement from 'vue-custom-element';
import { eventBus } from './event-bus-web-components';
import { eventSearchBus } from './event-search-bus';

Vue.use(Buefy);

Vue.use(VueCustomElement);

import Text from '../classes/field-types/text/Text.vue';
import Textarea from '../classes/field-types/textarea/Textarea.vue';
import Selectbox from '../classes/field-types/selectbox/Selectbox.vue';
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
eventSearchBus.registerComponent( 'tainacan-filter-custom-interval' );

Vue.customElement('tainacan-filter-selectbox', FilterSelectbox);
eventSearchBus.registerComponent( 'tainacan-filter-selectbox' );


Vue.customElement('tainacan-filter-autocomplete', FilterAutocomplete);
eventSearchBus.registerComponent( 'tainacan-filter-autocomplete' );

Vue.customElement('tainacan-filter-checkbox', FilterCheckbox);
eventSearchBus.registerComponent( 'tainacan-filter-checkbox' );

Vue.customElement('tainacan-filter-taginput', FilterTaginput);
eventSearchBus.registerComponent( 'tainacan-filter-taginput' );

eventSearchBus.listener();
