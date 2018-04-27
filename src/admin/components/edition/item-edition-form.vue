<template>
    <div class="page-container">
        <b-tag 
                v-if="!isLoading" 
                :type="'is-' + getStatusColor(item.status)" 
                v-text="item.status"/>
        <form 
                v-if="!isLoading" 
                class="tainacan-form" 
                label-width="120px">

            <div class="columns">
                <div class="column is-narrow">

                    <!-- Thumbnail -------------------------------- --> 
                    <b-field 
                        :addons="false"
                        :label="$i18n.get('label_thumbnail')">
                        <div class="thumbnail-field">
                            <a 
                                    class="button is-rounred is-secondary"
                                    id="button-edit-thumbnail" 
                                    :aria-label="$i18n.get('label_button_edit_thumb')"
                                    @click.prevent="thumbnailMediaFrame.openFrame($event)">
                                <b-icon icon="pencil" />
                            </a>
                            <figure class="image is-128x128">
                                <span 
                                        v-if="item.featured_image == undefined || item.featured_image == false"
                                        class="image-placeholder">{{ $i18n.get('label_empty_thumbnail') }}</span>
                                <img
                                        id="thumbail-image"  
                                        :alt="$i18n.get('label_thumbnail')" 
                                        :src="(item.featured_image == undefined || item.featured_image == false) ? thumbPlaceholderPath : item.featured_image">
                            </figure>
                            <div class="thumbnail-buttons-row">
                                <a 
                                        id="button-delete" 
                                        :aria-label="$i18n.get('label_button_delete_thumb')" 
                                        @click="deleteThumbnail()">
                                    <b-icon icon="delete" />
                                </a>
                            </div>
                        </div>
                    </b-field>

                    <!-- Attachments ------------------------------------------ -->
                    <b-field 
                            :addons="false"
                            :label="$i18n.get('label_attachments')">
                        <button 
                                class="button is-secondary"
                                @click.prevent="attachmentMediaFrame.openFrame($event)">
                            Attatchments (tests)
                        </button>

                        <div class="uploaded-files">
                            <div 
                                    v-for="(file, index) in form.files"
                                    :key="index">
                                <span class="tag is-primary">
                                    {{ file.title.rendered }}
                                </span>
                            </div>
                        </div>   
                    </b-field>   
                </div>

                <div class="column">
                    <!-- Status -------------------------------- --> 
                    <b-field 
                            :addons="false"
                            :label="$i18n.get('label_status')">
                        <help-button 
                                :title="$i18n.getHelperTitle('items', 'status')" 
                                :message="$i18n.getHelperMessage('items', 'status')"/>
                        <b-select 
                                id="status-select"
                                v-model="form.status"
                                :placeholder="$i18n.get('instruction_select_a_status')">
                            <option
                                    :id="`status-option-${statusOption.value}`"
                                    v-for="statusOption in statusOptions"
                                    :key="statusOption.value"
                                    :value="statusOption.value"
                                    :disabled="statusOption.disabled">{{ statusOption.label }}
                            </option>
                        </b-select>
                    </b-field> 
                        
                    <!-- Fields from Collection-------------------------------- -->   
                    <tainacan-form-item                 
                        v-for="(field, index) in fieldList"
                        :key="index"
                        :field="field"/>    
                </div>
            </div>

            <div class="field is-grouped form-submit">
                <div class="control">     
                    <button
                            id="button-cancel-item-creation"
                            class="button is-outlined"
                            type="button"
                            @click="cancelBack">{{ $i18n.get('cancel') }}</button>
                </div>
                <div class="control">
                    <button
                            id="button-submit-item-creation"
                            @click.prevent="onSubmit"
                            class="button is-success" 
                            >{{ $i18n.get('save') }}</button> 
                </div>
            </div>
            <p class="help is-danger">{{ formErrorMessage }}</p> 
        </form>

        <b-loading 
                :active.sync="isLoading" 
                :can-cancel="false"/></div>
</template>

<script>
import { mapActions, mapGetters } from 'vuex';
import { eventBus } from '../../../js/event-bus-web-components.js'
import wpMediaFrames from '../../js/wp-media-frames';

export default {
    name: 'ItemEditionForm',
    data(){
        return {
            pageTitle: '',
            itemId: Number,
            item: null,
            collectionId: Number,
            isLoading: false,
            form: {
                collectionId: Number,
                status: '',
                files:[],
            },
            thumbnail: {},
            // Can be obtained from api later
            statusOptions: [{ 
                value: 'publish',
                label: this.$i18n.get('publish')
                }, {
                value: 'draft',
                label: this.$i18n.get('draft')
                }, {
                value: 'private',
                label: this.$i18n.get('private')
                }, {
                value: 'trash',
                label: this.$i18n.get('trash')
            }],
            formErrorMessage: '',
            thumbPlaceholderPath: tainacan_plugin.base_url + '/admin/images/placeholder_square.png',
            thumbnailMediaFrame: undefined,
            attachmentMediaFrame: undefined
        }
    },
    methods: {
        ...mapActions('item', [
            'sendItem',
            'updateItem',
            'fetchFields',
            'sendField',
            'fetchItem',
            'cleanFields',
            'sendAttachment',
            'updateThumbnail',
            'fetchAttachments'
        ]),
        ...mapGetters('item',[
            'getFields',
            'getItem',
        ]),
        onSubmit() {
            // Puts loading on Item edition
            this.isLoading = true;

            let data = {item_id: this.itemId, status: this.form.status};
            
            this.updateItem(data).then(updatedItem => {    
                
                this.item = updatedItem;

                // Fill this.form data with current data.
                this.form.status = this.item.status;

                this.isLoading = false;

                this.$router.push(this.$routerHelper.getItemPath(this.form.collectionId, this.itemId));
            })
            .catch((errors) => {
                for (let error of errors.errors) {     
                    for (let field of Object.keys(error)){                        
                       eventBus.errors.push({ field_id: field, errors: error[field]});
                    }  
                }
                this.formErrorMessage = errors.error_message;

                this.isLoading = false;
            });
        },
        getStatusColor(status) {
            switch(status) {
                case 'publish': 
                    return 'success'
                case 'draft':
                    return 'info'
                case 'private': 
                    return 'warning'
                case 'trash':
                    return 'danger'
                default:
                    return 'info'
            }
        },
        createNewItem() {
            // Puts loading on Draft Item creation
            this.isLoading = true;

            // Creates draft Item
            let data = {collection_id: this.form.collectionId, status: 'auto-draft'}; 
            this.sendItem(data).then(res => {

                this.itemId = res.id;
                this.item = res;

                // Initializes Media Frames now that itemId exists
                this.initializeMediaFrames();

                // Pre-fill status with publish to incentivate it
                this.form.status = 'publish';

                this.loadMetadata();
                
            })
            .catch(error => this.$console.error(error));
        },
        loadMetadata() { 
            // Obtains Item Field
            this.fetchFields(this.itemId).then(() => {
                this.isLoading = false;
            });
        }, 
        cancelBack(){
            this.$router.push(this.$routerHelper.getCollectionPath(this.collectionId));
        },
        uploadAttachment($event) {
            
            for (let file of $event) {
                this.sendAttachment({ item_id: this.itemId, file: file })
                .then(() => {
                    
                })
                .catch((error) => {
                    this.$console.error(error);
                });
            }
        },
        deleteThumbnail() {
            this.updateThumbnail({itemId: this.itemId, thumbnailId: 0})
            .then(() => {
                this.item.featured_image = false;
            })
            .catch((error) => {
                this.$console.error(error);
            });    
        },
        deleteFile(index) {
            this.$console.log("Delete:" + index);
        },
        initializeMediaFrames() {

            this.thumbnailMediaFrame = new wpMediaFrames.thumbnailControl(
                'my-thumbnail-media-frame', {
                    button_labels: {
                        frame_title: this.$i18n.get('instruction_select_item_thumbnail'),
                    },
                    relatedPostId: this.itemId,
                    onSave: (mediaId) => {
                        this.updateThumbnail({itemId: this.itemId, thumbnailId: mediaId})
                        .then((res) => {
                            this.item.featured_image = res.featured_image;
                        })
                        .catch(error => this.$console.error(error));
                    }
                }
            );

            this.attachmentMediaFrame = new wpMediaFrames.attachmentControl(
                'my-attachment-media-frame', {
                    button_labels: {
                        frame_title: this.$i18n.get('instruction_select_files_to_attach_to_item'),
                        frame_button: this.$i18n.get('label_attach_to_item'),
                    },
                    relatedPostId: this.itemId,
                    onSave: (files) => {
                        for (let file of files) {                      
                            let index = this.form.files.findIndex(newFile => newFile.id === file.id);
                            if ( index >= 0){
                                this.form.files[index] = file;
                            } else {
                                this.form.files.push( file );
                            }
                        }
                    }
                }
            );

        }
    },
    computed: {
        fieldList(){
            return this.getFields();
        }
    },
    created(){
        // Obtains collection ID
        this.cleanFields();
        this.collectionId = this.$route.params.collectionId;
        this.form.collectionId = this.collectionId;

        if (this.$route.fullPath.split("/").pop() == "new") {
            this.createNewItem();
        } else if (this.$route.fullPath.split("/").pop() == "edit") {
            this.isLoading = true;

            // Obtains current Item ID from URL
            this.itemId = this.$route.params.itemId;

            // Initializes Media Frames now that itemId exists
            this.initializeMediaFrames();

            this.fetchItem(this.itemId).then(res => {
                this.item = res;
                
                // Fill this.form data with current data.
                this.form.status = this.item.status;

                this.loadMetadata();
            });

            // Fetch current existing attachments
            this.fetchAttachments(this.itemId)
            .then(res => {
                this.form.files = res;
            });

        }
        
        
    }

}
</script>

<style lang="scss" scoped>

    .thumbnail-field {
        max-height: 128px;
        margin-bottom: 96px;
        margin-top: -20px;
        
        .content {
            padding: 10px;
            font-size: 0.8em;
        }
        img {
            position: absolute;
        }
.image-placeholder {
            position: absolute;
            margin-left: 10px;
            margin-right: 10px;
            bottom: 50%;
            font-size: 0.8rem;
            font-weight: bold;
            z-index: 99;
            text-align: center;
            color: gray;
        }
        #button-edit-thumbnail {

            border-radius: 100px !important;
            height: 40px !important;
            width: 40px !important;
            bottom: -20px;
            left: -20px;
            z-index: 99;
            
            .icon {
                display: inherit;
                padding: 0;
                margin: 0;
                margin-left: -8px;
                margin-top: 3px;
            }
        }
        .thumbnail-buttons-row {
            display: none;
        }
        &:hover {
             .thumbnail-buttons-row {
                display: inline-block;
                position: relative;
                top: -128px;
                background-color: rgba(255, 255, 255, 0.9);
                padding: 2px 8px;
                border-radius: 0px 0px 0px 4px;
                left: 88px;
            }
        }
    
    }


</style>


