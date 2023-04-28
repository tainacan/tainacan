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
                        @input="autoComplete"
                        icon-right="magnify"
                        type="search" />
            </b-field>
            
            <span
                    class="selected-terms-info"
                    v-if="selectedColumnIndex >= 0 && currentUserCanEditTaxonomy">
                {{ selectedColumnIndex == 0 ? $i18n.get('label_all_root_terms_selected') : $i18n.getWithVariables('label_terms_child_of_%s_selected', [ termColumns[selectedColumnIndex].name ]) }}
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
                        trap-focus>
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
                            @click="deleteSelectedTerms()"
                            id="item-delete-selected-terms"
                            aria-role="listitem">
                        {{ $i18n.get('label_delete_permanently') }}
                    </b-dropdown-item>
                    <b-dropdown-item
                            @click="updateSelectedTermsParent()"
                            id="item-update-selected-terms"
                            aria-role="listitem">
                        {{ $i18n.get('label_update_parent') }}
                    </b-dropdown-item>
                </b-dropdown>
            </div>
        </div>

        <!-- Search Results -->
        <div
                v-if="isSearching"
                class="tainacan-checkbox-list-container">
            <a
                    v-if="searchResultsOffset"
                    role="button"
                    class="tainacan-checkbox-list-page-changer"
                    @click="previousSearchPage">
                <span class="icon">
                    <i class="tainacan-icon tainacan-icon-previous"/>
                </span>
            </a>
            <ul class="tainacan-modal-checkbox-list-body">
                <template v-if="searchResults.length">
                    <li
                            class="tainacan-li-checkbox-list"
                            v-for="(term, index) in searchResults"
                            :key="index">
                        <label class="b-checkbox checkbox">
                            <input
                                    @input="updateSelectedTerms(term)"
                                    :checked="selectedColumnIndex >= 0 ? termColumns[selectedColumnIndex].id == term.parent : isTermSelected(term.id)"
                                    :value="getTermIdAsNumber(term.id)"
                                    :disabled="selectedColumnIndex >= 0"
                                    type="checkbox"> 
                            <span class="check" /> 
                            <span class="control-label">
                                <span 
                                        class="checkbox-name-text"
                                        v-html="renderTermHierarchyLabel(term)" /> 
                            </span>
                        </label>
                        <button 
                                v-if="currentUserCanEditTaxonomy"
                                type="button"
                                @click.prevent="onEditTerm(term)">
                            <span
                                    v-tooltip="{
                                        content: $i18n.get('edit'),
                                        autoHide: true,
                                        popperClass: ['tainacan-tooltip', 'tooltip', 'tainacan-repository-tooltip'],
                                        placement: 'bottom'
                                    }"
                                    class="icon">
                                <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-edit"/>
                            </span>
                        </button>
                        <button
                                v-if="currentUserCanEditTaxonomy"
                                type="button"
                                @click.prevent="removeTerm(term)">
                            <span
                                    v-tooltip="{
                                        content: $i18n.get('delete'),
                                        autoHide: true,
                                        popperClass: ['tainacan-tooltip', 'tooltip', 'tainacan-repository-tooltip'],
                                        placement: 'bottom'
                                    }"
                                    class="icon">
                                <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-delete"/>
                            </span>
                        </button>                             
                    </li>
                </template>
                <template v-if="!isLoadingSearch && !searchResults.length">
                    <li class="tainacan-li-checkbox-list result-info">
                        {{ $i18n.get('info_no_terms_found') }}
                    </li>
                </template>
                <b-loading
                        :is-full-page="false"
                        :active.sync="isLoadingSearch"/>
            </ul>
            <button
                    v-if="hasMoreSearchPages"
                    type="button"
                    class="tainacan-checkbox-list-page-changer"
                    @click="nextSearchPage">
                <span class="icon">
                    <i class="tainacan-icon tainacan-icon-next"/>
                </span>
            </button>
        </div>

        <div 
                v-if="!isSearching"
                class="tainacan-hierarchical-list-container">

            <b-loading
                    :is-full-page="false"
                    :active.sync="isColumnLoading"/>

            <transition name="appear-from-top">
                <button
                        v-if="termColumns.length > 3"
                        type="button"
                        class="scroll-back-to-root-button"
                        @click="scrollBackToRoot()">
                   {{ $i18n.get('label_root_terms') }}
                </button>
            </transition>

            <!-- Hierarchical lists -->
            <transition-group
                    class="tainacan-hierarchical-list-columns-container"
                    name="page-left"
                    ref="tainacan-finder-scrolling-container">
                <div 
                        v-for="(column, columnIndex) in termColumns"
                        class="tainacan-hierarchical-list-column"
                        :key="column.name + '-' + columnIndex">
                    <div class="column-header">
                        <p class="column-name">
                            <span>{{ column.name ? $i18n.getWithVariables('info_children_of_%s', [ column.name ]) : $i18n.get('label_root_terms') }}</span>
                        </p>
                        <p class="column-subheader">
                            <label 
                                    :style="!column.children.length ? 'opacity: 0; visibility: hidden;' : ''"
                                    class="b-checkbox checkbox">
                                <input
                                        type="checkbox"
                                        @input="selectColumn(columnIndex)"
                                        :checked="selectedColumnIndex == columnIndex"
                                        :value="columnIndex"> 
                                <span class="check" /> 
                                <span 
                                        v-if="column.id"
                                        class="control-label">
                                    {{ termColumns.length <= 2 ? $i18n.get('label_select_child_terms_long') : $i18n.get('label_select_child_terms_short') }}
                                </span>
                                <span 
                                        v-else
                                        class="control-label">
                                    {{ termColumns.length <= 2 ? $i18n.get('label_select_root_terms_long') : $i18n.get('label_select_root_terms_short') }}
                                </span>
                            </label>
                            <a 
                                    :style="!column.children.length ? 'opacity: 0; visibility: hidden;' : ''"
                                    @click="onAddNewChildTerm(column.id)"
                                    class="add-link">
                                <span class="icon is-small">
                                    <i class="tainacan-icon has-text-secondary tainacan-icon-add"/>
                                </span>
                                &nbsp;{{ $i18n.get('label_create_term') }}
                            </a>
                        </p>
                    </div>
                    <ul v-if="column.children.length">
                        <b-field
                                :addons="false"
                                class="tainacan-li-checkbox-modal"
                                :class="{ 'tainacan-li-checkbox-parent-active': termColumns[columnIndex + 1] && termColumns[columnIndex + 1].id == term.id }"
                                v-for="(term, index) in column.children"
                                :id="`${columnIndex}.${index}-tainacan-li-checkbox-model`"
                                :ref="`${columnIndex}.${index}-tainacan-li-checkbox-model`"
                                :key="term.id">
                            <label class="b-checkbox checkbox" >
                                <input 
                                        @input="updateSelectedTerms(term)"
                                        :checked="selectedColumnIndex >= 0 ? selectedColumnIndex == columnIndex : isTermSelected(term.id)"
                                        :disabled="selectedColumnIndex >= 0"
                                        :value="term.id"
                                        type="checkbox"> 
                                <span class="check" /> 
                                <span class="control-label">{{ term.name }}</span>
                            </label>
                            <button 
                                    v-if="currentUserCanEditTaxonomy"
                                    type="button"
                                    @click.prevent="onEditTerm(term)">
                                <span
                                        v-tooltip="{
                                            content: $i18n.get('edit'),
                                            autoHide: true,
                                            popperClass: ['tainacan-tooltip', 'tooltip', 'tainacan-repository-tooltip'],
                                            placement: 'bottom'
                                        }"
                                        class="icon">
                                    <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-edit"/>
                                </span>
                            </button>
                            <button 
                                    v-if="currentUserCanEditTaxonomy"
                                    type="button"
                                    @click.prevent="removeTerm(term)">
                                <span
                                        v-tooltip="{
                                            content: $i18n.get('delete'),
                                            autoHide: true,
                                            popperClass: ['tainacan-tooltip', 'tooltip', 'tainacan-repository-tooltip'],
                                            placement: 'bottom'
                                        }"
                                        class="icon">
                                    <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-delete"/>
                                </span>
                            </button>
                            <button
                                    class="load-children-button"
                                    type="button"
                                    @click="fetchTerms(term, columnIndex)">
                                <span 
                                        style="margin-right: 0.25rem; opacity: 1.0;"
                                        class="icon">
                                    <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-nextlevel"/>
                                </span>
                                <span 
                                        class="is-hidden-mobile"
                                        v-if="termColumns.length <= 1 ">
                                    {{ term.total_children + ' ' + $i18n.get('label_children_terms') }}
                                </span>
                                <span 
                                        v-tooltip="{
                                            content: term.total_children + ' ' + $i18n.get('label_children_terms'),
                                            autoHide: false,
                                            popperClass: ['tainacan-tooltip', 'tooltip']
                                        }" 
                                        v-else>
                                    {{ term.total_children }}
                                </span>
                            </button>
                        </b-field>
                        <li v-if="column.children.length">
                            <div
                                    v-if="shouldShowMoreButton(columnIndex)"
                                    @click="fetchMoreTerms(column, columnIndex)"
                                    class="tainacan-show-more">
                                <span class="icon">
                                    <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-showmore"/>
                                </span>
                            </div>
                        </li>
                    </ul>
                    <div
                            v-else
                            class="warning-no-more-terms">
                        <p>
                            <span class="icon is-medium">
                                <i class="tainacan-icon tainacan-icon-30px tainacan-icon-terms"/>
                            </span>
                        </p>
                        <p>{{ column.name ? $i18n.getWithVariables('info_no_child_term_of_%s_found', [ column.name ]) : $i18n.get('info_no_terms_found') }}</p>
                        <p>
                            <a 
                                    @click="onAddNewChildTerm(column.id)"
                                    class="add-link">
                                <span class="icon is-small">
                                    <i class="tainacan-icon has-text-secondary tainacan-icon-add"/>
                                </span>
                                &nbsp;{{ $i18n.get('label_create_term') }}
                            </a>
                        </p>
                    </div>
                </div>
            </transition-group>

        </div>

        <section 
                v-if="( termColumns instanceof Array ? termColumns.length <= 0 : !termColumns ) && !isSearching && !isLoadingSearch && !isColumnLoading"
                class="section">
            <div class="content has-text-grey has-text-centered">
                <p>
                    <span class="icon is-medium">
                        <i class="tainacan-icon tainacan-icon-30px tainacan-icon-terms" />
                    </span>
                </p>
                <p>{{ $i18n.get('info_no_terms_found') }}</p>
                <p>
                    <a 
                            @click="onAddNewChildTerm(0)"
                            class="add-link">
                        <span class="icon is-small">
                            <i class="tainacan-icon has-text-secondary tainacan-icon-add"/>
                        </span>
                        &nbsp;{{ $i18n.get('label_create_term') }}
                    </a>
                </p>
            </div>
        </section>
        
        <b-modal
                v-model="isEditingTerm"
                :width="768"
                trap-focus
                aria-role="dialog"
                aria-modal
                :can-cancel="['outside', 'escape']"
                custom-class="tainacan-modal"
                :close-button-aria-label="$i18n.get('close')">
            <term-edition-form 
                    :style="{ 'top': termEditionFormTop + 'px'}"
                    :taxonomy-id="taxonomyId"
                    :is-modal="true"
                    @onEditionFinished="onTermEditionFinished($event.term, $event.hasChangedParent, $event.initialParent)"
                    :original-form="editTerm" />
        </b-modal>
    </div>
</template>

<script>
    import { tainacan as axios } from '../../js/axios';
    import { mapActions } from 'vuex';
    import TermDeletionDialog from '../other/term-deletion-dialog.vue';
    import TermParentSelectionDialog from '../other/term-parent-selection-dialog.vue';

    export default {
        name: 'CheckboxRadioMetadataInput',
        props: {
            taxonomyId: Number,
            currentUserCanEditTaxonomy: Boolean
        },
        data() {
            return {
                termColumns: [],
                isColumnLoading: false,
                totalRemaining: {},
                searchResults: [],
                searchString: '',
                isSearching: false,
                maxSearchResultsPerPage: 20,
                maxTermsPerColumn: 100,
                searchResultsOffset: 0,
                isLoadingSearch: false,
                hasMoreSearchPages: true,
                isEditingTerm: false,
                editTerm: null,
                selected: [],
                selectedColumnIndex: -1
            }
        },
        computed: {
            amountOfTermsSelected() {
                if ( this.selectedColumnIndex >= 0 )
                   return this.termColumns[this.selectedColumnIndex].total_children;
                else if ( this.selected.length )
                    return this.selected.length;
                else
                    return 0;
            }
        },
        watch: {
            searchString(newValue, oldValue) {
                if (newValue != oldValue) {
                    this.hasMoreSearchPages = true;
                    this.searchResultsOffset = 0;
                }
            },
        },
        created() {
            this.fetchTerms();
        },
        methods: {
            ...mapActions('taxonomy', [
                'updateTerm',
                'deleteTerm',
                'deleteTerms',
            ]),
            shouldShowMoreButton(columnIndex) {
                return this.totalRemaining[columnIndex].remaining === true || (this.termColumns[columnIndex].children.length < this.totalRemaining[columnIndex].remaining);
            },
            previousSearchPage() {

                this.hasMoreSearchPages = true;

                this.searchResultsOffset -= this.maxSearchResultsPerPage;
                if ( this.searchResultsOffset < 0 )
                    this.searchResultsOffset = 0;

                this.autoComplete();
                
            },
            nextSearchPage() {

                if ( this.hasMoreSearchPages ) {
                    if ( this.searchResultsOffset === this.maxSearchResultsPerPage )
                        this.searchResultsOffset += this.maxSearchResultsPerPage - 1;
                    else
                        this.searchResultsOffset += this.maxSearchResultsPerPage;
                }
                
                this.autoComplete();
            },
            autoComplete: _.debounce( function () {
                
                this.isSearching = !!this.searchString.length;
                
                if (!this.isSearching)
                    return;

                this.isLoadingSearch = true;

                const query = `?order=asc&number=${this.maxSearchResultsPerPage}&searchterm=${this.searchString}&hideempty=0&offset=${this.searchResultsOffset}`;

                const route = `/taxonomy/${this.taxonomyId}/terms/${query}`;
                axios.get(route)
                    .then((res) => {
                        this.searchResults = res.data;
                        this.isLoadingSearch = false;
                        
                        if (res.headers && res.headers['x-wp-total'])
                            this.hasMoreSearchPages = res.headers['x-wp-total'] > (this.searchResultsOffset + this.searchResults.length);

                    })
                    .catch((error) => {
                        this.$console.log(error);
                    });
                
            }, 500),
            removeLevelsAfter(parentColumnIndex){
                if (parentColumnIndex != undefined)
                    this.termColumns.length = parentColumnIndex + 1;
            },
            createColumn(res, column, name, id) {
                let children = res.data;
                let first = undefined;

                if (children.length > 0) {
                    for (let column in this.termColumns) {
                        if (this.termColumns[column].children[0].id == children[0].id) {
                            first = column;
                            break;
                        }
                    }
                }
                
                if (first != undefined)
                    this.termColumns.splice(first, 1, { name: name, id: id, children: children, total_children: res.headers['x-wp-total'] });
                else
                    this.termColumns.push({ name: name, id: id, children: children, total_children: res.headers['x-wp-total'] });

                this.$nextTick(() => {
                    setTimeout(() => {
                        if (
                            this.$refs &&
                            this.$refs[`${column}.0-tainacan-li-checkbox-model`] &&
                            this.$refs[`${column}.0-tainacan-li-checkbox-model`][0] &&
                            this.$refs[`${column}.0-tainacan-li-checkbox-model`][0].$el &&
                            this.$refs['tainacan-finder-scrolling-container'] &&
                            this.$refs['tainacan-finder-scrolling-container'].$el
                        ) {
                            // Scroll Into does not solve as it would scroll vertically as well...
                            //this.$refs[`${column}.0-tainacan-li-checkbox-model`][0].$el.scrollIntoView({ behavior: "smooth", inline: "start" });

                            this.$refs['tainacan-finder-scrolling-container'].$el.scrollTo({
                                top: 0,
                                left: first != undefined ? 0 : this.$refs[`${column}.0-tainacan-li-checkbox-model`][0].$el.offsetLeft,
                                behavior: 'smooth'
                            });
                        }
                    }, 500);
                }); 
            },
            scrollBackToRoot() {
                if (
                    this.$refs &&
                    this.$refs['tainacan-finder-scrolling-container'] &&
                    this.$refs['tainacan-finder-scrolling-container'].$el
                ) {
                    this.$refs['tainacan-finder-scrolling-container'].$el.scrollTo({
                        top: 0,
                        left: 0,
                        behavior: 'smooth'
                    });
                }
            },
            appendMoreTermsToColumn(terms, columnIndex) {
                for (let term of terms)
                    this.termColumns[columnIndex].children.push(term);
            },
            fetchTerms(parentTerm, parentColumnIndex) {

                this.isColumnLoading = true;

                const parentId = parentTerm ? parentTerm.id : 0;
                const route = `/taxonomy/${this.taxonomyId}/terms?order=asc&parent=${parentId}&number=${this.maxTermsPerColumn}&offset=0&hideempty=0`;
                axios.get(route)
                    .then(res => {
                        
                        this.totalRemaining = Object.assign({}, this.totalRemaining, {
                            [`${parentColumnIndex == undefined ? 0 : parentColumnIndex + 1}`]: {
                                remaining: res.headers['x-wp-total'],
                            }
                        });

                        this.removeLevelsAfter(parentColumnIndex);
                        this.createColumn(res, parentColumnIndex, parentTerm ? parentTerm.name : null, parentId);

                        this.isColumnLoading = false;
                    })
                    .catch(error => {
                        this.$console.log(error);
                        this.isColumnLoading = false;
                    });

            },
            fetchMoreTerms(column, parentColumnIndex) {
                if (column.children && column.children.length > 0) {

                    const parentId = column.children[0].parent;
                    const offset = column.children.length;

                    this.isColumnLoading = true;

                    const route = `/taxonomy/${this.taxonomyId}/terms/?order=asc&parent=${parentId}&number=${this.maxTermsPerColumn}&offset=${offset}&hideempty=`;
                    axios.get(route)
                        .then(res => {

                            this.totalRemaining = Object.assign({}, this.totalRemaining, {
                                [`${parentColumnIndex}`]: {
                                    remaining: res.headers['x-wp-total'],
                                }
                            });

                            this.appendMoreTermsToColumn(res.data, parentColumnIndex);
                            
                            this.isColumnLoading = false;
                        })
                        .catch(error => {
                            this.$console.log(error);

                            this.isColumnLoading = false;
                        });
                }
            },
            renderTermHierarchyLabel(term) {
                if ( term.hierarchy_path ) 
                    return '<span style="color: var(--tainacan-info-color);">' + term.hierarchy_path.replace(/>/g, '&nbsp;<span class="hierarchy-separator"> &gt; </span>&nbsp;') + '</span>' + term.name;

                return term.name;
            }, 
            selectColumn(index) {
                this.selectedColumnIndex = this.selectedColumnIndex != index ? index : -1;
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
            },
            onEditTerm(term) {
                this.editTerm = term;
                this.isEditingTerm = true;

                if ( !this.isSearching ) {
                    for (let i = 0; i < this.termColumns.length; i++) {
                        if ( this.termColumns[i].id == this.editTerm.id ) {
                            this.termColumns.splice(i, this.termColumns.length - i);
                            break;
                        }
                    }
                }
            },
            removeTerm(term) {

                this.$buefy.modal.open({
                    parent: this,
                    component: TermDeletionDialog,
                    props: {
                        message: term.total_children && term.total_children != '0' ?  this.$i18n.get('info_warning_term_with_child') : this.$i18n.get('info_warning_selected_term_delete'),
                        showDescendantsDeleteButton: term.total_children && term.total_children != '0',
                        amountOfTerms: 1,
                        onConfirm: (typeOfDelete) => { 
                            
                            // If all checks passed, term can be deleted  
                            if ( typeOfDelete == 'descendants' ) { 
                                this.deleteTerm({
                                        taxonomyId: this.taxonomyId, 
                                        termId: term.id, 
                                        parent: term.parent,
                                        deleteChildTerms: true })
                                    .then(() => {
                                        this.onTermRemovalFinished(term);
                                    })
                                    .catch((error) => {
                                        this.$console.log(error);
                                    });
                            } else { 
                                this.deleteTerm({
                                        taxonomyId: this.taxonomyId, 
                                        termId: term.id, 
                                        parent: term.parent })
                                    .then(() => {
                                        this.onTermRemovalFinished(term);
                                    })
                                    .catch((error) => {
                                        this.$console.log(error);
                                    });
                            }
                        }
                    },
                    trapFocus: true,
                    customClass: 'tainacan-modal',
                    closeButtonAriaLabel: this.$i18n.get('close')
                });  
            },
            onTermRemovalFinished(term) {
                if ( this.isSearching ) {
                    const removedTermIndex = this.searchResults.findIndex((aTerm) => aTerm.id == term.id);

                    if ( removedTermIndex >= 0 )
                        this.searchResults.splice(removedTermIndex, 1);
                } else {
                    const removedTermParentColumn = this.termColumns.findIndex((aFinderColumn) => aFinderColumn.id == term.parent);
                    
                    if ( removedTermParentColumn >= 0 ) {
                        const removedTermIndex = this.termColumns[removedTermParentColumn].children.findIndex((aTerm) => aTerm.id == term.id);
                        
                        if ( removedTermIndex >= 0 ) {
                            this.totalRemaining[removedTermParentColumn].remaining = Number(this.totalRemaining[removedTermParentColumn].remaining) - 1;

                            this.termColumns[removedTermParentColumn].children.splice(removedTermIndex, 1);
                            this.termColumns[removedTermParentColumn].total_children = Number(this.termColumns[removedTermParentColumn].total_children) - 1;
                            
                            if ( this.termColumns[removedTermParentColumn - 1] ) {
                                const parentTermIndex = this.termColumns[removedTermParentColumn - 1].children.findIndex((aTerm) => aTerm.id== term.parent);
                                if ( parentTermIndex >= 0)
                                    this.termColumns[removedTermParentColumn - 1].children[parentTermIndex].total_children = Number(this.termColumns[removedTermParentColumn].total_children);
                            }

                            this.removeLevelsAfter(removedTermParentColumn);
                        }
                    }
                }
            },
            onTermEditionFinished(term, hasChangedParent, initialParent) {
                if ( this.isSearching ) {
                    const updatedTermIndex = this.searchResults.findIndex((aTerm) => aTerm.id == term.id);

                    if ( updatedTermIndex >= 0 )
                        this.searchResults.splice(updatedTermIndex, 1, term);
                } else {
                    const updatedTermParentColumn = this.termColumns.findIndex((aFinderColumn) => aFinderColumn.id == term.parent);
                    
                    if ( updatedTermParentColumn >= 0 ) {
                        const updatedTermIndex = this.termColumns[updatedTermParentColumn].children.findIndex((aTerm) => aTerm.id == term.id);
                        
                        if ( updatedTermIndex >= 0 ) {
                            this.termColumns[updatedTermParentColumn].children.splice(updatedTermIndex, 1, term);
                            
                            if ( term.total_children > 0 && this.termColumns[updatedTermParentColumn + 1] )
                                this.termColumns[updatedTermParentColumn + 1].name = term.name;

                        } else {
                            const immediateFollowingTermNameIndex = this.termColumns[updatedTermParentColumn].children.findIndex((aTerm) => aTerm.name.toLowerCase() > term.name.toLowerCase());
                            if ( immediateFollowingTermNameIndex >= 0 )
                                this.termColumns[updatedTermParentColumn].children.splice(immediateFollowingTermNameIndex, 0, term);
                            else
                                this.termColumns[updatedTermParentColumn].children.push(term);

                            this.termColumns[updatedTermParentColumn].total_children = Number(this.termColumns[updatedTermParentColumn].total_children) + 1;
                            
                            if ( this.termColumns[updatedTermParentColumn - 1] ) {
                                const parentTermIndex = this.termColumns[updatedTermParentColumn - 1].children.findIndex((aTerm) => aTerm.id== term.parent);
                                if ( parentTermIndex >= 0)
                                    this.termColumns[updatedTermParentColumn - 1].children[parentTermIndex].total_children = Number(this.termColumns[updatedTermParentColumn].total_children);
                            }
                        }
                    }
                    
                    if ( hasChangedParent ) {
                        const previousTermParentColumn = this.termColumns.findIndex((aFinderColumn) => aFinderColumn.id == initialParent);
                        
                        if ( previousTermParentColumn >= updatedTermParentColumn ) {
                            if ( previousTermParentColumn >= 0 ) {
                                const previousTermIndex = this.termColumns[previousTermParentColumn].children.findIndex((aTerm) => aTerm.id == term.id);
                                
                                if ( previousTermIndex >= 0 ) {
                                    this.termColumns[previousTermParentColumn].children.splice(previousTermIndex, 1);
                                    this.totalRemaining[previousTermParentColumn].remaining = Number(this.totalRemaining[previousTermParentColumn].remaining) - 1;
                                }
                            }
                            
                            if ( this.termColumns[previousTermParentColumn - 1] ) {
                                
                                const newParentIndex = this.termColumns[previousTermParentColumn - 1].children.findIndex((aTerm) => aTerm.id == term.parent);
                                if ( newParentIndex >= 0 )
                                    this.termColumns[previousTermParentColumn - 1].children[newParentIndex].total_children = Number(this.termColumns[previousTermParentColumn - 1].children[newParentIndex].total_children) + 1;

                                const oldParentIndex = this.termColumns[previousTermParentColumn - 1].children.findIndex((aTerm) => aTerm.id == initialParent);
                                if ( oldParentIndex >= 0 )
                                    this.termColumns[previousTermParentColumn - 1].children[oldParentIndex].total_children = Number(this.termColumns[previousTermParentColumn - 1].children[oldParentIndex].total_children) - 1;
                                console.log(oldParentIndex, previousTermParentColumn, this.termColumns[previousTermParentColumn - 1])
                            }

                        } else {
                            
                            for (let i = 0; i < this.termColumns.length; i++) {
                                if ( this.termColumns[i].id == term.id ) {
                                    this.removeLevelsAfter(i);
                                    break;
                                }
                            }
                        }
                    }
                }
            },
            onAddNewChildTerm(parent) {
                let newTerm = {
                    taxonomyId: this.taxonomyId,
                    name: '',
                    description: '',
                    parent: parent,
                    id: 'new',
                    saved: false
                }
                this.onEditTerm(newTerm);
            },
            deleteSelectedTerms() {

                this.$buefy.modal.open({
                    parent: this,
                    component: TermDeletionDialog,
                    props: {
                        message: this.$i18n.get('info_warning_some_terms_with_child'),
                        showDescendantsDeleteButton: true,
                        amountOfTerms: this.amountOfTermsSelected,
                        onConfirm: (typeOfDelete) => { 
                            // If all checks passed, term can be deleted 
                            this.deleteTerms({
                                    taxonomyId: this.taxonomyId, 
                                    terms: this.selectedColumnIndex >= 0 ? [] : this.selected.map((aTerm) => aTerm.id),
                                    parent: this.selectedColumnIndex >= 0 ? this.termColumns[this.selectedColumnIndex].id : undefined,
                                    deleteChildTerms: typeOfDelete === 'descendants'
                                })
                                .then(() => {
                                    this.resetTermsListUI();
                                })
                                .catch((error) => {
                                    this.$console.log(error);
                                });
                        }
                    },
                    trapFocus: true,
                    customClass: 'tainacan-modal',
                    closeButtonAriaLabel: this.$i18n.get('close')
                });  
            },
            updateSelectedTermsParent() {

                this.$buefy.modal.open({
                    parent: this,
                    component: TermParentSelectionDialog,
                    props: {
                        amountOfTerms: this.amountOfTermsSelected,
                        excludeTree: this.selectedColumnIndex >= 0 ? this.termColumns[this.selectedColumnIndex].id : this.selected.map((aTerm) => aTerm.id), 
                        taxonomyId: this.taxonomyId,
                        onConfirm: (selectedParentTerm) => { 
                            console.log(selectedParentTerm);
                            // If all checks passed, term can be deleted   
                            // this.deleteTerm({
                            //         taxonomyId: this.taxonomyId, 
                            //         termId: term.id, 
                            //         parent: term.parent })
                            //     .then(() => {
                            //         this.onTermRemovalFinished(term);
                            //     })
                            //     .catch((error) => {
                            //         this.$console.log(error);
                            //     });
                            this.resetTermsListUI();  
                        }
                    },
                    trapFocus: true,
                    customClass: 'tainacan-modal',
                    closeButtonAriaLabel: this.$i18n.get('close')
                });  
            },
            resetTermsListUI() {
                this.selected = [];
                this.selectedColumnIndex = -1;
                this.removeLevelsAfter(-1);
                
                if ( this.isSearching )
                    this.autoComplete();
                else
                    this.fetchTerms();
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
                background-color: var(--tainacan-blue1);

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
                .checkbox-name-text {
                    font-size: 1.375em !important;
                }
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

    .tainacan-show-more {
        width: 100%;
        display: flex;
        justify-content: center;
        cursor: pointer;
        border: 1px solid var(--tainacan-gray1);
        margin-top: 10px;
        margin-bottom: -0.2em;

        &:hover {
            background-color: var(--tainacan-blue1);
        }
    }

    .tainacan-li-checkbox-modal {
        display: flex;
        justify-content: space-between;
        align-items: stretch;
        margin-left: 0px !important;
        padding: 0 !important;
        -webkit-break-inside: avoid;
        break-inside: avoid;

        /deep/ .b-checkbox, /deep/ .b-radio {
            max-width: 100%;
            min-height: 1.5em;
            margin-left: 0.7em;
            margin-bottom: 0px !important;
            height: auto;
            padding-top: 0px;
            padding-bottom: 0px;
            -webkit-break-inside: avoid;
            break-inside: avoid;

            .control-label {
                white-space: normal;
                overflow: visible;
                padding-top: 0.125em;
                padding-bottom: 0.125em;
                word-break: break-word;
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

            input:disabled+.check {
                cursor: not-allowed;
                opacity: 0.5;
            }
        }

        &:hover {
            background-color: var(--tainacan-gray1);

            &>a:not(.add-link),
            &>button:not(.add-link) {
                opacity: 1.0;
                background-color: var(--tainacan-gray2);
            }
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

    .tainacan-checkbox-list-container {
        border: 1px solid var(--tainacan-gray2);
        min-height: 232px;
        display: flex;
        align-items: center;
        position: relative;
        padding: 0 20px !important;

        &>ul+.tainacan-checkbox-list-page-changer {
            right: 0;
            left: auto;
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

            .tainacan-icon {
                font-size: 1.5em;
            }
        }
    }

    .tainacan-checkbox-list-page-changer {
        height: calc(100% - 1px);
        top: 1px;
        position: absolute;
        left: 0;
        right: auto;
        align-items: center;
        display: flex;
        padding: 0 !important;
        background-color: var(--tainacan-gray1) !important;

        &:hover {
            background-color: var(--tainacan-blue1) !important;
        }
    }

    .tainacan-modal-checkbox-list-body {
        -moz-column-count: 2;
        -moz-column-gap: 0;
        -moz-column-rule: none;
        -webkit-column-count: 2;
        -webkit-column-gap: 0;
        -webkit-column-rule: none;
        column-count: 2;
        column-gap: 2em;
        column-rule: none;
        column-fill: auto;
        list-style: none;
        width: 100%;
        margin: 0;
        padding: 0 !important;
        max-height: 42vh;
        overflow: auto;
    }

    .tainacan-modal-checkbox-search-results-body {
        list-style: none;
        -moz-column-count: 2;
        -moz-column-gap: 0;
        -moz-column-rule: none;
        -webkit-column-count: 2;
        -webkit-column-gap: 0;
        -webkit-column-rule: none;
        column-count: 2;
        column-gap: 2em;
        column-rule: none;
    }

    .tainacan-li-checkbox-parent-active {
        background-color: var(--tainacan-gray0);
        position: sticky;
        top: 0px;
        bottom: 0px;
        z-index: 1;
        border-bottom: 1px solid var(--tainacan-gray1);
        border-top: 1px solid var(--tainacan-gray1);

        .load-children-button {
            background-color: var(--tainacan-blue1) !important;
        }
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

    .warning-no-more-terms {
        margin: 1.5rem 0;
        color: var(--tainacan-info-color);
        font-size: 0.75em;
        padding: 0.5em;
        text-align: center;
    }

    @media screen and (max-width: 768px) {

        .tainacan-modal-checkbox-list-body,
        .tainacan-hierarchical-list-column.has-only-one-column ul,
        .tainacan-modal-checkbox-search-results-body {
            -moz-column-count: auto;
            -webkit-column-count: auto;
            column-count: auto;
            overflow-y: auto;
        }
        .tainacan-modal-checkbox-search-results-body,
        .tainacan-modal-checkbox-list-body,
        .tainacan-hierarchical-list-columns-container {
            font-size: 1.125em;
        }
        .tainacan-hierarchical-list-columns-container {
            max-height: calc(100vh - 184px - 56px);

            .tainacan-hierarchical-list-column,
            .tainacan-hierarchical-list-column ul {
                max-height: 100%;
            }
            .tainacan-hierarchical-list-column {
                max-width: calc(99vw - 0.75em - 0.75em - 2px);
                min-width: calc(99vw - 0.75em - 0.75em - 24px);
            }
            .tainacan-hierarchical-list-column .column-header+ul {
                max-height: calc(100% - 0.75em - 0.45em - 0.45em - 3px);
            }
            .tainacan-hierarchical-list-column a {
                width: 3.5em;
                border-left: 1px solid var(--tainacan-gray1);
                border-bottom: 1px solid var(--tainacan-gray1);
                display: flex;
                justify-content: center;
                align-items: center;
            }
        }
        .tainacan-li-checkbox-list {
            max-width: calc(100% - 20px) !important;
        }
    }

</style>


 
