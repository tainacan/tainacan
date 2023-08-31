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

// Metadata Types
import Text from '../components/metadata-types/text/Text.vue';
import Textarea from '../components/metadata-types/textarea/Textarea.vue';
import Selectbox from '../components/metadata-types/selectbox/Selectbox.vue';
import Numeric from '../components/metadata-types/numeric/Numeric.vue';
import Date from '../components/metadata-types/date/Date.vue';
import Relationship from '../components/metadata-types/relationship/Relationship.vue';
import Taxonomy from '../components/metadata-types/taxonomy/Taxonomy.vue';
import Compound from '../components/metadata-types/compound/Compound.vue';
import User from '../components/metadata-types/user/User.vue';
import GeoCoordinate from '../components/metadata-types/geocoordinate/GeoCoordinate.vue'

import FormText from '../components/metadata-types/text/FormText.vue';
import FormRelationship from '../components/metadata-types/relationship/FormRelationship.vue';
import FormTaxonomy from '../components/metadata-types/taxonomy/FormTaxonomy.vue';
import FormSelectbox from '../components/metadata-types/selectbox/FormSelectbox.vue';
import FormNumeric from '../components/metadata-types/numeric/FormNumeric.vue';
import FormUser from '../components/metadata-types/user/FormUser.vue';
import FormGeoCoordinate from '../components/metadata-types/geocoordinate/FormGeoCoordinate.vue';

// Term edition form must be imported here so that it is not necessary on item-submission bundle
import TermEditionForm from '../components/edition/term-edition-form.vue';

import FormFilterNumeric from '../components/filter-types/numeric/FormNumeric.vue';
import FormFilterNumericInterval from '../components/filter-types/numeric-interval/FormNumericInterval.vue';
import FormFilterNumericListInterval from '../components/filter-types/numeric-list-interval/FormNumericListInterval.vue';
// import FormDate from '../../../classes/filter-types/date/FormDate.vue';

import TainacanFormItem from '../components/metadata-types/tainacan-form-item.vue';

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
    AdminOptionsHelperPlugin 
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

            const eventBusMetadataList = mitt();
            app.config.globalProperties.$eventBusMetadataList = emitter;

            /* Registers Extra Vue Plugins passed to the window.tainacan_extra_plugins  */
            if (typeof window.tainacan_extra_plugins != "undefined") {
                for (let [extraVuePluginName, extraVuePluginObject] of Object.entries(window.tainacan_extra_plugins))
                    app.use(extraVuePluginObject);
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
                    'taianacan-tooltip': {
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
                    'tainacan-repository-tooltip': {
                        '$extend': 'tainacan-header-tooltip',
                        triggers: ['hover', 'focus', 'touch'],
                        autoHide: true,
                        html: true,
                    },
                    'tainacan-repository-tooltip': {
                        '$extend': 'tainacan-repository-header-tooltip',
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
            app.use(ConsolePlugin, {visual: false});
            app.use(CommentsStatusHelperPlugin);
            app.use(AdminOptionsHelperPlugin, pageElement.dataset['options']);


            /* Registers Extra Vue Components passed to the window.tainacan_extra_components  */
            if (typeof window.tainacan_extra_components != "undefined") {
                for (let [extraVueComponentName, extraVueComponentObject] of Object.entries(window.tainacan_extra_components)) {
                    app.component(extraVueComponentName, extraVueComponentObject);
                }
            }

            /* Metadata */
            app.component('tainacan-text', Text);
            app.component('tainacan-textarea', Textarea);
            app.component('tainacan-selectbox', Selectbox);
            app.component('tainacan-numeric', Numeric);
            app.component('tainacan-date', Date);
            app.component('tainacan-relationship', Relationship);
            app.component('tainacan-taxonomy', Taxonomy);
            app.component('tainacan-compound', Compound);
            app.component('tainacan-user', User);
            app.component('tainacan-geocoordinate', GeoCoordinate);
            

            /* Metadata Option forms */
            app.component('tainacan-form-text', FormText);
            app.component('tainacan-form-relationship', FormRelationship);
            app.component('tainacan-form-taxonomy', FormTaxonomy);
            app.component('tainacan-form-selectbox', FormSelectbox);
            app.component('tainacan-form-numeric', FormNumeric);
            app.component('tainacan-form-user', FormUser);
            app.component('term-edition-form', TermEditionForm);
            app.component('tainacan-form-geocoordinate', FormGeoCoordinate);

            /* Filter Metadata Option forms */
            app.component('tainacan-filter-form-numeric', FormFilterNumeric);
            app.component('tainacan-filter-form-numeric-interval', FormFilterNumericInterval);
            app.component('tainacan-filter-form-numeric-list-interval', FormFilterNumericListInterval);
            //  app.component('tainacan-filter-form-date', FormDate);

            // Metadadum parent containers
            app.component('tainacan-form-item', TainacanFormItem);

            /* Others */
            app.component('help-button', HelpButton);
            app.component('tainacan-title', TainacanTitle);

            // Event bus are needed to facilate comunication between child-parent-child components
            app.use(eventBusMetadataList);
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