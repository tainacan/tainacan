<template>
    <div v-if="collectionId">
        <b-loading
                :is-full-page="false"
                :active.sync="isLoading"
                :can-cancel="false"/>
        <form
                v-if="!hasSentForm"
                v-show="!isLoading && !isSubmitting && !isUploading"
                class="tainacan-form"
                label-width="120px">
    
            <!-- Hook for extra Form options -->
            <template
                    v-if="formHooks != undefined &&
                        formHooks['item'] != undefined &&
                        formHooks['item']['begin-left'] != undefined">
                <form
                        id="form-item-begin-left"
                        class="form-hook-region"
                        v-html="formHooks['item']['begin-left'].join('')"/>
            </template>

            <!-- Document -------------------------------- -->
            <template v-if="!hideFileModalButton || !hideTextModalButton || !hideLinkModalButton">
                <div 
                        v-if="documentSectionLabel"
                        class="section-label">
                    <label>{{ documentSectionLabel }}</label>
                    <help-button
                            v-if="!hideHelpButtons"
                            :title="$i18n.getHelperTitle('items', 'document')"
                            :message="$i18n.getHelperMessage('items', 'document')"/>
                </div>
                <div 
                        class="section-box document-field"
                        id="tainacan-item-metadatum_id-document">
                    <div v-if="form.document_type != '' && form.document_type != undefined && form.document_type != null && form.document_type != 'empty'">
                        <div v-if="form.document_type == 'attachment'">
                            <b-upload
                                    expanded 
                                    v-if="!form.document"
                                    v-model="form.document"
                                    drag-drop>
                                <section class="section">
                                    <div class="content has-text-centered">
                                        <p>
                                            <span class="icon">
                                                <i class="tainacan-icon tainacan-icon-36px tainacan-icon-upload" />
                                            </span>
                                        </p>
                                        <p>{{ $i18n.get('instruction_drop_file_or_click_to_upload') }}</p>
                                    </div>
                                </section>
                            </b-upload>
                            <div 
                                    v-else
                                    class="files-list">
                                <b-tag 
                                        rounded
                                        closable
                                        attached
                                        :aria-close-label="$i18n.get('delete')"
                                        @close="form.document = ''"
                                        :type="formErrors.find(error => error.metadatum_id== 'document') ? 'is-danger' : ''">
                                    {{ form.document.name }}
                                </b-tag>
                            </div>
                        </div>
                        <div v-if="form.document_type == 'text'">
                            <b-input 
                                    type="textarea"
                                    v-model="form.document" />
                            <br v-if="hasMoreThanOneDocumentTypeOption">                      
                        </div>
                        <div v-if="form.document_type == 'url'">
                            <b-input 
                                    :placeholder="$i18n.get('instruction_insert_url')"
                                    type="url"
                                    v-model="form.document" />
                            <br v-if="hasMoreThanOneDocumentTypeOption">
                        </div>
                        <button
                                v-if="hasMoreThanOneDocumentTypeOption"
                                type="button"
                                class="button is-outlined"
                                @click="form.document = ''; form.document_type = 'empty'">
                            {{ $i18n.get('label_switch_document_type') }}
                        </button>
                    </div>
                    <ul v-else>
                        <li v-if="!hideFileModalButton">
                            <button
                                    type="button"
                                    @click.prevent="form.document_type = 'attachment'">
                                <span class="icon">
                                    <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-upload"/>
                                </span>
                            </button>
                            <p>{{ $i18n.get('label_file') }}</p>
                        </li>
                        <li v-if="!hideTextModalButton">
                            <button
                                    type="button"
                                    @click.prevent="form.document_type = 'text'">
                                <span class="icon">
                                    <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-text"/>
                                </span>
                            </button>
                            <p>{{ $i18n.get('label_text') }}</p>
                        </li>
                        <li v-if="!hideLinkModalButton">
                            <button
                                    type="button"
                                    @click.prevent="form.document_type = 'url'">
                                <span class="icon">
                                    <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-url"/>
                                </span>
                            </button>
                            <p>{{ $i18n.get('label_url') }}</p>
                        </li>
                    </ul>
                </div>
               
            </template>

            <!-- Thumbnail -------------------------------- -->
            <template v-if="!hideThumbnailSection">
                <div 
                        v-if="thumbnailSectionLabel"
                        class="section-label">
                    <label>{{ thumbnailSectionLabel }}</label>
                    <help-button
                            v-if="!hideHelpButtons"
                            :title="$i18n.getHelperTitle('items', '_thumbnail_id')"
                            :message="$i18n.getHelperMessage('items', '_thumbnail_id')"/>

                </div>
                <div class="section-toggle">
                    <p>{{ showThumbnailInput ? $i18n.get('info_thumbnail_custom') : $i18n.get('info_thumbnail_default_from_document') }}</p>
                    <div class="field has-addons">
                        <b-switch
                                id="tainacan-checkbox-show-thumbnail-input"
                                size="is-small"
                                v-model="showThumbnailInput">
                            {{ $i18n.get('label_upload_custom_thumbnail') }}
                        </b-switch>
                    </div>
                </div>
                <div 
                        v-if="!isLoading && showThumbnailInput"
                        class="section-box section-thumbnail"
                        id="tainacan-item-metadatum_id-thumbnail">
                    <b-upload 
                            expanded
                            v-if="!form.thumbnail"
                            v-model="form.thumbnail"
                            drag-drop>
                        <section class="section">
                            <div class="content has-text-centered">
                                <p>
                                    <span class="icon">
                                        <i class="tainacan-icon tainacan-icon-36px tainacan-icon-upload" />
                                    </span>
                                </p>
                                <p>{{ $i18n.get('instruction_drop_file_or_click_to_upload') }}</p>
                            </div>
                        </section>
                    </b-upload>
                    <div 
                            v-else
                            class="files-list">
                        <b-tag 
                                rounded
                                closable
                                attached
                                :aria-close-label="$i18n.get('delete')"
                                @close="form.thumbnail = null"
                                :type="formErrors.find(error => error.metadatum_id == 'thumbnail') ? 'is-danger' : ''">
                            {{ form.thumbnail.name }}
                        </b-tag>
                    </div>
                </div>
            </template>

            <!-- Hook for extra Form options -->
            <template
                    v-if="formHooks != undefined &&
                        formHooks['item'] != undefined &&
                        formHooks['item']['end-left'] != undefined">
                <form
                    id="form-item-end-left"
                    class="form-hook-region"
                    v-html="formHooks['item']['end-left'].join('')"/>
            </template>

            <!-- Attachments ------------------------------------------ -->
            <template v-if="!hideAttachmentsSection">
                
                <div class="section-label">
                    <label v-if="attachmentsSectionLabel">
                        <span>{{ attachmentsSectionLabel }}</span>
                        <span class="icon has-text-gray4">
                            <i class="tainacan-icon tainacan-icon-18px tainacan-icon-attachments"/>
                        </span>
                    </label>
                </div>                

                <div 
                        v-if="itemSubmission != undefined"
                        class="section-box"
                        id="tainacan-item-metadatum_id-attachments">
                    <b-upload 
                            expanded
                            v-model="form.attachments"
                            multiple
                            drag-drop>
                        <section class="section">
                            <div class="content has-text-centered">
                                <p>
                                    <span class="icon">
                                        <i class="tainacan-icon tainacan-icon-36px tainacan-icon-upload" />
                                    </span>
                                </p>
                                <p>{{ $i18n.get('instruction_drop_file_or_click_to_upload') }}</p>
                            </div>
                        </section>
                    </b-upload>
                    <div 
                            v-if="form.attachments && form.attachments.length"
                            class="files-list">
                        <b-tag 
                                v-for="(attachment, index) of form.attachments"
                                :key="index"
                                rounded
                                closable
                                attached
                                :aria-close-label="$i18n.get('delete')"
                                @close="form.attachments.splice(index, 1)"
                                :type="formErrors.find(error => error.metadatum_id == 'attachments') ? 'is-danger' : ''">
                            {{ attachment.name }}
                        </b-tag>
                    </div>
                </div>
            </template>
        
            <!-- Hook for extra Form options -->
            <template
                    v-if="formHooks != undefined &&
                        formHooks['item'] != undefined &&
                        formHooks['item']['begin-right'] != undefined">
                <form
                    id="form-item-begin-right"
                    class="form-hook-region"
                    v-html="formHooks['item']['begin-right'].join('')"/>
            </template>

            <!-- Comment Status ------------------------ -->
            <template v-if="showAllowCommentsSection">
                <div class="section-label">
                    <label>{{ $i18n.get('label_comments') }}</label>
                    <help-button
                            v-if="!hideHelpButtons"
                            :title="$i18n.getHelperTitle('items', 'comment_status')"
                            :message="$i18n.getHelperMessage('items', 'comment_status')"/>
                </div>
                <div class="section-toggle">
                    <div class="field has-addons">
                        <b-switch
                                id="tainacan-checkbox-comment-status"
                                size="is-small"
                                true-value="open"
                                false-value="closed"
                                v-model="form.comment_status">
                            {{ $i18n.get('label_allow_comments') }}
                        </b-switch>
                    </div>
                </div>
            </template>

            <!-- Metadata from Collection-------------------------------- -->
            <div class="section-label">
                <label v-if="metadataSectionLabel">
                    <span>{{ metadataSectionLabel }}</span>
                    <span class="icon has-text-gray4">
                        <i class="tainacan-icon tainacan-icon-18px tainacan-icon-metadata"/>
                    </span>
                </label>
            </div>

            <a
                    v-if="!hideCollapses"
                    class="collapse-all"
                    @click="toggleCollapseAll()">
                {{ collapseAll ? $i18n.get('label_collapse_all') : $i18n.get('label_expand_all') }}
                <span class="icon">
                    <i
                            :class="{ 'tainacan-icon-arrowdown' : collapseAll, 'tainacan-icon-arrowright' : !collapseAll }"
                            class="tainacan-icon tainacan-icon-1-25em"/>
                </span>
            </a>
            <template v-for="(itemMetadatum, index) of metadatumList">
                <tainacan-form-item
                        :key="index"
                        v-if="enabledMetadata[index] == 'true'"
                        :item-metadatum="itemMetadatum"
                        :hide-collapses="hideCollapses"
                        :is-collapsed="metadataCollapses[index]"
                        @changeCollapse="onChangeCollapse($event, index)"/>
            </template>

            <!-- Hook for extra Form options -->
            <template
                    v-if="formHooks != undefined &&
                        formHooks['item'] != undefined &&
                        formHooks['item']['end-right'] != undefined">
                <form
                    id="form-item-end-right"
                    class="form-hook-region"
                    v-html="formHooks['item']['end-right'].join('')"/>
            </template>

            <footer class="form-submission-footer">

                <button
                        @click="onDiscard()"
                        type="button"
                        class="button is-outlined">{{ $i18n.get('cancel') }}</button>

                <!-- Updated and Error Info -->
                <div class="update-info-section">
                    <p class="footer-message">

                        <span class="help is-danger">
                            {{ formErrorMessage }}
                            <item-metadatum-errors-tooltip 
                                    v-if="formErrors.length && formErrors[0].errors && formErrors[0].errors.length"
                                    :form-errors="formErrors" />
                        </span>
                    </p>
                </div>

                <button
                        @click="onSubmit()"
                        type="button"
                        class="button is-secondary">{{ $i18n.get('label_submit') }}</button>
            </footer>
        </form>

        <!-- Message displayed when the form is being submitted -->
        <section 
                v-if="isSubmitting || isUploading"
                class="section">
            <div class="content has-text-grey has-text-centered">
                <br>
                <p>
                    <span class="icon is-medium">
                        <i class="tainacan-icon tainacan-icon-30px tainacan-icon-updating tainacan-icon-spin"/>
                    </span>
                </p>
                <h2>{{ $i18n.get('label_sending_form') }}</h2>
                <p v-if="isSubmitting">{{ $i18n.get('info_submission_processing') }}</p>
                <p v-if="isUploading">{{ $i18n.get('info_submission_uploading') }}</p>
                <br>
            </div>
        </section>

        <!-- Message displayed once the form is submitted -->
        <section 
                v-if="hasSentForm"
                class="section">
            <div class="content has-text-grey has-text-centered">
                <br>
                <p>
                    <span class="icon is-medium">
                        <i class="tainacan-icon tainacan-icon-30px tainacan-icon-approvedcircle"/>
                    </span>
                </p>
                <h2 v-if="sentFormHeading">{{ sentFormHeading }}</h2>
                <p v-if="sentFormMessage">{{ sentFormMessage }}</p>
                <br>
            </div>
        </section>
    </div>
</template>

<script>
import { mapActions, mapGetters } from 'vuex';
import { eventBusItemMetadata } from '../../admin/js/event-bus-item-metadata';
import { formHooks } from '../../admin/js/mixins';
import ItemMetadatumErrorsTooltip from '../../admin/components/other/item-metadatum-errors-tooltip.vue';

export default {
    name: 'ItemSubmissionForm',
    components: {
        ItemMetadatumErrorsTooltip,
    },
    mixins: [ formHooks ],
    props: {
        collectionId: String,
        hideFileModalButton: Boolean,
        hideTextModalButton: Boolean,
        hideLinkModalButton: Boolean,
        hideThumbnailSection: Boolean,
        hideAttachmentsSection: Boolean,
        showAllowCommentsSection: Boolean,
        hideCollapses: Boolean,
        hideHelpButtons: Boolean,
        hideMetadataTypes: Boolean,
        enabledMetadata: Array,
        sentFormHeading: String,
        sentFormMessage: String,
        documentSectionLabel: String,
        thumbnailSectionLabel: String,
        attachmentsSectionLabel: String,
        metadataSectionLabel: String,
        useCaptcha: Boolean,
        captchaSiteKey: String,
        captchaSecretKey: String
    },
    data(){
        return {
            isLoading: false,
            isSubmitting: false,
            isUploading: false,
            metadataCollapses: [],
            collapseAll: true,
            form: {
                collection_id: Number,
                document: '',
                document_type: '',
                comment_status: '',
                attachments: [],
                thumbnail: ''
            },
            formErrorMessage: '',
            hasSentForm: false,
            showThumbnailInput: false
        }
    },
    computed: {
        itemSubmission() {
            return this.getItemSubmission();
        },
        itemSubmissionMetadata() {
            return this.getItemSubmissionMetadata();
        },
        metadatumList() {
            return (this.itemSubmissionMetadata && this.itemSubmissionMetadata.length) ? JSON.parse(JSON.stringify(this.getMetadata().map((metadatum) => { return { metadatum: metadatum, item: {}, value: this.itemSubmissionMetadata.find((aMetadatum) => aMetadatum.metadatum_id == metadatum.id).value } } ))) : [];
        },
        formErrors() {
           return eventBusItemMetadata && eventBusItemMetadata.errors && eventBusItemMetadata.errors.length ? eventBusItemMetadata.errors : []
        },
        hasMoreThanOneDocumentTypeOption() {
            return [ this.hideFileModalButton, this.hideTextModalButton, this.hideLinkModalButton ].filter((option) => { return option == false }).length > 1;
        }
    },
    created() {

        // Initialize clear data from store
        this.clearItemSubmission();
        
        eventBusItemMetadata.clearAllErrors();
        this.formErrorMessage = '';
        this.form.collection_id = this.collectionId;

        // CREATING NEW ITEM SUBMISSION
        this.createNewItem();

        eventBusItemMetadata.$on('hasErrorsOnForm', (hasErrors) => {
            if (hasErrors)
                this.formErrorMessage = this.formErrorMessage ? this.formErrorMessage : this.$i18n.get('info_errors_in_form');
            else
                this.formErrorMessage = '';
        });
    },
    mounted() {
        // Checks if only one type of document is allowed. In this case we preset document type
        if (!this.hideFileModalButton && this.hideTextModalButton && this.hideLinkModalButton)
            this.form.document_type = 'attachment';
        else if (this.hideFileModalButton && !this.hideTextModalButton && this.hideLinkModalButton)
            this.form.document_type = 'text';
        else if (this.hideFileModalButton && this.hideTextModalButton && !this.hideLinkModalButton)
            this.form.document_type = 'url';
    },
    beforeDestroy () {
        eventBusItemMetadata.$off('hasErrorsOnForm');
    },
    methods: {
        ...mapActions('item', [
            'setItemSubmission',
            'setItemSubmissionMetadata',
            'submitItemSubmission',
            'finishItemSubmission',
            'clearItemSubmission'
        ]),
        ...mapGetters('item',[
            'getItemSubmission',
            'getItemSubmissionMetadata',
        ]),
        ...mapActions('metadata',[
            'fetchMetadata'
        ]),
        ...mapGetters('metadata',[
            'getMetadata'
        ]),
        onSubmit() {

            // Puts loading on Item edition
            this.isSubmitting = true;

            let data = this.form;
            this.fillExtraFormData(data);
            this.updateExtraFormData(data);
            this.setItemSubmission(Object.assign(this.itemSubmission, data));

            // Clear errors so we don't have them duplicated from api
            eventBusItemMetadata.errors = [];

            this.submitItemSubmission({ itemSubmission: this.itemSubmission, itemSubmissionMetadata: this.itemSubmissionMetadata })
                .then((fakeItemId) => {

                    this.isUploading = true;
                    this.isSubmitting = false;

                    if (fakeItemId) {
                        this.finishItemSubmission({ itemSubmission: this.itemSubmission, fakeItemId: fakeItemId })
                            .then(() => {
                                this.hasSentForm = true;
                                this.isUploading = false;
                            })
                            .catch((errors) => { 
                                if (errors.errors) {
                                    for (let error of errors.errors) {
                                        for (let metadatum of Object.keys(error)) {
                                            eventBusItemMetadata.errors.push({ 
                                                metadatum_id: metadatum,
                                                errors: error[metadatum]
                                            });
                                        }  
                                    }
                                }
                                this.formErrorMessage = errors.error_message;
                                
                                this.isSubmitting =  false;
                                this.hasSentForm = false;
                                this.isUploading = false;
                            });
                    }
                })
                .catch((errors) => { 
                    if (errors.errors) {
                        for (let error of errors.errors) {
                            for (let metadatum of Object.keys(error)) {
                                eventBusItemMetadata.errors.push({ 
                                    metadatum_id: metadatum,
                                    errors: error[metadatum]
                                });
                            }   
                        }
                    }
                    this.formErrorMessage = errors.error_message;

                    this.isSubmitting =  false;
                    this.hasSentForm = false;
                    this.isUploading = false;
                });
        },
        onDiscard() {
            // Initialize clear data from store
            this.clearItemSubmission();
            
            eventBusItemMetadata.clearAllErrors();
            this.formErrorMessage = '';
            this.form.collection_id = this.collectionId;

            // CREATING NEW ITEM SUBMISSION
            this.createNewItem();
        },
        createNewItem() {
            // Puts loading on Draft Item creation
            this.isLoading = true;

            // Clear errors so we don't have them duplicated from api
            eventBusItemMetadata.errors = [];

            let data = JSON.parse(JSON.stringify(this.form));

            this.fillExtraFormData(data);
            this.setItemSubmission(data);

            // Loads metadata
            this.loadMetadata();
        },
        loadMetadata() {
            // Obtains Item Metadatum
            this.fetchMetadata({ collectionId: this.collectionId }).then(resp => {
                resp.request.then((metadata) => {
                    this.metadataCollapses = [];

                    for (let i = 0; i < metadata.length; i++) {
                        this.metadataCollapses.push(false);
                        this.metadataCollapses[i] = true;
                    }                
                    this.setItemSubmissionMetadata( metadata.map((metadatum) => { return { metadatum_id: metadatum.id, value: null } }) );
                    this.isLoading = false;
                });
            });
        },
        toggleCollapseAll() {
            this.collapseAll = !this.collapseAll;

            for (let i = 0; i < this.metadataCollapses.length; i++)
                this.metadataCollapses[i] = this.collapseAll;

        },
        onChangeCollapse(event, index) {
            this.metadataCollapses.splice(index, 1, event);
        }
    }
}
</script>

<style lang="scss" scoped>

.tainacan-item-submission-form {

    .tainacan-form {
        background-color: var(--tainacan-background-color);
        padding-left: var(--tainacan-one-column);
        padding-right: var(--tainacan-one-column);
        padding-top: 24px;
        padding-bottom: 10px;

        @media screen and (max-width: 769px) {
            max-width: 100%;
        }
        .field:not(:last-child) {
            margin-bottom: 0.5em;
        }
        .field {
            padding: 10px 0px 14px 34px;

        }
         .columns {
            flex-wrap: wrap;
            justify-content: space-between;

            .column {
                padding: 1em 12px 0 12px;
            }
        }
    }

    .collapse-all {
        font-size: 0.75em;
        .icon {
            vertical-align: bottom;
        }
    }


    .section-label {
        position: relative;
        margin-top: 14px;
        label {
            font-size: 1em !important;
            font-weight: 500 !important;
            color: var(--tainacan-label-color) !important;
            line-height: 1.2em;
        }
    }

    .section-toggle p {
        font-size: 0.875em;
        margin-bottom: 0;
        padding-left: calc(0.75em - 1px);
    }

    .section-box {
        padding: 0 calc(0.75em - 1px);
        margin-top: 10px;
        margin-bottom: 14px;

        ul {
            padding: 0;
            display: flex;
            flex-wrap: wrap;
            
            li {
                margin-left: 0.25em;
                margin-right: 1.5em;
                text-align: center;
                button {
                    border-radius: 50px;
                    height: 72px;
                    width: 72px;
                    border: none;
                    background-color: var(--tainacan-gray2);
                    color: var(--tainacan-secondary);
                    margin-bottom: 6px;
                    &:hover {
                        background-color: var(--tainacan-primary);
                        cursor: pointer;
                    }
                }
                p { 
                    color: var(--tainacan-secondary); 
                    font-size: 0.8125em;
                }
            }
        }
    }

    .document-field {
        ul {
            list-style: none;
        }
        .document-buttons-row {
            text-align: right;
            top: -21px;
            position: relative;
        }
    }

    .files-list {
        display: flex;
        flex-wrap: wrap;
    }

    .form-submission-footer {
        padding: 18px 0;
        width: 100%;
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 1em;

        @keyframes blink {
            from { color: var(--tainacan-blue5); }
            to { color: var(--tainacan-info-color); }
        }

        .footer-message {
            display: flex;
            align-items: center;
            margin: 12px;
            font-size: 0.875em;
        }
        .update-info-section {
            color: var(--tainacan-info-color);
            margin-right: auto;
        }

        .help {
            display: inline-block;
            font-size: 1.0em;
            margin-top: 0;
            margin-left: 24px;
        }
    }

}
</style>
