<template>
    <div>
        <b-loading
                :is-full-page="false"
                :active.sync="isLoading"
                :can-cancel="false"/>
        <div class="tainacan-page-title">
            <h1 v-if="isCreatingBulkAdd">{{ $i18n.get('title_create_item_collection') + ' ' }}<span style="font-weight: 600;">{{ collectionName }}</span></h1>
            <h1 v-else>{{ $i18n.get('title_edit_item') + ' ' }}<span style="font-weight: 600;">{{ (item != null && item != undefined) ? item.title : '' }}</span></h1>
            <a 
                    @click="$router.go(-1)"
                    class="back-link has-text-secondary">
                {{ $i18n.get('back') }}
            </a>
            <hr>
        </div>
        <form
                @submit.prevent="createBulkEditGroup()"
                v-if="!isLoading"
                class="tainacan-form" 
                label-width="120px">
                
            <!-- File Source input -->
            <b-field :addons="false">
                <label class="label">{{ $i18n.get('label_source_file') }}</label>
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
                <!-- Sequence Progress -->
                <div 
                        v-if="uploadedItems.length > 0"
                        :style="{ width: (amountFinished/uploadedItems.length)*100 + '%' }"
                        class="sequence-progress"/>
                <div    
                        v-if="uploadedItems.length > 0"
                        class="sequence-progress-background"/>

                <transition-group name="item-appear">
                    <div 
                            class="document-item"
                            v-for="(item, index) of uploadedItems"
                            :key="index">
                        <img 
                                v-if="item.document!= undefined && item.document != '' && item.document_type != 'empty'"
                                class="document-thumb"
                                :alt="item.title"
                                :src="item.thumbnail.tainacan_small ? item.thumbnail.tainacan_small : item.thumbnail.thumb" > 
                        <span 
                            class="document-name"
                            v-html="item.title" />                            
                        <span 
                                v-if="item.errorMessage != undefined" 
                                class="help is-danger">
                            {{ item.errorMessage }}
                        </span>                       
                        <div class="document-actions">
                            <span 
                                    v-if="(item.errorMessage == undefined) && (item.document == '' || item.document_type == 'empty')"
                                    class="icon has-text-success loading-icon">
                                <div class="control has-icons-right is-loading is-clearfix" />
                            </span>  
                            <span 
                                    v-tooltip="{
                                        content: $i18n.get('label_remove'),
                                        autoHide: false,
                                        placement: 'auto-start'
                                    }"
                                    v-if="item.document != '' && item.document_type != 'empty'"
                                    class="icon has-text-gray action-icon"
                                    @click="deleteOneItem(item.id, index)">
                                <i class="mdi mdi-18px mdi-delete"/>
                            </span>
                        </div>                    
                    </div>
                </transition-group>
            </div>
            <div class="field is-grouped form-submit">
                <div class="control">
                    <button 
                            type="button"
                            class="button is-outlined" 
                            @click.prevent="$router.go(-1)" 
                            slot="trigger">{{ $i18n.get('cancel') }}</button>
                </div>
                <div class="control">
                    <button 
                            :disabled="uploadedItems.length <= 0 "
                            class="button is-success" 
                            type="submit">{{ $i18n.get('save') }}</button>
                </div>
            </div>
        </form>
    </div>
</template>

<script>
import { mapActions, mapGetters } from 'vuex';
import CustomDialog from '../other/custom-dialog.vue';

export default {
    name: 'ItemBulkEditionForm',
    data(){
        return {
            isLoading: false,
            isCreatingBulkAdd: true,
            collectionName: '',
            submitedFileList: [],
            thumbPlaceholderPath: tainacan_plugin.base_url + '/admin/images/placeholder_square.png',
            uploadedItems: [],
            amountFinished: 0
        }
    },
    computed: {
        uploadedFileList() {
            return this.getFiles();
        }
    },
    methods: {
        ...mapActions('collection', [
            'sendFile',
            'cleanFiles',
            'fetchCollectionName',
            'deleteItem'
        ]),
         ...mapGetters('collection', [
            'getFiles',
        ]),
        ...mapActions('item', [
            'sendItem',
            'updateItemDocument',
        ]),
        ...mapActions('bulkedition', [
            'fetchItemIdInSequence',
            'fetchGroup',
            'createEditGroup'
        ]),
        ...mapGetters('bulkedition', [
            'getItemIdInSequence',
            'getGroup'
        ]),
        uploadFiles() {
            
            for (let file of this.submitedFileList) {

                // Creates draft Item
                let data = {
                    collection_id: this.collectionId, 
                    status: 'auto-draft', 
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
                                            this.$set( this.uploadedItems, index, item );
                                        else 
                                            this.uploadedItems.unshift( item );
                                    })
                                    .catch((error) => {
                                        this.$console.error(error);
                                    });
                            })
                            .catch((error) => {
                                item.errorMessage = error.data.message;
                                this.$console.error(error);
                            });
                })
                .catch((error) => {
                    this.$console.error(error)
                });

                    
            }
        },
        createBulkEditGroup() {
            let onlyItemIds = this.uploadedItems.map(item => item.id);

            this.createEditGroup({
                object: onlyItemIds,
                collectionID: this.collectionId
            }).then((group) => {
                let groupId = group.id;
                this.$router.push(this.$routerHelper.getItemMetadataBulkAddPath(this.collectionId, groupId));
            }); 
        },
        deleteOneItem(itemId, index) {
            this.$modal.open({
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
                            this.uploadedItems.splice(index, 1) 
                            this.amountFinished --;
                        });
                    }
                }
            });
        },
    },
    created() {
        // Obtains collection ID
        this.collectionId = this.$route.params.collectionId;

        // Obtains collection name
        this.fetchCollectionName(this.collectionId).then((collectionName) => {
            this.collectionName = collectionName;
        });

        this.cleanFiles();

        // ITEM BULK ADDITION
        if (this.$route.fullPath.split("/").pop() == "bulk-add") {
            this.isCreatingBulkAdd = true;

        // EDITING BULK ADDITION
        } else  {
            this.isLoading = true;
        }

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
            margin-bottom: 40px;
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
            a.back-link{
                font-weight: 500;
                float: right;
                margin-top: 5px;
            }
            hr{
                margin: 3px 0px 4px 0px; 
                height: 1px;
                background-color: $secondary;
                width: 100%;
            }
        }
        .source-file-upload {
            width: 100%;
            display: grid;
        }
        .document-list {
            display: inline-block;
            width: 100%;

            .document-item {
                display: flex;
                flex-wrap: nowrap;
                width: 100%;
                justify-content: space-between;
                align-items: center;
                margin: 0.75rem;
                cursor: default;

                .document-thumb {
                    max-height: 42px;
                    max-width: 42px;
                    margin-right: 0.75rem;
                }

                .document-actions {
                    margin-left: auto;
                    

                    .loading-icon .control.is-loading::after {
                        position: relative !important;
                        right: 0;
                        top: 0;
                    }
                }

                .help.is-danger {
                    margin-left: auto;
                }
            }

            .sequence-progress {
                height: 5px;
                background: $turquoise5;
                width: 0%;
                transition: width 0.2s;
            }
            .sequence-progress-background {
                height: 5px;
                background: $gray3;
                width: 100%;
                top: -5px;
                z-index: -1;
                position: relative;
            }        
        }
    }

</style>
