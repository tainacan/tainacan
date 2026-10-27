<template>
    <div v-if="!$adminOptions.hideItemEditionDocument && ( !$adminOptions.hideItemEditionDocumentFileInput || !$adminOptions.hideItemEditionDocumentTextInput || !$adminOptions.hideItemEditionDocumentUrlInput )">
        <div class="section-label">
            <label>
                <span class="icon has-text-dark">
                    <i :class="'tainacan-icon tainacan-icon-' + ( (!form.document_type || form.document_type == 'empty' ) ? 'item' : (form.document_type == 'attachment' ? 'attachments' : form.document_type))" />
                </span>
                {{ collection && collection.item_document_label ? collection.item_document_label : ( (form.document != undefined && form.document != null && form.document != '') ? $i18n.get('label_document') : $i18n.get('label_document_empty') ) }}
            </label>
            <help-button
                    :title="collection && collection.item_document_label ? collection.item_document_label : $i18n.getHelperTitle('items', 'document')"
                    :message="$i18n.getHelperMessage('items', 'document')" />
        </div>
        <div
                class="section-box document-field"
                :class="{ 'is-dragging-over': isDraggingOver }"
                @dragenter="onDragEnter"
                @dragover="onDragOver"
                @dragleave="onDragLeave"
                @drop="onDrop">
            <div
                    v-if="isDraggingOver"
                    class="document-drop-overlay"
                    aria-hidden="true">
                <span class="icon">
                    <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-upload" />
                </span>
                <p>{{ $i18n.get('instruction_drop_file_to_set_document') }}</p>
            </div>
            <div
                    v-if="uploadProgress !== null"
                    class="document-upload-progress"
                    role="progressbar"
                    :aria-label="$i18n.get('info_uploading_document')"
                    aria-valuemin="0"
                    aria-valuemax="100"
                    :aria-valuenow="uploadProgress">
                <p>{{ $i18n.get('info_uploading_document') }} ({{ uploadProgress }}%)</p>
                <div class="document-upload-progress-track">
                    <div
                            class="document-upload-progress-bar"
                            :style="{ width: uploadProgress + '%' }" />
                </div>
            </div>
            <div
                    v-if="form.document != undefined && form.document != null &&
                        form.document_type != undefined && form.document_type != null &&
                        form.document != '' && form.document_type != 'empty'"
                    class="document-field-content"
                    :class="'document-field-content--' + form.document_type">
                <div v-html="item.document_as_html" />
                <div class="document-buttons-row">
                    <a
                            id="button-edit-document"
                            class="button is-rounded is-secondary"
                            size="is-small"
                            role="button"
                            tabindex="0"
                            :aria-label="$i18n.get('label_button_edit_document')"
                            @click.prevent="($event) => $emit('on-set-document', $event, form.document_type)"
                            @keydown.enter.prevent="($event) => $emit('on-set-document', $event, form.document_type)"
                            @keydown.space.prevent="($event) => $emit('on-set-document', $event, form.document_type)">
                        <span
                                v-tooltip="{
                                    content: $i18n.get('edit'),
                                    autoHide: true,
                                    placement: 'bottom',
                                    popperClass: ['tainacan-tooltip', 'tooltip']
                                }"
                                class="icon"
                                aria-hidden="true">
                            <i class="tainacan-icon tainacan-icon-edit" />
                        </span>
                    </a>
                    <a
                            v-if="collection && collection.item_enable_document_content_editing === 'yes' && form.document_type !== 'text'"
                            id="button-edit-document-content-index"
                            class="button is-rounded is-secondary"
                            size="is-small"
                            role="button"
                            tabindex="0"
                            :aria-label="$i18n.get('label_button_index_document')"
                            @click.prevent="($event) => $emit('on-edit-document-content-index', $event)"
                            @keydown.enter.prevent="($event) => $emit('on-edit-document-content-index', $event)"
                            @keydown.space.prevent="($event) => $emit('on-edit-document-content-index', $event)">
                        <span
                                v-tooltip="{
                                    content: $i18n.get('label_document_content'),
                                    autoHide: true,
                                    placement: 'bottom',
                                    popperClass: ['tainacan-tooltip', 'tooltip']
                                }"
                                class="icon"
                                aria-hidden="true">
                            <i class="tainacan-icon tainacan-icon-text" />
                        </span>
                    </a>
                    <a
                            id="button-delete-document"
                            class="button is-rounded is-secondary"
                            size="is-small"
                            :aria-label="$i18n.get('label_button_delete_document')"
                            role="button"
                            tabindex="0"
                            @click.prevent="$emit('on-remove-document')"
                            @keydown.enter.prevent="$emit('on-remove-document')"
                            @keydown.space.prevent="$emit('on-remove-document')">
                        <span
                                v-tooltip="{
                                    content: $i18n.get('delete'),
                                    autoHide: true,
                                    placement: 'bottom',
                                    popperClass: ['tainacan-tooltip', 'tooltip']
                                }"
                                class="icon"
                                aria-hidden="true">
                            <i class="tainacan-icon tainacan-icon-delete" />
                        </span>
                    </a>
                </div>
            </div>
            <ul 
                    v-else
                    class="document-field-placeholder">
                <li v-if="!$adminOptions.hideItemEditionDocumentFileInput && (collection && collection.item_enabled_document_types && collection.item_enabled_document_types['attachment'] && collection.item_enabled_document_types['attachment']['enabled'] === 'yes')">
                    <button
                            type="button"
                            tabindex="0"
                            @click.prevent="($event) => $emit('on-set-file-document', $event)"
                            @keydown.enter.prevent="($event) => $emit('on-set-file-document', $event)"
                            @keydown.space.prevent="($event) => $emit('on-set-file-document', $event)">
                        <span 
                                aria-hidden="true"
                                class="icon">
                            <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-upload" />
                        </span>
                        <p>{{ $i18n.get('label_file') }}</p>
                    </button>
                </li>
                <li v-if="!$adminOptions.hideItemEditionDocumentTextInput && (collection && collection.item_enabled_document_types && collection.item_enabled_document_types['text'] && collection.item_enabled_document_types['text']['enabled'] === 'yes')">
                    <button
                            type="button"
                            tabindex="0"
                            @click.prevent="$emit('on-set-text-document')"
                            @keydown.enter.prevent="$emit('on-set-text-document')"
                            @keydown.space.prevent="$emit('on-set-text-document')">
                        <span 
                                aria-hidden="true"
                                class="icon">
                            <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-text" />
                        </span>
                        <p>{{ $i18n.get('label_text') }}</p>
                    </button>
                </li>
                <li v-if="!$adminOptions.hideItemEditionDocumentUrlInput && (collection && collection.item_enabled_document_types && collection.item_enabled_document_types['url'] && collection.item_enabled_document_types['url']['enabled'] === 'yes')">
                    <button
                            type="button"
                            tabindex="0"
                            @click.prevent="$emit('on-set-url-document')"
                            @keydown.enter.prevent="$emit('on-set-url-document')"
                            @keydown.space.prevent="$emit('on-set-url-document')">
                        <span 
                                aria-hidden="true"
                                class="icon">
                            <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-url" />
                        </span>
                        <p>{{ $i18n.get('label_url') }}</p>
                    </button>
                </li>
            </ul>
        </div>
    </div>
</template>

<script>
export default {
    props: {
        item: Object,
        form: Object,
        collection: Object,
        uploadProgress: {
            type: Number,
            default: null
        }
    },
    emits: [
        'on-set-file-document',
        'on-drop-file-document',
        'on-set-text-document',
        'on-set-url-document',
        'on-set-document',
        'on-remove-document',
        'on-edit-document-content-index'
    ],
    data() {
        return {
            isDraggingOver: false,
            dragEnterCounter: 0
        }
    },
    computed: {
        acceptsFileDrop() {
            return !this.$adminOptions.hideItemEditionDocumentFileInput &&
                this.uploadProgress === null &&
                this.collection &&
                this.collection.item_enabled_document_types &&
                this.collection.item_enabled_document_types['attachment'] &&
                this.collection.item_enabled_document_types['attachment']['enabled'] === 'yes';
        }
    },
    methods: {
        isDraggingFiles(event) {
            return !!event.dataTransfer && Array.from(event.dataTransfer.types || []).includes('Files');
        },
        onDragEnter(event) {
            if ( !this.acceptsFileDrop || !this.isDraggingFiles(event) )
                return;

            event.preventDefault();

            // Counts nested dragenter/dragleave pairs so the overlay does not flicker over child elements
            this.dragEnterCounter++;
            this.isDraggingOver = true;
        },
        onDragOver(event) {
            if ( !this.acceptsFileDrop || !this.isDraggingFiles(event) )
                return;

            // Required for the browser to allow the drop event
            event.preventDefault();
            event.dataTransfer.dropEffect = 'copy';
        },
        onDragLeave() {
            if ( !this.isDraggingOver )
                return;

            this.dragEnterCounter--;
            if ( this.dragEnterCounter <= 0 )
                this.resetDragState();
        },
        onDrop(event) {
            if ( !this.acceptsFileDrop || !this.isDraggingFiles(event) )
                return;

            event.preventDefault();
            this.resetDragState();

            if ( event.dataTransfer.files && event.dataTransfer.files.length )
                this.$emit('on-drop-file-document', Array.from(event.dataTransfer.files));
        },
        resetDragState() {
            this.dragEnterCounter = 0;
            this.isDraggingOver = false;
        }
    }
}
</script>

<style lang="scss" scoped>
    
    .document-field {

        &.is-dragging-over .document-field-placeholder,
        &.is-dragging-over .document-field-content {
            opacity: 0.25;
        }

        .document-drop-overlay,
        .document-upload-progress {
            position: absolute;
            inset: 0;
            // Matches the horizontal padding of .section-box in the item edition form
            inset-inline-start: 1.75em;
            inset-inline-end: 0.75em;
            z-index: 2;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            gap: 0.5rem;
            padding: 1rem;
            text-align: center;
            border-radius: var(--tainacan-item-border-radius, 3px);
            color: var(--tainacan-secondary);

            p {
                font-size: 0.875em;
                margin: 0;
            }
        }
        .document-drop-overlay {
            pointer-events: none;
            border: 2px dashed var(--tainacan-secondary);
            background-color: var(--tainacan-primary);
        }
        .document-upload-progress {
            background-color: var(--tainacan-background-color);

            .document-upload-progress-track {
                width: 80%;
                height: 0.5rem;
                overflow: hidden;
                border-radius: 0.25rem;
                background-color: var(--tainacan-input-border-color);
            }
            .document-upload-progress-bar {
                height: 100%;
                background-color: var(--tainacan-secondary);
                transition: width 0.2s ease;
            }
        }

        .document-field-content {
            max-height: 32vh;

            &.document-field-content--text {

                :deep(article) {
                    max-height: calc(32vh - 2rem);
                    overflow-y: auto;
                }
            }
            &.document-field-content--url:not(:has(iframe)):not(:has(blockquote)):not(:has(audio)):not(:has(figure)):not(:has(video)):not(:has(img)),
            &.document-field-content--text {
                border-radius: var(--tainacan-item-border-radius, 3px);
                background-color: var(--tainacan-input-disabled-color, var(--tainacan-input-border-color));
                padding: 0.75em 0.75em 1.75rem 0.75em;
                white-space: break-spaces;
                word-wrap: normal;
                font-size: 0.875em;

                :deep(a) {
                    min-height: unset;
                }
            }

            :deep(img),
            :deep(video),
            :deep(figure) {
                max-width: 100% !important;
                max-height: 32vh;
                width: auto !important;
                margin: 0;
            }
            :deep(a) {
                min-height: 60px;
                display: block;
            }
            :deep(audio),
            :deep(iframe),
            :deep(blockquote) {
                max-width: 100%;
                max-height: 32vh;
                width: 100%;
                margin: 0;
            }
            :deep(iframe):only-child,
            :deep(blockquote):only-child {
                min-height: 150px;
            }
            :deep(blockquote+iframe) {
                max-height: calc(32vh - 150px);
            }
            :deep(audio) {
                min-height: 80px;
            }

            @media screen and (max-height: 760px) {
                max-height: 25vh;

                :deep(img),
                :deep(video),
                :deep(figure),
                :deep(audio),
                :deep(iframe),
                :deep(blockquote) {
                    max-height: 25vh;
                }
            }
        }

        @supports (contain: inline-size) {
            container-type: inline-size;
            container-name: documentfield; 
        }

        .document-field-placeholder {
            display: flex;
            gap: 0.25rem;
            justify-content: space-evenly;
            align-items: center;
            padding: 0.5rem;
            min-height: 136px;
            border: 1px solid var(--tainacan-input-border-color);
            border-radius: var(--tainacan-item-border-radius, 3px);
            background-color: var(--tainacan-input-disabled-color);

            @container documentfield (max-width: 322px) {
                & {
                    flex-direction: column;
                    align-items: start;
                    
                    li {
                        button {
                            flex-direction: row !important;
                            width: 100% !important;
                            justify-content: start !important;
                            text-align: start !important;
                            max-height: 40px !important;
                            gap: 8px !important;
                            padding: 4px 8px !important;

                            p {
                                margin-bottom: 0px !important;
                            }
                        }
                    }
                }
            }

            li {
                min-width: 28%;
                button {
                    text-align: center;
                    display: flex;
                    justify-content: center;
                    flex-direction: column;
                    align-items: center;
                    border-radius: var(--tainacan-button-border-radius, 4px);
                    height: 100px;
                    min-width: 100px;
                    width: 100%;
                    padding: 4px;
                    border: 1px solid var(--tainacan-input-border-color);
                    background-color: var(--tainacan-background-color);
                    color: var(--tainacan-secondary);
                    transition: background-color 0.3s ease;
                    
                    &:hover {
                        background-color: var(--tainacan-primary);
                        border-color: var(--tainacan-secondary);
                        cursor: pointer;
                    }
                    .icon {
                        height: calc(80px - 1em);
                    }
                    p { 
                        font-size: 0.8125em;
                        margin-bottom: 4px;
                    }
                }
            }
        }
    }
    .document-buttons-row {
        bottom: -12px;
        inset-inline-start: 0.875em;
        position: absolute;
    }

</style>