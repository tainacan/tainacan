<template>
    <div class="tnc-advanced-search-container">
        <transition-group name="filter-item">
            <b-field
                    v-for="searchCriterion in searchCriteria"
                    :key="searchCriterion"
                    grouped
                    class="tainacan-form">

                <!-- Metadata (Search criteria) -->
                <b-field class="column">
                    <b-select
                            :loading="isLoadingMetadata"
                            :placeholder="$i18n.get('instruction_select_a_metadatum')"
                            :disabled="(advancedSearchQuery.taxquery[searchCriterion] || advancedSearchQuery.metaquery[searchCriterion]) ? true : false"
                            :value="(
                                    advancedSearchQuery.metaquery[searchCriterion] ? advancedSearchQuery.metaquery[searchCriterion].key : null
                                ) || (
                                    advancedSearchQuery.taxquery[searchCriterion] ? advancedSearchQuery.taxquery[searchCriterion].key : null
                                )"
                            @input="addMetadatumToAdvancedSearchQuery(
                                { 
                                    metadatumId: $event,
                                    type: (metadataAsObject[$event] && metadataAsObject[$event].metadata_type_object) ? metadataAsObject[$event].metadata_type_object.primitive_type : '',
                                    taxonomy: (metadataAsObject[$event] && metadataAsObject[$event].metadata_type_options) ? metadataAsObject[$event].metadata_type_options.taxonomy : ''
                                }, 
                                searchCriterion
                            )">
                        <template v-for="(metadatum, metadatumIndex) in metadataAsArray">
                            <option
                                    v-if="metadatum.metadata_type_object.component !== 'tainacan-user' &&
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
                                <option
                                        v-for="(childMetadatum, childIndex) of metadatum.metadata_type_options.children_objects"
                                        :key="childIndex"
                                        :value="childMetadatum.id">
                                    {{ childMetadatum.name }}
                                </option>
                            </optgroup>
                        </template>
                        <option value="document_content_index-undefined-string">
                            {{ $i18n.get('label_document') }}
                        </option>
                    </b-select>
                </b-field>

                <!-- Comparators -->
                <b-field class="column">
                    <b-select
                            :loading="isLoadingMetadata"
                            v-if="advancedSearchQuery.taxquery[searchCriterion] ||
                                advancedSearchQuery.metaquery[searchCriterion] ? true : false"
                            @input="addComparatorToAdvancedSearchQuery($event, searchCriterion)"
                            :value="advancedSearchQuery.taxquery[searchCriterion] ?
                                advancedSearchQuery.taxquery[searchCriterion].operator :
                                (advancedSearchQuery.metaquery[searchCriterion] ? advancedSearchQuery.metaquery[searchCriterion].compare : '')">

                        <option 
                                v-for="(comparator, key) in getComparators(searchCriterion)"
                                :key="key"
                                :value="key"
                        >{{ comparator }}</option>
                    </b-select>
                    <b-input
                            v-else
                            type="text"
                            disabled />
                </b-field>

                <!-- Inputs -->
                <b-field class="column is-half">
                    <b-input
                            v-if="advancedSearchQuery.metaquery[searchCriterion] && getAdvancedSearchQueryCriterionMetadataType(searchCriterion) != 'date'"
                            :type="(getAdvancedSearchQueryCriterionMetadataType(searchCriterion) == 'int' || getAdvancedSearchQueryCriterionMetadataType(searchCriterion) == 'float') ? 'number' : 'text'"
                            step="any"
                            @input="addValueToAdvancedSearchQuery($event, searchCriterion)"
                            :value="advancedSearchQuery.metaquery[searchCriterion].value"
                    />
                    <input
                            v-else-if="getAdvancedSearchQueryCriterionMetadataType(searchCriterion) == 'date'"
                            class="input"
                            :value="parseValidDateToNavigatorLanguage(advancedSearchQuery.metaquery[searchCriterion].value)"
                            v-mask="dateMask"
                            @input="addValueToAdvancedSearchQuery($event.target.value, searchCriterion)"
                            :placeholder="dateFormat" 
                            type="text" >
                    <b-input
                            v-else-if="advancedSearchQuery.taxquery[searchCriterion]"
                            :value="advancedSearchQuery.taxquery[searchCriterion].terms"
                            @input="addValueToAdvancedSearchQuery($event, searchCriterion)"
                            type="text" />
                    <b-input
                            v-else
                            type="text"
                            disabled />
                </b-field>

                <div class="field">
                    <button
                            @click="removeCriterion(searchCriterion)"
                            class="button is-white is-pulled-right has-text-secondary">
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
                    @click="addSearchCriteria"
                    style="font-size: 0.75em;">
                <span class="icon is-small">
                    <i class="has-text-secondary tainacan-icon tainacan-icon-add"/>
                </span>
                {{ searchCriteria.length &lt;= 0 ?
                    $i18n.get('add_one_search_criterion') :
                        $i18n.get('add_another_search_criterion')
                }}
            </a>
        </div>

        <!-- Clear and search button -->
        <div class="field is-grouped is-justify-content-flex-end">
            <p
                    v-if="Object.keys(advancedSearchQuery.taxquery).length > 0 || Object.keys(advancedSearchQuery.metaquery).length > 0"
                    class="control">
                <button
                        @click="clearSearch(); performAdvancedSearch();"
                        class="button is-outlined">{{ $i18n.get('clear_search') }}</button>
            </p>
            <p class="control">
                <button
                        @click="performAdvancedSearch()"
                        class="button is-success">{{ $i18n.get('apply') }}</button>
            </p>
        </div>

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

</div>
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
            hasAdvancedSearchResults: false,
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
                searchCriteria: [1],
                advancedSearchQuery: {
                    advancedSearch: true,
                    metaquery: {},
                    taxquery: {}
                },
                isLoadingMetadata: false,
                metadataAsObject: {},
                metadataAsArray: [],
                metadataSearchCancel: undefined
            }
        },
        created() {
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
                            // We create and object keyed by IDs to easily match the query params,
                            // but keep an array version to use the order in the select
                            this.metadataAsArray = metadata;
                            metadata.forEach(metadatum => {
                                this.metadataAsObject[metadatum.id] = metadatum;
                            });

                            // Search Request Token for cancelling
                            this.metadataSearchCancel = resp.source;

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
                if (this.advancedSearchQuery.taxquery[searchCriterion]) {
                    return this.taxqueryOperators;
                } else if (this.advancedSearchQuery.metaquery[searchCriterion]) {
                    const metadataType = this.getAdvancedSearchQueryCriterionMetadataType(searchCriterion);
                    if (metadataType == 'date' || metadataType == 'int' || metadataType == 'float')
                        return this.metaqueryOperatorsForInterval;
                    else
                        return this.metaqueryOperatorsRegular;
                }
            },
            buildAdvancedSearchQueryFromRoute() {
                if (
                    (this.$route.query.metaquery && Object.keys(this.$route.query.metaquery).length > 0) ||
                    (this.$route.query.taxquery && Object.keys(this.$route.query.taxquery).length > 0)
                ) {
                    this.searchCriteria = [];
                }

                if (this.$route.query.metaquery && Object.keys(this.$route.query.metaquery).length > 0) {

                    let metaquery = this.$route.query.metaquery;

                    for (let meta in metaquery) {
                        if ( this.metaqueryOperatorsRegular.hasOwnProperty(metaquery[meta]['operator']) || this.metaqueryOperatorsForInterval.hasOwnProperty(metaquery[meta]['operator']) )
                            this.$set(this.advancedSearchQuery.metaquery, `${meta}`, metaquery[meta]);
                    }

                    let metakeys = Object.keys(this.advancedSearchQuery.metaquery);

                    let relationIndex = metakeys.findIndex((element) => element == 'relation');

                    if (relationIndex != -1)
                        metakeys.splice(relationIndex, 1);

                    for (let metakey of metakeys)
                        this.searchCriteria.push(metakey);

                }

                if (this.$route.query.taxquery && Object.keys(this.$route.query.taxquery).length > 0) {
                    let taxquery = this.$route.query.taxquery;

                    for (let tax in taxquery) {
                        if ( this.taxqueryOperators.hasOwnProperty(taxquery[tax]['operator']) )
                            this.$set(this.advancedSearchQuery.taxquery, `${tax}`, taxquery[tax]);
                    }

                    let taxkeys = Object.keys(this.advancedSearchQuery.taxquery);

                    let relationIndex = taxkeys.findIndex((element) => element == 'relation');

                    if (relationIndex != -1) 
                        taxkeys.splice(relationIndex, 1);

                    for (let taxkey of taxkeys)
                        this.searchCriteria.push(taxkey);
                }

                // If we're coming from a preset advanced search, execute it!
                if (this.searchCriteria.length) {
                    this.$eventBusSearch.updateStoreFromURL();
                    this.performAdvancedSearch();
                }
            },
            removeCriterion(searchCriterion) {
                let criteriaIndex = this.searchCriteria.findIndex((element) => element == searchCriterion);
                
                if (criteriaIndex >= 0)
                    this.searchCriteria.splice(criteriaIndex, 1);

                if (this.advancedSearchQuery.taxquery[searchCriterion])
                    delete this.advancedSearchQuery.taxquery[searchCriterion];
                else if (this.advancedSearchQuery.metaquery[searchCriterion])
                    delete this.advancedSearchQuery.metaquery[searchCriterion];
            },
            addSearchCriteria() {
                let aleatoryKey = Math.floor(Math.random() * (1000 - 2 + 1)) + 2;

                let found = this.searchCriteria.find((element) => element == aleatoryKey);

                if (found == undefined)
                    this.searchCriteria.push(aleatoryKey);
                else
                    this.addSearchCriteria();
            },
            clearSearch() {
                this.searchCriteria = [1];
                this.advancedSearchQuery = {
                    advancedSearch: true,
                    metaquery: {},
                    taxquery: {}
                };
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
            addMetadatumToAdvancedSearchQuery({ metadatumId, type, taxonomy }, index) {
                if (!metadatumId)
                    return;
                    
                if (type === 'term') {
                    // Was selected a taxonomy criteria      
                    this.advancedSearchQuery.taxquery = Object.assign({}, this.advancedSearchQuery.taxquery, {
                        [`${index}`]: {
                            key: metadatumId,
                            taxonomy: taxonomy,
                            operator: 'LIKE'
                        }
                    });
                } else {
                    // Was selected a metadatum criteria
                    if (type != 'date' && type != 'int' && type != 'float') {
                        this.advancedSearchQuery.metaquery = Object.assign({}, this.advancedSearchQuery.metaquery, {
                            [`${index}`]: {
                                key: metadatumId,
                                compare: 'LIKE'
                            }
                        });
                    } else {
                        this.advancedSearchQuery.metaquery = Object.assign({}, this.advancedSearchQuery.metaquery, {
                            [`${index}`]: {
                                key: metadatumId,
                                compare: '='
                            }
                        });
                    }
                }
            },
            addValueToAdvancedSearchQuery(value, index) {
                if (!value)
                    return;

                if (this.advancedSearchQuery.metaquery[index])
                    this.$set(this.advancedSearchQuery.metaquery[index], 'value', value);
                else if (this.advancedSearchQuery.taxquery[index])
                    this.$set(this.advancedSearchQuery.taxquery[index], 'terms', value);
            },
            addComparatorToAdvancedSearchQuery(comparator, index) {
                if (!comparator)
                    return;

                if (this.advancedSearchQuery.metaquery[index])
                    this.$set(this.advancedSearchQuery.metaquery[index], 'compare', comparator);
                else if (this.advancedSearchQuery.taxquery[index])
                    this.$set(this.advancedSearchQuery.taxquery[index], 'operator', comparator);
            },
            performAdvancedSearch() {
                
                if (
                    Object.keys(this.advancedSearchQuery.taxquery).length > 0 &&
                    Object.keys(this.advancedSearchQuery.metaquery).length > 0
                ) {
                    this.advancedSearchQuery.relation = 'AND';
                } 

                if (Object.keys(this.advancedSearchQuery.taxquery).length > 1)
                    this.$set(this.advancedSearchQuery.taxquery, 'relation', 'AND');
                else if (this.advancedSearchQuery.taxquery.hasOwnProperty('relation'))
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

                if (Object.keys(this.advancedSearchQuery.metaquery).length > 1)
                    this.$set(this.advancedSearchQuery.metaquery, 'relation', 'AND');
                else if (this.advancedSearchQuery.metaquery.hasOwnProperty('relation'))
                    delete this.advancedSearchQuery.metaquery.relation;
                
                if (this.advancedSearchQuery.hasOwnProperty('relation') && Object.keys(this.advancedSearchQuery).length <= 3)
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
        margin: 0.5em var(--tainacan-one-column) 0.875em;
        background: var(--tainacan-input-background-color);
        border: 1px solid var(--tainacan-input-border-color);
        border-radius: 1px;
    }

    .tnc-advanced-search-container {
        padding: 1em;
        padding-top: 3.5em;

        .column {
            padding: 0 0.5em 0.75em !important;
        }

        .control {
            font-size: 1em;
            .select{
                width: 100% !important;
                select{
                    width: 100% !important;
                }
            }
        }

        .add-link-advanced-search {
            padding-left: 8px !important;
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