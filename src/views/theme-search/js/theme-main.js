// Main imports
import Vue from 'vue';
import Buefy from 'buefy';
import VTooltip from 'v-tooltip';
import VueMasonry from 'vue-masonry-css';
import cssVars from 'css-vars-ponyfill';

// Filters
import FilterNumeric from '../../admin/components/filter-types/numeric/Numeric.vue';
import FilterDate from '../../admin/components/filter-types/date/Date.vue';
import FilterSelectbox from '../../admin/components/filter-types/selectbox/Selectbox.vue';
import FilterAutocomplete from '../../admin/components/filter-types/autocomplete/Autocomplete.vue';
import FilterCheckbox from '../../admin/components/filter-types/checkbox/Checkbox.vue';
import FilterTaginput from '../../admin/components/filter-types/taginput/Taginput.vue';
import FilterTaxonomyCheckbox from '../../admin/components/filter-types/taxonomy/Checkbox.vue';
import FilterTaxonomyTaginput from '../../admin/components/filter-types/taxonomy/Taginput.vue';
import FilterDateInterval from '../../admin/components/filter-types/date-interval/DateInterval.vue';
import FilterNumericInterval from '../../admin/components/filter-types/numeric-interval/NumericInterval.vue';
import FilterNumericListInterval from '../../admin/components/filter-types/numeric-list-interval/NumericListInterval.vue';

import TaincanFiltersList from '../../admin/components/filter-types/tainacan-filter-item.vue';
import ThemeItemsPage from '../pages/theme-items-page.vue';
import ThemeSearch from '../theme-search.vue';

// View Modes
import ViewModeTable from '../components/view-mode-table.vue';
import ViewModeCards from '../components/view-mode-cards.vue';
import ViewModeRecords from '../components/view-mode-records.vue';
import ViewModeMasonry from '../components/view-mode-masonry.vue';
import ViewModeSlideshow from '../components/view-mode-slideshow.vue';

// Remaining imports
import store from '../../admin/js/store/store';
import routerTheme from './theme-router.js';
import eventBusSearch from '../../admin/js/event-bus-search';
import { I18NPlugin, UserPrefsPlugin, ConsolePlugin } from '../../admin/js/utilities';

// Configure and Register Plugins
Vue.use(Buefy, {
    defaultTooltipAnimated: true
});
Vue.use(VTooltip);
Vue.use(VueMasonry);
Vue.use(I18NPlugin);
Vue.use(UserPrefsPlugin);
Vue.use(ConsolePlugin, {visual: false});

Vue.component('tainacan-filter-item', TaincanFiltersList);

/* Filters */
Vue.component('tainacan-filter-numeric', FilterNumeric);
Vue.component('tainacan-filter-date', FilterDate);
Vue.component('tainacan-filter-selectbox', FilterSelectbox);
Vue.component('tainacan-filter-autocomplete', FilterAutocomplete);
Vue.component('tainacan-filter-checkbox', FilterCheckbox);
Vue.component('tainacan-filter-taginput', FilterTaginput);
Vue.component('tainacan-filter-taxonomy-checkbox', FilterTaxonomyCheckbox);
Vue.component('tainacan-filter-taxonomy-taginput', FilterTaxonomyTaginput);
Vue.component('tainacan-filter-date-interval', FilterDateInterval);
Vue.component('tainacan-filter-numeric-interval', FilterNumericInterval);
Vue.component('tainacan-filter-numeric-list-interval', FilterNumericListInterval);

/* Main page component */
Vue.component('theme-items-page', ThemeItemsPage);
Vue.component('theme-search', ThemeSearch);

// Oficial view modes
Vue.component('view-mode-table', ViewModeTable);
Vue.component('view-mode-cards', ViewModeCards);
Vue.component('view-mode-records', ViewModeRecords);
Vue.component('view-mode-masonry', ViewModeMasonry);
Vue.component('view-mode-slideshow', ViewModeSlideshow);

Vue.use(eventBusSearch, { store: store, router: routerTheme});

document.addEventListener("DOMContentLoaded", () => {
    new Vue({
        el: '#tainacan-items-page',
        store,
        router: routerTheme,
        data: {
            termId: '',
            taxonomy: '',
            collectionId: '',
            defaultViewMode: '',
            enabledViewModes: {},
            hideFilters: false,
            hideHideFiltersButton: false,
            hideSearch: false,
            hideAdvancedSearch: false,
            hideSortByButton: false,
            hideItemsPerPageButton: false,
            hideGoToPageButton: false,
            startWithFiltersHidden: false,
            filtersAsModal: false,
            showInlineViewModeOptions: false,
            showFullscreenWithViewModes: false
        },
        beforeMount () {
            // Collection or Term source settings
            if (this.$el.attributes['collection-id'] != undefined)
                this.collectionId = this.$el.attributes['collection-id'].value;
            if (this.$el.attributes['term-id'] != undefined)
                this.termId = this.$el.attributes['term-id'].value;
            if (this.$el.attributes['taxonomy'] != undefined)
                this.taxonomy = this.$el.attributes['taxonomy'].value;

            // View Mode settings
            if (this.$el.attributes['default-view-mode'] != undefined)
                this.defaultViewMode = this.$el.attributes['default-view-mode'].value;
            else
                this.defaultViewMode = 'cards';

            if (this.$el.attributes['enabled-view-modes'] != undefined)
                this.enabledViewModes = this.$el.attributes['enabled-view-modes'].value.split(',');
            
            // Options related to hidding elements
            if (this.$el.attributes['hide-filters'] != undefined)
                this.hideFilters = this.$el.attributes['hide-filters'].value == 'true' ? true : false;
            if (this.$el.attributes['hide-hide-filters-button'] != undefined)
                this.hideHideFiltersButton = this.$el.attributes['hide-hide-filters-button'].value == 'true' ? true : false;
            if (this.$el.attributes['hide-search'] != undefined)
                this.hideSearch = this.$el.attributes['hide-search'].value == 'true' ? true : false;
            if (this.$el.attributes['hide-advanced-search'] != undefined)
                this.hideAdvancedSearch = this.$el.attributes['hide-advanced-search'].value == 'true' ? true : false;
            if (this.$el.attributes['hide-sort-by-button'] != undefined)
                this.hideSortByButton = this.$el.attributes['hide-sort-by-button'].value == 'true' ? true : false;
            if (this.$el.attributes['hide-items-per-page-button'] != undefined)
                this.hideItemsPerPageButton = this.$el.attributes['hide-items-per-page-button'].value == 'true' ? true : false;
            if (this.$el.attributes['hide-go-to-page-button'] != undefined)
                this.hideGoToPageButton = this.$el.attributes['hide-go-to-page-button'].value == 'true' ? true : false;
            // Other Tweaks
            if (this.$el.attributes['start-with-filters-hidden'] != undefined)
                this.startWithFiltersHidden = this.$el.attributes['start-with-filters-hidden'].value == 'true' ? true : false;
            if (this.$el.attributes['filters-as-modal'] != undefined)
                this.filtersAsModal = this.$el.attributes['filters-as-modal'].value == 'true' ? true : false;
            if (this.$el.attributes['show-inline-view-mode-options'] != undefined)
                this.showInlineViewModeOptions = this.$el.attributes['show-inline-view-mode-options'].value == 'true' ? true : false;
            if (this.$el.attributes['show-fullscreen-with-view-modes'] != undefined)
                this.showFullscreenWithViewModes = this.$el.attributes['show-fullscreen-with-view-modes'].value == 'true' ? true : false;
        },
        render: h => h(ThemeSearch)
    });
});

// Initialize Ponyfill for Custom CSS properties
cssVars({
// Options...
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
