<template>
    <div v-if="collectionId">
        <b-loading
                :is-full-page="false"
                :active.sync="isLoading"
                :can-cancel="false"/>
        <form
                v-if="!hasSentForm"
                v-show="!isLoading"
                class="tainacan-form"
                label-width="120px">
            <div class="columns">
                <div class="column is-5">
            
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
                        <div class="section-label">
                            <label>{{ form.document != undefined && form.document != null && form.document != '' ? $i18n.get('label_document') : $i18n.get('label_document_empty') }}</label>
                            <help-button
                                    :title="$i18n.getHelperTitle('items', 'document')"
                                    :message="$i18n.getHelperMessage('items', 'document')"/>
                        </div>
                        <div class="section-box document-field">
                            <div
                                    v-if="form.document != undefined && form.document != null &&
                                            form.document_type != undefined && form.document_type != null &&
                                            form.document != '' && form.document_type != 'empty'">
                                <div v-if="form.document_type == 'attachment'">
                                    <!-- <div v-html="item.document_as_html" /> -->
                                    <document-item :document-html="item.document_as_html"/>

                                    <div class="document-buttons-row">
                                        <a
                                                class="button is-rounded is-secondary"
                                                size="is-small"
                                                id="button-edit-document"
                                                :aria-label="$i18n.get('label_button_edit_document')"
                                                @click.prevent="setFileDocument($event)">
                                            <span
                                                    v-tooltip="{
                                                        content: $i18n.get('edit'),
                                                        autoHide: true,
                                                        placement: 'bottom'
                                                    }"
                                                    class="icon">
                                                <i class="tainacan-icon tainacan-icon-edit"/>
                                            </span>
                                        </a>
                                        <a
                                                class="button is-rounded is-secondary"
                                                size="is-small"
                                                id="button-delete-document"
                                                :aria-label="$i18n.get('label_button_delete_document')"
                                                @click.prevent="removeDocument()">
                                            <span
                                                    v-tooltip="{
                                                        content: $i18n.get('delete'),
                                                        autoHide: true,
                                                        placement: 'bottom'
                                                    }"
                                                    class="icon">
                                                <i class="tainacan-icon tainacan-icon-delete"/>
                                            </span>
                                        </a>
                                    </div>
                                </div>
                                <div v-if="form.document_type == 'text'">
                                    <div v-html="itemSubmission.document_as_html" />
                                    <div class="document-buttons-row">
                                        <a
                                                class="button is-rounded is-secondary"
                                                :aria-label="$i18n.get('label_button_edit_document')"
                                                id="button-edit-document"
                                                @click.prevent="setTextDocument()">
                                            <span
                                                    v-tooltip="{
                                                        content: $i18n.get('edit'),
                                                        autoHide: true,
                                                        placement: 'bottom'
                                                    }"
                                                    class="icon">
                                                <i class="tainacan-icon tainacan-icon-edit"/>
                                            </span>
                                        </a>
                                        <a
                                                class="button is-rounded is-secondary"
                                                size="is-small"
                                                :aria-label="$i18n.get('label_button_delete_document')"
                                                id="button-delete-document"
                                                @click.prevent="removeDocument()">
                                            <span
                                                    v-tooltip="{
                                                        content: $i18n.get('delete'),
                                                        autoHide: true,
                                                        placement: 'bottom'
                                                    }"
                                                    class="icon">
                                                <i class="tainacan-icon tainacan-icon-delete"/>
                                            </span>
                                        </a>
                                    </div>
                                </div>
                                <div v-if="form.document_type == 'url'">
                                    <div v-html="itemSubmission.document_as_html" />
                                    <div class="document-buttons-row">
                                        <a
                                                class="button is-rounded is-secondary"
                                                size="is-small"
                                                :aria-label="$i18n.get('label_button_edit_document')"
                                                id="button-edit-document"
                                                @click.prevent="setURLDocument()">
                                            <span
                                                    v-tooltip="{
                                                        content: $i18n.get('edit'),
                                                        autoHide: true,
                                                        placement: 'bottom'
                                                    }"
                                                    class="icon">
                                                <i class="tainacan-icon tainacan-icon-edit"/>
                                            </span>
                                        </a>
                                        <a
                                                class="button is-rounded is-secondary"
                                                size="is-small"
                                                :aria-label="$i18n.get('label_button_delete_document')"
                                                id="button-delete-document"
                                                @click.prevent="removeDocument()">
                                            <span
                                                    v-tooltip="{
                                                        content: $i18n.get('delete'),
                                                        autoHide: true,
                                                        placement: 'bottom'
                                                    }"
                                                    class="icon">
                                                <i class="tainacan-icon tainacan-icon-delete"/>
                                            </span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <ul v-else>
                                <li v-if="!hideFileModalButton">
                                    <button
                                            type="button"
                                            @click.prevent="setFileDocument($event)">
                                        <span class="icon">
                                            <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-upload"/>
                                        </span>
                                    </button>
                                    <p>{{ $i18n.get('label_file') }}</p>
                                </li>
                                <li v-if="!hideTextModalButton">
                                    <button
                                            type="button"
                                            @click.prevent="setTextDocument()">
                                        <span class="icon">
                                            <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-text"/>
                                        </span>
                                    </button>
                                    <p>{{ $i18n.get('label_text') }}</p>
                                </li>
                                <li v-if="!hideLinkModalButton">
                                    <button
                                            type="button"
                                            @click.prevent="setURLDocument()">
                                        <span class="icon">
                                            <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-url"/>
                                        </span>
                                    </button>
                                    <p>{{ $i18n.get('label_url') }}</p>
                                </li>
                            </ul>
                        </div>

                        <!-- Text Insert Modal ----------------- -->
                        <b-modal
                                :can-cancel="false"
                                :active.sync="isTextModalActive"
                                :width="640"
                                scroll="keep"
                                trap-focus
                                aria-modal
                                aria-role="dialog">
                            <div class="tainacan-modal-content">
                                <div class="tainacan-modal-title">
                                    <h2>{{ $i18n.get('instruction_write_text') }}</h2>
                                    <hr>
                                </div>
                                <b-input
                                        type="textarea"
                                        v-model="textContent"/>

                                <div class="field is-grouped form-submit">
                                    <div class="control">
                                        <button
                                                id="button-cancel-text-content-writing"
                                                class="button is-outlined"
                                                type="button"
                                                @click="cancelTextWriting()">
                                            {{ $i18n.get('cancel') }}</button>
                                    </div>
                                    <div class="control">
                                        <button
                                                id="button-submit-text-content-writing"
                                                type="submit"
                                                @click.prevent="confirmTextWriting()"
                                                class="button is-success">
                                            {{ $i18n.get('save') }}</button>
                                    </div>
                                </div>
                            </div>
                        </b-modal>

                        <!-- URL Insert Modal ----------------- -->
                        <b-modal
                                :can-cancel="false"
                                :active.sync="isURLModalActive"
                                :width="640"
                                scroll="keep"
                                trap-focus
                                role="dialog"
                                tabindex="-1"
                                aria-modal
                                aria-role="dialog">
                            <div class="tainacan-modal-content">
                                <div class="tainacan-modal-title">
                                    <h2>{{ $i18n.get('instruction_insert_url') }}</h2>
                                    <hr>
                                </div>
                                <b-input v-model="urlLink"/>

                                <div class="field is-grouped form-submit">
                                    <div class="control">
                                        <button
                                                id="button-cancel-url-link-selection"
                                                class="button is-outlined"
                                                type="button"
                                                @click="cancelURLSelection()">
                                            {{ $i18n.get('cancel') }}</button>
                                    </div>
                                    <div class="control">
                                        <button
                                                id="button-submit-url-link-selection"
                                                @click.prevent="confirmURLSelection()"
                                                class="button is-success">
                                            {{ $i18n.get('save') }}</button>
                                    </div>
                                </div>
                            </div>
                        </b-modal>
                    
                    </template>

                    <!-- Thumbnail -------------------------------- -->
                    <template v-if="!hideThumbnailSection">
                        <div class="section-label">
                            <label>{{ $i18n.get('label_thumbnail') }}</label>
                            <help-button
                                    :title="$i18n.getHelperTitle('items', '_thumbnail_id')"
                                    :message="$i18n.getHelperMessage('items', '_thumbnail_id')"/>

                        </div>
                        <div 
                                v-if="!isLoading"
                                class="section-box section-thumbnail">
                            <div class="thumbnail-field">
                                <file-item
                                        v-if="itemSubmission.thumbnail != undefined && ((itemSubmission.thumbnail['tainacan-medium'] != undefined && itemSubmission.thumbnail['tainacan-medium'] != false) || (itemSubmission.thumbnail.medium != undefined && itemSubmission.thumbnail.medium != false))"
                                        :show-name="false"
                                        :modal-on-click="false"
                                        :size="178"
                                        :file="{
                                            media_type: 'image',
                                            thumbnails: { 'tainacan-medium': [ itemSubmission.thumbnail['tainacan-medium'] ? itemSubmission.thumbnail['tainacan-medium'][0] : itemSubmission.thumbnail.medium[0] ] },
                                            title: $i18n.get('label_thumbnail'),
                                            description: `<img alt='` + $i18n.get('label_thumbnail') + `' src='` + itemSubmission.thumbnail.full[0] + `'/>` 
                                        }"/>
                                <figure
                                        v-if="itemSubmission.thumbnail == undefined || ((itemSubmission.thumbnail.medium == undefined || itemSubmission.thumbnail.medium == false) && (itemSubmission.thumbnail['tainacan-medium'] == undefined || itemSubmission.thumbnail['tainacan-medium'] == false))"
                                        class="image">
                                    <span class="image-placeholder">{{ $i18n.get('label_empty_thumbnail') }}</span>
                                    <img
                                            :alt="$i18n.get('label_thumbnail')"
                                            :src="thumbPlaceholderPath">
                                </figure>
                                <div class="thumbnail-buttons-row">
                                    <a
                                            class="button is-rounded is-secondary"
                                            id="button-edit-thumbnail"
                                            :aria-label="$i18n.get('label_button_edit_thumb')"
                                            @click.prevent="thumbnailMediaFrame.openFrame($event)">
                                        <span
                                                v-tooltip="{
                                                    content: $i18n.get('edit'),
                                                    autoHide: true,
                                                    placement: 'bottom'
                                                }"
                                                class="icon">
                                            <i class="tainacan-icon tainacan-icon-edit"/>
                                        </span>
                                    </a>
                                    <a
                                            v-if="itemSubmission.thumbnail && itemSubmission.thumbnail.thumbnail != undefined && itemSubmission.thumbnail.thumbnail != false"
                                            id="button-delete-thumbnail"
                                            class="button is-rounded is-secondary"
                                            :aria-label="$i18n.get('label_button_delete_thumb')"
                                            @click="deleteThumbnail()">
                                    <span
                                            v-tooltip="{
                                                content: $i18n.get('delete'),
                                                autoHide: true,
                                                placement: 'bottom'
                                            }"
                                            class="icon">
                                        <i class="tainacan-icon tainacan-icon-delete"/>
                                    </span>
                                    </a>
                                </div>
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
                        <template slot="header">
                            <span class="icon has-text-gray4">
                                <i class="tainacan-icon tainacan-icon-18px tainacan-icon-attachments"/>
                            </span>
                            <span>
                                {{ $i18n.get('label_attachments') }}
                                <span
                                        v-if="totalAttachments != null && totalAttachments != undefined"
                                        class="has-text-gray">
                                    ({{ totalAttachments }})
                                </span>
                            </span>
                        </template>

                        <div v-if="itemSubmission != undefined">
                            <br>
                            <button
                                    style="margin-left: calc(var(--tainacan-one-column) + 12px)"
                                    type="button"
                                    class="button is-secondary"
                                    @click.prevent="attachmentMediaFrame.openFrame($event)"
                                    :disabled="isLoadingAttachments">
                                {{ $i18n.get("label_edit_attachments") }}
                            </button>
                            <!-- <attachments-list
                                    v-if="itemSubmission != undefined"
                                    :item="itemSubmission"
                                    :is-editable="true"
                                    :is-loading.sync="isLoadingAttachments"
                                    @isLoadingAttachments="(isLoading) => isLoadingAttachments = isLoading"
                                    @onDeleteAttachment="deleteAttachment($event)"/> -->
                        </div>
                    </template>
                </div>
                <div class="column is-7">

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

                    <div class="columns">

                        <!-- Comment Status ------------------------ -->
                        <div class="column is-narrow">
                            <div class="section-label">
                                <label>{{ $i18n.get('label_comments') }}</label>
                                <help-button
                                            :title="$i18n.getHelperTitle('items', 'comment_status')"
                                            :message="$i18n.getHelperMessage('items', 'comment_status')"/>
                            </div>
                            <div class="section-status">
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
                        </div>

                    </div>

                    <!-- Metadata from Collection-------------------------------- -->
                    <template slot="header">
                        <span class="icon has-text-gray4">
                            <i class="tainacan-icon tainacan-icon-18px tainacan-icon-metadata"/>
                        </span>
                        <span>{{ $i18n.get('metadata') }}</span>
                    </template>

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
                </div>
            </div>

            <footer class="footer">

                <!-- Updated and Error Info -->
                <div class="update-info-section">
                    <p class="footer-message">

                        <span class="help is-danger">
                            {{ formErrorMessage }}
                            <item-metadatum-errors-tooltip 
                                    v-if="formErrors.length"
                                    :form-errors="formErrors" />
                        </span>
                    </p>
                </div>
                <div class="form-submission-footer">
                    <button
                            @click="onDiscard()"
                            type="button"
                            class="button is-outlined">{{ $i18n.get('cancel') }}</button>
                    <button
                            @click="onSubmit()"
                            type="button"
                            class="button is-secondary">{{ $i18n.get('label_submit') }}</button>
                </div>
            </footer>
        </form>

        <section 
                v-else
                class="section">
            <div class="content has-text-grey has-text-centered">
                <br>
                <p>
                    <span class="icon is-medium">
                        <i class="tainacan-icon tainacan-icon-30px tainacan-icon-approvedcircle"/>
                    </span>
                </p>
                <h2>{{ sentFormHeading ? sentFormHeading : $i18n.get('label_sent_form') }}</h2>
                <p>{{ sentFormMessage? sentFormMessage: $i18n.get('info_sent_form') }}</p>
                <br>
            </div>
        </section>
    </div>
</template>

<script>
import { mapActions, mapGetters } from 'vuex';
import { eventBusItemMetadata } from '../../admin/js/event-bus-item-metadata';
import wpMediaFrames from '../../admin/js/wp-media-frames';
import FileItem from '../../admin/components/other/file-item.vue';
import DocumentItem from '../../admin/components/other/document-item.vue';
import CustomDialog from '../../admin/components/other/custom-dialog.vue';
//import AttachmentsList from '../../admin/components/lists/attachments-list.vue';
import { formHooks } from '../../admin/js/mixins';

export default {
    name: 'ItemSubmissionForm',
    components: {
        FileItem,
        DocumentItem,
        //AttachmentsList
    },
    mixins: [ formHooks ],
    props: {
        collectionId: String,
        hideFileModalButton: Boolean,
        hideTextModalButton: Boolean,
        hideLinkModalButton: Boolean,
        hideThumbnailSection: Boolean,
        hideAttachmentsSection: Boolean,
        hideCollapses: Boolean,
        enabledMetadata: Array,
        sentFormHeading: String,
        sentFormMessage: String
    },
    data(){
        return {
            isLoading: false,
            metadataCollapses: [],
            collapseAll: true,
            form: {
                collectionId: Number,
                document: '',
                document_type: '',
                comment_status: ''
            },
            thumbnail: {},
            formErrorMessage: '',
            thumbPlaceholderPath: tainacan_plugin.base_url + '/assets/images/placeholder_square.png',
            thumbnailMediaFrame: undefined,
            attachmentMediaFrame: undefined,
            fileMediaFrame: undefined,
            isURLModalActive: false,
            urlLink: '',
            isTextModalActive: false,
            textLink: '',
            isLoadingAttachments: false,
            hasSentForm: false
        }
    },
    computed: {
        itemSubmission() {
            return this.getItemSubmission();
        },
        collection() {
            return this.getCollection()
        },
        metadatumList() {
            return this.itemSubmission && this.itemSubmission.metadata ? JSON.parse(JSON.stringify(this.getMetadata().map((metadatum) => { return { metadatum: metadatum, item: {}, value: this.itemSubmission.metadata.find((aMetadatum) => aMetadatum.metadatum_id == metadatum.id).value } } ))) : [];
        },
        totalAttachments() {
            return this.getTotalAttachments();
        },
        formErrors() {
           return eventBusItemMetadata && eventBusItemMetadata.errors && eventBusItemMetadata.errors.length ? eventBusItemMetadata.errors : []
        }
    },
    created() {

        // Initialize clear data from store
        this.clearItemSubmission();
        
        eventBusItemMetadata.clearAllErrors();
        this.formErrorMessage = '';
        this.form.collectionId = this.collectionId;

        // CREATING NEW ITEM SUBMISSION
        this.createNewItem();

        eventBusItemMetadata.$on('hasErrorsOnForm', (hasErrors) => {
            if (hasErrors)
                this.formErrorMessage = this.formErrorMessage ? this.formErrorMessage : this.$i18n.get('info_errors_in_form');
            else
                this.formErrorMessage = '';
        });
    },
    beforeDestroy () {
        eventBusItemMetadata.$off('hasErrorsOnForm');
    },
    methods: {
        ...mapActions('item', [
            'setItemSubmission',
            'updateItemSubmission',
            'submitItemSubmission',
            'updateItem',
            'updateItemDocument',
            'fetchItemMetadata',
            'fetchItem',
            'clearItemSubmission',
            'sendAttachments',
            'fetchAttachments',
            'deletePermanentlyAttachment',
            'updateThumbnail',
            'removeAttachmentFromItem'
        ]),
        ...mapGetters('item',[
            'getItemSubmission',
            'getItemMetadata',
            'getTotalAttachments'
        ]),
        ...mapActions('metadata',[
            'fetchMetadata'
        ]),
        ...mapGetters('metadata',[
            'getMetadata'
        ]),
        ...mapActions('collection', [
            'deleteItem',
        ]),
        ...mapGetters('collection', [
            'getCollection',
        ]),
        onSubmit() {

            // Puts loading on Item edition
            this.isLoading = true;

            this.form.comment_status = this.form.comment_status == 'open' ? 'open' : 'closed';

            let data = { comment_status: this.form.comment_status };
            this.fillExtraFormData(data);
            this.updateExtraFormData(this.itemSubmission);

            // Clear errors so we don't have them duplicated from api
            eventBusItemMetadata.errors = [];

            this.submitItemSubmission(this.itemSubmission)
                .then(() => {
                    this.hasSentForm = true;
                    this.isLoading = false;
                })
                .catch((errors) => { 
                    if (errors.errors) {
                        for (let error of errors.errors) {
                            for (let metadatum of Object.keys(error)){
                                eventBusItemMetadata.errors.push({ 
                                    metadatum_id: metadatum,
                                    errors: error[metadatum]
                                });
                            }   
                        }
                        this.formErrorMessage = errors.error_message;
                    }
                    this.isLoading = false;
                });
        },
        onDiscard() {
            console.log('FORMULÁRIO DESCARTADO');
        },
        createNewItem() {
            // Puts loading on Draft Item creation
            this.isLoading = true;

            // Clear errors so we don't have them duplicated from api
            eventBusItemMetadata.errors = [];

            // Creates draft Item
            this.form.comment_status = this.form.comment_status == 'open' ? 'open' : 'closed';
            let data = { collection_id: this.form.collectionId, comment_status: this.form.comment_status };

            this.fillExtraFormData(data);
            this.setItemSubmission(data);

            // Initializes Media Frames now that itemId exists
            //this.initializeMediaFrames();

            // Pre-fill values incentivate it
            this.form.document = this.itemSubmission.document;
            this.form.document_type = this.itemSubmission.document_type;
            this.form.comment_status = this.itemSubmission.comment_status;

            // Loads metadata and attachments
            this.loadMetadata();
            // this.isLoadingAttachments = true;
            // this.fetchAttachments({ page: 1, attachmentsPerPage: 24, itemId: this.itemId })
            //     .then(() => this.isLoadingAttachments = false)
            //     .catch(() => this.isLoadingAttachments = false);

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
                    this.updateItemSubmission({ key: 'metadata', value: metadata.map((metadatum) => { return { metadatum_id: metadatum.id, value: null } }) });
                    this.isLoading = false;
                });
            });
        },
        setFileDocument(event) {
            this.fileMediaFrame.openFrame(event);
        },
        setTextDocument() {
            this.isTextModalActive = true;
        },
        confirmTextWriting() {
            //this.isLoading = true;
            this.isTextModalActive = false;
            this.form.document_type = 'text';
            this.form.document = this.textContent;
            // this.updateItemDocument({
            //     document: this.form.document,
            //     document_type: this.form.document_type 
            // })
            // .then(item => {
            //     this.item.document_as_html = item.document_as_html;
            //     this.isLoading = false;
            // })
            // .catch((errors) => {
            //     for (let error of errors.errors) {
            //         for (let metadatum of Object.keys(error)){
            //             eventBusItemMetadata.errors.push({ 
            //                 metadatum_id: metadatum, 
            //                 errors: error[metadatum]
            //             });
            //         }
            //     }
            //     this.formErrorMessage = errors.error_message;

            //     this.isLoading = false;
            // });
        },
        cancelTextWriting() {
            this.isTextModalActive = false;
            this.textContent = '';
        },
        setURLDocument() {
            this.isURLModalActive = true;
        },
        confirmURLSelection() {
            //this.isLoading = true;
            this.isURLModalActive = false;
            this.form.document_type = 'url';
            this.form.document = this.urlLink;
            // this.updateItemDocument({ 
            //     document: this.form.document,
            //     document_type: this.form.document_type
            // })
            // .then(item => {
            //     this.item.document_as_html = item.document_as_html;
            //     this.isLoading = false;

            //     let oldThumbnail = this.item.thumbnail;
            //     if (item.document_type == 'url' && oldThumbnail != item.thumbnail )
            //         this.item.thumbnail = item.thumbnail;
            // })
            // .catch((errors) => {
            //     for (let error of errors.errors) {
            //         for (let metadatum of Object.keys(error)) {
            //             eventBusItemMetadata.errors.push({ 
            //                 metadatum_id: metadatum, 
            //                 errors: error[metadatum]
            //             });
            //         }
            //     }
            //     this.formErrorMessage = errors.error_message;

            //     this.isLoading = false;
            // });
        },
        cancelURLSelection() {
            this.isURLModalActive = false;
            this.urlLink = '';
        },
        removeDocument() {
            this.textContent = '';
            this.urlLink = '';
            this.form.document_type = 'empty';
            this.form.document = '';
            this.updateItemDocument({
                document: this.form.document,
                document_type: this.form.document_type
            })
            .then(() => {
                // this.isLoadingAttachments = true;
                // this.fetchAttachments({ page: 1, attachmentsPerPage: 24, itemId: this.itemId, documentId: this.item.document })
                //     .then(() => this.isLoadingAttachments = false)
                //     .catch(() => this.isLoadingAttachments = false);
            })
            .catch((errors) => {
                for (let error of errors.errors) {
                    for (let metadatum of Object.keys(error)){
                        eventBusItemMetadata.errors.push({
                            metadatum_id: metadatum,
                            errors: error[metadatum]
                        });
                    }
                }
                this.formErrorMessage = errors.error_message;
            });
        },
        deleteThumbnail() {
            this.updateThumbnail({ thumbnailId: 0})
                .catch((error) => {
                    this.$console.error(error);
                });
        },
        deleteAttachment(attachment) {
            this.$buefy.modal.open({
                parent: this,
                component: CustomDialog,
                props: {
                    icon: 'alert',
                    title: this.$i18n.get('label_warning'),
                    message: this.$i18n.get('info_warning_attachment_delete'),
                    onConfirm: () => {
                        this.deletePermanentlyAttachment(attachment.id)
                            .then(() => {
                                // this.isLoadingAttachments = true;

                                // this.fetchAttachments({ page: 1, attachmentsPerPage: 24, itemId: this.itemId, documentId: this.item.document })
                                //     .then(() => this.isLoadingAttachments = false)
                                //     .catch(() => this.isLoadingAttachments = false);
                            })
                            .catch((error) => {
                                this.$console.error(error);
                            });
                    }
                },
                trapFocus: true
            });

        },
        initializeMediaFrames() {

            this.fileMediaFrame = new wpMediaFrames.documentFileControl(
                'my-file-media-frame', {
                    button_labels: {
                        frame_title: this.$i18n.get('instruction_select_document_file_for_item'),
                        frame_button: this.$i18n.get('label_select_file'),
                    },
                    onSave: (file) => {
                        //this.isLoading = true;
                        this.form.document_type = 'attachment';
                        this.form.document = file.id + '';
                        // this.updateItemDocument({ item_id: this.itemId, document: this.form.document, document_type: this.form.document_type })
                        //     .then((item) => {
                        //         this.isLoading = false;
                        //         this.item.document_as_html = item.document_as_html;

                        //         let oldThumbnail = this.item.thumbnail;
                        //         if (item.document_type == 'attachment' && oldThumbnail != item.thumbnail )
                        //             this.item.thumbnail = item.thumbnail;

                        //     })
                        //     .catch((errors) => {
                        //         for (let error of errors.errors) {
                        //             for (let metadatum of Object.keys(error)){
                        //                 eventBusItemMetadata.errors.push({ metadatum_id: metadatum, errors: error[metadatum]});
                        //             }
                        //         }
                        //         this.formErrorMessage = errors.error_message;
                        //         this.isLoading = false;
                        //     });
                    }
                }
            );
            this.thumbnailMediaFrame = new wpMediaFrames.thumbnailControl(
                'my-thumbnail-media-frame', {
                    button_labels: {
                        frame_title: this.$i18n.get('instruction_select_item_thumbnail'),
                    },
                    // onSave: (media) => {
                    //     this.updateThumbnail({itemId: this.itemId, thumbnailId: media.id})
                    //         .then((res) => {
                    //             this.item.thumbnail = res.thumbnail;
                    //         })
                    //         .catch(error => this.$console.error(error));
                    // }
                }
            );
            this.attachmentMediaFrame = new wpMediaFrames.attachmentControl(
                'my-attachment-media-frame', {
                    button_labels: {
                        frame_title: this.$i18n.get('instruction_select_files_to_attach_to_item'),
                        frame_button: this.$i18n.get('label_attach_to_item'),
                    },
                    // onSave: () => {
                    //     // Fetch current existing attachments
                    //     this.isLoadingAttachments = true;
                    //     this.fetchAttachments({ page: 1, attachmentsPerPage: 24, itemId: this.itemId, documentId: this.item.document })
                    //         .then(() => this.isLoadingAttachments = false)
                    //         .catch(() => this.isLoadingAttachments = false);
                    // }
                }
            );

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

    .tainacan-form {
        background-color: var(--tainacan-background-color);
    }

    .tainacan-form .field:not(:last-child) {
        margin-bottom: 0.5em;
    }

    .column.is-5 {
        padding-left: var(--tainacan-one-column);
        padding-right: var(--tainacan-one-column);

        @media screen and (max-width: 769px) {
            max-width: 100%;
        }
    }
    .column.is-7 {
        padding-left: 0;
        padding-right: var(--tainacan-one-column);

        .columns {
            flex-wrap: wrap;
            justify-content: space-between;

            .column {
                padding: 1em 12px 0 12px;
            }
        }

        .field {
            padding: 10px 0px 14px 60px;
        }

        @media screen and (max-width: 769px) {
            padding-left: var(--tainacan-one-column);
            max-width: 100%;
        }
    }

    .section-label {
        position: relative;
        label {
            font-size: 1em !important;
            font-weight: 500 !important;
            color: var(--tainacan-label-color) !important;
            line-height: 1.2em;
        }
    }

    .collapse-all {
        font-size: 0.75em;
        .icon {
            vertical-align: bottom;
        }
    }

    .section-box {
        padding: 0 var(--tainacan-one-column) 0 0;
        margin-top: 14px;
        margin-bottom: 32px;

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

    #button-edit-thumbnail,
    #button-edit-document,
    #button-delete-thumbnail,
    #button-delete-document {

        border-radius: 100px !important;
        max-height: 2.125em !important;
        max-width: 2.125em !important;
        min-height: 2.125em !important;
        min-width: 2.125em !important;
        padding: 0 !important;
        z-index: 99;
        margin-left: 12px !important;

        .icon {
            display: inherit;
            padding: 0;
            margin: 0;
            margin-top: -2px;
            font-size: 1.125em;
        }
    }

    .thumbnail-field {

        .image {
            position: relative;
        }
        .content {
            padding: 10px;
            font-size: 0.8em;
        }
        img {
            height: 178px;
            width: 178px;
        }
        .image-placeholder {
            position: absolute;
            margin-left: 45px;
            margin-right: 45px;
            font-size: 0.8em;
            font-weight: bold;
            z-index: 99;
            text-align: center;
            color: var(--tainacan-info-color);
            top: 70px;
            max-width: 90px;
            background: transparent;
        }

        .thumbnail-buttons-row {
            position: relative;
            left: 90px;
            bottom: 1.0em;
        }
    }

    .footer {
        padding: 18px var(--tainacan-one-column);
        width: 100%;
        height: 65px;
        display: flex;
        justify-content: flex-end;
        align-items: center;

        .form-submission-footer {
            .button {
                margin-left: 16px;
                margin-right: 6px;
            }
        }

        @keyframes blink {
            from { color: var(--tainacan-blue5); }
            to { color: var(--tainacan-info-color); }
        }

        .footer-message {
            display: flex;
            align-items: center;
        }
        .update-warning {
            color: var(--tainacan-blue5);
            animation-name: blink;
            animation-duration: 0.5s;
            animation-delay: 0.5s;
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

</style>
