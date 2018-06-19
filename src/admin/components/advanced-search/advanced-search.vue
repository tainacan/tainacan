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
                                v-if="advancedSearchQuery.metaquery[searchCriteria]"
                                :type="advancedSearchQuery.metaquery[searchCriteria].ptype == 'date' ?
                                 'date' : (advancedSearchQuery.metaquery[searchCriteria].ptype == 'int' || advancedSearchQuery.metaquery[searchCriteria].ptype == 'float' ? 'number' : 'text')"
                                @input="addValueToAdvancedSearchQuery($event, 'value', searchCriteria)"
                                />
                        <b-taginput
                                v-else-if="advancedSearchQuery.taxquery[searchCriteria]"
                                :data="terms"
                                autocomplete
                                attached
                                ellipsis
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
                                @input="addToAdvancedSearchQuery($event, 'comparator', searchCriteria)">

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
            <div
                    :style="{'padding-left': '25px !important'}"
                    class="field column is-12">

                <div class="is-inline">
                    <b-icon
                            icon="plus-circle"
                            size="is-small"
                            type="is-secondary"/>
                    <a
                            @click="addSearchCriteria"
                            class="is-secondary is-small">
                        {{ $i18n.get('add_another_search_criteria') }}</a>
                </div>

            </div>
            <div 
                :class="{'tag-container-border': Object.keys(advancedSearchQuery.taxquery).length > 0 || Object.keys(advancedSearchQuery.metaquery).length > 0}"
                class="field column is-12">
                <b-field 
                        :style="{'padding': '0.3rem 0 0 0'}"
                        grouped
                        group-multiline>
                    <div 
                            v-for="searchCriteria in searchCriterias"
                            :key="searchCriteria"
                            class="control taginput-container">
                        <b-tag 
                                v-if="(advancedSearchQuery.taxquery[searchCriteria] && advancedSearchQuery.taxquery[searchCriteria].terms)"
                                type="is-white"
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
                                @close="removeThis(searchCriteria)" 
                                attached
                                :loading="isFetching" 
                                closable>
                                {{ Array.isArray(advancedSearchQuery.metaquery[searchCriteria].value) ?
                                 advancedSearchQuery.metaquery[searchCriteria].value:
                                  advancedSearchQuery.metaquery[searchCriteria].value }}
                        </b-tag>
                    </div>
                </b-field>
            </div>
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
        <!-- <pre>{{ advancedSearchQuery }}</pre> -->
    </div>
</template>

<script>

    import { mapActions, mapGetters } from 'vuex';

    export default {
        name: "AdvancedSearch",
        props: {
            metadata: Array,
            isRepositoryLevel: false,
        },
        data() {
            return {
                metaqueryOperators: {
                    '=': this.$i18n.get('is_equal_to'),
                    '!=': this.$i18n.get('is_not_equal_to'),
                    'LIKE': this.$i18n.get('contains'),
                    'NOT LIKE': this.$i18n.get('not_contains'),
                    '>': this.$i18n.get('greater_than'),
                    '<': this.$i18n.get('less_than'),
                    '>=': this.$i18n.get('greater_than_or_equal_to'),
                    '<=': this.$i18n.get('less_than_or_equal_to'),
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
                    }
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
                    return this.metaqueryOperators;
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
            removeValueOf(searchCriteria){
                if(this.advancedSearchQuery.taxquery[searchCriteria]){
                    this.$set(this.advancedSearchQuery.taxquery[searchCriteria], 'btags', []);
                    this.$set(this.advancedSearchQuery.taxquery[searchCriteria], 'terms', []);
                } else if(this.advancedSearchQuery.metaquery[searchCriteria]){
                    this.$set(this.advancedSearchQuery.metaquery[searchCriteria], 'value', '');
                }
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
            addValueToAdvancedSearchQuery: _.debounce(function(value, type, searchCriteria) {
                this.addToAdvancedSearchQuery(value, type, searchCriteria);
            }, 900),
            searchAdvanced(){
                if(Object.keys(this.advancedSearchQuery).length > 2){
                    this.advancedSearchQuery.relation = 'AND';
                } 

                if(Object.keys(this.advancedSearchQuery.taxquery).length > 1){
                    this.advancedSearchQuery.taxquery.relation = 'AND';
                } else if(this.advancedSearchQuery.taxquery.hasOwnProperty('relation')){
                    delete this.advancedSearchQuery.taxquery.relation;
                }

                if(Object.keys(this.advancedSearchQuery.metaquery).length > 1){
                    this.advancedSearchQuery.metaquery.relation = 'AND';
                } else if(this.advancedSearchQuery.metaquery.hasOwnProperty('relation')){
                    delete this.advancedSearchQuery.metaquery.relation;
                }
                
                if(this.advancedSearchQuery.hasOwnProperty('relation') && Object.keys(this.advancedSearchQuery).length <= 3){
                    delete this.advancedSearchQuery.relation;
                }

                this.$eventBusSearch.$emit('searchAdvanced', this.advancedSearchQuery);
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
                                taxonomy_id: Number(criteriaKey[1].match(/[\d]+/))
                            }
                        });
                    } else {
                        // Was selected a metadatum criteria
                        this.advancedSearchQuery.metaquery = Object.assign({}, this.advancedSearchQuery.metaquery, {
                            [`${searchCriteria}`]: {
                                key: Number(criteriaKey[0]),
                            }
                        });

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

        .tag-container-border {
            border: 1px solid $tainacan-input-background;

            span.tag{
                color: $secondary;
            }
        }

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