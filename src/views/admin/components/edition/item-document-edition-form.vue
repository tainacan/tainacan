<template>
    <div v-if="!$adminOptions.hideItemEditionDocument && ( !$adminOptions.hideItemEditionDocumentFileInput && !$adminOptions.hideItemEditionDocumentTextInput && !$adminOptions.hideItemEditionDocumentUrlInput )">
        <div class="section-label">
            <label>
                <span class="icon has-text-gray4">
                    <i :class="'tainacan-icon tainacan-icon-' + ( (!form.document_type || form.document_type == 'empty' ) ? 'item' : (form.document_type == 'attachment' ? 'attachments' : form.document_type))" />
                </span>
                {{ collection && collection.item_document_label ? collection.item_document_label : ( (form.document != undefined && form.document != null && form.document != '') ? $i18n.get('label_document') : $i18n.get('label_document_empty') ) }}
            </label>
            <help-button
                    :title="collection && collection.item_document_label ? collection.item_document_label : $i18n.getHelperTitle('items', 'document')"
                    :message="$i18n.getHelperMessage('items', 'document')" />
        </div>
        <div class="section-box document-field">
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
                            :aria-label="$i18n.get('label_button_edit_document')"
                            @click.prevent="($event) => $emit('on-set-document', $event, form.document_type)">
                        <span
                                v-tooltip="{
                                    content: $i18n.get('edit'),
                                    autoHide: true,
                                    placement: 'bottom',
                                    popperClass: ['tainacan-tooltip', 'tooltip']
                                }"
                                class="icon">
                            <i class="tainacan-icon tainacan-icon-edit" />
                        </span>
                    </a>
                    <a
                            id="button-delete-document"
                            class="button is-rounded is-secondary"
                            size="is-small"
                            :aria-label="$i18n.get('label_button_delete_document')"
                            @click.prevent="$emit('on-remove-document')">
                        <span
                                v-tooltip="{
                                    content: $i18n.get('delete'),
                                    autoHide: true,
                                    placement: 'bottom',
                                    popperClass: ['tainacan-tooltip', 'tooltip']
                                }"
                                class="icon">
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
                            @click.prevent="($event) => $emit('on-set-file-document', $event)">
                        <span class="icon">
                            <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-upload" />
                        </span>
                    </button>
                    <p>{{ $i18n.get('label_file') }}</p>
                </li>
                <li v-if="!$adminOptions.hideItemEditionDocumentTextInput && (collection && collection.item_enabled_document_types && collection.item_enabled_document_types['text'] && collection.item_enabled_document_types['text']['enabled'] === 'yes')">
                    <button
                            type="button"
                            @click.prevent="$emit('on-set-text-document')">
                        <span class="icon">
                            <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-text" />
                        </span>
                    </button>
                    <p>{{ $i18n.get('label_text') }}</p>
                </li>
                <li v-if="!$adminOptions.hideItemEditionDocumentUrlInput && (collection && collection.item_enabled_document_types && collection.item_enabled_document_types['url'] && collection.item_enabled_document_types['url']['enabled'] === 'yes')">
                    <button
                            type="button"
                            @click.prevent="$emit('on-set-url-document')">
                        <span class="icon">
                            <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-url" />
                        </span>
                    </button>
                    <p>{{ $i18n.get('label_url') }}</p>
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
        collection: Object
    },
    emits: [
        'on-set-file-document',
        'on-set-text-document',
        'on-set-url-document',
        'on-set-document',
        'on-remove-document'
    ]
}
</script>

<style lang="scss" scoped>
    
    .document-field {

        .document-field-content {
            max-height: 32vh;

            &.document-field-content--text {
                padding-bottom: 2rem;

                :deep(article) {
                    max-height: calc(32vh - 2rem);
                    overflow-y: auto;
                }
            }

            :deep(img),
            :deep(video),
            :deep(figure) {
                max-width: 100%;
                max-height: 32vh;
                width: auto !important;
                margin: 0;
            }
            :deep(a){
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
                min-height: 150px;
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

        .document-field-placeholder {
            display: flex;
            justify-content: space-evenly;
            padding: 1.5rem 1rem 2rem 1rem;
            border: 1px solid var(--tainacan-input-border-color);
            border-radius: var(--tainacan-input-border-radius, 1px);

            li {
                text-align: center;
                button {
                    border-radius: 1px;
                    height: 72px;
                    width: 72px;
                    border: none;
                    background-color: var(--tainacan-background-color);
                    color: var(--tainacan-secondary);
                    margin-bottom: 6px;
                    transition: background-color 0.3s ease;
                    
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
    .document-buttons-row {
        bottom: -12px;
        left: 0.875em;
        position: absolute;
    }

</style>