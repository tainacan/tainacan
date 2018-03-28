<template>
    <div>
        <button
                class="button is-secondary is-small"
                type="button"
                @click="addNewTerm()">
            {{ $i18n.get('label_new_term') }}
        </button>
        <div    
                class="term-item"
                :class="{
                    'not-sortable-item': term.id == 'new' || term.id == undefined || openedTermId != '' , 
                    'not-focusable-item': openedTermId == term.id
                }" 
                :style="{'margin-left': (term.depth * 20) + 'px'}"
                v-for="(term, index) in orderedTermsList"
                :key="index">
            <span 
                    class="term-name" 
                    :class="{'is-danger': formWithErrors == term.id }">
                {{ term.name }}
            </span>
            <span   
                    v-if="term.id != undefined"
                    class="label-details">
                <span 
                        class="not-saved" 
                        v-if="(editForms[term.id] != undefined && editForms[term.id].saved != true) || term.id == 'new'"> 
                    {{ $i18n.get('info_not_saved') }}
                </span>
            </span>
            <span 
                    class="loading-spinner" 
                    v-if="term.id == undefined"/>
            <span class="controls" >

                <a @click.prevent="editTerm(term)">
                    <b-icon 
                            type="is-gray" 
                            icon="pencil"/>
                </a>
                <a 
                    @click.prevent="removeTerm(term)">
                    <b-icon 
                            type="is-gray" 
                            icon="delete"/>
                </a>
            </span>
            <div v-if="openedTermId == term.id">
                <term-edition-form   
                        :category-id="categoryId"
                        @onEditionFinished="onTermEditionFinished()"
                        @onEditionCanceled="onTermEditionCanceled()"
                        @onErrorFound="formWithErrors = term.id"
                        :index="index"
                        :original-term="term"
                        :edited-term="editForms[term.id]"/>

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
            // Terms related
            isLoadingTerms: false,
            formWithErrors: '',
            openedTermId: '',
            editForms: [],
            orderedTermsList: []
        }
    },
    props: {
        categoryId: String
    },
    computed: {
        termsList() {
            return this.getTerms();
        }
    },
    watch: {
        'termsList'() {
            this.generateOrderedTerms();
        }
    },
    components: {
        TermEditionForm
    },
    beforeRouteUpdate ( to, from, next ) {
        let hasUnsavedForms = false;
        for (let editForm in this.editForms) {
            if (!this.editForms[editForm].saved) 
                hasUnsavedForms = true;
        }
        if ((this.openedTermId != '' && this.openedTermId != undefined) || hasUnsavedForms ) {
            this.$dialog.confirm({
                message: this.$i18n.get('info_warning_terms_not_saved'),
                    onConfirm: () => {
                        this.onEditionCanceled();
                        next();
                    },
                    cancelText: this.$i18n.get('cancel'),
                    confirmText: this.$i18n.get('continue'),
                    type: 'is-secondary'
                });  
        } else {
            next()
        }  
    },
    methods: {
        ...mapActions('category', [
            'fetchTerms',
            'updateTerm',
            'deleteTerm'
        ]),
        ...mapGetters('category',[
            'getTerms'
        ]),
        addNewTerm() {
            let newTerm = {
                categoryId: this.categoryId,
                name: '',
                description: '',
                parent: 0,
                id: 'new'
            }
            this.termsList.push(newTerm);
            this.editTerm(newTerm);
        },
        editTerm(term) {

            // Closing collapse
            if (this.openedTermId == term.id) {    
                this.openedTermId = '';

            // Opening collapse
            } else {

                this.openedTermId = term.id;
                // First time opening
                if (this.editForms[this.openedTermId] == undefined) {
                    this.editForms[this.openedTermId] = JSON.parse(JSON.stringify(term));
                    this.editForms[this.openedTermId].saved = true;
                    
                    if (term.id == 'new')
                        this.editForms[this.openedTermId].saved = false;
                }     
            }
        },
        removeTerm(term) {
            if (term.id == 'new') {
                let index = this.termsList.findIndex(deletedTerm => deletedTerm.id == 'new');
                if (index >= 0) {
                    this.termsList.splice(index, 1);
                }
                if (this.openedTermId == 'new')
                    this.openedTermId = '';
                delete this.editForms['new'];
            } else {
                this.deleteTerm({categoryId: this.categoryId, termId: term.id})
                .then(() => {

                })
                .catch((error) => {
                    this.$console.log(error);
                });
            }
        },
        onTermEditionFinished() {   
            let index = this.termsList.findIndex(deletedTerm => deletedTerm.id == 'new');
            if (index >= 0) {
                this.termsList.splice(index, 1);
            }      
            this.formWithErrors = '';
            delete this.editForms[this.openedTermId];
            this.openedTermId = '';
        },
        onTermEditionCanceled() {
            this.formWithErrors = '';
            delete this.editForms[this.openedTermId];
            this.openedTermId = '';
        },
        buildOrderedTermsList(parentId, termDepth) {

            for (let term of this.termsList) {

                if (term.parent != parentId ) {    
                    continue;
                } 
                
                term.depth = termDepth;
                this.orderedTermsList.push(term);

                this.buildOrderedTermsList(term.id, termDepth + 1);
            }
        },
        generateOrderedTerms() {
            this.orderedTermsList = new Array();
            this.buildOrderedTermsList(0, 0);
            this.$console.log(this.orderedTermsList);
        }
    },
    created() {

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
}
</script>

<style lang="scss">

    @import "../../scss/_variables.scss";

    .loading-spinner {
        animation: spinAround 500ms infinite linear;
        border: 2px solid #dbdbdb;
        border-radius: 290486px;
        border-right-color: transparent;
        border-top-color: transparent;
        content: "";
        display: inline-block;
        height: 1em; 
        width: 1em;
    }

    .term-item {
        font-size: 14px;
        background-color: white;
        padding: 0.7em 0.9em;
        margin: 4px;
        min-height: 40px;
        display: block; 
        position: relative;
        cursor: grab;
        
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
    .term-item:hover:not(.not-sortable-item) {
        background-color: $secondary;
        border-color: $secondary;
        color: white !important;

        .label-details, .icon, .not-saved {
            color: white !important;
        }

        .grip-icon { 
            fill: white; 
        }
    }

</style>


