<template>
    <div class="tainacan-modal-content">
        <header class="tainacan-modal-title">
            <h2>{{ modalTitle }}
                <small class="tainacan-total-objects-info">
                    {{ `(${totalItems} ${objectType})` }}
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
                            :disabled="!!bulkEditionProcedures[criterion].metadatumID"
                            class="tainacan-bulk-edition-field tainacan-bulk-edition-field-not-last"
                            :placeholder="$i18n.get('instruction_select_a_metadatum')"
                            @input="addToBulkEditionProcedures($event, 'metadatumID', criterion)">
                        <option
                                v-for="metadatum in metadata"
                                v-if="metadatum.id"
                                :value="metadatum.id"
                                :key="metadatum.id">
                            {{ metadatum.name }}
                        </option>
                    </b-select>

                    <b-select
                            v-if="bulkEditionProcedures[criterion] &&
                            bulkEditionProcedures[criterion].metadatumID"
                            :disabled="!!bulkEditionProcedures[criterion].action"
                            class="tainacan-bulk-edition-field tainacan-bulk-edition-field-not-last"
                            :placeholder="$i18n.get('instruction_select_a_action')"
                            @input="addToBulkEditionProcedures($event, 'action', criterion)">
                        <template v-if="getMetadataByID(bulkEditionProcedures[criterion].metadatumID).multiple == 'yes'">
                            <option
                                    v-for="(edtAct, key) in editionActionsForMultiple"
                                    :value="edtAct"
                                    :key="key">
                                {{ edtAct }}
                            </option>
                        </template>
                        <template v-else>
                            <option
                                    v-for="(edtAct, key) in editionActionsForNotMultiple"
                                    :value="edtAct"
                                    :key="key">
                                {{ edtAct }}
                            </option>
                        </template>
                    </b-select>

                    <!-- DISABLED FIELD -->
                    <b-input
                            v-else
                            class="tainacan-bulk-edition-field tainacan-bulk-edition-field-not-last"
                            type="text"
                            disabled />

                    <!-- Replace or Redefine in case of multiple -->
                    <template
                            v-if="bulkEditionProcedures[criterion] &&
                             bulkEditionProcedures[criterion].metadatumID &&
                             (bulkEditionProcedures[criterion].action == editionActionsForMultiple.replace ||
                             (bulkEditionProcedures[criterion].action == editionActionsForMultiple.redefine &&
                              getMetadataByID(bulkEditionProcedures[criterion].metadatumID).multiple == 'yes'))">

                        <component
                                :disabled="bulkEditionProcedures[criterion].isDone"
                                :id="getMetadataByID(bulkEditionProcedures[criterion].metadatumID).metadata_type_object.component +
                             '-' + getMetadataByID(bulkEditionProcedures[criterion].metadatumID).slug"
                                :is="getMetadataByID(bulkEditionProcedures[criterion].metadatumID).metadata_type_object.component"
                                :metadatum="{metadatum: getMetadataByID(bulkEditionProcedures[criterion].metadatumID)}"
                                class="tainacan-bulk-edition-field"
                                @input="addToBulkEditionProcedures($event, 'oldValue', criterion)"
                        />

                        <div class="tainacan-bulk-edition-field tainacan-bulk-edition-field-not-last tainacan-by-text">
                            <small>
                                {{ $i18n.get('info_by_inner') }}
                            </small>
                        </div>

                        <b-input
                                :disabled="!!bulkEditionProcedures[criterion].isDone"
                                class="tainacan-bulk-edition-field tainacan-bulk-edition-field-not-last"
                                type="text"
                                @input="addToBulkEditionProcedures($event, 'newValue', criterion)"
                        />
                    </template>

                    <!-- Not replace -->
                    <template
                            v-else-if="bulkEditionProcedures[criterion] &&
                             bulkEditionProcedures[criterion].metadatumID &&
                             bulkEditionProcedures[criterion].action">
                        <component
                                :disabled="bulkEditionProcedures[criterion].isDone"
                                :id="getMetadataByID(bulkEditionProcedures[criterion].metadatumID).metadata_type_object.component +
                             '-' + getMetadataByID(bulkEditionProcedures[criterion].metadatumID).slug"
                                :is="getMetadataByID(bulkEditionProcedures[criterion].metadatumID).metadata_type_object.component"
                                :metadatum="{metadatum: getMetadataByID(bulkEditionProcedures[criterion].metadatumID)}"
                                class="tainacan-bulk-edition-field tainacan-bulk-edition-field-last"
                                @input="addToBulkEditionProcedures($event, 'newValue', criterion)"
                        />
                    </template>

                    <!-- DISABLED FIELD -->
                    <template v-else>
                        <b-input
                                class="tainacan-bulk-edition-field tainacan-bulk-edition-field-last"
                                type="text"
                                disabled />
                    </template>

                    <div class="field buttons-r-bulk">
                        <button
                                v-if="!bulkEditionProcedures[criterion].isDone && !bulkEditionProcedures[criterion].isExecuting"
                                @click="removeThis(criterion)"
                                class="button is-white is-pulled-right">
                            <b-icon
                                    type="is-secondary"
                                    icon="close"/>
                        </button>
                        <a
                                v-if="bulkEditionProcedures[criterion].isDone"
                                class="is-pulled-right">
                            <b-icon
                                    type="is-success"
                                    icon="check-circle"/>
                        </a>
                        <button
                                v-if="!bulkEditionProcedures[criterion].isDone &&
                                 !bulkEditionProcedures[criterion].isExecuting &&
                                   bulkEditionProcedures[criterion].metadatumID &&
                                    bulkEditionProcedures[criterion].action"
                                @click="executeBulkEditionProcedure(criterion)"
                                class="button is-white is-pulled-right">
                            <b-icon
                                    icon="play-circle-outline"/>
                        </button>
                        <div v-if="bulkEditionProcedures[criterion].isExecuting">
                            <b-icon
                                    class="tainacan-loader"
                                    type="is-success"
                                    icon="loading"/>
                        </div>
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

            <!--<footer class="field is-grouped form-submit">-->
                <!--<div class="control">-->
                    <!--<button-->
                            <!--class="button is-outlined"-->
                            <!--type="button"-->
                            <!--@click="$parent.close()">{{ $i18n.get('cancel') }}-->
                    <!--</button>-->
                <!--</div>-->
                <!--<div class="control">-->
                    <!--<button-->
                            <!--type="button"-->
                            <!--class="button is-success">{{ $i18n.get('save') }}-->
                    <!--</button>-->
                <!--</div>-->
            <!--</footer>-->
        </div>
    </div>
</template>

<script>
    import { mapActions, mapGetters } from 'vuex';

    export default {
        name: "BulkEditionModal",
        props: {
            modalTitle: String,
            totalItems: Array,
            objectType: String,
            metadata: Array,
            selectedForBulk: Object,
            collectionID: Number,
        },
        created(){
            this.createEditGroup({
                object: this.selectedForBulk,
                collectionID: this.collectionID
            }).then(() => {
                this.groupID = this.getGroupID();
            });
        },
        data() {
            return {
                editionCriteria: [1],
                editionActionsForMultiple: {
                    remove: this.$i18n.get('remove'),
                    redefine: this.$i18n.get('redefine'),
                    replace: this.$i18n.get('replace'),
                },
                editionActionsForNotMultiple: {
                    redefine: this.$i18n.get('redefine'),
                },
                bulkEditionProcedures: {
                    1: {
                        isDone: false,
                        isExecuting: false
                    }
                },
                groupID: null,
            }
        },
        methods: {
            ...mapGetters('bulkedition', [
                'getGroupID',
                'getActionResult'
            ]),
            ...mapActions('bulkedition', [
                'createEditGroup',
                'setValueInBulk',
            ]),
            executeBulkEditionProcedure(criterion){
                let procedure = this.bulkEditionProcedures[criterion];

                if(procedure.action === 'Redefine'){
                    this.$set(this.bulkEditionProcedures[criterion], 'isExecuting', true);

                    this.setValueInBulk({
                        collectionID: this.collectionID,
                        groupID: this.groupID,
                        bodyParams: {
                            metadatum_id: procedure.metadatumID,
                            value: procedure.newValue
                        }
                    }).then(() => {
                        this.$set(this.bulkEditionProcedures[criterion], 'isDone', true);
                        this.$set(this.bulkEditionProcedures[criterion], 'isExecuting', false);
                    });
                }
            },
            addEditionCriterion() {
                let aleatoryKey = Math.floor(Math.random() * (1000 - 2 + 1)) + 2;

                let found = this.editionCriteria.find((element) => {
                    return element == aleatoryKey;
                });

                if (found == undefined) {
                    this.editionCriteria.push(aleatoryKey);

                    this.bulkEditionProcedures = Object.assign({}, this.bulkEditionProcedures, {
                        [`${aleatoryKey}`]: {
                            isDone: false,
                            isExecuting: false
                        }
                    });
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
                    delete this.bulkEditionProcedures[criterion];
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

    .tainacan-by-text {
        max-width: 28px;
    }

    .tainacan-bulk-edition-inline-fields {
        display: inline-flex;
        flex-direction: row;
        flex-wrap: wrap;
        width: 100%;

        .control {
            .select {
                width: 100% !important;

                select {
                    width: 100% !important;
                }
            }
        }

        .tainacan-bulk-edition-field {
            flex-grow: 1;
            flex-shrink: 1;
            text-align: center;
            padding-bottom: 9px;
            max-height: 150px;
            overflow-y: auto;

            &:not(:first-child) {
                padding-left: 13px;
            }
        }

        .buttons-r-bulk {
            display: flex;
            align-items: center;
            height: 32px;
            margin-left: 10px;
            flex-direction: row-reverse;
        }
    }

    .tainacan-add-edition-criterion-button {
        font-size: 12px;
        color: $turquoise5;
    }

    .tainacan-loader {
        -webkit-animation: spin 2s linear infinite; /* Safari */
        animation: spin 2s linear infinite;
    }

    /* Safari */
    @-webkit-keyframes spin {
        0% { -webkit-transform: rotate(0deg); }
        100% { -webkit-transform: rotate(360deg); }
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

</style>