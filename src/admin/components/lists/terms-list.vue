<template>
<div>
    <div 
            v-if="termsList.length > 0 && !isLoadingTerms"
            class="terms-list-header">
        <button
                class="button is-secondary"
                type="button"
                @click="addNewTerm(0)"
                :disabled="isEditingTerm">
            {{ $i18n.get('label_new_term') }}
        </button>
        <b-field class="order-area">
            <button
                    :disabled="localTerms.length <= 0 || isLoadingTerms || isEditingTerm || order == 'asc'"
                    class="button is-white is-small"
                    @click="onChangeOrder('asc')">
                <b-icon 
                        class="gray-icon"
                        icon="sort-ascending"/>
            </button>
            <button
                    :disabled="localTerms.length <= 0 || isLoadingTerms || isEditingTerm || order == 'desc'"
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
                    v-for="(term, index) in localTerms"
                    :key="term.id">
                
                <recursive-terms-list 
                        :term="term"
                        :index="index"
                        :taxonomy-id="taxonomyId"/>
            </div>
        </div>
        <div 
                class="column is-4 edit-forms-list"
                v-if="isEditingTerm">
            <term-edition-form 
                    :style="{ 'top': termEditionFormTop + 'px'}"
                    :taxonomy-id="taxonomyId"
                    @onEditionFinished="onTermEditionFinished(editTerm)"
                    @onEditionCanceled="onTermEditionCanceled(editTerm)"
                    @onErrorFound="formWithErrors = editTerm.id"
                    :edit-form="editTerm"/>
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
                        @click="addNewTerm(0)">
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
import RecursiveTermsList from './recursive-terms-list.vue'
import t from 't';

export default {
    name: 'TermsList',
    data(){
        return {
            isLoadingTerms: false,
            isEditingTerm: false,
            formWithErrors: '',
            order: 'asc',
            termEditionFormTop: 0,
            searchQuery: '',
            localTerms: [],
            editTerm: null
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
        termsList: {
            handler() { 
                this.localTerms = JSON.parse(JSON.stringify(this.termsList));
                for (let aTerm of this.localTerms) {
                    t.dfs(aTerm, [], (node) => { 
                        node.opened = false; 
                    });
                }
            },
            deep: true
        },
        taxonomyId() {
            this.loadTerms(0);
        }
    },
    components: {
        RecursiveTermsList,
        TermEditionForm
    },
    methods: {
        ...mapActions('taxonomy', [
            'deleteTerm',
            'fetchChildTerms',
            'fetchTerms',
            'clearTerms'
        ]),
        ...mapGetters('taxonomy',[
            'getTerms'
        ]),
        onChangeOrder(newOrder) {
            this.order = newOrder;
            this.loadTerms(0);
        },
        addNewTerm(parent) {

            let newTerm = {
                taxonomyId: this.taxonomyId,
                name: this.$i18n.get('label_term_without_name'),
                description: '',
                parent: parent,
                id: 'new',
                saved: false,
                opened: true
            }
            if (parent == 0) {
                this.localTerms.unshift(newTerm);
            } else {
                for (let term of this.localTerms) {
                    let parentTerm = t.find(term, [], (node, par) => { return node.id == parent; });
                    if (parentTerm != undefined) {
                        if (parentTerm['children'] == undefined) {
                            this.$set(parentTerm, 'children', []);
                            parentTerm.total_children = 1;
                        }
                        parentTerm['children'].unshift(newTerm); 
                    }
                }
            }
            this.$termsListBus.onEditTerm(newTerm);
        },
        onTermEditionFinished(term) {
            this.$termsListBus.onTermEditionSaved(term);
        },
        onTermEditionCanceled(term) {

            let originalTerm;
            for (let aTerm of this.termsList) {
                if (aTerm.id == term.id)
                    originalTerm = aTerm;
                else {
                    let childOriginalTerm = t.find(aTerm, [], (node, par) => { return node.id == term.id} );
                    if (childOriginalTerm != undefined)
                        originalTerm = childOriginalTerm;
                }
            }

            if (originalTerm != undefined) {
                for (let i = 0; i < this.localTerms.length; i++) {
                    if (this.localTerms[i].id == term.id) {
                        this.$set(this.localTerms, i, JSON.parse(JSON.stringify(originalTerm)));
                        break;
                    } else { 
                        let canceledParent = t.find(this.localTerms[i], [], (node, par) => { return node.id == originalTerm.parent }); 
                        if (canceledParent != undefined) {
                            for (let j = 0; j < canceledParent['children'].length; j++){
                                if (canceledParent['children'][j].id == originalTerm.id) {
                                    this.$set(canceledParent['children'], j, JSON.parse(JSON.stringify(originalTerm)));
                                    break;
                                }
                            }
                            break;              
                        }          
                    }
                }
            } else {
                if (term.id == 'new') { 
                    for (let i = 0; i < this.localTerms.length; i++) {
                        if (this.localTerms[i].id == term.id) {
                            this.localTerms.splice(i, 1);
                            break;
                        } else { 
                            let canceledParent = t.find(this.localTerms[i], [], (node, par) => { return node.id == term.parent }); 
                            if (canceledParent != undefined) {
                                for (let j = 0; j < canceledParent['children'].length; j++){
                                    if (canceledParent['children'][j].id == term.id) {
                                        canceledParent['children'].splice(j, i);
                                        break;
                                    }
                                }
                                break;              
                            }          
                        }
                    }
                }
            }
            this.$termsListBus.onTermEditionCanceled(term);
        },
        loadTerms(parentId, parentIndex) {

            this.clearTerms();

            this.isLoadingTerms = true;
            let search = (this.searchQuery != undefined && this.searchQuery != '') ? { searchterm: this.searchQuery } : '';
            this.fetchChildTerms({ parentId: parentId, taxonomyId: this.taxonomyId, fetchOnly: '', search: search, all: '', order: this.order})
                .then(() => {
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
        this.$termsListBus.$on('editTerm', (term) => {
            this.editTerm = term;
            this.isEditingTerm = true;
        });
        this.$termsListBus.$on('termEditionSaved', (term) => {
            this.isEditingTerm = false;
            this.editTerm = null;
        });
        this.$termsListBus.$on('termEditionCanceled', (term) => {
            this.isEditingTerm = false;
            this.editTerm = null;
        });
        this.$termsListBus.$on('addNewChildTerm', (parentId) => {
            this.addNewTerm(parentId);
        });
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

    .edit-forms-list {
        padding-left: 0;
    }

</style>


