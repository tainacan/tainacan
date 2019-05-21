<template>
<div 
        style="width: 100%;">
        
    <div
            class="term-item"
            :style="{
                'border-left-color': term.parent > 0 ? '#f2f2f2' : 'transparent'
            }"
            :class="{
                'opened-term': term.opened
            }">
        <span 
                v-if="term.parent != 0 && index == 0"
                class="icon children-icon">
            <i class="tainacan-icon tainacan-icon-20px tainacan-icon-nextlevel"/>
        </span> 
        <span
                v-tooltip="{
                    content: $i18n.get('label_show_children_terms'),
                    autoHide: true,
                    classes: ['tooltip', 'repository-tooltip'],
                    placement: 'bottom'
                }"
                class="children-dropdown icon">
            <i 
                    :class="{ 
                        'tainacan-icon-arrowright': !showChildren,  
                        'tainacan-icon-arrowdown': showChildren,
                        'is-disabled': isEditingTerm }"
                    class="tainacan-icon tainacan-icon-36px"
                    v-if="term.total_children > 0"
                    @click.prevent="toggleShowChildren()"/>
        </span>
        <span 
                class="term-name" 
                :class="{'is-danger': formWithErrors == term.id }">
            {{ term.name }}
        </span>
        <span 
                v-if="term.id == 'new'"
                class="label-details">
            <span class="not-saved" > 
                {{ $i18n.get('info_not_saved') }}
            </span>
        </span>
        <span 
                class="children-counter"
                v-if="term.total_children > 0">
            <span>{{ term.total_children + ' ' + $i18n.get('label_children_terms') }}</span>
        </span>
        <span 
                class="controls" 
                :class="{'is-disabled': isEditingTerm}">
            <a @click="addNewChildTerm(term, index)">
                <span
                        v-tooltip="{
                            content: $i18n.get('label_new_child'),
                            autoHide: true,
                            classes: ['tooltip', 'repository-tooltip'],
                            placement: 'bottom'
                        }"
                        class="icon">
                    <i class="tainacan-icon tainacan-icon-20px tainacan-icon-add"/>
                </span>
            </a>
            <a
                    @click.prevent="editTerm()">
                <span
                        v-tooltip="{
                            content: $i18n.get('edit'),
                            autoHide: true,
                            classes: ['tooltip', 'repository-tooltip'],
                            placement: 'bottom'
                        }"
                        class="icon">
                    <i class="tainacan-icon tainacan-icon-20px tainacan-icon-edit"/>
                </span>
            </a>
            <a @click.prevent="tryToRemoveTerm()">
                <span
                        v-tooltip="{
                            content: $i18n.get('delete'),
                            autoHide: true,
                            classes: ['tooltip', 'repository-tooltip'],
                            placement: 'bottom'
                        }"
                        class="icon">
                    <i class="tainacan-icon tainacan-icon-20px tainacan-icon-delete"/>
                </span>
            </a>
        </span>
    </div>

    <transition-group 
            class="children-area"
            name="filter-item">
        <div    
                class="term-item"
                :style="{
                    'border-left-color': term.parent > 0 && childTerm.parent > 0 ? '#f2f2f2' : 'transparent'
                }"
                :class="{
                    'opened-term': childTerm.opened,
                }" 
                v-for="(childTerm, childIndex) in term.children"
                :key="childTerm.id"
                v-if="showChildren">
            
            <recursive-term-item
                    :term="childTerm"
                    :index="childIndex"
                    :taxonomy-id="taxonomyId"
                    :order="order"/>
        </div>
    </transition-group>
    <a 
            class="view-more-terms"
            :class="{'is-disabled': isEditingTerm}"
            @click="offset = offset + maxTerms; loadChildTerms(term.id)"
            v-if="showChildren && term.children != undefined && (totalTerms > term.children.length)">
        {{ $i18n.get('label_view_more') + ' (' + Number(totalTerms - term.children.length) + ' ' + $i18n.get('terms') + ')' }}
    </a>
</div>
</template>

<script>
import { mapActions } from 'vuex';
import RecursiveTermItem from './recursive-term-item.vue';
import CustomDialog from '../other/custom-dialog.vue';

export default {
    name: 'RecursiveTermItem',
    data(){
        return {
            isLoadingTerms: false,
            isEditingTerm: false,
            searchQuery: '',
            showChildren: false,
            maxTerms: 100,
            offset: 0,
            totalTerms: 0
        }
    },
    props: {
        term: Object,
        index: Number,
        taxonomyId: Number,
        order: String,
    },
    components: {
        RecursiveTermItem,
    },
    methods: {
        ...mapActions('taxonomy', [
            'updateChildTerm',
            'deleteChildTerm',
            'fetchChildTerms',
            'clearTerms',
            'updateChildTermLocal'
        ]),
        teste() {
            this.totalTerms = this.totalTerms - 1;
        },
        addNewChildTerm() {
            this.showChildren = true;
            this.$termsListBus.onAddNewChildTerm(this.term.id);
        },       
        toggleShowChildren() {
            if (!this.isLoadingTerms && (this.term.children == undefined || this.term.children.length <= 0)) {
                this.loadChildTerms(this.term.id);
            } else {
                this.showChildren = !this.showChildren;
            } 
        }, 
        loadChildTerms(parentId) {

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
                    number: this.maxTerms
                })
                .then((resp) => {
                    this.isLoadingTerms = false;
                    this.showChildren = true;   
                    this.totalTerms = resp.total;
                })
                .catch((error) => {
                    this.isLoadingTerms = false;   
                    this.$console.log(error);
                });
           
        },
        editTerm() {
                    
            this.term.opened = !this.term.opened;
            
            this.$termsListBus.onEditTerm(this.term);
        
        },
        tryToRemoveTerm() {

            // Checks if user is deleting a term with unsaved info.
            if (this.term.id == 'new') {
                this.$modal.open({
                    parent: this,
                    component: CustomDialog,
                    props: {
                        icon: 'alert',
                        title: this.$i18n.get('label_warning'),
                        message: this.$i18n.get('info_warning_terms_not_saved'),
                        onConfirm: () => { this.removeTerm(); },
                    }
                });  
            } else {
                this.removeTerm();
            }

        },
        removeTerm() {

            this.$modal.open({
                parent: this,
                component: CustomDialog,
                props: {
                    icon: 'alert',
                    title: this.$i18n.get('label_warning'),
                    message: this.$i18n.get('info_warning_selected_term_delete'),
                    onConfirm: () => { 
                        
                        // If all checks passed, term can be deleted   
                        this.deleteChildTerm({
                                taxonomyId: this.taxonomyId, 
                                termId: this.term.id, 
                                parent: this.term.parent })
                            .then(() => {
                                this.$root.$emit('onChildTermDeleted', this.term.parent);
                            })
                            .catch((error) => {
                                this.$console.log(error);
                            });

                        // Updates parent IDs for orphans
                        if (this.term.children != undefined && this.term.children.length > 0) {
                            for (let orphanTerm of this.term.children) { 
                                this.updateChildTermLocal({ 
                                    term: orphanTerm, 
                                    parent: this.term.parent, 
                                    oldParent: this.term.id
                                });                     
                            } 
                        }    
                    },
                }
            });  
        },
        eventOnChildTermDeleted(parentTermId) {
            if (this.term.id == parentTermId && this.totalTerms > 0) {
                this.totalTerms--;
                this.loadChildTerms(parentTermId);
            }
            
        },
        eventOnEditTerm() {
            this.isEditingTerm = true;
        },
        eventOnTermEditionSaved($event) {
            if (this.term.id == $event.term.id) {
                    this.$set(this.term, 'description', $event.term.description);
                    this.$set(this.term, 'header_image', $event.term.header_image);
                    this.$set(this.term, 'header_image_id', $event.term.header_image_id);
                    this.$set(this.term, 'name', $event.term.name);
                    this.$set(this.term, 'parent', $event.term.parent);
                    this.$set(this.term, 'id', $event.term.id);
            } else if (this.term.children != undefined) {
                for (let i = 0; i < this.term.children.length; i++) {
                    if (this.term.children[i].id == $event.term.id) {
                        this.$set(this.term.children[i], 'description', $event.term.description);
                        this.$set(this.term.children[i], 'header_image', $event.term.header_image);
                        this.$set(this.term.children[i], 'header_image_id', $event.term.header_image_id);
                        this.$set(this.term.children[i], 'name', $event.term.name);
                        this.$set(this.term.children[i], 'parent', $event.term.parent);
                        this.$set(this.term.children[i], 'id', $event.term.id);
                    } 
                }
            }

            this.isEditingTerm = false;
            this.term.opened = false;
        },
        eventOnTermEditionCanceled() {
            this.isEditingTerm = false;
            this.term.opened = false;
        }
    },
    created() { 
        this.$root.$on('onChildTermDeleted', this.eventOnChildTermDeleted);
        this.$termsListBus.$on('editTerm', this.eventOnEditTerm);
        this.$termsListBus.$on('termEditionSaved', this.eventOnTermEditionSaved);
        this.$termsListBus.$on('termEditionCanceled', this.eventOnTermEditionCanceled);        
    },
    beforeDestroy() { 
        this.$root.$off('onChildTermDeleted', this.eventOnChildTermDeleted);
        this.$termsListBus.$off('editTerm', this.eventOnEditTerm);
        this.$termsListBus.$off('termEditionSaved', this.eventOnTermEditionSaved);
        this.$termsListBus.$off('termEditionCanceled', this.eventOnTermEditionCanceled);        
    }
}
</script>

<style lang="scss" scoped>
    @import "../../scss/_variables.scss";

    // Term Item
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
        width: 100%;

        & .term-item:first-child:hover {
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
            left: -21px;
            top: 1px;
            font-size: 24px;
        }
        .children-dropdown {
            color: $blue4;
            position: absolute;
            left: 5px;
            cursor: pointer;
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
            margin-left: 1rem;
            margin-right: auto;
        }
        .children-counter {
            margin-left: 1rem;
            margin-right: auto;
            color: $gray4;
            padding-right: 1rem;
            white-space: nowrap;
            overflow: hidden;
        }
        .not-saved {
            font-style: italic;
            font-weight: bold;
            color: $danger;
        }
        .controls { 
            visibility: hidden;
            opacity: 0.0;
            display: flex;
            justify-content: space-between;
            background-color: $gray2;
            padding: 0.5rem 0.875rem;

            a {
                display: flex;
                align-items: center;
                margin: 0 0.375rem;
                .icon {
                    bottom: 1px;   
                    position: relative;
                    i, i:before { font-size: 20px; }
                }
            }            
        }
        .controls.is-disabled a, .children-dropdown i.is-disabled {
            color: $gray4 !important;
            cursor: not-allowed !important;
            user-select: none;
        }

        &.opened-term>div:first-child>div {
            cursor: default;
            background-color: $gray1;

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

        &.collapsed-term {
            display: none;
            visibility: hidden;
            opacity: 0;
        }
    }
    .view-more-terms {
        font-size: 0.875rem;
        margin: 0 0 0 1.75rem !important;
        padding: 0.5rem 0 0.5rem 1.75rem;
        display: flex;
        border-top: 1px solid #f2f2f2;
    }
</style>
