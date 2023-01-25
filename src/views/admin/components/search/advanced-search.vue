<template>
    <form 
            tabindex="0"
            @submit.prevent.stop="performAdvancedSearch"
            class="tnc-advanced-search-container">
        <h3>{{ $i18n.get('advanced_search') }}</h3>
        <transition-group name="filter-item">
            <b-field
                    v-for="(searchCriterion, index) in searchCriteria"
                    :key="index + '-' + searchCriterion.index + '-' + searchCriterion.type"
                    grouped
                    class="tainacan-form">

                <!-- Metadata (Search criteria) -->
                <b-field class="column">
                    <b-select
                            :loading="isLoadingMetadata"
                            :placeholder="$i18n.get('instruction_select_a_metadatum')"
                            :aria-label="$i18n.get('instruction_select_a_metadatum')"
                            :disabled="advancedSearchQuery[searchCriterion.type] && advancedSearchQuery[searchCriterion.type][searchCriterion.index]"
                            :value="(advancedSearchQuery[searchCriterion.type] && advancedSearchQuery[searchCriterion.type][searchCriterion.index] ) ? advancedSearchQuery[searchCriterion.type][searchCriterion.index].key : null"
                            @input="addMetadatumToAdvancedSearchQuery(
                                { 
                                    metadatumId: $event,
                                    type: (metadataAsObject[$event] && metadataAsObject[$event].metadata_type_object) ? metadataAsObject[$event].metadata_type_object.primitive_type : '',
                                    taxonomy: (metadataAsObject[$event] && metadataAsObject[$event].metadata_type_options) ? metadataAsObject[$event].metadata_type_options.taxonomy : ''
                                }, 
                                searchCriterion,
                                index
                            )">
                        <template v-for="(metadatum, metadatumIndex) in metadataAsArray">
                            <option
                                    v-if="metadatum.metadata_type_object.component !== 'tainacan-user' &&
                                        metadatum.metadata_type_object.component !== 'tainacan-geocoordinate' &&
                                        metadatum.metadata_type_object.component !== 'tainacan-relationship' &&
                                        metadatum.metadata_type_object.component !== 'tainacan-compound' &&
                                        metadatum.parent <= 0"
                                    :value="metadatum.id"
                                    :key="metadatumIndex">
                                {{ metadatum.name }}
                            </option>
                            <optgroup
                                    v-if="metadatum.metadata_type_object.component === 'tainacan-compound'"
                                    :key="metadatumIndex"
                                    :label="metadatum.name">
                                <template v-for="(childMetadatum, childIndex) of metadatum.metadata_type_options.children_objects">
                                    <option
                                            v-if="childMetadatum.metadata_type_object.component !== 'tainacan-user' &&
                                                childMetadatum.metadata_type_object.component !== 'tainacan-geocoordinate' &&
                                                childMetadatum.metadata_type_object.component !== 'tainacan-relationship'"
                                            :key="childIndex"
                                            :value="childMetadatum.id">
                                        {{ childMetadatum.name }}
                                    </option>
                                </template>
                            </optgroup>
                        </template>
                        <option value="document_content_index">
                            {{ $i18n.get('label_document') }}
                        </option>
                    </b-select>
                </b-field>

                <!-- Comparators -->
                <b-field class="column">
                    <b-select
                            :loading="isLoadingMetadata"
                            v-if="searchCriterion.type == 'metaquery' && advancedSearchQuery.metaquery[searchCriterion.index]"
                            @input="addComparatorToAdvancedSearchQuery($event, searchCriterion)"
                            :value="advancedSearchQuery.metaquery[searchCriterion.index].compare"
                            :placeholder="$i18n.get('label_criterion_to_compare')"
                            :aria-label="$i18n.get('label_criterion_to_compare')">
                        <option 
                                v-for="(comparator, key) in getComparators(searchCriterion)"
                                :key="key"
                                :value="key"
                        >{{ comparator }}</option>
                    </b-select>
                    <b-select
                            :loading="isLoadingMetadata"
                            v-else-if="searchCriterion.type == 'taxquery' && advancedSearchQuery.taxquery[searchCriterion.index]"
                            @input="addComparatorToAdvancedSearchQuery($event, searchCriterion)"
                            :value="advancedSearchQuery.taxquery[searchCriterion.index].operator"
                            :placeholder="$i18n.get('label_criterion_to_compare')"
                            :aria-label="$i18n.get('label_criterion_to_compare')">
                        <option 
                                v-for="(comparator, key) in getComparators(searchCriterion)"
                                :key="key"
                                :value="key"
                        >{{ comparator }}</option>
                    </b-select>
                    <b-input
                            v-else
                            type="text"
                            disabled
                            :aria-label="$i18n.get('label_disabled')" />
                </b-field>

                <!-- Inputs -->
                <b-field class="column is-half">
                    <template v-if="searchCriterion.type == 'metaquery' && advancedSearchQuery.metaquery[searchCriterion.index]">
                        <b-input
                                v-if="getAdvancedSearchQueryCriterionMetadataType(searchCriterion.index) == 'int' || getAdvancedSearchQueryCriterionMetadataType(searchCriterion.index) == 'float'"
                                type="number"
                                step="any"
                                @input="addValueToAdvancedSearchQuery($event, searchCriterion)"
                                :value="advancedSearchQuery.metaquery[searchCriterion.index].value"
                                :placeholder="$i18n.get('label_number_to_search_for')"
                                :aria-label="$i18n.get('label_number_to_search_for')"
                        />
                        <input
                                v-else-if="getAdvancedSearchQueryCriterionMetadataType(searchCriterion.index) == 'date'"
                                class="input"
                                :value="parseValidDateToNavigatorLanguage(advancedSearchQuery.metaquery[searchCriterion.index].value)"
                                v-mask="dateMask"
                                @input="addValueToAdvancedSearchQuery($event.target.value, searchCriterion)"
                                :placeholder="dateFormat" 
                                type="text"
                                :aria-label="$i18n.get('label_date_to_search_for')" >
                        <b-input
                                v-else
                                type="text"
                                @input="addValueToAdvancedSearchQuery($event, searchCriterion)"
                                :value="advancedSearchQuery.metaquery[searchCriterion.index].value"
                                :placeholder="$i18n.get('label_string_to_search_for')"
                                :aria-label="$i18n.get('label_string_to_search_for')"
                        />
                    </template>
                    <b-input
                            v-else-if="searchCriterion.type == 'taxquery' && advancedSearchQuery.taxquery[searchCriterion.index]"
                            :value="advancedSearchQuery.taxquery[searchCriterion.index].terms"
                            @input="addValueToAdvancedSearchQuery($event, searchCriterion)"
                            type="text"
                            :placeholder="$i18n.get('label_string_to_search_for')"
                            :aria-label="$i18n.get('label_string_to_search_for')" />
                    <b-input
                            v-else
                            type="text"
                            disabled
                            :aria-label="$i18n.get('label_disabled')" />
                </b-field>

                <div class="field">
                    <button
                            @click.prevent="removeCriterion(searchCriterion)"
                            class="button is-white is-pulled-right has-text-secondary"
                            type="button"
                            :aria-label="$i18n.get('remove_search_criterion')">
                        <span 
                                v-tooltip="{
                                    content: $i18n.get('remove_search_criterion'),
                                    autoHide: true,
                                    placement: 'auto-end'
                                }"
                                class="icon">
                            <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-cancel"/>
                        </span>
                    </button>
                </div>

            </b-field>
        </transition-group>

        <!-- Add button -->
        <div class="add-link-advanced-search">
            <a 
                    role="button"
                    @click="addSearchCriteria">
                <span class="icon">
                    <i class="has-text-secondary tainacan-icon tainacan-icon-add"/>
                </span>
                {{ searchCriteria.length &lt;= 0 ?
                    $i18n.get('add_one_search_criterion') :
                        $i18n.get('add_another_search_criterion')
                }}
            </a>
            <a
                    role="button"
                    v-if="Object.keys(advancedSearchQuery.taxquery).length > 0 || Object.keys(advancedSearchQuery.metaquery).length > 0"
                    @click="clearSearch();">
                <span class="icon">
                    <i class="has-text-secondary tainacan-icon tainacan-icon-remove"/>
                </span>
                {{ $i18n.get('label_remove_all_criteria') }}
            </a>
        </div>

        <!-- Clear and search button -->
        <div 
                style="margin-bottom: 0;"
                class="field is-grouped is-justify-content-flex-end">
            <p class="control">
                <button
                        type="reset"
                        @click="clearSearch(); $emit('close')"
                        class="button is-outlined">
                    {{ $i18n.get('label_close_search') }}
                </button>
            </p>
            <p class="control">
                <button
                        aria-controls="items-list-results"
                        type="submit"
                        :disabled="!hasUpdatedSearch"
                        @click.prevent="performAdvancedSearch"
                        class="button is-secondary">
                    {{ $i18n.get('apply') }}
                </button>
            </p>
        </div>

        <b-loading 
                :is-full-page="false" 
                :active.sync="isLoadingMetadata" />
        
        <section
                v-if="!isLoadingMetadata && metadataAsArray && metadataAsArray.length <= 0"
                class="field is-grouped-centered section">
            <div class="content has-text-gray has-text-centered">
                <p>
                    <span class="icon is-large">
                        <i class="tainacan-icon tainacan-icon-36px tainacan-icon-metadata"/>
                    </span>
                </p>
                <p>
                    {{ isRepositoryLevel ? $i18n.get('info_there_are_no_metadata_in_repository_level' ) : $i18n.get('info_there_are_no_metadata_to_search' ) }}
                </p>
            </div>
        </section>

    </form>
</template>

<script>
    import { mapActions } from 'vuex';
    import { dateInter } from '../../js/mixins.js';
    import moment from 'moment';

    export default {
        name: "AdvancedSearch",
        mixins: [ dateInter ],
        props: {
            isRepositoryLevel: false,
            collectionId: ''
        },
        data() {
            return {
                metaqueryOperatorsForInterval: {
                    '=': this.$i18n.get('is_equal_to'),
                    '!=': this.$i18n.get('is_not_equal_to'),
                    '>': this.$i18n.get('greater_than'),
                    '<': this.$i18n.get('less_than'),
                    '>=': this.$i18n.get('greater_than_or_equal_to'),
                    '<=': this.$i18n.get('less_than_or_equal_to'),
                },
                metaqueryOperatorsRegular: {
                    '=': this.$i18n.get('is_equal_to'),
                    '!=': this.$i18n.get('is_not_equal_to'),
                    'LIKE': this.$i18n.get('contains'),
                    'NOT LIKE': this.$i18n.get('not_contains'),
                },
                taxqueryOperators: {
                    'LIKE': this.$i18n.get('contains'),
                    'NOT LIKE': this.$i18n.get('not_contains')
                },
                searchCriteria: [],
                advancedSearchQuery: {
                    advancedSearch: true,
                    metaquery: [],
                    taxquery: []
                },
                isLoadingMetadata: false,
                metadataAsObject: {},
                metadataAsArray: [],
                metadataSearchCancel: undefined,
                hasUpdatedSearch: false
            }
        },
        mounted() {
            this.isLoadingMetadata = true;

            this.fetchMetadata({
                collectionId: this.isRepositoryLevel ? false : this.collectionId,
                isRepositoryLevel: this.isRepositoryLevel,
                isContextEdit: false,
                includeDisabled: false,
                isAdvancedSearch: true,
                parent: 'any'
            }).then((resp) => {
                    resp.request
                        .then((metadata) => {

                            this.metadataAsArray = JSON.parse(JSON.stringify(metadata));

                            // In repository level, if set, we add fake options to search on every title and description
                            if (this.isRepositoryLevel && tainacan_plugin.tainacan_enable_core_metadata_on_advanced_search == true) {

                                 this.metadataAsArray.unshift({
                                    collection_id: 'default',
                                    id: 'tainacan_core_description',
                                    metadata_section_id: 'default_section',
                                    metadata_type: 'Tainacan\\Metadata_Types\\Core_Description',
                                    metadata_type_object: {
                                        className: "Tainacan\\Metadata_Types\\Core_Description",
                                        component: "tainacan-textarea",
                                        core: true,
                                        errors: null,
                                        form_component: "tainacan-form-textarea",
                                        name: this.$i18n.get('label_core_description'),
                                    },
                                    metadata_type_options: [],
                                    name: this.$i18n.get('label_core_description'),
                                    parent: 0,
                                    repository_level: null,
                                    slug: 'tainacan-core-description'
                                });

                                this.metadataAsArray.unshift({
                                    collection_id: 'default',
                                    id: 'tainacan_core_title',
                                    metadata_section_id: 'default_section',
                                    metadata_type: 'Tainacan\\Metadata_Types\\Core_Title',
                                    metadata_type_object: {
                                        className: "Tainacan\\Metadata_Types\\Core_Title",
                                        component: "tainacan-text",
                                        core: true,
                                        errors: null,
                                        form_component: "tainacan-form-text",
                                        name: this.$i18n.get('label_core_title'),
                                    },
                                    metadata_type_options: [],
                                    name: this.$i18n.get('label_core_title'),
                                    parent: 0,
                                    repository_level: null,
                                    slug: 'tainacan-core-title'
                                });
                            }

                            // We create and object keyed by IDs to easily match the query params,
                            // but keep an array version to use the order in the select
                            metadata.forEach(metadatum => {
                                this.metadataAsObject[metadatum.id] = metadatum;
                            });

                            // Search Request Token for cancelling
                            this.metadataSearchCancel = resp.source;

                            // Loads existing search query
                            this.buildAdvancedSearchQueryFromRoute();

                            this.isLoadingMetadata = false;
                        }).catch(() => {
                            this.isLoadingMetadata = false;
                        });
                })
                .catch(() => this.isLoadingMetadata = false);  
        },
        beforeDestroy() {
            // Cancels previous Request
            if (this.metadataSearchCancel != undefined)
                this.metadataSearchCancel.cancel('Metadata search Canceled.');

        },
        methods: {
            ...mapActions('metadata', [
                'fetchMetadata'
            ]),
            getComparators(searchCriterion) {
                if (searchCriterion.type == 'taxquery' && this.advancedSearchQuery.taxquery[searchCriterion.index]) {
                    return this.taxqueryOperators;
                } else if (searchCriterion.type == 'metaquery' && this.advancedSearchQuery.metaquery[searchCriterion.index]) {
                    const metadataType = this.getAdvancedSearchQueryCriterionMetadataType(searchCriterion.index);
                    if (metadataType == 'date' || metadataType == 'int' || metadataType == 'float')
                        return this.metaqueryOperatorsForInterval;
                    else
                        return this.metaqueryOperatorsRegular;
                }
            },
            buildAdvancedSearchQueryFromRoute() {

                const hasMetaQueries = this.$route.query.metaquery && Object.keys(this.$route.query.metaquery).length > 0;
                const hasTaxQueries = this.$route.query.taxquery && Object.keys(this.$route.query.taxquery).length > 0;
                if (hasMetaQueries || hasTaxQueries)
                    this.searchCriteria = [];

                if (hasMetaQueries) {

                    let metaquery = this.$route.query.metaquery;

                    for (let meta in metaquery) {
                        if (
                            Object.prototype.hasOwnProperty.call(this.metaqueryOperatorsRegular, metaquery[meta]['compare']) ||
                            Object.prototype.hasOwnProperty.call(this.metaqueryOperatorsForInterval, metaquery[meta]['compare'])
                        )
                            this.$set(this.advancedSearchQuery.metaquery, `${meta}`, metaquery[meta]);
                    }

                    let metakeys = Object.keys(this.advancedSearchQuery.metaquery);

                    let relationIndex = metakeys.findIndex((element) => element == 'relation');

                    if (relationIndex != -1)
                        metakeys.splice(relationIndex, 1);

                    for (let metakey of metakeys)
                        this.searchCriteria.push({ index: metakey, type: 'metaquery' });
                }

                if (hasTaxQueries) {
                    let taxquery = this.$route.query.taxquery;

                    for (let tax in taxquery) {
                        if ( Object.prototype.hasOwnProperty.call(this.taxqueryOperators, taxquery[tax]['operator']) )
                            this.$set(this.advancedSearchQuery.taxquery, `${tax}`, taxquery[tax]);
                    }

                    let taxkeys = Object.keys(this.advancedSearchQuery.taxquery);

                    let relationIndex = taxkeys.findIndex((element) => element == 'relation');

                    if (relationIndex != -1) 
                        taxkeys.splice(relationIndex, 1);

                    for (let taxkey of taxkeys)
                        this.searchCriteria.push({ index: taxkey, type: 'taxquery' });
                }

                // If we're coming from a preset advanced search, execute it,
                // otherwise, create an empty row.
                if (this.searchCriteria.length) {
                    this.$eventBusSearch.updateStoreFromURL();
                    this.performAdvancedSearch();
                } else {
                    this.addSearchCriteria();
                }
            },
            addSearchCriteria() {
                let aleatoryKey = Math.floor(Math.random() * (1000 - 2 + 1)) + 2;

                let existingKeyIndex = this.searchCriteria.findIndex((element) => element.index == aleatoryKey);

                if (existingKeyIndex < 0)
                    this.searchCriteria.push({ index: aleatoryKey, type: undefined });
                else
                    this.addSearchCriteria();
            },
            removeCriterion(searchCriterion) {
                
                // First, check if this criterion row is defined
                if (!searchCriterion.type) {
                    let searchCriterionIndex = this.searchCriteria.findIndex((element) => element.index == searchCriterion.index);
                    if (searchCriterionIndex >= 0)
                        this.searchCriteria.splice(searchCriterionIndex, 1);

                // If it was defined, then we need to update advancedSearchQuery properly when removing
                } else {
                    let searchCriterionIndex = this.searchCriteria.findIndex((element) => element.index == searchCriterion.index && element.type == searchCriterion.type);
                
                    if (searchCriterionIndex >= 0) {
                        
                        this.advancedSearchQuery[searchCriterion.type].splice(searchCriterion.index, 1);
                        this.searchCriteria.splice(searchCriterionIndex, 1);

                        for (let queryIndex = 0; queryIndex < this.advancedSearchQuery.metaquery.length; queryIndex++) { 
                            let isCriterionIndexUpdated = false;
                            let expectedIndex = queryIndex;
                            while(!isCriterionIndexUpdated && expectedIndex < this.searchCriteria.length) {
                                if (this.searchCriteria[expectedIndex] && this.searchCriteria[expectedIndex].type == 'metaquery') {
                                    this.$set(this.searchCriteria[expectedIndex], 'index', queryIndex);
                                    isCriterionIndexUpdated = true;
                                } else {
                                    expectedIndex++;
                                }
                            }
                        }
                        for (let queryIndex = 0; queryIndex < this.advancedSearchQuery.taxquery.length; queryIndex++) {
                            let isCriterionIndexUpdated = false;
                            let expectedIndex = queryIndex;
                            while(!isCriterionIndexUpdated && expectedIndex < this.searchCriteria.length) {
                                if (this.searchCriteria[expectedIndex] && this.searchCriteria[expectedIndex].type == 'taxquery') {
                                    this.$set(this.searchCriteria[expectedIndex], 'index', queryIndex);
                                    isCriterionIndexUpdated = true;
                                } else {
                                    expectedIndex++;
                                }
                            }
                        }
                    }
                }

                this.hasUpdatedSearch = true;
            },
            clearSearch() {
                this.searchCriteria = [];
                this.advancedSearchQuery = {
                    advancedSearch: true,
                    metaquery: [],
                    taxquery: []
                };
                this.hasUpdatedSearch = true;
            },
            convertDateToMatchInDB(dateValue) {
                return moment(dateValue,  this.dateFormat).toISOString().split('T')[0];
            },
            parseValidDateToNavigatorLanguage(date) {
                if (date && date.length === this.dateMask.length)
                    return (
                        moment(date, this.dateFormat).toISOString(true) &&
                        moment(date, this.dateFormat).toISOString(true).split('T') &&
                        moment(date, this.dateFormat).toISOString(true).split('T')[0]
                    ) ? this.parseDateToNavigatorLanguage(moment(date, this.dateFormat).toISOString(true).split('T')[0]) : this.parseDateToNavigatorLanguage(date);
                else
                    return date;
            },
            addMetadatumToAdvancedSearchQuery({ metadatumId, type, taxonomy }, searchCriterion, index) {
                if (!metadatumId)
                    return;
                    
                if (type === 'term') {
                    
                    // Convert fake placeholder criterion row to a tax row
                    let totalOfTaxCriteria = this.searchCriteria.reduce((counter, { type }) => type === 'taxquery' ? counter += 1 : counter, 0);
                    this.$set(this.searchCriteria[index], 'type', 'taxquery');
                    this.$set(this.searchCriteria[index], 'index', totalOfTaxCriteria);

                    // Was selected a taxonomy criteria      
                    this.advancedSearchQuery.taxquery.push({
                        key: metadatumId,
                        taxonomy: taxonomy,
                        operator: 'LIKE'
                    });
                } else {

                    // Convert fake placeholder criterion row to a meta row
                    let totalOfMetaCriteria = this.searchCriteria.reduce((counter, { type }) => type === 'metaquery' ? counter += 1 : counter, 0);
                    this.$set(this.searchCriteria[index], 'type', 'metaquery');
                    this.$set(this.searchCriteria[index], 'index', totalOfMetaCriteria);

                    // Was selected a metadatum criteria
                    if (type != 'date' && type != 'int' && type != 'float') {
                        this.advancedSearchQuery.metaquery.push({
                            key: metadatumId,
                            compare: 'LIKE'
                        });
                    } else {
                        this.advancedSearchQuery.metaquery.push({
                            key: metadatumId,
                            compare: '='
                        });
                    }
                }
            },
            addValueToAdvancedSearchQuery(value, searchCriterion) {
                if (!value)
                    return;

                if (searchCriterion.type == 'metaquery' && this.advancedSearchQuery.metaquery[searchCriterion.index])
                    this.$set(this.advancedSearchQuery.metaquery[searchCriterion.index], 'value', value);
                else if (searchCriterion.type == 'taxquery' && this.advancedSearchQuery.taxquery[searchCriterion.index])
                    this.$set(this.advancedSearchQuery.taxquery[searchCriterion.index], 'terms', value);

                this.hasUpdatedSearch = true;
            },
            addComparatorToAdvancedSearchQuery(comparator, searchCriterion) {
                if (!comparator)
                    return;

                if (searchCriterion.type == 'metaquery' && this.advancedSearchQuery.metaquery[searchCriterion.index])
                    this.$set(this.advancedSearchQuery.metaquery[searchCriterion.index], 'compare', comparator);
                else if (searchCriterion.type == 'taxquery' && this.advancedSearchQuery.taxquery[searchCriterion.index])
                    this.$set(this.advancedSearchQuery.taxquery[searchCriterion.index], 'operator', comparator);

                this.hasUpdatedSearch = true;
            },
            performAdvancedSearch() {

                if (
                    Object.keys(this.advancedSearchQuery.taxquery).length > 0 &&
                    Object.keys(this.advancedSearchQuery.metaquery).length > 0
                ) {
                    this.advancedSearchQuery.relation = 'AND';
                } 

                if ( Object.keys(this.advancedSearchQuery.taxquery).length > 1 )
                    this.$set(this.advancedSearchQuery.taxquery, 'relation', 'AND');
                else if ( Object.prototype.hasOwnProperty.call(this.advancedSearchQuery.taxquery, 'relation') )
                    delete this.advancedSearchQuery.taxquery.relation;

                // Convert date values to a format (ISO_8601) that will match in database
                if (Object.keys(this.advancedSearchQuery.metaquery).length > 0) {

                    for (let metaquery in this.advancedSearchQuery.metaquery) {
                        if (this.getAdvancedSearchQueryCriterionMetadataType(metaquery) == 'date') {
                            let value = this.advancedSearchQuery.metaquery[metaquery].value;
                            
                            if (value.includes('/'))
                                this.$set(this.advancedSearchQuery.metaquery[metaquery], 'value', this.convertDateToMatchInDB(value));
                        }
                    }
                }

                if ( Object.keys(this.advancedSearchQuery.metaquery).length > 1 )
                    this.$set(this.advancedSearchQuery.metaquery, 'relation', 'AND');
                else if ( Object.prototype.hasOwnProperty.call(this.advancedSearchQuery.metaquery, 'relation') )
                    delete this.advancedSearchQuery.metaquery.relation;
                
                if ( Object.prototype.hasOwnProperty.call(this.advancedSearchQuery, 'relation') && Object.keys(this.advancedSearchQuery).length <= 3)
                    delete this.advancedSearchQuery.relation;
                
                if (Object.keys(this.advancedSearchQuery.metaquery).length > 0) {

                    for (let metaquery in this.advancedSearchQuery.metaquery) {
                        if (this.getAdvancedSearchQueryCriterionMetadataType(metaquery) == 'date') {
                            let value = this.advancedSearchQuery.metaquery[metaquery].value;
                            
                            if (value.includes('-'))
                                this.$set(this.advancedSearchQuery.metaquery[metaquery], 'value', this.parseValidDateToNavigatorLanguage(value));
                        }
                    }
                }

                this.hasUpdatedSearch = false;
                this.$eventBusSearch.$emit('performAdvancedSearch', this.advancedSearchQuery);
            },
            getAdvancedSearchQueryCriterionMetadataType(searchCriterion) {
                if (this.advancedSearchQuery.metaquery[searchCriterion] &&
                    this.advancedSearchQuery.metaquery[searchCriterion].key &&
                    this.metadataAsObject[this.advancedSearchQuery.metaquery[searchCriterion].key] &&
                    this.metadataAsObject[this.advancedSearchQuery.metaquery[searchCriterion].key].metadata_type_object
                ) {
                    return this.metadataAsObject[this.advancedSearchQuery.metaquery[searchCriterion].key].metadata_type_object.primitive_type
                }

                return '';
            }
        }
    }
</script>

<style lang="scss">

    #advanced-search-container {
        width: calc(100% - (2 * var(--tainacan-one-column)));
        margin: 0 var(--tainacan-one-column) 0.875em;
        background: var(--tainacan-background-color);
        border: 1px solid var(--tainacan-input-border-color);
        border-radius: 1px;
        transition: height 0.2s ease;
    }

    .tnc-advanced-search-container {
        position: relative;
        padding: 1.25em;

        h3 {
            font-size: 1em !important;
            padding-top: 0 !important;
            color: var(--tainacan-heading-color) !important;
            margin-bottom: 1em !important;
        }
        .tainacan-form {
            margin-bottom: 0.125em !important;
        }
        .column {
            padding: 0;
        }
        .control {
            font-size: 1em;
            margin-bottom: 0px !important;

            .select{
                width: 100% !important;
                select{
                    width: 100% !important;
                }
            }
        }

        .add-link-advanced-search a {
            font-size: 0.8125em;
            display: inline-flex;
            align-items: center;
            margin-right: 1em;
        }

        @media screen and (max-width: 768px) {
            .is-12>.columns {
                flex-wrap: wrap;
            }
            .is-two-thirds {
                order: 3;
                flex-basis: 100%;
            }
        }

    }

</style>