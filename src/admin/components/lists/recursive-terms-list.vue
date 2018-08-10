<template>
<div>
    <div
            class="term-item"
            :class="{
                'opened-term': term.opened,
            }">
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
                @click.prevent="loadChildTerms(term.id, index)">
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
            <a 
                    v-if="!isEditingTerm"
                    @click.prevent="editTerm()">
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
    <div    
            class="term-item"
            :class="{
                'opened-term': childTerm.opened,
            }" 
            v-for="(childTerm, childIndex) in term.children"
            :key="childTerm.id"
            v-if="showChildren">
        
        <recursive-terms-list 
                :term="childTerm"
                :index="childIndex"
                :taxonomy-id="taxonomyId"/>
    </div>
</div>
</template>

<script>
import { mapActions, mapGetters } from 'vuex';
import RecursiveTermsList from './recursive-terms-list.vue';

export default {
    name: 'RecursiveTermsList',
    data(){
        return {
            isLoadingTerms: false,
            isEditingTerm: false,
            searchQuery: '',
            order: 'asc',
            showChildren: false 
        }
    },
    props: {
        term: Object,
        index: Number,
        taxonomyId: Number
    },
    components: {
        RecursiveTermsList,
    },
    methods: {
        ...mapActions('taxonomy', [
            'updateTerm',
            'deleteTerm',
            'fetchChildTerms',
            'fetchTerms'
        ]),
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
        getUnsavedTermName(term) {
            // let originalIndex = this.termsList.findIndex(anOriginalTerm => anOriginalTerm.id == term.id);
            // if (originalIndex >= 0)
            //     return this.termsList[originalIndex].name;
            // else 
                return term.name;
        },        
        loadChildTerms(parentId, parentIndex) {

            if (this.term.children == undefined || this.term.children.length <= 0) {

                this.isLoadingTerms = true;
                let search = (this.searchQuery != undefined && this.searchQuery != '') ? { searchterm: this.searchQuery } : '';
                this.fetchChildTerms({ parentId: parentId, taxonomyId: this.taxonomyId, fetchOnly: '', search: search, all: '', order: this.order})
                    .then(() => {
                        this.isLoadingTerms = false;
                        this.showChildren = true;   
                    })
                    .catch((error) => {
                        this.isLoadingTerms = false;   
                        this.$console.log(error);
                    });
            } else {
                this.showChildren = !this.showChildren;
            }
        },
        editTerm() {
            
            // Position edit form in a visible area
            let container = document.getElementById('repository-container');
            if (container && container.scrollTop && container.scrollTop > 80)
                this.termEditionFormTop = container.scrollTop - 80;
            else
                this.termEditionFormTop = 0;
        
            this.term.opened = !this.term.opened;
            
            this.$termsListBus.onEditTerm(this.term);
            // this.orderedTermsList.splice(index, 1, term);
        
        },
    },
    created() {
        this.$termsListBus.$on('editTerm', (term) => {
            this.isEditingTerm = true;
        });
        this.$termsListBus.$on('termEditionSaved', (term) => {
            this.isEditingTerm = false;
            this.term.opened = false;
        });
        this.$termsListBus.$on('termEditionCanceled', (term) => {
            this.isEditingTerm = false;
            this.term.opened = false;
        });
    }
}
</script>

<style lang="scss">
    @import "../../scss/_variables.scss";

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
</style>
