// Main imports
import { createApp, h } from 'vue';
import {
    Field,
    Numberinput,
    Switch,
    Tabs,
    Tag,
    Icon,
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
import mitt from 'mitt';
import getDataAttribute from '../../js/compatibility/tainacan-blocks-compat-data-attributes.js';

// Metadata Types
import TainacanText from '../../../admin/components/metadata-types/text/TainacanText.vue';
import TainacanTextarea from '../../../admin/components/metadata-types/textarea/TainacanTextarea.vue';
import TainacanSelectbox from '../../../admin/components/metadata-types/selectbox/TainacanSelectbox.vue';
import TainacanNumeric from '../../../admin/components/metadata-types/numeric/TainacanNumeric.vue';
import TainacanDate from '../../../admin/components/metadata-types/date/TainacanDate.vue';
import TainacanRelationship from '../../../admin/components/metadata-types/relationship/TainacanRelationship.vue';
import TainacanTaxonomy from '../../../admin/components/metadata-types/taxonomy/TainacanTaxonomy.vue';
import TainacanCompound from '../../../admin/components/metadata-types/compound/TainacanCompound.vue';
import TainacanUser from '../../../admin/components/metadata-types/user/TainacanUser.vue';
import TainacanGeoCoordinate from '../../../admin/components/metadata-types/geocoordinate/TainacanGeoCoordinate.vue';
import TainacanURL from '../../../admin/components/metadata-types/url/TainacanURL.vue';

// Main components
import ItemSubmissionForm from './theme.vue';

// Remaining imports
import TainacanFormItem from '../../../admin/components/metadata-types/tainacan-form-item.vue';
import TermCreationPanel from './item-submission/components/term-creation-panel.vue';
import HelpButton from '../../../admin/components/other/help-button.vue';
import store from '../../../admin/js/store/store';
import { I18NPlugin, UserPrefsPlugin, RouterHelperPlugin, ConsolePlugin, StatusHelperPlugin, CommentsStatusHelperPlugin, AdminOptionsHelperPlugin } from '../../../admin/js/admin-utilities';
import { ThumbnailHelperPlugin } from '../../../admin/js/utilities';

const isParameterTrue = function(value) {
    return (value == true || value == 'true' || value == '1' || value == 1) ? true : false;
}

export default (element) => {
    function renderItemSubmissionForm() {

        // Gets the div with the content of the block
        let blockElement = element ? element : document.getElementById('tainacan-item-submission-form');

        // Mount only if the div exists
        if ( blockElement && blockElement.classList && !blockElement.classList.contains('has-mounted') ) {

            const VueItemSubmission = createApp({
                created() {
                    blockElement.classList.add('tainacan-item-submission-form'); // This used to be on the component, but as Vue now do not renders the component inside a div...
                },
                mounted() {
                    blockElement.classList.add('has-mounted');
                },
                render: () => h(ItemSubmissionForm, {
                    collectionId: getDataAttribute(blockElement, 'collection-id'),
                    hideFileModalButton: isParameterTrue(getDataAttribute(blockElement,'hide-file-modal-button')),
                    hideTextModalButton: isParameterTrue(getDataAttribute(blockElement,'hide-text-modal-button')),
                    hideLinkModalButton: isParameterTrue(getDataAttribute(blockElement,'hide-link-modal-button')),
                    hideThumbnailSection: isParameterTrue(getDataAttribute(blockElement,'hide-thumbnail-section')),
                    hideAttachmentsSection: isParameterTrue(getDataAttribute(blockElement,'hide-attachments-section')),
                    showAllowCommentsSection: isParameterTrue(getDataAttribute(blockElement,'show-allow-comments-section')),
                    hideCollapses: isParameterTrue(getDataAttribute(blockElement,'hide-collapses')),
                    hideHelpButtons: isParameterTrue(getDataAttribute(blockElement,'hide-help-buttons')),
                    hideMetadataTypes: isParameterTrue(getDataAttribute(blockElement,'hide-metadata-types')),
                    helpInfoBellowLabel: isParameterTrue(getDataAttribute(blockElement,'help-info-bellow-label')),
                    isLayoutSteps: isParameterTrue(getDataAttribute(blockElement,'is-layout-steps')),
                    documentSectionLabel: getDataAttribute(blockElement,'document-section-label'),
                    thumbnailSectionLabel: getDataAttribute(blockElement,'thumbnail-section-label'),
                    attachmentsSectionLabel: getDataAttribute(blockElement,'attachments-section-label'),
                    metadataSectionLabel: getDataAttribute(blockElement,'metadata-section-label'),
                    sentFormHeading: getDataAttribute(blockElement,'sent-form-heading'),
                    sentFormMessage: getDataAttribute(blockElement,'sent-form-message'),
                    itemLinkButtonLabel: getDataAttribute(blockElement,'item-link-button-label'),
                    showItemLinkButton: isParameterTrue(getDataAttribute(blockElement,'show-item-link-button')),
                    showTermsAgreementCheckbox: isParameterTrue(getDataAttribute(blockElement,'show-terms-agreement-checkbox')),
                    termsAgreementMessage: getDataAttribute(blockElement,'terms-agreement-message'),
                    enabledMetadata: (() => {
                        try {
                            return JSON.parse(getDataAttribute(blockElement,'enabled-metadata'));
                        } catch {
                            return {};
                        }
                    })(),
                })
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
            VueItemSubmission.use(Icon);
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
            VueItemSubmission.use(ConsolePlugin, { visual: false });
            VueItemSubmission.use(CommentsStatusHelperPlugin);
            VueItemSubmission.use(ThumbnailHelperPlugin);
            VueItemSubmission.use(AdminOptionsHelperPlugin, blockElement.dataset['options']);

            /* Registers Extra VueItemSubmission Components passed to the window.tainacan_extra_components  */
            if ( typeof window.tainacan_extra_components != "undefined" ) {
                for ( let [extraVueComponentName, extraVueComponentObject] of Object.entries(window.tainacan_extra_components) ) {
                    VueItemSubmission.component(extraVueComponentName, extraVueComponentObject);
                }
            }

            /* Metadata */
            VueItemSubmission.component('tainacan-text', TainacanText);
            VueItemSubmission.component('tainacan-textarea', TainacanTextarea);
            VueItemSubmission.component('tainacan-selectbox', TainacanSelectbox);
            VueItemSubmission.component('tainacan-numeric', TainacanNumeric);
            VueItemSubmission.component('tainacan-date', TainacanDate);
            VueItemSubmission.component('tainacan-relationship', TainacanRelationship);
            VueItemSubmission.component('tainacan-taxonomy', TainacanTaxonomy);
            VueItemSubmission.component('tainacan-compound', TainacanCompound);
            VueItemSubmission.component('tainacan-user', TainacanUser);
            VueItemSubmission.component('tainacan-geocoordinate', TainacanGeoCoordinate);
            VueItemSubmission.component('tainacan-url', TainacanURL);

            /* Others */
            VueItemSubmission.component('tainacan-form-item', TainacanFormItem);
            VueItemSubmission.component('term-creation-panel', TermCreationPanel);
            VueItemSubmission.component('help-button', HelpButton);

            // Global emitter
            const emitter = mitt();
            VueItemSubmission.config.globalProperties.$emitter = emitter;

            VueItemSubmission.mount('#' + blockElement.id);

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