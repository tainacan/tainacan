<template>
    <div 
            aria-labelledby="alert-dialog-title"
            aria-modal
            autofocus
            role="alertdialog"
            class="tainacan-form tainacan-dialog dialog"
            ref="termParentSelectionDialog">
        <div    
                class="modal-card" 
                style="width: auto">

            <div class="modal-custom-icon">
                <span class="icon is-large">
                    <i 
                            style="color: var(--tainacan-blue5);"
                            class="tainacan-icon tainacan-icon-taxonomies"/>
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
                        {{ $i18n.get('label_update_parent') }}
                    </h1>
                </header>
                
                <!-- Parent -------------- -->
                <div class="parent-term-options">
                    <b-radio 
                            :native-value="false"
                            v-model="hasParent">
                        {{ $i18n.get('label_no_parent_root_term') }}
                    </b-radio>
                    <b-radio 
                            :native-value="true"
                            v-model="hasParent">
                        {{ $i18n.get('instruction_select_a_parent_term') }}
                    </b-radio>
                    <b-autocomplete
                            id="tainacan-add-parent-field"
                            :placeholder="$i18n.get('instruction_parent_term')"
                            :data="parentTerms"
                            field="name"
                            clearable
                            v-model="parentTermName"
                            @select="onSelectParentTerm($event)"
                            :loading="isFetchingParentTerms"
                            @input="fetchParentTerms"
                            :disabled="!hasParent"
                            check-infinite-scroll
                            :append-to-body="true"
                            @infinite-scroll="fetchMoreParentTerms">
                        <template slot-scope="props">
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
                        <template slot="empty">{{ $i18n.get('info_no_parent_term_found') }}</template>
                    </b-autocomplete>
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
                        :disabled="hasParent ? !selectedParentTerm : false"
                        @click="onConfirm(hasParent ? selectedParentTerm : 0); $parent.close();">
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
            amountOfTerms: {
                type: Number,
                default: 1
            },
            taxonomyId: '',
            excludeTree: ''
        },
        data() {
            return {
                hasParent: false,
                parentTerms: [],
                isFetchingParentTerms: false,
                parentTermSearchQuery: '',
                parentTermSearchOffset: 0,
                selectedParentTerm: undefined,
                parentTermName: '',
                totalTerms: undefined
            }
        },
        mounted() {
            if (this.$refs.termParentSelectionDialog)
                this.$refs.termParentSelectionDialog.focus();
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
   
    i.tainacan-icon,
    i.tainacan-icon::before {
        font-size: 40px;
    }

    button.is-success {
        margin-left: auto;
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

