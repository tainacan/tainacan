<template>
    <div
            class="tainacan-modal-content"
            style="width: auto; min-height: 600px;">
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
                        class="tainacan-checkbox-list-page-changer"
                        @click="beforePage">
                    <b-icon
                            icon="chevron-left"/>
                </a>
                <ul class="tainacan-modal-checkbox-list-body">
                    <li
                            v-for="(option, key) in options"
                            :key="key">
                        <b-checkbox
                                v-model="selected"
                                :native-value="option.value">
                            {{ `${option.label}` }}
                        </b-checkbox>
                    </li>
                </ul>
                <a
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
                            :addons="false"
                            v-if="finderColumn.length"
                            class="tainacan-li-checkbox-modal"
                            v-for="(option, index) in finderColumn"
                            :id="`${key}.${index}-tainacan-li-checkbox-model`"
                            :ref="`${key}.${index}-tainacan-li-checkbox-model`"
                            :key="index">
                        <b-checkbox
                                v-model="selected"
                                :native-value="option.id">
                            {{ `${option.name}` }}
                        </b-checkbox>
                        <a @click="getOptionChildren(option, key, index)">
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
                    <li
                            v-else
                            class="tainacan-li-no-children">
                        <section
                                class="field is-grouped-centered">
                            <div class="content has-text-gray has-text-centered">
                                <p>
                                    <b-icon
                                            icon="format-list-checks"
                                            size="is-medium"/>
                                </p>
                                <p>{{ $i18n.get('info_no_more_options') }}</p>
                            </div>
                        </section>
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
                            v-for="(option, key) in searchResults"
                            :key="key">
                        <b-checkbox
                                v-model="selected"
                                :native-value="option.id ? option.id : option.value">
                            {{ `${option.name ? option.name : option.label}` }}
                        </b-checkbox>
                    </li>
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
                maxNumSearchResultsShow: 18,
                maxNumOptionsCheckboxFinderColumns: 100,
                checkboxListOffset: 0,
                collection: this.collection_id,
            }
        },
        created() {
            if(this.isTaxonomy) {
                this.getOptionChildren();
            } else {
                this.getOptions(0);
            }
        },
        methods: {
            beforePage(){
                this.checkboxListOffset -= this.maxNumOptionsCheckboxList;

                this.getOptions(this.checkboxListOffset);
            },
            nextPage(){
                this.checkboxListOffset = this.options.length;

                this.getOptions(this.checkboxListOffset);
            },
            getOptions(offset){
                if ( this.metadatum_type === 'Tainacan\\Metadata_Types\\Relationship' ) {
                    let collectionTarget = ( this.metadatum_object && this.metadatum_object.metadata_type_options.collection_id ) ?
                        this.metadatum_object.metadata_type_options.collection_id : this.collection_id;

                    this.getValuesRelationship( collectionTarget, this.optionName, [], offset, this.maxNumOptionsCheckboxList, true);
                } else {
                    this.getValuesPlainText( this.metadatum_id, this.optionName, this.isRepositoryLevel, [], offset, this.maxNumOptionsCheckboxList, true);
                }
            },
            autoComplete: _.debounce( function () {

                if(this.isTaxonomy) {
                    this.isSearching = !!this.optionName.length;

                    let query = `?hideempty=0&order=asc&number=${this.maxNumSearchResultsShow}&searchterm=${this.optionName}`;

                    axios.get(`/taxonomy/${this.taxonomy_id}/terms${query}`)
                        .then((res) => {
                            this.searchResults = res.data;
                        }).catch((error) => {
                        this.$console.log(error);
                    })
                } else {
                    this.isSearching = !!this.optionName.length;

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
                        if (this.finderColumns[f][0].id == children[0].id) {
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

                if(key != undefined) {
                    this.addToHierarchicalPath(key, index);
                }

                let parent = 0;

                if (option) {
                    parent = option.id;
                }

                let query = `?hideempty=0&order=asc&parent=${parent}&number=${this.maxNumOptionsCheckboxFinderColumns}&offset=0`;

                this.isColumnLoading = true;

                axios.get(`/taxonomy/${this.taxonomy_id}/terms${query}`)
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

                    let query = `?hideempty=0&order=asc&parent=${parent}&number=${this.maxNumOptionsCheckboxFinderColumns}&offset=${offset}`;

                    this.isColumnLoading = true;

                    axios.get(`/taxonomy/${this.taxonomy_id}/terms${query}`)
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

                let onlyLabels = [];

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
                            let valueIndex = this.finderColumns[i].findIndex(option => option.id == selected);

                            if (valueIndex >= 0) {
                                onlyLabels.push(this.finderColumns[i][valueIndex].name);
                            }
                        }
                    }

                    this.$eventBusSearch.$emit('sendValuesToTags', {
                        filterId: this.filter.id,
                        value: onlyLabels,
                    });
                } else {
                    this.$eventBusSearch.$emit('input', {
                        filter: 'checkbox',
                        compare: 'IN',
                        metadatum_id: this.metadatum_id,
                        collection_id: this.collection_id ? this.collection_id : this.filter.collection_id,
                        value: this.selected,
                    });

                    if(!isNaN(this.selected[0])){
                        for (let option of this.options) {
                            let valueIndex = this.selected.findIndex(item => item == option.value);

                            if (valueIndex >= 0) {
                                onlyLabels.push(this.options[valueIndex].label);
                            }
                        }
                    }

                    onlyLabels = onlyLabels.length ? onlyLabels : this.selected;

                    this.$eventBusSearch.$emit( 'sendValuesToTags', {
                        filterId: this.filter.id,
                        value: onlyLabels,
                    });
                }

                this.$root.$emit('appliedCheckBoxModal', onlyLabels);
            }
        }
    }
</script>

<style lang="scss" scoped>

    @import "../../scss/variables.scss";

    .tainacan-show-more {
        width: 100%;
        display: flex;
        justify-content: center;
        cursor: pointer;
        border: 1px solid $gray1;
        margin-top: 10px;
        margin-bottom: 0.2rem;
    }

    .tainacan-li-checkbox-modal:hover {
        background-color: $blue1;
    }

    .tainacan-finder-columns-container {
        background-color: white;
        border: solid 1px $gray1;
        display: flex;
        overflow: auto;
        padding: 0 !important;
        min-height: 253px;
    }

    .tainacan-finder-columns-container:focus {
        outline: none;
    }

    .tainacan-finder-column {
        border-right: solid 1px $gray1;
        max-height: 400px;
        min-height: inherit;
        min-width: 200px;
        overflow-y: auto;
        list-style: none;
        padding: 0 0.2rem 0 1rem;
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
        margin: 0 10px;

        &:hover {
            background-color: $blue1;
        }
    }

    .tainacan-modal-checkbox-list-body {
        columns: 2;
        list-style: none;
        width: 100%;
        align-self: baseline;
    }

    .tainacan-search-results-container {
        padding: 0 20px !important;
        min-height: 253px;
    }

    .tainacan-modal-checkbox-search-results-body {
        columns: 2;
        list-style: none;
    }

    .tainacan-li-no-children {
        padding: 3rem 1.5rem 3rem 0.5rem;
    }

    .tainacan-li-checkbox-last-active {
        background-color: $blue2;
    }

    .tainacan-li-checkbox-parent-active {
        background-color: $gray2;
    }

</style>


 
