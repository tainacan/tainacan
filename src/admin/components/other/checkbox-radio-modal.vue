<template>
    <div class="tainacan-modal-content">
        <header class="tainacan-modal-title">
            <h2 v-if="isFilter">{{ $i18n.get('filter') }} <em>{{ filter.name }}</em></h2>
            <h2 v-else>{{ $i18n.get('metadatum') }} <em>{{ metadatum.name }}</em></h2>
            <hr>
        </header>
        <div class="tainacan-form">
            <div class="is-clearfix tainacan-checkbox-search-section">
                <input
                        autocomplete="on"
                        :placeholder="$i18n.get('instruction_search')"
                        v-model="optionName"
                        @input="autoComplete"
                        class="input">
                <span class="icon is-right">
                    <i
                            class="mdi mdi-magnify"/>
                </span>
            </div>

            <b-tabs
                    v-if="!isSearching"
                    size="is-small"
                    animated
                    @input="fetchSelectedLabels()"
                    v-model="activeTab">
                <b-tab-item :label="$i18n.get('label_all_terms')">

                    <div
                            v-if="!isSearching && !isTaxonomy"
                            class="modal-card-body tainacan-checkbox-list-container">
                        <a
                                v-if="checkboxListOffset"
                                role="button"
                                class="tainacan-checkbox-list-page-changer"
                                @click="beforePage">
                            <b-icon
                                    icon="chevron-left"/>
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
                                <b-checkbox
                                        v-model="selected"
                                        :native-value="option.value">
                                    {{ `${ limitChars(option.label) }` }}
                                </b-checkbox>
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
                            <b-icon
                                    icon="chevron-right"/>
                        </a>
                    </div>

                    <div
                            v-if="!isSearching && isTaxonomy"
                            class="modal-card-body tainacan-finder-columns-container">
                        <ul
                                class="tainacan-finder-column"
                                v-for="(finderColumn, key) in finderColumns"
                                :key="key">
                            <b-field
                                    role="li"
                                    :addons="false"
                                    v-if="finderColumn.length"
                                    class="tainacan-li-checkbox-modal"
                                    v-for="(option, index) in finderColumn"
                                    :id="`${key}.${index}-tainacan-li-checkbox-model`"
                                    :ref="`${key}.${index}-tainacan-li-checkbox-model`"
                                    :key="index">
                                <b-checkbox
                                        v-if="isCheckbox"
                                        v-model="selected"
                                        :native-value="option.value">
                                    {{ `${option.label}` }}
                                </b-checkbox>
                                <b-radio
                                        v-else
                                        v-model="selected"
                                        :native-value="option.value">
                                    {{ `${option.label}` }}
                                </b-radio>
                                <a
                                        v-if="option.total_children > 0"
                                        @click="getOptionChildren(option, key, index)">
                                    <b-icon
                                            class="is-pulled-right"
                                            icon="menu-right"
                                    />
                                </a>
                            </b-field>
                            <li v-if="finderColumn.length">
                                <div
                                        v-if="finderColumn.length < totalRemaining[key].remaining"
                                        @click="getMoreOptions(finderColumn, key)"
                                        class="tainacan-show-more">
                                    <b-icon
                                            size="is-small"
                                            icon="chevron-down"/>
                                </div>
                            </li>
                        </ul>
                        <b-loading
                                :is-full-page="false"
                                :active.sync="isColumnLoading"/>
                    </div>
                    <nav
                            style="margin-top: 10px;"
                            class="breadcrumb is-small has-succeeds-separator"
                            aria-label="breadcrumbs">
                        <ul>
                            <li
                                    v-for="(pathItem, pi) in hierarchicalPath"
                                    :class="{'is-active': pi === hierarchicalPath.length-1}"
                                    :key="pi">
                                <a
                                        @click="getOptionChildren(pathItem.option, pathItem.column, pathItem.element)">
                                    {{ pathItem.option.label }}
                                </a>
                            </li>
                        </ul>
                    </nav>
                </b-tab-item>

                <b-tab-item
                        :label="$i18n.get('label_selected_terms')">

                    <div class="modal-card-body tainacan-tags-container">
                        <b-field
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
                                        @close="selected instanceof Array ? selected.splice(index, 1) : selected = ''">
                                    {{ isTaxonomy ? selectedTagsName[term] : term }}
                                </b-tag>
                            </div>
                        </b-field>
                        <b-loading
                                :is-full-page="false"
                                :active.sync="isSelectedTermsLoading"/>
                    </div>
                </b-tab-item>
            </b-tabs>
            <!--<pre>{{ hierarchicalPath }}</pre>-->
            <!--<pre>{{ totalRemaining }}</pre>-->
            <!--<pre>{{ selected }}</pre>-->
            <!--<pre>{{ options }}</pre>-->
            <!--<pre>{{ searchResults }}</pre>-->
            <!--<pre>{{ selectedTagsName }}</pre>-->

            <div
                    v-if="isSearching"
                    class="modal-card-body tainacan-search-results-container">
                <ul class="tainacan-modal-checkbox-search-results-body">
                    <li
                            class="tainacan-li-search-results"
                            v-for="(option, key) in searchResults"
                            :key="key">
                        <b-checkbox
                                v-if="isCheckbox"
                                v-model="selected"
                                :native-value="option.id ? option.id : option.value">
                            {{ `${ option.name ? limitChars(option.name) : limitChars(option.label) }` }}
                        </b-checkbox>
                        <b-radio
                                v-else
                                v-model="selected"
                                :native-value="option.id ? option.id : option.value">
                            {{ `${ option.name ? limitChars(option.name) : limitChars(option.label) }` }}
                        </b-radio>
                    </li>
                    <b-loading
                            :is-full-page="false"
                            :active.sync="isSearchingLoading"/>
                </ul>
            </div>

            <footer class="field is-grouped form-submit">
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
    import { tainacan as axios } from '../../../js/axios/axios';
    import { filter_type_mixin } from '../../../classes/filter-types/filter-types-mixin';

    export default {
        name: 'CheckboxFilterModal',
        mixins: [ filter_type_mixin ],
        props: {
            isFilter: {
                type: Boolean,
                default: true
            },
            filter: '',
            parent: Number,
            taxonomy_id: Number,
            taxonomy: String,
            collection_id: Number,
            metadatum_id: Number,
            metadatum: Object,
            selected: Array,
            isTaxonomy: {
                type: Boolean,
                default: false,
            },
            metadatum_type: String,
            metadatum_object: Object,
            isRepositoryLevel: Boolean,
            isCheckbox: {
                type: Boolean,
                default: true,
            },
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
                collection: this.collection_id,
                isCheckboxListLoading: false,
                isSearchingLoading: false,
                noMorePage: 0,
                maxTextToShow: 47,
                activeTab: 0,
                selectedTagsName: {},
                isSelectedTermsLoading: false,
            }
        },
        updated(){
            if(!this.isSearching){
                this.highlightHierarchyPath();
            }
        },
        created() {
            if(this.isTaxonomy) {
                this.getOptionChildren();
            } else {
                this.isCheckboxListLoading = true;

                this.getOptions(0);
            }
        },
        methods: {
            fetchSelectedLabels() {
                this.isSelectedTermsLoading = true;

                let selected = this.selected instanceof Array ? this.selected : [this.selected];

                if(this.taxonomy_id && selected.length) {
                    for (const term of selected) {

                        if(!this.isSelectedTermsLoading){
                            this.isSelectedTermsLoading = true;
                        }

                        axios.get(`/taxonomy/${this.taxonomy_id}/terms/${term}`)
                            .then((res) => {
                                this.saveSelectedTagName(res.data.id, res.data.name);
                                this.isSelectedTermsLoading = false;
                            })
                            .catch((error) => {
                                this.$console.log(error);
                                this.isSelectedTermsLoading = false;
                            });
                    }
                } else {
                    this.isSelectedTermsLoading = false;
                }
            },
            saveSelectedTagName(value, label){
                if(!this.selectedTagsName[value]) {
                    this.$set(this.selectedTagsName, `${value}`, label);
                }
            },
            limitChars(label){
                if(label.length > this.maxTextToShow){
                    return label.slice(0, this.maxTextToShow)+'...';
                }

                return label;
            },
            beforePage(){
                this.checkboxListOffset -= this.maxNumOptionsCheckboxList;
                this.noMorePage = 0;

                if(this.checkboxListOffset < 0){
                    this.checkboxListOffset = 0;
                }

                this.isCheckboxListLoading = true;

                this.getOptions(this.checkboxListOffset);
            },
            nextPage(){
                if(!this.noMorePage) {
                    // LIMIT 0, 20 / LIMIT 19, 20 / LIMIT 39, 20 / LIMIT 59, 20
                    if(this.checkboxListOffset === this.maxNumOptionsCheckboxList){
                        this.checkboxListOffset += this.maxNumOptionsCheckboxList-1;
                    } else {
                        this.checkboxListOffset += this.maxNumOptionsCheckboxList;
                    }
                }

                this.isCheckboxListLoading = true;

                this.getOptions(this.checkboxListOffset);
            },
            getOptions(offset){
                let promise = '';

                if ( this.metadatum_type === 'Tainacan\\Metadata_Types\\Relationship' ) {
                    let collectionTarget = ( this.metadatum_object && this.metadatum_object.metadata_type_options.collection_id ) ?
                        this.metadatum_object.metadata_type_options.collection_id : this.collection_id;

                    promise = this.getValuesRelationship( collectionTarget, this.optionName, [], offset, this.maxNumOptionsCheckboxList, true);

                    promise
                        .then(() => {
                            this.isCheckboxListLoading = false;
                            this.isSearchingLoading = false;
                        })
                        .catch(error => {
                            this.$console.log(error);
                        })
                } else {
                    promise = this.getValuesPlainText( this.metadatum_id, this.optionName, this.isRepositoryLevel, [], offset, this.maxNumOptionsCheckboxList, true);

                    promise
                        .then(() => {
                            this.isCheckboxListLoading = false;
                            this.isSearchingLoading = false;
                        })
                        .catch(error => {
                            this.$console.log(error);
                        })
                }
            },
            autoComplete: _.debounce( function () {
                this.isSearching = !!this.optionName.length;

                if(!this.isSearching){
                    return;
                }

                if(this.isTaxonomy) {
                    this.isSearchingLoading = true;
                    let query_items = { 'current_query': this.query };

                    let query = `?hideempty=0&order=asc&number=${this.maxNumSearchResultsShow}&searchterm=${this.optionName}&` + qs.stringify(query_items);

                    let route = `/collection/${this.collection_id}/facets/${this.metadatum_id}${query}`;

                    if(this.collection_id == 'default' || this.collection_id == 'filter_in_repository'){
                        route = `/facets/${this.metadatum_id}${query}`
                    }

                    axios.get(route)
                        .then((res) => {
                            this.searchResults = res.data;
                            this.isSearchingLoading = false;
                        }).catch((error) => {
                        this.$console.log(error);
                    });
                } else {
                    this.isSearchingLoading = true;

                    this.getOptions(0);
                }
            }, 500),
            highlightHierarchyPath(){
                for(let [index, el] of this.hierarchicalPath.entries()){
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
                for(let el of this.hierarchicalPath){
                    if(!!this.$refs[`${el.column}.${el.element}-tainacan-li-checkbox-model`][0]) {
                        let htmlEl = this.$refs[`${el.column}.${el.element}-tainacan-li-checkbox-model`][0].$el;

                        htmlEl.classList.remove('tainacan-li-checkbox-last-active');
                        htmlEl.classList.remove('tainacan-li-checkbox-parent-active');
                    }
                }
            },
            removeLevelsAfter(key){
                if(key != undefined){
                    this.finderColumns.splice(key+1);
                }
            },
            createColumn(res, column) {
                let children = res.data;

                this.totalRemaining = Object.assign({}, this.totalRemaining, {
                    [`${column == undefined ? 0 : column+1}`]: {
                        remaining: res.headers['x-wp-total'],
                    }
                });

                let first = undefined;

                if (children.length > 0) {
                    for (let f in this.finderColumns) {
                        if (this.finderColumns[f][0].value == children[0].value) {
                            first = f;
                            break;
                        }
                    }
                }

                if (first != undefined) {
                    this.finderColumns.splice(first, 1, children);
                } else {
                    this.finderColumns.push(children);
                }
            },
            appendMore(options, key) {
                for (let option of options) {
                    this.finderColumns[key].push(option)
                }
            },
            getOptionChildren(option, key, index) {
                let query_items = { 'current_query': this.query };

                if(key != undefined) {
                    this.addToHierarchicalPath(key, index, option);
                }

                let parent = 0;

                if (option) {
                    parent = option.value;
                }

                let query = `?hideempty=0&order=asc&parent=${parent}&number=${this.maxNumOptionsCheckboxFinderColumns}&offset=0&` + qs.stringify(query_items);

                this.isColumnLoading = true;

                let route = `/collection/${this.collection_id}/facets/${this.metadatum_id}${query}`;

                if(this.collection_id == 'default' || this.collection_id == 'filter_in_repository'){
                    route = `/facets/${this.metadatum_id}${query}`
                }

                axios.get(route)
                    .then(res => {
                        this.removeLevelsAfter(key);
                        this.createColumn(res, key);

                        this.isColumnLoading = false;
                    })
                    .catch(error => {
                        this.$console.log(error);

                        this.isColumnLoading = false;
                    });

            },
            getMoreOptions(finderColumn, key) {
                if (finderColumn.length > 0) {
                    let parent = finderColumn[0].parent;
                    let offset = finderColumn.length;
                    let query_items = { 'current_query': this.query };

                    let query = `?hideempty=0&order=asc&parent=${parent}&number=${this.maxNumOptionsCheckboxFinderColumns}&offset=${offset}&` + qs.stringify(query_items);

                    this.isColumnLoading = true;

                    let route = `/collection/${this.collection_id}/facets/${this.metadatum_id}${query}`;

                    if(this.collection_id == 'default' || this.collection_id == 'filter_in_repository'){
                        route = `/facets/${this.metadatum_id}${query}`
                    }

                    axios.get(route)
                        .then(res => {
                            this.appendMore(res.data, key);

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

                if(this.isTaxonomy && this.isFilter){
                    this.$eventBusSearch.$emit('input', {
                        filter: 'checkbox',
                        taxonomy: this.taxonomy,
                        compare: 'IN',
                        metadatum_id: this.metadatum_id,
                        collection_id: this.collection_id,
                        terms: this.selected
                    });         
                } else if(this.isFilter) {
                    this.$eventBusSearch.$emit('input', {
                        filter: 'checkbox',
                        compare: 'IN',
                        metadatum_id: this.metadatum_id,
                        collection_id: this.collection_id ? this.collection_id : this.filter.collection_id,
                        value: this.selected,
                    });
                } else {
                    this.$emit('input', this.selected)
                }

                this.$emit('appliedCheckBoxModal');
            }
        }
    }
</script>

<style lang="scss" scoped>

    @import "../../scss/variables.scss";

    .breadcrumb {
        background-color: white !important;
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
            flex-wrap: nowrap !important;
        }

        .tainacan-li-search-results {
            max-width: calc(100% - 8.3333333%) !important;
        }

        .tainacan-li-checkbox-list {
            max-width: calc(100% - 8.3333333%) !important;
        }
    }

    .tainacan-modal-content {
        width: auto;
        min-height: 600px;
        border-radius: 10px;
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
        color: $turquoise5 !important;
    }

    .tainacan-form {
        max-width: 100%;
    }

    .tainacan-show-more {
        width: 100%;
        display: flex;
        justify-content: center;
        cursor: pointer;
        border: 1px solid $gray1;
        margin-top: 10px;
        margin-bottom: 0.2rem;

        &:hover {
            background-color: $blue1;
        }
    }

    .tainacan-li-search-results {
        flex-grow: 0;
        flex-shrink: 1;
        max-width: calc(50% - 8.3333333%);

        .b-checkbox, .b-radio {
            max-width: 86%;
            margin-right: 10px;
        }

        &:hover {
            background-color: $gray1;
        }
    }

    .tainacan-li-checkbox-modal {
        display: flex;
        padding: 0;

        .b-checkbox, .b-radio {
            max-width: 86%;
            margin-left: 0.7rem;
            height: 24px;
        }

        &:hover {
            background-color: $gray1;
        }

    }

    .tainacan-li-checkbox-list {
        flex-grow: 0;
        flex-shrink: 1;
        max-width: calc(50% - 8.3333333%);

        .b-checkbox, .b-radio {
            margin-right: 10px;
        }

        &:hover {
            background-color: $gray1;
        }
    }

    .tainacan-finder-columns-container {
        background-color: white;
        border: solid 1px $gray1;
        display: flex;
        overflow: auto;
        padding: 0 !important;
        min-height: 253px;

        &:focus {
            outline: none;
        }
    }

    .tainacan-finder-column {
        border-right: solid 1px $gray1;
        max-height: 400px;
        max-width: 25%;
        min-height: inherit;
        min-width: 200px;
        overflow-y: auto;
        list-style: none;
        margin: 0;
        padding: 0rem;
    }

    ul {
        // For Safari
        -webkit-margin-after: 0;
        -webkit-margin-start: 0;
        -webkit-margin-end: 0;
        -webkit-padding-start: 0;
        -webkit-margin-before: 0;
    }

    .tainacan-li-checkbox-modal:first-child {
        margin-top: 0.7rem;
    }

    .field:not(:last-child) {
        margin-bottom: 0 !important;
    }

    .tainacan-checkbox-search-section {
        margin-bottom: 25px;
        display: flex;
        align-items: center;
        position: relative;

        .icon {
            pointer-events: all;
            color: $blue5;
            cursor: pointer;
            height: 27px;
            font-size: 1.125rem;
            width: 30px !important;
            position: absolute;
            right: 0;
        }
    }

    .tainacan-checkbox-list-container {
        padding: 0 20px !important;
        min-height: 253px;
        display: flex;
        align-items: center;
        padding-right: 0 !important;
        padding-left: 0 !important;
    }

    .tainacan-checkbox-list-page-changer {
        height: 253px;
        align-items: center;
        display: flex;
        background-color: $gray1;

        &:hover {
            background-color: $blue1;
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
        margin-left: $page-side-padding !important;
    }

    .tainacan-modal-checkbox-list-body-dynamic-m-r {
        margin-right: $page-side-padding !important;
    }

    .tainacan-search-results-container {
        padding: 0 20px !important;
        min-height: 253px;
    }

    .tainacan-tags-container {
        padding: 0 20px !important;
        min-height: 253px;
    }

    .tainacan-modal-checkbox-search-results-body {
        list-style: none;
        display: flex;
        flex-direction: column;
        flex-wrap: wrap;
        max-height: 253px;
    }

    .tainacan-li-no-children {
        padding: 3rem 1.5rem 3rem 0.5rem;
    }

    .tainacan-li-checkbox-last-active {
        background-color: $turquoise1;
    }

    .tainacan-li-checkbox-parent-active {
        background-color: $turquoise1;
    }

</style>


 
