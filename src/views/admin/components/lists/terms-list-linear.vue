<template>
    <div class="tainacan-checkbox-list-container">
        <a
                v-if="searchResultsOffset"
                role="button"
                class="tainacan-checkbox-list-page-changer"
                @click="previousSearchPage">
            <span class="icon">
                <i class="tainacan-icon tainacan-icon-previous"/>
            </span>
        </a>
        <ul class="tainacan-modal-checkbox-list-body">
            <template v-if="searchResults.length">
                <li
                        class="tainacan-li-checkbox-list"
                        v-for="(term, index) in searchResults"
                        :key="index">
                    <label class="b-checkbox checkbox">
                        <input
                                @input="updateSelectedTerms(term)"
                                :checked="isTermSelected(term.id)"
                                :value="getTermIdAsNumber(term.id)"
                                type="checkbox"> 
                        <span class="check" /> 
                        <span class="control-label">
                            <span 
                                    class="checkbox-name-text"
                                    v-html="renderTermHierarchyLabel(term)" /> 
                        </span>
                    </label>
                    <button 
                            v-if="currentUserCanEditTaxonomy"
                            type="button"
                            @click.prevent="onEditTerm(term)">
                        <span
                                v-tooltip="{
                                    content: $i18n.get('edit'),
                                    autoHide: true,
                                    popperClass: ['tainacan-tooltip', 'tooltip', 'tainacan-repository-tooltip'],
                                    placement: 'bottom'
                                }"
                                class="icon">
                            <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-edit"/>
                        </span>
                    </button>
                    <button
                            v-if="currentUserCanEditTaxonomy"
                            type="button"
                            @click.prevent="removeTerm(term)">
                        <span
                                v-tooltip="{
                                    content: $i18n.get('delete'),
                                    autoHide: true,
                                    popperClass: ['tainacan-tooltip', 'tooltip', 'tainacan-repository-tooltip'],
                                    placement: 'bottom'
                                }"
                                class="icon">
                            <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-delete"/>
                        </span>
                    </button>                             
                </li>
            </template>
            <template v-if="!isLoadingSearch && !searchResults.length">
                <li class="tainacan-li-checkbox-list result-info">
                    {{ $i18n.get('info_no_terms_found') }}
                </li>
            </template>
            <b-loading
                    :is-full-page="false"
                    :active.sync="isLoadingSearch"/>
        </ul>
        <button
                v-if="hasMoreSearchPages"
                type="button"
                class="tainacan-checkbox-list-page-changer"
                @click="nextSearchPage">
            <span class="icon">
                <i class="tainacan-icon tainacan-icon-next"/>
            </span>
        </button>

        <b-modal
                v-model="isEditingTerm"
                :width="768"
                trap-focus
                aria-role="dialog"
                aria-modal
                :can-cancel="['outside', 'escape']"
                custom-class="tainacan-modal"
                :close-button-aria-label="$i18n.get('close')">
            <term-edition-form 
                    :taxonomy-id="taxonomyId"
                    :is-modal="true"
                    @onEditionFinished="onTermEditionFinished($event.term, $event.hasChangedParent, $event.initialParent)"
                    :original-form="editTerm" />
        </b-modal>

    </div>
</template>

<script>
    import { tainacan as axios } from '../../js/axios';
    import { mapActions } from 'vuex';
    import TermDeletionDialog from '../other/term-deletion-dialog.vue';
    import TermParentSelectionDialog from '../other/term-parent-selection-dialog.vue';

    export default {
        name: 'TermsListLinear',
        props: {
            taxonomyId: Number,
            currentUserCanEditTaxonomy: Boolean,
            selected: Array,
            searchString: String
        },
        data() {
            return {
                totalRemaining: {},
                searchResults: [],
                maxSearchResultsPerPage: 20,
                searchResultsOffset: 0,
                isLoadingSearch: false,
                hasMoreSearchPages: true,
                isEditingTerm: false,
                editTerm: null,
            }
        },
        watch: {
            searchString: {
                handler(newValue, oldValue) {
                    if (newValue != oldValue) {
                        this.hasMoreSearchPages = true;
                        this.searchResultsOffset = 0;

                        this.autoComplete();
                    }
                },
                immediate: true
            },
        },
        created() {
            this.$parent.$on('deleteSelectedTerms', this.deleteSelectedTerms);
            this.$parent.$on('updateSelectedTermsParent', this.updateSelectedTermsParent);
        },
        beforeDestroy() {
            this.$parent.$off('deleteSelectedTerms', this.deleteSelectedTerms);
            this.$parent.$off('updateSelectedTermsParent', this.updateSelectedTermsParent);
        },
        methods: {
            ...mapActions('taxonomy', [
                'updateTerm',
                'deleteTerm',
                'deleteTerms',
            ]),
            shouldShowMoreButton(columnIndex) {
                return this.totalRemaining[columnIndex].remaining === true || (this.termColumns[columnIndex].children.length < this.totalRemaining[columnIndex].remaining);
            },
            previousSearchPage() {

                this.hasMoreSearchPages = true;

                this.searchResultsOffset -= this.maxSearchResultsPerPage;
                if ( this.searchResultsOffset < 0 )
                    this.searchResultsOffset = 0;

                this.autoComplete();
                
            },
            nextSearchPage() {

                if ( this.hasMoreSearchPages ) {
                    if ( this.searchResultsOffset === this.maxSearchResultsPerPage )
                        this.searchResultsOffset += this.maxSearchResultsPerPage - 1;
                    else
                        this.searchResultsOffset += this.maxSearchResultsPerPage;
                }
                
                this.autoComplete();
            },
            autoComplete: _.debounce( function () {
                
                if (!this.searchString.length)
                    return;

                this.isLoadingSearch = true;

                const query = `?order=asc&number=${this.maxSearchResultsPerPage}&searchterm=${this.searchString}&hideempty=0&offset=${this.searchResultsOffset}`;

                const route = `/taxonomy/${this.taxonomyId}/terms/${query}`;
                axios.get(route)
                    .then((res) => {
                        this.searchResults = res.data;
                        this.isLoadingSearch = false;
                        
                        if (res.headers && res.headers['x-wp-total'])
                            this.hasMoreSearchPages = res.headers['x-wp-total'] > (this.searchResultsOffset + this.searchResults.length);

                    })
                    .catch((error) => {
                        this.$console.log(error);
                    });
                
            }, 500),
            renderTermHierarchyLabel(term) {
                if ( term.hierarchy_path ) 
                    return '<span style="color: var(--tainacan-info-color);">' + term.hierarchy_path.replace(/>/g, '&nbsp;<span class="hierarchy-separator"> &gt; </span>&nbsp;') + '</span>' + term.name;

                return term.name;
            }, 
            isTermSelected(termId) {
                return this.selected.findIndex(aSelectedTerm => aSelectedTerm.id == termId) >= 0;
            },
            getTermIdAsNumber(termId) {
                return isNaN(Number(termId)) ? termId : Number(termId)
            },
            updateSelectedTerms(selectedTerm) {

                let currentSelected = JSON.parse(JSON.stringify(this.selected));
                
                const existingValueIndex = this.selected.findIndex(aSelectedTerm => aSelectedTerm.id == selectedTerm.id);
                
                if (existingValueIndex >= 0)
                    currentSelected.splice(existingValueIndex, 1);
                else
                    currentSelected.push(selectedTerm);

                this.$emit('onUpdateSelectedTerms', currentSelected);
            },
            onEditTerm(term) {
                this.editTerm = term;
                this.isEditingTerm = true;
            },
            removeTerm(term) {

                this.$buefy.modal.open({
                    parent: this,
                    component: TermDeletionDialog,
                    props: {
                        message: term.total_children && term.total_children != '0' ?  this.$i18n.get('info_warning_term_with_child') : this.$i18n.get('info_warning_selected_term_delete'),
                        showDescendantsDeleteButton: term.total_children && term.total_children != '0',
                        amountOfTerms: 1,
                        onConfirm: (typeOfDelete) => { 
                            
                            // If all checks passed, term can be deleted  
                            if ( typeOfDelete == 'descendants' ) { 
                                this.deleteTerm({
                                        taxonomyId: this.taxonomyId, 
                                        termId: term.id, 
                                        parent: term.parent,
                                        deleteChildTerms: true })
                                    .then(() => {
                                        this.onTermRemovalFinished(term);
                                    })
                                    .catch((error) => {
                                        this.$console.log(error);
                                    });
                            } else { 
                                this.deleteTerm({
                                        taxonomyId: this.taxonomyId, 
                                        termId: term.id, 
                                        parent: term.parent })
                                    .then(() => {
                                        this.onTermRemovalFinished(term);
                                    })
                                    .catch((error) => {
                                        this.$console.log(error);
                                    });
                            }
                        }
                    },
                    trapFocus: true,
                    customClass: 'tainacan-modal',
                    closeButtonAriaLabel: this.$i18n.get('close')
                });  
            },
            onTermRemovalFinished(term) {
                const removedTermIndex = this.searchResults.findIndex((aTerm) => aTerm.id == term.id);

                if ( removedTermIndex >= 0 )
                    this.searchResults.splice(removedTermIndex, 1);
            },
            onTermEditionFinished(term) {
                const updatedTermIndex = this.searchResults.findIndex((aTerm) => aTerm.id == term.id);

                if ( updatedTermIndex >= 0 )
                    this.searchResults.splice(updatedTermIndex, 1, term);
            },
            deleteSelectedTerms() {

                this.$buefy.modal.open({
                    parent: this,
                    component: TermDeletionDialog,
                    props: {
                        message: this.$i18n.get('info_warning_some_terms_with_child'),
                        showDescendantsDeleteButton: true,
                        amountOfTerms: this.amountOfTermsSelected,
                        onConfirm: (typeOfDelete) => { 
                            // If all checks passed, term can be deleted 
                            this.deleteTerms({
                                    taxonomyId: this.taxonomyId, 
                                    terms: this.selected.map((aTerm) => aTerm.id),
                                    deleteChildTerms: typeOfDelete === 'descendants'
                                })
                                .then(() => {
                                    this.resetTermsListUI();
                                })
                                .catch((error) => {
                                    this.$console.log(error);
                                });
                        }
                    },
                    trapFocus: true,
                    customClass: 'tainacan-modal',
                    closeButtonAriaLabel: this.$i18n.get('close')
                });  
            },
            updateSelectedTermsParent() {

                this.$buefy.modal.open({
                    parent: this,
                    component: TermParentSelectionDialog,
                    props: {
                        amountOfTerms: this.amountOfTermsSelected,
                        excludeTree: this.selected.map((aTerm) => aTerm.id), 
                        taxonomyId: this.taxonomyId,
                        onConfirm: (selectedParentTerm) => { 
                            console.log(selectedParentTerm);
                            // If all checks passed, term can be deleted   
                            // this.deleteTerm({
                            //         taxonomyId: this.taxonomyId, 
                            //         termId: term.id, 
                            //         parent: term.parent })
                            //     .then(() => {
                            //         this.onTermRemovalFinished(term);
                            //     })
                            //     .catch((error) => {
                            //         this.$console.log(error);
                            //     });
                            this.resetTermsListUI();  
                        }
                    },
                    trapFocus: true,
                    customClass: 'tainacan-modal',
                    closeButtonAriaLabel: this.$i18n.get('close')
                });  
            },
            resetTermsListUI() {
                this.$emit('onUpdateSelectedTerms', []);
                this.autoComplete();
            }
        }
    }
</script>

<style lang="scss" scoped>

    .tainacan-li-checkbox-list {
        flex-grow: 0;
        flex-shrink: 1;
        max-width: 100%;
        padding-left: 0.5em;
        margin: 0;
        -webkit-break-inside: avoid;
        break-inside: avoid;
        display: flex;
        justify-content: space-between;
        align-items: stretch;
        min-height: 1.5em;

        /deep/ .b-checkbox, /deep/ .b-radio {
            margin-right: 0px;
            margin-bottom: 0;
            -webkit-break-inside: avoid;
            break-inside: avoid;

            .control-label {
                white-space: normal;
                overflow: visible;
                padding-top: 0.125em;
                padding-bottom: 0.125em;
                word-break: break-word;
            }
            input:disabled+.check {
                cursor: not-allowed;
                opacity: 0.5;
            }
            @media screen and (max-width: 768px) {
                .control-label {
                    padding-top: 0.8125em;
                    padding-bottom: 0.8125em;
                    padding-left: calc(0.875em - 1px);
                    width: 100%;
                    border-bottom: 1px solid var(--tainacan-gray1);
                }
            }
        }

        &>a:not(.add-link),
        &>button:not(.add-link) {
            opacity: 0.0;
            transition: opacity 0.2s ease;       
        }
        button.load-children-button {
            opacity: 0.95;
            border-left: 1px dashed var(--tainacan-gray1);
        }
        button .tainacan-icon {
            color: var(--tainacan-blue5);
        }
        &:hover:not(.result-info) {
            background-color: var(--tainacan-gray1);

            &>a:not(.add-link),
            &>button:not(.add-link) {
                opacity: 1.0;
                background-color: var(--tainacan-gray2);
            }
        }
        &.result-info {
            padding: 0.5rem 0.25rem 0.25rem 0.25rem;
            width: 100%;
            max-width: 100%;
            column-span: all;
            font-size: 0.75em;
            color: var(--tainacan-info-color);
            text-align: center;
        }
    }

    ul {
        // For Safari
        -webkit-margin-after: 0;
        -webkit-margin-start: 0;
        -webkit-margin-end: 0;
        -webkit-padding-start: 0;
        -webkit-margin-before: 0;
    }

    .field:not(:last-child) {
        margin-bottom: 0 !important;
    }

    .tainacan-checkbox-list-container {
        border: 1px solid var(--tainacan-gray2);
        min-height: 232px;
        display: flex;
        align-items: center;
        position: relative;
        padding: 0 20px !important;

        &>ul+.tainacan-checkbox-list-page-changer {
            right: 0;
            left: auto;
        }

        a:not(.add-link),
        button:not(.add-link) {
            border: none;
            background: transparent;
            font-size: 0.75em;
            white-space: nowrap;
            display: flex;
            align-items: center;
            padding: 8px 0.5rem;

            .tainacan-icon {
                font-size: 1.5em;
            }
        }
    }

    .tainacan-checkbox-list-page-changer {
        height: calc(100% - 1px);
        top: 1px;
        position: absolute;
        left: 0;
        right: auto;
        align-items: center;
        display: flex;
        padding: 0 !important;
        background-color: var(--tainacan-gray1) !important;

        &:hover {
            background-color: var(--tainacan-blue1) !important;
        }
    }

    .tainacan-modal-checkbox-list-body {
        -moz-column-count: 2;
        -moz-column-gap: 0;
        -moz-column-rule: none;
        -webkit-column-count: 2;
        -webkit-column-gap: 0;
        -webkit-column-rule: none;
        column-count: 2;
        column-gap: 2em;
        column-rule: none;
        column-fill: auto;
        list-style: none;
        width: 100%;
        margin: 0;
        padding: 0 !important;
        max-height: 42vh;
        overflow: auto;
    }

    .tainacan-modal-checkbox-search-results-body {
        list-style: none;
        -moz-column-count: 2;
        -moz-column-gap: 0;
        -moz-column-rule: none;
        -webkit-column-count: 2;
        -webkit-column-gap: 0;
        -webkit-column-rule: none;
        column-count: 2;
        column-gap: 2em;
        column-rule: none;
    }

    .b-checkbox .control-label {
        display: flex;
        flex-wrap: nowrap;
        align-items: center;
        width: 100%;
        overflow: visible !important;
        white-space: normal !important;

        .checkbox-name-text {
            line-height: 1.25em;
            padding-right: 3px;
            break-inside: avoid;
        }
    }

    @media screen and (max-width: 768px) {

        .tainacan-modal-checkbox-list-body,
        .tainacan-modal-checkbox-search-results-body {
            -moz-column-count: auto;
            -webkit-column-count: auto;
            column-count: auto;
            overflow-y: auto;
        }
        .tainacan-modal-checkbox-search-results-body,
        .tainacan-modal-checkbox-list-body {
            font-size: 1.125em;
        }
        .tainacan-li-checkbox-list {
            max-width: calc(100% - 20px) !important;
        }
    }

</style>


 
