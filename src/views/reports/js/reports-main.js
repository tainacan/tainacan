import Vue from 'vue';
import store from '../../admin/js/store/store';
import router from './reports-router';
import VTooltip from 'v-tooltip';
import { Snackbar, Modal } from 'buefy';
import VueApexCharts from 'vue-apexcharts';
import cssVars from 'css-vars-ponyfill';
import { 
    I18NPlugin,
    UserCapabilitiesPlugin,
    StatusHelperPlugin,
} from '../../admin/js/admin-utilities';

// Vue Dev Tools!
Vue.config.devtools = process && process.env && process.env.NODE_ENV === 'development';

import ReportsPage from '../reports.vue';
import NumberBlock from '../components/number-block.vue';
import ItemsPerTermBlock from '../components/items-per-term-block.vue';
import ItemsPerTermCollectionBlock from '../components/items-per-term-collection-block.vue';
import TermsPerTaxonomyBlock from '../components/terms-per-taxonomy-block.vue';
import MetadataTypesBlock from '../components/metadata-types-block.vue';
import MetadataDistributionBlock from '../components/metadata-distribution-block.vue';
import CollectionsListBlock from '../components/collections-list-block.vue';
import ActivitiesBlock from '../components/activities-block.vue';
import ActivitiesPerUserBlock from '../components/activities-per-user-block.vue';

Vue.use(VueApexCharts)

Apex.colors = [
    '#298596', // Tainacan Turquoise
    '#01295c', // Tainacan Blue
    '#25a189', // Tainacan Green
    '#e69810', // Tainacan Yellow
    '#a23939', // Tainacan Red
    '#592570', // Tainacan Purple
    '#ed4f63', // Tainacan Pink
    '#b46659',  // Tainacan Brown
    '#e5721c',  // Tainacan Orange
    '#04a5ff',  // Tainacan Other Blue
    '#454647'  // Tainacan Dark Gray
];

Vue.use(I18NPlugin);
Vue.use(UserCapabilitiesPlugin);
Vue.use(StatusHelperPlugin);
Vue.use(VTooltip);
Vue.use(Snackbar);
Vue.use(Modal);

Vue.component('number-block', NumberBlock);
Vue.component('items-per-term-block', ItemsPerTermBlock);
Vue.component('items-per-term-collection-block', ItemsPerTermCollectionBlock);
Vue.component('terms-per-taxonomy-block', TermsPerTaxonomyBlock);
Vue.component('metadata-types-block', MetadataTypesBlock);
Vue.component('metadata-distribution-block', MetadataDistributionBlock);
Vue.component('collections-list-block', CollectionsListBlock);
Vue.component('activities-block', ActivitiesBlock);
Vue.component('activities-per-user-block', ActivitiesPerUserBlock);
Vue.component('apexchart', VueApexCharts);

// Changing title of pages
router.beforeEach((to, from, next) => {
    document.title = to.meta.title;
    if (next() != undefined)
        next();
});

new Vue({
    el: '#tainacan-reports-app',
    store,
    router,
    render: h => h(ReportsPage)
});

listen("load", window, function() {
    var iconsStyle = document.createElement("style");
    iconsStyle.setAttribute('type', 'text/css');
    iconsStyle.innerText = '.tainacan-icon::before{ opacity: 1.0 !important; }';
    document.head.appendChild(iconsStyle);
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
        iconHideStyle.innerText = '.tainacan-icon::before{ opacity: 0.0 !important; }'; 
        document.head.appendChild(iconHideStyle);
    } else {
        var iconHideStyle = document.createElement("style");
        iconHideStyle.innerText = '.tainacan-icon::before{ opacity: 0.0 !important; }'; 
        document.getElementsByTagName("head")[0].appendChild(iconHideStyle);
    }
}