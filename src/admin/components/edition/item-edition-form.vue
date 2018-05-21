<template>
    <div>
        <tainacan-title />
        <form
                v-if="!isLoading"
                class="tainacan-form"
                label-width="120px">
            <div class="columns">
                <div class="column is-5">

                    <!-- Status -------------------------------- -->
                    <div class="section-label">
                        <label>{{ $i18n.get('label_status') }}</label>
                        <span class="required-field-asterisk">*</span>
                        <help-button
                                :title="$i18n.getHelperTitle('items', 'status')"
                                :message="$i18n.getHelperMessage('items', 'status')"/>
                    </div>
                    <div class="section-box">
                        <div class="field">
                            <b-select
                                    v-model="form.status"
                                    :placeholder="$i18n.get('instruction_select_a_status')">
                                <option
                                        :id="`status-option-${statusOption.value}`"
                                        v-for="statusOption in statusOptions"
                                        :key="statusOption.value"
                                        :value="statusOption.value"
                                        :disabled="statusOption.disabled">{{ statusOption.label }}
                                </option>
                            </b-select>
                            <p
                                    v-if="item.status == 'auto-draft'"
                                    class="help is-danger">
                                {{ $i18n.get('info_item_not_saved') }}
                            </p>
                        </div>
                        <div class="field is-grouped">
                            <div class="control">
                                <button
                                        type="button"
                                        id="button-submit-item-creation"
                                        @click.prevent="onSubmit"
                                        class="button is-success">
                                    {{ $i18n.get('save') }}
                                </button>
                            </div>
                        </div>
                        <p class="help is-danger">{{ formErrorMessage }}</p>
                    </div>

                    <!-- Document -------------------------------- -->
                    <div class="section-label">
                        <label>{{ form.document != undefined && form.document != null && form.document != '' ? $i18n.get('label_document') : $i18n.get('label_document_empty') }}</label>
                        <help-button
                                :title="$i18n.getHelperTitle('items', 'document')"
                                :message="$i18n.getHelperMessage('items', 'document')"/>
                    </div>
                    <div class="section-box">
                        <div
                                v-if="form.document != undefined && form.document != null &&
                                        form.document_type != undefined && form.document_type != null &&
                                        form.document != '' && form.document_type != 'empty'">
                            <div v-if="form.document_type == 'attachment'">
                                <div v-html="item.document_as_html" />
                                <button
                                        type="button"
                                        class="button is-primary"
                                        size="is-small"
                                        @click.prevent="setFileDocument($event)">
                                    {{ $i18n.get('edit') }}
                                </button>
                                <button
                                        type="button"
                                        class="button is-primary"
                                        size="is-small"
                                        @click.prevent="removeDocument()">
                                    {{ $i18n.get('remove') }}
                                </button>
                            </div>
                            <div v-if="form.document_type == 'text'">
                                <div v-html="item.document_as_html" />
                                <button
                                        type="button"
                                        class="button is-primary"
                                        size="is-small"
                                        @click.prevent="setTextDocument()">
                                    {{ $i18n.get('edit') }}
                                </button>
                                <button
                                        type="button"
                                        class="button is-primary"
                                        size="is-small"
                                        @click.prevent="removeDocument()">
                                    {{ $i18n.get('remove') }}
                                </button>
                            </div>
                            <div v-if="form.document_type == 'url'">
                                <div v-html="item.document_as_html" />
                                <button
                                        type="button"
                                        class="button is-primary"
                                        size="is-small"
                                        @click.prevent="setURLDocument()">
                                    {{ $i18n.get('edit') }}
                                </button>
                                <button
                                        type="button"
                                        class="button is-primary"
                                        size="is-small"
                                        @click.prevent="removeDocument()">
                                    {{ $i18n.get('remove') }}
                                </button>
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
                                    :class="{'has-content': textContent != undefined && textContent != ''}"
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
                            <b-input
                                    :class="{'has-content': urlLink != undefined && urlLink != ''}"
                                    v-model="urlLink"/>

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
                    <div class="section-box">
                        <div class="thumbnail-field">
                            <a
                                    class="button is-rounred is-secondary"
                                    id="button-edit-thumbnail"
                                    :aria-label="$i18n.get('label_button_edit_thumb')"
                                    @click.prevent="thumbnailMediaFrame.openFrame($event)">
                                <b-icon icon="pencil" />
                            </a>
                            <figure class="image">
                                <span
                                        v-if="item.thumbnail == undefined || item.thumbnail == false"
                                        class="image-placeholder">{{ $i18n.get('label_empty_thumbnail') }}</span>
                                <img
                                        id="thumbail-image"
                                        :alt="$i18n.get('label_thumbnail')"
                                        :src="(item.thumbnail == undefined || item.thumbnail == false) ? thumbPlaceholderPath : item.thumbnail">
                            </figure>
                            <div class="thumbnail-buttons-row">
                                <a
                                        id="button-delete"
                                        :aria-label="$i18n.get('label_button_delete_thumb')"
                                        @click="deleteThumbnail()">
                                    <b-icon icon="delete" />
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Attachments ------------------------------------------ -->
                    <div class="section-label">
                        <label>{{ $i18n.get('label_attachments') }}</label>
                    </div>
                    <div class="section-box">
                        <button
                                type="button"
                                class="button is-secondary"
                                @click.prevent="attachmentMediaFrame.openFrame($event)">
                            {{ $i18n.get("label_add_attachment") }}
                        </button>

                        <div class="uploaded-files">
                            <file-item 
                                    v-for="(attachment, index) in attachmentsList"
                                    :key="index"
                                    :show-name="true"
                                    :file="attachment"/>
                        </div>
                    </div>

                </div>
                <div class="column is-1" />
                <div class="column is-6">
                    <label class="section-label">{{ $i18n.get('fields') }}</label>
                    <br>
                    <a
                            class="collapse-all"
                            @click="toggleCollapseAll()">
                        {{ collapseAll ? $i18n.get('label_collapse_all') : $i18n.get('label_expand_all') }}
                         <b-icon
                                type="is-secondary"
                                :icon=" collapseAll ? 'menu-down' : 'menu-right'" />
                    </a>

                    <!-- Fields from Collection-------------------------------- -->
                    <tainacan-form-item
                            v-for="(field, index) of fieldList"
                            :key="index"
                            :field="field"
                            :is-collapsed="fieldCollapses[index]" 
                            @changeCollapse="onChangeCollapse($event, index)"/>

                </div>
            </div>
        </form>

        <b-loading
                :active.sync="isLoading"
                :can-cancel="false"/>
    </div>
</template>

<script>
import { mapActions, mapGetters } from 'vuex';
import { eventBus } from '../../../js/event-bus-web-components.js'
import wpMediaFrames from '../../js/wp-media-frames';
import FileItem from '../other/file-item.vue';

export default {
    name: 'ItemEditionForm',
    data(){
        return {
            pageTitle: '',
            itemId: Number,
            item: null,
            collectionId: Number,
            isLoading: false,
            fieldCollapses: [],
            collapseAll: false,
            form: {
                collectionId: Number,
                status: '',
                document: '',
                document_type: ''
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
            textLink: ''
        }
    },
    components: {
        FileItem
    },
    methods: {
        ...mapActions('item', [
            'sendItem',
            'updateItem',
            'updateItemDocument',
            'fetchFields',
            'sendField',
            'fetchItem',
            'cleanFields',
            'sendAttachments',
            'updateThumbnail',
            'fetchAttachments'
        ]),
        ...mapGetters('item',[
            'getFields',
            'getAttachments'
        ]),
        onSubmit() {
            // Puts loading on Item edition
            this.isLoading = true;

            let data = {item_id: this.itemId, status: this.form.status};

            this.updateItem(data).then(updatedItem => {

                this.item = updatedItem;

                // Fill this.form data with current data.
                this.form.status = this.item.status;
                this.form.document = this.item.document;
                this.form.document_type = this.item.document_type;

                this.isLoading = false;

                this.$router.push(this.$routerHelper.getItemPath(this.form.collectionId, this.itemId));
            })
            .catch((errors) => {
                for (let error of errors.errors) {
                    for (let field of Object.keys(error)){
                       eventBus.errors.push({ field_id: field, errors: error[field]});
                    }
                }
                this.formErrorMessage = errors.error_message;

                this.isLoading = false;
            });
        },
        createNewItem() {
            // Puts loading on Draft Item creation
            this.isLoading = true;

            // Creates draft Item
            let data = {collection_id: this.form.collectionId, status: 'auto-draft'};
            this.sendItem(data).then(res => {

                this.itemId = res.id;
                this.item = res;

                // Initializes Media Frames now that itemId exists
                this.initializeMediaFrames();

                // Pre-fill status with publish to incentivate it
                this.form.status = 'publish';
                this.form.document = this.item.document;
                this.form.document_type = this.item.document_type;

                this.loadMetadata();

            })
            .catch(error => this.$console.error(error));
        },
        loadMetadata() {
            // Obtains Item Field
            this.fetchFields(this.itemId).then((fields) => {
                this.isLoading = false;
                for (let field of fields) {
                    this.fieldCollapses.push(field.field.required == 'yes');
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
                    for (let field of Object.keys(error)){
                       eventBus.errors.push({ field_id: field, errors: error[field]});
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
                    for (let field of Object.keys(error)){
                       eventBus.errors.push({ field_id: field, errors: error[field]});
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
                                for (let field of Object.keys(error)){
                                eventBus.errors.push({ field_id: field, errors: error[field]});
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

            for (let i = 0; i < this.fieldCollapses.length; i++)
                this.fieldCollapses[i] = this.collapseAll;

        },
        onChangeCollapse(event, index) {
            this.fieldCollapses.splice(index, 1, event);
        }
    },
    computed: {
        fieldList() {
            return JSON.parse(JSON.stringify(this.getFields()));
        },
        attachmentsList(){
            return this.getAttachments();
        }
    },
    created(){
        // Obtains collection ID
        this.cleanFields();
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
                if (this.form.document_type != undefined && this.form.document_type == 'url')
                    this.urlLink = this.form.document;
                if (this.form.document_type != undefined && this.form.document_type == 'text')
                    this.textContent = this.form.document;

                this.loadMetadata();
            });

            // Fetch current existing attachments
            this.fetchAttachments(this.itemId);
        }
    },
    mounted() {
        document.getElementById('collection-page-container').addEventListener('scroll', ($event) => {
            this.$shouldShrink = ($event.originalTarget.scrollTop > 5); 
        });
    },
    beforeRouteLeave ( to, from, next ) {
        if (this.item.status == 'auto-draft') {
            this.$dialog.confirm({
                message: this.$i18n.get('info_warning_item_not_saved'),
                    onConfirm: () => {
                        next();
                    },
                    cancelText: this.$i18n.get('cancel'),
                    confirmText: this.$i18n.get('continue'),
                    type: 'is-secondary'
                });  
        } else {
            next()
        }  
    }
}
</script>

<style lang="scss" scoped>

    @import '../../scss/_variables.scss';

    .page-container{
        height: calc(100% - 82px);
    }

    form>.columns>.column{
        padding: 0px;
    }

    .section-label {
        position: relative;
        label {
            font-size: 16px !important;
            font-weight: 500 !important;
            color: $tertiary !important;
            line-height: 1.2em;
        }
    }

    .collapse-all {
        font-size: 12px;
        .icon { vertical-align: bottom; }
    }

    .section-box {
        border: 1px solid $draggable-border-color;
        padding: 30px;
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
                    background-color: $tainacan-input-background;
                    color: $secondary;
                    margin-bottom: 6px;
                    &:hover {
                        background-color: $primary-light;
                        cursor: pointer;
                    }
                }
                p { color: $secondary; }
            }
        }
    }

    .uploaded-files {
        display: flex;
        flex-flow: wrap;
    }

    .thumbnail-field {
        max-height: 128px;
        margin-bottom: 96px;
        margin-top: -20px;

        .content {
            padding: 10px;
            font-size: 0.8em;
        }
        img {
            position: absolute;
            height: 105px;
            width: 105px;
        }
        .image-placeholder {
            position: absolute;
            margin-left: 10px;
            margin-right: 10px;
            font-size: 0.8rem;
            font-weight: bold;
            z-index: 99;
            text-align: center;
            color: gray;
            top: 38px;
            max-width: 90px;
        }
        #button-edit-thumbnail {

            border-radius: 100px !important;
            height: 40px !important;
            width: 40px !important;
            bottom: -20px;
            left: -20px;
            z-index: 99;

            .icon {
                display: inherit;
                padding: 0;
                margin: 0;
                margin-top: 1px;
            }
        }
        .thumbnail-buttons-row {
            display: none;
        }
        &:hover {
             .thumbnail-buttons-row {
                display: inline-block;
                position: relative;
                top: 0px;
                background-color: rgba(255, 255, 255, 0.9);
                padding: 2px 8px;
                border-radius: 0px 0px 0px 4px;
                left: 65px;
            }
        }

    }


</style>
