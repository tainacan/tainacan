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
                        <p>{{ $i18n.get('label_file') }}</p>
                    </button>
                </li>
                <li v-if="!$adminOptions.hideItemEditionDocumentTextInput && (collection && collection.item_enabled_document_types && collection.item_enabled_document_types['text'] && collection.item_enabled_document_types['text']['enabled'] === 'yes')">
                    <button
                            type="button"
                            @click.prevent="$emit('on-set-text-document')">
                        <span class="icon">
                            <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-text" />
                        </span>
                        <p>{{ $i18n.get('label_text') }}</p>
                    </button>
                </li>
                <li v-if="!$adminOptions.hideItemEditionDocumentUrlInput && (collection && collection.item_enabled_document_types && collection.item_enabled_document_types['url'] && collection.item_enabled_document_types['url']['enabled'] === 'yes')">
                    <button
                            type="button"
                            @click.prevent="$emit('on-set-url-document')">
                        <span class="icon">
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
            gap: 0.125rem;
            justify-content: space-evenly;
            align-items: center;
            padding: 0.5rem;
            min-height: 130px;
            border: 1px solid var(--tainacan-input-border-color);
            border-radius: var(--tainacan-input-border-radius, 2px);

            @container documentfield (max-width: 300px) {
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
                button {
                    text-align: center;
                    display: flex;
                    justify-content: center;
                    flex-direction: column;
                    align-items: center;
                    border-radius: var(--tainacan-button-border-radius, 4px);
                    height: 92px;
                    width: 92px;
                    padding: 4px;
                    border: none;
                    background-color: var(--tainacan-background-color);
                    color: var(--tainacan-secondary);
                    transition: background-color 0.3s ease;
                    
                    &:hover {
                        background-color: var(--tainacan-primary);
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
        left: 0.875em;
        position: absolute;
    }

</style>