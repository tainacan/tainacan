<template>
    <div class="tainacan-modal-content">
        <header class="tainacan-modal-title">
            <h2>{{ modalTitle }}
                <small class="tainacan-total-objects-info">
                    {{ `(${objects.length} ${objectType})` }}
                </small>
            </h2>
            <hr>
        </header>
        <div class="tainacan-form">
            <div class="modal-card-body">
                <div
                        v-for="criterion in editionCriteria"
                        :key="criterion"
                        class="tainacan-bulk-edition-inline-fields">
                    <b-select
                            class="tainacan-bulk-edition-field tainacan-bulk-edition-field-not-last"
                            :placeholder="$i18n.get('instruction_select_a_metadatum')"
                            @input="addToBulkEditionProcedures($event, 'metadatum', criterion)">
                        <option
                                v-for="metadatum in metadata"
                                v-if="metadatum.id"
                                :value="metadatum.id"
                                :key="metadatum.id">
                            {{ metadatum.name }}
                        </option>
                    </b-select>

                    <b-select
                            class="tainacan-bulk-edition-field tainacan-bulk-edition-field-not-last"
                            :placeholder="$i18n.get('instruction_select_a_action')"
                            @input="addToBulkEditionProcedures($event, 'action', criterion)">
                        <option
                                v-for="(edtAct, key) in editionActions"
                                :value="edtAct"
                                :key="key">
                            {{ edtAct }}
                        </option>
                    </b-select>

                    <b-input
                            v-if="bulkEditionProcedures[criterion] &&
                             bulkEditionProcedures[criterion].metadatum &&
                             bulkEditionProcedures[criterion].action == editionActions.replace"
                            class="tainacan-bulk-edition-field tainacan-bulk-edition-field-not-last"
                            type="text"
                    />

                    <div
                            v-if="bulkEditionProcedures[criterion] &&
                             bulkEditionProcedures[criterion].metadatum &&
                             bulkEditionProcedures[criterion].action == editionActions.replace"
                            class="tainacan-bulk-edition-field tainacan-bulk-edition-field-not-last">
                        <small>
                        {{ $i18n.get('info_by_inner') }}
                        </small>
                    </div>

                    <component
                            v-if="bulkEditionProcedures[criterion] &&
                             bulkEditionProcedures[criterion].metadatum &&
                             bulkEditionProcedures[criterion].action"
                            :id="getMetadataByID(bulkEditionProcedures[criterion].metadatum).metadata_type_object.component +
                             '-' + getMetadataByID(bulkEditionProcedures[criterion].metadatum).slug"
                            :is="getMetadataByID(bulkEditionProcedures[criterion].metadatum).metadata_type_object.component"
                            :metadatum="{metadatum: getMetadataByID(bulkEditionProcedures[criterion].metadatum)}"
                            :class="{'tainacan-bulk-edition-field-last': bulkEditionProcedures[criterion].action != editionActions.replace}"
                            class="tainacan-bulk-edition-field"
                            @input="addToBulkEditionProcedures($event, 'newValue', criterion)"
                    />
                    <b-input
                            v-else
                            class="tainacan-bulk-edition-field tainacan-bulk-edition-field-last"
                            type="text"
                            disabled/>

                    <div class="field">
                        <button
                                @click="removeThis(criterion)"
                                class="button is-white is-pulled-right">
                            <b-icon
                                    type="is-secondary"
                                    icon="close"/>
                        </button>
                    </div>
                </div>
                <div>
                    <a
                            @click="addEditionCriterion()"
                            class="tainacan-add-edition-criterion-button">
                        <b-icon
                                icon="plus-circle"
                                size="is-small"
                                type="is-secondary"/>
                        {{ editionCriteria.length &lt;= 0 ?
                            $i18n.get('add_one_edition_criterion') :
                            $i18n.get('add_another_edition_criterion')
                        }}
                    </a>
                </div>
            </div>
            <pre>{{ bulkEditionProcedures }}</pre>

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
                            type="button"
                            class="button is-success">{{ $i18n.get('save') }}
                    </button>
                </div>
            </footer>
        </div>
    </div>
</template>

<script>
    export default {
        name: "BulkEditionModal",
        props: {
            modalTitle: String,
            objects: Array,
            objectType: String,
            metadata: Array,
        },
        data() {
            return {
                editionCriteria: [1],
                editionActions: {
                    remove: 'Remove',
                    redefine: 'Redefine',
                    replace: 'Replace',
                },
                bulkEditionProcedures: {
                    1: {}
                }
            }
        },
        methods: {
            addEditionCriterion() {
                let aleatoryKey = Math.floor(Math.random() * (1000 - 2 + 1)) + 2;

                let found = this.editionCriteria.find((element) => {
                    return element == aleatoryKey;
                });

                if (found == undefined) {
                    this.editionCriteria.push(aleatoryKey);
                    this.bulkEditionProcedures = Object.assign({}, this.bulkEditionProcedures, {[`${aleatoryKey}`]: {}});
                } else {
                    this.addEditionCriterion();
                }
            },
            removeThis(criterion){
                let criterionIndex = this.editionCriteria.findIndex((element) => {
                    return element == criterion;
                });

                if(this.editionCriteria[criterionIndex]){
                    this.editionCriteria.splice(criterionIndex, 1);
                }
            },
            getMetadataByID(id){
                let found = this.metadata.find((element) => {
                    return element.id == id;
                });

                return found;
            },
            addToBulkEditionProcedures(value, key, criterion){
                this.$set(this.bulkEditionProcedures[criterion], `${key}`, value);
            }
        },
    }
</script>

<style lang="scss">

    @import '../../scss/_variables.scss';

    .modal-card-body {
        padding: 0 !important;
    }

    .tainacan-total-objects-info {
        font-size: 12px;
        font-weight: normal;
    }

    .tainacan-bulk-edition-inline-fields {
        display: inline-flex;
        flex-direction: row;
        flex-wrap: wrap;
        width: 100%;

        .control {
            .select{
                width: 100% !important;
                select{
                    width: 100% !important;
                }
            }
        }

        .tainacan-bulk-edition-field {
            flex-grow: 1;
            flex-shrink: 1;
            text-align: center;
            padding-bottom: 9px;

            &:not(:first-child) {
                padding-left: 13px;
            }
        }

        .tainacan-bulk-edition-field-not-last {
            flex-basis: auto;
        }

        .tainacan-bulk-edition-field-last {
            flex-basis: 52%;
        }

    }

    .tainacan-add-edition-criterion-button {
        font-size: 12px;
        color: $turquoise5;
    }

</style>