<template>
    <div>
        <b-field
                :addons="false"
                :label="$i18n.get('label_category_terms')">
            <button
                    class="button is-secondary is-pulled-right"
                    type="button"
                    @click="addNewTerm()">
                {{ $i18n.get('label_new_term') }}
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
                :style="{'margin-left': (term.depth * 20) + 'px'}"
                v-for="(term, index) in orderedTermsList"
                :key="term.id"> 
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
                        :href="categoryPath + '/' + term.slug">
                    {{ $i18n.get('label_view_term') }}
                </button>
            -->
                <a
                        class="is-small"
                        type="button"
                        @click="addNewChildTerm(term, index)">
                    <b-icon 
                            size="is-small"
                            icon="plus-circle"/>
                    {{ $i18n.get('label_new_child') }}
                </a>

                <a @click.prevent="editTerm(term, index)">
                    <b-icon 
                            type="is-gray" 
                            icon="pencil"/>
                </a>
                <a 
                    @click.prevent="tryToRemoveTerm(term)">
                    <b-icon 
                            type="is-gray" 
                            icon="delete"/>
                </a>
            </span>
            <div v-show="term.opened">
                <term-edition-form 
                        :category-id="categoryId"
                        @onEditionFinished="onTermEditionFinished()"
                        @onEditionCanceled="onTermEditionCanceled(term)"
                        @onErrorFound="formWithErrors = term.id"
                        :edit-form="term"/>

            </div>
        </div>
        <b-loading 
                :active.sync="isLoadingTerms" 
                :can-cancel="false"/>
    </div>
</template>

<script>
import { mapActions, mapGetters } from 'vuex';
import TermEditionForm from '../edition/term-edition-form.vue'

export default {
    name: 'TermsList',
    data(){
        return {
            isLoadingTerms: false,
            formWithErrors: '',
            orderedTermsList: []
        }
    },
    props: {
        categoryId: String,
        //categoryPath: ''
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
        categoryId() {
            this.loadTerms();
        }
    },
    components: {
        TermEditionForm
    },
    methods: {
        ...mapActions('category', [
            'updateTerm',
            'deleteTerm',
            'fetchTerms'
        ]),
        ...mapGetters('category',[
            'getTerms'
        ]),
        addNewTerm() {
            let newTerm = {
                categoryId: this.categoryId,
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
            let newTerm = {
                categoryId: this.categoryId,
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
                this.$dialog.confirm({
                    message: this.$i18n.get('info_warning_terms_not_saved'),
                        onCancel: () => { return },
                        onConfirm: () => { this.removeTerm(term);},
                        cancelText: this.$i18n.get('cancel'),
                        confirmText: this.$i18n.get('continue'),
                        type: 'is-secondary'
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
                
                this.deleteTerm({categoryId: this.categoryId, termId: term.id})
                .then(() => {

                })
                .catch((error) => {
                    this.$console.log(error);
                });

                // Updates parent IDs for orphans
                for (let orphanTerm of this.termsList) {
                    if (orphanTerm.parent == term.id) {
                        this.updateTerm({
                            categoryId: this.categoryId, 
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
            this.fetchTerms(this.categoryId)
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
        if (this.categoryId !== String) {
            this.loadTerms();
        }
    }
    
}
</script>

<style lang="scss">

    @import "../../scss/_variables.scss";

    .term-item {
        font-size: 14px;
        border-left: 1px solid #eee;
        padding: 0.7em 0.9em;
        margin: 4px;
        min-height: 40px;
        display: block; 
        position: relative;
        //cursor: grab;
        
        .handle {
            padding-right: 6em;
        }
        .grip-icon { 
            fill: $gray; 
            top: 2px;
            position: relative;
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

            .handle .label-details, .handle .icon {
                color: $gray !important;
            }
        } 
        &.not-focusable-item, &.not-focusable-item:hover {
            cursor: default;
            
            .term-name {
                color: $primary;
            }
            .handle .label-details, .handle .icon {
                color: $gray !important;
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


