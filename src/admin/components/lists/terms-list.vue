<template>
    <div class="columns">
        <div class="column">
            <button
                    class="button is-secondary"
                    type="button"
                    @click="addNewTerm()">
                {{ $i18n.get('label_new_term') }}
            </button>
            <b-field class="order-area">
                <button
                        :disabled="orderedTermsList.length <= 0 || isLoadingTerms || isEditingTerm"
                        class="button is-white is-small"
                        @click="onChangeOrder()">
                    <b-icon 
                            class="gray-icon"
                            :icon="order === 'ASC' ? 'sort-ascending' : 'sort-descending'"/>
                </button>
            </b-field>

            <br>
            <br>
            <div    
                    class="term-item"
                    :class="{
                        'not-sortable-item': term.opened || !term.saved, 
                        'not-focusable-item': term.opened
                    }" 
                    :style="{'margin-left': (term.depth * 40) + 'px'}"
                    v-for="(term, index) in orderedTermsList"
                    :key="term.id"> 
                <a
                        class="is-medium"
                        type="button"
                        @click="addNewChildTerm(term, index)"
                        :disabled="isEditingTerm">
                    <b-icon icon="plus-circle"/>
                </a>
                <span 
                        class="term-name" 
                        :class="{'is-danger': formWithErrors == term.id }">
                    {{ term.saved && !term.opened ? term.name : getUnsavedTermName(term) }}
                </span>
                <span   
                        v-if="term.id != undefined"
                        class="label-details">
                    <span 
                            class="not-saved" 
                            v-if="!term.saved"> 
                        {{ $i18n.get('info_not_saved') }}
                    </span>
                </span>
            
                <span class="controls" >
                <!--
                    <button
                            class="button is-success is-small"
                            type="button"
                            :href="taxonomyPath + '/' + term.slug">
                        {{ $i18n.get('label_view_term') }}
                    </button>
                -->

                    <a @click.prevent="editTerm(term, index)">
                        <b-icon 
                                type="is-secondary" 
                                icon="pencil"/>
                    </a>
                    <a 
                        @click.prevent="tryToRemoveTerm(term)">
                        <b-icon 
                                type="is-secondary" 
                                icon="delete"/>
                    </a>
                </span>
                
            </div>
        </div>
        <div 
                class="column" 
                :key="index"
                v-for="(term, index) in orderedTermsList"
                v-show="term.opened">
            <term-edition-form 
                    :taxonomy-id="taxonomyId"
                    @onEditionFinished="onTermEditionFinished()"
                    @onEditionCanceled="onTermEditionCanceled(term)"
                    @onErrorFound="formWithErrors = term.id"
                    :edit-form="term"/>


        </div>
        <b-loading 
                :active.sync="isLoadingTerms" 
                :can-cancel="false"/>
    </div>
</template>

<script>
import { mapActions, mapGetters } from 'vuex';
import TermEditionForm from '../edition/term-edition-form.vue';
import CustomDialog from '../other/custom-dialog.vue';

export default {
    name: 'TermsList',
    data(){
        return {
            isLoadingTerms: false,
            isEditingTerm: false,
            formWithErrors: '',
            orderedTermsList: [],
            order: 'asc'
        }
    },
    props: {
        taxonomyId: String,
    },
    computed: {
        termsList() {
            return this.getTerms();
        }
    },
    watch: {
        termsList() {
            this.generateOrderedTerms();
        },
        taxonomyId() {
            this.loadTerms();
        }
    },
    components: {
        TermEditionForm
    },
    methods: {
        ...mapActions('taxonomy', [
            'updateTerm',
            'deleteTerm',
            'fetchTerms'
        ]),
        ...mapGetters('taxonomy',[
            'getTerms'
        ]),
        onChangeOrder() {
            this.order == 'asc' ? this.order = 'desc' : this.order = 'asc';
            this.loadTerms();
        },
        addNewTerm() {
            if (this.isEditingTerm) {
                let editingTermIndex = this.orderedTermsList.findIndex(anEditingTerm => anEditingTerm.opened == true);
                if (editingTermIndex >= 0)
                    this.onTermEditionCanceled(this.orderedTermsList[editingTermIndex]);
            }

            let newTerm = {
                taxonomyId: this.taxonomyId,
                name: this.$i18n.get('label_term_without_name'),
                description: '',
                parent: 0,
                id: 'new',
                saved: false,
                depth: 0
            }
            this.orderedTermsList.push(newTerm);
            this.editTerm(newTerm, this.orderedTermsList.length - 1);
        },
        addNewChildTerm(parent, parentIndex) {
            if (this.isEditingTerm) {
                let editingTermIndex = this.orderedTermsList.findIndex(anEditingTerm => anEditingTerm.opened == true);
                if (editingTermIndex >= 0)
                    this.onTermEditionCanceled(this.orderedTermsList[editingTermIndex]);
            }

            let newTerm = {
                taxonomyId: this.taxonomyId,
                name:  this.$i18n.get('label_term_without_name'),
                description: '',
                parent: parent.id,
                id: 'new',
                saved: false,
                depth: parent.depth + 1
            }
            this.orderedTermsList.splice(parentIndex + 1, 0, newTerm);
            this.editTerm(newTerm, parentIndex + 1);
        },
        editTerm(term, index) {
            this.isEditingTerm = true;
            if (!term.opened) {

                for (let i = 0; i < this.orderedTermsList.length; i++) {
                    // Checks if other terms are opened
                    if (term.id != this.orderedTermsList[i].id && this.orderedTermsList[i].opened) {
                        let otherTerm = this.orderedTermsList[i];
                    
                        // Checks there's an original version of term (wasn't saved)
                        let originalIndex = this.termsList.findIndex(anOriginalTerm => anOriginalTerm.id === otherTerm.id);
                        if (originalIndex >= 0) {
                            let originalTerm = JSON.parse(JSON.stringify(this.termsList[originalIndex]));
                            originalTerm.saved = otherTerm.saved;
                            originalTerm.opened = otherTerm.opened;
                            if (JSON.stringify(otherTerm) != JSON.stringify(originalTerm)) {
                                otherTerm.saved = false;
                            } else {
                                otherTerm.saved = true;
                            }
                        // A new term is being closed
                        } else {
                            otherTerm.saved = false;
                        }
                                      
                        otherTerm.opened = false;
                        this.orderedTermsList.splice(i, 1, otherTerm);
                    }
                }
            } else {
                let originalIndex = this.termsList.findIndex(anOriginalTerm => anOriginalTerm.id === term.id);
                if (originalIndex >= 0) {
                    let originalTerm = JSON.parse(JSON.stringify(this.termsList[originalIndex]));
                    originalTerm.saved = term.saved;
                    originalTerm.opened = term.opened;
                    if (JSON.stringify(term) != JSON.stringify(originalTerm)) {

                        term.saved = false;
                    } else {
                        term.saved = true;
                    }
                } else {
                    term.saved = false;
                }
            }
            
            term.opened = !term.opened;
            this.orderedTermsList.splice(index, 1, term);
        
        },
        getOriginalTerm(termId){
            let index = this.orderedTermsList.findIndex(originalTerm => originalTerm.id == termId);
            if (index >= 0) {
                return this.termsList[index];
            }
            return null
        },
        tryToRemoveTerm(term) {

            // Checks if user is deleting a term with unsaved info.
            if (term.id == 'new' || !term.saved || term.opened) {
                this.$modal.open({
                    parent: this,
                    component: CustomDialog,
                    props: {
                        icon: 'alert',
                        title: this.$i18n.get('label_warning'),
                        message: this.$i18n.get('info_warning_terms_not_saved'),
                        onConfirm: () => { this.removeTerm(term); },
                    }
                });  
            } else{
                this.removeTerm(term);
            }

        },
        removeTerm(term) {

            // If all checks passed, term can be deleted
            if (term.id == 'new') {
                let index = this.orderedTermsList.findIndex(deletedTerm => deletedTerm.id == 'new');
                if (index >= 0) {
                    this.orderedTermsList.splice(index, 1);
                }
            } else {
                
                this.deleteTerm({taxonomyId: this.taxonomyId, termId: term.id})
                .then(() => {

                })
                .catch((error) => {
                    this.$console.log(error);
                });

                // Updates parent IDs for orphans
                for (let orphanTerm of this.termsList) {
                    if (orphanTerm.parent == term.id) {
                        this.updateTerm({
                            taxonomyId: this.taxonomyId, 
                            termId: orphanTerm.id, 
                            name: orphanTerm.name,
                            description: orphanTerm.description,
                            parent: term.parent
                        })
                        .catch((error) => {
                            this.$console.log(error);
                        });
                    }
                }
            }   
        },
        onTermEditionFinished() {   
            this.isEditingTerm = false;
        },
        onTermEditionCanceled(term) {

            let originalIndex = this.termsList.findIndex(anOriginalTerm => anOriginalTerm.id == term.id);
            let canceledIndex = this.orderedTermsList.findIndex(canceledTerm => canceledTerm.id == term.id);
            if (originalIndex >= 0 && canceledIndex >= 0) {
                let originalTerm = JSON.parse(JSON.stringify(this.termsList[originalIndex]));
                this.orderedTermsList.splice(canceledIndex, 1, originalTerm);
            } else {
                if (term.id == 'new')
                    this.removeTerm(term);
            }
            this.isEditingTerm = false;
        },
        buildOrderedTermsList(parentId, termDepth) {

            for (let i = 0; i < this.termsList.length; i++) {
                let term = this.termsList[i];

                if (term.parent != parentId ) {    
                    continue;
                } 
                
                term.depth = termDepth;
                if (this.orderedTermsList[term.id] === undefined ) {
                    term.opened = false;
                    term.saved = true ;
                } else {
                    term.opened = (this.orderedTermsList[term.id].opened === undefined ? false : this.orderedTermsList[term.id].opened);
                    term.saved = (this.orderedTermsList[term.id].saved === undefined ? true : this.orderedTermsList[term.id].saved);
                }
                if (term.taxonomy != null)
                    this.orderedTermsList.push(JSON.parse(JSON.stringify(term)));

                this.buildOrderedTermsList(term.id, termDepth + 1);
            }
        },
        generateOrderedTerms() {
            this.orderedTermsList = new Array();
            this.buildOrderedTermsList(0, 0);
        },
        getUnsavedTermName(term) {
            let originalIndex = this.termsList.findIndex(anOriginalTerm => anOriginalTerm.id == term.id);
            if (originalIndex >= 0)
                return this.termsList[originalIndex].name;
            else 
                return term.name;
        },
        loadTerms() {
            this.isLoadingTerms = true;
            this.fetchTerms({ taxonomyId: this.taxonomyId, fetchOnly: '', search: '', all: '', order: this.order})
                .then(() => {
                    // Fill this.form data with current data.
                    this.isLoadingTerms = false;
                    this.generateOrderedTerms();
                })
                .catch((error) => {
                    this.$console.log(error);
                });
        }
    },
    created() {
        if (this.taxonomyId !== String) {
            this.loadTerms();
        }
    }
    
}
</script>

<style lang="scss">

    @import "../../scss/_variables.scss";

    .order-area {
        display: inline-block;
        padding: 4px;
        margin-left: 30px;

        .gray-icon, .gray-icon .icon {
            color: $tainacan-placeholder-color !important;
        }
        .gray-icon .icon i::before, .gray-icon i::before {
            font-size: 21px !important;
        }
    }

    .term-item {
        font-size: 14px;
        padding: 0.7em 0.9em;
        margin: 4px;
        min-height: 40px;
        display: block; 
        position: relative;
        
        .handle {
            padding-right: 6em;
        }
        .term-name {
            text-overflow: ellipsis;
            overflow-x: hidden;
            white-space: nowrap;
            font-weight: bold;
            margin-left: 0.4em;
            margin-right: 0.4em;

            &.is-danger {
                color: $danger !important;
            }
        }
        .label-details {
            font-weight: normal;
            color: $gray;
        }
        .not-saved {
            font-style: italic;
            font-weight: bold;
            color: $danger;
        }
        .controls { 
            position: absolute;
            right: 5px;
            top: 10px;

            .icon {
                bottom: 1px;   
                position: relative;
                i, i:before { font-size: 20px; }
                .mdi-plus-circle, .mdi-plus-circle:before{
                    font-size: 18px;
                }
                a {
                    margin-right: 8px;
                }
            }
            .button {
                margin-right: 1em;
            }
        }

        &.not-sortable-item, &.not-sortable-item:hover {
            cursor: default;
            background-color: white !important;

        } 
        &.not-focusable-item, &.not-focusable-item:hover {
            cursor: default;
            
            .term-name {
                color: $primary;
            }
        }
  
    }
    // .term-item:hover:not(.not-sortable-item) {
    //     background-color: $secondary;
    //     border-color: $secondary;
    //     color: white !important;

    //     .label-details, .icon, .not-saved {
    //         color: white !important;
    //     }

    //     .grip-icon { 
    //         fill: white; 
    //     }
    // }

</style>


