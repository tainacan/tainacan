<template>
    <div 
            class="tainacan-form">
        <input 
                type="text"
                aria-hidden="true"
                class="is-special-hidden-for-mobile"
                autocomplete="on"
                @focus="onMobileSpecialFocus">
        <b-tabs
                size="is-small"
                animated
                @input="fetchSelectedLabels()"
                v-model="activeTab"
                :class="{ 'hidden-tabs-section': (shouldBeginWithListExpanded && !hasToDisplaySearchBar) }">
            <b-tab-item :label="isTaxonomy ? $i18n.get('label_all_terms') : $i18n.get('label_all_metadatum_values')">
                
                <!-- Search input -->
                <b-field 
                        v-if="!shouldBeginWithListExpanded || hasToDisplaySearchBar"
                        class="is-clearfix tainacan-checkbox-search-section">
                    <p 
                            v-if="!shouldBeginWithListExpanded"
                            class="control">
                        <b-button 
                                :class="{ 'is-active': expandResultsSection }"
                                class="button"
                                @click="toggleResultsSection()">
                            <span 
                                    class="icon is-left has-text-gray">
                                <i 
                                        class="tainacan-icon tainacan-icon-1-25em"
                                        :class="isTaxonomy ? 'tainacan-icon-taxonomies' : 'tainacan-icon-view-table'"/>
                            </span>
                        </b-button>
                    </p>
                    <b-input
                            expanded
                            autocomplete="on"
                            :placeholder="metadatum.placeholder ? metadatum.placeholder : ( expandResultsSection ? $i18n.get('instruction_search') : $i18n.get('instruction_click_to_see_or_search') )"
                            :aria-label="expandResultsSection ? $i18n.get('instruction_search') : $i18n.get('instruction_click_to_see_or_search')"
                            v-model="optionName"
                            @input="autoComplete"
                            @focus="!shouldBeginWithListExpanded && !expandResultsSection ? toggleResultsSection() : null"
                            icon-right="magnify"
                            type="search" />
                </b-field>

                <!-- Search Results -->
                <div
                        v-if="isSearching"
                        :style="{ height: expandResultsSection ? 'auto' : '0px' }"
                        class="modal-card-body tainacan-checkbox-list-container">
                    <a
                            v-if="checkboxListOffset"
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
                                    v-for="(option, key) in searchResults"
                                    :key="key">
                                <label 
                                        :class="{ 
                                            'b-checkbox checkbox': isCheckbox,
                                            'b-radio radio': !isCheckbox,
                                            'is-disabled': !isOptionSelected(option.value) && maxMultipleValues !== undefined && (maxMultipleValues - 1 < selected.length) 
                                        }">
                                    <input
                                            :disabled="!isOptionSelected(option.id ? option.id : option.value) && maxMultipleValues !== undefined && (maxMultipleValues - 1 < selected.length)"
                                            @input="updateLocalSelection($event.target.value)"
                                            :checked="isOptionSelected(option.id ? option.id : option.value)"
                                            :value="option.id ? getOptionValue(option.id) : getOptionValue(option.value)"
                                            :type="isCheckbox ? 'checkbox' : 'radio'"> 
                                    <span class="check" /> 
                                    <span class="control-label">
                                        <span 
                                                class="checkbox-label-text"
                                                v-html="`${ option.name ? option.name : (option.label ? (option.hierarchy_path ? renderHierarchicalPath(option.hierarchy_path, option.label) : option.label) : '') }`" /> 
                                    </span>
                                </label>                                
                            </li>
                        </template>
                        <template v-if="!isLoadingSearch && !searchResults.length">
                            <li class="tainacan-li-checkbox-list result-info">
                                {{ $i18n.get('info_no_terms_found') }}
                            </li>
                        </template>
                       <template v-if="!isLoadingSearch && allowNew && !searchResults.length">
                            <li class="tainacan-li-checkbox-list result-info">
                                <a @click="$emit('showAddNewTerm', { name: optionName })">
                                    {{ $i18n.get('label_create_new_term') + ' "' + optionName + '"' }}
                                </a>
                            </li>
                        </template>
                        <b-loading
                                :is-full-page="false"
                                :active.sync="isLoadingSearch"/>
                    </ul>
                    <a
                            v-if="!noMoreSearchPage"
                            role="button"
                            class="tainacan-checkbox-list-page-changer"
                            @click="nextSearchPage">
                        <span class="icon">
                            <i class="tainacan-icon tainacan-icon-next"/>
                        </span>
                    </a>
                </div>

                <!-- Non-hierarchical lists -->
                <div
                        v-if="!isSearching && !isTaxonomy"
                        :style="{ height: expandResultsSection ? 'auto' : '0px' }"
                        class="modal-card-body tainacan-checkbox-list-container">
                    <a
                            v-if="checkboxListOffset"
                            role="button"
                            class="tainacan-checkbox-list-page-changer"
                            @click="previousPage">
                        <span class="icon">
                            <i class="tainacan-icon tainacan-icon-previous"/>
                        </span>
                    </a>
                    <ul class="tainacan-modal-checkbox-list-body">
                        <li
                                class="tainacan-li-checkbox-list"
                                v-for="(option, key) in options"
                                :key="key">
                            <label
                                :class="{ 
                                    'b-checkbox checkbox': isCheckbox,
                                    'b-radio radio': !isCheckbox,
                                    'is-disabled': !isOptionSelected(option.value) && maxMultipleValues !== undefined && (maxMultipleValues - 1 < selected.length) 
                                }">
                                <input 
                                        :disabled="!isOptionSelected(option.value) && maxMultipleValues !== undefined && (maxMultipleValues - 1 < selected.length)"
                                        :checked="isOptionSelected(option.value)"
                                        @input="updateLocalSelection($event.target.value)"
                                        :value="getOptionValue(option.value)"
                                        :type="isCheckbox ? 'checkbox' : 'radio'"> 
                                <span class="check" /> 
                                <span class="control-label">
                                    <span class="checkbox-label-text">{{ `${ (option.label ? option.label : '') }` }}</span> 
                                </span>
                            </label>
                        </li>
                        <b-loading
                                :is-full-page="false"
                                :active.sync="isCheckboxListLoading"/>
                    </ul>
                    <a
                            v-if="!noMorePage"
                            role="button"
                            class="tainacan-checkbox-list-page-changer"
                            @click="nextPage">
                        <span class="icon">
                            <i class="tainacan-icon tainacan-icon-next"/>
                        </span>
                    </a>
                </div>

                <!-- Hierarchical lists -->
                <transition-group
                        v-if="!isSearching && isTaxonomy"
                        class="modal-card-body tainacan-finder-columns-container"
                        :style="{ height: expandResultsSection ? 'auto' : '0px' }"
                        name="page-left"
                        ref="tainacan-finder-scrolling-container">
                    <div 
                            v-for="(finderColumn, key) in finderColumns"
                            class="tainacan-finder-column"
                            :class="!hasToDisplaySearchBar ? 'has-only-one-column' : ''"
                            :key="finderColumn.label + '-' + key">
                        <p 
                                v-if="hasToDisplaySearchBar"
                                class="column-label">
                            {{ finderColumn.label ? finderColumn.label : $i18n.get('label_root_terms') }}
                        </p>
                        <ul v-if="finderColumn.children.length">
                            <b-field
                                    :addons="false"
                                    class="tainacan-li-checkbox-modal"
                                    v-for="(option, index) in finderColumn.children"
                                    :id="`${key}.${index}-tainacan-li-checkbox-model`"
                                    :ref="`${key}.${index}-tainacan-li-checkbox-model`"
                                    :key="index">
                                <label 
                                        :class="{
                                            'b-checkbox checkbox': isCheckbox,
                                            'b-radio radio': !isCheckbox, 
                                            'is-disabled': !isOptionSelected(option.value) && maxMultipleValues !== undefined && (maxMultipleValues - 1 < selected.length) 
                                        }" >
                                    <input 
                                            :disabled="!isOptionSelected(option.value) && maxMultipleValues !== undefined && (maxMultipleValues - 1 < selected.length)"
                                            @input="updateLocalSelection($event.target.value)"
                                            :checked="isOptionSelected(option.value)"
                                            :value="getOptionValue(option.value)"
                                            :type="isCheckbox ? 'checkbox' : 'radio'"> 
                                    <span class="check" /> 
                                    <span class="control-label">
                                        <span class="checkbox-label-text">{{ option.label }}</span>
                                    </span>
                                </label>
                                <a
                                        v-if="option.total_children > 0"
                                        @click="getOptionChildren(option, key, index)">
                                    <span 
                                            class="is-hidden-mobile"
                                            v-if="finderColumns.length <= 1 ">
                                        {{ option.total_children + ' ' + $i18n.get('label_children_terms') }}
                                    </span>
                                    <span 
                                            v-tooltip="{
                                                content: option.total_children + ' ' + $i18n.get('label_children_terms'),
                                                autoHide: false,
                                                popperClass: ['tainacan-tooltip', 'tooltip']
                                            }" 
                                            v-else>{{ option.total_children }}</span>
                                    <span class="icon is-pulled-right">
                                        <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-arrowright"/>
                                    </span>
                                </a>
                            </b-field>
                            <li v-if="finderColumn.children.length">
                                <div
                                        v-if="shouldShowMoreButton(key)"
                                        @click="getMoreOptions(finderColumn, key)"
                                        class="tainacan-show-more">
                                    <span class="icon">
                                        <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-showmore"/>
                                    </span>
                                </div>
                            </li>
                        </ul>
                    </div>
                </transition-group>
                <section 
                        v-if="( (isTaxonomy && (finderColumns instanceof Array ? finderColumns.length <= 0 : !finderColumns) ) || (!isTaxonomy && options instanceof Array ? options.length <= 0 : !options) ) && expandResultsSection && !isSearching && !isLoadingSearch && !isColumnLoading"
                        class="section">
                    <div class="content has-text-grey has-text-centered">
                        <p>
                            <span class="icon is-medium">
                                <i  
                                        class="tainacan-icon tainacan-icon-30px"
                                        :class="{ 'tainacan-icon-terms': isTaxonomy, 'tainacan-icon-metadata': !isTaxonomy }"/>
                            </span>
                        </p>
                        <p>{{ isTaxonomy ? $i18n.get('info_no_terms_found') : $i18n.get('label_nothing_selected') }}</p>
                    </div>
                </section>

                <b-loading
                        :is-full-page="false"
                        :active.sync="isColumnLoading"/>
                
            </b-tab-item>

            <b-tab-item
                    style="min-height: 56px;"
                    :label="(isTaxonomy ? $i18n.get('label_selected_terms') : $i18n.get('label_selected_metadatum_values')) + (amountSelected !== null && amountSelected !== undefined ? (' (' + amountSelected + ')' ): '') ">

                <div class="modal-card-body tainacan-tags-container">
                    <b-field
                            v-if="(selected instanceof Array ? selected.length > 0 : selected) && !isSelectedTermsLoading"
                            grouped
                            group-multiline>
                        <div
                                v-for="(term, index) in (selected instanceof Array ? selected : [selected])"
                                :key="index"
                                class="control">
                            <b-tag
                                    v-if="selected instanceof Array ? true : selected != ''"
                                    attached
                                    closable
                                    class="is-small"
                                    @close="updateLocalSelection(term)">
                                 <span v-html="(isTaxonomy || metadatum_type === 'Tainacan\\Metadata_Types\\Relationship') ? selectedTagsName[term] : term" />
                            </b-tag>
                        </div>
                    </b-field>
                    <section 
                            v-if="(selected instanceof Array ? selected.length <= 0 : !selected) && !isSelectedTermsLoading"
                            class="section">
                        <div class="content has-text-grey has-text-centered">
                            <p>
                                <span class="icon is-medium">
                                    <i  
                                            class="tainacan-icon tainacan-icon-30px"
                                            :class="{ 'tainacan-icon-terms': isTaxonomy, 'tainacan-icon-metadata': !isTaxonomy }"/>
                                </span>
                            </p>
                            <p>{{ isTaxonomy ? $i18n.get('label_no_terms_selected') : $i18n.get('label_nothing_selected') }}</p>
                        </div>
                    </section>
                    <b-loading
                            :is-full-page="false"
                            :active.sync="isSelectedTermsLoading"/>
                </div>
            </b-tab-item>
        </b-tabs>
        <!-- <pre>{{ hierarchicalPath }}</pre>
        <pre>{{ finderColumns }}</pre> -->
        <!--<pre>{{ totalRemaining }}</pre>-->
        <!-- <pre>{{ selected }}</pre> -->
        <!--<pre>{{ options }}</pre>-->
        <!--<pre>{{ searchResults }}</pre>-->
        <!--<pre>{{ selectedTagsName }}</pre>-->
    </div>
</template>

<script>
    import qs from 'qs';
    import { tainacan as axios, isCancel } from '../../js/axios';
    import { dynamicFilterTypeMixin } from '../../js/filter-types-mixin';

    export default {
        name: 'CheckboxRadioMetadataInput',
        mixins: [ dynamicFilterTypeMixin ],
        props: {
            parent: Number,
            taxonomy_id: Number,
            taxonomy: String,
            collectionId: Number,
            metadatumId: Number,
            metadatum: Object,
            selected: Array,
            allowNew: Boolean,
            isTaxonomy: {
                type: Boolean,
                default: false,
            },
            metadatum_type: String,
            isRepositoryLevel: Boolean,
            isCheckbox: {
                type: Boolean,
                default: true,
            },
            amountSelected: 0,
            maxMultipleValues: undefined,
            isMobileScreen: false
        },
        data() {
            return {
                finderColumns: [],
                itemActive: false,
                isColumnLoading: false,
                loadingComponent: undefined,
                totalRemaining: {},
                hierarchicalPath: [],
                searchResults: [],
                optionName: '',
                isSearching: false,
                options: [],
                maxNumOptionsCheckboxList: 20,
                maxNumSearchResultsShow: 20,
                maxNumOptionsCheckboxFinderColumns: 100,
                checkboxListOffset: 0,
                isCheckboxListLoading: false,
                isLoadingSearch: false,
                noMorePage: false,
                noMoreSearchPage: false,
                activeTab: 0,
                selectedTagsName: {},
                isSelectedTermsLoading: false,
                expandResultsSection: false,
                hasToDisplaySearchBar: false // Different from the checkbox filter component, we cannot hide search bar as it affects the first loading
            }
        },
        computed: {
            shouldBeginWithListExpanded() {
                return this.isTaxonomy && this.metadatum && this.metadatum.metadata_type_options && this.metadatum.metadata_type_options.visible_options_list;
            }
        },
        watch: {
            selected() {
                this.$emit('input', this.selected);
            },
            optionName(newValue, oldValue) {
                if (newValue != oldValue) {
                    this.noMoreSearchPage = false;
                    this.checkboxListOffset = 0;
                }
            }
        },
        updated(){
            if (!this.isSearching && this.isTaxonomy)
                this.highlightHierarchyPath();
        },
        created() {
            if (this.shouldBeginWithListExpanded)
                this.initializeValues();

            this.expandResultsSection = this.shouldBeginWithListExpanded;
            
            this.$parent.$on('update-taxonomy-inputs', ($event) => { 
                if ($event.taxonomyId == this.taxonomy_id && $event.metadatumId == this.metadatumId) {
                    this.finderColumns = [];
                    this.optionName = '';
                    this.hierarchicalPath = [];
                    this.isSearching = false;
                    this.searchResults = [];
                    this.initializeValues();
                }
            });
        },
        beforeDestroy() {
            // Cancels previous Request
            if (this.getOptionsValuesCancel != undefined)
                this.getOptionsValuesCancel.cancel('Get options request canceled.');
        },
        methods: {
            initializeValues() {
                this.maxNumOptionsCheckboxFinderColumns = 50;

                if (this.isTaxonomy) {
                    this.getOptionChildren();
                } else {
                    this.isCheckboxListLoading = true;
                    this.getOptions(0);
                }
            },
            shouldShowMoreButton(key) {
                return this.totalRemaining[key].remaining === true || (this.finderColumns[key].children.length < this.totalRemaining[key].remaining);
            },
            fetchSelectedLabels() {

                let allSelected = this.selected instanceof Array ? this.selected : [this.selected];

                // If a new item was added from item submission block, the value will be a string, and the term does not exists yet.
                let selected = allSelected.filter((aValue) => !isNaN(aValue));
                let selectedFromItemSubmission = allSelected.filter((aValue) => isNaN(aValue));

                if (this.taxonomy_id) {

                    if (selected.length) {
                        this.isSelectedTermsLoading = true;
                        axios.get(`/taxonomy/${this.taxonomy_id}/terms/?${qs.stringify({ hideempty: 0, include: selected})}`)
                            .then((res) => {
                                for (const term of res.data)
                                    this.saveSelectedTagName(term.id, term.name, term.url);

                                this.isSelectedTermsLoading = false;
                            })
                            .catch((error) => {
                                this.$console.log(error);
                                this.isSelectedTermsLoading = false;
                            });
                    }

                    if (selectedFromItemSubmission) {
                        for (const term of selectedFromItemSubmission)
                            this.saveSelectedTagName(term, term.split('>')[term.split('>').length - 1], '');
                    }
                    
                } else if (this.metadatum_type === 'Tainacan\\Metadata_Types\\Relationship' && selected.length) {
                    
                    this.isSelectedTermsLoading = true;

                    axios.get(`/items/?${qs.stringify({ fetch_only: 'title', postin: selected})}`)
                        .then((res) => {
                            for (const item of res.data.items)
                                this.saveSelectedTagName(item.id, item.title, item.url);

                            this.isSelectedTermsLoading = false;
                        })
                        .catch((error) => {
                            this.$console.log(error);
                            this.isSelectedTermsLoading = false;
                        });
                }
            },
            saveSelectedTagName(value, label, link){
                if (!this.selectedTagsName[value])
                    this.$set(this.selectedTagsName, `${value}`, link ? ('<a href=' + link + ' target="_blank">' + label + '</a>') : label );
            },
            previousPage() {

                this.noMorePage = false;
                this.isCheckboxListLoading = true;

                this.checkboxListOffset -= this.maxNumOptionsCheckboxList;
                if (this.checkboxListOffset < 0)
                    this.checkboxListOffset = 0;

                this.getOptions(this.checkboxListOffset);
                
            },
            previousSearchPage() {

                this.noMoreSearchPage = false;
                this.isCheckboxListLoading = true;

                this.checkboxListOffset -= this.maxNumSearchResultsShow;
                if (this.checkboxListOffset < 0)
                    this.checkboxListOffset = 0;

                this.autoComplete();
                
            },
            nextPage() {
    
                if (!this.noMorePage) {
                    // LIMIT 0, 20 / LIMIT 19, 20 / LIMIT 39, 20 / LIMIT 59, 20
                    if (this.checkboxListOffset === this.maxNumOptionsCheckboxList)
                        this.checkboxListOffset += this.maxNumOptionsCheckboxList - 1;
                    else
                        this.checkboxListOffset += this.maxNumOptionsCheckboxList;
                }

                this.isCheckboxListLoading = true;

                this.getOptions(this.checkboxListOffset);
            },
            nextSearchPage() {

                if (!this.noMoreSearchPage) {
                    // LIMIT 0, 20 / LIMIT 19, 20 / LIMIT 39, 20 / LIMIT 59, 20
                    if (this.checkboxListOffset === this.maxNumSearchResultsShow)
                        this.checkboxListOffset += this.maxNumSearchResultsShow - 1;
                    else
                        this.checkboxListOffset += this.maxNumSearchResultsShow;
                }
                
                this.isCheckboxListLoading = true;

                this.autoComplete();
            },
            getOptions(offset) {
                let promise = '';
                
                // Cancels previous Request
                if (this.getOptionsValuesCancel != undefined)
                    this.getOptionsValuesCancel.cancel('Facet search Canceled.');

                if ( this.metadatum_type === 'Tainacan\\Metadata_Types\\Relationship' )
                    promise = this.getValuesRelationship({
                        search: this.optionName, 
                        isRepositoryLevel: this.isRepositoryLevel,
                        valuesToIgnore: [],
                        offset: offset,
                        number: this.maxNumOptionsCheckboxList,
                        isInCheckboxModal: true,
                        countItems: false
                    });
                else
                    promise = this.getValuesPlainText({
                        metadatumId: this.metadatumId, 
                        search: this.optionName, 
                        isRepositoryLevel: this.isRepositoryLevel, 
                        valuesToIgnore: [],
                        offset: offset, 
                        number: this.maxNumOptionsCheckboxList,
                        isInCheckboxModal: true,
                        countItems: false
                    });
                
                promise.request
                    .then((res) => {
                        this.isCheckboxListLoading = false;
                        this.isLoadingSearch = false;

                        this.hasToDisplaySearchBar = !this.isSearching && (this.hasToDisplaySearchBar || res.headers['x-wp-totalpages'] > 1);
                    })
                    .catch(error => {
                        if (isCancel(error))
                            this.$console.log('Request canceled: ' + error.message);
                        else
                            this.$console.error( error );
                    })

                // Search Request Token for cancelling
                this.getOptionsValuesCancel = promise.source;
            },
            autoComplete: _.debounce( function () {
                
                this.isSearching = !!this.optionName.length;
                
                if (!this.isSearching)
                    return;

                if (this.isTaxonomy) {
                    this.isLoadingSearch = true;

                    let query = `?order=asc&number=${this.maxNumSearchResultsShow}&search=${this.optionName}&hideempty=0&offset=${this.checkboxListOffset}&count_items=0`;

                    let route = `/collection/${this.collectionId}/facets/${this.metadatumId}${query}`;

                    if (this.collectionId == 'default')
                        route = `/facets/${this.metadatumId}${query}`;

                    axios.get(route)
                        .then((res) => {
                            this.searchResults = res.data.values;
                            this.isLoadingSearch = false;

                            if (res.headers && res.headers['x-wp-total'])
                                this.noMoreSearchPage = res.headers['x-wp-total'] <= this.checkboxListOffset + this.searchResults.length;

                        })
                        .catch((error) => {
                            this.$console.log(error);
                        });
                } else {
                    this.isLoadingSearch = true;

                    this.getOptions(0);
                }
            }, 500),
            highlightHierarchyPath(){
                for (let [index, el] of this.hierarchicalPath.entries()) {
                    let htmlEl = this.$refs[`${el.column}.${el.element}-tainacan-li-checkbox-model`][0].$el;

                    if(index == this.hierarchicalPath.length-1){
                        htmlEl.classList.add('tainacan-li-checkbox-last-active')
                    } else {
                        htmlEl.classList.add('tainacan-li-checkbox-parent-active')
                    }
                }
            },
            addToHierarchicalPath(column, element, option){

                let found = undefined;
                let toBeAdded = {
                    column: column,
                    element: element,
                    option: option,
                };

                for (let f in this.hierarchicalPath) {
                    if (this.hierarchicalPath[f].column == column) {
                        found = f;
                        break;
                    }
                }

                if (found != undefined) {
                    this.removeHighlightNotSelectedLevels();

                    this.hierarchicalPath.splice(found);
                    this.hierarchicalPath.splice(found, 1, toBeAdded);
                } else {
                    this.hierarchicalPath.push(toBeAdded);
                }

                this.highlightHierarchyPath();
            },
            removeHighlightNotSelectedLevels(){
                for (let el of this.hierarchicalPath) {
                    if (this.$refs[`${el.column}.${el.element}-tainacan-li-checkbox-model`][0]) {
                        let htmlEl = this.$refs[`${el.column}.${el.element}-tainacan-li-checkbox-model`][0].$el;

                        htmlEl.classList.remove('tainacan-li-checkbox-last-active');
                        htmlEl.classList.remove('tainacan-li-checkbox-parent-active');
                    }
                }
            },
            removeLevelsAfter(key){
                if (key != undefined)
                    this.finderColumns.length = key + 1;
            },
            createColumn(res, column, label) {
                let children = res.data.values;

                this.totalRemaining = Object.assign({}, this.totalRemaining, {
                    [`${column == undefined ? 0 : column + 1}`]: {
                        remaining: res.headers['x-wp-total'],
                    }
                });

                let first = undefined;

                if (children.length > 0) {
                    for (let f in this.finderColumns) {
                        if (this.finderColumns[f].children[0].value == children[0].value) {
                            first = f;
                            break;
                        }
                    }
                }

                if (first != undefined)
                    this.finderColumns.splice(first, 1, { label: label, children: children, lastTerm: res.data.last_term.es_term });
                else
                    this.finderColumns.push({ label: label, children: children, lastTerm: res.data.last_term.es_term });

                this.$nextTick(() => {
                    setTimeout(() => {
                        if (
                            this.$refs &&
                            this.$refs[`${column + 1}.0-tainacan-li-checkbox-model`] &&
                            this.$refs[`${column + 1}.0-tainacan-li-checkbox-model`][0] &&
                            this.$refs[`${column + 1}.0-tainacan-li-checkbox-model`][0].$el &&
                            this.$refs['tainacan-finder-scrolling-container'] &&
                            this.$refs['tainacan-finder-scrolling-container'].$el) {

                            // Scroll Into does not solve as it would scroll vertically as well...
                            //this.$refs[`${column + 1}.0-tainacan-li-checkbox-model`][0].$el.scrollIntoView({ behavior: "smooth", inline: "start" });

                            this.$refs['tainacan-finder-scrolling-container'].$el.scrollTo({
                                top: 0,
                                left: first != undefined ? 0 : this.$refs[`${column + 1}.0-tainacan-li-checkbox-model`][0].$el.offsetLeft,
                                behavior: 'smooth'
                            });
                        }
                    }, 500);
                }); 
            },
            appendMore(options, key, lastTerm) {
                for (let option of options)
                    this.finderColumns[key].children.push(option);
                
                this.finderColumns[key].lastTerm = lastTerm;
            },
            toggleResultsSection() {
                if (!this.expandResultsSection)
                    this.initializeValues();

                this.expandResultsSection = !this.expandResultsSection;

                if (!this.expandResultsSection) {
                    this.isSearching = false;
                    this.optionName = '';
                }
            },
            getOptionChildren(option, key, index) {

                if (key != undefined)
                    this.addToHierarchicalPath(key, index, option);

                let parent = 0;

                if (option)
                    parent = option.value;

                let query = `?order=asc&parent=${parent}&number=${this.maxNumOptionsCheckboxFinderColumns}&offset=0&hideempty=0&count_items=0`;

                this.isColumnLoading = true;

                let route = `/collection/${this.collectionId}/facets/${this.metadatumId}${query}`;

                if (this.collectionId == 'default')
                    route = `/facets/${this.metadatumId}${query}`
                
                axios.get(route)
                    .then(res => {
                        
                        this.hasToDisplaySearchBar = !this.isSearching && (this.hasToDisplaySearchBar || res.headers['x-wp-totalpages'] > 1 || res.data.values.some((aValue) => aValue.total_children != undefined && aValue.total_children != 0));

                        this.removeLevelsAfter(key);
                        this.createColumn(res, key, option ? option.label : null);

                        this.isColumnLoading = false;
                    })
                    .catch(error => {
                        this.$console.log(error);

                        this.isColumnLoading = false;
                    });

            },
            getMoreOptions(finderColumn, key) {

                if (finderColumn.children && finderColumn.children.length > 0) {
                    let parent = finderColumn.children[0].parent;
                    let offset = finderColumn.children.length;

                    let query = `?order=asc&parent=${parent}&number=${this.maxNumOptionsCheckboxFinderColumns}&offset=${offset}&hideempty=0&count_items=0`;

                    if (finderColumn.lastTerm)
                        query += '&last_term=' + encodeURIComponent(finderColumn.lastTerm)

                    this.isColumnLoading = true;

                    let route = `/collection/${this.collectionId}/facets/${this.metadatumId}${query}`;

                    if (this.collectionId == 'default')
                        route = `/facets/${this.metadatumId}${query}`

                    axios.get(route)
                        .then(res => {
                            this.appendMore(res.data.values, key, res.data.last_term.es_term);

                            this.hasToDisplaySearchBar = !this.isSearching && (this.hasToDisplaySearchBar || res.headers['x-wp-totalpages'] > 1 || res.data.values.some((aValue) => aValue.total_children != undefined && aValue.total_children != 0));

                            this.totalRemaining = Object.assign({}, this.totalRemaining, {
                                [`${key}`]: {
                                    remaining: res.headers['x-wp-total'],
                                }
                            });
                            this.isColumnLoading = false;
                        })
                        .catch(error => {
                            this.$console.log(error);

                            this.isColumnLoading = false;
                        });
                }
            },
            renderHierarchicalPath(hierachyPath, label) {
                return '<span style="color: var(--tainacan-info-color);">' + hierachyPath.replace(/>/g, '&nbsp;<span class="hierarchy-separator"> &gt; </span>&nbsp;') + '</span>' + label;
            },
            onMobileSpecialFocus($event) {
                $event.target.blur();
                this.$emit('mobileSpecialFocus');
            },
            isOptionSelected(optionValue) {
                if (Array.isArray(this.selected))
                    return (this.selected.indexOf((isNaN(Number(optionValue)) ? optionValue : Number(optionValue))) >= 0)
                else
                    return optionValue == this.selected;
            },
            getOptionValue(optionValue) {
                return isNaN(Number(optionValue)) ? optionValue : Number(optionValue)
            },
            updateLocalSelection(newSelected) {

                let localSelection = this.isCheckbox ? this.selected : (Array.isArray(this.selected) ? this.selected[0] : this.selected);

                if (Array.isArray(localSelection)) {
                    const existingValueIndex = this.selected.indexOf(isNaN(Number(newSelected)) ? newSelected : Number(newSelected));
                    
                    if (existingValueIndex >= 0)
                        localSelection.splice(existingValueIndex, 1);
                    else
                        localSelection.push(isNaN(Number(newSelected)) ? newSelected : Number(newSelected));
                } else {

                    if (newSelected == localSelection)
                        localSelection = false;
                    else
                        localSelection = isNaN(Number(newSelected)) ? newSelected : Number(newSelected);
                }
                this.$emit('input', localSelection);
            }
        }
    }
</script>

<style lang="scss" scoped>

    /deep/ .tabs {
        margin-bottom: 0 !important;

        ul {
            padding: 0;
        }
    }
    .hidden-tabs-section /deep/ .tabs {
        display: none;
        visibility: hidden;
    }
    .hidden-tabs-section /deep/ .tab-content {
        padding-top: 0 !important;

        .tainacan-finder-columns-container {
            border: none;
        }
    }
    /deep/ .tab-content {
        transition: height 0.2s ease;
        padding: 0.5em 0px !important;
    }

    // In theme, the bootstrap removes the style of <a> without href
    a {
        cursor: pointer;
        color: var(--tainacan-turquoise5);
    }

    .tainacan-form {
        margin-top: 12px;
        max-width: 100%;

        .form-submit {
            padding-top: 16px !important;
        }
        &.is-expanded-on-mobile:focus,
        &.is-expanded-on-mobile:focus-within,
        &.is-expanded-on-mobile:focus-visible {
            background-color: var(--tainacan-background-color);
            z-index: 9999999;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
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
        padding: 2px 0 !important;
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

            &.is-disabled {
                cursor: not-allowed;
                opacity: 0.5;
            }
        }

        &:hover {
            background-color: var(--tainacan-gray1);
        }

    }

    .tainacan-li-checkbox-list {
        flex-grow: 0;
        flex-shrink: 1;
        max-width: calc(100% - 0.5em);
        padding-left: 0.5em;
        margin: 0;
        -webkit-break-inside: avoid;
        break-inside: avoid;

        /deep/ .b-checkbox, /deep/ .b-radio {
            margin-right: 0px;
            margin-bottom: 0;
            -webkit-break-inside: avoid;
            break-inside: avoid;

            .control-label {
                white-space: normal;
                overflow: visible;
            }
            &.is-disabled {
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

        &:hover:not(.result-info) {
            background-color: var(--tainacan-gray1);
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

    .tainacan-finder-columns-container {
        background-color: var(--tainacan-white);
        border: 1px solid var(--tainacan-gray1);
        border-top: 0px;
        margin-top: -1px;
        display: flex;
        overflow: auto;
        scroll-snap-type: x mandatory;
        scroll-snap-align: start;
        padding: 0 !important;
        max-height: 40vh;
        transition: heigth 0.5s ease, min-height 0.5s ease;

        &:focus {
            outline: none;
        }
    }

    .tainacan-finder-column {
        border-right: solid 1px var(--tainacan-gray1);        
        flex-basis: auto;
        flex-grow: 1;
        max-width: 600px;
        min-width: 200px;
        margin: 0;
        padding: 0em;
        transition: width 0.2s ease;

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
            max-height: calc(253px - 20px - 0.7em);
            min-height: inherit;
            overflow-y: auto;
            overflow-x: hidden;
            list-style: none;
            margin: 0;
            padding-left: 0;
        }
        a {
            font-size: 0.75em;
            white-space: nowrap;
            display: flex;
            align-items: center;

            .tainacan-icon {
                font-size: 1.5em;
            }
        }

        .column-label {
            color: var(--tainacan-label-color);
            display: block;
            font-size: 0.75em;
            font-weight: bold;
            padding: 0.45em 0.75em;
            margin: 0;
            position: relative;
            border-bottom: 1px solid var(--tainacan-gray1);
        }

        &:not(:first-child) .column-label {
            padding-left: calc(0.75em + 12px);

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
                border-top-width: calc(1.2em + 1px);
                border-bottom-width: calc(1.2em + 0px);
                left: -3px;
            }
            &::before {
                top: 0px;
                border-color: transparent transparent transparent var(--tainacan-gray1);
                border-left-width: 12px;
                border-top-width: calc(1.2em + 1px);
                border-bottom-width: calc(1.2em + 0px);
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

    .tainacan-checkbox-search-section {
        margin: 0px !important;
        padding: 0px !important;

        .control {
            margin: 0;
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
            background-color: var(--tainacan-primary);

            .tainacan-icon::before {
                color: var(--tainacan-secondary)
            }
        } 
        /deep/ .field-body>.field {
            padding: 0px !important;
            margin-left: 0px !important;
        }
    }

    .tainacan-checkbox-list-container {
        border: 1px solid var(--tainacan-gray1);
        border-top: 0px;
        margin-top: -1px;
        min-height: 232px;
        display: flex;
        align-items: center;
        position: relative;
        padding: 0 20px !important;

        &>ul+.tainacan-checkbox-list-page-changer {
            right: 0;
            left: auto;
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
        background-color: var(--tainacan-gray1);

        &:hover {
            background-color: var(--tainacan-primary);
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
        list-style: none;
        width: 100%;
        margin: 0;
        padding: 0 !important;
        max-height: 253px;
        overflow: auto;
    }

    .tainacan-tags-container {
        min-height: 64px;
        padding: 0px !important;
        display: inline;

        .control {
            margin-bottom: 0.25rem;
            margin-right: 0.25rem;
        }

        .tags.is-small {
            font-size: 0.875em;
        }

        section p {
            font-size: 0.875em;
        }
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

    .tainacan-li-no-children {
        padding: 3em 1.5em 3em 0.5em;
    }

    .tainacan-li-checkbox-last-active {
        background-color: var(--tainacan-primary);
    }

    .tainacan-li-checkbox-parent-active {
        background-color: var(--tainacan-primary);
    }

    .b-checkbox .control-label {
        display: flex;
        flex-wrap: nowrap;
        align-items: center;
        width: 100%;
        overflow: visible !important;
        white-space: normal !important;

        .checkbox-label-text {
            line-height: 1.25em;
            padding-right: 3px;
            break-inside: avoid;
        }
    }

    .warning-no-more-terms {
        color: var(--tainacan-info-color);
        font-size: 0.75em;
        padding: 0.5em;
        text-align: center;
    }

    @media screen and (max-width: 768px) {

        .tainacan-modal-checkbox-list-body,
        .tainacan-finder-column.has-only-one-column ul,
        .tainacan-modal-checkbox-search-results-body {
            -moz-column-count: auto;
            -webkit-column-count: auto;
            column-count: auto;
            overflow-y: auto;
        }
        .tainacan-modal-checkbox-search-results-body,
        .tainacan-modal-checkbox-list-body,
        .tainacan-finder-columns-container {
            font-size: 1.125em;
        }
        .tainacan-finder-columns-container {
            max-height: calc(100vh - 184px - 56px);

            .tainacan-finder-column,
            .tainacan-finder-column ul {
                max-height: 100%;
            }
            .tainacan-finder-column {
                max-width: calc(99vw - 0.75em - 0.75em - 2px);
                min-width: calc(99vw - 0.75em - 0.75em - 24px);
            }
            .tainacan-finder-column .column-label+ul {
                max-height: calc(100% - 0.75em - 0.45em - 0.45em - 3px);
            }
            .tainacan-finder-column a {
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


 
