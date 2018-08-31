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
                            :class="{'is-field-history': bulkEditionProcedures[criterion].isDone}"
                            :disabled="!!bulkEditionProcedures[criterion].metadatumID"
                            class="tainacan-bulk-edition-field tainacan-bulk-edition-field-not-last"
                            :placeholder="$i18n.get('instruction_select_a_metadatum')"
                            @input="addToBulkEditionProcedures($event, 'metadatumID', criterion)">
                        <template v-for="(metadatum, index) in metadata">
                            <option
                                    v-if="metadatum.id"
                                    :value="metadatum.id">
                                {{ metadatum.name }}
                            </option>
                            <option
                                    v-if="index === Object.keys(metadata).length-1"
                                    value="status">
                                {{ $i18n.get('label_status') }}
                            </option>
                        </template>
                    </b-select>

                    <b-select
                            :class="{'is-field-history': bulkEditionProcedures[criterion].isDone}"
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
                                :forced-component-type="getMetadataByID(bulkEditionProcedures[criterion].metadatumID)
                                 .metadata_type_object.component.includes('taxonomy') ? 'tainacan-taxonomy-tag-input' : ''"
                                :allow-new="false"
                                :allow-select-to-create="getMetadataByID(bulkEditionProcedures[criterion].metadatumID)
                                 .metadata_type_options.allow_new_terms === 'yes'"
                                :maxtags="1"
                                :class="{'is-field-history': bulkEditionProcedures[criterion].isDone}"
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
                                :class="{'is-field-history': bulkEditionProcedures[criterion].isDone}"
                                :disabled="bulkEditionProcedures[criterion].isDone"
                                class="tainacan-bulk-edition-field tainacan-bulk-edition-field-not-last"
                                type="text"
                                @input="addToBulkEditionProcedures($event, 'newValue', criterion)"
                        />
                    </template>

                    <!-- Not replace -->
                    <template
                            v-else-if="bulkEditionProcedures[criterion] &&
                             bulkEditionProcedures[criterion].metadatumID == 'status'">
                        <b-select
                                :class="{'is-field-history': bulkEditionProcedures[criterion].isDone}"
                                :disabled="bulkEditionProcedures[criterion].isDone"
                                class="tainacan-bulk-edition-field tainacan-bulk-edition-field-last"
                                :placeholder="$i18n.get('instruction_select_a_status2')"
                                @input="addToBulkEditionProcedures($event, 'newValue', criterion)">
                            <option
                                    v-for="(status, key) in statuses"
                                    :key="key"
                                    :value="status">
                                {{ $i18n.get(status) }}
                            </option>
                        </b-select>
                    </template>

                    <template
                            v-else-if="bulkEditionProcedures[criterion] &&
                             bulkEditionProcedures[criterion].metadatumID &&
                             bulkEditionProcedures[criterion].action">
                        <component
                                :forced-component-type="getMetadataByID(bulkEditionProcedures[criterion].metadatumID)
                                 .metadata_type_object.component.includes('taxonomy') ? 'tainacan-taxonomy-tag-input' : ''"
                                :allow-new="false"
                                :allow-select-to-create="getMetadataByID(bulkEditionProcedures[criterion].metadatumID)
                                 .metadata_type_options.allow_new_terms === 'yes'"
                                :maxtags="1"
                                :class="{'is-field-history': bulkEditionProcedures[criterion].isDone}"
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
                    <!--<template v-else>-->
                        <!--<input-->
                                <!--style="border: none !important; background-color: white !important;"-->
                                <!--class="tainacan-bulk-edition-field tainacan-bulk-edition-field-last"-->
                                <!--type="text"-->
                                <!--disabled >-->
                    <!--</template>-->

                    <div class="field buttons-r-bulk">
                        <button
                                v-if="!bulkEditionProcedures[criterion].isDone && !bulkEditionProcedures[criterion].isExecuting"
                                @click="removeThis(criterion)"
                                class="button is-white is-pulled-right">
                            <b-icon
                                    type="is-gray4"
                                    icon="close-circle-outline"/>
                        </button>
                        <div
                                v-if="bulkEditionProcedures[criterion].isDone"
                                class="is-pulled-right">
                            <b-tooltip
                                    class="is-success"
                                    :label="actionResult.constructor.name !== 'Object' && actionResult === 1 ? `${actionResult} ${$i18n.get('info_item_edited')}` : `${actionResult} ${$i18n.get('info_items_edited')}`">
                                <b-icon
                                        type="is-success"
                                        icon="check-circle"/>
                            </b-tooltip>
                        </div>
                        <div
                                v-if="bulkEditionProcedures[criterion].isDoneWithError && !bulkEditionProcedures[criterion].isExecuting"
                                class="is-pulled-right">
                            <b-tooltip
                                    class="is-danger"
                                    :label="actionResult.constructor.name === 'Object' ? (actionResult.error_message ? actionResult.error_message : actionResult.message) : ''">
                                <b-icon
                                        type="is-danger"
                                        icon="alert-circle"/>
                            </b-tooltip>
                        </div>
                        <button
                                v-if="!bulkEditionProcedures[criterion].isDone &&
                                 !bulkEditionProcedures[criterion].isExecuting &&
                                   bulkEditionProcedures[criterion].metadatumID &&
                                    bulkEditionProcedures[criterion].action"
                                @click="executeBulkEditionProcedure(criterion)"
                                class="button is-white is-pulled-right">
                            <b-icon
                                    type="is-gray4"
                                    icon="play-circle"/>
                        </button>
                        <div v-if="bulkEditionProcedures[criterion].isExecuting">
                            <b-icon
                                    class="tainacan-loader"
                                    type="is-success"
                                    icon="loading"/>
                        </div>
                    </div>
                </div>
                <button
                        style="padding-left: 3px !important;"
                        class="button is-white"
                        :disabled="dones.every((item) => item === true) === false"
                        @click="addEditionCriterion()">
                    <a
                            :class="{ 'tainacan-add-edition-criterion-button-disabled': dones.every((item) => item === true) === false }"
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
                </button>
            </div>
            <!--<pre>{{ bulkEditionProcedures }}</pre>-->

            <footer class="field form-submit">
                <div class="control is-pulled-right">
                    <button
                            :disabled="dones.every((item) => item === true) === false"
                            class="button is-success"
                            type="button"
                            @click="$parent.close()">{{ $i18n.get('conclude') }}
                    </button>
                </div>
            </footer>
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
                statuses: {
                    draft: 'draft',
                    publish: 'publish',
                    private: 'private'
                },
                editionCriteria: [1],
                editionActionsForMultiple: {
                    add: this.$i18n.get('add'),
                    redefine: this.$i18n.get('redefine'),
                    remove: this.$i18n.get('remove'),
                    replace: this.$i18n.get('replace'),
                },
                editionActionsForNotMultiple: {
                    redefine: this.$i18n.get('redefine'),
                },
                bulkEditionProcedures: {
                    1: {
                        isDone: false,
                        isDoneWithError: false,
                        isExecuting: false
                    }
                },
                groupID: null,
                dones: [false],
                totalItemsEditedWithSuccess: 0,
                actionResult: '',
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
                'addValueInBulk',
                'replaceValueInBulk',
                'redefineValueInBulk',
                'setStatusInBulk',
                'removeValueInBulk'
            ]),
            finalizeProcedure(criterion, withError){
                this.$set(this.bulkEditionProcedures[criterion], 'isDone', !withError);
                this.$set(this.bulkEditionProcedures[criterion], 'isDoneWithError', withError);

                let index = this.editionCriteria.indexOf(criterion);

                this.dones[index] = !withError;

                this.$set(this.bulkEditionProcedures[criterion], 'isExecuting', false);
            },
            executeBulkEditionProcedure(criterion){
                let procedure = this.bulkEditionProcedures[criterion];

                if(procedure.action === this.editionActionsForMultiple.redefine){
                    this.$set(this.bulkEditionProcedures[criterion], 'isExecuting', true);

                    if(procedure.metadatumID === 'status'){
                        this.setStatusInBulk({
                            collectionID: this.collectionID,
                            groupID: this.groupID,
                            bodyParams: { value: procedure.newValue }
                        }).then(() => {
                            this.actionResult = this.getActionResult();

                            if(this.actionResult.constructor.name === 'Object' &&
                                (this.actionResult.data &&
                                    this.actionResult.data.status.toString().split('')[0] != 2) ||
                                this.actionResult.error_message) {

                                this.finalizeProcedure(criterion, true);
                            } else {
                                this.finalizeProcedure(criterion);
                                this.totalItemsEditedWithSuccess = this.actionResult;
                            }
                        });
                    } else {
                        this.setValueInBulk({
                            collectionID: this.collectionID,
                            groupID: this.groupID,
                            bodyParams: {
                                metadatum_id: procedure.metadatumID,
                                value: procedure.newValue
                            }
                        }).then(() => {
                            this.actionResult = this.getActionResult();

                            if(this.actionResult.constructor.name === 'Object' &&
                                (this.actionResult.data &&
                                    this.actionResult.data.status.toString().split('')[0] != 2) ||
                                this.actionResult.error_message) {

                                this.finalizeProcedure(criterion, true);
                            } else {
                                this.finalizeProcedure(criterion);
                                this.totalItemsEditedWithSuccess = this.actionResult;
                            }
                        });
                    }
                } else if(procedure.action === this.editionActionsForMultiple.add){
                    this.$set(this.bulkEditionProcedures[criterion], 'isExecuting', true);

                    this.addValueInBulk({
                        collectionID: this.collectionID,
                        groupID: this.groupID,
                        bodyParams: {
                            metadatum_id: procedure.metadatumID,
                            value: procedure.newValue,
                        }
                    }).then(() => {
                        this.actionResult = this.getActionResult();

                        if(this.actionResult.constructor.name === 'Object' &&
                            (this.actionResult.data &&
                                this.actionResult.data.status.toString().split('')[0] != 2) ||
                            this.actionResult.error_message) {

                            this.finalizeProcedure(criterion, true);
                        } else {
                            this.finalizeProcedure(criterion);
                            this.totalItemsEditedWithSuccess = this.actionResult;
                        }
                    });
                } else if(procedure.action === this.editionActionsForMultiple.replace){
                    this.$set(this.bulkEditionProcedures[criterion], 'isExecuting', true);

                    this.replaceValueInBulk({
                        collectionID: this.collectionID,
                        groupID: this.groupID,
                        bodyParams: {
                            metadatum_id: procedure.metadatumID,
                            old_value: procedure.oldValue,
                            new_value: procedure.newValue,
                        }
                    }).then(() => {
                        this.actionResult = this.getActionResult();

                        if(this.actionResult.constructor.name === 'Object' &&
                            (this.actionResult.data &&
                                this.actionResult.data.status.toString().split('')[0] != 2) ||
                            this.actionResult.error_message) {

                            this.finalizeProcedure(criterion, true);
                        } else {
                            this.finalizeProcedure(criterion);
                            this.totalItemsEditedWithSuccess = this.actionResult;
                        }
                    });
                } else if(procedure.action === this.editionActionsForMultiple.remove){
                    this.$set(this.bulkEditionProcedures[criterion], 'isExecuting', true);

                    this.removeValueInBulk({
                        collectionID: this.collectionID,
                        groupID: this.groupID,
                        bodyParams: {
                            metadatum_id: procedure.metadatumID,
                            value: procedure.newValue,
                        }
                    }).then(() => {
                        this.actionResult = this.getActionResult();

                        if(this.actionResult.constructor.name === 'Object' &&
                            (this.actionResult.data &&
                                this.actionResult.data.status.toString().split('')[0] != 2) ||
                            this.actionResult.error_message) {

                            this.finalizeProcedure(criterion, true);
                        } else {
                            this.finalizeProcedure(criterion);
                            this.totalItemsEditedWithSuccess = this.actionResult;
                        }
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
                            isDoneWithError: false,
                            isExecuting: false
                        }
                    });

                    this.dones.push(false)
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
                    this.dones.splice(criterionIndex, 1)
                }
            },
            getMetadataByID(id){
                let found = this.metadata.find((element) => {
                    return element.id == id;
                });

                return found ? found : {};
            },
            addToBulkEditionProcedures(value, key, criterion){
                if(Array.isArray(value)){
                    value = value[0];
                }

                this.$set(this.bulkEditionProcedures[criterion], `${key}`, value);
            }
        },
    }
</script>

<style lang="scss">

    @import '../../scss/_variables.scss';

    .tainacan-modal-content {
        border-radius: 10px;
    }

    .modal-card-body {
        padding: 0 !important;
        overflow: unset !important;
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

        .taginput-container {
            height: 32px !important;

            .tags {
                margin-bottom: calc(0.275em - 1px) !important;

                .tag {
                    height: 2em !important;
                    padding-left: 0.75em !important;
                    padding-right: 0.75em !important;
                    margin-right: 0 !important;
                }
            }

            .icon {
                height: 28px !important;
            }
        }

        .is-field-history {

            .input[disabled], .taginput [disabled].taginput-container.is-focusable, .textarea[disabled] {
                color: black !important;
                border: none !important;
                background-color: white !important;
            }

            .taginput-container {
                .tags {
                    color: black !important;
                    background-color: white !important;
                    border: none !important;

                    .tag.is-delete {
                        display: none !important;
                    }

                    .tag {
                        max-width: 100% !important;
                    }

                    &:hover {
                        background-color: white !important;
                    }
                }

                .icon {
                    display: none !important;
                }
            }

            input {
                color: black !important;
                border: none !important;
                background-color: white !important;
            }

            textarea {
                color: black !important;
                border: none !important;
                background-color: white !important;
                min-height: auto !important;
            }

            .select {

                &:after {
                    display: none !important;
                }

                select {
                    border: none !important;
                }
            }
        }

        .tainacan-bulk-edition-field {
            flex-grow: 1;
            flex-shrink: 1;
            text-align: center;
            padding-bottom: 9px;
            flex-basis: 10%;

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

            .icon.has-text-gray4:hover {
                color: $gray5 !important;
            }
        }
    }

    .tainacan-add-edition-criterion-button {
        font-size: 12px;
        color: $turquoise5;
    }

    .tainacan-add-edition-criterion-button-disabled {
        cursor: not-allowed !important;
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