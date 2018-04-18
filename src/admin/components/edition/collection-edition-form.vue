<template>
    <div 
            class="page-container" 
            :class="{'primary-page' : isNewCollection }">
        <b-tag 
                v-if="collection != null && collection != undefined" 
                :type="'is-' + getStatusColor(collection.status)" 
                v-text="collection.status"/>
        <form 
                v-if="collection != null && collection != undefined" 
                class="tainacan-form" 
                label-width="120px">
            
            <!-- Name -------------------------------- --> 
            <b-field 
                :addons="false"
                :label="$i18n.get('label_name')"
                :type="editFormErrors['name'] != undefined ? 'is-danger' : ''" 
                :message="editFormErrors['name'] != undefined ? editFormErrors['name'] : ''">
                <help-button 
                    :title="$i18n.getHelperTitle('collections', 'name')" 
                    :message="$i18n.getHelperMessage('collections', 'name')"/>
                <b-input
                    id="tainacan-text-name"
                    v-model="form.name"
                    @focus="clearErrors('name')"/>  
            </b-field>

            <!-- Thumbnail -------------------------------- --> 
            <b-field 
                :addons="false"
                :label="$i18n.get('label_image')">
                <div class="thumbnail-field">
                    <b-upload 
                        v-if="collection.featured_image == undefined || collection.featured_image == false"
                        v-model="thumbnail"
                        drag-drop
                        @input="uploadThumbnail($event)">
                        <div class="content has-text-centered">
                            <p>
                            <b-icon
                                icon="upload"/>
                            </p>
                            <p>{{ $i18n.get('instruction_image_upload_box') }}</p>
                        </div>
                    </b-upload>
                    <div v-else> 
                        <figure class="image is-128x128">
                            <img 
                                    :alt="$i18n.get('label_thumbnail')" 
                                    :src="collection.featured_image">
                        </figure>
                        <div class="thumbnail-buttons-row">
                            <b-upload 
                                    v-model="thumbnail"
                                    @input="uploadThumbnail($event)">
                                <a 
                                        id="button-edit" 
                                        :aria-label="$i18n.get('label_button_edit_thumb')"><b-icon icon="pencil"/></a>
                            </b-upload>
                            <a 
                                    id="button-delete" 
                                    :aria-label="$i18n.get('label_button_delete_thumb')" 
                                    @click="deleteThumbnail()"><b-icon icon="delete"/></a>
                        </div>
                    </div> 
                </div>
            </b-field>

            <!-- Cover  Image-------------------------------- --> 
            <b-field 
                :addons="false"
                :label="$i18n.get('label_cover_image')">
 
                <div class="thumbnail-field">
                    <b-upload 
                        v-if="collection.cover_image == undefined || collection.cover_image == false"
                        v-model="cover"
                        drag-drop
                        @input="uploadCover($event)">
                        <div class="content has-text-centered">
                            <p>
                            <b-icon
                                icon="upload"/>
                            </p>
                            <p>{{ $i18n.get('instruction_image_upload_box') }}</p>
                        </div>
                    </b-upload>
                    <div v-else> 
                        <figure class="image is-128x128">
                            <img 
                                    :alt="$i18n.get('label_cover_image')" 
                                    :src="collection.cover_image">
                        </figure>
                        <div class="thumbnail-buttons-row">
                            <b-upload 
                                    v-model="cover"
                                    @input="uploadCover($event)">
                                <a 
                                        id="button-edit" 
                                        :aria-label="$i18n.get('label_button_edit_thumb')"><b-icon icon="pencil"/></a>
                            </b-upload>
                            <a 
                                    id="button-delete" 
                                    :aria-label="$i18n.get('label_button_delete_thumb')" 
                                    @click="deleteThumbnail()"><b-icon icon="delete"/></a>
                        </div>
                    </div> 
                </div>
            </b-field>  
                  
            <!-- Description -------------------------------- --> 
            <b-field
                    :addons="false" 
                    :label="$i18n.get('label_description')"
                    :type="editFormErrors['description'] != undefined ? 'is-danger' : ''" 
                    :message="editFormErrors['description'] != undefined ? editFormErrors['description'] : ''">
                <help-button 
                        :title="$i18n.getHelperTitle('collections', 'description')" 
                        :message="$i18n.getHelperMessage('collections', 'description')"/>
                <b-input
                        id="tainacan-text-description"
                        type="textarea"
                        v-model="form.description"
                        @focus="clearErrors('description')"/>
            </b-field>

             <!-- Status -------------------------------- --> 
            <b-field
                    :addons="false" 
                    :label="$i18n.get('label_status')"
                    :type="editFormErrors['status'] != undefined ? 'is-danger' : ''" 
                    :message="editFormErrors['status'] != undefined ? editFormErrors['status'] : ''">
                <help-button 
                        :title="$i18n.getHelperTitle('collections', 'status')" 
                        :message="$i18n.getHelperMessage('collections', 'status')"/>
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

            <!-- Enable Cover Page -------------------------------- -->
            <div class="field">
                <b-checkbox
                        id="tainacan-checkbox-cover-page" 
                        size="is-small"
                        true-value="yes" 
                        false-value="no"
                        v-model="form.enable_cover_page">
                    {{ $i18n.get('label_enable_cover_page') }}
                </b-checkbox>
                 <help-button 
                        :title="$i18n.getHelperTitle('collections', 'enable_cover_page')" 
                        :message="$i18n.getHelperMessage('collections', 'enable_cover_page')"/>
            </div>

            <!-- Cover Page -------------------------------- --> 
            <b-field
                    v-show="form.enable_cover_page == 'yes'"
                    :addons="false" 
                    :label="$i18n.get('label_cover_page')"
                    :type="editFormErrors['cover_page_id'] != undefined ? 'is-danger' : ''" 
                    :message="editFormErrors['cover_page_id'] != undefined ? editFormErrors['cover_page_id'] : ''">
                <help-button 
                        :title="$i18n.getHelperTitle('collections', 'cover_page_id')" 
                        :message="$i18n.getHelperMessage('collections', 'cover_page_id')"/>
                <b-autocomplete
                        id="tainacan-text-cover-page"
                        :data="coverPages"
                        v-model="coverPageName"
                        @select="option => { form.cover_page_id = option.id; coverPageName = option.title.rendered}"
                        :loading="isFetchingPages"
                        @input="fecthCoverPages()"
                        @focus="clearErrors('cover_page_id')">
                    <template slot-scope="props">
                        {{ props.option.title.rendered }}
                    </template>
                    <template slot="empty">No esults found</template>
                </b-autocomplete>
            </b-field>

            <!-- Slug -------------------------------- --> 
            <b-field
                    :addons="false" 
                    :label="$i18n.get('label_slug')"
                    :type="editFormErrors['slug'] != undefined ? 'is-danger' : ''" 
                    :message="editFormErrors['slug'] != undefined ? editFormErrors['slug'] : ''">
                <help-button 
                        :title="$i18n.getHelperTitle('collections', 'slug')" 
                        :message="$i18n.getHelperMessage('collections', 'slug')"/>
                <b-input
                        id="tainacan-text-slug"
                        v-model="form.slug"
                        @focus="clearErrors('slug')"/>
            </b-field>

            <!-- Attachments -------------------------------- --> 
            <div class="field is-grouped form-submit">
                <div class="control">
                    <button
                            id="button-cancel-collection-creation"
                            class="button is-outlined"
                            type="button"
                            @click="cancelBack">{{ $i18n.get('cancel') }}</button>
                </div>
                <div class="control">
                    <button
                            id="button-submit-collection-creation"
                            @click.prevent="onSubmit"
                            class="button is-success">{{ $i18n.get('save') }}</button>
                </div>
            </div>
            <p class="help is-danger">{{ formErrorMessage }}</p> 
        </form>

        <b-loading 
                :active.sync="isLoading" 
                :can-cancel="false"/>
    </div>
</template>

<script>
import { mapActions } from 'vuex'

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
                slug: '',
                enable_cover_page: '',	
                featured_image: '',
                cover_image: '',
                files:[]
            },
            thumbnail: {},
            cover: {},
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
            isFetchingPages: false,
            coverPages: [],
            coverPageName: '',
            editFormErrors: {},
            formErrorMessage: '',
            isNewCollection: false
        }
    },
    methods: {
        ...mapActions('collection', [
            'sendCollection',
            'updateCollection',
            'fetchCollection',
            'sendAttachment',
            'updateThumbnail',
            'updateCover',
            'fetchPages',
            'fetchPage'
        ]),
        onSubmit() {
            // Puts loading on Draft Collection creation
            this.isLoading = true;

            let data = { 
                collection_id: this.collectionId, 
                name: this.form.name, 
                description: this.form.description,
                enable_cover_page: this.form.enable_cover_page, 
                slug: this.form.slug, 
                status: this.form.status
            };
            this.updateCollection(data).then(updatedCollection => {    
                
                this.collection = updatedCollection;

                // Fill this.form data with current data.
                this.form.name = this.collection.name;
                this.form.slug = this.collection.slug;
                this.form.description = this.collection.description;
                this.form.status = this.collection.status;
                this.form.enable_cover_page = this.collection.enable_cover_page;

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
                this.form.enable_cover_page = this.collection.enable_cover_page;
                this.form.slug = this.collection.slug;
                
                // Pre-fill status with publish to incentivate it
                this.form.status = 'publish';

                this.isLoading = false;
                
            })
            .catch(error => this.$console.error(error));
        },
        clearErrors(attribute) {
            this.editFormErrors[attribute] = undefined;
        },
        cancelBack(){
            this.$router.push(this.$routerHelper.getCollectionsPath());
        },
        uploadThumbnail($event) {

            this.sendAttachment({ collection_id: this.collectionId, file: $event[0] })
            .then((res) => {

                this.updateThumbnail({collectionId: this.collectionId, thumbnailId: res.id})
                .then((res) => {
                    this.collection.featured_image = res.featured_image;
                })
                .catch((error) => {
                    this.$console.error(error);
                });
            })
            .catch((error) => {
                this.$console.error(error);
            });
            
        },
        deleteThumbnail() {

            this.updateThumbnail({collectionId: this.collectionId, thumbnailId: 0})
            .then(() => {
                this.collection.featured_image = false;
            })
            .catch((error) => {
                this.$console.error(error);
            });    
        },
        uploadCover($event) {

            this.sendAttachment({ collection_id: this.collectionId, file: $event[0] })
            .then((res) => {

                this.updateCover({collectionId: this.collectionId, coverId: res.id})
                .then((res) => {
                    this.collection.cover_image = res.cover_image;
                })
                .catch((error) => {
                    this.$console.error(error);
                });
            })
            .catch((error) => {
                this.$console.error(error);
            });
            
        },
        deleteCover() {

            this.updateCover({collectionId: this.collectionId, coverId: 0})
            .then(() => {
                this.collection.cover_image = false;
            })
            .catch((error) => {
                this.$console.error(error);
            });    
        },
        fecthCoverPages() {
            this.isFetchingPages = true;
            this.fetchPages()
            .then((pages) => {
                this.coverPages = pages;
                this.isFetchingPages = false;
            })
            .catch((error) => {
                this.$console.error(error);
                this.isFetchingPages = false;
            }); 
        }
    },
    created(){

        if (this.$route.fullPath.split("/").pop() == "new") {
            this.createNewCollection();
            this.isNewCollection = true;
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
                this.form.slug = this.collection.slug;
                this.form.status = this.collection.status;
                this.form.enable_cover_page = this.collection.enable_cover_page;
                
                if (this.form.cover_page_id != undefined && this.form.cover_page_id != '') {
                    
                    this.isFetchingPages = true;
                    this.fetchPage(this.form.cover_page_id)
                    .then((page) => {
                        this.coverPageName = page.title.rendered;
                        this.isFetchingPages = false;
                    })
                    .catch((error) => {
                        this.$console.error(error);
                        this.isFetchingPages = false;
                    }); 
                } 

                this.isLoading = false; 
            });
        } 
    }

}
</script>

<style lang="scss" scoped>

    .thumbnail-field {
        width: 128px;
        height: 128px;
        max-width: 128px;
        max-height: 128px;
        
        .content {
            padding: 10px;
            font-size: 0.8em;
        }
        img {
            bottom: 0;
            position: absolute;
        }

        .thumbnail-buttons-row {
            display: none;
        }
        &:hover {
             .thumbnail-buttons-row {
                display: inline-block;
                position: relative;
                bottom: 31px;
                background-color: rgba(255,255,255,0.8);
                padding: 2px 8px;
                border-radius: 0px 4px 0px 0px;
            }
        }
    }

</style>


