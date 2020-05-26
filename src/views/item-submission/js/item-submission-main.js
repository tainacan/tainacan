// Main imports
import Vue from 'vue';
import Buefy from 'buefy';
import VTooltip from 'v-tooltip';
import cssVars from 'css-vars-ponyfill';
import VueTheMask from 'vue-the-mask';
import qs from 'qs';

// Metadata Types
import Text from '../../admin/components/metadata-types/text/Text.vue';
import Textarea from '../../admin/components/metadata-types/textarea/Textarea.vue';
import Selectbox from '../../admin/components/metadata-types/selectbox/Selectbox.vue';
import Numeric from '../../admin/components/metadata-types/numeric/Numeric.vue';
import Date from '../../admin/components/metadata-types/date/Date.vue';
import Relationship from '../../admin/components/metadata-types/relationship/Relationship.vue';
import Taxonomy from '../../admin/components/metadata-types/taxonomy/Taxonomy.vue';
import Compound from '../../admin/components/metadata-types/compound/Compound.vue';
import User from '../../admin/components/metadata-types/user/User.vue';

// Main components
import ItemSubmissionForm from '../pages/item-submission-form.vue';
import ItemSubmission from '../item-submission.vue';

// Remaining imports
import TainacanFormItem from '../../admin/components/metadata-types/tainacan-form-item.vue';
import HelpButton from '../../admin/components/other/help-button.vue';
import store from '../../admin/js/store/store';
import { I18NPlugin, UserPrefsPlugin, RouterHelperPlugin, ConsolePlugin, StatusHelperPlugin, CommentsStatusHelperPlugin } from '../../admin/js/utilities';

document.addEventListener("DOMContentLoaded", () => {

    // Mount only if the div exists
    if (document.getElementById('tainacan-item-submission-form')) {


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

        /* Registers Extra Vue Plugins passed to the window.tainacan_extra_plugins  */
        if (typeof window.tainacan_extra_plugins != "undefined") {
            for (let [extraVuePluginName, extraVuePluginObject] of Object.entries(window.tainacan_extra_plugins))
                Vue.use(extraVuePluginObject);
        }

        // Configure and Register Plugins
        Vue.use(Buefy, {
            defaultTooltipAnimated: true
        });
        Vue.use(VTooltip);
        Vue.use(I18NPlugin);
        Vue.use(UserPrefsPlugin);
        Vue.use(StatusHelperPlugin);
        Vue.use(RouterHelperPlugin);
        Vue.use(ConsolePlugin, {visual: false});
        Vue.use(VueTheMask);
        Vue.use(CommentsStatusHelperPlugin);

        /* Registers Extra Vue Components passed to the window.tainacan_extra_components  */
        if (typeof window.tainacan_extra_components != "undefined") {
            for (let [extraVueComponentName, extraVueComponentObject] of Object.entries(window.tainacan_extra_components)) {
                Vue.component(extraVueComponentName, extraVueComponentObject);
            }
        }

        /* Metadata */
        Vue.component('tainacan-text', Text);
        Vue.component('tainacan-textarea', Textarea);
        Vue.component('tainacan-selectbox', Selectbox);
        Vue.component('tainacan-numeric', Numeric);
        Vue.component('tainacan-date', Date);
        Vue.component('tainacan-relationship', Relationship);
        Vue.component('tainacan-taxonomy', Taxonomy);
        Vue.component('tainacan-compound', Compound);
        Vue.component('tainacan-user', User);

        /* Main page component */
        Vue.component('item-submission-form', ItemSubmissionForm);
        Vue.component('item-submission', ItemSubmission);

        /* Others */
        Vue.component('tainacan-form-item', TainacanFormItem);
        Vue.component('help-button', HelpButton);
            
        const VueItemSubmission = new Vue({
            store,
            data: {
                collectionId: ''
            },
            beforeMount () {
                // Collection source settings
                if (this.$el.attributes['collection-id'] != undefined)
                    this.collectionId = this.$el.attributes['collection-id'].value;
            },
            methods: {
                isParameterTrue(parameter) {
                    const value = this.$el.attributes[parameter].value;
                    return (value == true || value == 'true' || value == '1' || value == 1) ? true : false;
                }
            },
            render: h => h(ItemSubmission)
        });

        VueItemSubmission.$mount('#tainacan-item-submission-form');

        listen("load", window, function() {
            var iconsStyle = document.createElement("style");
            iconsStyle.setAttribute('type', 'text/css');
            iconsStyle.innerText = '.tainacan-icon{ opacity: 1 !important; }';
            document.head.appendChild(iconsStyle);
        });

        // Initialize Ponyfill for Custom CSS properties
        cssVars({
            // Options...
        });
    }
});