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
                <transition-group name="filter-item">
                    <div
                            v-for="criterion in editionCriteria"
                            :key="criterion"
                            class="tainacan-bulk-edition-inline-fields">

                        <!-- FIRST FIELD - METADATUM -------------------------- -->
                        <b-select
                                :loading="metadataIsLoading"
                                :class="{ 'is-field-history': bulkEditionProcedures[criterion].isDone, 'hidden-select-arrow': !!bulkEditionProcedures[criterion].metadatum }"
                                :disabled="!!bulkEditionProcedures[criterion].metadatum || metadataIsLoading"
                                class="tainacan-bulk-edition-field tainacan-bulk-edition-field-not-last"
                                :placeholder="$i18n.get('instruction_select_a_metadatum')"
                                @input="addToBulkEditionProcedures($event, 'metadatum', criterion)">
                            <template v-for="(metadatum, index) in metadata">
                                <option
                                        :key="index"
                                        v-if="metadatum.id && metadatum.metadata_type_object.component !== 'tainacan-compound' && metadatum.parent <= 0"
                                        :value="metadatum">
                                    {{ metadatum.name }}
                                </option>
                                <optgroup 
                                        v-if="metadatum.id && metadatum.metadata_type_object.component === 'tainacan-compound'"
                                        :key="index"
                                        :label="metadatum.name">
                                    <option 
                                            v-for="(childMetadatum, childIndex) of metadatum.metadata_type_options.children_objects"
                                            :key="childIndex"
                                            v-if="childMetadatum.id"
                                            :value="childMetadatum">
                                        {{ childMetadatum.name }}
                                    </option>
                                </optgroup>
                            </template>
                            <option :value="{ id: 'status' }">
                                {{ $i18n.get('label_status') }}
                            </option>
                            <option :value="{ id: 'comments' }">
                                {{ $i18n.get('label_allow_comments') }}
                            </option>
                        </b-select>

                        <!-- SECOND FIELD - ACTION -------------------------- -->
                        <b-select
                                :class="{'is-field-history': bulkEditionProcedures[criterion].isDone, 'hidden-select-arrow': !!bulkEditionProcedures[criterion].action }"
                                :disabled="!!bulkEditionProcedures[criterion].action || !bulkEditionProcedures[criterion].metadatum"
                                :value="bulkEditionProcedures[criterion].action ? bulkEditionProcedures[criterion].action : undefined"
                                class="tainacan-bulk-edition-field tainacan-bulk-edition-field-not-last"
                                :placeholder="$i18n.get('instruction_select_a_action')"
                                @input="addToBulkEditionProcedures($event, 'action', criterion)">
                            <template v-if="bulkEditionProcedures[criterion].metadatum">
                                <option
                                        v-for="(edtAct, key) in getValidEditionActions(bulkEditionProcedures[criterion].metadatum)"
                                        :value="edtAct"
                                        :key="key">
                                    {{ edtAct }}
                                </option>
                            </template>
                        </b-select>

                        <!-- THIRD FIELD - DYNAMIC INPUTS -->
                        <transition name="filter-item">
                            <template v-if="bulkEditionProcedures[criterion] && bulkEditionProcedures[criterion].metadatum && bulkEditionProcedures[criterion].action"> 
                                
                                <!-- This has do be a 'div' as the transition animation only renders one child -->
                                <div 
                                        style="margin-left: 12px; display: flex;"
                                        v-if="bulkEditionProcedures[criterion].action == editionActions.replace">
                                    
                                    <component
                                            :is="bulkEditionProcedures[criterion].metadatum.metadata_type_object.component"
                                            :forced-component-type="bulkEditionProcedures[criterion].metadatum.metadata_type_object.component.includes('taxonomy') ? 'tainacan-taxonomy-tag-input' : ''"
                                            :item-metadatum="{ metadatum: bulkEditionProcedures[criterion].metadatum }"
                                            :allow-new="false"
                                            :allow-select-to-create="false"
                                            :maxtags="1"
                                            :class="{'is-field-history': bulkEditionProcedures[criterion].isDone}"
                                            :disabled="bulkEditionProcedures[criterion].isDone"
                                            class="tainacan-bulk-edition-field"
                                            @input="addToBulkEditionProcedures($event, 'oldValue', criterion)"
                                    />

                                    <div class="tainacan-bulk-edition-field tainacan-bulk-edition-field-not-last tainacan-by-text">
                                        <small>
                                            {{ $i18n.get('info_by_inner') }}
                                        </small>
                                    </div>

                                    <component
                                            :is="bulkEditionProcedures[criterion].metadatum.metadata_type_object.component"
                                            :forced-component-type="bulkEditionProcedures[criterion].metadatum.metadata_type_object.component.includes('taxonomy') ? 'tainacan-taxonomy-tag-input' : ''"
                                            :item-metadatum="{ metadatum: bulkEditionProcedures[criterion].metadatum }"
                                            :allow-new="false"
                                            :allow-select-to-create="bulkEditionProcedures[criterion].metadatum.metadata_type_options.allow_new_terms === 'yes'"
                                            :maxtags="1"
                                            :class="{'is-field-history': bulkEditionProcedures[criterion].isDone}"
                                            class="tainacan-bulk-edition-field tainacan-bulk-edition-field-not-last"
                                            :disabled="bulkEditionProcedures[criterion].isDone"  
                                            @input="addToBulkEditionProcedures($event, 'newValue', criterion)"
                                    />
                                </div>

                                <template
                                        v-else-if="bulkEditionProcedures[criterion].metadatum.id == 'status'">
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

                                <template v-else-if="bulkEditionProcedures[criterion].metadatum.id == 'comments'">
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

                                <template v-else-if="bulkEditionProcedures[criterion].action == editionActions.copy">
                                    <b-select
                                            :loading="metadataIsLoading"
                                            :class="{'is-field-history': bulkEditionProcedures[criterion].isDone, 'hidden-select-arrow': !!bulkEditionProcedures[criterion].metadatumIdCopyFrom }"
                                            :disabled="bulkEditionProcedures[criterion].isDone || bulkEditionProcedures[criterion].isExecuting && !!bulkEditionProcedures[criterion].metadatumIdCopyFrom || metadataIsLoading"
                                            class="tainacan-bulk-edition-field tainacan-bulk-edition-field-last"
                                            :placeholder="$i18n.get('instruction_select_a_metadatum')"
                                            @input="addToBulkEditionProcedures($event, 'metadatumIdCopyFrom', criterion)">
                                        <template 
                                                v-for="(metadatumForCopy, index) in getAllowedMetadataForCopy(criterion)">
                                            <option
                                                    :key="index"
                                                    v-if="metadatumForCopy.id && metadatumForCopy.metadata_type_object.component !== 'tainacan-compound' && metadatumForCopy.parent <= 0"
                                                    :value="metadatumForCopy.id">
                                                {{ metadatumForCopy.name }}
                                            </option>
                                            <optgroup 
                                                    v-if="metadatumForCopy.id && metadatumForCopy.metadata_type_object.component === 'tainacan-compound'"
                                                    :key="index"
                                                    :label="metadatumForCopy.name">
                                                <option 
                                                        v-for="(childmetadatumForCopy, childIndex) of metadatumForCopy.metadata_type_options.children_objects"
                                                        :key="childIndex"
                                                        v-if="childMetadatumForCopy.id"
                                                        :value="childMetadatumForCopy.id">
                                                    {{ childMetadatumForCopy.name }}
                                                </option>
                                            </optgroup>
                                        </template>
                                        <option 
                                                v-if="bulkEditionProcedures[criterion].metadatum.metadata_type_object && bulkEditionProcedures[criterion].metadatum.metadata_type_object.component == 'tainacan-user'"
                                                value="created_by">
                                            {{ $i18n.get('label_created_by') }}
                                        </option>
                                    </b-select>
                                </template>

                                <template v-else-if="bulkEditionProcedures[criterion].action != editionActions.clear">
                                    <component
                                            :is="bulkEditionProcedures[criterion].metadatum.metadata_type_object.component"
                                            :forced-component-type="bulkEditionProcedures[criterion].metadatum.metadata_type_object.component.includes('taxonomy') ? 'tainacan-taxonomy-tag-input' : ''"
                                            :item-metadatum="{ metadatum: bulkEditionProcedures[criterion].metadatum }"
                                            :allow-new="false"
                                            :allow-select-to-create="bulkEditionProcedures[criterion].metadatum.metadata_type_options.allow_new_terms === 'yes'"
                                            :maxtags="1"
                                            :class="{ 'is-field-history': bulkEditionProcedures[criterion].isDone }"
                                            class="tainacan-bulk-edition-field tainacan-bulk-edition-field-last"
                                            :disabled="bulkEditionProcedures[criterion].isDone || bulkEditionProcedures[criterion].isExecuting"
                                            @input="addToBulkEditionProcedures($event, 'newValue', criterion)"
                                    />
                                </template>
                            </template>
                        </transition> 

                        <!-- FOURTH FIELD - ICONS AND BUTTONS -->
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
                                            content: $i18n.get('remove_bulk_edition'),
                                            autoHide: true,
                                            placement: 'auto-end'
                                        }"
                                        class="icon">
                                    <i class="has-text-gray4 tainacan-icon tainacan-icon-1-25em tainacan-icon-cancel"/>
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
                                        <i class="has-text-success tainacan-icon tainacan-icon-1-25em tainacan-icon-approvedcircle"/>
                                    </span>
                                </b-tooltip>
                            </div>

                            <button
                                    :disabled="!groupId"
                                    v-if="!bulkEditionProcedures[criterion].isDone &&
                                        !bulkEditionProcedures[criterion].isExecuting &&
                                        bulkEditionProcedures[criterion].metadatum &&
                                        bulkEditionProcedures[criterion].action && 
                                        (bulkEditionProcedures[criterion].action != editionActions.copy || (bulkEditionProcedures[criterion].action == editionActions.copy && !!bulkEditionProcedures[criterion].metadatumIdCopyFrom))"
                                    @click="executeBulkEditionProcedure(criterion)"
                                    class="button is-white is-pulled-right">
                                <span 
                                        v-tooltip="{
                                            content: $i18n.get('label_apply_changes'),
                                            autoHide: true,
                                            placement: 'auto-end'
                                        }"
                                        class="icon">
                                    <i class="has-text-gray4 tainacan-icon tainacan-icon-1-25em tainacan-icon-play"/>
                                </span>
                            </button>

                            <div v-if="bulkEditionProcedures[criterion].isExecuting">
                                <button class="button is-white is-loading" />
                            </div>
                        </div>
                    </div>
                </transition-group>
                <a 
                        :disabled="dones.every((item) => item === true) === false"
                        @click="addEditionCriterion()"
                        class="has-text-right is-block add-link">
                    <span class="icon is-small">
                        <i class="tainacan-icon has-text-secondary tainacan-icon-add"/>
                    </span>
                    &nbsp;{{ $i18n.get('new_bulk_edition_criterion') }}
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
            collectionId: Number
        },
        data() {
            return {
                editionCriteria: [1],
                editionActions: {
                    add: this.$i18n.get('add_value'),
                    redefine: this.$i18n.get('set_new_value'),
                    replace: this.$i18n.get('replace_value'),
                    remove: this.$i18n.get('remove_a_value'),
                    clear: this.$i18n.get('clear_values'),
                    copy: this.$i18n.get('copy_value')
                },
                bulkEditionProcedures: {
                    1: {
                        isDone: false,
                        isExecuting: false,
                        totalItemsEditedWithSuccess: 0,
                        tooltipShow: true,
                    }
                },
                groupId: null,
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
            if (this.collectionId) {
                this.metadataIsLoading = true;

                // Cancels previous Request
                if (this.metadataSearchCancel != undefined)
                    this.metadataSearchCancel.cancel('Metadata search Canceled.');

                this.fetchMetadata({
                    collectionId: this.collectionId,
                    isRepositoryLevel: false,
                    isContextEdit: true,
                    includeDisabled: false,
                    parent: 'any'
                })
                .then((resp) => {
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
                collectionId: this.collectionId ? this.collectionId : 'default'
            }).then(() => {
                this.groupId = this.getGroupId();
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
                'getGroupId'
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
                'removeValueInBulk',
                'copyValuesInBulk'
            ]),
            ...mapActions('metadata', [
                'fetchMetadata'
            ]),
            ...mapGetters('metadata', [
                'getMetadata'
            ]),
            finalizeProcedure(criterion){

                this.$set(this.bulkEditionProcedures[criterion], 'isDone', true);

                this.dones[this.editionCriteria.indexOf(criterion)] = true;
 
                this.$set(this.bulkEditionProcedures[criterion], 'isExecuting', false);
            },
            executeBulkEditionProcedure(criterion){
                let procedure = this.bulkEditionProcedures[criterion];

                if (procedure.action === this.editionActions.redefine) {
                    this.$set(this.bulkEditionProcedures[criterion], 'isExecuting', true);

                    if (procedure.metadatum.id === 'status'){
                        this.setStatusInBulk({
                            collectionId: this.collectionId,
                            groupId: this.groupId,
                            bodyParams: { value: procedure.newValue }
                        }).then(() => {
                            this.finalizeProcedure(criterion);
                        });
                    } if(procedure.metadatum.id === 'comments'){
                        this.setCommentStatusInBulk({
                            collectionId: this.collectionId,
                            groupId: this.groupId,
                            bodyParams: { value: procedure.newValue }
                        }).then(() => {
                            this.finalizeProcedure(criterion);
                        });
                    } else {
                        this.setValueInBulk({
                            collectionId: this.collectionId,
                            groupId: this.groupId,
                            bodyParams: {
                                metadatum_id: procedure.metadatum.id,
                                value: procedure.newValue
                            }
                        }).then(() => {
                            this.finalizeProcedure(criterion);
                        });
                    }
                } else if (procedure.action === this.editionActions.add) {
                    this.$set(this.bulkEditionProcedures[criterion], 'isExecuting', true);

                    this.addValueInBulk({
                        collectionId: this.collectionId,
                        groupId: this.groupId,
                        bodyParams: {
                            metadatum_id: procedure.metadatum.id,
                            value: procedure.newValue,
                        }
                    }).then(() => {
                        this.finalizeProcedure(criterion);
                    });
                } else if (procedure.action === this.editionActions.replace) {
                    this.$set(this.bulkEditionProcedures[criterion], 'isExecuting', true);

                    this.replaceValueInBulk({
                        collectionId: this.collectionId,
                        groupId: this.groupId,
                        bodyParams: {
                            metadatum_id: procedure.metadatum.id,
                            old_value: procedure.oldValue,
                            new_value: procedure.newValue,
                        }
                    }).then(() => {
                        this.finalizeProcedure(criterion);
                    });
                } else if (procedure.action === this.editionActions.remove) {
                    this.$set(this.bulkEditionProcedures[criterion], 'isExecuting', true);

                    this.removeValueInBulk({
                        collectionId: this.collectionId,
                        groupId: this.groupId,
                        bodyParams: {
                            metadatum_id: procedure.metadatum.id,
                            value: procedure.newValue,
                        }
                    }).then(() => {
                        this.finalizeProcedure(criterion);
                    });
                } else if (procedure.action === this.editionActions.clear) {
                    this.$set(this.bulkEditionProcedures[criterion], 'isExecuting', true);

                    this.clearValuesInBulk({
                        collectionId: this.collectionId,
                        groupId: this.groupId,
                        bodyParams: {
                            metadatum_id: procedure.metadatum.id
                        }
                    }).then(() => {
                        this.finalizeProcedure(criterion);
                    });
                } else if (procedure.action === this.editionActions.copy) {
                    this.$set(this.bulkEditionProcedures[criterion], 'isExecuting', true);

                    this.copyValuesInBulk({
                        collectionId: this.collectionId,
                        groupId: this.groupId,
                        bodyParams: {
                            metadatum_id_to: parseInt(procedure.metadatum.id),
                            metadatum_id_from: procedure.metadatumIdCopyFrom,
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

                if (this.editionCriteria[criterionIndex]) {
                    this.editionCriteria.splice(criterionIndex, 1);
                    delete this.bulkEditionProcedures[criterion];
                    this.dones.splice(criterionIndex, 1)
                }
            },
            getValidEditionActions(metadatum) {
                let validEditionActions = JSON.parse(JSON.stringify(this.editionActions));
                
                for (let actionKey of Object.keys(this.editionActions)) {

                    // Not multiple metadata have less options
                    if (metadatum.multiple != 'yes' && (actionKey == 'add' || actionKey == 'replace' || actionKey == 'remove')) {
                        delete validEditionActions[actionKey];
                        continue;
                    }

                    // These special metadata are even more limited
                    if ((metadatum.id == 'status' || metadatum.id == 'comments') && (actionKey == 'clear' || actionKey == 'copy')) {
                        delete validEditionActions[actionKey];
                        continue;
                    }

                    // For allowing copy, we also need to check more details of the metadata
                    // We only offer copy when there is another metadataum of same type, that is not a child component;
                    // The exception are User metadatum, as we can also copy values from created_by
                    if (actionKey == 'copy' && metadatum.metadata_type_object) {
                        const otherMetadatumOfSameTypeIndex = this.metadata.findIndex(otherMetadatum => {
                            return (
                                otherMetadatum.id != metadatum.id && 
                                otherMetadatum.metadata_type_object.component == metadatum.metadata_type_object.component &&
                                otherMetadatum.parent <= 0
                            );
                        });
                        
                        if (otherMetadatumOfSameTypeIndex < 0 && metadatum.metadata_type_object.component != 'tainacan-user') {
                            delete validEditionActions[actionKey];
                        }
                    }
                }
                
                return validEditionActions;
            },
            getAllowedMetadataForCopy(criterion) {
                if (this.bulkEditionProcedures[criterion] &&
                    this.bulkEditionProcedures[criterion].metadatum &&
                    this.bulkEditionProcedures[criterion].action &&
                    this.bulkEditionProcedures[criterion].action == this.editionActions.copy) {
                    
                    const selectedMetadatum = this.bulkEditionProcedures[criterion].metadatum;
                    if (selectedMetadatum.metadata_type_object && selectedMetadatum.metadata_type) {
                        return JSON.parse(JSON.stringify(this.metadata)).filter((metadatum) => {
                            return (
                                metadatum.id != selectedMetadatum.id &&
                                metadatum.metadata_type == selectedMetadatum.metadata_type &&
                                (selectedMetadatum.multiple == 'yes' || (metadatum.multiple != 'yes' && selectedMetadatum.multiple != 'yes'))
                            )
                        });
                    }
                }

                return [];
            },
            addToBulkEditionProcedures(value, key, criterion) {
                if (Array.isArray(value))
                    value = value[0];

                this.$set(this.bulkEditionProcedures[criterion], `${key}`, value);
            }
        }
    }
</script>

<style lang="scss">

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
        
        .button.is-white.is-loading {
            cursor: inherit;
            color: var(--tainacan-gray4) !important;
        }
        .is-pulled-right .is-success {
            background-color: transparent !important;
        }
        .add-link {
            font-size: 0.875em;
        }
        .form-submit {
            padding: 42px 0 0.4em 0 !important;
        }
    }

    .no-overflow-modal-card-body {
        padding: 0 !important;
        overflow: unset !important;
    }

    .tainacan-total-objects-info {
        font-size: 0.75em;
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
                color: var(--tainacan-black) !important;
                border: none !important;
                background-color: var(--tainacan-input-background-color) !important;
            }

            .taginput-container {
                .tags {
                    color: var(--tainacan-black) !important;
                    background-color: var(--tainacan-white) !important;
                    border: none !important;

                    .tag.is-delete {
                        display: none !important;
                    }

                    .tag {
                        max-width: 100% !important;
                    }

                    &:hover, .tag {
                        background-color: var(--tainacan-white) !important;
                    }
                }

                .icon {
                    display: none !important;
                }
            }

            input {
                color: var(--tainacan-black) !important;
                border: none !important;
                background-color: var(--tainacan-white) !important;
            }

            textarea {
                color: var(--tainacan-black) !important;
                border: none !important;
                background-color: var(--tainacan-white) !important;
                min-height: auto !important;
                line-height: 1.5em;
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
                color: var(--tainacan-heading-color) !important;
            }
        }
    }

    .tainacan-add-edition-criterion-button {
        font-size: 0.75em;
        color: var(--tainacan-turquoise5);
    }

    .tainacan-add-edition-criterion-button-disabled {
        cursor: not-allowed !important;
    }
</style>