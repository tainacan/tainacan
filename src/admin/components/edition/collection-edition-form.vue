<template>
    <div class="page-container">
        <b-tag v-if="collection != null && collection != undefined" :type="'is-' + getStatusColor(collection.status)" v-text="collection.status"></b-tag>
        <form class="tainacan-form" label-width="120px">
            <b-field 
                :label="$i18n.get('label_name')"
                :type="editFormErrors['name'] != undefined ? 'is-danger' : ''" 
                :message="editFormErrors['name'] != undefined ? editFormErrors['name'] : ''">
                <b-input
                    id="tainacan-text-name"
                    v-model="form.name"
                    @focus="clearErrors('name')">
                </b-input>
            </b-field>
            <b-field 
                    :label="$i18n.get('label_description')"
                    :type="editFormErrors['description'] != undefined ? 'is-danger' : ''" 
                    :message="editFormErrors['description'] != undefined ? editFormErrors['description'] : ''">
                <b-input
                        id="tainacan-text-description"
                        type="textarea"
                        v-model="form.description"
                        @focus="clearErrors('description')">
                </b-input>
            </b-field>
            <b-field 
                :label="$i18n.get('label_status')"
                :type="editFormErrors['status'] != undefined ? 'is-danger' : ''" 
                :message="editFormErrors['status'] != undefined ? editFormErrors['status'] : ''">
                <b-select
                        id="tainacan-select-status"
                        v-model="form.status"
                        @focus="clearErrors('status')"
                        :placeholder="$i18n.get('instruction_select_a_status')">
                    <option
                            v-for="statusOption in statusOptions"
                            :key="statusOption.value"
                            :value="statusOption.value"
                            :disabled="statusOption.disabled">{{ statusOption.label }}
                    </option>
                </b-select>
            </b-field>
            <b-field
                    :type="editFormErrors['image'] != undefined ? 'is-danger' : ''" 
                    :message="editFormErrors['image'] != undefined ? editFormErrors['image'] : ''"
                    :label="$i18n.get('label_image')">
                <b-upload v-model="form.files"
                          multiple
                          drag-drop
                          @focus="clearErrors('image')">
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
            <button
                id="button-cancel-collection-creation"
                class="button"
                type="button"
                @click="cancelBack">{{ $i18n.get('cancel') }}</button>
            <button
                id="button-submit-collection-creation"
                @click.prevent="onSubmit"
                class="button is-primary">{{ $i18n.get('save') }}</button>
            <p class="help is-danger">{{formErrorMessage}}</p>
        </form>

        <b-loading :active.sync="isLoading" :canCancel="false">
    </div>
</template>

<script>
import { mapActions, mapGetters } from 'vuex'

export default {
    name: 'CollectionEditionForm',
    data(){
        return {
            collectionId: Number,
            collection: null,
            isLoading: false,
            form: {
                name: '',
                status: '',
                description: '',
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
            }],
            editFormErrors: {},
            formErrorMessage: '',
        }
    },
    methods: {
        ...mapActions('collection', [
            'sendCollection',
            'updateCollection',
            'fetchCollection'
        ]),
        ...mapGetters('collection',[
            'getCollection'
        ]),
        onSubmit() {
            // Puts loading on Draft Collection creation
            this.isLoading = true;

            let data = {collection_id: this.collectionId, name: this.form.name, description: this.form.description, status: this.form.status};
            this.updateCollection(data).then(updatedCollection => {    
                
                this.collection = updatedCollection;

                // Fill this.form data with current data.
                this.form.name = this.collection.name;
                this.form.description = this.collection.description;
                this.form.status = this.collection.status;

                this.isLoading = false;
                this.formErrorMessage = '';
                this.editFormErrors = {};

                this.$router.push(this.$routerHelper.getCollectionPath(this.collectionId));
            })
            .catch((errors) => {
                for (let error of errors.errors) {     
                    for (let attribute of Object.keys(error))
                        this.editFormErrors[attribute] = error[attribute];
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
        createNewCollection() {
            // Puts loading on Draft Collection creation
            this.isLoading = true;

            // Creates draft Collection
            let data = { name: '', description: '', status: 'auto-draft'};
            this.sendCollection(data).then(res => {

                this.collectionId = res.id;
                this.collection = res;

                // Fill this.form data with current data.
                this.form.name = this.collection.name;
                this.form.description = this.collection.description;
                
                // Pre-fill status with publish to incentivate it
                this.form.status = 'publish';

                this.isLoading = false;
                
            })
            .catch(error => console.log(error));
        },
        clearErrors(attribute) {
            this.editFormErrors[attribute] = undefined;
        },
        cancelBack(){
            this.$router.push(this.$routerHelper.getCollectionsPath());
        }
    },
    created(){

        if (this.$route.fullPath.split("/").pop() == "new") {
            this.createNewCollection();
        } else if (this.$route.fullPath.split("/").pop() == "edit") {

            this.isLoading = true;

            // Obtains current Collection ID from URL
            this.pathArray = this.$route.fullPath.split("/").reverse(); 
            this.collectionId = this.pathArray[1];

            this.fetchCollection(this.collectionId).then(res => {
                this.collection = res;

                // Fill this.form data with current data.
                this.form.name = this.collection.name;
                this.form.description = this.collection.description;
                this.form.status = this.collection.status;

                this.isLoading = false; 
            });
        } 
    }

}
</script>

<style scoped>

</style>


