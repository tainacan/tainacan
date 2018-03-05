<template>
    <div>
        <b-tag v-if="item != null && item != undefined" :type="'is-' + getStatusColor(item.status)" v-text="item.status"></b-tag>
        <form label-width="120px">
            <b-field :label="$i18n.get('label_status')">
                <b-select id="status-select"
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
                          drag-drop>
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
            <tainacan-form-item                  
                v-for="(field, index) in fieldList"
                v-bind:key="index"
                :field="field"></tainacan-form-item>           
            <button
                id="button-cancel-item-creation"
                class="button"
                type="button"
                @click="cancelBack">{{ $i18n.get('cancel') }}</button>
            <a
                id="button-submit-item-creation"
                @click="onSubmit"
                class="button is-success is-hovered">{{ $i18n.get('save') }}</a>
        </form>

        <b-loading :active.sync="isLoading" :canCancel="false">
    </div>
</template>

<script>
import { mapActions, mapGetters } from 'vuex'

export default {
    name: 'ItemEditionPage',
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
            'cleanFields'
        ]),
        ...mapGetters('item',[
            'getFields',
            'getItem'
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
                console.log(error);

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

                // Fill this.form data with current data.
                this.form.status = this.item.status;

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
            this.pageTitle = this.$i18n.get('title_create_item');
        } else if (this.$route.fullPath.split("/").pop() == "edit") {

            this.isLoading = true;
            this.pageTitle = this.$i18n.get('title_item_edition');

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


