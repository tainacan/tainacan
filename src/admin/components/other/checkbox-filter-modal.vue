<template>
    <form action="">
        <div
                class="tainacan-modal-content"
                style="width: auto">
            <header class="tainacan-modal-title">
                <h2>{{ this.$i18n.get('filter') }} <em>{{ filter.name }}</em></h2>
                <hr>
            </header>
            <section class="tainacan-form">
                <section
                        id="filter-modal-checkbox-body"
                        class="modal-card-body">
                    <ul>
                        <li
                                v-for="(optionLevel0, index) in optionsLevel0"
                                :key="index">
                            <b-checkbox
                                    v-model="selected"
                                    :native-value="optionLevel0.id"
                            >{{ optionLevel0.name }}
                            </b-checkbox>
                        </li>
                    </ul>
                    <pre>{{ selected }}</pre>
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
                                class="button is-success">{{ $i18n.get('apply') }}</button>
                    </div>
                </footer>
            </section>
        </div>
    </form>
</template>

<script>

    import finder from 'finderjs/index.js';

    export default {
        name: 'CheckboxFilterModal',
        props: {
            filter: '',
            parent: Number,
            taxonomy_id: Number,
            taxonomy: String,
            optionsLevel0: Array,
            selected: Array,
        },
        created() {

            let container = document.getElementById('filter-modal-checkbox-body');

            this.createFinder(container);
        },
        methods: {
            prepareDataForFinder(dataVanilla){
                return true;
            },
            getOptionChildren(){
                return true;
            },
            createFinder(container){
                let F = finder(container, {}, {});

                console.log(F);
            },
            applyFilter(){
                this.$emit('input', {
                    filter: 'checkbox',
                    taxonomy: this.taxonomy,
                    compare: 'IN',
                    metadatum_id: this.metadatum_id,
                    collection_id: this.collection_id,
                    terms: this.selected
                });

                let onlyLabels = [];
                for(let selected of this.selected) {
                    let valueIndex = this.options.findIndex(option => option.id == selected );
                    if (valueIndex >= 0)
                        onlyLabels.push(this.options[valueIndex].name)
                }

                this.$eventBusSearch.$emit("sendValuesToTags", {
                    filterId: this.filter.id,
                    value: onlyLabels
                });
            }
        }
    }
</script>

<style lang="scss" scoped>

</style>


 
