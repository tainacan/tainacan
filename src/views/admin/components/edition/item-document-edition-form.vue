<template>
    <div>
        <div 
                v-if="!$adminOptions.hideItemEditionDocument"
                class="section-label">
            <label>
                <span class="icon has-text-gray4">
                    <i :class="'tainacan-icon tainacan-icon-' + ( (!form.document_type || form.document_type == 'empty' ) ? 'item' : (form.document_type == 'attachment' ? 'attachments' : form.document_type))"/>
                </span>
                {{ form.document != undefined && form.document != null && form.document != '' ? $i18n.get('label_document') : $i18n.get('label_document_empty') }}
            </label>
            <help-button
                    :title="$i18n.getHelperTitle('items', 'document')"
                    :message="$i18n.getHelperMessage('items', 'document')"/>
        </div>
        <div 
                v-if="!$adminOptions.hideItemEditionDocument"
                class="section-box document-field">
            <div
                    v-if="form.document != undefined && form.document != null &&
                            form.document_type != undefined && form.document_type != null &&
                            form.document != '' && form.document_type != 'empty'"
                    class="document-field-content"
                    :class="'document-field-content--' + form.document_type">
                <div v-html="item.document_as_html" />
                <div class="document-buttons-row">
                    <a
                            class="button is-rounded is-secondary"
                            size="is-small"
                            id="button-edit-document"
                            :aria-label="$i18n.get('label_button_edit_document')"
                            @click.prevent="($event) => $emit('onSetDocument', $event, form.document_type)">
                        <span
                                v-tooltip="{
                                    content: $i18n.get('edit'),
                                    autoHide: true,
                                    placement: 'bottom',
                                    popperClass: ['tainacan-tooltip', 'tooltip']
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
                            @click.prevent="$emit('onRemoveDocument')">
                        <span
                                v-tooltip="{
                                    content: $i18n.get('delete'),
                                    autoHide: true,
                                    placement: 'bottom',
                                    popperClass: ['tainacan-tooltip', 'tooltip']
                                }"
                                class="icon">
                            <i class="tainacan-icon tainacan-icon-delete"/>
                        </span>
                    </a>
                </div>
            </div>
            <ul 
                    v-else
                    class="document-field-placeholder">
                <li v-if="!$adminOptions.hideItemEditionDocumentFileInput">
                    <button
                            type="button"
                            @click.prevent="($event) => $emit('onSetFileDocument', $event)">
                        <span class="icon">
                            <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-upload"/>
                        </span>
                    </button>
                    <p>{{ $i18n.get('label_file') }}</p>
                </li>
                <li v-if="!$adminOptions.hideItemEditionDocumentTextInput">
                    <button
                            type="button"
                            @click.prevent="$emit('onSetTextDocument')">
                        <span class="icon">
                            <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-text"/>
                        </span>
                    </button>
                    <p>{{ $i18n.get('label_text') }}</p>
                </li>
                <li v-if="!$adminOptions.hideItemEditionDocumentUrlInput">
                    <button
                            type="button"
                            @click.prevent="$emit('onSetURLDocument')">
                        <span class="icon">
                            <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-url"/>
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
        form: Object
    }
}
</script>

<style lang="scss" scoped>
    
    .document-field {

        .document-field-content {
            max-height: 32vh;

            &.document-field-content--text {
                padding-bottom: 2rem;
            }

            /deep/ img,
            /deep/ video,
            /deep/ figure {
                max-width: 100%;
                max-height: 32vh;
                width: auto !important;
                margin: 0;
            }
            /deep/ a {
                min-height: 60px;
                display: block;
            }
            /deep/ audio,
            /deep/ iframe,
            /deep/ blockquote {
                max-width: 100%;
                max-height: 32vh;
                width: 100%;
                margin: 0;
                min-height: 150px;
            }
            /deep/ audio {
                min-height: 80px;
            }

            @media screen and (max-height: 760px) {
                max-height: 25vh;

                /deep/ img,
                /deep/ video,
                /deep/ figure {
                    max-height: 25vh;
                }
                /deep/ audio,
                /deep/ iframe,
                /deep/ blockquote {
                    max-height: 25vh;
                }
            }
        }

        .document-field-placeholder {
            display: flex;
            justify-content: space-evenly;
            padding: 1.5rem 1rem 2rem 1rem;
            border: 1px solid var(--tainacan-input-border-color);

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