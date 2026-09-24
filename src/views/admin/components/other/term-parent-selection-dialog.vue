<template>
    <div 
            ref="termParentSelectionDialog"
            aria-labelledby="alert-dialog-title"
            aria-modal
            autofocus
            role="alertdialog"
            class="tainacan-form tainacan-dialog dialog tainacan-repository-level-colors">
        <div    
                class="modal-card" 
                style="width: auto">

            <div class="modal-custom-icon">
                <span 
                        aria-hidden="true"
                        class="icon is-large">
                    <i 
                            style="color: var(--tainacan-blue5);"
                            class="tainacan-icon tainacan-icon-taxonomies" />
                </span>
            </div>
            <section class="modal-card-body">
                <header 
                        class="modal-card-head">
                    <h1 
                            id="alert-dialog-title"
                            class="modal-card-title">
                        {{ $i18n.get('label_update_parent') }}
                    </h1>
                </header>
                
                <!-- Parent -------------- -->
                <div class="parent-term-options">
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
                            id="tainacan-add-parent-field"
                            v-model="parentTermName"
                            v-a11y-autocomplete="{ appendToBody: true }"
                            :placeholder="$i18n.get('instruction_parent_term')"
                            :data="parentTerms"
                            field="name"
                            clearable
                            icon-right="menu-down"
                            :loading="isFetchingParentTerms"
                            :disabled="!hasParent"
                            check-infinite-scroll
                            :append-to-body="true"
                            open-on-focus
                            @select="onSelectParentTerm"
                            @focus="browseParentTerms"
                            @active="onParentTermSuggestionsActive"
                            @typing="fetchParentTerms"
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
                        <template #empty>
                            {{ $i18n.get('info_no_parent_term_found') }}
                        </template>
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
                        @click="onConfirm(hasParent ? selectedParentTerm : 0); $emit('close');">
                    {{ $i18n.get('continue') }}
                </button>
            </footer>
        </div>
    </div>
</template>

<script>
    import { mapActions } from 'vuex'

    export default {
        name: 'TermParentSelectionDialog',
        props: {
            title: String,
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
            taxonomyId: '',
            excludeTree: ''
        },
        emits: [
            'close',
            'beforeClose'
        ],
        data() {
            return {
                hasParent: false,
                parentTerms: [],
                isFetchingParentTerms: false,
                parentTermSearchQuery: '',
                parentTermSearchOffset: 0,
                selectedParentTerm: undefined,
                parentTermName: '',
                committedParentTermName: '',
                totalTerms: undefined
            }
        },
        watch: {
            parentTermName(name) {
                if (!name)
                    this.onSelectParentTerm(null);
            }
        },
        mounted() {
            if (this.$refs.termParentSelectionDialog)
                this.$refs.termParentSelectionDialog.focus();
        },
        beforeUnmount() {
            this.$emit('beforeClose');
        },
        methods: {
            ...mapActions('taxonomy', [
                'fetchPossibleParentTerms'
            ]),
            browseParentTerms() {
                this.parentTermSearchQuery = '';
                this.parentTermSearchOffset = 0;
                this.totalTerms = undefined;
                this.parentTerms = [];
                this.isFetchingParentTerms = true;
                this.requestParentTerms('');
            },
            onParentTermSuggestionsActive(isOpen) {
                if (isOpen)
                    return;

                if (this.selectedParentTerm && this.committedParentTermName && this.parentTermName !== this.committedParentTermName)
                    this.parentTermName = this.committedParentTermName;
            },
            fetchParentTerms: _.debounce(function(search) {
                const query = search || '';

                if (this.committedParentTermName && query === this.committedParentTermName)
                    return;

                if (query !== this.parentTermSearchQuery) {
                    this.parentTermSearchQuery = query;
                    this.parentTerms = [];
                    this.parentTermSearchOffset = 0;
                    this.totalTerms = undefined;
                }

                if (this.parentTermSearchOffset > 0 && this.totalTerms !== undefined && this.parentTerms.length >= this.totalTerms)
                    return;

                this.isFetchingParentTerms = true;
                this.requestParentTerms(query);
            }, 500),
            requestParentTerms(query) {
                this.fetchPossibleParentTerms({
                        taxonomyId: this.taxonomyId,
                        termId: this.excludeTree,
                        search: query,
                        offset: this.parentTermSearchOffset })
                    .then((res) => {
                        const terms = res.parentTerms ? res.parentTerms : [];
                        if (this.parentTermSearchOffset === 0)
                            this.parentTerms = terms;
                        else {
                            for (let term of terms)
                                this.parentTerms.push(term);
                        }

                        this.parentTermSearchOffset += 12;
                        this.totalTerms = res.totalTerms;
                        this.isFetchingParentTerms = false;
                    })
                    .catch((error) => {
                        this.$console.error(error);
                        this.isFetchingParentTerms = false;
                    });
            },
            fetchMoreParentTerms: _.debounce(function () {
                this.fetchParentTerms(this.parentTermSearchQuery)
            }, 250),
            onSelectParentTerm(selectedParentTerm) {
                if (!selectedParentTerm) {
                    if (this.parentTermName || (this.selectedParentTerm == undefined && !this.committedParentTermName))
                        return;

                    this.selectedParentTerm = undefined;
                    this.committedParentTermName = '';
                    return;
                }

                this.selectedParentTerm = selectedParentTerm.id;
                this.committedParentTermName = selectedParentTerm.name;
                this.parentTermName = selectedParentTerm.name;
            }
        }
    }
</script>

<style scoped>
   
    i.tainacan-icon,
    i.tainacan-icon::before {
        font-size: 40px;
    }

    button.is-success {
        margin-inline-start: auto;
    }

    .b-checkbox.checkbox {
        margin-top: 12px;
        width: auto !important;
    }

    .parent-term-options {
        margin-top: 0.5rem;
        max-width: 97%;
    }
    .parent-term-options .b-radio {
        font-size: 1.125em;
    }

    @media screen and (max-width: 768px) {
        .modal-custom-icon {
            display: none !important;
        }
    }

</style>

