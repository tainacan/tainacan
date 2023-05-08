<template>
    <div class="tainacan-form">

        <div class="search-and-selection-control"> 

            <!-- Search input -->
            <b-field class="is-clearfix terms-search">
                <b-input
                        expanded
                        autocomplete="on"
                        :placeholder="$i18n.get('instruction_search')"
                        :aria-name="$i18n.get('instruction_search')"
                        v-model="searchString"
                        icon-right="magnify"
                        type="search" />
            </b-field>
            
            <span
                    class="selected-terms-info"
                    v-if="selectedColumnIndex >= 0 && currentUserCanEditTaxonomy">
                {{ selectedColumnIndex == 0 ? $i18n.get('label_all_root_terms_selected') : $i18n.getWithVariables('label_terms_child_of_%s_selected', [ selectedColumnObject.name ]) }}
                <button
                        type="button"
                        class="link-style"
                        @click="selectedColumnIndex = -1">
                    <span class="icon">
                        <i class="tainacan-icon tainacan-icon-close" />
                    </span>
                </button>
            </span>
            <div 
                    v-if="selected.length && selectedColumnIndex < 0"
                    class="field selected-terms-info">
                <b-dropdown
                        :mobile-modal="true"
                        id="selected-terms-dropdown"
                        aria-role="list"
                        trap-focus
                        position="is-bottom-left">
                    <button
                            type="button"
                            class="button is-white"
                            slot="trigger">
                        <span>{{ selected.length == 1 ? $i18n.get('label_one_selected_term') : $i18n.getWithVariables('label_%s_selected_terms', [ selected.length ]) }}</span>
                        <span class="icon">
                            <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-arrowdown"/>
                        </span>
                    </button>
                    <b-dropdown-item
                            custom
                            v-for="term of selected"
                            :key="term.id"
                            aria-role="list-item">
                        <label class="b-checkbox checkbox">
                            <input
                                type="checkbox"
                                @input="updateSelectedTerms(term)"
                                :checked="isTermSelected(term.id)"
                                :value="getTermIdAsNumber(term.id)">
                            <span class="check" /> 
                            <span class="control-label">
                                <span 
                                        class="checkbox-name-text"
                                        v-html="renderTermHierarchyLabel(term)" /> 
                            </span>
                        </label>
                    </b-dropdown-item>
                </b-dropdown>
            </div>

            <div 
                    v-if="currentUserCanEditTaxonomy"
                    class="field">
                <b-dropdown
                        :mobile-modal="true"
                        position="is-bottom-left"
                        :disabled="amountOfTermsSelected <= 1"
                        id="bulk-actions-dropdown"
                        aria-role="list"
                        trap-focus>
                    <button
                            type="button"
                            class="button is-white"
                            slot="trigger">
                        <span>{{ $i18n.get('label_actions_for_the_selection') }}</span>
                        <span class="icon">
                            <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-arrowdown"/>
                        </span>
                    </button>
                    <b-dropdown-item
                            @click="$emit('deleteSelectedTerms')"
                            id="item-delete-selected-terms"
                            aria-role="listitem">
                        {{ $i18n.get('label_delete_permanently') }}
                    </b-dropdown-item>
                    <b-dropdown-item
                            v-if="isHierarchical"
                            @click="$emit('updateSelectedTermsParent')"
                            id="item-update-selected-terms"
                            aria-role="listitem">
                        {{ $i18n.get('label_update_parent') }}
                    </b-dropdown-item>
                </b-dropdown>
            </div>
        </div>

        <!-- Terms list with hierarchy -->
        <terms-list-hierarchical 
                :is-hierarchical="isHierarchical"
                :search-string="searchString"
                :taxonomy-id="taxonomyId"
                :current-user-can-edit-taxonomy="currentUserCanEditTaxonomy"
                :selected="selected"
                :selected-column-index="selectedColumnIndex"
                @onUpdateSelectedTerms="(newSelected) => selected = newSelected"
                @onUpdateSelectedColumnIndex="(newColumnSelected) => { selectedColumnIndex = newColumnSelected.index; selectedColumnObject = newColumnSelected.object; }"
         />
    </div>
</template>

<script>
import TermsListHierarchical from './terms-list-hierarchical.vue';

export default {
    name: 'TermsList',
    components: {
        TermsListHierarchical
    },
    props: {
        taxonomyId: Number,
        currentUserCanEditTaxonomy: Boolean,
        isHierarchical: Boolean
    },
    data() {
        return {
            searchString: '',
            selected: [],
            selectedColumnIndex: -1,
            selectedColumnObject: null
        }
    },
    computed: {
        amountOfTermsSelected() {
            if ( this.selectedColumnIndex >= 0 )
                return this.selectedColumnObject.total_children;
            else if ( this.selected.length )
                return this.selected.length;
            else
                return 0;
        }
    },
    methods: {
        renderTermHierarchyLabel(term) {
            if ( term.hierarchy_path ) 
                return '<span style="color: var(--tainacan-info-color);">' + term.hierarchy_path.replace(/>/g, '&nbsp;<span class="hierarchy-separator"> &gt; </span>&nbsp;') + '</span>' + term.name;

            return term.name;
        }, 
        isTermSelected(termId) {
            return this.selected.findIndex(aSelectedTerm => aSelectedTerm.id == termId) >= 0;
        },
        getTermIdAsNumber(termId) {
            return isNaN(Number(termId)) ? termId : Number(termId)
        },
        updateSelectedTerms(selectedTerm) {
            this.selectedColumnIndex = -1;

            let currentSelected = JSON.parse(JSON.stringify(this.selected));
            
            const existingValueIndex = this.selected.findIndex(aSelectedTerm => aSelectedTerm.id == selectedTerm.id);
            
            if (existingValueIndex >= 0)
                currentSelected.splice(existingValueIndex, 1);
            else
                currentSelected.push(selectedTerm);

            this.selected = currentSelected;
        } 
    }
}
</script>

<style lang="scss" scoped>

    .search-and-selection-control {
        margin: 0.25em 0 0.5em 0;
        padding: 0px;
        display: flex;
        align-items: center;
        justify-content: space-between;

        .terms-search {
            margin: 0px 0.5em 0px 0px !important;
            padding: 0px !important;

            .control {
                margin: 0;
            }
            /deep/ .input {
                height: 0.875em;
            }
            .input .icon .mdi::before {
                color: var(--tainacan-input-color);
            }
            .button {
                border-radius: 0 !important;
                min-height: 100%;
                background-color: var(--tainacan-input-background-color);
                border: 1px solid var(--tainacan-input-border-color);
                transition: background 0.2s ease;
            }
            .button.is-active {
                background-color: var(--tainacan-blue5);

                .tainacan-icon::before {
                    color: var(--tainacan-secondary)
                }
            } 
            /deep/ .field-body>.field {
                padding: 0px !important;
                margin-left: 0px !important;
            }
        }

        .selected-terms-info {
            margin: 0 0.5em 0 auto;
            font-size: 0.875em;
            color: var(--tainacan-info-color);

            .link-style {
                border-radius: 36px;
                &:hover {
                    background-color: var(--tainacan-gray1) !important;
                }
            }

            #selected-terms-dropdown {
                /deep/ .dropdown-trigger {
                    font-size: 1.125em !important;
                }
                /deep/ .dropdown-menu {
                    width: max-content;
                    max-width: 380px;
                }
                .checkbox-name-text {
                    font-size: 1.375em !important;
                }
            }

            &:not(.field) {
                border: 1px solid var(--tainacan-input-border-color);
                padding: 0.2rem 0.5rem;
            }
        }
    }

    .tainacan-form {
        margin-top: 0px;
        max-width: 100%;

        a:not(:disabled),
        button:not(:disabled) {
            color: var(--tainacan-blue5);
            cursor: pointer;
        }

        .form-submit {
            padding-top: 16px !important;
        }
    }

    .tainacan-li-checkbox-list {
        flex-grow: 0;
        flex-shrink: 1;
        max-width: 100%;
        padding-left: 0.5em;
        margin: 0;
        -webkit-break-inside: avoid;
        break-inside: avoid;
        display: flex;
        justify-content: space-between;
        align-items: stretch;
        min-height: 1.5em;

        /deep/ .b-checkbox, /deep/ .b-radio {
            margin-right: 0px;
            margin-bottom: 0;
            -webkit-break-inside: avoid;
            break-inside: avoid;

            .control-label {
                white-space: normal;
                overflow: visible;
                padding-top: 0.125em;
                padding-bottom: 0.125em;
                word-break: break-word;
            }
            input:disabled+.check {
                cursor: not-allowed;
                opacity: 0.5;
            }
            @media screen and (max-width: 768px) {
                .control-label {
                    padding-top: 0.8125em;
                    padding-bottom: 0.8125em;
                    padding-left: calc(0.875em - 1px);
                    width: 100%;
                    border-bottom: 1px solid var(--tainacan-gray1);
                }
            }
        }

        &>a:not(.add-link),
        &>button:not(.add-link) {
            opacity: 0.0;
            transition: opacity 0.2s ease;       
        }
        button.load-children-button {
            opacity: 0.95;
            border-left: 1px dashed var(--tainacan-gray1);
        }
        &:hover:not(.result-info) {
            background-color: var(--tainacan-gray1);

            &>a:not(.add-link),
            &>button:not(.add-link) {
                opacity: 1.0;
                background-color: var(--tainacan-gray2);
            }
        }
        &.result-info {
            padding: 0.5rem 0.25rem 0.25rem 0.25rem;
            width: 100%;
            max-width: 100%;
            column-span: all;
            font-size: 0.75em;
            color: var(--tainacan-info-color);
            text-align: center;
        }
    }

    .tainacan-hierarchical-list-columns-container {
        background-color: var(--tainacan-background-color);
        border: 1px solid var(--tainacan-gray2);
        border-radius: 2px;
        margin-top: 0px;
        display: flex;
        height: auto;
        overflow: auto;
        scroll-snap-type: x mandatory;
        scroll-snap-align: start;
        padding: 0 !important;
        //max-height: 42vh;
        transition: heigth 0.5s ease, min-height 0.5s ease;

        &:focus {
            outline: none;
        }
    }

    .tainacan-hierarchical-list-container {
        position: relative;

        .scroll-back-to-root-button {
            position: absolute;
            top: 0;
            left: 0;
            z-index: 9;
            background-color: var(--tainacan-background-color);
            border: 1px solid var(--tainacan-gray2);
            border-top-left-radius: 0;
            border-top-right-radius: 0;
            border-bottom-left-radius: 0;
            border-bottom-right-radius: 4px;
            padding: 0.55rem 0.8rem;
            white-space: nowrap;
            display: block;
            font-weight: bold;
            color: var(--tainacan-label-color);
            font-size: 0.8rem;
        }
    }

    .tainacan-hierarchical-list-column {
        border-right: solid 1px var(--tainacan-gray2);        
        flex-basis: auto;
        flex-grow: 1;
       // max-width: 720px;
        min-width: 268px;
        margin: 0;
        padding: 0em;
        transition: width 0.2s ease;

        &:only-child {
            max-width: 100%;
            border-right: none;
        }

        &:last-child {
            border-right: none;
        }

        &.has-only-one-column {
            max-width: 100%;
            border-right: none;

            ul {
                -moz-column-count: 2;
                -moz-column-gap: 0;
                -moz-column-rule: none;
                -webkit-column-count: 2;
                -webkit-column-gap: 0;
                -webkit-column-rule: none;
                column-count: 2;
                column-gap: 2em;
                column-rule: none;
                overflow-y: hidden;
                overflow-x: auto;
            }
        }

        ul {
            max-height: calc(42vh - 20px - 0.7em);
            min-height: inherit;
            overflow-y: auto;
            overflow-x: hidden;
            list-style: none;
            margin: 0;
            padding-left: 0;
            box-shadow: inset 0px 4px 10px -12px #000;
        }
        a:not(.add-link),
        button:not(.add-link) {
            border: none;
            background: transparent;
            font-size: 0.75em;
            white-space: nowrap;
            display: flex;
            align-items: center;
            padding: 8px 0.5rem;
            opacity: 0.0;
            transition: opacity 0.2s ease;     

            .tainacan-icon {
                font-size: 1.5em;
            }
        }
        button.load-children-button {
            opacity: 0.95;
            border-left: 1px solid var(--tainacan-gray2);
        }

        .column-header {
            color: var(--tainacan-label-color);
            padding: 0.45em 0.75em;
            margin: 0;
            position: relative;
            border-bottom: 1px solid var(--tainacan-gray2);
            display: flex;
            flex-direction: column;

            .column-name {
                display: flex;
                font-weight: bold;
                flex-wrap: nowrap;
                justify-content: space-between;
                align-items: baseline;
                margin-bottom: 0.5em;
            }

            .add-link {
                font-weight: normal;
                margin: 0 0 auto 0;
                font-size: 0.9375em;
                overflow: initial;
            }

            .column-subheader {
                display: flex;
                flex-wrap: nowrap;
                justify-content: space-between;

                .checkbox {
                    margin-bottom: 0;
                    margin-top: 0.25;
                    font-size: 1.25em;
                    width: auto;
                }
            }
        }

        &:not(:first-child) .column-header {

            .column-name {
                padding-left: calc(0.75em + 12px);
            }

            &::after,
            &::before {
                content: '';
                display: block;
                position: absolute;
                right: 100%;
                width: 0;
                height: 0;
                border-style: solid;
            }
            &::after {
                top: 0px;
                border-color: transparent transparent transparent white;
                border-left-width: 12px;
                border-top-width: calc(1em + 1px);
                border-bottom-width: calc(1em + 0px);
                left: -2px;
            }
            &::before {
                top: 0px;
                border-color: transparent transparent transparent var(--tainacan-gray2);
                border-left-width: 12px;
                border-top-width: calc(1em + 1px);
                border-bottom-width: calc(1em + 0px);
                left: -1px;
            }
        }
        
    }

    ul {
        // For Safari
        -webkit-margin-after: 0;
        -webkit-margin-start: 0;
        -webkit-margin-end: 0;
        -webkit-padding-start: 0;
        -webkit-margin-before: 0;
    }

    .field:not(:last-child) {
        margin-bottom: 0 !important;
    }

    .b-checkbox .control-label {
        display: flex;
        flex-wrap: nowrap;
        align-items: center;
        width: 100%;
        overflow: visible !important;
        white-space: normal !important;

        .checkbox-name-text {
            line-height: 1.25em;
            padding-right: 3px;
            break-inside: avoid;
        }
    }

</style>


 
