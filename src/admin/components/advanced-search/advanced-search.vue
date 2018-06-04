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
                    <b-field class="column is-one-fifth">
                        <b-select
                                @input="addToAdvancedSearchQuery">
                            <option
                                    v-for="(metadata, key) in metadataList"
                                    v-if="metadata.enabled"
                                    :value="metadata.id"
                                    :key="key"
                            >{{ metadata.name }}</option>
                        </b-select>
                    </b-field>

                    <b-field class="column is-two-thirds">
                        <b-input
                                @input="addToAdvancedSearchQuery"/>
                    </b-field>

                    <b-field class="column is-one-fifth">
                        <b-select
                                @input="addToAdvancedSearchQuery">
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
                            type="is-info"/>
                    <a
                            @click="addSearchMetadata"
                            class="is-info is-small">
                        Adicionar mais um campo de busca</a>
                </div>

            </div>
            <div class="column">
                <div class="field is-grouped is-pulled-right">
                    <p class="control">
                        <button
                                @click="clearSearch"
                                class="button is-outlined">Limpar busca</button>
                    </p>
                    <p class="control">
                        <button
                                @click="searchAdvanced"
                                class="button is-secondary">Buscar</button>
                    </p>
                </div>
            </div>
            <pre>{{ metadataIds }} {{ toCompare }}</pre>
        </div>
    </div>
</template>

<script>
    export default {
        name: "AdvancedSearch",
        props: {
            metadataList: Array,
        },
        data(){
            return {
                compare: {'=':'Igual', '!=':'Diferente', 'IN':'Contém', 'NOT IN':'Não Contém'},
                totalSearchMetadata: 1,
                advancedSearchQuery: {},
            }
        },
        methods: {
            addSearchMetadata(){
                this.totalSearchMetadata++;
            },
            clearSearch(){
                this.totalSearchMetadata = 1;
            },
            searchAdvanced(){

            },
            addToAdvancedSearchQuery(optionValue){
                console.log(optionValue);
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
        padding-top: 47px;

        .column {
            padding: 0 0.3rem 0.3rem !important;
        }
    }

</style>