<template>
    <div>
        <div class="columns is-multiline tnc-advanced-search-container">

            <div
                    v-for="searchCriteria in searchCriterias"
                    :key="searchCriteria"
                    class="field column is-12 tainacan-form">

                <b-field
                        class="columns"
                        grouped>

                    <!-- Metadata (Search criterias) -->
                    <b-field class="column">
                        <b-select
                                :disabled="advancedSearchQuery.taxquery[searchCriteria] ||
                            advancedSearchQuery.metaquery[searchCriteria] ? true : false"
                                @input="addToAdvancedSearchQuery($event, 'metadatum', searchCriteria)">
                            <option
                                    v-for="metadatum in metadata"
                                    :value="`${metadatum.id}-${metadatum.metadata_type_options.taxonomy}-${metadatum.metadata_type_object.primitive_type}`"
                                    :key="metadatum.id"
                            >{{ metadatum.name }}</option>
                        </b-select>
                    </b-field>

                    <!-- Inputs -->
                    <b-field 
                            class="column is-two-thirds">
                        <b-input
                                v-if="advancedSearchQuery.metaquery[searchCriteria] &&
                                 advancedSearchQuery.metaquery[searchCriteria].ptype != 'date'"
                                :type="advancedSearchQuery.metaquery[searchCriteria].ptype == 'int' ||
                                 advancedSearchQuery.metaquery[searchCriteria].ptype == 'float' ? 'number' : 'text'"
                                @input="addValueToAdvancedSearchQuery($event, 'value', searchCriteria)"
                                />
                        <input
                            v-else-if="advancedSearchQuery.metaquery[searchCriteria] &&
                             advancedSearchQuery.metaquery[searchCriteria].ptype == 'date'"
                            class="input"
                            v-mask="dateMask"
                            @focus="addValueToAdvancedSearchQueryWithoutDelay($event, '', searchCriteria)"
                            @input="addValueToAdvancedSearchQueryWithoutDelay($event, 'value', searchCriteria)"
                            :placeholder="dateFormat" 
                            type="text">
                        <b-taginput
                                v-else-if="advancedSearchQuery.taxquery[searchCriteria]"
                                :data="terms"
                                autocomplete
                                :loading="isFetching"
                                attached
                                ellipsis
                                :before-adding="hasTagIn($event, searchCriteria)"
                                @remove="removeValueOf($event, searchCriteria)"
                                @add="addValueToAdvancedSearchQuery($event, 'terms', searchCriteria)"
                                @typing="autoCompleteTerm($event, searchCriteria)"
                                />
                    </b-field>

                    <!-- Comparators -->
                    <b-field 
                            class="column">
                        <b-select
                                v-if="advancedSearchQuery.taxquery[searchCriteria] ||
                                advancedSearchQuery.metaquery[searchCriteria] ? true : false"
                                @input="addToAdvancedSearchQuery($event, 'comparator', searchCriteria)"
                                :value="advancedSearchQuery.taxquery[searchCriteria] ?
                                 advancedSearchQuery.taxquery[searchCriteria].operator : 
                                 (advancedSearchQuery.metaquery[searchCriteria] ? advancedSearchQuery.metaquery[searchCriteria].compare : '')">

                            <option 
                                    v-for="(comparator, key) in getComparators(searchCriteria)"
                                    :key="key"
                                    :value="key"
                            >{{ comparator }}</option>
                        </b-select>
                    </b-field>

                    <div class="field">
                        <button
                                @click="removeThis(searchCriteria)"
                                class="button is-white is-pulled-right">
                            <b-icon
                                    size="is-small"
                                    icon="close"/>
                        </button>
                    </div>
                </b-field>

            </div>

            <!-- Add button -->
            <div
                    :style="{'padding-left': '25px !important'}"
                    class="field column is-12">
                <a
                    @click="addSearchCriteria"
                    class="is-secondary is-small">
                    <b-icon
                            icon="plus-circle"
                            size="is-small"
                            type="is-secondary"/>
                    {{ $i18n.get('add_another_search_criteria') }}</a>
            </div>

            <!-- Tags -->
            <div 
                class="field column is-12">
                <b-field 
                        grouped
                        group-multiline>
                    <div 
                            v-for="searchCriteria in searchCriterias"
                            :key="searchCriteria"
                            class="control taginput-container">
                        <b-tag 
                                v-if="(advancedSearchQuery.taxquery[searchCriteria] && advancedSearchQuery.taxquery[searchCriteria].terms)"
                                type="is-white"
                                class="is-rounded"
                                @close="removeThis(searchCriteria)" 
                                attached 
                                closable>
                                {{ Array.isArray(advancedSearchQuery.taxquery[searchCriteria].terms) ?
                                 advancedSearchQuery.taxquery[searchCriteria].btags.toString() :
                                  advancedSearchQuery.taxquery[searchCriteria].btags }}
                        </b-tag>
                        <b-tag 
                                v-else-if="(advancedSearchQuery.metaquery[searchCriteria] && advancedSearchQuery.metaquery[searchCriteria].value)"
                                type="is-white"
                                class="is-rounded"
                                @close="removeThis(searchCriteria)" 
                                attached
                                :loading="isFetching" 
                                closable>
                                {{ advancedSearchQuery.metaquery[searchCriteria].value }}
                        </b-tag>
                    </div>
                </b-field>
            </div>

            <!-- Clear and search button -->
            <div class="column">
                <div class="field is-grouped is-pulled-right">
                    <p class="control">
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

    import { mapActions, mapGetters } from 'vuex';
    import { dateInter } from '../../js/mixins.js';
    import moment from 'moment';

    export default {
        name: "AdvancedSearch",
        mixins: [ dateInter ],
        props: {
            metadata: Array,
            isRepositoryLevel: false,
        },
        created(){
            let locale = navigator.language;

            moment.locale(locale);

            let localeData = moment.localeData();
            this.dateFormat = localeData.longDateFormat('L');
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
                searchCriterias: [1],
                advancedSearchQuery: {
                    advancedSearch: true,
                    metaquery: {},
                    taxquery: {},
                },
                termList: [],
                terms: [],
                isFetching: false,
                dateMask: this.getDateLocaleMask(),
                dateFormat: '',
            }
        },
        methods: {
            ...mapActions('taxonomy', [
                'fetchTerms'
            ]),
            ...mapGetters('taxonomy', [
                'getTerms'
            ]),
            autoCompleteTerm: _.debounce( function(value, searchCriteria){
                this.termList = [];
                this.terms = [];
                this.isFetching = true;

                this.fetchTerms({ 
                    taxonomyId: this.advancedSearchQuery.taxquery[searchCriteria].taxonomy_id,
                    fetchOnly: { 
                        fetch_only: {
                            0: 'name',
                            1: 'id'
                        }
                    },
                    search: { 
                        searchterm: value
                    },
                    all: false
                }).then((res) => {
                    this.termList = res;

                    for(let term in this.termList){
                        this.terms.push(this.termList[term].name);
                        let i = this.terms.length - 1;
                        this.termList[term].i = i;
                    }

                    this.isFetching = false;
                }).catch((error) => {
                    this.isFetching = false;
                    throw error;
                });
            }, 300),
            getComparators(searchCriteria){
                if(this.advancedSearchQuery.taxquery[searchCriteria]){
                    return this.taxqueryOperators;
                } else if(this.advancedSearchQuery.metaquery[searchCriteria]){
                    if(this.advancedSearchQuery.metaquery[searchCriteria].ptype == 'int' ||
                    this.advancedSearchQuery.metaquery[searchCriteria].ptype == 'float' ||
                    this.advancedSearchQuery.metaquery[searchCriteria].ptype == 'date'){
                        return this.metaqueryOperatorsForInterval;
                    } else{
                        return this.metaqueryOperatorsRegular;
                    }
                }
            },
            removeThis(searchCriteria){
                let criteriaIndex = this.searchCriterias.findIndex((element) => {
                    return element == searchCriteria;
                });

                this.searchCriterias.splice(criteriaIndex, 1);

                if(this.advancedSearchQuery.taxquery[searchCriteria]){
                    delete this.advancedSearchQuery.taxquery[searchCriteria];
                } else if(this.advancedSearchQuery.metaquery[searchCriteria]){
                    delete this.advancedSearchQuery.metaquery[searchCriteria];
                }
            },
            removeValueOf(value, searchCriteria){
                if(this.advancedSearchQuery.taxquery[searchCriteria]){
                    let tagIndex = this.advancedSearchQuery.taxquery[searchCriteria].btags.findIndex((element) => {
                        return element == value;
                    });

                    this.advancedSearchQuery.taxquery[searchCriteria].btags.splice(tagIndex, 1);
                    this.advancedSearchQuery.taxquery[searchCriteria].terms.splice(tagIndex, 1);
                } else if(this.advancedSearchQuery.metaquery[searchCriteria]){
                    this.$set(this.advancedSearchQuery.metaquery[searchCriteria], 'value', '');
                }
            },
            hasTagIn(value, searchCriteria){
                return !!this.advancedSearchQuery.taxquery[searchCriteria].btags.find((element) => {
                    return element == value;
                });
            },
            addSearchCriteria(){
                let aleatoryKey = Math.floor(Math.random() * 1000) + 2;

                let found = this.searchCriterias.find((element) => {
                    return element == aleatoryKey;
                });

                if(found == undefined){
                    this.searchCriterias.push(aleatoryKey);
                } else {
                    this.addSearchCriteria();
                }
            },
            clearSearch(){
                this.searchCriterias = [1];
                this.advancedSearchQuery = {
                    advancedSearch: true,
                    metaquery: {},
                    taxquery: {}
                };
            },
            convertDateToMatchInDB(dateValue){
                return moment(dateValue,  this.dateFormat).toISOString().split('T')[0];
            },
            addValueToAdvancedSearchQueryWithoutDelay($event, type, searchCriteria){
                if(type == ''){
                    this.$set($event.target, 'value', '');
                    this.addToAdvancedSearchQuery('', 'value', searchCriteria);
                } else {               
                    this.addToAdvancedSearchQuery($event.target.value, type, searchCriteria);
                }
            },
            addValueToAdvancedSearchQuery: _.debounce(function(value, type, searchCriteria) {
                this.addToAdvancedSearchQuery(value, type, searchCriteria);
            }, 900),
            searchAdvanced(){                
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
                                this.advancedSearchQuery.metaquery[metaquery].value = this.convertDateToMatchInDB(value);
                                //this.$set(this.advancedSearchQuery.metaquery[metaquery], 'value', this.convertDateToMatchInDB(value));
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
                            }, 110);
                        }
                    }
                }
            },
            parseDateToNavigatorLanguage(date){
                date = new Date(date.replace(/-/g, '/'));

                return moment(date, moment.ISO_8601).format(this.dateFormat);
            },
            addToAdvancedSearchQuery(value, type, searchCriteria){

                if(type == 'metadatum'){
                    const criteriaKey = value.split('-');
                    
                    if(criteriaKey[1] != 'undefined'){
                        // Was selected a taxonomy criteria      
                        this.advancedSearchQuery.taxquery = Object.assign({}, this.advancedSearchQuery.taxquery, {
                            [`${searchCriteria}`]: {
                                taxonomy: criteriaKey[1],
                                terms: [],
                                btags: [],
                                field: 'term_id',
                                operator: 'IN',
                                taxonomy_id: Number(criteriaKey[1].match(/[\d]+/))
                            }
                        });
                    } else {
                        // Was selected a metadatum criteria
                        if(criteriaKey[2] != 'date' && criteriaKey[2] != 'int' && criteriaKey[2] != 'float'){
                            this.advancedSearchQuery.metaquery = Object.assign({}, this.advancedSearchQuery.metaquery, {
                                [`${searchCriteria}`]: {
                                    key: Number(criteriaKey[0]),
                                    compare: 'LIKE'
                                }
                            });
                        } else {
                            this.advancedSearchQuery.metaquery = Object.assign({}, this.advancedSearchQuery.metaquery, {
                                [`${searchCriteria}`]: {
                                    key: Number(criteriaKey[0]),
                                    compare: '='
                                }
                            });
                        }

                        this.$set(this.advancedSearchQuery.metaquery[searchCriteria], 'ptype', criteriaKey[2]);
                    }
                } else if(type == 'terms'){
                    let termIndex = this.terms.findIndex((element, index) => {
                        if(element == value && index == this.termList[index].i){
                            return true;
                        }
                    });

                    this.advancedSearchQuery.taxquery[searchCriteria].terms.push(this.termList[termIndex].id);
                    this.advancedSearchQuery.taxquery[searchCriteria].btags.push(value);
                    this.terms = [];
                } else if(type == 'value'){
                    this.$set(this.advancedSearchQuery.metaquery[searchCriteria], 'value', value);
                } else if(type == 'comparator'){
                    if(this.advancedSearchQuery.taxquery[searchCriteria]){
                        this.$set(this.advancedSearchQuery.taxquery[searchCriteria], 'operator', value);
                    } else if(this.advancedSearchQuery.metaquery[searchCriteria]){
                        this.$set(this.advancedSearchQuery.metaquery[searchCriteria], 'compare', value);
                    }
                }
            },
        }
    }
</script>

<style lang="scss">

    @import '../../scss/_variables.scss';

    .tnc-advanced-search-container {
        padding-top: 47px;
        padding-right: $page-side-padding;
        padding-left: $page-side-padding;
        padding-bottom: 47px;

        .column {
            padding: 0 0.3rem 0.3rem !important;
        }

        .control {
            .select{
                width: 100% !important;
                select{
                    width: 100% !important;
                }
            }
        }
    }

</style>