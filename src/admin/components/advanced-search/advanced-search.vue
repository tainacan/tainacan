<template>
    <div>
        <div class="columns is-multiline tnc-advanced-search-container">

            <div
                    v-for="searchField in totalSearchMetadata"
                    :key="searchField"
                    class="field column is-12 tainacan-form">

                <b-field
                        class="columns"
                        grouped>
                    <b-field class="column">
                        <b-select
                                @input="addToAdvancedSearchQuery($event, 'field_id', searchField)">
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
                                @input="addValueToAdvancedSearchQuery($event, 'value', searchField)"/>
                    </b-field>

                    <b-field class="column">
                        <b-select
                                @input="addToAdvancedSearchQuery($event, 'compare', searchField)">
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
                            @click="addSearchMetadata"
                            class="is-secondary is-small">
                        {{ $i18n.get('add_more_one_search_field') }}</a>
                </div>

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
        <pre>{{ advancedSearchQuery }}</pre>
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
                totalSearchMetadata: 1,
                advancedSearchQuery: {
                    advancedSearch: true,
                    metaquery: {
                        relation: 'AND',
                    }
                },
            }
        },
        methods: {
            addSearchMetadata(){
                this.totalSearchMetadata++;
            },
            clearSearch(){
                this.totalSearchMetadata = 1;
                this.advancedSearchQuery = {
                    advancedSearch: true,
                    relation: 'AND',
                };
            },
            addValueToAdvancedSearchQuery: _.debounce(function(value, type, relation) {
                let vm = this;

                vm.addToAdvancedSearchQuery(value, type, relation);
            }, 900),
            searchAdvanced(){
                this.$eventBusSearch.$emit('searchAdvanced', this.advancedSearchQuery);
            },
            addToAdvancedSearchQuery(value, type, relation){
                if(this.advancedSearchQuery.metaquery.hasOwnProperty(relation)){
                    //if(this.advancedSearchQuery[relation].compare === 'IN'){
                        //this.advancedSearchQuery[relation][type] = value.split(' ');
                    //} else {
                    this.advancedSearchQuery.metaquery[relation][type] = value;
                    //}
                } else {
                    this.advancedSearchQuery.metaquery = Object.assign({}, this.advancedSearchQuery.metaquery, {
                        [`${relation}`]: {
                            [`${type}`]: value,
                        }
                    });
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