// Main imports
import { createApp, h } from 'vue';
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
} from '@ntohq/buefy-next';
import VTooltip from 'floating-vue';
import cssVars from 'css-vars-ponyfill';

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

        // Gets the div with the content of the block
        let blockElement = element ? element : document.getElementById('tainacan-item-submission-form');

        // Mount only if the div exists
        if ( blockElement && blockElement.classList && !blockElement.classList.contains('has-mounted') ) {

            const VueItemSubmission = createApp({
                el: '#tainacan-item-submission-form',
                data: () => {
                    return {
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
                        enabledMetadata: {},
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
                    }
                },
                beforeMount () {
                    // Collection source settings
                    if (blockElement.attributes['collection-id'] != undefined)
                        this.collectionId = blockElement.attributes['collection-id'].value;

                    // Elements shown on form
                    if (blockElement.attributes['hide-file-modal-button'] != undefined)
                        this.hideFileModalButton = this.isParameterTrue('hide-file-modal-button');
                    if (blockElement.attributes['hide-text-modal-button'] != undefined)
                        this.hideTextModalButton = this.isParameterTrue('hide-text-modal-button');
                    if (blockElement.attributes['hide-link-modal-button'] != undefined)
                        this.hideLinkModalButton = this.isParameterTrue('hide-link-modal-button');
                    if (blockElement.attributes['hide-thumbnail-section'] != undefined)
                        this.hideThumbnailSection = this.isParameterTrue('hide-thumbnail-section');
                    if (blockElement.attributes['hide-attachments-section'] != undefined)
                        this.hideAttachmentsSection = this.isParameterTrue('hide-attachments-section');
                    if (blockElement.attributes['show-allow-comments-section'] != undefined)
                        this.showAllowCommentsSection = this.isParameterTrue('show-allow-comments-section');
                    if (blockElement.attributes['hide-collapses'] != undefined)
                        this.hideCollapses = this.isParameterTrue('hide-collapses');
                    if (blockElement.attributes['hide-help-buttons'] != undefined)
                        this.hideHelpButtons = this.isParameterTrue('hide-help-buttons');
                    if (blockElement.attributes['hide-metadata-types'] != undefined)
                        this.hideMetadataTypes = this.isParameterTrue('hide-metadata-types');
                    if (blockElement.attributes['help-info-bellow-label'] != undefined)
                        this.helpInfoBellowLabel = this.isParameterTrue('help-info-bellow-label');
                    if (blockElement.attributes['is-layout-steps'] != undefined)
                        this.isLayoutSteps = this.isParameterTrue('is-layout-steps');

                    // Form sections labels
                    if (blockElement.attributes['document-section-label'] != undefined)
                        this.documentSectionLabel = blockElement.attributes['document-section-label'].value;
                    if (blockElement.attributes['thumbnail-section-label'] != undefined)
                        this.thumbnailSectionLabel = blockElement.attributes['thumbnail-section-label'].value;
                    if (blockElement.attributes['attachments-section-label'] != undefined)
                        this.attachmentsSectionLabel = blockElement.attributes['attachments-section-label'].value;
                    if (blockElement.attributes['metadata-section-label'] != undefined)
                        this.metadataSectionLabel = blockElement.attributes['metadata-section-label'].value;

                    // Form submission feedback messages
                    if (blockElement.attributes['sent-form-heading'] != undefined)
                        this.sentFormHeading = blockElement.attributes['sent-form-heading'].value;
                    if (blockElement.attributes['sent-form-message'] != undefined)
                        this.sentFormMessage = blockElement.attributes['sent-form-message'].value;
                    if (blockElement.attributes['item-link-button-label'] != undefined)
                        this.itemLinkButtonLabel = blockElement.attributes['item-link-button-label'].value;
                    if (blockElement.attributes['show-item-link-button'] != undefined)
                        this.showItemLinkButton = this.isParameterTrue('show-item-link-button');
                    
                    /* Terms agreements confirmation checkbox */
                    if (blockElement.attributes['show-terms-agreement-checkbox'] != undefined)
                        this.showTermsAgreementCheckbox = this.isParameterTrue('show-terms-agreement-checkbox');
                    if (blockElement.attributes['terms-agreement-message'] != undefined)
                        this.termsAgreementMessage = blockElement.attributes['terms-agreement-message'].value;
    
                    // List of metadata
                    if (this.$el.attributes['enabled-metadata'] != undefined && this.$el.attributes['enabled-metadata'].value) {
                        try {
                            this.enabledMetadata = JSON.parse(this.$el.attributes['enabled-metadata'].value);
                        } catch {
                            this.enabledMetadata = {};
                        }
                    }

                },
                methods: {
                    isParameterTrue(parameter) {
                        const value = blockElement.attributes[parameter].value;
                        return (value == true || value == 'true' || value == '1' || value == 1) ? true : false;
                    }
                },
                render: () => h(ItemSubmission)
            });

            VueItemSubmission.use(store);

            /* Registers Extra VueItemSubmission Plugins passed to the window.tainacan_extra_plugins  */
            if (typeof window.tainacan_extra_plugins != "undefined") {
                for (let [extraVuePluginName, extraVuePluginObject] of Object.entries(window.tainacan_extra_plugins))
                    VueItemSubmission.use(extraVuePluginObject);
            }

            // Configure and Register Plugins
            VueItemSubmission.use(Field);
            VueItemSubmission.use(Numberinput);
            VueItemSubmission.use(Switch);
            VueItemSubmission.use(Tabs);
            VueItemSubmission.use(Tag);
            VueItemSubmission.use(Checkbox);
            VueItemSubmission.use(Radio);
            VueItemSubmission.use(Button);
            VueItemSubmission.use(Select);
            VueItemSubmission.use(Loading);
            VueItemSubmission.use(Dropdown);
            VueItemSubmission.use(Datepicker);
            VueItemSubmission.use(Upload);
            VueItemSubmission.use(Taginput);
            VueItemSubmission.use(Autocomplete);
            VueItemSubmission.use(Collapse);
            VueItemSubmission.use(Snackbar);
            VueItemSubmission.use(Modal);
            VueItemSubmission.use(Input);
            VueItemSubmission.use(Steps);
            VueItemSubmission.use(VTooltip, {
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
            VueItemSubmission.use(I18NPlugin);
            VueItemSubmission.use(UserPrefsPlugin);
            VueItemSubmission.use(StatusHelperPlugin);
            VueItemSubmission.use(RouterHelperPlugin);
            VueItemSubmission.use(ConsolePlugin, {visual: false});
            VueItemSubmission.use(CommentsStatusHelperPlugin);
            VueItemSubmission.use(ThumbnailHelperPlugin);
            VueItemSubmission.use(AdminOptionsHelperPlugin, blockElement.dataset['options']);

            /* Registers Extra VueItemSubmission Components passed to the window.tainacan_extra_components  */
            if (typeof window.tainacan_extra_components != "undefined") {
                for (let [extraVueComponentName, extraVueComponentObject] of Object.entries(window.tainacan_extra_components)) {
                    VueItemSubmission.component(extraVueComponentName, extraVueComponentObject);
                }
            }

            /* Metadata */
            VueItemSubmission.component('tainacan-text', Text);
            VueItemSubmission.component('tainacan-textarea', Textarea);
            VueItemSubmission.component('tainacan-selectbox', Selectbox);
            VueItemSubmission.component('tainacan-numeric', Numeric);
            VueItemSubmission.component('tainacan-date', Date);
            VueItemSubmission.component('tainacan-relationship', Relationship);
            VueItemSubmission.component('tainacan-taxonomy', Taxonomy);
            VueItemSubmission.component('tainacan-compound', Compound);
            VueItemSubmission.component('tainacan-user', User);
            VueItemSubmission.component('tainacan-geocoordinate', GeoCoordinate);

            /* Main page component */
            VueItemSubmission.component('item-submission-form', ItemSubmissionForm);
            VueItemSubmission.component('item-submission', ItemSubmission);

            /* Others */
            VueItemSubmission.component('tainacan-form-item', TainacanFormItem);
            VueItemSubmission.component('term-creation-panel', TermCreationPanel);
            VueItemSubmission.component('help-button', HelpButton);

            VueItemSubmission.mount('#tainacan-item-submission-form');

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