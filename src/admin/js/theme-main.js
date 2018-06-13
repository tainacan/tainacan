// Main imports
import Vue from 'vue';
import Buefy from 'buefy';
import VTooltip from 'v-tooltip'

// Custom elements
import Text from '../../classes/metadata-types/text/Text.vue';
import Textarea from '../../classes/metadata-types/textarea/Textarea.vue';
import Selectbox from '../../classes/metadata-types/selectbox/Selectbox.vue';
import Numeric from '../../classes/metadata-types/numeric/Numeric.vue';
import Date from '../../classes/metadata-types/date/Date.vue';
import Relationship from '../../classes/metadata-types/relationship/Relationship.vue';
import Taxonomy from '../../classes/metadata-types/taxonomy/Taxonomy.vue';

import FormRelationship from '../../classes/metadata-types/relationship/FormRelationship.vue';
import FormTaxonomy from '../../classes/metadata-types/taxonomy/FormTaxonomy.vue';
import FormSelectbox from '../../classes/metadata-types/selectbox/FormSelectbox.vue';

import FilterCustomInterval from '../../classes/filter-types/custom-interval/CustomInterval.vue';
import FilterSelectbox from '../../classes/filter-types/selectbox/Selectbox.vue';
import FilterAutocomplete from '../../classes/filter-types/autocomplete/Autocomplete.vue';
import FilterCheckbox from '../../classes/filter-types/checkbox/Checkbox.vue';
import FilterTaginput from '../../classes/filter-types/taginput/Taginput.vue';

import FilterTaxonomyCheckbox from '../../classes/filter-types/taxonomy/Checkbox.vue';
import FilterTaxonomyTaginput from '../../classes/filter-types/taxonomy/Taginput.vue';
import FilterTaxonomySelectbox from '../../classes/filter-types/taxonomy/Selectbox.vue';

import TaincanFormItem from '../../classes/metadata-types/tainacan-form-item.vue';
import TaincanFiltersList from '../../classes/filter-types/tainacan-filter-item.vue';
import ItemsPage from '../pages/lists/items-page.vue';
import ViewModeTable from '../../theme-helper/view-mode-table.vue';
import ViewModeCards from '../../theme-helper/view-mode-cards.vue';

// Remaining imports
import HelpButton from '../components/other/help-button.vue';
import draggable from 'vuedraggable'
import store from '../../js/store/store'
import routerTheme from './theme-router.js'
import eventBusSearch from '../../js/event-bus-search';
import { I18NPlugin, UserPrefsPlugin, RouterHelperPlugin, ConsolePlugin } from './utilities';

// Configure and Register Plugins
Vue.use(Buefy);
Vue.use(VTooltip)
Vue.use(I18NPlugin);
Vue.use(UserPrefsPlugin);
Vue.use(RouterHelperPlugin);
Vue.use(ConsolePlugin, {visual: false});

/* Metadata */
Vue.component('tainacan-text', Text);
Vue.component('tainacan-textarea', Textarea);
Vue.component('tainacan-selectbox', Selectbox);
Vue.component('tainacan-numeric', Numeric);
Vue.component('tainacan-date', Date);
Vue.component('tainacan-relationship', Relationship);
Vue.component('tainacan-taxonomy', Taxonomy);

Vue.component('tainacan-form-relationship', FormRelationship);
Vue.component('tainacan-form-taxonomy', FormTaxonomy);
Vue.component('tainacan-form-selectbox', FormSelectbox);
Vue.component('tainacan-form-item', TaincanFormItem);
Vue.component('tainacan-filter-item', TaincanFiltersList);

/* Filters */
Vue.component('tainacan-filter-custom-interval', FilterCustomInterval);
Vue.component('tainacan-filter-selectbox', FilterSelectbox);
Vue.component('tainacan-filter-autocomplete', FilterAutocomplete);
Vue.component('tainacan-filter-checkbox', FilterCheckbox);
Vue.component('tainacan-filter-taginput', FilterTaginput);
Vue.component('tainacan-filter-taxonomy-checkbox', FilterTaxonomyCheckbox);
Vue.component('tainacan-filter-taxonomy-taginput', FilterTaxonomyTaginput);
Vue.component('tainacan-filter-taxonomy-selectbox', FilterTaxonomySelectbox);

/* Others */
Vue.component('help-button', HelpButton);
Vue.component('draggable', draggable);
Vue.component('items-page', ItemsPage);

// Oficial view modes
Vue.component('view-mode-table', ViewModeTable);
Vue.component('view-mode-cards', ViewModeCards);

Vue.use(eventBusSearch, { store: store, router: routerTheme});

import ThemeItemsList from '../theme-items-list.vue';

new Vue({
    el: '#tainacan-items-page',
    store,
    router: routerTheme, 
    data: {
        collectionId: '',
        defaultViewMode: '',
        enabledViewModes: {}   
    },
    render: h => h(ThemeItemsList),
    beforeMount () {

        if (this.$el.attributes['collection-id'] != undefined)
            this.collectionId = this.$el.attributes['collection-id'].value;
        if (this.$el.attributes['default-view-mode'] != undefined)
            this.defaultViewMode = this.$el.attributes['default-view-mode'].value;
        if (this.$el.attributes['enabled-view-modes'] != undefined)
            this.enabledViewModes = this.$el.attributes['enabled-view-modes'].value.split(',');

    }
    
});