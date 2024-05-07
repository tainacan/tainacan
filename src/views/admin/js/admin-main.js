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
} from '@ntohq/buefy-next';
import FloatingVue from 'floating-vue';
import cssVars from 'css-vars-ponyfill';
import VueBlurHash from 'another-vue3-blurhash';

// Remaining imports
import AdminPage from '../admin.vue'
import HelpButton from '../components/other/help-button.vue';
import TainacanTitle from '../components/navigation/tainacan-title.vue';
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

// import { configureCompat } from 'vue';
// configureCompat({
//     COMPONENT_V_MODEL: false,
//     ATTR_FALSE_VALUE: false,
//     RENDER_FUNCTION: false,
//     MODE: 3
// })

function copyAppContext(src, dest) {
    // replacing _context won't work because methods of app bypasses app._context
    const { _context: srcContext } = src
    const { _context: destContext } = dest
    destContext.config = srcContext.config
    destContext.mixins = srcContext.mixins
    destContext.components = srcContext.components
    destContext.directives = srcContext.directives
    destContext.provdes = srcContext.provides
    destContext.optionsCache = srcContext.optionsCache
    destContext.propsCache = srcContext.propsCache
    destContext.emitsCache = srcContext.emitsCache
}

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
                    //copyAppContext(app, aPlugin);
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
                    'tainacan-header-tooltip': {
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


            /* Registers Extra Vue Components passed to the window.tainacan_extra_components  */
            if (typeof window.tainacan_extra_components != "undefined") {
                for (let [extraVueComponentName, extraVueComponentObject] of Object.entries(window.tainacan_extra_components)) {
                    const aComponent = app.component(extraVueComponentName, extraVueComponentObject);
                    ///copyAppContext(app, aComponent);
                }
            }

            /* Others */
            app.component('help-button', HelpButton);
            app.component('tainacan-title', TainacanTitle);
            
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