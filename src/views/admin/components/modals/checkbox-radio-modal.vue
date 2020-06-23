<template>
    <div 
            :autofocus="isModal"
            :role="isModal ? 'dialog' : ''"
            :class="{ 'tainacan-modal-content': isModal }"
            :tabindex="isModal ? -1 : 0"
            :aria-modal="isModal"
            ref="checkboxRadioModal">
        <header 
                v-if="isModal"
                class="tainacan-modal-title">
            <h2 v-if="isFilter">{{ $i18n.get('filter') }} <em>{{ filter.name }}</em></h2>
            <h2 v-else>{{ $i18n.get('metadatum') }} <em>{{ metadatum.name }}</em></h2>
            <hr>
        </header>

        <div 
                :style="isModal ? '' : 'margin-top: 12px'"
                class="tainacan-form">
            <b-tabs
                    :size="isModal ? '' : 'is-small'"
                    animated
                    @input="fetchSelectedLabels()"
                    v-model="activeTab">
                <b-tab-item 
                        :style="{ margin: isModal ? '0' : '0 -1.5rem' }"
                        :label="isTaxonomy ? $i18n.get('label_all_terms') : $i18n.get('label_all_metadatum_values')">
                    
                    <!-- Search input -->
                    <b-field class="is-clearfix tainacan-checkbox-search-section">
                        <p 
                                v-if="!isModal && !shouldBeginWithListExpanded"
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
                                :placeholder="isModal || expandResultsSection ? $i18n.get('instruction_search') : $i18n.get('instruction_click_to_see_or_search')"
                                :aria-label="isModal || expandResultsSection ? $i18n.get('instruction_search') : $i18n.get('instruction_click_to_see_or_search')"
                                v-model="optionName"
                                @input="autoComplete"
                                @focus="!shouldBeginWithListExpanded && !expandResultsSection ? toggleResultsSection() : null"
                                icon-right="magnify"
                                type="search" />
                    </b-field>

                    <!-- Non-hierarchical lists -->
                    <div
                            v-if="!isSearching && !isTaxonomy"
                            :style="{ height: (isModal || expandResultsSection) ? '253px' : '0px' }"
                            class="modal-card-body tainacan-checkbox-list-container">
                        <a
                                v-if="isUsingElasticSearch ? lastTermOnFisrtPage != checkboxListOffset : checkboxListOffset"
                                role="button"
                                class="tainacan-checkbox-list-page-changer"
                                @click="previousPage">
                            <span class="icon">
                                <i class="tainacan-icon tainacan-icon-previous"/>
                            </span>
                        </a>
                        <ul
                                :class="{
                            'tainacan-modal-checkbox-list-body-dynamic-m-l': !checkboxListOffset,
                            'tainacan-modal-checkbox-list-body-dynamic-m-r': noMorePage,
                        }"
                                class="tainacan-modal-checkbox-list-body">
                            <li
                                    class="tainacan-li-checkbox-list"
                                    v-for="(option, key) in options"
                                    :key="key">
                                <label class="b-checkbox checkbox">
                                    <input 
                                            v-model="selected"
                                            :value="option.value"
                                            type="checkbox"> 
                                    <span class="check" /> 
                                    <span class="control-label">
                                        <span 
                                                v-tooltip="{
                                                    content: option.label + ((isFilter && option.total_items != undefined) ? ('(' + option.total_items + ' ' + $i18n.get('items') + ')') : ''),
                                                    autoHide: false,
                                                }" 
                                                class="checkbox-label-text">{{ `${ (option.label ? option.label : '') }` }}</span> 
                                        <span 
                                            v-if="isFilter && option.total_items != undefined"
                                            class="has-text-gray">&nbsp;{{ "(" + option.total_items + ")" }}</span>
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
                            :style="{ height: (isModal || expandResultsSection) ? '253px' : '0px' }"
                            name="page-left">
                        <div 
                                v-for="(finderColumn, key) in finderColumns"
                                class="tainacan-finder-column"
                                :key="finderColumn.label + '-' + key">
                            <p class="column-label">
                                {{ finderColumn.label ? finderColumn.label : $i18n.get('label_terms_without_parent') }}
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
                                            v-if="isCheckbox"
                                            class="b-checkbox checkbox">
                                        <input 
                                                v-model="selected"
                                                :value="(isNaN(Number(option.value)) ? option.value : Number(option.value))"
                                                type="checkbox"> 
                                        <span class="check" /> 
                                        <span class="control-label">
                                            <span 
                                                    v-tooltip="{
                                                        content: option.label + ((isFilter && option.total_items != undefined) ? ('(' + option.total_items + ' ' + $i18n.get('items') + ')') : ''),
                                                        autoHide: false,
                                                    }" 
                                                    class="checkbox-label-text">{{ `${option.label}` }}</span> 
                                            <span 
                                                    v-if="isFilter && option.total_items != undefined"
                                                    class="has-text-gray">
                                                &nbsp;{{ "(" + option.total_items + ")" }}
                                            </span>
                                        </span>
                                    </label>
                                    <b-radio
                                            v-tooltip="{
                                                content: option.label,
                                                autoHide: false,
                                            }" 
                                            v-else
                                            v-model="selected"
                                            :native-value="(isNaN(Number(option.value)) ? option.value : Number(option.value))">
                                        {{ `${option.label}` }}
                                        <span 
                                                v-if="isFilter && option.total_items != undefined"
                                                class="has-text-gray">
                                            &nbsp;{{ "(" + option.total_items + ")" }}
                                        </span>
                                    </b-radio>
                                    <a
                                            v-if="option.total_children > 0"
                                            @click="getOptionChildren(option, key, index)">
                                        <span v-if="finderColumns.length <= 1 ">{{ option.total_children + ' ' + $i18n.get('label_children_terms') }}</span>
                                        <span 
                                                v-tooltip="{
                                                    content: option.total_children + ' ' + $i18n.get('label_children_terms'),
                                                    autoHide: false,
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

                    <!-- Search Results -->
                    <div
                            v-if="isSearching"
                            :style="{ height: (isModal || expandResultsSection) ? '253px' : '0px' }"
                            class="modal-card-body tainacan-search-results-container">
                        <ul class="tainacan-modal-checkbox-search-results-body">
                            <li
                                    class="tainacan-li-search-results"
                                    v-for="(option, key) in searchResults"
                                    :key="key">
                                <label 
                                        v-if="isCheckbox"
                                        class="b-checkbox checkbox">
                                    <input                                     
                                            v-model="selected"
                                            :value="option.id ? (isNaN(Number(option.id)) ? option.id : Number(option.id)) : (isNaN(Number(option.value)) ? option.value : Number(option.value))"
                                            type="checkbox"> 
                                    <span class="check" /> 
                                    <span class="control-label">
                                        <span 
                                                v-tooltip="{
                                                    content: (option.name ? option.name : (option.hierarchy_path ? renderHierarchicalPath(option.hierarchy_path, option.label) : option.label)) + ((isFilter && option.total_items != undefined) ? ('(' + option.total_items + ' ' + $i18n.get('items') + ')') : ''),
                                                    autoHide: false,
                                                }"
                                                class="checkbox-label-text"
                                                v-html="`${ option.name ? option.name : (option.label ? (option.hierarchy_path ? renderHierarchicalPath(option.hierarchy_path, option.label) : option.label) : '') }`" /> 
                                        <span 
                                                v-if="isFilter && option.total_items != undefined"
                                                class="has-text-gray">
                                            &nbsp;{{ "(" + option.total_items + ")" }}
                                        </span>
                                    </span>
                                </label>
                                <b-radio
                                        v-tooltip="{
                                            content: (option.name ? option.name : option.label) + ((isFilter && option.total_items != undefined) ? ('(' + option.total_items + ' ' + $i18n.get('items') + ')') : ''),
                                            autoHide: false,
                                        }"
                                        v-else
                                        v-model="selected"
                                        :native-value="option.id ? (isNaN(Number(option.id)) ? option.id : Number(option.value)) : (isNaN(Number(option.value)) ? option.value : Number(option.value))">
                                    {{ `${ option.name ? option.name : (option.label ? option.label : '') }` }}
                                    <span 
                                            v-if="isFilter && option.total_items != undefined"
                                            class="has-text-gray">
                                        &nbsp;{{ "(" + option.total_items + ")" }}
                                    </span>
                                </b-radio>
                            </li>
                            <b-loading
                                    :is-full-page="false"
                                    :active.sync="isLoadingSearch"/>
                        </ul>
                    </div>
                    
                </b-tab-item>

                <b-tab-item :label="isTaxonomy ? $i18n.get('label_selected_terms') : $i18n.get('label_selected_metadatum_values')">

                    <div class="modal-card-body tainacan-tags-container">
                        <b-field
                                v-if="selected.length > 0 && !isSelectedTermsLoading"
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
                                        @close="selected instanceof Array ? selected.splice(index, 1) : selected = ''">
                                    {{ (isTaxonomy || metadatum_type === 'Tainacan\\Metadata_Types\\Relationship') ? selectedTagsName[term] : term }}
                                </b-tag>
                            </div>
                        </b-field>
                        <section 
                                v-if="selected.length <= 0 && !isSelectedTermsLoading"
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
            <!--<pre>{{ selected }}</pre>-->
            <!--<pre>{{ options }}</pre>-->
            <!--<pre>{{ searchResults }}</pre>-->
            <!--<pre>{{ selectedTagsName }}</pre>-->

            <footer 
                    v-if="isModal"
                    class="field is-grouped form-submit">
                <div class="control">
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
        name: 'CheckboxFilterModal',
        mixins: [ dynamicFilterTypeMixin ],
        props: {
            isFilter: {
                type: Boolean,
                default: true
            },
            filter: '',
            parent: Number,
            taxonomy_id: Number,
            taxonomy: String,
            collectionId: Number,
            metadatumId: Number,
            metadatum: Object,
            selected: Array,
            isTaxonomy: {
                type: Boolean,
                default: false,
            },
            metadatum_type: String,
            query: Object,
            isRepositoryLevel: Boolean,
            isCheckbox: {
                type: Boolean,
                default: true,
            },
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
                maxNumOptionsCheckboxList: 20,
                maxNumSearchResultsShow: 20,
                maxNumOptionsCheckboxFinderColumns: 100,
                checkboxListOffset: 0,
                isCheckboxListLoading: false,
                isLoadingSearch: false,
                noMorePage: 0,
                activeTab: 0,
                selectedTagsName: {},
                isSelectedTermsLoading: false,
                isUsingElasticSearch: tainacan_plugin.wp_elasticpress == "1" ? true : false,
                previousLastTerms: [],
                lastTermOnFisrtPage: null,
                expandResultsSection: false
            }
        },
        computed: {
            shouldBeginWithListExpanded() {
                return this.isTaxonomy && this.metadatum && this.metadatum.metadata_type_options && this.metadatum.metadata_type_options.visible_options_list;
            }
        },
        watch: {
            selected() {
                if (!this.isModal)
                    this.$emit('input', this.selected);
            }
        },
        updated(){
            if (!this.isSearching)
                this.highlightHierarchyPath();
        },
        created() {
            if (this.isModal || this.shouldBeginWithListExpanded)
                this.initializeValues();

            this.expandResultsSection = this.shouldBeginWithListExpanded;
            
            this.$parent.$on('update-taxonomy-inputs', ($event) => { 
                if ($event.taxonomyId == this.taxonomy_id && $event.metadatumId == this.metadatumId) {
                    this.finderColumns = [];
                    this.hierarchicalPath = [];
                    this.isSearching = false;
                    this.searchResults = [];
                    this.initializeValues();
                }
            });
        },
        mounted() {
            if (this.isModal && this.$refs.checkboxRadioModal)
                this.$refs.checkboxRadioModal.focus()
        },
        beforeDestroy() {
            // Cancels previous Request
            if (this.getOptionsValuesCancel != undefined)
                this.getOptionsValuesCancel.cancel('Get options request canceled.');
        },
        methods: {
            initializeValues() {
                if (!this.isModal)
                    this.maxNumOptionsCheckboxFinderColumns = 12;

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
                                this.saveSelectedTagName(term.id, term.name);

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
                            for (const item of res.data)
                                this.saveSelectedTagName(item.id, item.title);

                            this.isSelectedTermsLoading = false;
                        })
                        .catch((error) => {
                            this.$console.log(error);
                            this.isSelectedTermsLoading = false;
                        });
                }
            },
            saveSelectedTagName(value, label){
                if (!this.selectedTagsName[value])
                    this.$set(this.selectedTagsName, `${value}`, label);
            },
            previousPage() {

                this.noMorePage = 0;
                this.isCheckboxListLoading = true;

                if (this.isUsingElasticSearch) {
                    this.previousLastTerms.pop();

                    if (this.previousLastTerms.length > 0) {
                        this.getOptions(this.previousLastTerms.pop());
                        this.previousLastTerms.push(this.checkboxListOffset);
                    } else {
                        this.getOptions(0);
                    }
                } else {
                    this.checkboxListOffset -= this.maxNumOptionsCheckboxList;
                    if (this.checkboxListOffset < 0)
                        this.checkboxListOffset = 0;

                    this.getOptions(this.checkboxListOffset);
                }
            },
            nextPage() {
    
                if (this.isUsingElasticSearch)
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
            getOptions(offset) {
                let promise = '';
                
                // Cancels previous Request
                if (this.getOptionsValuesCancel != undefined)
                    this.getOptionsValuesCancel.cancel('Facet search Canceled.');

                if ( this.metadatum_type === 'Tainacan\\Metadata_Types\\Relationship' )
                    promise = this.getValuesRelationship( this.optionName, this.isRepositoryLevel, [], offset, this.maxNumOptionsCheckboxList, true);
                else
                    promise = this.getValuesPlainText( this.metadatumId, this.optionName, this.isRepositoryLevel, [], offset, this.maxNumOptionsCheckboxList, true);
                
                promise.request
                    .then((res) => {
                        this.isCheckboxListLoading = false;
                        this.isLoadingSearch = false;

                        if (this.isUsingElasticSearch) {
                                                        
                            this.checkboxListOffset = res.data.last_term;

                            if (!this.lastTermOnFisrtPage || this.lastTermOnFisrtPage == this.checkboxListOffset) {
                                this.lastTermOnFisrtPage = this.checkboxListOffset;
                                this.previousLastTerms.push(0);
                            }
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

                if (this.isTaxonomy) {
                    this.isLoadingSearch = true;
                    let query_items = { 'current_query': this.query };

                    let query = `?order=asc&number=${this.maxNumSearchResultsShow}&search=${this.optionName}&` + qs.stringify(query_items);

                    if (!this.isFilter)
                        query += '&hideempty=0';

                    let route = `/collection/${this.collectionId}/facets/${this.metadatumId}${query}`;

                    if (this.collectionId == 'default')
                        route = `/facets/${this.metadatumId}${query}`;

                    axios.get(route)
                        .then((res) => {
                            this.searchResults = res.data.values;
                            this.isLoadingSearch = false;
                        }).catch((error) => {
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
                        remaining: this.isUsingElasticSearch ? (children.length > 0 ? res.data.last_term == children[children.length - 1].value : false) : res.headers['x-wp-total'],
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
                    this.finderColumns.splice(first, 1, { label: label, children: children, lastTerm: res.data.last_term });
                else
                    this.finderColumns.push({ label: label, children: children, lastTerm: res.data.last_term });
            },
            appendMore(options, key, lastTerm) {
                for (let option of options)
                    this.finderColumns[key].children.push(option);
                
                this.finderColumns[key].lastTerm = lastTerm;
            },
            toggleResultsSection() {
                if (!this.isModal) { 
                    if (!this.expandResultsSection)
                        this.initializeValues();

                    this.expandResultsSection = !this.expandResultsSection;

                    if (!this.expandResultsSection) {
                        this.isSearching = false;
                        this.optionName = '';
                    }
                }
            },
            getOptionChildren(option, key, index) {
                
                let query_items = { 'current_query': this.query };

                if (key != undefined)
                    this.addToHierarchicalPath(key, index, option);

                let parent = 0;

                if (option)
                    parent = option.value;

                let query = `?order=asc&parent=${parent}&number=${this.maxNumOptionsCheckboxFinderColumns}&offset=0&` + qs.stringify(query_items);

                if (!this.isFilter)
                    query += '&hideempty=0';

                this.isColumnLoading = true;

                let route = `/collection/${this.collectionId}/facets/${this.metadatumId}${query}`;

                if (this.collectionId == 'default')
                    route = `/facets/${this.metadatumId}${query}`
                
                axios.get(route)
                    .then(res => {
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

                    let query = `?order=asc&parent=${parent}&number=${this.maxNumOptionsCheckboxFinderColumns}&offset=${offset}&` + qs.stringify(query_items);

                    if (!this.isFilter)
                        query += '&hideempty=0';

                    if (finderColumn.lastTerm)
                        query += '&last_term=' + finderColumn.lastTerm

                    this.isColumnLoading = true;

                    let route = `/collection/${this.collectionId}/facets/${this.metadatumId}${query}`;

                    if (this.collectionId == 'default')
                        route = `/facets/${this.metadatumId}${query}`

                    axios.get(route)
                        .then(res => {
                            this.appendMore(res.data.values, key, res.data.last_term);

                            this.totalRemaining = Object.assign({}, this.totalRemaining, {
                                [`${key}`]: {
                                    remaining: this.isUsingElasticSearch ? (res.data.values.length > 0 ? (res.data.last_term == res.data.values[res.data.values.length - 1].value) : false) : res.headers['x-wp-total'],
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
            applyFilter() {
                this.$parent.close();

                this.$eventBusSearch.resetPageOnStore();

                if (this.isTaxonomy && this.isFilter) {
                    this.$eventBusSearch.$emit('input', {
                        filter: 'checkbox',
                        taxonomy: this.taxonomy,
                        compare: 'IN',
                        metadatum_id: this.metadatumId ? this.metadatumId : this.filter.metatadum_id,
                        collection_id: this.collectionId ? this.collectionId : this.filter.collection_id,
                        terms: this.selected
                    });         
                } else if(this.isFilter) {
                    this.$eventBusSearch.$emit('input', {
                        filter: 'checkbox',
                        compare: 'IN',
                        metadatum_id: this.metadatumId ? this.metadatumId : this.filter.metatadum_id,
                        collection_id: this.collectionId ? this.collectionId : this.filter.collection_id,
                        value: this.selected,
                    });
                } else {
                    this.$emit('input', this.selected);
                }

                this.$emit('appliedCheckBoxModal');
            },
            renderHierarchicalPath(hierachyPath, label) {
                return '<span class="hierarchical-path">' + hierachyPath.replace(/>/g, '&nbsp;<span class="hierarchy-separator"> &gt; </span>&nbsp;') + '</span><strong>' + label + '</strong>';
            }
        }
    }
</script>

<style lang="scss" scoped>

    .tainacan-modal-title {
        margin-bottom: 16px;
    }

    .breadcrumb {
        background-color: var(--tainacan-white) !important;

        ul {
            list-style: none;
        }
        li + li::before {
            content: ">" !important;
        }
    }

    @media screen and (max-width: 768px) {
        .tainacan-modal-content {
            flex-direction: column;
            display: flex;
            align-items: center;
            justify-content: space-around;
        }

        .tainacan-modal-checkbox-list-body {
            flex-wrap: nowrap !important;
        }

        .tainacan-modal-checkbox-search-results-body {
            column-count: 1;
        }

        .tainacan-li-checkbox-list {
            max-width: calc(100% - (2 * var(--tainacan-one-column))) !important;
        }
    }

    .tainacan-modal-content {
        width: auto;
        min-height: 550px;

        .b-tabs {
            margin-bottom: 0 !important;
            
            .tab-content {
                padding: 0.5em;
                transition: height 0.2s ease;
            }
        }
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
        margin-bottom: -0.2em;

        &:hover {
            background-color: var(--tainacan-blue1);
        }
    }

    .tainacan-li-search-results {
        flex-grow: 0;
        flex-shrink: 1;
        width: 100%;
        padding: 0 0.5em;

        .b-checkbox, .b-radio {
            max-width: 100%;
            margin-right: 10px;
            margin-bottom: 0;
            overflow: hidden;
        }

        &:hover {
            background-color: var(--tainacan-gray1);
        }
    }

    .tainacan-li-checkbox-modal {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0;

        .b-checkbox, .b-radio {
            max-width: 100%;
            margin-left: 0.7em;
            margin-bottom: 0;
            height: 24px;
            overflow: hidden;
        }

        &:hover {
            background-color: var(--tainacan-gray1);
        }

    }

    .tainacan-li-checkbox-list {
        flex-grow: 0;
        flex-shrink: 1;
        max-width: calc(50% - (2 * var(--tainacan-one-column)));
        padding-left: 0.5em;

        .b-checkbox, .b-radio {
            margin-right: 0px;
            margin-bottom: 0;
        }

        &:hover {
            background-color: var(--tainacan-gray1);
        }
    }

    .tainacan-finder-columns-container {
        background-color: var(--tainacan-white);
        border: 1px solid var(--tainacan-gray1);
        border-top: 0px;
        margin-top: -1px;
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

        ul {
            max-height: calc(253px - 20px - 0.7em);
            min-height: inherit;
            overflow-y: auto;
            overflow-x: hidden;
            list-style: none;
        }
        a {
            font-size: 0.75em;
            white-space: nowrap;
            display: flex;
            .tainacan-icon {
                font-size: 1.5em;
            }
        }

        .column-label {
            color: var(--tainacan-label-color);
            display: block;
            font-size: 0.75em;
            font-weight: bold;
            padding: 0.35em 0.75em;
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
                border-top-width: calc(1.15em + 1px);
                border-bottom-width: calc(1.15em + 0px);
                left: -3px;
            }
            &::before {
                top: 0px;
                border-color: transparent transparent transparent var(--tainacan-gray1);
                border-left-width: 12px;
                border-top-width: calc(1.15em + 1px);
                border-bottom-width: calc(1.15em + 0px);
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
        .input .icon .mdi::before {
            color: var(--tainacan-input-color);
        }
        .button {
            border-radius: 0 !important;
            min-height: 100%;
            border: 1px solid var(--tainacan-input-border-color);
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
        padding: 0 20px !important;
        min-height: 232px;
        display: flex;
        align-items: center;
        padding-right: 0 !important;
        padding-left: 0 !important;
    }

    .tainacan-checkbox-list-page-changer {
        height: 253px;
        align-items: center;
        display: flex;
        background-color: var(--tainacan-gray1);

        &:hover {
            background-color: var(--tainacan-blue1);
        }
    }

    .tainacan-modal-checkbox-list-body {
        list-style: none;
        width: 100%;
        align-self: baseline;
        margin: 0 10px;
        display: flex;
        flex-direction: column;
        flex-wrap: wrap;
        padding: 0 !important;
        max-height: 253px;
        overflow: auto;
    }

    .tainacan-modal-checkbox-list-body-dynamic-m-l {
        margin-left: var(--tainacan-one-column) !important;
    }

    .tainacan-modal-checkbox-list-body-dynamic-m-r {
        margin-right: var(--tainacan-one-column) !important;
    }

    .tainacan-search-results-container {
        padding: 0.15em 20px !important;
    }

    .tainacan-tags-container {
        padding: 0px !important;
        display: inline;

        .control {
            margin-bottom: 0.25rem;
            margin-right: 0.25rem;
        }

        .tags.is-small {
            font-size: 0.875em;
        }
    }

    .tainacan-modal-checkbox-search-results-body {
        list-style: none;
        column-count: 2;
        max-height: 253px;
    }

    .tainacan-li-no-children {
        padding: 3em 1.5em 3em 0.5em;
    }

    .tainacan-li-checkbox-last-active {
        background-color: var(--tainacan-turquoise1);
    }

    .tainacan-li-checkbox-parent-active {
        background-color: var(--tainacan-turquoise1);
    }

    .b-checkbox .control-label {
        display: flex;
        flex-wrap: nowrap;
        width: 100%;

        .checkbox-label-text {
            white-space: nowrap;
            text-overflow: ellipsis;
            overflow: hidden;
            line-height: 1.45em;
        }
    }

    .warning-no-more-terms {
        color: var(--tainacan-info-color);
        font-size: 0.75em;
        padding: 0.5em;
        text-align: center;
    }


</style>


 
