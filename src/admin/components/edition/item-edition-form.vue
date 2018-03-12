<template>
    <div class="page-container">
        <b-tag v-if="item != null && item != undefined" :type="'is-' + getStatusColor(item.status)" v-text="item.status"></b-tag>
        <form class="tainacan-form" label-width="120px">
            <b-field :label="$i18n.get('label_status')">
                <b-select 
                        id="status-select"
                        v-model="form.status"
                        :placeholder="$i18n.get('instruction_select_a_status')">
                    <option
                            id="{{'status-option-' + statusOption.value}}"
                            v-for="statusOption in statusOptions"
                            :key="statusOption.value"
                            :value="statusOption.value"
                            :disabled="statusOption.disabled">{{ statusOption.label }}
                    </option>
                </b-select>
            </b-field>
            <b-field
                    :label="$i18n.get('label_image')">
                <b-upload v-model="form.files"
                          multiple
                          drag-drop
                          @input="uploadAttachment($event)">
                    <section class="section">
                        <div class="content has-text-centered">
                            <p>
                                <b-icon
                                        icon="upload"
                                        size="is-large">
                                </b-icon>
                            </p>
                            <p>{{ $i18n.get('instruction_image_upload_box') }}</p>
                        </div>
                    </section>
                </b-upload>
            </b-field>    
            <div class="uploaded-files">
                <div v-for="(file, index) in form.files"
                    :key="index">
                    <span class="tag is-primary">
                        {{ file.name }}
                        <button class="delete is-small"
                            type="button"
                            @click="deleteFile(index)">
                        </button>
                    </span>
                    <!-- <progress class="progress is-secondary" value="15" max="100">30%</progress> -->
                </div>
            </div>    
            <tainacan-form-item                 
                v-for="(field, index) in fieldList"
                v-bind:key="index"
                :field="field"></tainacan-form-item>    
            <div>
                <p v-for="(attachment, index) of attachmentsList" :key="index">{{attachment}}</p>
            </div>       
            <button
                id="button-cancel-item-creation"
                class="button"
                type="button"
                @click="cancelBack">{{ $i18n.get('cancel') }}</button>
            <button
                id="button-submit-item-creation"
                @click.prevent="onSubmit"
                class="button is-primary" :disabled="formHasErrors">{{ $i18n.get('save') }}</button> 
        </form>

        <b-loading :active.sync="isLoading" :canCancel="false">
    </div>
</template>

<script>
import { mapActions, mapGetters } from 'vuex';
import { eventBus } from '../../../js/event-bus-web-components.js'

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
                files:[]
            },
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
            }]
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
            'fetchAttachments',
            'sendAttachment'
        ]),
        ...mapGetters('item',[
            'getFields',
            'getItem',
            'getAttachments'
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
            }).catch(error => {
                
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

                // Pre-fill status with publish to incentivate it
                this.form.status = 'publish';

                this.loadMetadata();
                
            })
            .catch(error => console.log(error));
        },
        loadMetadata() { 
            // Obtains Item Field
            this.fetchFields(this.itemId).then(res => {
                this.isLoading = false;
            });
        }, 
        cancelBack(){
            this.$router.push(this.$routerHelper.getCollectionPath(this.collectionId));
        },
        uploadAttachment($event) {
            
            for (let file of $event) {
                this.sendAttachment({ item_id: this.itemId, file: file })
                .then((res) => {
                    
                })
                .catch((error) => {
                    console.log(error);
                });
            }
        },
        deleteFile(index) {

        }
    },
    computed: {
        fieldList(){
            return this.getFields();
        },
        formHasErrors(){
            return eventBus.errors.length > 0;
        },
        attachmentsList(){
            return this.getAttachments();
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

            this.fetchItem(this.itemId).then(res => {
                this.item = res;
                
                // Fill this.form data with current data.
                this.form.status = this.item.status;

                this.loadMetadata();
            });
        }
        
        
    }

}
</script>

<style scoped>

</style>


