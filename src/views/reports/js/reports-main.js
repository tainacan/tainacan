import { createApp } from 'vue';
import store from '../../admin/js/store/store';
import router from './reports-router';
import { Snackbar, Modal } from 'buefy';
import VueApexCharts from 'vue-apexcharts';
import cssVars from 'css-vars-ponyfill';
import { 
    I18NPlugin,
    UserCapabilitiesPlugin,
    StatusHelperPlugin,
} from '../../admin/js/admin-utilities';

import ReportsPage from '../reports.vue';
import TainacanReportsSubheader from '../components/tainacan-reports-subheader.vue';
import NumberBlock from '../components/number-block.vue';
import ItemsPerTermBlock from '../components/items-per-term-block.vue';
import ItemsPerTermCollectionBlock from '../components/items-per-term-collection-block.vue';
import TermsPerTaxonomyBlock from '../components/terms-per-taxonomy-block.vue';
import MetadataTypesBlock from '../components/metadata-types-block.vue';
import MetadataDistributionBlock from '../components/metadata-distribution-block.vue';
import CollectionsListBlock from '../components/collections-list-block.vue';
import ActivitiesBlock from '../components/activities-block.vue';
import ActivitiesPerUserBlock from '../components/activities-per-user-block.vue';

/* Sets some locale configs */
import enLocaleConfig from 'apexcharts/dist/locales/en.json';
import esLocaleConfig from 'apexcharts/dist/locales/es.json';
import frLocaleConfig from 'apexcharts/dist/locales/fr.json';
import ptBrLocaleConfig from 'apexcharts/dist/locales/pt-br.json';

export default (element) => {

    function renderTainacanReportsPage() {
        
        // Gets the div with the content of the page
        let pageElement = element ? element : document.getElementById('tainacan-reports-app');

        // Mount only if the div exists and it is not already mounted
        if ( pageElement && pageElement.classList && !pageElement.classList.contains('has-mounted') ) {

            Apex.colors = [
                '#187181', // Tainacan Turquoise
                '#062a57', // Tainacan Blue
                '#1a745c', // Tainacan Green
                '#a06522', // Tainacan Yellow
                '#9b3636', // Tainacan Red
                '#592570', // Tainacan Purple
                '#ed4f63', // Tainacan Pink
                '#b46659',  // Tainacan Brown
                '#e5721c',  // Tainacan Orange
                '#04a5ff',  // Tainacan Other Blue
                '#373839'  // Tainacan Dark Gray
            ];
            const availableLocales = ['en', 'es', 'fr', 'pt-br'];
            const browserLanguage = navigator.language.toLocaleLowerCase();

            if (availableLocales.indexOf(browserLanguage) >= 0) {
                let localeConfig = {};

                switch(browserLanguage) {
                    case 'es': localeConfig = esLocaleConfig; break;
                    case 'fr': localeConfig = frLocaleConfig; break;
                    case 'pt-br': localeConfig = ptBrLocaleConfig; break;
                    case 'en': default: localeConfig = enLocaleConfig; break;
                }
                Apex.chart = {
                    defaultLocale: browserLanguage,
                    locales: [ localeConfig ]
                }
            }

            const VueReports = createApp({
                el: '#tainacan-reports-app',
                render: h => h(ReportsPage)
            });

            VueReports.use(store);
            VueReports.use(router);

            VueReports.use(VueApexCharts)

            VueReports.use(I18NPlugin);
            VueReports.use(UserCapabilitiesPlugin);
            VueReports.use(StatusHelperPlugin);
            VueReports.use(Snackbar);
            VueReports.use(Modal);

            VueReports.component('tainacan-reports-subheader', TainacanReportsSubheader);
            VueReports.component('number-block', NumberBlock);
            VueReports.component('items-per-term-block', ItemsPerTermBlock);
            VueReports.component('items-per-term-collection-block', ItemsPerTermCollectionBlock);
            VueReports.component('terms-per-taxonomy-block', TermsPerTaxonomyBlock);
            VueReports.component('metadata-types-block', MetadataTypesBlock);
            VueReports.component('metadata-distribution-block', MetadataDistributionBlock);
            VueReports.component('collections-list-block', CollectionsListBlock);
            VueReports.component('activities-block', ActivitiesBlock);
            VueReports.component('activities-per-user-block', ActivitiesPerUserBlock);
            VueReports.component('apexchart', VueApexCharts);

            // Changing title of pages
            router.beforeEach((to, from, next) => {
                document.title = to.meta.title;
                if (next() != undefined)
                    next();
            });

            // Initialize Ponyfill for Custom CSS properties
            cssVars({
                // Options...
            });
        }
    };

    // This is rendered on the reports page.
    renderTainacanReportsPage();
};