// Main imports
import Vue from 'vue';
import Buefy from 'buefy';
import VTooltip from 'v-tooltip';
import VueMasonry from 'vue-masonry-css';

// Custom elements
import FilterCustomInterval from '../../classes/filter-types/custom-interval/CustomInterval.vue';
import FilterNumeric from '../../classes/filter-types/numeric/Numeric.vue';
import FilterDate from '../../classes/filter-types/date/Date.vue';
import FilterSelectbox from '../../classes/filter-types/selectbox/Selectbox.vue';
import FilterAutocomplete from '../../classes/filter-types/autocomplete/Autocomplete.vue';
import FilterCheckbox from '../../classes/filter-types/checkbox/Checkbox.vue';
import FilterTaginput from '../../classes/filter-types/taginput/Taginput.vue';

import FilterTaxonomyCheckbox from '../../classes/filter-types/taxonomy/Checkbox.vue';
import FilterTaxonomyTaginput from '../../classes/filter-types/taxonomy/Taginput.vue';

import TaincanFiltersList from '../../classes/filter-types/tainacan-filter-item.vue';
import ItemsPage from '../pages/lists/items-page.vue';
import TermItemsPage from '../pages/lists/term-items-page.vue';
import ViewModeTable from '../../theme-helper/view-mode-table.vue';
import ViewModeCards from '../../theme-helper/view-mode-cards.vue';
import ViewModeRecords from '../../theme-helper/view-mode-records.vue';
import ViewModeMasonry from '../../theme-helper/view-mode-masonry.vue';
import ViewModeSlideshow from '../../theme-helper/view-mode-slideshow.vue';

// Remaining imports
import store from '../../js/store/store'
import routerTheme from './theme-router.js'
import eventBusSearch from '../../js/event-bus-search';
import { I18NPlugin, UserPrefsPlugin, RouterHelperPlugin, ConsolePlugin, StatusHelperPlugin } from './utilities';

// Configure and Register Plugins
Vue.use(Buefy);
Vue.use(VTooltip);
Vue.use(VueMasonry);
Vue.use(I18NPlugin);
Vue.use(UserPrefsPlugin);
Vue.use(RouterHelperPlugin);
Vue.use(StatusHelperPlugin);
Vue.use(ConsolePlugin, {visual: false});


Vue.component('tainacan-filter-item', TaincanFiltersList);

/* Filters */
Vue.component('tainacan-filter-custom-interval', FilterCustomInterval);
Vue.component('tainacan-filter-numeric', FilterNumeric);
Vue.component('tainacan-filter-date', FilterDate);
Vue.component('tainacan-filter-selectbox', FilterSelectbox);
Vue.component('tainacan-filter-autocomplete', FilterAutocomplete);
Vue.component('tainacan-filter-checkbox', FilterCheckbox);
Vue.component('tainacan-filter-taginput', FilterTaginput);
Vue.component('tainacan-filter-taxonomy-checkbox', FilterTaxonomyCheckbox);
Vue.component('tainacan-filter-taxonomy-taginput', FilterTaxonomyTaginput);

/* Others */
Vue.component('items-page', ItemsPage);
Vue.component('term-items-page', TermItemsPage);

// Oficial view modes
Vue.component('view-mode-table', ViewModeTable);
Vue.component('view-mode-cards', ViewModeCards);
Vue.component('view-mode-records', ViewModeRecords);
Vue.component('view-mode-masonry', ViewModeMasonry);
Vue.component('view-mode-slideshow', ViewModeSlideshow);

Vue.use(eventBusSearch, { store: store, router: routerTheme});

// THEME ITEMS LIST (COLLECTIONS)
import ThemeItemsList from '../theme-items-list.vue';

export const ThemeItemsListing =  new Vue({
    el: '#tainacan-items-page',
    store,
    router: routerTheme, 
    data: {
        termId: '',
        taxonomy: '',
        collectionId: '',
        defaultViewMode: '',
        enabledViewModes: {},
        customFilters: []
    },
    render: h => h(ThemeItemsList),
    beforeMount () {
        
        this.collectionId = this.$el.attributes['collection-id'] != undefined ? this.$el.attributes['collection-id'].value : undefined;
        
        if (this.$el.attributes['default-view-mode'] != undefined)
            this.defaultViewMode = this.$el.attributes['default-view-mode'].value;
        else
            this.defaultViewMode = 'cards';

        if (this.$el.attributes['enabled-view-modes'] != undefined)
            this.enabledViewModes = this.$el.attributes['enabled-view-modes'].value.split(',');

        if (this.$el.attributes['term-id'] != undefined)
            this.termId = this.$el.attributes['term-id'].value;
        if (this.$el.attributes['taxonomy'] != undefined)
            this.taxonomy = this.$el.attributes['taxonomy'].value;

    }
    
});

// Display Icons only once everything is loaded 
function listen(evnt, elem, func) {
    if (elem.addEventListener)  // W3C DOM
        elem.addEventListener(evnt,func,false);
    else if (elem.attachEvent) { // IE DOM
         var r = elem.attachEvent("on"+evnt, func);
         return r;
    }
    else {
        jQuery('head').append('<style>.tainacan-icon{ opacity: 1 !important; }</style>');
    }
}
listen("load", window, function() {
    var iconsStyle = document.createElement("style");
    iconsStyle.setAttribute('type', 'text/css');
    iconsStyle.innerText = '.tainacan-icon{ opacity: 1 !important; }';
    document.head.appendChild(iconsStyle);
});
