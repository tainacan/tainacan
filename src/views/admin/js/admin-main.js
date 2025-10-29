// Overrides lodash by original WordPress Underscore Library
//window.lodash = _.noConflict();
//window.underscore = _.noConflict();

// Main imports
import { createApp, h } from 'vue';
import {
    Field,
    Input,
    Collapse,
    Autocomplete,
    Taginput,
    Tabs,
    Select,
    Switch,
    Upload,
    Icon,
    Button,
    Datepicker,
    Checkbox,
    Radio,
    Tag,
    Loading,
    Pagination,
    Dropdown,
    Modal,
    Dialog,
    Snackbar,
    Toast,
    Numberinput
} from 'buefy';
import FloatingVue from 'floating-vue';
import cssVars from 'css-vars-ponyfill';
import VueBlurHash from 'another-vue3-blurhash';
import VueApexCharts from 'vue3-apexcharts';

// Remaining imports
import AdminPage from '../admin.vue'
import HelpButton from '../components/other/help-button.vue';
import TainacanTitle from '../components/navigation/tainacan-title.vue';
import TainacanExternalLink from '../components/navigation/tainacan-external-link.vue';
import store from './store/store';
import router from './router';
import eventBusSearch from './event-bus-search';
import { 
    I18NPlugin,
    UserPrefsPlugin,
    RouterHelperPlugin,
    ConsolePlugin,
    UserCapabilitiesPlugin,
    StatusHelperPlugin,
    CommentsStatusHelperPlugin,
    AdminOptionsHelperPlugin,
    HtmlSanitizerPlugin,
    AxiosErrorHandlerPlugin 
} from './admin-utilities';
import { 
    ThumbnailHelperPlugin,
    OrderByHelperPlugin
} from './utilities';
import mitt from 'mitt';

/* Sets some locale configs for Reports ApexChart */
import enLocaleConfig from 'apexcharts/dist/locales/en.json';
import esLocaleConfig from 'apexcharts/dist/locales/es.json';
import frLocaleConfig from 'apexcharts/dist/locales/fr.json';
import ptBrLocaleConfig from 'apexcharts/dist/locales/pt-br.json';

export default (element) => {

    function renderTainacanAdminPage() {

        // Gets the div with the content of the page
        let pageElement = element ? element : document.getElementById('tainacan-admin-app');

        // Mount only if the div exists and it is not already mounted
        if ( pageElement && pageElement.classList && !pageElement.classList.contains('has-mounted') ) {

            const app = createApp({
                el: '#tainacan-admin-app',
                render: () => h(AdminPage)
            });
            
            app.use(router);
            app.use(store);

            const emitter = mitt();
            app.config.globalProperties.$emitter = emitter;

            /* Registers Extra Vue Plugins passed to the window.tainacan_extra_plugins  */
            if (typeof window.tainacan_extra_plugins != "undefined") {
                for (let [extraVuePluginName, extraVuePluginObject] of Object.entries(window.tainacan_extra_plugins)) {
                    const aPlugin = app.use(extraVuePluginObject);
                }
            }

            // Configure and Register Plugins
            app.use(Field);
            app.use(Input);
            app.use(Autocomplete);
            app.use(Taginput);
            app.use(Collapse);
            app.use(Button); 
            app.use(Datepicker);
            app.use(Select);
            app.use(Switch);
            app.use(Upload);
            app.use(Icon);
            app.use(Pagination);
            app.use(Checkbox);
            app.use(Radio);
            app.use(Tag);
            app.use(Tabs);
            app.use(Loading);
            app.use(Dropdown);
            app.use(Modal);
            app.use(Dialog);
            app.use(Snackbar);
            app.use(Toast);
            app.use(Numberinput);
            app.use(FloatingVue, {
                popperTriggers: ['hover', 'touch'],
                themes: {
                    'tainacan-tooltip': {
                        '$extend': 'tooltip',
                        triggers: ['hover', 'focus', 'touch'],
                        autoHide: true,
                        html: true
                    },
                    'tainacan-repository-tooltip': {
                        '$extend': 'tainacan-tooltip',
                        triggers: ['hover', 'focus', 'touch'],
                        autoHide: true,
                        html: true,
                    },
                    'tainacan-repository-header-tooltip': {
                        '$extend': 'tainacan-repository-tooltip',
                        triggers: ['hover', 'focus', 'touch'],
                        autoHide: true,
                        html: true,
                    },
                    'tainacan-helper-tooltip': {
                        '$extend': 'tainacan-tooltip',
                        triggers: ['hover', 'focus', 'touch'],
                        autoHide: true,
                        html: true,
                    }
                }
            });
            app.use(VueBlurHash);
            app.use(I18NPlugin);
            app.use(UserPrefsPlugin);
            app.use(RouterHelperPlugin);
            app.use(UserCapabilitiesPlugin);
            app.use(ThumbnailHelperPlugin);
            app.use(OrderByHelperPlugin);
            app.use(StatusHelperPlugin);
            app.use(HtmlSanitizerPlugin);
            app.use(ConsolePlugin, {visual: false});
            app.use(CommentsStatusHelperPlugin);
            app.use(AxiosErrorHandlerPlugin);
            app.use(AdminOptionsHelperPlugin, pageElement.dataset['options']);

            /* Reports-related */
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

            app.use(VueApexCharts);


            /* Registers Extra Vue Components passed to the window.tainacan_extra_components  */
            if (typeof window.tainacan_extra_components != "undefined") {
                for (let [extraVueComponentName, extraVueComponentObject] of Object.entries(window.tainacan_extra_components)) {
                    const aComponent = app.component(extraVueComponentName, extraVueComponentObject);
                }
            }

            /* Others */
            app.component('help-button', HelpButton);
            app.component('tainacan-title', TainacanTitle);
            app.component('tainacan-external-link', TainacanExternalLink)
            
            // Event bus are needed to facilate comunication between child-parent-child components
            app.use(eventBusSearch);

            // Changing title of pages
            router.beforeEach((to, from, next) => {
                document.title = to.meta.title;
                if (next() != undefined)
                    next();
            });

            app.mount('#tainacan-admin-app');

            // Initialize Ponyfill for Custom CSS properties
            cssVars({
            // Options...
            });
        }
    }

    // This is rendered on the admin page.
    renderTainacanAdminPage();
};