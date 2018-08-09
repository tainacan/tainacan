<template>
<div>
    <div 
            v-if="termsList.length > 0 && !isLoadingTerms"
            class="terms-list-header">
        <button
                class="button is-secondary"
                type="button"
                @click="addNewTerm()"
                :disabled="isEditingTerm">
            {{ $i18n.get('label_new_term') }}
        </button>
        <b-field class="order-area">
            <button
                    :disabled="orderedTermsList.length <= 0 || isLoadingTerms || isEditingTerm || order == 'asc'"
                    class="button is-white is-small"
                    @click="onChangeOrder('asc')">
                <b-icon 
                        class="gray-icon"
                        icon="sort-ascending"/>
            </button>
            <button
                    :disabled="orderedTermsList.length <= 0 || isLoadingTerms || isEditingTerm || order == 'desc'"
                    class="button is-white is-small"
                    @click="onChangeOrder('desc')">
                <b-icon 
                        class="gray-icon"
                        icon="sort-descending"/>
            </button>
        </b-field>
        <div class="search-area is-hidden-mobile">
            <div class="control has-icons-right  is-small is-clearfix">
                <input
                        class="input is-small"
                        :placeholder="$i18n.get('instruction_search')"
                        type="search"
                        autocomplete="on"
                        v-model="searchQuery"
                        @keyup.enter="loadTerms(0)"
                        :disabled="isEditingTerm">
                    <span
                            @click="loadTerms(0)"
                            class="icon is-right">
                        <i class="mdi mdi-magnify" />
                    </span>
            </div>
        </div>
    </div>
    <div class="columns">
        <b-loading 
                :is-full-page="false"
                :active.sync="isLoadingTerms" 
                :can-cancel="false"/>
        <div 
                :class="{ 'is-12': !isEditingTerm, 'is-8': isEditingTerm }"
                class="column">
            <br>

            <div    
                    class="term-item"
                    :class="{
                        'opened-term': term.opened,
                        'collapsed-term': term.collapsed ? term.collapsed : false
                    }" 
                    :style="{'margin-left': (term.depth * 55) + 'px', 'border-left-color': term.depth > 0 ? '#f2f2f2' : 'transparent'}"
                    v-for="(term, index) in orderedTermsList"
                    :key="term.id">
                <span 
                        v-if="term.depth > 0"
                        class="icon children-icon">
                    <i class="mdi mdi-24px mdi-subdirectory-arrow-right"/>
                </span> 
                <span 
                        class="term-name" 
                        :class="{'is-danger': formWithErrors == term.id }">
                    {{ term.saved && !term.opened ? term.name : getUnsavedTermName(term) }}
                </span>
                <span class="has-text-gray">
                    {{ String(term.hasLoadedChildren) + ' | ' + String(term.hasCollapsedChildren) }}
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
                <span 
                        class="children-dropdown"
                        v-if="!isEditingTerm && term.total_children > 0"
                        @click.prevent="(term.hasLoadedChildren != undefined && term.hasLoadedChildren) ? collapseTermChildren(term.id, index) : loadTerms(term.id, index)">
                    <span class="icon">
                        <i 
                                :class="{ 
                                    'mdi-menu-right': !term.hasLoadedChildren || (term.hasCollapsedChildren && term.hasLoadedChildren),  
                                    'mdi-menu-down': (!term.hasCollapsedChildren && term.hasLoadedChildren) || (term.hasCollapsedChildren != undefined && term.hasCollapsedChildren) }"
                                class="mdi mdi-24px"/>
                    </span>
                    <span>{{ term.total_children + ' ' + $i18n.get('label_children_terms') }}</span>
                </span>
                <span class="controls" >
                    <a 
                            @click="addNewChildTerm(term, index)"
                            :disabled="isEditingTerm">
                        <b-icon 
                                size="is-small"
                                icon="plus-circle"/>
                    </a>
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
                class="column is-4 edit-forms-list" 
                :key="index"
                v-for="(term, index) in orderedTermsList"
                v-show="term.opened">
            <term-edition-form 
                    :style="{ 'top': termEditionFormTop + 'px'}"
                    :taxonomy-id="taxonomyId"
                    @onEditionFinished="onTermEditionFinished()"
                    @onEditionCanceled="onTermEditionCanceled(term)"
                    @onErrorFound="formWithErrors = term.id"
                    :edit-form="term"/>
        </div>
    </div>
    <!-- Empty state image -->
    <div v-if="termsList.length <= 0 && !isLoadingTerms && !isEditingTerm">
        <section class="section">
            <div class="content has-text-grey has-text-centered">
                <p>
                    <b-icon
                            icon="inbox"
                            size="is-large"/>
                </p>
                <p>{{ $i18n.get('info_no_terms_created_on_taxonomy') }}</p>
                <button
                        id="button-create-term"
                        class="button is-secondary"
                        @click="addNewTerm()">
                    {{ $i18n.get('label_new_term') }}
                </button>
            </div>
        </section>
    </div>       
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
            order: 'asc',
            termEditionFormTop: 0,
            searchQuery: '',
            termsColapses: [],
            isGeneratingOrderedTerms: false
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
            this.loadTerms(0);
        }
    },
    components: {
        TermEditionForm
    },
    methods: {
        ...mapActions('taxonomy', [
            'updateTerm',
            'deleteTerm',
            'fetchChildTerms',
            'fetchTerms'
        ]),
        ...mapGetters('taxonomy',[
            'getTerms'
        ]),
        onChangeOrder(newOrder) {
            this.order = newOrder;
            this.loadTerms(0);
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
            
            let container = document.getElementById('repository-container');
            if (container && container.scrollTop && container.scrollTop > 80)
                this.termEditionFormTop = container.scrollTop - 80;
            else
                this.termEditionFormTop = 0;
            
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

                    if (JSON.stringify(term) != JSON.stringify(originalTerm))
                        term.saved = false;
                    else 
                        term.saved = true;
                    
                } else {
                    term.saved = false;
                }
                this.isEditingTerm = false;
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

            this.$modal.open({
                parent: this,
                component: CustomDialog,
                props: {
                    icon: 'alert',
                    title: this.$i18n.get('label_warning'),
                    message: this.$i18n.get('info_warning_selected_term_delete'),
                    onConfirm: () => { 
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
                }
            });  
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
                if (term.taxonomy != null) {
                    let termCollapseIndex = this.termsColapses.findIndex(aTerm => aTerm.id == term.id);
                    if (termCollapseIndex >= 0) {
                        term.collapsed = this.termsColapses[termCollapseIndex].collapsed;
                        term.hasLoadedChildren = this.termsColapses[termCollapseIndex].hasLoadedChildren;
                        term.hasCollapsedChildren = this.termsColapses[termCollapseIndex].hasCollapsedChildren;
                        term.opened = this.termsColapses[termCollapseIndex].opened;
                        term.saved = this.termsColapses[termCollapseIndex].saved;
                    }
                    this.orderedTermsList.push(JSON.parse(JSON.stringify(term)));
                }

                this.buildOrderedTermsList(term.id, termDepth + 1);
            }
        },
        generateOrderedTerms() {
            this.termsColapses = JSON.parse(JSON.stringify(this.orderedTermsList));
            this.isGeneratingOrderedTerms = true;
            this.orderedTermsList = new Array();
            this.buildOrderedTermsList(0, 0);
            this.isGeneratingOrderedTerms = false;
        },
        getUnsavedTermName(term) {
            let originalIndex = this.termsList.findIndex(anOriginalTerm => anOriginalTerm.id == term.id);
            if (originalIndex >= 0)
                return this.termsList[originalIndex].name;
            else 
                return term.name;
        },
        collapseTermChildren(parentId, parentIndex) {
            if (this.orderedTermsList[parentIndex].hasCollapsedChildren != undefined && !this.orderedTermsList[parentIndex].hasCollapsedChildren) {
                for (let i = 0; i < this.orderedTermsList.length; i++) {
                    if (this.orderedTermsList[i].parent == parentId) {
                        this.$set(this.orderedTermsList[i], 'collapsed', true);
                        this.$set(this.orderedTermsList[i], 'hasCollapsedChildren', false);
                        this.collapseTermChildren(this.orderedTermsList[i].id, i);
                    }
                }
                this.$set(this.orderedTermsList[parentIndex], 'hasCollapsedChildren', true);
            } else {
                for (let i = 0; i < this.orderedTermsList.length; i++) {
                    if (this.orderedTermsList[i].parent == parentId) {
                        this.$set(this.orderedTermsList[i], 'collapsed', false);
                        this.collapseTermChildren(this.orderedTermsList[i].id, i);
                    }
                }
                this.$set(this.orderedTermsList[parentIndex], 'hasCollapsedChildren', false);
            }
        },
        loadTerms(parentId, parentIndex) {

            this.isLoadingTerms = true;
            let search = (this.searchQuery != undefined && this.searchQuery != '') ? { searchterm: this.searchQuery } : '';
            this.fetchChildTerms({ parentId: parentId, taxonomyId: this.taxonomyId, fetchOnly: '', search: search, all: '', order: this.order})
                .then(() => {
                                 
                    if (parentId != undefined && parentId > 0) {
                        this.orderedTermsList[parentIndex].hasLoadedChildren = true;
                        this.orderedTermsList[parentIndex].hasCollapsedChildren = false;
                    }
                    this.generateOrderedTerms();
                    this.isLoadingTerms = false;   
                })
                .catch((error) => {
                    this.isLoadingTerms = false;   
                    this.$console.log(error);
                });
        }
    },
    created() {
        if (this.taxonomyId !== String) {
            this.loadTerms(0);
        }
    }
    
}
</script>

<style lang="scss">

    @import "../../scss/_variables.scss";

    .columns {
        position: relative;
    }

    .terms-list-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;

        .order-area {
            display: inline-flex !important;
            padding: 4px;
            margin-left: auto;

            .gray-icon, .gray-icon .icon {
                color: $gray4 !important;
            }
            .gray-icon .icon i::before, .gray-icon i::before {
                font-size: 21px !important;
            }
        }

        .search-area {
            display: inline-flex;
            align-items: center;

            .input {
                border: 1px solid $gray2;
            }
            .control {
                width: 100%;
                .icon {
                    pointer-events: all;
                    cursor: pointer;
                    color: $blue5;
                    height: 27px;
                    font-size: 18px !important;
                    height: 2rem !important;
                }
            }
            a {
                margin-left: 12px;
                white-space: nowrap; 
            }
        }
    }

    .term-item {
        font-size: 14px;
        padding: 0 0 0 1.75rem;
        min-height: 40px;
        display: flex; 
        position: relative;
        align-items: center;
        justify-content: space-between;
        border-left: 1px solid transparent;
        visibility: visible;
        opacity: 1;
        transition: display 0.3s, visibility 0.3s, opacity 0.3s;

        &:hover {
            background-color: $gray1 !important;
            .controls {
                visibility: visible;
                opacity: 1.0;
            }
            &::before {
                border-color: transparent transparent transparent $gray2 !important;
            }
        }
        
        .children-icon {
            color: $blue2;
            position: absolute;
            left: -25px;
        }

        .term-name {
            text-overflow: ellipsis;
            overflow-x: hidden;
            white-space: nowrap;
            margin-left: 0.4em;
            margin-right: 0.4em;
            display: inline-block;
            max-width: 73%; 

            &.is-danger {
                color: $danger !important;
            }
        }
        .label-details {
            font-weight: normal;
            color: $gray3;
            margin-right: auto;
        }
        .not-saved {
            font-style: italic;
            font-weight: bold;
            color: $danger;
        }
        .children-dropdown {
            margin-left: auto;
            color: $blue4;
            cursor: pointer;
            padding-right: 1rem;
            white-space: nowrap;
            overflow: hidden;
            display: flex;
            align-items: center;
        }
        .controls { 
            visibility: hidden;
            opacity: 0.0;
            background-color: $gray2;
            padding: 0.4375rem 0.875rem;

            a {
                margin: 0 0.375rem;
                .icon {
                    bottom: 1px;   
                    position: relative;
                    i, i:before { font-size: 20px; }
                    a {
                        margin-right: 8px;
                    }
                }
            }            
        }

        &.opened-term {
            cursor: default;
            background-color: $blue1;

            &:before {
                content: '';
                display: block;
                position: absolute;
                left: 100%;
                right: -20px;
                width: 0;
                height: 0;
                border-style: solid;
                border-color: transparent transparent transparent $blue1;
                border-left-width: 24px;
                border-top-width: 20px;
                border-bottom-width: 20px;
                top: 0;
            }
            &:hover:before {
                border-color: transparent transparent transparent $gray1;
            }
        }

        &.collapsed-term {
            display: none;
            visibility: hidden;
            opacity: 0;
            transition: display 0.3s, visibility 0.3s, opacity 0.3s;
        }
  
    }
    .edit-forms-list {
        padding-left: 0;
    }

</style>


