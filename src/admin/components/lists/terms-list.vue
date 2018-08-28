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
                        :value="searchQuery"
                        @keyup.enter="searchQuery = $event.target.value;searchTerms(0)"
                        :disabled="isEditingTerm">
                    <span
                            @click="searchTerms(0)"
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

            <!-- Basic list, without hierarchy, used during search -->
            <div 
                    v-if="searchQuery != undefined && searchQuery != ''"
                    v-for="(term, index) in localTerms"
                    :key="term.id">
                <basic-term-item 
                        :term="term"
                        :index="index"
                        :taxonomy-id="taxonomyId"
                        :order="order"/>
            </div>
            <a 
                    class="view-more-terms-level-0"
                    :class="{'is-disabled': isEditingTerm}"
                    @click="offset = offset + maxTerms; searchTerms(offset)"
                    v-if="(searchQuery != undefined && searchQuery != '') && totalTerms > localTerms.length">
                {{ $i18n.get('label_view_more') + ' (' + Number(totalTerms - localTerms.length) + ' ' + $i18n.get('terms') + ')' }}
            </a>

            <!-- Recursive list for hierarchy -->
            <div    
                    v-if="searchQuery == undefined || searchQuery == ''"
                    v-for="(term, index) in localTerms"
                    :key="term.id">
                
                <recursive-term-item 
                        :term="term"
                        :index="index"
                        :taxonomy-id="taxonomyId"
                        :order="order"/>
            </div>
            <a 
                    class="view-more-terms-level-0"
                    :class="{'is-disabled': isEditingTerm}"
                    @click="offset = offset + maxTerms; loadTerms(0)"
                    v-if="(searchQuery == undefined || searchQuery == '') && totalTerms > localTerms.length">
                {{ $i18n.get('label_view_more') + ' (' + Number(totalTerms - localTerms.length) + ' ' + $i18n.get('terms') + ')' }}
            </a>
        </div>
        <div 
                class="column is-4 edit-forms-list"
                v-if="isEditingTerm">
            <term-edition-form 
                    :style="{ 'top': termEditionFormTop + 'px'}"
                    :taxonomy-id="taxonomyId"
                    @onEditionFinished="onTermEditionFinished($event)"
                    @onEditionCanceled="onTermEditionCanceled($event)"
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
import RecursiveTermItem from './recursive-term-item.vue'
import BasicTermItem from './basic-term-item.vue'
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
            editTerm: null,
            maxTerms: 100,
            offset: 0,
            totalTerms: 0
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
        RecursiveTermItem,
        BasicTermItem,
        TermEditionForm
    },
    methods: {
        ...mapActions('taxonomy', [
            'deleteTerm',
            'fetchChildTerms',
            'clearTerms',
            'fetchTerms',
            'updateTerm'
        ]),
        ...mapGetters('taxonomy',[
            'getTerms'
        ]),
        onChangeOrder(newOrder) {
            this.offset = 0;
            this.order = newOrder;
            this.clearTerms();
            this.searchTerms(0);
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
                    let parentTerm = t.find(term, [], (node) => { return node.id == parent; });
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
        onTermEditionFinished($event) {
            this.$termsListBus.onTermEditionSaved($event);
        },
        onTermEditionCanceled($event) {

            let term = $event;

            if (term.id == 'new') { 
                for (let i = 0; i < this.localTerms.length; i++) {
                    if (this.localTerms[i].id == term.id) {
                        this.localTerms.splice(i, 1);
                        break;
                    } else { 
                        let canceledParent = t.find(this.localTerms[i], [], (node) => { return node.id == term.parent }); 
                        if (canceledParent != undefined) {
                            for (let j = 0; j < canceledParent['children'].length; j++){
                                if (canceledParent['children'][j].id == term.id) {
                                    canceledParent['children'].splice(j, 1);
                                    break;
                                }
                            }
                            break;              
                        }          
                    }
                }
            } else {

                let originalTerm;
                for (let aTerm of this.termsList) {
                    if (aTerm.id == term.id)
                        originalTerm = aTerm;
                    else {
                        let childOriginalTerm = t.find(aTerm, [], (node) => { return node.id == term.id} );
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
                            let canceledParent = t.find(this.localTerms[i], [], (node) => { return node.id == originalTerm.parent }); 
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
                }
            }
            this.$termsListBus.onTermEditionCanceled(term);
        },
        loadTerms(parentId) {

            if (this.offset == 0)
                this.clearTerms();

            this.isLoadingTerms = true;
            let search = (this.searchQuery != undefined && this.searchQuery != '') ? { searchterm: this.searchQuery } : '';
            this.fetchChildTerms({ 
                    parentId: parentId, 
                    taxonomyId: this.taxonomyId, 
                    fetchOnly: '', 
                    search: search, 
                    all: '', 
                    order: this.order,
                    offset: this.offset,
                    number: this.maxTerms })
                .then((resp) => {
                    this.isLoadingTerms = false;   
                    this.totalTerms = resp.total;
                })
                .catch((error) => {
                    this.isLoadingTerms = false;   
                    this.$console.log(error);
                });
        },
        searchTerms(offset) {
            
            if (this.searchQuery == undefined || this.searchQuery == '') {
                this.offset = 0;
                this.loadTerms(0);
            } else {
                this.offset = offset;
                if (this.offset == 0)
                    this.clearTerms();

                this.isLoadingTerms = true;
                let search = { searchterm: this.searchQuery };
                this.fetchTerms({ 
                        taxonomyId: this.taxonomyId, 
                        fetchOnly: '', 
                        search: search, 
                        all: '', 
                        order: this.order,
                        offset: this.offset,
                        number: this.maxTerms })
                    .then((resp) => {
                        this.isLoadingTerms = false;   
                        this.totalTerms = resp.total;
                    })
                    .catch((error) => {
                        this.isLoadingTerms = false;   
                        this.$console.log(error);
                    });
            }
        },
        // When searching, term deletion is perfomed by list as it has control of it's children
        deleteBasicTerm(term) {
            this.deleteTerm({taxonomyId: this.taxonomyId, termId: term.id })
                .then(() => {
                    this.searchTerms(this.offset);
                })
                .catch((error) => {
                    this.$console.log(error);
                });
        }
    },
    created() {
        if (this.taxonomyId !== String) {
            this.loadTerms(0);
        }
        this.$termsListBus.$on('editTerm', (term) => {

            // Position edit form in a visible area
            let container = document.getElementById('repository-container');
            if (container && container.scrollTop && container.scrollTop > 80)
                this.termEditionFormTop = container.scrollTop - 80;
            else
                this.termEditionFormTop = 0;

            this.editTerm = term;
            this.isEditingTerm = true;
        });
        this.$termsListBus.$on('termEditionSaved', ({hasChangedParent}) => {
            this.isEditingTerm = false;
            this.editTerm = null;

            if (hasChangedParent)
                this.loadTerms(0);
        });
        this.$termsListBus.$on('termEditionCanceled', () => {
            this.isEditingTerm = false;
            this.editTerm = null;
        });
        this.$termsListBus.$on('addNewChildTerm', (parentId) => {
            this.addNewTerm(parentId);
        });
        this.$termsListBus.$on('deleteBasicTermItem', (term) => {
            this.deleteBasicTerm(term);
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

    .view-more-terms-level-0 {
        font-size: 0.875rem;
        margin: 0;
        padding: 0.5rem 0 0.5rem 1.75rem;
        display: flex;
        border-top: 1px solid #f2f2f2;
    }

    .edit-forms-list {
        padding-left: 0;
    }

</style>


