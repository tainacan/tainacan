<template>
<div 
        style="width: 100%;">
    <div
            class="term-item"
            :class="{
                'opened-term': term.opened
            }">
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
                    v-if="term.id == 'new'"> 
                {{ $i18n.get('info_not_saved') }}
            </span>
        </span>
        <span 
                class="controls" 
                :class="{'is-disabled': isEditingTerm}">
            <a
                    @click.prevent="editTerm()">
                <span
                        v-tooltip="{
                            content: $i18n.get('edit'),
                            autoHide: true,
                            placement: 'auto'
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
                            placement: 'auto'
                        }"
                        class="icon">
                    <i class="tainacan-icon tainacan-icon-20px tainacan-icon-delete"/>
                </span>
            </a>
        </span>
    </div>
</div>
</template>

<script>
import { mapActions } from 'vuex';
import CustomDialog from '../other/custom-dialog.vue';

export default {
    name: 'RecursiveTermItem',
    data(){
        return {
            isLoadingTerms: false,
            isEditingTerm: false,
        }
    },
    props: {
        term: Object,
        index: Number,
        taxonomyId: Number,
        order: String,
    },
    methods: {
        ...mapActions('taxonomy', [
            'updateTerm',
            'deleteTerm'
        ]),
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
                        this.$termsListBus.onDeleteBasicTermItem(this.term);
                    },
                }
            });  
        },
        eventOnEditTerm() {
            this.isEditingTerm = true;
        },
        eventOnTermEditionSaved() {
            this.isEditingTerm = false;
            this.term.opened = false;
        },
        eventOnTermEditionCanceled() {
            this.isEditingTerm = false;
            this.term.opened = false;
        }
    },
    created() {
        this.$termsListBus.$on('editTerm', this.eventOnEditTerm);
        this.$termsListBus.$on('termEditionSaved', this.eventOnTermEditionSaved);
        this.$termsListBus.$on('termEditionCanceled', this.eventOnTermEditionCanceled);        
    },
    beforeDestroy() {
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
        transition: display 0.3s, visibility 0.3s, opacity 0.3s;
        width: 100%;

        &:first-child:hover {
            background-color: $gray1 !important;
            .controls {
                visibility: visible;
                opacity: 1.0;
            }
            &::before {
                border-color: transparent transparent transparent $gray2 !important;
            }
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
        .controls.is-disabled a {
            color: $gray4 !important;
            cursor: not-allowed !important;
            user-select: none;
        }

        &.opened-term:first-child {
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
    .view-more-terms {
        font-size: 0.875rem;
        margin: 0 0 0 1.75rem !important;
        padding: 0.5rem 0 0.5rem 1.75rem;
        display: flex;
        border-top: 1px solid #f2f2f2;
    }
</style>
