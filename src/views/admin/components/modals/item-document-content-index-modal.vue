<template>
    <form
            ref="itemDocumentContentIndexModal"
            autofocus
            role="dialog"
            tabindex="-1"
            aria-modal
            aria-labelledby="item-document-content-index-modal-title"
            class="tainacan-modal-content tainacan-form"
            @submit.prevent="saveDocumentContentIndex()">
        <div class="tainacan-modal-title">
            <h2 id="item-document-content-index-modal-title">
                {{ $i18n.get('label_document_content') }}
            </h2>
            <p class="help">
                <strong>{{ $i18n.get('info_document_content_index_description') }}</strong>
            </p>
        </div>
        <b-loading
                v-model="isLoading"
                :is-full-page="false"
                :can-cancel="false" />
        <template v-if="!isLoading">
            <div
                    v-if="!localDocumentContentIndex"
                    class="document-content-index-empty-help help">
                <p><em>{{ $i18n.get('info_document_content_index_empty') }}</em></p>
                <p v-if="isPdfDocument">
                    {{ $i18n.get('info_document_content_index_empty_pdf') }}
                </p>
                <p v-else>
                    {{ $i18n.get('info_document_content_index_empty_non_pdf') }}
                </p>
            </div>
            <b-input
                    ref="item-document-content-index-input"
                    v-model="localDocumentContentIndex"
                    aria-labelledby="item-document-content-index-modal-title"
                    type="textarea"
                    :rows="12" />
            <div
                    v-if="canExtractDocumentContent"
                    class="document-content-index-actions">
                <a
                        id="button-extract-document-content-index"
                        role="button"
                        tabindex="0"
                        class="link-style"
                        :class="{ 'is-disabled': isExtracting }"
                        :aria-disabled="isExtracting"
                        @click.prevent="!isExtracting && extractDocumentContentIndex()"
                        @keydown.enter.prevent="!isExtracting && extractDocumentContentIndex()"
                        @keydown.space.prevent="!isExtracting && extractDocumentContentIndex()">
                    <span class="icon is-small">
                        <i
                                class="tainacan-icon has-text-secondary tainacan-icon-updating"
                                :class="{ 'tainacan-icon-spin': isExtracting }" />
                    </span>
                    {{ $i18n.get('label_extract_document_content') }}
                </a>
            </div>
        </template>

        <div class="field is-grouped form-submit">
            <div class="control">
                <button
                        id="button-cancel-document-content-index"
                        class="button is-outlined"
                        type="button"
                        :disabled="isLoading || isSaving"
                        @click="closeModal()">
                    {{ $i18n.get('cancel') }}</button>
            </div>
            <div class="control">
                <button
                        id="button-save-document-content-index"
                        type="submit"
                        class="button is-success"
                        :disabled="isLoading || isSaving || !hasDocumentContentIndexChanged"
                        :loading="isSaving">
                    {{ $i18n.get('save') }}</button>
            </div>
        </div>
    </form>
</template>

<script>
import { mapActions } from 'vuex';

export default {
    props: {
        itemId: Number|String,
        documentType: String,
        documentMimetype: String
    },
    emits: [
        'beforeClose',
        'close'
    ],
    data() {
        return {
            localDocumentContentIndex: '',
            savedDocumentContentIndex: '',
            isLoading: false,
            isExtracting: false,
            isSaving: false
        }
    },
    computed: {
        isPdfDocument() {
            return this.documentType === 'attachment' && this.documentMimetype === 'application/pdf';
        },
        canExtractDocumentContent() {
            return this.isPdfDocument
                && typeof tainacan_plugin !== 'undefined'
                && tainacan_plugin.tainacan_index_pdf_content;
        },
        hasDocumentContentIndexChanged() {
            return this.localDocumentContentIndex !== this.savedDocumentContentIndex;
        }
    },
    watch: {
        isLoading(isLoading) {
            if (!isLoading)
                this.$nextTick(() => this.focusModal());
        }
    },
    created() {
        this.loadDocumentContentIndex();
    },
    mounted() {
        this.focusModal();
    },
    methods: {
        ...mapActions('item', [
            'fetchItemDocumentContentIndex',
            'extractItemDocumentContentIndex',
            'updateItemDocumentContentIndex'
        ]),
        closeModal() {
            this.$emit('beforeClose');
            this.$emit('close');
        },
        focusModal() {
            if (this.$refs.itemDocumentContentIndexModal)
                this.$refs.itemDocumentContentIndexModal.focus();
        },
        loadDocumentContentIndex() {
            this.isLoading = true;
            this.fetchItemDocumentContentIndex({
                itemId: this.itemId,
                contextEdit: true
            })
                .then((documentContentIndex) => {
                    const content = documentContentIndex || '';
                    this.localDocumentContentIndex = content;
                    this.savedDocumentContentIndex = content;
                })
                .catch(() => {
                    this.localDocumentContentIndex = '';
                    this.savedDocumentContentIndex = '';
                })
                .finally(() => {
                    this.isLoading = false;
                });
        },
        extractDocumentContentIndex() {
            this.isExtracting = true;
            this.extractItemDocumentContentIndex({
                itemId: this.itemId
            })
                .then((documentContentIndex) => {
                    this.localDocumentContentIndex = documentContentIndex || '';
                    this.$buefy.toast.open({
                        duration: 3000,
                        message: this.$i18n.get('info_document_content_index_extracted'),
                        position: 'is-bottom-right',
                        type: 'is-success',
                        queue: false
                    });
                })
                .catch((errors) => {
                    this.$buefy.snackbar.open({
                        message: errors.error_message || this.$i18n.get('error_document_content_index_extraction_failed'),
                        type: 'is-danger',
                        position: 'is-bottom-right',
                        duration: 6000,
                        queue: false
                    });
                })
                .finally(() => {
                    this.isExtracting = false;
                });
        },
        saveDocumentContentIndex() {
            if (!this.hasDocumentContentIndexChanged) {
                this.closeModal();
                return;
            }

            this.isSaving = true;
            this.updateItemDocumentContentIndex({
                itemId: this.itemId,
                documentContentIndex: this.localDocumentContentIndex
            })
                .then(() => {
                    this.closeModal();
                })
                .catch((errors) => {
                    this.$buefy.snackbar.open({
                        message: errors.error_message || this.$i18n.get('error_document_content_index_save_failed'),
                        type: 'is-danger',
                        position: 'is-bottom-right',
                        duration: 6000,
                        queue: false
                    });
                })
                .finally(() => {
                    this.isSaving = false;
                });
        }
    }
}
</script>

<style scoped>

    .tainacan-modal-title p {
        font-size: 0.875em;
    }

    .document-content-index-empty-help {
        margin-bottom: 0.75rem;

        p + p {
            margin-top: 0.5rem;
        }
    }

    .document-content-index-actions {
        display: flex;
        justify-content: flex-end;
        font-size: 0.875em;
        margin-top: 0.5rem;
    }
</style>
