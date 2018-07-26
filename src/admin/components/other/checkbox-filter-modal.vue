<template>
    <div
            class="tainacan-modal-content"
            style="width: auto">
        <header class="tainacan-modal-title">
            <h2>{{ this.$i18n.get('filter') }} <em>{{ filter.name }}</em></h2>
            <hr>
        </header>
        <section class="tainacan-form">
            <div class="is-clearfix tainacan-checkbox-search-section">
                <input
                        disabled
                        autocomplete="on"
                        :placeholder="$i18n.get('instruction_search')"
                        class="input">
                <span class="icon is-right">
                    <i
                            class="mdi mdi-magnify"/>
                </span>
            </div>
            <section
                    class="modal-card-body tainacan-finder-columns-container">
                <ul
                        class="tainacan-finder-column"
                        v-for="(finderColumn, key) in finderColumns"
                        :key="key">
                    <li
                            class="tainacan-li-checkbox-modal"
                            v-for="(option, index) in finderColumn"
                            :key="index">
                        <b-checkbox
                                v-model="selected"
                                :native-value="option.id">
                            {{ `${option.name} (${option.total_children})` }}
                        </b-checkbox>
                        <a @click="getOptionChildren(option, key)">
                            <b-icon
                                    class="is-pulled-right"
                                    icon="menu-right"
                                    />
                        </a>
                    </li>
                    <li>
                        <div
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
                <!--<pre>{{ selected }}</pre>-->
            </section>

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
        </section>
    </div>
</template>

<script>

    import {tainacan as axios} from '../../../js/axios/axios';

    export default {
        name: 'CheckboxFilterModal',
        props: {
            filter: '',
            parent: Number,
            taxonomy_id: Number,
            taxonomy: String,
            collection_id: Number,
            metadatum_id: Number,
            selected: Array,
        },
        data() {
            return {
                finderColumns: [],
                itemActive: false,
                isColumnLoading: false,
                loadingComponent: undefined,
            }
        },
        created() {
            this.getOptionChildren();
        },
        methods: {
            removeLevelsAfter(key){
                if(key != undefined){
                    this.finderColumns.splice(key+1);
                }
            },
            createColumn(children) {
                if (children.length > 0) {
                    let first = undefined;

                    for (let f in this.finderColumns) {
                        if (this.finderColumns[f][0].id == children[0].id) {
                            first = f;
                            break;
                        }
                    }

                    if (first != undefined) {
                        this.finderColumns.splice(first, 1, children);
                    } else {
                        this.finderColumns.push(children);
                    }
                }
            },
            appendMore(options, key) {
                for (let option of options) {
                    this.finderColumns[key].push(option)
                }
            },
            getOptionChildren(option, key) {
                let parent = 0;

                if (option) {
                    parent = option.id;
                }

                let query = `?hideempty=0&order=asc&parent=${parent}&number=100`;

                this.isColumnLoading = true;

                axios.get(`/taxonomy/${this.taxonomy_id}/terms${query}`)
                    .then(res => {
                        this.removeLevelsAfter(key);
                        this.createColumn(res.data);

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

                    let query = `?hideempty=0&order=asc&parent=${parent}&number=100&offset=${offset}`;

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

                this.$eventBusSearch.$emit('input', {
                    filter: 'checkbox',
                    taxonomy: this.taxonomy,
                    compare: 'IN',
                    metadatum_id: this.metadatum_id,
                    collection_id: this.collection_id,
                    terms: this.selected
                });

                let onlyLabels = [];

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
        border: 1px solid #f2f2f2;
        margin-top: 10px;
        margin-bottom: 0.2rem;
    }

    .tainacan-li-checkbox-modal:hover {
        background-color: #e6f4ff;
    }

    .tainacan-finder-columns-container {
        background-color: white;
        border: solid 1px #f2f2f2;
        display: flex;
        overflow: auto;
        padding: 0 !important;
    }

    .tainacan-finder-columns-container:focus {
        outline: none;
    }

    .tainacan-finder-column {
        border-right: solid 1px #f2f2f2;
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

</style>


 
