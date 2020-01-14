<template>
    <div 
            aria-labelledby="alert-dialog-title"
            autofocus
            role="alertdialog"
            tabindex="-1"
            aria-modal
            class="tainacan-modal-content this-tainacan-modal-content"
            ref="bulkEditionModal">
        <header class="tainacan-modal-title">
            <h2>{{ modalTitle }}
                <small class="tainacan-total-objects-info">
                    {{ `(${totalItems} ${objectType})` }}
                </small>
            </h2>
            <hr>
        </header>
        <div class="tainacan-form">
            <div class="modal-card-body no-overflow-modal-card-body">
                <div
                        v-for="criterion in editionCriteria"
                        :key="criterion"
                        class="tainacan-bulk-edition-inline-fields">

                    <b-select
                            :loading="metadataIsLoading"
                            :class="{'is-field-history': bulkEditionProcedures[criterion].isDone, 'hidden-select-arrow': !!bulkEditionProcedures[criterion].metadatumID}"
                            :disabled="!!bulkEditionProcedures[criterion].metadatumID || metadataIsLoading"
                            class="tainacan-bulk-edition-field tainacan-bulk-edition-field-not-last"
                            :placeholder="$i18n.get('instruction_select_a_metadatum')"
                            @input="addToBulkEditionProcedures($event, 'metadatumID', criterion)">
                        <template 
                                v-for="(metadatum, index) in metadata">
                            <option
                                    :key="index"
                                    v-if="metadatum.id"
                                    :value="metadatum.id">
                                {{ metadatum.name }}
                            </option>
                            <option
                                    :key="index"
                                    v-if="index === Object.keys(metadata).length-1"
                                    value="status">
                                {{ $i18n.get('label_status') }}
                            </option>
                            <option
                                    :key="index"
                                    v-if="index === Object.keys(metadata).length-1"
                                    value="comments">
                                {{ $i18n.get('label_allow_comments') }}
                            </option>
                        </template>
                    </b-select>

                    <b-select
                            :class="{'is-field-history': bulkEditionProcedures[criterion].isDone, 'hidden-select-arrow': !!bulkEditionProcedures[criterion].action }"
                            v-if="bulkEditionProcedures[criterion] &&
                            bulkEditionProcedures[criterion].metadatumID"
                            :disabled="!!bulkEditionProcedures[criterion].action"
                            :value="bulkEditionProcedures[criterion].action ? bulkEditionProcedures[criterion].action : undefined"
                            class="tainacan-bulk-edition-field tainacan-bulk-edition-field-not-last"
                            :placeholder="$i18n.get('instruction_select_a_action')"
                            @input="addToBulkEditionProcedures($event, 'action', criterion)">
                        <option
                                v-for="(edtAct, key) in getValidEditionActions(bulkEditionProcedures[criterion].metadatumID)"
                                :value="edtAct"
                                :key="key">
                            {{ edtAct }}
                        </option>
                    </b-select>

                    <!-- DISABLED FIELD -->
                    <b-input
                            v-else
                            class="tainacan-bulk-edition-field tainacan-bulk-edition-field-not-last"
                            type="text"
                            disabled />

                    <!-- Replace -->
                    <template
                            v-if="bulkEditionProcedures[criterion] &&
                                bulkEditionProcedures[criterion].metadatumID &&
                                bulkEditionProcedures[criterion].action == editionActionsForMultiple.replace">

                        <component
                                :forced-component-type="getMetadataByID(bulkEditionProcedures[criterion].metadatumID)
                                    .metadata_type_object.component.includes('taxonomy') ? 'tainacan-taxonomy-tag-input' : ''"
                                :allow-new="false"
                                :allow-select-to-create="false"
                                :maxtags="1"
                                :class="{'is-field-history': bulkEditionProcedures[criterion].isDone}"
                                :disabled="bulkEditionProcedures[criterion].isDone"
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

                        <component
                                :forced-component-type="getMetadataByID(bulkEditionProcedures[criterion].metadatumID)
                                    .metadata_type_object.component.includes('taxonomy') ? 'tainacan-taxonomy-tag-input' : ''"
                                :allow-new="false"
                                :allow-select-to-create="getMetadataByID(bulkEditionProcedures[criterion].metadatumID)
                                    .metadata_type_options.allow_new_terms === 'yes'"
                                :maxtags="1"
                                :class="{'is-field-history': bulkEditionProcedures[criterion].isDone}"
                                :disabled="bulkEditionProcedures[criterion].isDone"
                                :is="getMetadataByID(bulkEditionProcedures[criterion].metadatumID).metadata_type_object.component"
                                :metadatum="{metadatum: getMetadataByID(bulkEditionProcedures[criterion].metadatumID)}"
                                class="tainacan-bulk-edition-field tainacan-bulk-edition-field-not-last"
                                @input="addToBulkEditionProcedures($event, 'newValue', criterion)"
                        />
                    </template>

                    <!-- Not replace -->
                    <template
                            v-else-if="bulkEditionProcedures[criterion] &&
                             bulkEditionProcedures[criterion].metadatumID == 'status'">
                        <b-select
                                :class="{'is-field-history': bulkEditionProcedures[criterion].isDone, 'hidden-select-arrow': bulkEditionProcedures[criterion].isDone}"
                                :disabled="bulkEditionProcedures[criterion].isDone"
                                class="tainacan-bulk-edition-field tainacan-bulk-edition-field-last"
                                :placeholder="$i18n.get('instruction_select_a_status2')"
                                @input="addToBulkEditionProcedures($event, 'newValue', criterion)">
                            <option
                                    v-for="(statusOption, index) of $statusHelper.getStatuses().filter(option => { return option.value != 'trash' })"
                                    :key="index"
                                    :value="statusOption.slug">
                                {{ statusOption.name }}
                            </option>
                        </b-select>
                    </template>

                    <template
                            v-else-if="bulkEditionProcedures[criterion] &&
                             bulkEditionProcedures[criterion].metadatumID == 'comments'">
                        <b-select
                                :class="{'is-field-history': bulkEditionProcedures[criterion].isDone, 'hidden-select-arrow': bulkEditionProcedures[criterion].isDone}"
                                :disabled="bulkEditionProcedures[criterion].isDone"
                                class="tainacan-bulk-edition-field tainacan-bulk-edition-field-last"
                                :placeholder="$i18n.get('instruction_select_a_comments_status')"
                                @input="addToBulkEditionProcedures($event, 'newValue', criterion)">
                            <option
                                    v-for="(statusOption, index) of $commentsStatusHelper.getStatuses()"
                                    :key="index"
                                    :value="statusOption.slug">
                                {{ statusOption.name }}
                            </option>
                        </b-select>
                    </template>

                    <template
                            v-else-if="bulkEditionProcedures[criterion] &&
                             bulkEditionProcedures[criterion].metadatumID &&
                             bulkEditionProcedures[criterion].action &&
                             bulkEditionProcedures[criterion].action != editionActionsForMultiple.clear">
                        <component
                                :forced-component-type="getMetadataByID(bulkEditionProcedures[criterion].metadatumID)
                                 .metadata_type_object.component.includes('taxonomy') ? 'tainacan-taxonomy-tag-input' : ''"
                                :allow-new="false"
                                :allow-select-to-create="getMetadataByID(bulkEditionProcedures[criterion].metadatumID)
                                 .metadata_type_options.allow_new_terms === 'yes'"
                                :maxtags="1"
                                :class="{'is-field-history': bulkEditionProcedures[criterion].isDone}"
                                :disabled="bulkEditionProcedures[criterion].isDone || bulkEditionProcedures[criterion].isExecuting"
                                :is="getMetadataByID(bulkEditionProcedures[criterion].metadatumID).metadata_type_object.component"
                                :metadatum="{metadatum: getMetadataByID(bulkEditionProcedures[criterion].metadatumID)}"
                                class="tainacan-bulk-edition-field tainacan-bulk-edition-field-last"
                                @input="addToBulkEditionProcedures($event, 'newValue', criterion)"
                        />
                    </template>

                    <div
                            :style="{
                              marginRight: !bulkEditionProcedures[criterion].isDone && !bulkEditionProcedures[criterion].isExecuting ? '-7.4px': 0
                            }"
                            class="field buttons-r-bulk">

                        <button
                                v-if="!bulkEditionProcedures[criterion].isDone && !bulkEditionProcedures[criterion].isExecuting"
                                @click="removeThis(criterion)"
                                class="button is-white is-pulled-right">
                            <span 
                                    v-tooltip="{
                                        content: $i18n.get('remove_search_criterion'),
                                        autoHide: true,
                                        placement: 'auto-end'
                                    }"
                                    class="icon">
                                <i class="has-text-gray4 tainacan-icon tainacan-icon-20px tainacan-icon-cancel"/>
                            </span>
                        </button>

                        <div
                                v-if="bulkEditionProcedures[criterion].isDone"
                                @mouseover="$set(bulkEditionProcedures[criterion], 'tooltipShow', !bulkEditionProcedures[criterion].tooltipShow)"
                                class="is-pulled-right">
                            <b-tooltip
                                    :active="bulkEditionProcedures[criterion].tooltipShow"
                                    always
                                    class="is-success"
                                    size="is-small"
                                    position="is-left"
                                    animated
                                    :label="$i18n.get('info_bulk_edition_process_added')">
                                <span class="icon">
                                    <i class="has-text-success tainacan-icon tainacan-icon-20px tainacan-icon-approvedcircle"/>
                                </span>
                            </b-tooltip>
                        </div>

                        <button
                                :disabled="!groupID"
                                v-if="!bulkEditionProcedures[criterion].isDone &&
                                    !bulkEditionProcedures[criterion].isExecuting &&
                                    bulkEditionProcedures[criterion].metadatumID &&
                                    bulkEditionProcedures[criterion].action"
                                @click="executeBulkEditionProcedure(criterion)"
                                class="button is-white is-pulled-right">
                            <span 
                                    v-tooltip="{
                                        content: $i18n.get('label_apply_changes'),
                                        autoHide: true,
                                        placement: 'auto-end'
                                    }"
                                    class="icon">
                                <i class="has-text-gray4 tainacan-icon tainacan-icon-20px tainacan-icon-play"/>
                            </span>
                        </button>

                        <div v-if="bulkEditionProcedures[criterion].isExecuting">
                            <b-icon
                                    class="mdi-loader"
                                    type="is-success"
                                    icon="loading"/>
                        </div>
                    </div>
                </div>
                <a 
                        :disabled="dones.every((item) => item === true) === false"
                        @click="addEditionCriterion()"
                        class="has-text-right is-block add-link">
                    <span class="icon is-small">
                        <i class="tainacan-icon has-text-secondary tainacan-icon-add"/>
                    </span>
                    &nbsp;{{ $i18n.get('new_action') }}
                </a>
            </div>

            <footer class="field is-grouped form-submit">
                <p class="control">
                    <button
                            @click="$eventBusSearch.loadItems(); $parent.close()"
                            :disabled="(Object.keys(bulkEditionProcedures).length &&
                             bulkEditionProcedures[editionCriteria[editionCriteria.length-1]].isExecuting) || false"
                            type="button"
                            class="button is-outlined">
                        {{ $i18n.get('close') }}
                    </button>
                </p>
                <p class="control">
                    <button
                            :disabled="dones.every((item) => item === true) === false"
                            class="button is-success"
                            type="button"
                            @click="$root.$emit('openProcessesPopup'); $eventBusSearch.loadItems(); $parent.close();">
                        {{ $i18n.get('finish') }}
                    </button>
                </p>
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
            selectedForBulk: Object,
            collectionID: Number
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
                    add: this.$i18n.get('add_value'),
                    redefine: this.$i18n.get('set_new_value'),
                    replace: this.$i18n.get('replace_value'),
                    remove: this.$i18n.get('remove_a_value'),
                    clear: this.$i18n.get('clear_values')
                },
                editionActionsForNotMultiple: {
                    redefine: this.$i18n.get('set_new_value'),
                    clear: this.$i18n.get('clear_values')
                },
                bulkEditionProcedures: {
                    1: {
                        isDone: false,
                        isExecuting: false,
                        totalItemsEditedWithSuccess: 0,
                        tooltipShow: true,
                    }
                },
                groupID: null,
                dones: [false],
                metadataIsLoading: false,
                metadataSearchCancel: undefined
            }
        },
        computed: {
            metadata() {
                return this.getMetadata();
            }
        },
        created(){
            if (this.collectionID){
                this.metadataIsLoading = true;

                // Cancels previous Request
                if (this.metadataSearchCancel != undefined)
                    this.metadataSearchCancel.cancel('Metadata search Canceled.');

                this.fetchMetadata({
                    collectionId: this.collectionID,
                    isRepositoryLevel: false,
                    isContextEdit: true,
                    includeDisabled: false
                }).then((resp) => {
                        resp.request
                            .then(() => {
                                this.metadataIsLoading = false;
                            }).catch(() => {
                                this.metadataIsLoading = false;
                            });

                            // Search Request Token for cancelling
                            this.metadataSearchCancel = resp.source;
                    })
                    .catch(() => this.metadataIsLoading = false); 
            }

            this.createEditGroup({
                object: this.selectedForBulk,
                collectionID: this.collectionID ? this.collectionID : 'default'
            }).then(() => {
                this.groupID = this.getGroupID();
            });
        },
        mounted() {
            if (this.$refs.bulkEditionModal)
                this.$refs.bulkEditionModal.focus();
        },
        beforeDestroy() {
            // Cancels previous Request
            if (this.metadataSearchCancel != undefined)
                this.metadataSearchCancel.cancel('Metadata search Canceled.');

        },
        methods: {
            ...mapGetters('bulkedition', [
                'getGroupID'
            ]),
            ...mapActions('bulkedition', [
                'createEditGroup',
                'setValueInBulk',
                'clearValuesInBulk',
                'addValueInBulk',
                'replaceValueInBulk',
                'redefineValueInBulk',
                'setStatusInBulk',
                'setCommentStatusInBulk',
                'removeValueInBulk'
            ]),
            ...mapActions('metadata', [
                'fetchMetadata'
            ]),
            ...mapGetters('metadata', [
                'getMetadata'
            ]),
            finalizeProcedure(criterion){

                let withError = false;

                this.$set(this.bulkEditionProcedures[criterion], 'isDone', true);

                let index = this.editionCriteria.indexOf(criterion);

                this.dones[index] = !withError;
 
                this.$set(this.bulkEditionProcedures[criterion], 'isExecuting', false);
            },
            executeBulkEditionProcedure(criterion){
                let procedure = this.bulkEditionProcedures[criterion];

                if (procedure.action === this.editionActionsForMultiple.redefine) {
                    this.$set(this.bulkEditionProcedures[criterion], 'isExecuting', true);

                    if (procedure.metadatumID === 'status'){
                        this.setStatusInBulk({
                            collectionID: this.collectionID,
                            groupID: this.groupID,
                            bodyParams: { value: procedure.newValue }
                        }).then(() => {
                            this.finalizeProcedure(criterion);
                        });
                    } if(procedure.metadatumID === 'comments'){
                        this.setCommentStatusInBulk({
                            collectionID: this.collectionID,
                            groupID: this.groupID,
                            bodyParams: { value: procedure.newValue }
                        }).then(() => {
                            this.finalizeProcedure(criterion);
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
                            this.finalizeProcedure(criterion);
                        });
                    }
                } else if (procedure.action === this.editionActionsForMultiple.add) {
                    this.$set(this.bulkEditionProcedures[criterion], 'isExecuting', true);

                    this.addValueInBulk({
                        collectionID: this.collectionID,
                        groupID: this.groupID,
                        bodyParams: {
                            metadatum_id: procedure.metadatumID,
                            value: procedure.newValue,
                        }
                    }).then(() => {
                        this.finalizeProcedure(criterion);
                    });
                } else if (procedure.action === this.editionActionsForMultiple.replace) {
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
                        this.finalizeProcedure(criterion);
                    });
                } else if (procedure.action === this.editionActionsForMultiple.remove) {
                    this.$set(this.bulkEditionProcedures[criterion], 'isExecuting', true);

                    this.removeValueInBulk({
                        collectionID: this.collectionID,
                        groupID: this.groupID,
                        bodyParams: {
                            metadatum_id: procedure.metadatumID,
                            value: procedure.newValue,
                        }
                    }).then(() => {
                        this.finalizeProcedure(criterion);
                    });
                } else if (procedure.action === this.editionActionsForMultiple.clear) {
                    this.$set(this.bulkEditionProcedures[criterion], 'isExecuting', true);

                    this.clearValuesInBulk({
                        collectionID: this.collectionID,
                        groupID: this.groupID,
                        bodyParams: {
                            metadatum_id: procedure.metadatumID
                        }
                    }).then(() => {
                        this.finalizeProcedure(criterion);
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
                            isExecuting: false,
                            totalItemsEditedWithSuccess: 0,
                            tooltipShow: true,
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
            getValidEditionActions(metadatumID) {
                const isMultiple = this.getMetadataByID(metadatumID).multiple == 'yes';
                let validEditionActions = {};

                for (let [actionKey, action] of Object.entries(isMultiple ? this.editionActionsForMultiple : this.editionActionsForNotMultiple)) {
                    if ((metadatumID != 'status' && metadatumID != 'comments') || actionKey != 'clear')
                        validEditionActions[actionKey] = action;
                }

                return validEditionActions;
            },
            getMetadataByID(id){
                let found = this.metadata.find((element) => {
                    return element.id == id;
                });

                return found ? found : {};
            },
            addToBulkEditionProcedures(value, key, criterion){
                if (Array.isArray(value))
                    value = value[0];

                this.$set(this.bulkEditionProcedures[criterion], `${key}`, value);
            }
        }
    }
</script>

<style lang="scss">

    @import '../../scss/_variables.scss';

    @media screen and (max-width: 768px) {
        .tainacan-bulk-edition-inline-fields {
            flex-direction: column !important;

            .tainacan-bulk-edition-field:not(:first-child) {
                padding-left: 0 !important;
            }

            .buttons-r-bulk {
                margin-left: 0 !important;
                justify-content: center !important;
            }
        }
    }

    .this-tainacan-modal-content {
        min-height: 300px;
    }

    .this-tainacan-modal-content .form-submit {
        padding: 42px 0 0.4em 0 !important;
    }

    .no-overflow-modal-card-body {
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

                    &:hover, .tag {
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
                line-height: 1.5rem;
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

    .mdi-loader {
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