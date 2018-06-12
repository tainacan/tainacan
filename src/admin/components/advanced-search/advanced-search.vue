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
                    <b-field class="column">
                        <b-select
                                @input="addToAdvancedSearchQuery($event, 'key', searchCriteria)">
                            <option
                                    v-for="metadata in metadataList"
                                    v-if="metadata.enabled"
                                    :value="metadata.id"
                                    :key="metadata.id"
                            >{{ metadata.name }}</option>
                        </b-select>
                    </b-field>

                    <b-field class="column is-two-thirds">
                        <b-input
                                @input="addValueToAdvancedSearchQuery($event, 'value', searchCriteria)"/>
                    </b-field>

                    <b-field class="column">
                        <b-select
                                @input="addToAdvancedSearchQuery($event, 'compare', searchCriteria)">
                            <option
                                    v-for="(opt, key) in compare"
                                    :value="key"
                                    :key="key"
                            >{{ opt }}</option>
                        </b-select>
                    </b-field>
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
                        {{ $i18n.get('add_more_one_search_criteria') }}</a>
                </div>

            </div>
            <div class="field column is-12">
                <b-field 
                        grouped
                        group-multiline>
                    <div 
                            v-for="searchCriteria in searchCriterias"
                            :key="searchCriteria"
                            class="control taginput-container">
                        <b-tag 
                                v-if="advancedSearchQuery[searchCriteria] && advancedSearchQuery[searchCriteria].value"
                                type="is-white"
                                @close="removeThis(searchCriteria)" 
                                attached 
                                closable>
                                {{ Array.isArray(advancedSearchQuery[searchCriteria].value) ?
                                 advancedSearchQuery[searchCriteria].value.toString() :
                                  advancedSearchQuery[searchCriteria].value }}
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
    </div>
</template>

<script>
    export default {
        name: "AdvancedSearch",
        props: {
            metadataList: Array,
            isRepositoryLevel: false,
        },
        data() {
            return {
                compare: {
                    '=': this.$i18n.get('is_equal_to'),
                    '!=': this.$i18n.get('is_not_equal_to'),
                    'IN': this.$i18n.get('contains'),
                    'NOT IN': this.$i18n.get('not_contains')
                },
                searchCriterias: [1],
                advancedSearchQuery: {
                    advancedSearch: true,
                },
            }
        },
        methods: {
            removeThis(searchCriteria){
                let criteriaIndex = this.searchCriterias.findIndex((element) => {
                    return element == searchCriteria;
                });

                this.searchCriterias.splice(criteriaIndex, 1);
                delete this.advancedSearchQuery[criteriaIndex];
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
                };
            },
            addValueToAdvancedSearchQuery: _.debounce(function(value, type, relation) {
                let vm = this;

                vm.addToAdvancedSearchQuery(value, type, relation);
            }, 900),
            searchAdvanced(){
                if(this.advancedSearchQuery.length > 2){
                    this.advancedSearchQuery.relation = 'AND';
                }

                this.$eventBusSearch.$emit('searchAdvanced', this.advancedSearchQuery);
            },
            addToAdvancedSearchQuery(value, type, relation){
                if(this.advancedSearchQuery.hasOwnProperty(relation)){
                    //if(this.advancedSearchQuery[relation].compare === 'IN'){
                        //this.advancedSearchQuery[relation][type] = value.split(' ');
                    //} else {
                    if(type == 'compare' && (this.advancedSearchQuery[relation]['compare'] == 'IN' ||
                     this.advancedSearchQuery[relation]['compare'] == 'NOT IN')){

                        this.advancedSearchQuery[relation].value.push(value);
                    } else {
                        this.advancedSearchQuery[relation][type] = value;
                    }
                    //}
                } else {
                    if(type == 'compare' && (value == 'IN' || value == 'NOT IN')){

                        this.advancedSearchQuery = Object.assign({}, this.advancedSearchQuery, {
                            [`${relation}`]: {
                                [`${type}`]: [value],
                            }
                        });
                    } else {
                        this.advancedSearchQuery = Object.assign({}, this.advancedSearchQuery, {
                            [`${relation}`]: {
                                [`${type}`]: value,
                            }
                        });
                    }                    
                }

                console.log(this.advancedSearchQuery);
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