import Vue from 'vue';
import store from '../../admin/js/store/store';
import router from './reports-router';
import VTooltip from 'v-tooltip';
import { Snackbar, Modal } from 'buefy';
import VueApexCharts from 'vue-apexcharts';

// Vue Dev Tools!
Vue.config.devtools = process && process.env && process.env.NODE_ENV === 'development';

import { I18NPlugin } from './wp-i18n-plugin';

import ReportsPage from '../reports.vue';
import ChartBlock from '../components/chart-block.vue';

Vue.use(VueApexCharts)

// Apex.theme = {
//     monochrome: {
//         enabled: true,
//         color: '#298596',
//         shadeTo: 'light',
//         shadeIntensity: 0.65
//     }
// }
Apex.colors = [
    '#298596', // Tainacan Turquoise
    '#01295c', // Tainacan Blue
    '#25a189', // Tainacan Green
    '#e69810', // Tainacan Yellow
    '#a23939', // Tainacan Red
    '#592570', // Tainacan Purple
    '#ed4f63'  // Tainacan Pink
];

Vue.use(I18NPlugin);
Vue.use(VTooltip);
Vue.use(Snackbar);
Vue.use(Modal);

Vue.component('chart-block', ChartBlock);
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