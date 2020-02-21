<template>
    <div>
        <b-loading
                :is-full-page="false"
                :active.sync="isLoading"
                :can-cancel="false"/>
        <div class="tainacan-page-title">
            <h1>{{ $i18n.get('add_items_bulk') }}</h1>
            <a 
                    @click="$router.go(-1)"
                    class="back-link has-text-secondary">
                {{ $i18n.get('back') }}
            </a>
            <hr>
        </div>
        <form
                v-if="!isLoading && collection && collection.current_user_can_bulk_edit"
                class="tainacan-form" 
                label-width="120px">
                
            <!-- File Source input -->
            <b-field :addons="false">
                <label class="label">{{ $i18n.get('label_documents_upload') }}</label>
                <br>
                <b-upload
                        native
                        v-model="submitedFileList"
                        drag-drop
                        multiple
                        @input="uploadFiles()"
                        class="source-file-upload">
                    <section class="drop-inner">
                        <div class="content has-text-centered">
                            <p>
                                <b-icon
                                        icon="upload"
                                        size="is-large"/>
                            </p>
                            <p>{{ $i18n.get('instruction_drop_file_or_click_to_upload') }}</p>
                        </div>
                    </section>
                </b-upload>
            </b-field>
        
            <div class="document-list">

                <!-- Sequence Progress Info -->
                <div class="sequence-progress-info">
                    <p v-if="uploadedItems.length > 0 && uploadedItems.length != amountFinished">
                        <span class="icon is-small has-text-secondary">
                            <i class="tainacan-icon tainacan-icon-18px tainacan-icon-updating"/>
                        </span>
                        {{ $i18n.get('label_upload_file_prepare_items') }}
                    </p>
                    <p v-if="uploadedItems.length > 0 && uploadedItems.length == amountFinished">
                        <span class="icon is-small has-text-success">
                            <i class="tainacan-icon tainacan-icon-18px tainacan-icon-approvedcircle"/>
                        </span>
                        {{ $i18n.get('label_process_completed') }}
                    </p>
                    <p    
                            v-if="uploadedItems.length > 0 && (uploadedItems.length - amountFinished) > 1"
                            class="has-text-gray">
                        {{ $i18n.getWithVariables('label_%s_files_remaining', [(uploadedItems.length - amountFinished)]) }}
                    </p>
                    <p    
                            v-if="uploadedItems.length > 0 && (uploadedItems.length - amountFinished) == 1"
                            class="has-text-gray">
                        {{ $i18n.get('label_one_file_remaining') }}
                    </p>
                </div>

                <!-- Sequence Progress Bar -->
                <div 
                        v-if="uploadedItems.length > 0"
                        :style="{ width: (amountFinished/uploadedItems.length)*100 + '%' }"
                        class="sequence-progress"/>
                <div    
                        v-if="uploadedItems.length > 0"
                        class="sequence-progress-background"/>
                
                <!-- Uploaded Items -->
                <transition-group name="item-appear">
                    <div 
                            class="document-item"
                            v-for="(item, index) of uploadedItems"
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
                        <div class="document-process-state">
                            <span 
                                    v-if="(item.errorMessage == undefined) && (item.document == '' || item.document_type == 'empty')"
                                    class="icon has-text-success loading-icon">
                                <div class="control has-icons-right is-loading is-clearfix" />
                            </span>  
                            <span 
                                    v-if="item.document != '' && item.document_type != 'empty'"
                                    class="icon has-text-success"
                                    v-tooltip="{
                                        delay: {
                                            show: 500,
                                            hide: 300,
                                        },
                                        content: $i18n.get('label_document_uploaded'),
                                        autoHide: false,
                                        placement: 'auto-start'
                                    }">
                                <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-approvedcircle" />
                            </span>  
                        </div>   
                        <div 
                                v-if="item.document != '' && item.document_type != 'empty'"
                                class="document-actions">
                            <span 
                                    v-tooltip="{
                                        delay: {
                                            show: 500,
                                            hide: 300,
                                        },
                                        content: $i18n.get('label_button_delete_document'),
                                        autoHide: false,
                                        placement: 'auto-start'
                                    }"
                                    class="icon has-text-secondary action-icon"
                                    @click="deleteOneItem(item.id, index)">
                                <i class="tainacan-icon tainacan-icon-18px tainacan-icon-delete"/>
                            </span>
                        </div>                 
                    </div>
                </transition-group>
            </div>
            <footer class="footer">
                <div class="form-submission-footer field is-grouped form-submit">
                    <div class="control">
                        <button 
                                type="button"
                                class="button is-outlined" 
                                @click.prevent="$router.go(-1)" 
                                slot="trigger">{{ $i18n.get('cancel') }}</button>
                    </div>
                    <div 
                            style="margin-left: auto;"
                            class="control">
                        <button 
                                :disabled="!(uploadedItems.length > 0 && uploadedItems.length == amountFinished)"
                                class="button is-secondary" 
                                :class="{'is-loading': isCreatingSequenceEditGroup }"
                                @click.prevent="sequenceEditGroup()"
                                type="submit">{{ $i18n.get('label_sequence_edit_items') }}</button>
                    </div>
                    <div class="control">
                        <button 
                                :disabled="!(uploadedItems.length > 0 && uploadedItems.length == amountFinished)"
                                class="button is-secondary" 
                                @click.prevent="openBulkEditionModal()"
                                type="submit">{{ $i18n.get('label_bulk_edit_items') }}</button>
                    </div>
                </div>
            </footer>
        </form>
        <template v-else-if="!isLoading && collection && !collection.current_user_can_bulk_edit">
            <section class="section">
                <div class="content has-text-grey has-text-centered">
                    <p>
                        <span class="icon">
                            <i class="tainacan-icon tainacan-icon-30px tainacan-icon-collection"/>
                        </span>
                    </p>
                    <p>{{ $i18n.get('info_can_not_bulk_edit_items_collection') }}</p>
                </div>
            </section>
        </template>
    </div>
</template>

<script>
import { mapActions, mapGetters } from 'vuex';
import CustomDialog from '../other/custom-dialog.vue';
import BulkEditionModal from '../modals/bulk-edition-modal.vue';

export default {
    name: 'ItemBulkEditionForm',
    data(){
        return {
            collectionId: '',
            isLoading: false,
            isCreatingSequenceEditGroup: false,
            submitedFileList: [],
            thumbPlaceholderPath: tainacan_plugin.base_url + '/assets/images/placeholder_square.png',
            uploadedItems: [],
            amountFinished: 0
        }
    },
    computed: {
        uploadedFileList() {
            return this.getFiles();
        },
        collection() {
            return this.getCollection()
        }
    },
    created() {
        // Obtains collection ID
        this.collectionId = this.$route.params.collectionId;

        this.cleanFiles();

        // Updates Collection BreadCrumb
        this.$root.$emit('onCollectionBreadCrumbUpdate', [
            { path: this.$routerHelper.getCollectionPath(this.collectionId), label: this.$i18n.get('items') },
            { path: '', label: this.$i18n.get('add_items_bulk') }
        ]);
    },
    methods: {
        ...mapActions('collection', [
            'sendFile',
            'cleanFiles',
            'deleteItem'
        ]),
         ...mapGetters('collection', [
            'getFiles',
            'getCollection'
        ]),
        ...mapActions('item', [
            'sendItem',
            'updateItemDocument',
        ]),
        ...mapActions('bulkedition', [
            'createEditGroup',
            'createSequenceEditGroup',
            'setStatusInBulk'
        ]),
        uploadFiles() {
            
            for (let file of this.submitedFileList) {

                // Creates draft Item
                let data = {
                    collection_id: this.collectionId, 
                    status: 'draft', 
                    title: file.name
                };
                this.sendItem(data)
                    .then(item => {

                        let index = this.uploadedItems.findIndex(existingItem => existingItem.id === item.id);
                        if ( index >= 0)
                            this.$set( this.uploadedItems, index, item );
                        else 
                            this.uploadedItems.push( item );
                        
                        // Uploads Media Document
                        this.sendFile(file)
                            .then((uploadedFile) => {
                                
                                // Updates Item with Document
                                this.updateItemDocument({ 
                                        item_id: item.id, 
                                        document: new String(uploadedFile.id), 
                                        document_type: 'attachment' 
                                    })
                                    .then((item) => {     
                                        this.amountFinished++;

                                        let index = this.uploadedItems.findIndex(existingItem => existingItem.id === item.id);
                                        if ( index >= 0)
                                            this.$set( this.uploadedItems, index, item);
                                        else 
                                            this.uploadedItems.unshift( item );
                                    })
                                    .catch((error) => {
                                        this.$console.error(error);
                                    });
                            })
                            .catch((error) => {
                                let index = this.uploadedItems.findIndex(existingItem => existingItem.id === item.id);
                                if ( index >= 0)
                                    this.uploadedItems.splice(index, 1);

                                item.errorMessage = error.data && error.data.message ? error.data.message : this.$i18n.get('info_error_upload');
                                this.$buefy.toast.open({
                                    message: item.errorMessage + ": " + file.name,
                                    type: 'is-danger',
                                    position: 'is-bottom',
                                    duration: 3500
                                });
             
                                this.$console.error(error);
                            });
                })
                .catch((error) => {
                    this.$console.error(error)
                });

                    
            }
        },
        sequenceEditGroup() {
            let onlyItemIds = this.uploadedItems.map(item => item.id);
            this.isCreatingSequenceEditGroup = true;
            this.createSequenceEditGroup({
                object: onlyItemIds,
                collectionID: this.collectionId
            }).then((group) => {
                let sequenceId = group.id;
                this.isCreatingSequenceEditGroup = false;
                this.$router.push(this.$routerHelper.getCollectionSequenceEditPath(this.collectionId, sequenceId, 1));
            });
        },
        openBulkEditionModal() {

            let onlyItemIds = this.uploadedItems.map(item => item.id);

            this.$buefy.modal.open({
                parent: this,
                component: BulkEditionModal,
                props: {
                    modalTitle: this.$i18n.get('info_editing_items_in_bulk'),
                    totalItems: onlyItemIds.length,
                    selectedForBulk: onlyItemIds,
                    objectType: this.$i18n.get('items'),
                    collectionID: this.collectionId
                },
                width: 'calc(100% - 8.333333333%)',
                trapFocus: true
            });
        },
        deleteOneItem(itemId, index) {
            this.$buefy.modal.open({
                parent: this,
                component: CustomDialog,
                props: {
                    icon: 'alert',
                    title: this.$i18n.get('label_warning'),
                    message: this.isOnTrash ? this.$i18n.get('info_warning_item_delete') : this.$i18n.get('info_warning_item_trash'),
                    onConfirm: () => {
                        this.teste
                        this.deleteItem({
                            itemId: itemId
                        }).then(() => {
                            this.uploadedItems.splice(index, 1); 
                            this.amountFinished --;
                        });
                    }
                },
                trapFocus: true
            });
        },
    }
}
</script>

<style lang="scss" scoped>

    @import "../../scss/_variables.scss";


    .page-container {

        &>.tainacan-form {
            margin-bottom: 110px;
        }

        .tainacan-page-title {
            margin-bottom: 35px;
            display: flex;
            flex-wrap: wrap;
            align-items: flex-end;
            justify-content: space-between;

            h1, h2 {
                font-size: 1.25em;
                font-weight: 500;
                color: var(--tainacan-heading-color);
                display: inline-block;
                flex-shrink: 1;
                flex-grow: 1;
            }
            a.back-link{
                font-weight: 500;
                float: right;
                margin-top: 5px;
            }
            hr{
                margin: 3px 0px 4px 0px; 
                height: 1px;
                background-color: var(--tainacan-secondary);
                width: 100%;
            }
        }
        .source-file-upload {
            width: 100%;
            padding: 0.75em $page-side-padding;
            @include display-grid;
        }
        .document-list {
            display: inline-block;
            width: 100%;
            padding: 1em 8.333333%;

            .document-item {
                display: flex;
                flex-wrap: nowrap;
                width: 100%;
                justify-content: space-between;
                align-items: center;
                padding: 0.5em 0.75em;
                position: relative;
                cursor: default;

                .document-thumb {
                    max-height: 42px;
                    max-width: 42px;
                    margin-right: 1em;
                }

                .document-name {
                    text-overflow: ellipsis;
                    overflow: hidden;
                    width: 100%;
                    white-space: nowrap;
                }

                .document-process-state {
                    margin-left: auto;
                    
                    .loading-icon .control.is-loading::after {
                        position: relative !important;
                        right: 0;
                        top: 0;
                    }
                }

                .document-actions {
                    position: absolute;
                    right: 0;
                    background: var(--tainacan-gray2);
                    height: 100%;
                    display: none;
                    justify-content: center;
                    visibility: hidden;
                    align-items: center;
                    width: 42px;

                    .icon {
                        cursor: pointer;
                    }
                }

                &:hover {
                    background-color: var(--tainacan-gray1);

                    .document-actions {
                        display: flex;
                        visibility: visible;
                    }
                }

                .help.is-danger {
                    margin-left: auto;
                    width: 100%;
                    text-align: right;
                }
            }
            .sequence-progress-info {
                display: flex;
                justify-content: space-between;
                margin-bottom: 0.25em;

                .i::before {
                    font-size: 1.125em;
                    margin-left: 0.5em;
                }
            }
            .sequence-progress {
                height: 5px;
                background: var(--tainacan-turquoise5);
                width: 0%;
                transition: width 0.2s;
                margin-bottom: 1em;
            }
            .sequence-progress-background {
                height: 5px;
                background: var(--tainacan-gray3);
                width: 100%;
                top: -21px;
                z-index: -1;
                position: relative;
                margin-bottom: 1em;
            }        
        }

        .footer {
            padding: 18px $page-side-padding;
            position: absolute;
            bottom: 0;
            z-index: 999999;
            background-color: var(--tainacan-gray1);
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
        }
    }

</style>
