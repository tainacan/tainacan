<template>
    <div 
            ref="termMultipleInsertionDialog"
            aria-labelledby="alert-dialog-title"
            aria-modal
            autofocus
            role="alertdialog"
            class="tainacan-form tainacan-dialog dialog">
        <div    
                class="modal-card" 
                style="width: auto">

            <div class="modal-custom-icon">
                <span class="icon is-large">
                    <i 
                            style="color: var(--tainacan-blue5);"
                            class="tainacan-icon tainacan-icon-taxonomies" />
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
                        {{ $i18n.get('label_multiple_terms_insertion') }}
                    </h1>
                </header>

                <b-field :addons="false">
                        <label
                                class="label"
                                style="font-size: 1em;">
                            {{ $i18n.get('instruction_multiple_terms_insertion') }}
                        </label>

                    <b-taginput
                            v-model="termNames"
                            attached
                            :confirm-keys="termNamesSeparator"
                            :on-paste-separators="termNamesSeparator"
                            :remove-on-keys="[]"
                            :aria-close-label="$i18n.get('remove_value')"
                            class="tainacan-multiple-term-insertion--taginput"
                            :class="{'has-selected': termNames != undefined && termNames != []}"
                            :placeholder="$i18n.get('term') + '1,' + $i18n.get('term') + '2,' + $i18n.get('term') + '3 ...'" />
                    <div class="separator-term-names">
                        <label class="label is-inline">{{ $i18n.get('label_separator') }}:</label>
                        <b-checkbox
                                v-for="separator of ['Enter', ',', ';', '|']"
                                :key="separator"
                                v-model="termNamesSeparator"
                                name="term-multiple-insertion-separator"
                                :native-value="separator"
                                :disabled="separator == 'Enter'">
                            <kbd>{{ separator }}</kbd>
                        </b-checkbox>
                    </div>
                </b-field>
                
                <!-- Parent -------------- -->
                <div 
                        v-if="isHierarchical"
                        class="parent-term-options">
                    <b-radio 
                            v-model="hasParent"
                            :native-value="false">
                        {{ $i18n.get('label_no_parent_root_term') }}
                    </b-radio>
                    <b-radio 
                            v-model="hasParent"
                            :native-value="true">
                        {{ $i18n.get('instruction_select_a_parent_term') }}
                    </b-radio>
                    <b-autocomplete
                            v-if="hasParent"
                            id="tainacan-add-parent-field"
                            v-model="parentTermName"
                            :placeholder="$i18n.get('instruction_parent_term')"
                            :data="parentTerms"
                            field="name"
                            clearable
                            :loading="isFetchingParentTerms"
                            check-infinite-scroll
                            :append-to-body="true"
                            @select="onSelectParentTerm($event)"
                            @update:model-value="fetchParentTerms"
                            @infinite-scroll="fetchMoreParentTerms">
                        <template #default="props">
                            <div class="media">
                                <div 
                                        v-if="props.option.header_image_id"
                                        class="media-left">
                                    <img 
                                            width="28"
                                            :src="props.option.thumbnail && props.option.thumbnail['thumbnail'] && props.option.thumbnail['thumbnail'][0] ? props.option.thumbnail['thumbnail'][0] : props.option.header_image">
                                </div>
                                <div class="media-content">
                                    {{ props.option.name }}
                                </div>
                            </div>
                        </template>
                        <template #empty>{{ $i18n.get('info_no_parent_term_found') }}</template>
                    </b-autocomplete>
                </div>
            </section>
            <footer class="modal-card-foot form-submit">
                <button 
                        v-if="!hideCancel"
                        class="button is-outlined" 
                        type="button"
                        @click="$emit('close')">
                    {{ $i18n.get('cancel') }}
                </button>
                <button 
                        type="submit"
                        class="button is-success"
                        :disabled="hasParent ? !selectedParentTerm : false"
                        @click="onConfirm({ parent: hasParent ? selectedParentTerm : 0, termNames: termNames }); $emit('close');">
                    {{ $i18n.get('continue') }}
                </button>
            </footer>
        </div>
    </div>
</template>

<script>
    import { mapActions } from 'vuex'

    export default {
        name: 'TermMultipleInsertionDialog',
        props: {
            title: String,
            onConfirm: {
                type: Function,
                default: () => {}
            },
            taxonomyId: '',
            excludeTree: '',
            isHierarchical: Boolean,
            initialTermParent: String,
            initialTermParentName: String
        },
        data() {
            return {
                hasParent: false,
                termNames: [],
                termNamesSeparator: [",", "Enter"],
                parentTerms: [],
                isFetchingParentTerms: false,
                parentTermSearchQuery: '',
                parentTermSearchOffset: 0,
                selectedParentTerm: undefined,
                parentTermName: '',
                totalTerms: undefined
            }
        },
        created() {
            if ( this.initialTermParent && this.initialTermParentName ) {
                this.parentTermName = this.initialTermParentName;
                this.selectedParentTerm = this.initialTermParent;
                this.hasParent = true;
            }
        },
        mounted() {
            if (this.$refs.termMultipleInsertionDialog)
                this.$refs.termMultipleInsertionDialog.focus();
        },
        methods: {
            ...mapActions('taxonomy', [
                'fetchPossibleParentTerms'
            ]),
            fetchParentTerms: _.debounce(function(search) {

                // String update
                if (search != this.parentTermSearchQuery) {
                    this.parentTermSearchQuery = search;
                    this.parentTerms = [];
                    this.parentTermSearchOffset = 0;
                } 
                
                // String cleared
                if (!search.length) {
                    this.parentTermSearchQuery = search;
                    this.parentTerms = [];
                    this.parentTermSearchOffset = 0;
                }

                // No need to load more
                if (this.parentTermSearchOffset > 0 && this.totalTerms !== undefined && this.parentTerms.length >= this.totalTerms)
                    return

                this.isFetchingParentTerms = true;
                
                this.fetchPossibleParentTerms({
                        taxonomyId: this.taxonomyId, 
                        termId: this.excludeTree, 
                        search: this.parentTermSearchQuery,
                        offset: this.parentTermSearchOffset })
                    .then((res) => {
                        for (let term of res.parentTerms)
                            this.parentTerms.push(term);

                        this.parentTermSearchOffset += 12;
                        this.totalTerms = res.totalTerms;
                        this.isFetchingParentTerms = false;
                    })
                    .catch((error) => {
                        this.$console.error(error);
                        this.isFetchingParentTerms = false;
                    });
            }, 500),
            fetchMoreParentTerms: _.debounce(function () {
                this.fetchParentTerms(this.parentTermSearchQuery)
            }, 250),
            onSelectParentTerm(selectedParentTerm) {
                if ( selectedParentTerm ) {
                    this.selectedParentTerm = selectedParentTerm.id;
                    this.parentTermName = selectedParentTerm.name;
                } else {
                    this.selectedParentTerm = undefined;
                    this.parentTermName = '';
                }
            }
        }
    }
</script>

<style scoped>
    .dialog .modal-card {
        max-width: 980px;
    }
    i.tainacan-icon,
    i.tainacan-icon::before {
        font-size: 40px;
    }

    button.is-success {
        margin-left: auto;
    }

    .b-checkbox.checkbox {
        margin-top: 8px;
        width: auto !important;
    }

    .parent-term-options {
        margin-top: 0.5rem;
        max-width: 97%;
    }
    .parent-term-options .b-radio {
        font-size: 1.125em;
    }

    .separator-term-names {
        display: flex;
        flex-wrap: wrap;
        padding: 4px 10px;
        background: #f9f9f9;
        border: 1px solid var(--tainacan-gray1, #f2f2f2);
        border-bottom-right-radius: 2px;
        border-bottom-left-radius: 2px;
        font-size: 1.25em;
    }
    .separator-term-names .b-checkbox {
        width: auto;
        margin-right: 0.75em;
    }
    .separator-term-names > label {
        opacity: 0.875;
        font-size: 0.75em;
        margin-right: 1em;
        display: block;
        margin-bottom: 0;
    }
    .separator-term-names > label.is-inline {
        margin-top: 2px;
    }

    .tainacan-multiple-term-insertion--taginput :deep(.tag),
    .tainacan-multiple-term-insertion--taginput :deep(.tags) {
        white-space: normal !important;
        min-height: calc(2em - 1px) !important;
        height: auto !important;
    }
    .tainacan-multiple-term-insertion--taginput :deep(.tag.is-delete) {
        min-width: calc(2em - 1px) !important;
    }

    @media screen and (max-width: 768px) {
        .modal-custom-icon {
            display: none !important;
        }
    }

</style>

