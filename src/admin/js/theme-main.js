// Main imports
import Vue from 'vue';
import Buefy from 'buefy';
import VTooltip from 'v-tooltip'

// Custom elements
import Text from '../../classes/metadatum-types/text/Text.vue';
import Textarea from '../../classes/metadatum-types/textarea/Textarea.vue';
import Selectbox from '../../classes/metadatum-types/selectbox/Selectbox.vue';
import Numeric from '../../classes/metadatum-types/numeric/Numeric.vue';
import Date from '../../classes/metadatum-types/date/Date.vue';
import Relationship from '../../classes/metadatum-types/relationship/Relationship.vue';
import Category from '../../classes/metadatum-types/category/Category.vue';

import FormRelationship from '../../classes/metadatum-types/relationship/FormRelationship.vue';
import FormCategory from '../../classes/metadatum-types/category/FormCategory.vue';
import FormSelectbox from '../../classes/metadatum-types/selectbox/FormSelectbox.vue';

import FilterCustomInterval from '../../classes/filter-types/custom-interval/CustomInterval.vue';
import FilterSelectbox from '../../classes/filter-types/selectbox/Selectbox.vue';
import FilterAutocomplete from '../../classes/filter-types/autocomplete/Autocomplete.vue';
import FilterCheckbox from '../../classes/filter-types/checkbox/Checkbox.vue';
import FilterTaginput from '../../classes/filter-types/taginput/Taginput.vue';

import FilterCategoryCheckbox from '../../classes/filter-types/category/Checkbox.vue';
import FilterCategoryTaginput from '../../classes/filter-types/category/Taginput.vue';
import FilterCategorySelectbox from '../../classes/filter-types/category/Selectbox.vue';

import TaincanFormItem from '../../classes/metadatum-types/tainacan-form-item.vue';
import TaincanFiltersList from '../../classes/filter-types/tainacan-filter-item.vue';
import ItemsPage from '../pages/lists/items-page.vue';
import TableViewMode from '../../theme-helper/table-view-mode.vue';

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
Vue.component('tainacan-category', Category);

Vue.component('tainacan-form-relationship', FormRelationship);
Vue.component('tainacan-form-category', FormCategory);
Vue.component('tainacan-form-selectbox', FormSelectbox);
Vue.component('tainacan-form-item', TaincanFormItem);
Vue.component('tainacan-filter-item', TaincanFiltersList);

/* Filters */
Vue.component('tainacan-filter-custom-interval', FilterCustomInterval);
Vue.component('tainacan-filter-selectbox', FilterSelectbox);
Vue.component('tainacan-filter-autocomplete', FilterAutocomplete);
Vue.component('tainacan-filter-checkbox', FilterCheckbox);
Vue.component('tainacan-filter-taginput', FilterTaginput);
Vue.component('tainacan-filter-category-checkbox', FilterCategoryCheckbox);
Vue.component('tainacan-filter-category-taginput', FilterCategoryTaginput);
Vue.component('tainacan-filter-category-selectbox', FilterCategorySelectbox);

/* Others */
Vue.component('help-button', HelpButton);
Vue.component('draggable', draggable);
Vue.component('items-page', ItemsPage);

Vue.component('table-view-mode', TableViewMode);

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