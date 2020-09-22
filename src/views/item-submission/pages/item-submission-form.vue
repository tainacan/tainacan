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
                    <div v-if="form.document_type != undefined && form.document_type != null && form.document_type != 'empty'">
                        <div 
                                v-if="form.document_type == 'attachment'"
                                class="section-box section-thumbnail">
                            <b-upload 
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
                                        @close="form.document = null">
                                    {{ form.document.name }}
                                </b-tag>
                            </div>
                        </div>
                        <div v-if="form.document_type == 'text'">
                            <b-input 
                                    type="textarea"
                                    v-model="form.document" />
                            <br>                      
                        </div>
                        <div v-if="form.document_type == 'url'">
                            <b-input 
                                    :placeholder="$i18n.get('instruction_insert_url')"
                                    type="url"
                                    v-model="form.document" />
                            <br>
                        </div>
                        <button
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
                <div class="section-label">
                    <label>{{ $i18n.get('label_thumbnail') }}</label>
                    <help-button
                            :title="$i18n.getHelperTitle('items', '_thumbnail_id')"
                            :message="$i18n.getHelperMessage('items', '_thumbnail_id')"/>

                </div>
                <p>{{ showThumbnailInput ? $i18n.get('info_thumbnail_custom') : $i18n.get('info_thumbnail_default_from_document') }}</p>
                <div class="section-status">
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
                        class="section-box section-thumbnail">
                    <b-upload 
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
                                @close="form.thumbnail = null">
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
                    <label>
                        <span>{{ $i18n.get('label_attachments') }}</span>
                        <span class="icon has-text-gray4">
                            <i class="tainacan-icon tainacan-icon-18px tainacan-icon-attachments"/>
                        </span>
                    </label>
                </div>                

                <div v-if="itemSubmission != undefined">
                    <b-upload 
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
                                @close="form.attachments.splice(index, 1)">
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
            <template v-if="!hideCommentStatus">
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
            </template>

            <!-- Metadata from Collection-------------------------------- -->
            <div class="section-label">
                <label>
                    <span>{{ $i18n.get('metadata') }}</span>
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

            <footer class="footer">

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

        <!-- Message displayed once the form is submitted -->
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
            hasSentForm: false,
            showThumbnailInput: false
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
            'updateThumbnail'
        ]),
        ...mapGetters('item',[
            'getItemSubmission',
            'getItemMetadata'
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
            // Initialize clear data from store
            this.clearItemSubmission();
            
            eventBusItemMetadata.clearAllErrors();
            this.formErrorMessage = '';
            this.form.collectionId = this.collectionId;

            // CREATING NEW ITEM SUBMISSION
            this.createNewItem();
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

            // Pre-fill values incentivate it
            this.form.document = this.itemSubmission.document;
            this.form.document_type = this.itemSubmission.document_type;
            this.form.comment_status = this.itemSubmission.comment_status;

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
                    this.updateItemSubmission({ key: 'metadata', value: metadata.map((metadatum) => { return { metadatum_id: metadatum.id, value: null } }) });
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

    .tainacan-form {
        background-color: var(--tainacan-background-color);
        padding-left: var(--tainacan-one-column);
        padding-right: var(--tainacan-one-column);

        @media screen and (max-width: 769px) {
            max-width: 100%;
        }
        .field:not(:last-child) {
            margin-bottom: 0.5em;
        }
        .field {
            padding: 10px 0px 14px 60px;
        }
         .columns {
            flex-wrap: wrap;
            justify-content: space-between;

            .column {
                padding: 1em 12px 0 12px;
            }
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

    .files-list {
        display: flex;
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
