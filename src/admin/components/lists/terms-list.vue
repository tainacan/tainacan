<template>
<div>
    <div 
            v-if="(termsList.length > 0 || searchQuery != '') && !isLoadingTerms"
            class="terms-list-header">
        <button
                class="button is-secondary"
                type="button"
                @click="addNewTerm(0)"
                :disabled="isEditingTerm">
            {{ $i18n.get('label_new_term') }}
        </button>
        <b-field class="order-area">
            <label class="label">{{ $i18n.get('label_sort') }}</label>
            <b-dropdown
                    :mobile-modal="true"
                    :disabled="localTerms.length <= 0 || isLoadingTerms || isEditingTerm"
                    @input="onChangeOrder(order == 'asc' ? 'desc' : 'asc')"
                    aria-role="list">
                <button
                            :aria-label="$i18n.get('label_sorting_direction')"
                            class="button is-white"
                            slot="trigger">
                    <span class="icon is-small gray-icon">
                        <i 
                                :class="order == 'desc' ? 'tainacan-icon-sortdescending' : 'tainacan-icon-sortascending'"
                                class="tainacan-icon"/>
                    </span>
                    <span class="icon">
                        <i class="tainacan-icon tainacan-icon-20px tainacan-icon-arrowdown" />
                    </span>
                </button>
                <b-dropdown-item
                        aria-controls="items-list-results"
                        role="button"
                        :class="{ 'is-active': order == 'desc' }"
                        :value="'desc'"
                        aria-role="listitem"
                        style="padding-bottom: 0.45rem">
                    <span class="icon is-small gray-icon">
                        <i class="tainacan-icon tainacan-icon-18px tainacan-icon-sortdescending"/>
                    </span>
                    {{ $i18n.get('label_descending') }}
                </b-dropdown-item>
                <b-dropdown-item
                        aria-controls="items-list-results"
                        role="button"
                        :class="{ 'is-active': order == 'asc' }"
                        :value="'asc'"
                        aria-role="listitem"
                        style="padding-bottom: 0.45rem">
                    <span class="icon is-small gray-icon">
                        <i class="tainacan-icon tainacan-icon-18px tainacan-icon-sortascending"/>
                    </span>
                    {{ $i18n.get('label_ascending') }}
                </b-dropdown-item>
            </b-dropdown>
        </b-field>
        <div class="search-area is-hidden-mobile">
            <div class="control has-icons-right  is-small is-clearfix">
                <input
                        class="input is-small"
                        :placeholder="$i18n.get('instruction_search')"
                        type="search"
                        :aria-label="$i18n.get('instruction_search') + ' ' + $i18n.get('terms')"
                        autocomplete="on"
                        :value="searchQuery"
                        @keyup.enter="searchQuery = $event.target.value;searchTerms(0)"
                        :disabled="isEditingTerm">
                    <span
                            @click="searchTerms(0)"
                            class="icon is-right"
                            :class="{ 'has-text-gray3': isEditingTerm }">
                        <i class="tainacan-icon tainacan-icon-search" />
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
                :class="{ 'is-12': !isEditingTerm, 'is-8-fullhd is-7-fullscreen is-6-desktop is-5-tablet': isEditingTerm }"
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
                    :key="term.id"
                    class="parent-term">
                
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
                class="column is-4-fullhd is-5-fullscreen is-6-desktop is-7-tablet edit-forms-list"
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
                    <span class="icon is-medium">
                        <i class="tainacan-icon tainacan-icon-36px tainacan-icon-terms"/>
                    </span>
                </p>
                <p>{{ searchQuery != '' ? $i18n.get('info_no_terms_found') : $i18n.get('info_no_terms_created_on_taxonomy') }}</p>
                <button
                        v-if="searchQuery == ''"
                        id="button-create-term"
                        class="button is-secondary"
                        @click="addNewTerm(0)">
                    {{ $i18n.get('label_new_term') }}
                </button>
                <button
                        v-if="searchQuery != ''"
                        id="button-clear-search"
                        class="button is-outlined"
                        @click="searchQuery = ''; searchTerms(0);">
                    {{ $i18n.get('clear_search') }}
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
        },
        isEditingTerm(value) {
            this.$emit('isEditingTermUpdate', value);
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

            for (let i = 0; i < this.termsList.length; i++) {
                if (this.termsList[i].id == $event.term.id) {
                    this.$set(this.termsList[i], 'description', $event.term.description);
                    this.$set(this.termsList[i], 'header_image', $event.term.header_image);
                    this.$set(this.termsList[i], 'header_image_id', $event.term.header_image_id);
                    this.$set(this.termsList[i], 'name', $event.term.name);
                    this.$set(this.termsList[i], 'parent', $event.term.parent);
                    this.$set(this.termsList[i], 'id', $event.term.id);
                } 
                else if (this.termsList[i].children != undefined) {
                    for (let j = 0; j < this.termsList[i].children.length; j++) {
                        if (this.termsList[i].children[j].id == $event.term.id) {
                            this.$set(this.termsList[i].children[j], 'description', $event.term.description);
                            this.$set(this.termsList[i].children[j], 'header_image', $event.term.header_image);
                            this.$set(this.termsList[i].children[j], 'header_image_id', $event.term.header_image_id);
                            this.$set(this.termsList[i].children[j], 'name', $event.term.name);
                            this.$set(this.termsList[i].children[j], 'parent', $event.term.parent);
                            this.$set(this.termsList[i].children[j], 'id', $event.term.id);
                        } 
                    }
                }
            } 
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
                    this.totalTerms--;
                })
                .catch((error) => {
                    this.$console.log(error);
                });
        },
        eventOnChildTermDeleted(parentTermId) {
            if ((parentTermId == 0 || parentTermId == undefined ) && this.totalTerms > 0) {
                this.totalTerms--;
                this.loadTerms(parentTermId);
            }
        },
        eventOnEditTerm(term) {
            // Position edit form in a visible area
            let container = document.getElementById('repository-container');
            if (container && container.scrollTop && container.scrollTop > 80)
                this.termEditionFormTop = container.scrollTop - 80;
            else
                this.termEditionFormTop = 0;

            this.editTerm = term;
            this.isEditingTerm = true;
        },
        eventOnTermEditionSaved({hasChangedParent}) {
            this.isEditingTerm = false;
            this.editTerm = null;

            if (hasChangedParent)
                this.loadTerms(0);
        },
        eventOnTermEditionCanceled() {
            this.isEditingTerm = false;
            this.editTerm = null;
        }
    },
    created() {
        if (this.taxonomyId !== String) {
            this.loadTerms(0);
        }
        this.$root.$on('onChildTermDeleted', this.eventOnChildTermDeleted);
        this.$termsListBus.$on('editTerm', this.eventOnEditTerm);
        this.$termsListBus.$on('termEditionSaved', this.eventOnTermEditionSaved);
        this.$termsListBus.$on('termEditionCanceled', this.eventOnTermEditionCanceled);
        this.$termsListBus.$on('addNewChildTerm', this.addNewTerm);
        this.$termsListBus.$on('deleteBasicTermItem', this.deleteBasicTerm);
    },
    beforeDestroy() {
        this.$root.$off('onChildTermDeleted', this.eventOnChildTermDeleted);
        this.$termsListBus.$off('editTerm', this.eventOnEditTerm);
        this.$termsListBus.$off('termEditionSaved', this.eventOnTermEditionSaved);
        this.$termsListBus.$off('termEditionCanceled', this.eventOnTermEditionCanceled);
        this.$termsListBus.$off('addNewChildTerm', this.addNewTerm);
        this.$termsListBus.$off('deleteBasicTermItem', this.deleteBasicTerm);
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
            padding: 4px;
            margin-top: -4px;
            margin-left: auto;

            .label {
                font-size: 0.875rem;
                font-weight: normal;
                margin-top: 3px;
                margin-bottom: 2px;
                cursor: default;
            }

            .button {
                display: flex;
                align-items: center;
            }
            
            .field {
                align-items: center;
            }

            .gray-icon, .gray-icon .icon {
                color: $gray4 !important;
                padding-right: 10px;
            }
            .gray-icon .icon i::before, 
            .gray-icon i::before {
                font-size: 1.3125rem !important;
                max-width: 26px;
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

    .parent-term>div>.term-item:first-child:hover {
        background-color: $gray1 !important;
        .controls {
            visibility: visible;
            opacity: 1.0;
        }
        &::before {
            border-color: transparent transparent transparent $gray2 !important;
        }
    }
    .parent-term>div>.opened-term.term-item:first-child {
        cursor: default;
        background-color: $gray1 !important;

        &:before {
            content: '';
            display: block;
            position: absolute;
            left: 100%;
            right: -20px;
            width: 0;
            height: 0;
            border-style: solid;
            border-color: transparent transparent transparent $gray1;
            border-left-width: 24px;
            border-top-width: 20px;
            border-bottom-width: 20px;
            top: 0;
        }
        &:hover:before {
            border-color: transparent transparent transparent $gray1;
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


