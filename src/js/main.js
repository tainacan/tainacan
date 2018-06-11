import Vue from 'vue'
import Buefy from 'buefy'

// include vue-custom-element plugin to Vue
import VueCustomElement from 'vue-custom-element';
import { eventBus } from './event-bus-web-components';
import eventBusSearch from './event-bus-search';

Vue.use(Buefy);

Vue.use(VueCustomElement);

import Text from '../classes/metadata-types/text/Text.vue';
import Textarea from '../classes/metadata-types/textarea/Textarea.vue';
import Selectbox from '../classes/metadata-types/selectbox/Selectbox.vue';
import Numeric from '../classes/metadata-types/numeric/Numeric.vue';
import Date from '../classes/metadata-types/date/Date.vue';
import Relationship from '../classes/metadata-types/relationship/Relationship.vue';

import FormRelationship from '../classes/metadata-types/relationship/FormRelationship.vue';

import FilterCustomInterval from '../classes/filter-types/custom-interval/CustomInterval.vue';
import FilterSelectbox from '../classes/filter-types/selectbox/Selectbox.vue';
import FilterAutocomplete from '../classes/filter-types/autocomplete/Autocomplete.vue';
import FilterCheckbox from '../classes/filter-types/checkbox/Checkbox.vue';
import FilterTaginput from '../classes/filter-types/taginput/Taginput.vue';

Vue.use(eventBusSearch);

Vue.customElement('tainacan-text', Text);
//eventBus.registerComponent( 'tainacan-text' );

Vue.customElement('tainacan-textarea', Textarea);
//eventBus.registerComponent( 'tainacan-textarea' );

Vue.customElement('tainacan-selectbox', Selectbox);
//eventBus.registerComponent( 'tainacan-selectbox' );

Vue.customElement('tainacan-numeric', Numeric);
//eventBus.registerComponent( 'tainacan-numeric' );

Vue.customElement('tainacan-date', Date);
//eventBus.registerComponent( 'tainacan-date' );

Vue.customElement('tainacan-relationship', Relationship);
//eventBus.registerComponent( 'tainacan-relationship' );

//eventBus.listener();

/* Form */

Vue.customElement('tainacan-form-relationship', FormRelationship);
//eventBus.registerComponent( 'tainacan-form-relationship' );

/* Filters */

Vue.customElement('tainacan-filter-custom-interval', FilterCustomInterval);
//eventBusSearch.registerComponent( 'tainacan-filter-custom-interval' );

Vue.customElement('tainacan-filter-selectbox', FilterSelectbox);
//eventBusSearch.registerComponent( 'tainacan-filter-selectbox' );

Vue.customElement('tainacan-filter-autocomplete', FilterAutocomplete);
//eventBusSearch.registerComponent( 'tainacan-filter-autocomplete' );

Vue.customElement('tainacan-filter-checkbox', FilterCheckbox);
//eventBusSearch.registerComponent( 'tainacan-filter-checkbox' );

Vue.customElement('tainacan-filter-taginput', FilterTaginput);
//eventBusSearch.registerComponent( 'tainacan-filter-taginput' );

//eventBusSearch.listener();
