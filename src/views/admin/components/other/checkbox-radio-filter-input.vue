<template>
    <div 
            :autofocus="isModal"
            :role="isModal ? 'dialog' : ''"
            :class="{ 'tainacan-modal-content': isModal }"
            :tabindex="isModal ? -1 : 0"
            :aria-modal="isModal"
            :aria-labelledby="'checkbox-radio-filter--title-' + filter.id"
            ref="CheckboxRadioFilterInput">
        <header 
                v-if="isModal"
                class="tainacan-modal-title">
            <h2 :id="'checkbox-radio-filter--title-' + filter.id">{{ $i18n.get('filter') }} <em>{{ filter.name }}</em></h2>
            <hr>
        </header>

        <div 
                :style="isModal ? '' : 'margin-top: 12px'"
                class="tainacan-form">
            <b-tabs
                    size="is-small"
                    animated
                    @input="fetchSelectedLabels()"
                    v-model="activeTab"
                    :class="{ 'hidden-tabs-section': !isModal || !hasToDisplaySearchBar }">
                <b-tab-item 
                        :style="{ margin: isModal ? '0' : '0 0 1rem 0' }"
                        :label="isTaxonomy ? $i18n.get('label_all_terms') : $i18n.get('label_all_metadatum_values')">
                    
                    <!-- Search input -->
                    <b-field 
                            v-if="hasToDisplaySearchBar"
                            class="is-clearfix tainacan-checkbox-search-section">
                        <b-input
                                expanded
                                autocomplete="on"
                                :placeholder="$i18n.get('instruction_search')"
                                :aria-label="$i18n.get('instruction_search')"
                                v-model="optionName"
                                @input="autoComplete"
                                icon-right="magnify"
                                type="search" />
                    </b-field>

                    <!-- Search Results -->
                    <div
                            v-if="isSearching"
                            class="modal-card-body tainacan-checkbox-list-container">
                        <a
                                v-if="isUsingElasticSearch ? previousLastTerms.length && previousLastTerms[0] != checkboxListOffset : checkboxListOffset"
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
                                    <label class="b-checkbox checkbox">
                                        <input                                     
                                                @input="$emit('input', $event.target.value)"
                                                :value="option.id ? (isNaN(Number(option.id)) ? option.id : Number(option.id)) : (isNaN(Number(option.value)) ? option.value : Number(option.value))"
                                                :checked="isOptionSelected(option.value)"
                                                type="checkbox"> 
                                        <span class="check" /> 
                                        <span class="control-label">
                                            <span 
                                                    class="checkbox-label-text"
                                                    v-html="`${ option.name ? option.name : (option.label ? (option.hierarchy_path ? renderHierarchicalPath(option.hierarchy_path, option.label) : option.label) : '') }`" /> 
                                            <span 
                                                    v-if="option.total_items != undefined"
                                                    class="has-text-gray">
                                                &nbsp;{{ "(" + option.total_items + ")" }}
                                            </span>
                                        </span>
                                    </label>
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
                            class="modal-card-body tainacan-checkbox-list-container">
                        <a
                                v-if="isUsingElasticSearch ? previousLastTerms.length && previousLastTerms[0] != checkboxListOffset : checkboxListOffset"
                                role="button"
                                class="tainacan-checkbox-list-page-changer"
                                @click="previousPage">
                            <span class="icon">
                                <i class="tainacan-icon tainacan-icon-previous"/>
                            </span>
                        </a>
                        <ul 
                                :style="isCheckboxListLoading ? 'min-height: 84px' : ''"
                                class="tainacan-modal-checkbox-list-body">
                            <template v-if="options.length">
                                <li
                                        class="tainacan-li-checkbox-list"
                                        v-for="(option, key) in options"
                                        :key="key">
                                    <label class="b-checkbox checkbox">
                                        <input 
                                                @input="$emit('input', $event.target.value)"
                                                :value="option.value"
                                                :checked="isOptionSelected(option.value)"
                                                type="checkbox"> 
                                        <span class="check" /> 
                                        <span class="control-label">
                                            <span 
                                                    v-tooltip="{
                                                        content: option.label + (option.total_items != undefined ? ('(' + option.total_items + ' ' + $i18n.get('items') + ')') : ''),
                                                        autoHide: false,
                                                        popperClass: ['tainacan-tooltip', 'tooltip']
                                                    }" 
                                                    class="checkbox-label-text">{{ `${ (option.label ? option.label : '') }` }}</span> 
                                            <span 
                                                v-if="option.total_items != undefined"
                                                class="has-text-gray">&nbsp;{{ "(" + option.total_items + ")" }}</span>
                                        </span>
                                    </label>
                                </li>
                            </template>
                            <template v-if="!isCheckboxListLoading && !options.length">
                                <li class="tainacan-li-checkbox-list result-info warning-no-more-terms">
                                    {{ $i18n.get('info_no_terms_found') }}
                                </li>
                            </template>
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
                            name="page-left">
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
                                    <label class="b-checkbox checkbox">
                                        <input 
                                                @input="$emit('input', $event.target.value)"
                                                :value="(isNaN(Number(option.value)) ? option.value : Number(option.value))"
                                                :checked="isOptionSelected(option.value)"
                                                type="checkbox"> 
                                        <span class="check" /> 
                                        <span class="control-label">
                                            <span 
                                                    v-tooltip="{
                                                        content: option.label + (option.total_items != undefined ? ('(' + option.total_items + ' ' + $i18n.get('items') + ')') : ''),
                                                        autoHide: false,
                                                        popperClass: ['tainacan-tooltip', 'tooltip']
                                                    }" 
                                                    class="checkbox-label-text">{{ `${option.label}` }}</span> 
                                            <span 
                                                    v-if="option.total_items != undefined"
                                                    class="has-text-gray">
                                                &nbsp;{{ "(" + option.total_items + ")" }}
                                            </span>
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
                                    <div 
                                            class="warning-no-more-terms"
                                            v-else>
                                        {{ isUsingElasticSearch ? $i18n.get('info_no_more_terms_found') : '' }}
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </transition-group>

                    <b-loading
                            :is-full-page="false"
                            :active.sync="isColumnLoading"/>
                    
                </b-tab-item>

                <b-tab-item :label="( isTaxonomy ? $i18n.get('label_selected_terms') : $i18n.get('label_selected_metadatum_values') ) + ( Array.isArray(selected) && selected.length ? (' (' + selected.length + ')') : '' )">
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
                                        :class="isModal ? '' : 'is-small'"
                                        @close="$emit('input', term)">
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
            
            <footer 
                    class="field is-grouped"
                    :class="{ 'form-submit': isModal }">
                <div 
                        v-if="isModal"
                        class="control">
                    <button
                            class="button is-outlined"
                            type="button"
                            @click="$parent.close()">{{ $i18n.get('cancel') }}
                    </button>
                </div>
                <div class="control">
                    <button
                            @click="applyFilter"
                            type="button"
                            class="button is-success">{{ $i18n.get('apply') }}
                    </button>
                </div>
            </footer>
        </div>
    </div>
</template>

<script>
    import qs from 'qs';
    import { tainacan as axios, isCancel } from '../../js/axios';
    import { dynamicFilterTypeMixin } from '../../js/filter-types-mixin';

    export default {
        name: 'CheckboxRadioFilterInput',
        mixins: [ dynamicFilterTypeMixin ],
        props: {
            filter: '',
            parent: Number,
            taxonomy_id: Number,
            taxonomy: String,
            collectionId: Number,
            metadatumId: Number,
            metadatum: Object,
            selected: Array,
            currentCollectionId: String,
            isTaxonomy: {
                type: Boolean,
                default: false,
            },
            metadatum_type: String,
            query: Object,
            isRepositoryLevel: Boolean,
            isModal: {
                type: Boolean,
                default: true,
            }
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
                maxNumSearchResultsShow: 20,
                maxNumOptionsCheckboxList: 20,
                maxNumOptionsCheckboxFinderColumns: 100,
                checkboxListOffset: 0,
                isCheckboxListLoading: false,
                isLoadingSearch: false,
                noMorePage: false,
                noMoreSearchPage: false,
                activeTab: 0,
                selectedTagsName: {},
                isSelectedTermsLoading: false,
                isUsingElasticSearch: tainacan_plugin.wp_elasticpress == "1" ? true : false,
                previousLastTerms: [],
                hasToDisplaySearchBar: false
            }
        },
        watch: {
            optionName(newValue, oldValue) {
                if (newValue != oldValue) {
                    this.noMoreSearchPage = false;
                    this.checkboxListOffset = this.isUsingElasticSearch ? '' : 0;
                    this.previousLastTerms = [];
                }
            }
        },
        updated(){
            if (!this.isSearching && this.isTaxonomy)
                this.highlightHierarchyPath();
        },
        created() {
            this.initializeValues();
                
            if (this.isTaxonomy)
                this.getOptionChildren();
            else
                this.isCheckboxListLoading = true;
            
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
        mounted() {
            if (this.isModal && this.$refs.CheckboxRadioFilterInput)
                this.$refs.CheckboxRadioFilterInput.focus();
        },
        beforeDestroy() {
            // Cancels previous Request
            if (this.getOptionsValuesCancel != undefined)
                this.getOptionsValuesCancel.cancel('Get options request canceled.');
        },
        methods: {
            initializeValues() {
                if (!this.isModal)
                    this.maxNumOptionsCheckboxFinderColumns = 24;

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

                let selected = this.selected instanceof Array ? this.selected : [this.selected];

                if (this.taxonomy_id && selected.length) {

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

                if (this.isUsingElasticSearch) {
                    this.previousLastTerms.pop();

                    if (this.previousLastTerms.length > 0)
                        this.getOptions(this.previousLastTerms[this.previousLastTerms.length - 1]);
                    else
                        this.getOptions(0);
                } else {
                    this.checkboxListOffset -= this.maxNumOptionsCheckboxList;
                    if (this.checkboxListOffset < 0)
                        this.checkboxListOffset = 0;

                    this.getOptions(this.checkboxListOffset);
                }
            },
            previousSearchPage() {
                this.noMoreSearchPage = false;

                if (this.isUsingElasticSearch) {
                    this.previousLastTerms.pop();

                    if (this.previousLastTerms.length > 0)
                        this.checkboxListOffset = this.previousLastTerms[this.previousLastTerms.length - 1];
                    else
                        this.checkboxListOffset = '';

                } else {
                    this.checkboxListOffset -= this.maxNumSearchResultsShow;
                    
                    if (this.checkboxListOffset < 0)
                        this.checkboxListOffset = 0;
                }
                this.autoComplete();
            },
            nextPage() {
    
                if (this.isUsingElasticSearch && this.checkboxListOffset)
                    this.previousLastTerms.push(this.checkboxListOffset);

                if (!this.noMorePage && !this.isUsingElasticSearch) {
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
    
                if (this.isUsingElasticSearch && this.checkboxListOffset)
                    this.previousLastTerms.push(this.checkboxListOffset);

                if (!this.noMoreSearchPage && !this.isUsingElasticSearch) {
                    // LIMIT 0, 20 / LIMIT 19, 20 / LIMIT 39, 20 / LIMIT 59, 20
                    if (this.checkboxListOffset === this.maxNumSearchResultsShow)
                        this.checkboxListOffset += this.maxNumSearchResultsShow - 1;
                    else
                        this.checkboxListOffset += this.maxNumSearchResultsShow;
                }
                
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
                        isInCheckboxModal: true
                    });
                else
                    promise = this.getValuesPlainText({
                        metadatumId: this.metadatumId,
                        search: this.optionName,
                        isRepositoryLevel: this.isRepositoryLevel,
                        valuesToIgnore: [],
                        offset: offset, 
                        number: this.maxNumOptionsCheckboxList,
                        isInCheckboxModal: true
                    });
                
                promise.request
                    .then((res) => {
                        this.isCheckboxListLoading = false;
                        this.isLoadingSearch = false;
                        
                        this.hasToDisplaySearchBar = !this.isSearching && (this.isUsingElasticSearch || this.hasToDisplaySearchBar || res.headers['x-wp-totalpages'] > 1);
                        
                        if (!this.isUsingElasticSearch && res.headers && res.headers['x-wp-total'])
                            this.noMorePage = res.headers['x-wp-total'] <= (this.checkboxListOffset + res.data.values.length);

                        if (this.isUsingElasticSearch) {
                            this.checkboxListOffset = res.data.last_term.es_term;
                            
                            this.noMorePage = !res.data.last_term || !res.data.last_term.es_term;

                        }
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

                this.isLoadingSearch = true;

                let query_items = { 'current_query': this.query };
                let query = `?order=asc&number=${this.maxNumSearchResultsShow}&search=${this.optionName}&${qs.stringify(query_items)}&${this.isUsingElasticSearch ? 'last_term' : 'offset'}=${this.checkboxListOffset}`;

                let route = `/collection/${this.collectionId}/facets/${this.metadatumId}${query}`;
                
                if (this.collectionId == 'default')
                    route = `/facets/${this.metadatumId}${query}`;
                    
                axios.get(route)
                    .then((res) => {
                        this.searchResults = res.data.values;
                        this.isLoadingSearch = false;

                        if (!this.isUsingElasticSearch && res.headers && res.headers['x-wp-total'])
                            this.noMoreSearchPage = res.headers['x-wp-total'] <= this.checkboxListOffset + this.searchResults.length;
                        
                        if (this.isUsingElasticSearch) {                            
                            this.checkboxListOffset = res.data.last_term.es_term;

                            this.noMoreSearchPage = !res.data.last_term || !res.data.last_term.es_term;
                        }

                    }).catch((error) => {
                    this.$console.log(error);
                });
                
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
                        remaining: this.isUsingElasticSearch ? (children.length > 0 ? res.data.last_term.value == children[children.length - 1].value : false) : res.headers['x-wp-total'],
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
                
            },
            appendMore(options, key, lastTerm) {
                for (let option of options)
                    this.finderColumns[key].children.push(option);
                
                this.finderColumns[key].lastTerm = lastTerm;
            },
            getOptionChildren(option, key, index) {
                
                let query_items = { 'current_query': this.query };

                if (key != undefined)
                    this.addToHierarchicalPath(key, index, option);

                let parent = 0;

                if (option)
                    parent = option.value;

                let query = `?order=asc&parent=${parent}&number=${this.maxNumOptionsCheckboxFinderColumns}&offset=0&${qs.stringify(query_items)}`

                this.isColumnLoading = true;
                let route = `/collection/${this.collectionId}/facets/${this.metadatumId}${query}`;

                if (this.collectionId == 'default')
                    route = `/facets/${this.metadatumId}${query}`
                
                axios.get(route)
                    .then(res => {

                        this.hasToDisplaySearchBar = !this.isSearching && (this.isUsingElasticSearch || this.hasToDisplaySearchBar || res.headers['x-wp-totalpages'] > 1 || res.data.values.some((aValue) => aValue.total_children != undefined && aValue.total_children != 0));

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
                    let query_items = { 'current_query': this.query };

                    let query = `?order=asc&parent=${parent}&number=${this.maxNumOptionsCheckboxFinderColumns}&offset=${offset}&${qs.stringify(query_items)}`

                    if (finderColumn.lastTerm)
                        query += '&last_term=' + encodeURIComponent(finderColumn.lastTerm)

                    this.isColumnLoading = true;

                    let route = `/collection/${this.collectionId}/facets/${this.metadatumId}${query}`;

                    if (this.collectionId == 'default')
                        route = `/facets/${this.metadatumId}${query}`

                    axios.get(route)
                        .then(res => {
                            this.appendMore(res.data.values, key, res.data.last_term.es_term);
                            
                            this.hasToDisplaySearchBar = !this.isSearching && (this.isUsingElasticSearch || this.hasToDisplaySearchBar || res.headers['x-wp-totalpages'] > 1 || res.data.values.some((aValue) => aValue.total_children != undefined && aValue.total_children != 0));

                            this.totalRemaining = Object.assign({}, this.totalRemaining, {
                                [`${key}`]: {
                                    remaining: this.isUsingElasticSearch ? (res.data.values.length > 0 ? (res.data.last_term.value == res.data.values[res.data.values.length - 1].value) : false) : res.headers['x-wp-total'],
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
            isOptionSelected(optionValue) {
                if (Array.isArray(this.selected))
                    return this.selected.find(aSelected => aSelected == optionValue)
                else
                    return optionValue == this.selected;
            },
            applyFilter() {
                if (this.isModal)
                    this.$parent.close();
                else    
                    this.initializeValues();

                this.$eventBusSearch.resetPageOnStore();

                if (this.isTaxonomy) {
                    this.$eventBusSearch.$emit('input', {
                        filter: 'checkbox',
                        taxonomy: this.taxonomy,
                        compare: 'IN',
                        metadatum_id: this.metadatumId ? this.metadatumId : this.filter.metatadum_id,
                        collection_id: this.collectionId ? this.collectionId : this.filter.collection_id,
                        terms: this.selected
                    });         
                } else {
                    this.$eventBusSearch.$emit('input', {
                        filter: 'checkbox',
                        compare: 'IN',
                        metadatum_id: this.metadatumId ? this.metadatumId : this.filter.metatadum_id,
                        collection_id: this.collectionId ? this.collectionId : this.filter.collection_id,
                        value: this.selected,
                    });
                }

                this.$emit('appliedCheckBoxModal');
            },
            renderHierarchicalPath(hierachyPath, label) {
                return '<span style="color: var(--tainacan-info-color);">' + hierachyPath.replace(/>/g, '&nbsp;<span class="hierarchy-separator"> &gt; </span>&nbsp;') + '</span>' + label;
            }
        }
    }
</script>

<style lang="scss" scoped>

    /deep/ .tainacan-modal-title {
        margin-bottom: 16px;
    }

    .tainacan-modal-content {
        width: auto;
    }

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
    /deep/ .tab-content {
        transition: height 0.2s ease;
        padding: 0px !important;
        min-height: 86px;
    }

    .tainacan-modal-title {
        align-self: baseline;
        display: flex;
        flex-direction: column;
        width: 100%;
    }

    // In theme, the bootstrap removes the style of <a> without href
    a {
        cursor: pointer;
        color: var(--tainacan-turquoise5);
    }

    .tainacan-form {
        max-width: 100%;
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
        margin-bottom: 0.1em;

        &:hover {
            background-color: var(--tainacan-blue1);
        }
    }

    .tainacan-li-checkbox-modal {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0;
        -webkit-break-inside: avoid;
        break-inside: avoid;

        .b-checkbox {
            break-inside: avoid;
            max-width: 100%;
            min-height: 1.5em;
            margin-left: 0.7em;
            margin-right: 2em !important;
            margin-bottom: 0px !important;
        }

        @media screen and (max-width: 768px) {
            .control-label {
                padding-top: 0.45em;
                padding-bottom: 0.45em;
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

        .b-checkbox {
            -webkit-break-inside: avoid;
            break-inside: avoid;
            margin-right: 0px;
            margin-bottom: 0;
        }

        &:hover {
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
        height: auto;
        display: flex;
        overflow: auto;
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

            ul {
                -moz-column-count: 3;
                -moz-column-gap: 0;
                -moz-column-rule: none;
                -webkit-column-count: 3;
                -webkit-column-gap: 0;
                -webkit-column-rule: none;
                column-count: 3;
                column-gap: 2em;
                column-rule: none;
                overflow-y: hidden;
                overflow-x: auto;
            }
            @media screen and (max-width: 1366px) {
                ul {
                    -moz-column-count: 2;
                    -webkit-column-count: 2;
                    column-count: 2;
                }
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
        margin-bottom: 0;

        .control {
            margin: 0;
        }
        .input .icon .mdi::before {
            color: var(--tainacan-input-color);
        }
        .button {
            border-radius: 0 !important;
            min-height: 100%;
            border: 1px solid var(--tainacan-input-border-color);
            background-color: var(--tainacan-input-background-color);
            transition: background 0.2s ease;
        }
        .button.is-active {
            background-color: var(--tainacan-primary);

            .tainacan-icon::before {
                color: var(--tainacan-secondary)
            }
        } 
    }

    .tainacan-checkbox-list-container {
        height: auto;
        display: flex;
        align-items: center;
        position: relative;
        padding: 6px 20px !important;

        &>ul+.tainacan-checkbox-list-page-changer {
            right: 0;
            left: auto;
        }
    }

    .tainacan-checkbox-list-page-changer {
        height: 100%;
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
        max-height: 253px;
        padding: 0 !important;
        margin: 0;
        overflow: auto;
    }

    .tainacan-search-results-container {
        padding: 0.25em !important;
    }

    .tainacan-tags-container {
        min-height: 64px;
        padding: 6px 20px !important;
        display: block;

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
        .tainacan-modal-content {
            flex-direction: column;
            display: flex;
            align-items: center;
            justify-content: space-around;
        }

        .tainacan-modal-checkbox-list-body,
        .tainacan-finder-column.has-only-one-column,
        .tainacan-modal-checkbox-search-results-body {
            -moz-column-count: auto !important;
            -webkit-column-count: auto !important;
            column-count: auto !important;
        }

        .tainacan-modal-checkbox-search-results-body,
        .tainacan-checkbox-list-container,
        .tainacan-finder-columns-container {
            font-size: 1.125em;
        }

        .tainacan-finder-columns-container {
            max-height: 48vh;
            .tainacan-finder-column,
            .tainacan-finder-column ul {
                max-height: 100%;
            }
            .tainacan-finder-column .column-label+ul {
                max-height: calc(100% - 0.75em - 0.45em - 0.45em);
            }
        }

        .tainacan-li-checkbox-list {
            max-width: calc(100% - (2 * var(--tainacan-one-column))) !important;
        }
    }

</style>
