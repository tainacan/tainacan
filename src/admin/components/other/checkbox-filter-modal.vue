<template>
    <div class="tainacan-modal-content">
        <header class="tainacan-modal-title">
            <h2>{{ this.$i18n.get('filter') }} <em>{{ filter.name }}</em></h2>
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
                                v-model="selected"
                                :native-value="option.value">
                            {{ `${option.label}` }}
                        </b-checkbox>
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
                    <b-loading
                            :is-full-page="false"
                            :active.sync="isColumnLoading"/>
                </ul>
            </div>
            <!--<pre>{{ hierarchicalPath }}</pre>-->
            <!--<pre>{{ totalRemaining }}</pre>-->
            <!--<pre>{{ selected }}</pre>-->
            <!--<pre>{{ options }}</pre>-->
            <!--<pre>{{ searchResults }}</pre>-->

            <div
                    v-if="isSearching"
                    class="modal-card-body tainacan-search-results-container">
                <ul class="tainacan-modal-checkbox-search-results-body">
                    <li
                            class="tainacan-li-search-results"
                            v-for="(option, key) in searchResults"
                            :key="key">
                        <b-checkbox
                                v-model="selected"
                                :native-value="option.id ? option.id : option.value">
                            {{ `${ option.name ? limitChars(option.name) : limitChars(option.label) }` }}
                        </b-checkbox>
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
    import {tainacan as axios} from '../../../js/axios/axios';
    import { filter_type_mixin } from '../../../classes/filter-types/filter-types-mixin';

    export default {
        name: 'CheckboxFilterModal',
        mixins: [ filter_type_mixin ],
        props: {
            filter: '',
            parent: Number,
            taxonomy_id: Number,
            taxonomy: String,
            collection_id: Number,
            metadatum_id: Number,
            selected: Array,
            isTaxonomy: false,
            metadatum_type: String,
            metadatum_object: Object,
            isRepositoryLevel: Boolean,
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

                    axios.get(`/collection/${this.collection_id}/facets/${this.metadatum_id}${query}`)
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
            }, 300),
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
            addToHierarchicalPath(column, element){

                let found = undefined;
                let toBeAdded = {
                    column: column,
                    element: element
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
                    this.addToHierarchicalPath(key, index);
                }

                let parent = 0;

                if (option) {
                    parent = option.value;
                }

                let query = `?hideempty=0&order=asc&parent=${parent}&number=${this.maxNumOptionsCheckboxFinderColumns}&offset=0&` + qs.stringify(query_items);

                this.isColumnLoading = true;

                axios.get(`/collection/${this.collection_id}/facets/${this.metadatum_id}${query}`)
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

                    axios.get(`/collection/${this.collection_id}/facets/${this.metadatum_id}${query}`)
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

                let selectedOptions = [];

                if(this.isTaxonomy){
                    this.$eventBusSearch.$emit('input', {
                        filter: 'checkbox',
                        taxonomy: this.taxonomy,
                        compare: 'IN',
                        metadatum_id: this.metadatum_id,
                        collection_id: this.collection_id,
                        terms: this.selected
                    });

                    for (let selected of this.selected) {

                        for(let i in this.finderColumns){
                            let valueIndex = this.finderColumns[i].findIndex(option => option.value == selected);
                            if (valueIndex >= 0) {
                                selectedOptions.push(this.finderColumns[i][valueIndex]);
                            }
                        }
                    }

                    this.$eventBusSearch.$emit('sendValuesToTags', {
                        filterId: this.filter.id,
                        value: selectedOptions,
                    });
                    
                    this.$emit('appliedCheckBoxModal', selectedOptions);
                } else {
                    this.$eventBusSearch.$emit('input', {
                        filter: 'checkbox',
                        compare: 'IN',
                        metadatum_id: this.metadatum_id,
                        collection_id: this.collection_id ? this.collection_id : this.filter.collection_id,
                        value: this.selected,
                    });
                     
                    // if(!isNaN(this.selected[0])){
                    //     for (let option of this.options) {
                    //         let valueIndex = this.selected.findIndex(item => item == option.value);

                    //         if (valueIndex >= 0) {
                    //             selectedOptions.push(this.options[valueIndex].label);
                    //         }
                    //     }
                    // }
                    if(Array.isArray(this.selected)){
                        for (let aSelected of this.selected) {
                            let valueIndex = this.options.findIndex(option => option.value == aSelected);
                            
                            if (valueIndex >= 0) {
                                selectedOptions.push(this.options[valueIndex]);
                            }
                        }
                    } else {
                        let valueIndex = this.options.findIndex(option => option.value == this.selected);
                        
                        if (valueIndex >= 0) {
                            selectedOptions.push(this.options[valueIndex]);
                        }
                    }

                    this.$emit('appliedCheckBoxModal', selectedOptions);
                }

                
            }
        }
    }
</script>

<style lang="scss" scoped>

    @import "../../scss/variables.scss";

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

        .b-checkbox {
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

        .b-checkbox {
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

        .b-checkbox {
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
        margin-bottom: 40px;
        display: flex;
        align-items: center;
        position: relative;

        .icon {
            pointer-events: all;
            color: $blue5;
            cursor: pointer;
            height: 27px;
            font-size: 18px;
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

    .tainacan-modal-checkbox-search-results-body {
        list-style: none;
        display: flex;
        flex-direction: column;
        flex-wrap: wrap;
        max-height: 253px;

        // For Safari
        -webkit-margin-after: 0;
        -webkit-margin-start: 0;
        -webkit-margin-end: 0;
        -webkit-padding-start: 0;
        -webkit-margin-before: 0;
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


 
