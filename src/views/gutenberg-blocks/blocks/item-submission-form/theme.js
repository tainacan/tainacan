// Main imports
import Vue from 'vue';
import {
    Field,
    Numberinput,
    Switch,
    Tabs,
    Tag,
    Modal,
    Checkbox,
    Collapse,
    Radio,
    Button,
    Upload,
    Autocomplete,
    Datepicker,
    Dropdown,
    Loading,
    Input,
    Select,
    Taginput,
    Snackbar,
    Steps
} from 'buefy';
import VTooltip from 'floating-vue';
import cssVars from 'css-vars-ponyfill';
import VueTheMask from 'vue-the-mask';

// Metadata Types
import Text from '../../../admin/components/metadata-types/text/Text.vue';
import Textarea from '../../../admin/components/metadata-types/textarea/Textarea.vue';
import Selectbox from '../../../admin/components/metadata-types/selectbox/Selectbox.vue';
import Numeric from '../../../admin/components/metadata-types/numeric/Numeric.vue';
import Date from '../../../admin/components/metadata-types/date/Date.vue';
import Relationship from '../../../admin/components/metadata-types/relationship/Relationship.vue';
import Taxonomy from '../../../admin/components/metadata-types/taxonomy/Taxonomy.vue';
import Compound from '../../../admin/components/metadata-types/compound/Compound.vue';
import User from '../../../admin/components/metadata-types/user/User.vue';
import GeoCoordinate from '../../../admin/components/metadata-types/geocoordinate/GeoCoordinate.vue';

// Main components
import ItemSubmissionForm from './item-submission/item-submission-form.vue';
import ItemSubmission from './theme.vue';

// Remaining imports
import TainacanFormItem from '../../../admin/components/metadata-types/tainacan-form-item.vue';
import TermCreationPanel from './item-submission/components/term-creation-panel.vue';
import HelpButton from '../../../admin/components/other/help-button.vue';
import store from '../../../admin/js/store/store';
import { I18NPlugin, UserPrefsPlugin, RouterHelperPlugin, ConsolePlugin, StatusHelperPlugin, CommentsStatusHelperPlugin, AdminOptionsHelperPlugin } from '../../../admin/js/admin-utilities';
import { ThumbnailHelperPlugin } from '../../../admin/js/utilities';

export default (element) => {
    function renderItemSubmissionForm() {

        // Vue Dev Tools!
        Vue.config.devtools = TAINACAN_ENV === 'development';

        // Gets the div with the content of the block
        let blockElement = element ? element : document.getElementById('tainacan-item-submission-form');

        // Mount only if the div exists
        if ( blockElement && blockElement.classList && !blockElement.classList.contains('has-mounted') ) {

            /* Registers Extra Vue Plugins passed to the window.tainacan_extra_plugins  */
            if (typeof window.tainacan_extra_plugins != "undefined") {
                for (let [extraVuePluginName, extraVuePluginObject] of Object.entries(window.tainacan_extra_plugins))
                    Vue.use(extraVuePluginObject);
            }

            // Configure and Register Plugins
            Vue.use(Field);
            Vue.use(Numberinput);
            Vue.use(Switch);
            Vue.use(Tabs);
            Vue.use(Tag);
            Vue.use(Checkbox);
            Vue.use(Radio);
            Vue.use(Button);
            Vue.use(Select);
            Vue.use(Loading);
            Vue.use(Dropdown);
            Vue.use(Datepicker);
            Vue.use(Upload);
            Vue.use(Taginput);
            Vue.use(Autocomplete);
            Vue.use(Collapse);
            Vue.use(Snackbar);
            Vue.use(Modal);
            Vue.use(Input);
            Vue.use(Steps);
            Vue.use(VTooltip, {
                popperTriggers: ['hover'],
                themes: {
                    'taianacan-tooltip': {
                        '$extend': 'tooltip',
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
            Vue.use(I18NPlugin);
            Vue.use(UserPrefsPlugin);
            Vue.use(StatusHelperPlugin);
            Vue.use(RouterHelperPlugin);
            Vue.use(ConsolePlugin, {visual: false});
            Vue.use(VueTheMask);
            Vue.use(CommentsStatusHelperPlugin);
            Vue.use(ThumbnailHelperPlugin);
            Vue.use(AdminOptionsHelperPlugin, blockElement.dataset['options']);

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
            Vue.component('tainacan-geocoordinate', GeoCoordinate);

            /* Main page component */
            Vue.component('item-submission-form', ItemSubmissionForm);
            Vue.component('item-submission', ItemSubmission);

            /* Others */
            Vue.component('tainacan-form-item', TainacanFormItem);
            Vue.component('term-creation-panel', TermCreationPanel);
            Vue.component('help-button', HelpButton);
                
            const VueItemSubmission = new Vue({
                store,
                data: {
                    collectionId: '',
                    hideFileModalButton: false,
                    hideTextModalButton: false,
                    hideLinkModalButton: false,
                    hideThumbnailSection: false,
                    hideAttachmentsSection: false,
                    showAllowCommentsSection: false,
                    hideHelpButtons: false,
                    hideMetadataTypes: false,
                    hideCollapses: false,
                    enabledMetadata: [],
                    sentFormHeading: '',
                    sentFormMessage: '',
                    documentSectionLabel: '',
                    thumbnailSectionLabel: '',
                    attachmentsSectionLabel: '',
                    metadataSectionLabel: '',
                    showItemLinkButton: false,
                    itemLinkButtonLabel: '',
                    helpInfoBellowLabel: false,
                    showItemLinkButton: false,
                    termsAgreementMessage: '',
                    isLayoutSteps: false
                },
                beforeMount () {
                    // Collection source settings
                    if (this.$el.attributes['collection-id'] != undefined)
                        this.collectionId = this.$el.attributes['collection-id'].value;

                    // Elements shown on form
                    if (this.$el.attributes['hide-file-modal-button'] != undefined)
                        this.hideFileModalButton = this.isParameterTrue('hide-file-modal-button');
                    if (this.$el.attributes['hide-text-modal-button'] != undefined)
                        this.hideTextModalButton = this.isParameterTrue('hide-text-modal-button');
                    if (this.$el.attributes['hide-link-modal-button'] != undefined)
                        this.hideLinkModalButton = this.isParameterTrue('hide-link-modal-button');
                    if (this.$el.attributes['hide-thumbnail-section'] != undefined)
                        this.hideThumbnailSection = this.isParameterTrue('hide-thumbnail-section');
                    if (this.$el.attributes['hide-attachments-section'] != undefined)
                        this.hideAttachmentsSection = this.isParameterTrue('hide-attachments-section');
                    if (this.$el.attributes['show-allow-comments-section'] != undefined)
                        this.showAllowCommentsSection = this.isParameterTrue('show-allow-comments-section');
                    if (this.$el.attributes['hide-collapses'] != undefined)
                        this.hideCollapses = this.isParameterTrue('hide-collapses');
                    if (this.$el.attributes['hide-help-buttons'] != undefined)
                        this.hideHelpButtons = this.isParameterTrue('hide-help-buttons');
                    if (this.$el.attributes['hide-metadata-types'] != undefined)
                        this.hideMetadataTypes = this.isParameterTrue('hide-metadata-types');
                    if (this.$el.attributes['help-info-bellow-label'] != undefined)
                        this.helpInfoBellowLabel = this.isParameterTrue('help-info-bellow-label');
                    if (this.$el.attributes['is-layout-steps'] != undefined)
                        this.isLayoutSteps = this.isParameterTrue('is-layout-steps');

                    // Form sections labels
                    if (this.$el.attributes['document-section-label'] != undefined)
                        this.documentSectionLabel = this.$el.attributes['document-section-label'].value;
                    if (this.$el.attributes['thumbnail-section-label'] != undefined)
                        this.thumbnailSectionLabel = this.$el.attributes['thumbnail-section-label'].value;
                    if (this.$el.attributes['attachments-section-label'] != undefined)
                        this.attachmentsSectionLabel = this.$el.attributes['attachments-section-label'].value;
                    if (this.$el.attributes['metadata-section-label'] != undefined)
                        this.metadataSectionLabel = this.$el.attributes['metadata-section-label'].value;

                    // Form submission feedback messages
                    if (this.$el.attributes['sent-form-heading'] != undefined)
                        this.sentFormHeading = this.$el.attributes['sent-form-heading'].value;
                    if (this.$el.attributes['sent-form-message'] != undefined)
                        this.sentFormMessage = this.$el.attributes['sent-form-message'].value;
                    if (this.$el.attributes['item-link-button-label'] != undefined)
                        this.itemLinkButtonLabel = this.$el.attributes['item-link-button-label'].value;
                    if (this.$el.attributes['show-item-link-button'] != undefined)
                        this.showItemLinkButton = this.isParameterTrue('show-item-link-button');
                    
                    /* Terms agreements confirmation checkbox */
                    if (this.$el.attributes['show-terms-agreement-checkbox'] != undefined)
                        this.showTermsAgreementCheckbox = this.isParameterTrue('show-terms-agreement-checkbox');
                    if (this.$el.attributes['terms-agreement-message'] != undefined)
                        this.termsAgreementMessage = this.$el.attributes['terms-agreement-message'].value;
    
                    // List of metadata
                    if (this.$el.attributes['enabled-metadata'] != undefined && this.$el.attributes['enabled-metadata'].value)
                        this.enabledMetadata = this.$el.attributes['enabled-metadata'].value.split(',');

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

            // Initialize Ponyfill for Custom CSS properties
            cssVars({
                // Options...
            });
        }

    }

    // This is rendered on the theme side.
    renderItemSubmissionForm();

    // Also if a theme or plugin requested a reset...
    document.addEventListener("TainacanReloadItemSubmissionForm", () => {
        renderItemSubmissionForm();
    });
};