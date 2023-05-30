<template>
    <div 
            aria-labelledby="alert-dialog-title"
            aria-modal
            autofocus
            role="alertdialog"
            class="tainacan-form tainacan-dialog dialog"
            ref="termDeletionDialog">
        <div    
                class="modal-card" 
                style="width: auto">
            <div class="modal-custom-icon">
                <span class="icon is-large">
                    <i 
                            style="color: var(--tainacan-red2);"
                            class="tainacan-icon tainacan-icon-alert"/>
                </span>
            </div>
            <section 
                    tabindex="1"
                    class="modal-card-body">
                <header 
                        class="modal-card-head">
                    <h1 
                            id="alert-dialog-title"
                            class="modal-card-title">
                        {{ $i18n.get('label_warning') }}
                    </h1>
                </header>
                <span v-html="message" />
                <div 
                        v-if="showDescendantsDeleteButton"
                        class="type-of-deletion-options">
                    <b-radio 
                            native-value="selected"
                            v-model="typeOfDelete">
                        {{ amountOfTerms > 1 ? $i18n.get('label_remove_selected_terms') : $i18n.get('label_remove_selected_term') }}
                    </b-radio>
                    <b-radio 
                            native-value="descendants"
                            v-model="typeOfDelete">
                        {{ amountOfTerms > 1 ? $i18n.get('label_remove_terms_and_descendants') : $i18n.get('label_remove_term_and_descendants') }}
                    </b-radio>
                </div>
            </section>
            <footer class="modal-card-foot form-submit">
                <button 
                        v-if="!hideCancel"
                        class="button is-outlined" 
                        type="button"
                        @click="$parent.close()">
                    {{ $i18n.get('cancel') }}
                </button>
                <button 
                        type="submit"
                        class="button is-success"
                        @click="onConfirm(typeOfDelete); $parent.close();">
                    {{ $i18n.get('continue') }}
                </button>
            </footer>
        </div>
    </div>
</template>

<script>

    export default {
        name: 'TermDeletionDialog',
        props: {
            title: String,
            message: String,
            showDescendantsDeleteButton: {
                type: Boolean,
                default: false
            },
            onConfirm: {
                type: Function,
                default: () => {}
            },
            hideCancel: {
                type: Boolean,
                default: false,
            },
            amountOfTerms: {
                type: Number,
                default: 1
            },
        },
        data() {
            return {
                typeOfDelete: 'selected'
            }
        },
        mounted() {
            if (this.$refs.termDeletionDialog)
                this.$refs.termDeletionDialog.focus();
        }
    }
</script>

<style scoped>
   
    i.tainacan-icon,
    i.tainacan-icon::before {
        font-size: 56px;
    }

    button.is-success {
        margin-left: auto;
    }

    .b-checkbox.checkbox {
        margin-top: 12px;
        width: auto !important;
    }

    .type-of-deletion-options {
        margin-top: 0.5rem;
        font-size: 1.25em;
        max-width: 97%;
    }

    @media screen and (max-width: 768px) {
        .modal-custom-icon {
            display: none !important;
        }
    }

</style>

