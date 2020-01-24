// Overrides lodash by original WordPress Underscore Library
//window.lodash = _.noConflict();
//window.underscore = _.noConflict();

// Main imports
import Vue from 'vue';
import Buefy from 'buefy';
import VTooltip from 'v-tooltip';
import VueMasonry from 'vue-masonry-css';
import draggable from 'vuedraggable';
import VueTheMask from 'vue-the-mask';

// Metadata Types
import Text from '../components/metadata-types/text/Text.vue';
import Textarea from '../components/metadata-types/textarea/Textarea.vue';
import Selectbox from '../components/metadata-types/selectbox/Selectbox.vue';
import Numeric from '../components/metadata-types/numeric/Numeric.vue';
import Date from '../components/metadata-types/date/Date.vue';
import Relationship from '../components/metadata-types/relationship/Relationship.vue';
import Taxonomy from '../components/metadata-types/taxonomy/Taxonomy.vue';

import FormRelationship from '../components/metadata-types/relationship/FormRelationship.vue';
import FormTaxonomy from '../components/metadata-types/taxonomy/FormTaxonomy.vue';
import FormSelectbox from '../components/metadata-types/selectbox/FormSelectbox.vue';
import FormNumeric from '../components/metadata-types/numeric/FormNumeric.vue';

import FilterNumeric from '../components/filter-types/numeric/Numeric.vue';
import FilterDate from '../components/filter-types/date/Date.vue';
import FilterSelectbox from '../components/filter-types/selectbox/Selectbox.vue';
import FilterAutocomplete from '../components/filter-types/autocomplete/Autocomplete.vue';
import FilterCheckbox from '../components/filter-types/checkbox/Checkbox.vue';
import FilterTaginput from '../components/filter-types/taginput/Taginput.vue';
import FilterNumericInterval from '../components/filter-types/numeric-interval/NumericInterval.vue';
import FilterDateInterval from '../components/filter-types/date-interval/DateInterval.vue';
import FilterNumericListInterval from '../components/filter-types/numeric-list-interval/NumericListInterval.vue';
import FilterTaxonomyCheckbox from '../components/filter-types/taxonomy/Checkbox.vue';
import FilterTaxonomyTaginput from '../components/filter-types/taxonomy/Taginput.vue';

import FormFilterNumeric from '../components/filter-types/numeric/FormNumeric.vue';
import FormFilterNumericInterval from '../components/filter-types/numeric-interval/FormNumericInterval.vue';
import FormFilterNumericListInterval from '../components/filter-types/numeric-list-interval/FormNumericListInterval.vue';
// import FormDate from '../../../classes/filter-types/date/FormDate.vue';

import TainacanFormItem from '../components/metadata-types/tainacan-form-item.vue';
import TainacanFiltersList from '../components/filter-types/tainacan-filter-item.vue';

// Remaining imports
import AdminPage from '../admin.vue'
import HelpButton from '../components/other/help-button.vue';
import TainacanTitle from '../components/navigation/tainacan-title.vue';
import store from './store/store'
import router from './router'
import eventBusSearch from './event-bus-search';
import eventBusTermsList from './event-bus-terms-list.js';
import { I18NPlugin, UserPrefsPlugin, RouterHelperPlugin, ConsolePlugin, UserCapabilitiesPlugin, StatusHelperPlugin, CommentsStatusHelperPlugin } from './utilities';

// Configure and Register Plugins
Vue.use(Buefy, {
    defaultTooltipAnimated: true
});
Vue.use(VTooltip);
Vue.use(VueMasonry);
Vue.use(I18NPlugin);
Vue.use(UserPrefsPlugin);
Vue.use(RouterHelperPlugin);
Vue.use(UserCapabilitiesPlugin);
Vue.use(StatusHelperPlugin);
Vue.use(ConsolePlugin, {visual: false});
Vue.use(VueTheMask);
Vue.use(CommentsStatusHelperPlugin);

/* Metadata */
Vue.component('tainacan-text', Text);
Vue.component('tainacan-textarea', Textarea);
Vue.component('tainacan-selectbox', Selectbox);
Vue.component('tainacan-numeric', Numeric);
Vue.component('tainacan-date', Date);
Vue.component('tainacan-relationship', Relationship);
Vue.component('tainacan-taxonomy', Taxonomy);

/* Metadata Option forms */
Vue.component('tainacan-form-relationship', FormRelationship);
Vue.component('tainacan-form-taxonomy', FormTaxonomy);
Vue.component('tainacan-form-selectbox', FormSelectbox);
Vue.component('tainacan-form-numeric', FormNumeric);
Vue.component('tainacan-form-item', TainacanFormItem);
Vue.component('tainacan-filter-item', TainacanFiltersList);

/* Filters */
Vue.component('tainacan-filter-numeric', FilterNumeric);
Vue.component('tainacan-filter-date', FilterDate);
Vue.component('tainacan-filter-selectbox', FilterSelectbox);
Vue.component('tainacan-filter-autocomplete', FilterAutocomplete);
Vue.component('tainacan-filter-checkbox', FilterCheckbox);
Vue.component('tainacan-filter-taginput', FilterTaginput);
Vue.component('tainacan-filter-taxonomy-checkbox', FilterTaxonomyCheckbox);
Vue.component('tainacan-filter-taxonomy-taginput', FilterTaxonomyTaginput);
Vue.component('tainacan-filter-numeric-interval', FilterNumericInterval);
Vue.component('tainacan-filter-numeric-list-interval', FilterNumericListInterval);
Vue.component('tainacan-filter-date-interval', FilterDateInterval);

/* Filter Metadata Option forms */
Vue.component('tainacan-filter-form-numeric', FormFilterNumeric);
Vue.component('tainacan-filter-form-numeric-interval', FormFilterNumericInterval);
Vue.component('tainacan-filter-form-numeric-list-interval', FormFilterNumericListInterval);
// Vue.component('tainacan-filter-form-date', FormDate);

/* Others */
Vue.component('help-button', HelpButton);
Vue.component('draggable', draggable);
Vue.component('tainacan-title', TainacanTitle);

Vue.use(eventBusTermsList, {});
Vue.use(eventBusSearch, { store: store, router: router});

// Changing title of pages
router.beforeEach((to, from, next) => {
    document.title = to.meta.title;
    if (next() != undefined)
        next();
});

new Vue({
    el: '#tainacan-admin-app',
    store,
    router,
    render: h => h(AdminPage)
});

// Display Icons only once everything is loaded
function listen(evnt, elem, func) {
    if (elem.addEventListener)  // W3C DOM
        elem.addEventListener(evnt,func,false);
    else if (elem.attachEvent) { // IE DOM
         var r = elem.attachEvent("on"+evnt, func);
         return r;
    } else if (document.head) {
        var iconHideStyle = document.createElement("style");
        iconHideStyle.innerText = '.tainacan-icon{ opacity: 1 !important; }'; 
        document.head.appendChild(iconHideStyle);
    } else {
        var iconHideStyle = document.createElement("style");
        iconHideStyle.innerText = '.tainacan-icon{ opacity: 1 !important; }'; 
        document.getElementsByTagName("head")[0].appendChild(iconHideStyle);
    }
}
listen("load", window, function() {
    var iconsStyle = document.createElement("style");
    iconsStyle.setAttribute('type', 'text/css');
    iconsStyle.innerText = '.tainacan-icon{ opacity: 1 !important; }';
    document.head.appendChild(iconsStyle);
});
