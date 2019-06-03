<template>
    <div>
        <b-loading
                :is-full-page="false"
                :active.sync="isLoadingMetadata"
                :can-cancel="false"/>
        <div class="tainacan-page-title">
            <h1><span class="status-tag">{{ $i18n.get('status_' + status) }}</span>{{ $i18n.get('label_bulk_edit_items') }}</h1>
            <a 
                    @click="$router.go(-1)"
                    class="back-link has-text-secondary">
                {{ $i18n.get('back') }}
            </a>
            <hr>
        </div>
        <form
                class="tainacan-form" 
                label-width="120px">
                
            <div class="columns">
                <div class="column is-half document-list">
                    <div class="section-label">
                        <label>{{ $i18n.get('label_added_items') }}
                            <span 
                                    v-if="!isLoadingGroupInfo && bulkEditGroup.items_count != undefined"
                                    class="has-text-gray has-text-weight-normal">{{ ' (' + bulkEditGroup.items_count + ')' }}</span>
                        </label>
                    </div>
                    <br>
                    <p v-if="items.length <= 0 && !isLoadingGroupInfo && bulkEditGroup.items_count == 1">
                        {{ $i18n.get('info_there_is_one_item_being_edited') }}
                    </p>
                    <p v-if="items.length <= 0 && !isLoadingGroupInfo && bulkEditGroup.items_count > 1">
                        {{ $i18n.getWithVariables('info_there_are_%s_items_being_edited', [bulkEditGroup.items_count]) }}
                    </p>
                    <p v-if="items.length <= 0 && !isLoadingGroupInfo">
                        {{ $i18n.get('info_no_preview_found') }}
                    </p>
                    <transition-group name="item-appear">
                        <div 
                                class="document-item"
                                v-for="(item) of items"
                                :key="item.id">
                            <img 
                                v-if="item.document!= undefined && item.document != '' && item.document_type != 'empty'"
                                class="document-thumb"
                                :alt="$i18n.get('label_thumbnail') + ': ' + item.title"
                                :src="item.thumbnail['tainacan-small'] ? item.thumbnail['tainacan-small'][0] : (item.thumbnail.thumbnail ? item.thumbnail.thumbnail[0] : thumbPlaceholderPath)" > 
                            <span 
                                class="document-name"
                                v-html="item.title" />                            
                            <span 
                                    v-if="item.errorMessage != undefined" 
                                    class="help is-danger">
                                {{ item.errorMessage }}
                            </span>                                 
                        </div>
                    </transition-group>
                </div>
                <div class="column is-half">
                     
                    <!-- Visibility (status public or private) -------------------------------- -->
                    <div class="section-label">
                        <label>{{ $i18n.get('label_status') }}</label>
                        <span class="required-metadatum-asterisk">*</span>
                        <help-button
                                :title="$i18n.get('label_status')"
                                :message="$i18n.get('info_visibility_helper')"/>
                    </div>
                    <div class="section-status">
                        <div class="field has-addons">
                            <b-radio
                                    v-for="(statusOption, index) of $statusHelper.getStatuses().filter(option => { return option.value != 'trash' })"
                                    :key="index"
                                    v-model="status"
                                    @input="changeStatus($event)"
                                    :value="statusOption.slug"
                                    :native-value="statusOption.slug">
                                <span class="icon">
                                    <i 
                                            class="tainacan-icon"
                                            :class="$statusHelper.getIcon(statusOption.slug)"/>
                                </span> {{ statusOption.name }}
                            </b-radio>
                        </div>
                    </div>

                    <!-- Metadata from Collection-------------------------------- -->
                    <span class="section-label">
                        <label>{{ $i18n.get('metadata') }}</label>
                    </span>
                    <br>
                    <a
                            class="collapse-all"
                            @click="toggleCollapseAll()">
                        {{ collapseAll ? $i18n.get('label_collapse_all') : $i18n.get('label_expand_all') }}
                        <b-icon :icon=" collapseAll ? 'menu-down' : 'menu-right'" />
                    </a>

                    <template 
                                v-for="(metadatum, index) of metadata">
                        <b-field
                                :key="index"
                                :addons="false"
                                :message="getErrorMessage(formErrors[metadatum.id])"
                                :type="getErrorMessage(formErrors[metadatum.id]) != '' ? 'is-danger' : ''">
                            <span   
                                    class="collapse-handle"
                                    @click="changeCollapse(!metadatumCollapses[index], index)">
                                <b-icon 
                                        type="is-secondary"
                                        :icon="metadatumCollapses[index] ? 'menu-down' : 'menu-right'" />
                                <label class="label">{{ metadatum.name }}</label>
                                <span
                                        v-if="metadatum.required == 'yes'"
                                        class="required-metadatum-asterisk">*</span>
                                <span class="metadata-type">({{ $i18n.get(metadatum.metadata_type_object.component) }})</span>
                                <help-button 
                                        :title="metadatum.name"
                                        :message="metadatum.description"/>
                            </span>
                            <transition name="filter-item">
                                <div v-show="metadatumCollapses[index]">
                                    <component
                                            :forced-component-type="false"
                                            :allow-new="false"
                                            :allow-select-to-create="metadatum.metadata_type_options.allow_new_terms === 'yes'"
                                            :maxtags="1"
                                            :id="metadatum.metadata_type_object.component + '-' + metadatum.slug"
                                            :is="metadatum.metadata_type_object.component"
                                            :metadatum="{ metadatum: metadatum }"
                                            @input="clearErrorMessage(metadatum.id); bulkEdit($event, metadatum)"/>
                                            <!-- :class="{'is-field-history': bulkEditionProcedures[criterion].isDone}"
                                            :disabled="bulkEditionProcedures[criterion].isDone || bulkEditionProcedures[criterion].isExecuting" -->
                                </div>
                            </transition>
                        </b-field>
                    </template>

                    <b-loading 
                            :is-full-page="false"
                            :active.sync="isLoadingMetadata"
                            :can-cancel="false"/>
                </div>
            </div>
            <footer class="footer">
                <!-- Last Updated Info --> 
                <div class="update-info-section"> 
                    <p v-if="!isExecutingBulkEdit && lastUpdated != ''">
                        {{ ($i18n.get('info_updated_at') + ' ' + lastUpdated) }}
                        <span class="help is-danger">{{ formErrorMessage }}</span>
                    </p>
                    <p v-if="!isExecutingBulkEdit && lastUpdated == ''">
                        <span class="help is-danger">{{ formErrorMessage }}</span>
                    </p>          
                    <p 
                            class="update-warning"
                            v-if="isExecutingBulkEdit">
                        <b-icon icon="autorenew" />{{ $i18n.get('info_updating_metadata_values') }}
                        <span class="help is-danger">{{ formErrorMessage }}</span>
                    </p> 
                </div>  
                <div class="form-submission-footer">
                    <button 
                            @click="onSubmit('trash')"
                            type="button"
                            :class="{ 'is-loading': isTrashingItems }"
                            class="button is-outlined">{{ $i18n.get('label_send_to_trash') }}</button>        
                    <button 
                            class="button is-secondary" 
                            :class="{'is-loading': isCreatingSequenceEditGroup }"
                            @click.prevent="sequenceEditGroup()"
                            type="submit">{{ $i18n.get('label_sequence_edit_items') }}</button>           
                    <button 
                            :disabled="formErrorMessage != undefined && formErrorMessage != ''"
                            @click="onSubmit(status)"
                            type="button"
                            :class="{ 'is-loading': isPublishingItems }"
                            class="button is-success">{{ $i18n.get('finish') }}</button>
                </div>
            </footer>
        </form>
    </div>
</template>

<script>
import { mapActions, mapGetters } from 'vuex';
import CustomDialog from '../other/custom-dialog.vue';

export default {
    name: 'ItemMetadataBulkEditionForm',
    data(){
        return {
            collectionId: '',
            isLoadingItems: false,
            isLoadingMetadata: false,
            isLoadingGroupInfo: false,
            isExecutingBulkEdit: false,
            isCreatingSequenceEditGroup: false,
            isUpdatingItems: false,
            isTrashingItems: false,
            isPublishingItems: false,
            collapseAll: true,
            thumbPlaceholderPath: tainacan_plugin.base_url + '/admin/images/placeholder_square.png',
            metadatumCollapses: [],
            formErrors: {},
            status: 'draft',
            groupID: null,
            formErrorMessage: ''
        }
    },
    computed: {
        metadata() {
            return this.getMetadata();
        },
        lastUpdated() {
            return this.getLastUpdated();
        },
        items() {
            return this.getBulkAddItems();
        },
        bulkEditGroup() {
            return this.getGroup();
        }
    },
    methods: {
        ...mapActions('item', [
            'updateItem'
        ]),
        ...mapActions('metadata', [
            'fetchMetadata',
        ]),
        ...mapGetters('metadata', [
            'getMetadata',
        ]),
        ...mapActions('bulkedition', [
            'setValueInBulk',
            'addValueInBulk',
            'replaceValueInBulk',
            'redefineValueInBulk',
            'setStatusInBulk',
            'removeValueInBulk',
            'createEditGroup',
            'deleteItemsInBulk',
            'trashItemsInBulk',
            'fetchItemIdInSequence',
            'fetchGroup'  
        ]),
        ...mapGetters('bulkedition', [
            'getItemIdInSequence',
            'getGroup',
            'getLastUpdated',
            'getBulkAddItems'
        ]),
        toggleCollapseAll() {
            this.collapseAll = !this.collapseAll;

            for (let i = 0; i < this.metadatumCollapses.length; i++)
                this.metadatumCollapses[i] = this.collapseAll;
        },
        changeCollapse(event, index) {
            this.metadatumCollapses.splice(index, 1, event);
        },
        bulkEdit: _.debounce(function(newValue, metadatum) {
            let values = [];
            if (!(Array.isArray(newValue)))
                values.push(newValue);
            else
                values = newValue;

            for (let value of values) {
                this.isExecutingBulkEdit = true;
                this.setValueInBulk({
                    collectionID: this.collectionId,
                    groupID: this.groupID,
                    bodyParams: {
                        metadatum_id: metadatum.id,
                        value: value,
                    }
                }).then(() => {
                    this.isExecutingBulkEdit = false;

                }).catch(() => this.isExecutingBulkEdit = false);
            }

        }, 1000),
        onSubmit(status) {
            this.isExecutingBulkEdit = true;

            if (status != 'trash') {
                this.status = status;
                this.isPublishingItems = false;
                this.isExecutingBulkEdit = false;
   
                this.$modal.open({
                    parent: this,
                    component: CustomDialog,
                    props: {
                        icon: 'alert',
                        title: this.$i18n.get('label_warning'),
                        message: this.$i18n.get('info_leaving_bulk_edition'	),
                        onConfirm: () => {
                            this.$router.push(this.$routerHelper.getCollectionItemsPath(this.collectionId));
                        }
                    }
                });
                
            } else if (status == 'trash') {

                this.$modal.open({
                    parent: this,
                    component: CustomDialog,
                    props: {
                        icon: 'alert',
                        title: this.$i18n.get('label_warning'),
                        message: this.$i18n.get('info_warning_selected_items_trash'),
                        onConfirm: () => {
                            
                            this.isTrashingItems = true;
                            this.trashItemsInBulk({
                                    groupID: this.groupID,
                                    collectionID: this.collectionId
                                }).then(() => {
                                    this.status = status;
                                    this.isTrashingItems = false;
                                    this.isExecutingBulkEdit = false;
                                    this.$router.push(this.$routerHelper.getCollectionItemsPath(this.collectionId));
                                }).catch(() => {
                                    this.isExecutingBulkEdit = false;
                                    this.isTrashingItems = false;
                                });
                        }
                    }
                });            
            }
        },
        sequenceEditGroup() {
            this.isCreatingSequenceEditGroup = true;
            this.$router.push(this.$routerHelper.getCollectionSequenceEditPath(this.collectionId, this.groupID, 1));    
        },
        changeStatus(status) {
            this.isPublishingItems = true;

            // Gets an item from the bulk group
            this.fetchItemIdInSequence({ collectionId: this.collectionId, sequenceId: this.groupID, itemPosition: 1 })
                .then((itemId) => {

                    // Test if this item can be set to this status
                    this.updateItem({ id: itemId, status: status })
                        .then(() => {

                            // The status can be applied to everyone.
                            this.setStatusInBulk({
                                groupID: this.groupID,
                                collectionID: this.collectionId,
                                bodyParams: { value: status }
                            }).then(() => {
                            
                                this.status = status;
                                this.isPublishingItems = false;
                                this.isExecutingBulkEdit = false;
                                
                            }).catch(() => {
                                this.isPublishingItems = false;
                                this.isExecutingBulkEdit = false;
                            });
                        })
                        .catch((errors) => {
                            // The status can not be applied.
                            this.isPublishingItems = false;
                            this.isExecutingBulkEdit = false;

                            for (let error of errors.errors) {
                                for (let metadatum of Object.keys(error)){
                                    this.formErrors[metadatum] = error[metadatum];
                                }
                            }
                            this.formErrorMessage = errors.error_message;
                        });
                })
                .catch(() => {
                    this.isPublishingItems = false;
                    this.isExecutingBulkEdit = false;
                });
        },
        clearErrorMessage(metadatumId) {
            this.formErrors[metadatumId] = false;
            let amountClean = 0;

            for (let formError in this.formErrors) {
                if (formError == false || formError == undefined)
                    amountClean++;
            }

            if (amountClean == 0)
                this.formErrorMessage = '';
        },
        getErrorMessage(errors) {
                
            let msg = '';
            if ( errors != undefined && errors != false) {
                for (let error of errors) { 
                    for (let index of Object.keys(error)) {
                        msg += error[index] + '\n';
                    }
                }
            } 
            return msg;
        },
    },
    created() {
        // Obtains collection ID
        this.collectionId = this.$route.params.collectionId;
        this.groupID = this.$route.params.groupId;

        // Updates Collection BreadCrumb
        this.$root.$emit('onCollectionBreadCrumbUpdate', [
            { path: this.$routerHelper.getCollectionPath(this.collectionId), label: this.$i18n.get('items') },
            { path: '', label: this.$i18n.get('add_items_bulk') }
        ]);

        this.isLoadingMetadata = true;
        this.fetchMetadata({
            collectionId: this.collectionId,
            isRepositoryLevel: false,
            isContextEdit: true,
            includeDisabled: false,
        }).then(() => {
            this.isLoadingMetadata = false;
            for (let i = 0; i < this.metadata.length; i++) {
                this.metadatumCollapses.push(false);
                this.metadatumCollapses[i] = true;
            }
        });

        this.isLoadingGroupInfo = true;
        this.fetchGroup({ collectionId: this.collectionId, groupId: this.groupID })
            .then(() => this.isLoadingGroupInfo = false)
            .then(() => this.isLoadingGroupInfo = false)
       
    }
}
</script>

<style lang="scss" scoped>

    @import "../../scss/_variables.scss";


    .page-container {

        &>.tainacan-form {
            padding: 0 $page-side-padding; 
            margin-bottom: 110px;
        }

        .tainacan-page-title {
            margin-bottom: 35px;
            display: flex;
            flex-wrap: wrap;
            align-items: flex-end;
            justify-content: space-between;

            h1, h2 {
                font-size: 20px;
                font-weight: 500;
                color: $gray5;
                display: inline-block;
                flex-shrink: 1;
                flex-grow: 1;
            }
            .status-tag {
                color: white;
                background: $turquoise5;
                padding: 0.15rem 0.5rem;
                font-size: 0.75rem;
                margin: 0 1rem 0 0;
                font-weight: 600;
                position: relative;
                top: -2px;
            }
            a.back-link{
                font-weight: 500;
                float: right;
                margin-top: 5px;
            }
            hr{
                margin: 0px 0px 4px 0px; 
                height: 1px;
                background-color: $secondary;
                width: 100%;
            }
        }

        .document-list {
            display: inline-block;

            .document-item {
                display: flex;
                flex-wrap: nowrap;
                width: 100%;
                justify-content: flex-start;
                align-items: center;
                padding: 0.5rem 0.75rem;
                position: relative;
                cursor: default;

                .document-thumb {
                    max-height: 42px;
                    max-width: 42px;
                    margin-right: 1rem;
                }

                .document-name {
                    text-overflow: ellipsis;
                    overflow: hidden;
                    width: 100%;
                    white-space: nowrap;
                }
            }
        }

        .column {

            .section-status{
                padding: 16px 0;     
                .field {
                    border-bottom: none;
                    .b-radio {
                        margin-right: 24px;
                        .icon  {
                            font-size: 18px !important; 
                            color: $gray3;
                        }
                    }
                }
            }

            .section-label {
                cursor: default;
                position: relative;
                label {
                    font-size: 16px !important;
                    font-weight: 500 !important;
                    color: $gray5 !important;
                    line-height: 1.2em;
                }
            }

            .collapse-all {
                font-size: 12px;
                .icon { 
                    vertical-align: bottom; 
                }
            }

            .multiple-inputs {
                display: flex;
                align-items: center;
                justify-content: space-between;
            }

            .field {
                border-bottom: 1px solid $gray2;
                padding: 10px 0px 10px 60px;

                .label {
                    font-size: 0.875rem;
                    font-weight: 500;
                    margin-left: 15px;
                    margin-bottom: 0.5em;
                }
                .metadata-type {
                    font-size: 0.8125rem;
                    font-weight: 400;
                    color: $gray3;
                    top: -0.2em;
                    position: relative;
                }
                .help-wrapper {
                    top: -0.2em;
                }
                .collapse-handle {
                    cursor: pointer;
                    position: relative;
                    margin-left: -42px;
                }
            }
        }

        .footer {
            padding: 18px $page-side-padding;
            position: absolute;
            bottom: 0;
            z-index: 999999;
            background-color: $gray1;
            width: 100%;
            height: 65px;
            display: flex;
            justify-content: flex-end;
            align-items: center;
            left: 0;

            .form-submission-footer {    
                .button {
                    margin-left: 16px;
                    margin-right: 6px;
                }
            }
            

            @keyframes blink {
                from { color: $blue5; }
                to { color: $gray4; }
            }

            .update-warning {
                color: $blue5;
                animation-name: blink;
                animation-duration: 0.5s;
                animation-delay: 0.5s;
                align-items: center;
                display: flex;
            }

            .update-info-section {
                color: $gray4;
                margin-right: auto;
            }

            .help {
                display: inline-block;
                font-size: 1.0em;
                margin-top: 0;
                margin-left: 24px;
            }
        }

    }

</style>
