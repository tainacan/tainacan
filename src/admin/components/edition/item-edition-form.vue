<template>
    <div>
        <b-loading
                :active.sync="isLoading"
                :can-cancel="false"/>
        <button 
                id="metadata-column-compress-button"
                @click="isMetadataColumnCompressed = !isMetadataColumnCompressed">
            <b-icon :icon="isMetadataColumnCompressed ? 'menu-left' : 'menu-right'" />
        </button>
        <tainacan-title />
        <form
                v-if="!isLoading"
                class="tainacan-form"
                label-width="120px">
            <div class="columns">
                <div class="column is-5-5">

                    <!-- Document -------------------------------- -->
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
                                        <b-icon 
                                                size="is-small"
                                                icon="pencil" />
                                    </a>
                                    <a
                                            class="button is-rounded is-secondary"
                                            size="is-small"
                                            id="button-delete-document"
                                            :aria-label="$i18n.get('label_button_delete_document')"
                                            @click.prevent="removeDocument()">
                                        <b-icon 
                                                size="is-small"
                                                icon="delete" />
                                    </a>
                                </div>
                            </div>
                            <div v-if="form.document_type == 'text'">
                                <div v-html="item.document_as_html" />
                                <div class="document-buttons-row">
                                    <a
                                            class="button is-rounded is-secondary"
                                            :aria-label="$i18n.get('label_button_edit_document')"
                                            id="button-edit-document"
                                            @click.prevent="setTextDocument()">
                                        <b-icon 
                                                size="is-small"
                                                icon="pencil" />
                                    </a>
                                    <a
                                            class="button is-rounded is-secondary"
                                            size="is-small"
                                            :aria-label="$i18n.get('label_button_delete_document')"
                                            id="button-delete-document"
                                            @click.prevent="removeDocument()">
                                        <b-icon 
                                                size="is-small"
                                                icon="delete" />
                                    </a>
                                </div>
                            </div>
                            <div v-if="form.document_type == 'url'">
                                <div v-html="item.document_as_html" />
                                <div class="document-buttons-row">
                                    <a
                                            class="button is-rounded is-secondary"
                                            size="is-small"
                                            :aria-label="$i18n.get('label_button_edit_document')"
                                            id="button-edit-document"
                                            @click.prevent="setURLDocument()">
                                        <b-icon 
                                                size="is-small"
                                                icon="pencil" />
                                    </a>
                                    <a
                                            class="button is-rounded is-secondary"
                                            size="is-small"
                                            :aria-label="$i18n.get('label_button_delete_document')"
                                            id="button-delete-document"
                                            @click.prevent="removeDocument()">
                                        <b-icon 
                                                size="is-small"
                                                icon="delete" />
                                    </a>
                                </div>
                            </div>
                        </div>
                        <ul v-else>
                            <li>
                                <button 
                                        type="button"
                                        @click.prevent="setFileDocument($event)">
                                    <b-icon icon="upload"/>
                                </button>
                                <p>{{ $i18n.get('label_file') }}</p>
                            </li>
                            <li>
                                <button 
                                        type="button"
                                        @click.prevent="setTextDocument()">
                                    <b-icon icon="format-text"/>
                                </button>
                                <p>{{ $i18n.get('label_text') }}</p>
                            </li>
                            <li>
                                <button 
                                        type="button"
                                        @click.prevent="setURLDocument()">
                                    <b-icon icon="code-tags"/>
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
                            scroll="keep">
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
                            scroll="keep">
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

                    <!-- Thumbnail -------------------------------- -->
                    <div class="section-label">
                        <label>{{ $i18n.get('label_thumbnail') }}</label>
                        <help-button
                                :title="$i18n.getHelperTitle('items', '_thumbnail_id')"
                                :message="$i18n.getHelperMessage('items', '_thumbnail_id')"/>

                    </div>                    
                    <div class="section-box section-thumbnail">
                        <div class="thumbnail-field">
                            <file-item
                                    v-if="item.thumbnail != undefined && ((item.thumbnail.tainacan_medium != undefined && item.thumbnail.tainacan_medium != false) || (item.thumbnail.medium != undefined && item.thumbnail.medium != false))"
                                    :show-name="false"
                                    :size="178"
                                    :file="{ 
                                        media_type: 'image', 
                                        guid: { rendered: item.thumbnail.tainacan_medium ? item.thumbnail.tainacan_medium : item.thumbnail.medium },
                                        title: { rendered: $i18n.get('label_thumbnail')},
                                        description: { rendered: `<img alt='Thumbnail' src='` + item.thumbnail.full + `'/>` }}"/>
                            <figure 
                                    v-if="item.thumbnail == undefined || ((item.thumbnail.medium == undefined || item.thumbnail.medium == false) && (item.thumbnail.tainacan_medium == undefined || item.thumbnail.tainacan_medium == false))"
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
                                    <b-icon 
                                            size="is-small"
                                            icon="pencil" />
                                </a>
                                <a
                                        v-if="item.thumbnail.thumb != undefined && item.thumbnail.thumb != false"
                                        id="button-delete-thumbnail"
                                        class="button is-rounded is-secondary"
                                        :aria-label="$i18n.get('label_button_delete_thumb')"
                                        @click="deleteThumbnail()">
                                   <b-icon 
                                        size="is-small"
                                        icon="delete" />
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Comment Status ------------------------ --> 
                    <b-field
                            :addons="false" 
                            :label="$i18n.get('label_comment_status')"
                            v-if="collectionCommentStatus == 'open'">
                        <b-switch
                                id="tainacan-checkbox-comment-status" 
                                size="is-small"
                                true-value="open" 
                                false-value="closed"
                                v-model="form.comment_status" />
                        <help-button 
                                :title="$i18n.getHelperTitle('items', 'comment_status')" 
                                :message="$i18n.getHelperMessage('items', 'comment_status')"/>
                    </b-field>

                    <!-- Attachments ------------------------------------------ -->
                    <div class="section-label">
                        <label>{{ $i18n.get('label_attachments') }}</label>
                    </div>
                    <div class="section-box section-attachments">
                        <button
                                type="button"
                                class="button is-secondary"
                                @click.prevent="attachmentMediaFrame.openFrame($event)">
                            {{ $i18n.get("label_edit_attachments") }}
                        </button>

                        <div class="uploaded-files">
                            <file-item
                                    :style="{ margin: 15 + 'px'}"
                                    v-if="attachmentsList.length > 0" 
                                    v-for="(attachment, index) in attachmentsList"
                                    :key="index"
                                    :show-name="true"
                                    :file="attachment"/>
                            <p v-if="attachmentsList.length <= 0"><br>{{ $i18n.get('info_no_attachments_on_item_yet') }}</p>
                        </div>
                    </div>

                </div>
                <div 
                        class="column is-4-5"
                        v-show="!isMetadataColumnCompressed">

                    
                    <!-- Visibility (status public or private) -------------------------------- -->
                    <div class="section-label">
                        <label>{{ $i18n.get('label_visibility') }}</label>
                        <span class="required-metadatum-asterisk">*</span>
                        <help-button
                                :title="$i18n.get('label_visibility')"
                                :message="$i18n.get('info_visibility_helper')"/>
                    </div>
                    <div class="section-status">
                        <div class="field has-addons">
                            <b-radio
                                    v-model="visibility"
                                    value="publish"
                                    native-value="publish">
                                <span class="icon">
                                    <i class="mdi mdi-earth"/>
                                </span> {{ $i18n.get('publish_visibility') }}
                            </b-radio>
                            <b-radio
                                    v-model="visibility"
                                    value="private"
                                    native-value="private">
                                <span class="icon">
                                    <i class="mdi mdi-lock"/>
                                </span>  {{ $i18n.get('private_visibility') }}
                            </b-radio>
                        </div>
                    </div>

                    <!-- Collection -------------------------------- -->
                    <div class="section-label">
                        <label>{{ $i18n.get('collection') }}</label>
                    </div>
                    <div class="section-collection">
                        <div class="field has-addons">
                            <p>
                                {{ collectionName }}
                            </p>
                        </div>
                    </div>

                    <!-- Metadata from Collection-------------------------------- -->
                    <span class="section-label">
                        <label >{{ $i18n.get('metadata') }}</label>
                    </span>
                    <br>
                    <a
                            class="collapse-all"
                            @click="toggleCollapseAll()">
                        {{ collapseAll ? $i18n.get('label_collapse_all') : $i18n.get('label_expand_all') }}
                        <b-icon
                                type="is-turoquoise5"
                                :icon=" collapseAll ? 'menu-down' : 'menu-right'" />
                    </a>
                    <tainacan-form-item
                            v-for="(metadatum, index) of metadatumList"
                            :key="index"
                            :metadatum="metadatum"
                            :is-collapsed="metadatumCollapses[index]"
                            @changeCollapse="onChangeCollapse($event, index)"/>

                </div>
            </div>
            <div class="footer">
                <!-- Last Updated Info --> 
                <div class="update-info-section">     
                    <p v-if="!isUpdatingValues">
                        {{ ($i18n.get('info_updated_at') + ' ' + lastUpdated) }}
                        <span class="help is-danger">{{ formErrorMessage }}</span>
                    </p>     
                    <p 
                            class="update-warning"
                            v-if="isUpdatingValues">
                        <b-icon icon="autorenew" />{{ $i18n.get('info_updating_metadata_values') }}
                        <span class="help is-danger">{{ formErrorMessage }}</span>
                    </p>
                    
                </div>  
                <div 
                        class="form-submission-footer"
                        v-if="form.status == 'trash'">
                    <button 
                            @click="onDeletePermanently()"
                            type="button"
                            class="button is-outlined">{{ $i18n.get('label_delete_permanently') }}</button>
                    <button 
                            @click="onSubmit('draft')"
                            type="button"
                            class="button is-secondary">{{ $i18n.get('label_save_as_draft') }}</button>
                    <button 
                            @click="onSubmit(visibility)"
                            type="button"
                            class="button is-success">{{ $i18n.get('label_publish') }}</button>
                </div>
                <div 
                        class="form-submission-footer"
                        v-if="form.status == 'auto-draft' || form.status == 'draft' || form.status == undefined">
                    <button 
                            v-if="form.status == 'draft'"
                            @click="onSubmit('trash')"
                            type="button"
                            class="button is-outlined">{{ $i18n.get('label_send_to_trash') }}</button>
                    <button 
                            v-if="form.status == 'auto-draft'"
                            @click="onDiscard()"
                            type="button"
                            class="button is-outlined">{{ $i18n.get('label_discard') }}</button>
                    <button 
                            @click="onSubmit('draft')"
                            type="button"
                            class="button is-secondary">{{ form.status == 'draft' ? $i18n.get('label_update') : $i18n.get('label_save_as_draft') }}</button>
                    <button 
                            @click="onSubmit(visibility)"
                            type="button"
                            class="button is-success">{{ $i18n.get('label_publish') }}</button>
                </div>
                <div 
                        class="form-submission-footer"
                        v-if="form.status == 'publish' || form.status == 'private'">
                    <button 
                            @click="onSubmit('trash')"
                            type="button"
                            class="button is-outlined">{{ $i18n.get('label_send_to_trash') }}</button>
                    <button 
                            @click="onSubmit('draft')"
                            type="button"
                            class="button is-secondary">{{ $i18n.get('label_return_to_draft') }}</button>
                    <button 
                            :disabled="formErrorMessage != undefined && formErrorMessage != ''"
                            @click="onSubmit(visibility)"
                            type="button"
                            class="button is-secondary">{{ $i18n.get('label_update') }}</button>
                </div>
            </div>
        </form>
    </div>
</template>

<script>
import { mapActions, mapGetters } from 'vuex';
import { eventBus } from '../../../js/event-bus-web-components.js'
import wpMediaFrames from '../../js/wp-media-frames';
import FileItem from '../other/file-item.vue';
import DocumentItem from '../other/document-item.vue';
import CustomDialog from '../other/custom-dialog.vue';

export default {
    name: 'ItemEditionForm',
    data(){
        return {
            pageTitle: '',
            itemId: Number,
            item: null,
            collectionId: Number,
            isLoading: false,
            isMetadataColumnCompressed: false,
            metadatumCollapses: [],
            collapseAll: false,
            visibility: 'publish',
            form: {
                collectionId: Number,
                status: '',
                document: '',
                document_type: '',
                comment_status: ''
            },
            thumbnail: {},
            // Can be obtained from api later
            statusOptions: [{
                value: 'publish',
                label: this.$i18n.get('publish')
                }, {
                value: 'draft',
                label: this.$i18n.get('draft')
                }, {
                value: 'private',
                label: this.$i18n.get('private')
                }, {
                value: 'trash',
                label: this.$i18n.get('trash')
            }],
            formErrorMessage: '',
            thumbPlaceholderPath: tainacan_plugin.base_url + '/admin/images/placeholder_square.png',
            thumbnailMediaFrame: undefined,
            attachmentMediaFrame: undefined,
            fileMediaFrame: undefined,
            isURLModalActive: false,
            urlLink: '',
            isTextModalActive: false,
            textLink: '',
            isUpdatingValues: false,
            collectionName: '',
            collectionCommentStatus: ''
        }
    },
    computed: {
        metadatumList() {
            return JSON.parse(JSON.stringify(this.getMetadata()));
        },
        attachmentsList(){
            return this.getAttachments();
        },
        lastUpdated() {
            return this.getLastUpdated();
        }
    },
    components: {
        FileItem,
        DocumentItem
    },
    methods: {
        ...mapActions('item', [
            'sendItem',
            'updateItem',
            'updateItemDocument',
            'fetchMetadata',
            'sendMetadatum',
            'fetchItem',
            'cleanMetadata',
            'sendAttachments',
            'updateThumbnail',
            'fetchAttachments',
            'cleanLastUpdated',
            'setLastUpdated'
        ]),
        ...mapGetters('item',[
            'getMetadata',
            'getAttachments',
            'getLastUpdated'
        ]),
        ...mapActions('collection', [
            'fetchCollectionName',
            'fetchCollectionCommentStatus',
            'deleteItem',
        ]),
        onSubmit(status) {
            // Puts loading on Item edition
            this.isLoading = true;

            let previousStatus = this.form.status;
            this.form.status = status;

            let data = {item_id: this.itemId, status: this.form.status, comment_status: this.form.comment_status};

            this.updateItem(data).then(updatedItem => {

                this.item = updatedItem;

                // Fill this.form data with current data.
                this.form.status = this.item.status;
                this.form.document = this.item.document;
                this.form.document_type = this.item.document_type;
                this.form.comment_status = this.item.comment_status;

                this.isLoading = false;

                if (this.form.status != 'trash') 
                    this.$router.push(this.$routerHelper.getItemPath(this.form.collectionId, this.itemId));
                else
                    this.$router.push(this.$routerHelper.getCollectionPath(this.form.collectionId));
            })
            .catch((errors) => {
                for (let error of errors.errors) {
                    for (let metadatum of Object.keys(error)){
                       eventBus.errors.push({ metadatum_id: metadatum, errors: error[metadatum]});
                    }
                    
                }
                this.formErrorMessage = errors.error_message;
                this.form.status = previousStatus;
                this.item.status = previousStatus;

                this.isLoading = false;
            });
        },
        onDiscard() {
            this.$router.go(-1);
        },
        createNewItem() {
            // Puts loading on Draft Item creation
            this.isLoading = true;

            // Creates draft Item
            let data = {collection_id: this.form.collectionId, status: 'auto-draft', comment_status: this.form.comment_status};
            this.sendItem(data).then(res => {

                this.itemId = res.id;
                this.item = res;

                // Initializes Media Frames now that itemId exists
                this.initializeMediaFrames();

                // Pre-fill status with publish to incentivate it
                this.visibility = 'publish';
                this.form.status = 'auto-draft'
                this.form.document = this.item.document;
                this.form.document_type = this.item.document_type;
                this.form.comment_status = this.item.comment_status;

                this.loadMetadata();
                this.fetchAttachments(this.itemId);

            })
            .catch(error => this.$console.error(error));
        },
        loadMetadata() {
            // Obtains Item Metadatum
            this.fetchMetadata(this.itemId).then((metadata) => {
                this.isLoading = false;
                for (let metadatum of metadata) {
                    this.metadatumCollapses.push(metadatum.metadatum.required == 'yes');
                }
            });
        },
        setFileDocument(event) {
            this.fileMediaFrame.openFrame(event);
        },
        setTextDocument() {
            this.isTextModalActive = true;
        },
        confirmTextWriting() {
            this.isLoading = true;
            this.isTextModalActive = false;
            this.form.document_type = 'text';
            this.form.document = this.textContent;
            this.updateItemDocument({ item_id: this.itemId, document: this.form.document, document_type: this.form.document_type })
            .then(item => {
                this.item.document_as_html = item.document_as_html;
                this.isLoading = false;
            })
            .catch((errors) => {
                for (let error of errors.errors) {
                    for (let metadatum of Object.keys(error)){
                       eventBus.errors.push({ metadatum_id: metadatum, errors: error[metadatum]});
                    }
                }
                this.formErrorMessage = errors.error_message;

                this.isLoading = false;
            });
        },
        cancelTextWriting() {
            this.isTextModalActive = false;
            this.textContent = '';
        },
        setURLDocument() {
            this.isURLModalActive = true;
        },
        confirmURLSelection() {
            this.isLoading = true;
            this.isURLModalActive = false;
            this.form.document_type = 'url';
            this.form.document = this.urlLink;
            this.updateItemDocument({ item_id: this.itemId, document: this.form.document, document_type: this.form.document_type })
            .then(item => {
                this.item.document_as_html = item.document_as_html;
                this.isLoading = false;

                let oldThumbnail = this.item.thumbnail;
                if (item.document_type == 'url' && oldThumbnail != item.thumbnail )
                    this.item.thumbnail = item.thumbnail;
            })
            .catch((errors) => {
                for (let error of errors.errors) {
                    for (let metadatum of Object.keys(error)){
                       eventBus.errors.push({ metadatum_id: metadatum, errors: error[metadatum]});
                    }
                }
                this.formErrorMessage = errors.error_message;

                this.isLoading = false;
            });
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
            this.updateItemDocument({ item_id: this.itemId, document: this.form.document, document_type: this.form.document_type });
        },
        deleteThumbnail() {
            this.updateThumbnail({itemId: this.itemId, thumbnailId: 0})
            .then(() => {
                this.item.thumbnail = false;
            })
            .catch((error) => {
                this.$console.error(error);
            });
        },
        initializeMediaFrames() {

            this.fileMediaFrame = new wpMediaFrames.documentFileControl(
                'my-file-media-frame', {
                    button_labels: {
                        frame_title: this.$i18n.get('instruction_select_document_file_for_item'),
                        frame_button: this.$i18n.get('label_select_file'),
                    },
                    relatedPostId: this.itemId,
                    onSave: (file) => {
                        this.isLoading = true;
                        this.form.document_type = 'attachment';
                        this.form.document = file.id + '';
                        this.updateItemDocument({ item_id: this.itemId, document: this.form.document, document_type: this.form.document_type })
                        .then((item) => {
                            this.isLoading = false;
                            this.item.document_as_html = item.document_as_html;

                            let oldThumbnail = this.item.thumbnail;
                            if (item.document_type == 'attachment' && oldThumbnail != item.thumbnail )
                                this.item.thumbnail = item.thumbnail;

                        })
                        .catch((errors) => {
                            for (let error of errors.errors) {
                                for (let metadatum of Object.keys(error)){
                                eventBus.errors.push({ metadatum_id: metadatum, errors: error[metadatum]});
                                }
                            }
                            this.formErrorMessage = errors.error_message;
                            this.isLoading = false;
                        });
                    }
                }
            );

            this.thumbnailMediaFrame = new wpMediaFrames.thumbnailControl(
                'my-thumbnail-media-frame', {
                    button_labels: {
                        frame_title: this.$i18n.get('instruction_select_item_thumbnail'),
                    },
                    relatedPostId: this.itemId,
                    onSave: (mediaId) => {
                        this.updateThumbnail({itemId: this.itemId, thumbnailId: mediaId})
                        .then((res) => {
                            this.item.thumbnail = res.thumbnail;
                        })
                        .catch(error => this.$console.error(error));
                    }
                }
            );

            this.attachmentMediaFrame = new wpMediaFrames.attachmentControl(
                'my-attachment-media-frame', {
                    button_labels: {
                        frame_title: this.$i18n.get('instruction_select_files_to_attach_to_item'),
                        frame_button: this.$i18n.get('label_attach_to_item'),
                    },
                    relatedPostId: this.itemId,
                    onSave: () => {
                        // Fetch current existing attachments
                        this.fetchAttachments(this.itemId);
                    }
                }
            );

        },
        toggleCollapseAll() {
            this.collapseAll = !this.collapseAll;

            for (let i = 0; i < this.metadatumCollapses.length; i++)
                this.metadatumCollapses[i] = this.collapseAll;

        },
        onChangeCollapse(event, index) {
            this.metadatumCollapses.splice(index, 1, event);
        },
        onDeletePermanently() {
            this.$modal.open({
                parent: this,
                component: CustomDialog,
                props: {
                    icon: 'alert',
                    title: this.$i18n.get('label_warning'),
                    message: this.isOnTrash ? this.$i18n.get('info_warning_item_delete') : this.$i18n.get('info_warning_item_trash'),
                    onConfirm: () => {
                        this.deleteItem({ itemId: this.itemId, isPermanently: true })
                        this.$router.push(this.$routerHelper.getCollectionPath(this.form.collectionId))
                    }
                }
            });
        }
    },
    created(){
        // Obtains collection ID
        this.cleanMetadata();
        eventBus.clearAllErrors();
        this.formErrorMessage = '';
        this.collectionId = this.$route.params.collectionId;
        this.form.collectionId = this.collectionId;

        if (this.$route.fullPath.split("/").pop() == "new") {
            this.createNewItem();
        } else if (this.$route.fullPath.split("/").pop() == "edit") {
            this.isLoading = true;

            // Obtains current Item ID from URL
            this.itemId = this.$route.params.itemId;

            // Initializes Media Frames now that itemId exists
            this.initializeMediaFrames();

            this.fetchItem(this.itemId).then(res => {
                this.item = res;

                // Fill this.form data with current data.
                this.form.status = this.item.status;
                this.form.document = this.item.document;
                this.form.document_type = this.item.document_type;
                this.form.comment_status = this.item.comment_status;
                
                if (this.form.document_type != undefined && this.form.document_type == 'url')
                    this.urlLink = this.form.document;
                if (this.form.document_type != undefined && this.form.document_type == 'text')
                    this.textContent = this.form.document;

                if (this.item.status == 'publish' || this.item.status == 'private')
                    this.visibility = this.item.status;

                this.loadMetadata();
                this.setLastUpdated(this.item.modification_date);
            });

            // Fetch current existing attachments
            this.fetchAttachments(this.itemId);
        }

        // Obtains collection name
        this.fetchCollectionName(this.collectionId).then((collectionName) => {
            this.collectionName = collectionName;
        });
        
        // Obtains collection name
        this.fetchCollectionCommentStatus(this.collectionId).then((collectionCommentStatus) => {
            this.collectionCommentStatus = collectionCommentStatus;
        });

        // Sets feedback variables
        eventBus.$on('isUpdatingValue', (status) => {
            this.isUpdatingValues = status;
                // if (this.isUpdatingValues) {
                //     this.$toast.open({
                //         duration: 2000,
                //         message: this.$i18n.get('info_updating_metadata_values'),
                //         position: 'is-bottom',
                //     })
                // }
        });
        eventBus.$on('hasErrorsOnForm', (hasErrors) => {
            if (hasErrors)
                this.formErrorMessage = this.$i18n.get('info_errors_in_form');
            else 
                this.formErrorMessage = '';
        });
        this.cleanLastUpdated();
    },
    beforeRouteLeave ( to, from, next ) {
        if (this.item.status == 'auto-draft') {
            this.$modal.open({
                parent: this,
                component: CustomDialog,
                props: {
                    icon: 'alert',
                    title: this.$i18n.get('label_warning'),
                    message: this.$i18n.get('info_warning_item_not_saved'),
                    onConfirm: () => {
                        next();
                    },
                }
            });  
        } else {
            next()
        }  
    }
}
</script>

<style lang="scss" scoped>

    @import '../../scss/_variables.scss';

    #metadata-column-compress-button {
        position: relative;
        z-index: 99;
        float: right;
        top: 70px;
        max-width: 36px;
        height: 36px;
        width: 36px;
        border: none;
        background-color: $gray2;
        color: $secondary;
        padding: 0px;
        border-top-left-radius: 2px;
        border-bottom-left-radius: 2px;
        cursor: pointer;

        .icon {
            margin-top: 2px;
            margin-right: 8px;
        }
    }

    .page-container {
        padding: 25px 0px;

        .tainacan-page-title {
            padding-left: $page-side-padding;
            padding-right: $page-side-padding;
        }

        .column.is-5-5 {
            width: 45.833333333%;
            padding-left: $page-side-padding;
            padding-right: $page-side-padding;
            transition: width 0.6s;

            @media screen and (max-width: 769px) {
                width: 100%;
            }
        }
        .column.is-4-5 {
            width: 37.5%;
            padding-left: $page-side-padding;
            padding-right: $page-side-padding;
            transition: all 0.6s;

            .field {
                padding: 10px 0px 10px 60px;
            }

            @media screen and (max-width: 769px) {
                width: 100%;
            }

        }

    }

    .section-label {
        position: relative;
        label {
            font-size: 16px !important;
            font-weight: 500 !important;
            color: $blue5 !important;
            line-height: 1.2em;
        }
    }

    .collapse-all {
        font-size: 12px;
        .icon { 
            vertical-align: bottom; 
        }
    }

    .section-box {
       
        background-color: white;
        padding: 26px;
        margin-top: 16px;
        margin-bottom: 38px;

        ul {
            display: flex;
            justify-content: space-evenly;
            li {
                text-align: center;
                button {
                    border-radius: 50px;
                    height: 72px;
                    width: 72px;
                    border: none;
                    background-color: $gray2;
                    color: $secondary;
                    margin-bottom: 6px;
                    &:hover {
                        background-color: $turquoise2;
                        cursor: pointer;
                    }
                }
                p { color: $secondary; }
            }
        }
    }
    .section-status{
        padding: 16px 0;     
        .field .b-radio {
            margin-right: 24px;
            .icon  {
                font-size: 18px !important; 
                color: $gray3;
            }
        }
    }
    .section-attachments {
        border: 1px solid $gray2;
        height: 250px;
        max-width: 100%;
        resize: vertical;
        overflow: auto;

        p { margin: 4px 15px }
    }

    .uploaded-files {
        display: flex;
        flex-flow: wrap;
        margin-left: -15px;
        margin-right: -15px;
    }

    .document-field {  

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
        height: 30px !important;
        width: 30px !important;
        z-index: 99;
        margin-left: 10px !important;
        
        .icon {
            display: inherit;
            padding: 0;
            margin: 0;
            margin-top: 1px;
            font-size: 18px;
        }
    }

    .thumbnail-field {

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
            font-size: 0.8rem;
            font-weight: bold;
            z-index: 99;
            text-align: center;
            color: $gray4;
            top: 70px;
            max-width: 90px;
        }
    
        .thumbnail-buttons-row {
            position: relative;
            left: 90px;
            bottom: 22px;
        }
    }

    .footer {
        padding: 18px $page-side-padding;
        position: absolute;
        bottom: 0;
        z-index: 999999;
        background-color: $gray1;
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
            from { color: $blue5; }
            to { color: $gray4; }
        }

        .update-warning {
            color: $blue5;
            animation-name: blink;
            animation-duration: 0.5s;
            animation-delay: 0.5s;
            align-items: center;
            display: flex;
        }

        .update-info-section {
            color: $gray4;
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
