<template>
    <div>
        <section
                style="position: relative;"
                v-if="!metadata || metadata.length <= 0"
                class="field is-grouped-centered section">
            <b-loading
                    :is-full-page="false"
                    :active.sync="metadataIsLoading"/>
            <div
                    v-if="!metadataIsLoading"
                    class="content has-text-gray has-text-centered">
                <p>
                    <b-icon
                            icon="format-list-checks"
                            size="is-large"/>
                </p>
                <p>{{ isRepositoryLevel ?
                    $i18n.get('info_there_are_no_metadata_in_repository_level' ) :
                     $i18n.get('info_there_are_no_metadata_to_search' ) }}</p>
            </div>
        </section>
        <div
                v-else
                :style="advancedSearchResults ? { 'padding-top': '0' } : { 'padding-top': '47px' }"
                :class="{ 'padding-in-header': isHeader, 'padding-regular': !isHeader }"
                class="tnc-advanced-search-container">

            <div
                    v-show="!advancedSearchResults"
                    v-for="searchCriterion in searchCriteria"
                    :key="searchCriterion"
                    class="field is-12 tainacan-form">

                <b-field
                        class="columns"
                        grouped>

                    <!-- Metadata (Search criteria) -->
                    <b-field
                            :class="{'is-3': isHeader}"
                            class="column">
                        <b-select
                                :placeholder="$i18n.get('instruction_select_a_metadatum')"
                                :disabled="advancedSearchQuery.taxquery[searchCriterion] ||
                                 advancedSearchQuery.metaquery[searchCriterion] ? true : false"
                                :value="advancedSearchQuery.metaquery[searchCriterion] ?
                                 advancedSearchQuery.metaquery[searchCriterion].originalMeta :
                                  (advancedSearchQuery.taxquery[searchCriterion] ? advancedSearchQuery.taxquery[searchCriterion].originalMeta : undefined)"
                                @input="addToAdvancedSearchQuery($event, 'metadatum', searchCriterion)">
                            <option
                                    v-for="(metadatum, metadatumIndex) in metadata"
                                    v-if="isRelationship(metadatum, metadatumIndex)"
                                    :value="`${metadatum.id}-${metadatum.metadata_type_options.taxonomy}-${metadatum.metadata_type_object.primitive_type}`"
                                    :key="metadatum.id"
                            >{{ metadatum.name }}</option>
                        </b-select>
                    </b-field>

                    <!-- Inputs -->
                    <b-field
                            :class="{'is-two-thirds': !isHeader}"
                            class="column">
                        <b-input
                                v-if="advancedSearchQuery.metaquery[searchCriterion] &&
                                 advancedSearchQuery.metaquery[searchCriterion].ptype != 'date'"
                                :type="advancedSearchQuery.metaquery[searchCriterion].ptype == 'int' ||
                                 advancedSearchQuery.metaquery[searchCriterion].ptype == 'float' ? 'number' : 'text'"
                                step="any"
                                @input="addValueToAdvancedSearchQuery($event, 'value', searchCriterion)"
                                :value="advancedSearchQuery.metaquery[searchCriterion].value"
                        />
                        <input
                            v-else-if="advancedSearchQuery.metaquery[searchCriterion] &&
                             advancedSearchQuery.metaquery[searchCriterion].ptype == 'date'"
                            class="input"
                            :value="parseDateToNavigatorLanguage(advancedSearchQuery.metaquery[searchCriterion].value)"
                            v-mask="dateMask"
                            @focus="addValueToAdvancedSearchQueryWithoutDelay($event, '', searchCriterion)"
                            @input="addValueToAdvancedSearchQueryWithoutDelay($event, 'value', searchCriterion)"
                            :placeholder="dateFormat" 
                            type="text">
                        <b-taginput
                                v-else-if="advancedSearchQuery.taxquery[searchCriterion]"
                                :data="terms"
                                :value="advancedSearchQuery.taxquery[searchCriterion] &&
                                 advancedSearchQuery.taxquery[searchCriterion].btags.length ?
                                 Array.from(new Set(advancedSearchQuery.taxquery[searchCriterion].btags)) : []"
                                autocomplete
                                :loading="advancedSearchQuery.taxquery[searchCriterion].isFetching == true"
                                attached
                                ellipsis
                                @remove="removeValueOf($event, searchCriterion)"
                                @add="addValueToAdvancedSearchQuery($event, 'terms', searchCriterion)"
                                @typing="autoCompleteTerm($event, searchCriterion)"
                                />
                        <b-input
                                v-else
                                type="text"
                                disabled />
                    </b-field>

                    <!-- Comparators -->
                    <b-field
                            :class="{'is-3': isHeader}"
                            class="column">
                        <b-select
                                v-if="advancedSearchQuery.taxquery[searchCriterion] ||
                                 advancedSearchQuery.metaquery[searchCriterion] ? true : false"
                                @input="addToAdvancedSearchQuery($event, 'comparator', searchCriterion)"
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

                    <div class="field">
                        <button
                                @click="removeThis(searchCriterion)"
                                class="button is-white is-pulled-right">
                            <b-icon
                                    type="is-secondary"
                                    icon="close"/>
                        </button>
                    </div>
                </b-field>

            </div>

            <!-- Add button -->
            <div
                    v-show="!advancedSearchResults"
                    :class="{'add-link-advanced-search-header': isHeader, 'add-link-advanced-search': !isHeader }"
                    class="field column is-12">
                <a
                    @click="addSearchCriteria"
                    style="font-size: 12px;">
                    <b-icon
                            class="add-i"
                            icon="plus-circle"
                            size="is-small"
                            type="is-secondary"/>
                    {{ searchCriteria.length &lt;= 0 ?
                        $i18n.get('add_one_search_criterion') :
                         $i18n.get('add_another_search_criterion')
                    }}
                </a>
            </div>

            <!-- Tags -->
            <div
                    v-show="advancedSearchResults"
                    class="field column is-12">
                <b-field 
                        grouped
                        group-multiline>
                    <div 
                            v-for="searchCriterion in searchCriteria"
                            :key="searchCriterion"
                            class="control taginput-container">
                        <b-tag
                                v-if="advancedSearchQuery.taxquery[searchCriterion] && advancedSearchQuery.taxquery[searchCriterion].terms"
                                type="is-white"
                                class="is-rounded"
                                @close="removeThis(searchCriterion)"
                                attached 
                                closable>
                                {{ Array.isArray(advancedSearchQuery.taxquery[searchCriterion].terms) ?
                                 advancedSearchQuery.taxquery[searchCriterion].btags.toString() :
                                  advancedSearchQuery.taxquery[searchCriterion].btags }}
                        </b-tag>
                        <b-tag 
                                v-else-if="advancedSearchQuery.metaquery[searchCriterion] && advancedSearchQuery.metaquery[searchCriterion].value"
                                type="is-white"
                                class="is-rounded"
                                @close="removeThis(searchCriterion)"
                                attached
                                :loading="isFetching" 
                                closable>
                                {{ advancedSearchQuery.metaquery[searchCriterion].value }}
                        </b-tag>
                    </div>
                </b-field>
            </div>

            <!-- Clear and search button -->
            <div
                    v-show="!advancedSearchResults"
                    class="column">
                <div class="field is-grouped is-pulled-right">
                    <p
                            v-if="Object.keys(this.advancedSearchQuery.taxquery).length > 0 ||
                             Object.keys(this.advancedSearchQuery.metaquery).length > 0"
                            class="control">
                        <button
                                @click="clearSearch"
                                class="button is-outlined">{{ $i18n.get('clear_search') }}</button>
                    </p>
                    <p class="control">
                        <button
                                @click="searchAdvanced"
                                class="button is-secondary">{{ $i18n.get('search') }}</button>
                    </p>
                </div>
            </div>
        </div>
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
            isHeader: false,
            advancedSearchResults: false,
            openFormAdvancedSearch: false,
            isDoSearch: false,
        },
        watch: {
          isDoSearch(){
              this.searchAdvanced();
          }
        },
        mounted(){
          this.$root.$on('metadatumUpdated', (isRepositoryLevel) => {
              if(isRepositoryLevel) {
                  this.metadataIsLoading = true;

                  this.fetchMetadata({
                      collectionId: this.isRepositoryLevel ? false : this.$route.params.collectionId,
                      isRepositoryLevel: this.isRepositoryLevel,
                      isContextEdit: false,
                      includeDisabled: false,
                      isAdvancedSearch: true
                  }).then((metadata) => {
                      this.metadata = metadata;
                      this.metadataIsLoading = false;
                  });
              }
          });
        },
        created(){

            this.metadataIsLoading = true;

            this.fetchMetadata({
                collectionId: this.isRepositoryLevel ? false : this.$route.params.collectionId,
                isRepositoryLevel: this.isRepositoryLevel,
                isContextEdit: false,
                includeDisabled: false,
                isAdvancedSearch: true
            }).then((metadata) => {
                this.metadata = metadata;
                this.metadataIsLoading = false;
            });

            let locale = navigator.language;

            moment.locale(locale);

            let localeData = moment.localeData();
            this.dateFormat = localeData.longDateFormat('L');

            if((this.$route.query.metaquery && Object.keys(this.$route.query.metaquery).length > 0) ||
                (this.$route.query.taxquery && Object.keys(this.$route.query.taxquery).length > 0) ){
                this.searchCriteria = [];
            }

            if(this.$route.query.metaquery && Object.keys(this.$route.query.metaquery).length > 0){

                let metaquery = this.$route.query.metaquery;

                for(let meta in metaquery){
                    this.$set(this.advancedSearchQuery.metaquery, `${meta}`, metaquery[meta]);
                }

                let keys = Object.keys(this.advancedSearchQuery.metaquery);

                let relationIndex = keys.findIndex((element) => {
                    return element == 'relation';
                });

                if(relationIndex != -1) {
                    keys.splice(relationIndex, 1);
                }

                for(let k of keys){
                    this.searchCriteria.push(k);
                }

            }

            if(this.$route.query.taxquery && Object.keys(this.$route.query.taxquery).length > 0){
                let taxquery = this.$route.query.taxquery;

                for(let tax in taxquery){
                    this.$set(this.advancedSearchQuery.taxquery, `${tax}`, taxquery[tax]);
                }

                let keys = Object.keys(this.advancedSearchQuery.taxquery);

                let relationIndex = keys.findIndex((element) => {
                    return element == 'relation';
                });

                if(relationIndex != -1) {
                    keys.splice(relationIndex, 1);
                }

                for(let k of keys){
                    this.searchCriteria.push(k);
                }
            }
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
                    'IN': this.$i18n.get('contains'),
                    'NOT IN': this.$i18n.get('not_contains')
                },
                searchCriteria: [1],
                advancedSearchQuery: {
                    advancedSearch: true,
                    metaquery: {},
                    taxquery: {}
                },
                termList: [],
                terms: [],
                dateMask: this.getDateLocaleMask(),
                dateFormat: '',
                metadataIsLoading: false,
                metadata: [],
            }
        },
        methods: {
            ...mapActions('taxonomy', [
                'fetchTerms'
            ]),
            ...mapActions('metadata', [
                'fetchMetadata'
            ]),
            autoCompleteTerm: _.debounce( function(value, searchCriterion){
                if(!value){
                    return;
                }

                this.termList = [];
                this.terms = [];
                this.$set(this.advancedSearchQuery.taxquery[searchCriterion], 'isFetching', 1);

                this.fetchTerms({ 
                    taxonomyId: this.advancedSearchQuery.taxquery[searchCriterion].taxonomy_id,
                    fetchOnly: { 
                        fetch_only: {
                            0: 'name',
                            1: 'id'
                        }
                    },
                    search: { 
                        searchterm: value
                    },
                    all: true,
                    order: 'asc',
                    offset: 0,
                    number: 10,
                }).then((res) => {
                    this.termList = res.terms;

                    for(let term in this.termList){
                        this.terms.push(this.termList[term].name);
                        this.termList[term].i = this.terms.length - 1;
                    }

                    this.$set(this.advancedSearchQuery.taxquery[searchCriterion], 'isFetching', 0);
                }).catch((error) => {
                    this.$set(this.advancedSearchQuery.taxquery[searchCriterion], 'isFetching', 0);
                    throw error;
                });
            }, 300),
            isRelationship(metadatum, metadatumIndex){
                if(!metadatum){
                    return false;
                } else if(metadatum.metadata_type.includes('Relationship')){
                    this.metadata.splice(metadatumIndex, 1);

                    return false;
                }

                return true;
            },
            getComparators(searchCriterion){
                if(this.advancedSearchQuery.taxquery[searchCriterion]){
                    return this.taxqueryOperators;
                } else if(this.advancedSearchQuery.metaquery[searchCriterion]){
                    if(this.advancedSearchQuery.metaquery[searchCriterion].ptype == 'int' ||
                    this.advancedSearchQuery.metaquery[searchCriterion].ptype == 'float' ||
                    this.advancedSearchQuery.metaquery[searchCriterion].ptype == 'date'){
                        return this.metaqueryOperatorsForInterval;
                    } else{
                        return this.metaqueryOperatorsRegular;
                    }
                }
            },
            removeThis(searchCriterion){
                let criteriaIndex = this.searchCriteria.findIndex((element) => {
                    return element == searchCriterion;
                });

                this.searchCriteria.splice(criteriaIndex, 1);

                if(this.advancedSearchQuery.taxquery[searchCriterion]){
                    delete this.advancedSearchQuery.taxquery[searchCriterion];
                } else if(this.advancedSearchQuery.metaquery[searchCriterion]){
                    delete this.advancedSearchQuery.metaquery[searchCriterion];
                }
            },
            removeValueOf(value, searchCriterion){
                if(this.advancedSearchQuery.taxquery[searchCriterion]){
                    let tagIndex = this.advancedSearchQuery.taxquery[searchCriterion].btags.findIndex((element) => {
                        return element == value;
                    });

                    this.advancedSearchQuery.taxquery[searchCriterion].btags.splice(tagIndex, 1);
                    this.advancedSearchQuery.taxquery[searchCriterion].terms.splice(tagIndex, 1);
                } else if(this.advancedSearchQuery.metaquery[searchCriterion]){
                    this.$set(this.advancedSearchQuery.metaquery[searchCriterion], 'value', '');
                }
            },
            // hasTagIn(value, searchCriterion){
            //     return !!this.advancedSearchQuery.taxquery[searchCriterion].btags.find((element) => {
            //         return element == value;
            //     });
            // },
            addSearchCriteria(){
                let aleatoryKey = Math.floor(Math.random() * (1000 - 2 + 1)) + 2;

                let found = this.searchCriteria.find((element) => {
                    return element == aleatoryKey;
                });

                if(found == undefined){
                    this.searchCriteria.push(aleatoryKey);
                } else {
                    this.addSearchCriteria();
                }
            },
            clearSearch(){
                this.searchCriteria = [1];
                this.advancedSearchQuery = {
                    advancedSearch: true,
                    metaquery: {},
                    taxquery: {}
                };
            },
            convertDateToMatchInDB(dateValue){
                return moment(dateValue,  this.dateFormat).toISOString().split('T')[0];
            },
            addValueToAdvancedSearchQueryWithoutDelay($event, type, searchCriterion){
                if(type == ''){
                    this.$set($event.target, 'value', '');
                    this.addToAdvancedSearchQuery('', 'value', searchCriterion);
                } else {               
                    this.addToAdvancedSearchQuery($event.target.value, type, searchCriterion);
                }
            },
            addValueToAdvancedSearchQuery: _.debounce(function(value, type, searchCriterion) {
                this.addToAdvancedSearchQuery(value, type, searchCriterion);
            }, 900),
            searchAdvanced(){

                if(this.isHeader){
                    this.$root.$emit('closeAdvancedSearchShortcut', true);

                    if(this.$route.path == '/items') {
                        this.$root.$emit('openAdvancedSearch', true);
                    }

                    if(this.$route.path != '/items') {
                        this.$router.push({
                            path: '/items',
                        });
                    }
                }

                if(Object.keys(this.advancedSearchQuery.taxquery).length > 0 &&
                 Object.keys(this.advancedSearchQuery.metaquery).length > 0){
                    this.advancedSearchQuery.relation = 'AND';
                } 

                if(Object.keys(this.advancedSearchQuery.taxquery).length > 1){
                    this.$set(this.advancedSearchQuery.taxquery, 'relation', 'AND');
                } else if(this.advancedSearchQuery.taxquery.hasOwnProperty('relation')){
                    delete this.advancedSearchQuery.taxquery.relation;
                }

                // Convert date values to a format (ISO_8601) that will match in database
                if(Object.keys(this.advancedSearchQuery.metaquery).length > 0){
                    for(let metaquery in this.advancedSearchQuery.metaquery){
                        if(this.advancedSearchQuery.metaquery[metaquery].ptype == 'date'){
                            let value = this.advancedSearchQuery.metaquery[metaquery].value;
                            
                            if(value.includes('/')){
                                this.$set(this.advancedSearchQuery.metaquery[metaquery], 'value', this.convertDateToMatchInDB(value));
                            }
                        }
                    }
                }

                if(Object.keys(this.advancedSearchQuery.metaquery).length > 1){
                    this.$set(this.advancedSearchQuery.metaquery, 'relation', 'AND');
                } else if(this.advancedSearchQuery.metaquery.hasOwnProperty('relation')){
                    delete this.advancedSearchQuery.metaquery.relation;
                }
                
                if(this.advancedSearchQuery.hasOwnProperty('relation') && Object.keys(this.advancedSearchQuery).length <= 3){
                    delete this.advancedSearchQuery.relation;
                }

                this.$eventBusSearch.$emit('searchAdvanced', this.advancedSearchQuery);
                
                if(Object.keys(this.advancedSearchQuery.metaquery).length > 0){
                    for(let metaquery in this.advancedSearchQuery.metaquery){
                        if(this.advancedSearchQuery.metaquery[metaquery].ptype == 'date'){
                            let value = this.advancedSearchQuery.metaquery[metaquery].value;
                            
                            setTimeout(() => {
                                if(value.includes('-')){
                                    this.$set(this.advancedSearchQuery.metaquery[metaquery], 'value', this.parseDateToNavigatorLanguage(value));
                                }
                            }, 200);
                        }
                    }
                }
            },
            parseDateToNavigatorLanguage(date){
                if(date && date.length === this.dateMask.length) {
                    date = new Date(date.replace(/-/g, '/'));

                    return moment(date, moment.ISO_8601).format(this.dateFormat);
                } else {
                    return date;
                }
            },
            addToAdvancedSearchQuery(value, type, searchCriterion){
                if(!value){
                    return;
                }

                if(type == 'metadatum'){
                    const criteriaKey = value.split('-');
                    
                    if(criteriaKey[1] != 'undefined'){
                        // Was selected a taxonomy criteria      
                        this.advancedSearchQuery.taxquery = Object.assign({}, this.advancedSearchQuery.taxquery, {
                            [`${searchCriterion}`]: {
                                taxonomy: criteriaKey[1],
                                terms: [],
                                btags: [],
                                field: 'term_id',
                                operator: 'IN',
                                originalMeta: value,
                                taxonomy_id: Number(criteriaKey[1].match(/[\d]+/)),
                                isFetching: 0,
                            }
                        });
                    } else {
                        // Was selected a metadatum criteria
                        if(criteriaKey[2] != 'date' && criteriaKey[2] != 'int' && criteriaKey[2] != 'float'){
                            this.advancedSearchQuery.metaquery = Object.assign({}, this.advancedSearchQuery.metaquery, {
                                [`${searchCriterion}`]: {
                                    key: Number(criteriaKey[0]),
                                    compare: 'LIKE',
                                    originalMeta: value,
                                }
                            });
                        } else {
                            this.advancedSearchQuery.metaquery = Object.assign({}, this.advancedSearchQuery.metaquery, {
                                [`${searchCriterion}`]: {
                                    key: Number(criteriaKey[0]),
                                    compare: '=',
                                    originalMeta: value,
                                }
                            });
                        }

                        this.$set(this.advancedSearchQuery.metaquery[searchCriterion], 'ptype', criteriaKey[2]);
                    }
                } else if(type == 'terms'){
                    let termIndex = this.terms.findIndex((element, index) => {
                        if(element == value && index == this.termList[index].i){
                            return true;
                        }
                    });

                    this.advancedSearchQuery.taxquery[searchCriterion].terms.push(this.termList[termIndex].id);
                    this.advancedSearchQuery.taxquery[searchCriterion].btags.push(value);
                    this.terms = [];
                } else if(type == 'value'){
                    this.$set(this.advancedSearchQuery.metaquery[searchCriterion], 'value', value);
                } else if(type == 'comparator'){
                    if(this.advancedSearchQuery.taxquery[searchCriterion]){
                        this.$set(this.advancedSearchQuery.taxquery[searchCriterion], 'operator', value);
                    } else if(this.advancedSearchQuery.metaquery[searchCriterion]){
                        this.$set(this.advancedSearchQuery.metaquery[searchCriterion], 'compare', value);
                    }
                }
            },
        }
    }
</script>

<style lang="scss">

    @import '../../scss/_variables.scss';

    .padding-in-header {
        padding-right: 3.3%;
        padding-left: 3.7%;
    }

    .padding-regular {
        padding-right: $page-side-padding;
        padding-left: $page-side-padding;
    }

    .tnc-advanced-search-container {
        padding-bottom: 47px;

        .column {
            padding: 0 0.5rem 0.75rem !important;
        }

        .control {
            .select{
                width: 100% !important;
                select{
                    width: 100% !important;
                }
            }
        }

        .taginput-container {
            min-height: 32px !important;

            .tags {
                margin-bottom: calc(0.275em - 1px) !important;

                .tag {
                    height: 2em !important;
                    padding-left: 0.75em !important;
                    padding-right: 0.75em !important;
                    margin-right: 0 !important;
                }
            }
        }

        .add-link-advanced-search {
            margin-top: -15px !important;
            padding-left: 8px !important;
        }

        .add-link-advanced-search-header {
            margin-top: -20px !important;
            padding: 0 !important;
            margin-left: -5px !important;
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

    .advanced-search-header-dropdown {
        height: 27px !important;

        .dropdown-content {
            width: 800px !important;
        }

        .autocomplete {
            .dropdown-menu {
                top: auto !important;
                min-width: 100% !important;

                .dropdown-content {
                    width: auto !important;
                }
            }
        }

        .dropdown-item:hover {
            background-color: unset !important;
        }

        @media screen and (min-width: 1087px) {
            .dropdown-menu {
                top: 0 !important;
            }
        }

        .dropdown-item {
            span.icon:not(.is-right) {
                position: relative !important;
            }
        }

        .advanced-search-text {
            margin: 0 12px;
            font-size: 12px;
            color: $blue5;
        }

        .advanced-search-text-di {
            font-size: 14px;
            font-weight: 500;
            color: #01295c;
            margin-top: 4px;
        }

        .advanced-search-hr {
            height: 1px;
            margin: 8px 0;
            background-color: #298596;
        }
    }

</style>