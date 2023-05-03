import { mapActions } from 'vuex';
import TermDeletionDialog from '../components/other/term-deletion-dialog.vue';

export const termsListMixin = {
    props: {
        taxonomyId: Number,
        currentUserCanEditTaxonomy: Boolean,
        selected: Array,
        selectedColumnIndex: Number
    },
    data() {
        return {
            totalRemaining: {},
            isEditingTerm: false,
            editTerm: null
        }
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
            'changeTermsParent'
        ]),
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
        }
    }
};
