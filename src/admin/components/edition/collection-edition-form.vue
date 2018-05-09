<template>
    <div 
            class="page-container"
            :class="{'primary-page' : isNewCollection }">
        <tainacan-title />
        <form 
                v-if="collection != null && collection != undefined" 
                class="tainacan-form" 
                label-width="120px">

            <div class="columns is-variable is-8">
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
                                        v-if="collection.thumbnail == undefined || collection.thumbnail == false"
                                        class="image-placeholder">{{ $i18n.get('label_empty_thumbnail') }}</span>
                                <img
                                        id="thumbail-image"  
                                        :alt="$i18n.get('label_thumbnail')" 
                                        :src="(collection.thumbnail == undefined || collection.thumbnail == false) ? thumbPlaceholderPath : collection.thumbnail">
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
                    
                    <!-- Header Page -------------------------------- --> 
                    <b-field 
                        :addons="false"
                        :label="$i18n.get('label_header_image')">
                        <div class="thumbnail-field">                    
                            <a 
                                    class="button is-rounred is-secondary"
                                    id="button-edit-header-image" 
                                    :aria-label="$i18n.get('label_button_edit_header_image')"
                                    @click="headerImageMediaFrame.openFrame($event)">
                                <b-icon icon="pencil" />
                            </a>
                            <figure class="image is-128x128">
                                <span 
                                        v-if="collection.header_image == undefined || collection.header_image == false"
                                        class="image-placeholder">{{ $i18n.get('label_empty_header_image') }}</span>
                                <img  
                                        :alt="$i18n.get('label_thumbnail')" 
                                        :src="(collection.header_image == undefined || collection.header_image == false) ? headerPlaceholderPath : collection.header_image">
                            </figure>
                            <div class="thumbnail-buttons-row">
                                <a 
                                        id="button-delete" 
                                        :aria-label="$i18n.get('label_button_delete_thumb')" 
                                        @click="deleteHeaderImage()">
                                    <b-icon icon="delete" />
                                </a>
                            </div>     
                        </div>
                    </b-field>
                </div>
                <div class="column">
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
                            :class="{'has-content': form.name != undefined && form.name != ''}"
                            id="tainacan-text-name"
                            v-model="form.name"
                            @focus="clearErrors('name')"/>  
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
                                :class="{'has-content': form.description != undefined && form.description != ''}"
                                id="tainacan-text-description"
                                type="textarea"
                                v-model="form.description"
                                @focus="clearErrors('description')"/>
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
                                :placeholder="$i18n.get('instruction_cover_page')"
                                :data="coverPages"
                                v-model="coverPageTitle"
                                @select="onSelectCoverPage($event)"
                                :loading="isFetchingPages"
                                @input="fecthCoverPages($event)"
                                @focus="clearErrors('cover_page_id')"
                                v-if="coverPage == undefined || coverPage.title == undefined">
                            <template slot-scope="props">
                                {{ props.option.title.rendered }}
                            </template>
                            <template slot="empty">{{ $i18n.get('info_no_page_found') }}</template>
                        </b-autocomplete>
  
                        <div 
                                v-if="coverPage != undefined && coverPage.title != undefined"
                                class="control selected-cover-page">
                            <span v-html="coverPage.title.rendered" />
                            <span class="selected-cover-page-control">
                                <a 
                                        target="blank" 
                                        :href="coverPageEditPath">{{ $i18n.get('edit') }}</a>
                                &nbsp;&nbsp;
                                <a 
                                        target="_blank" 
                                        :href="coverPage.link">{{ $i18n.get('see') }}</a>
                                &nbsp;&nbsp;
                                <button  
                                        class="button is-secondary is-small"
                                        @click.prevent="removeCoverPage()">{{ $i18n.get('remove') }}</button>
                            </span>
                        </div>
                    </b-field>

                     <!-- Moderators List -------------------------------- --> 
                    <b-field
                            :addons="false" 
                            :label="$i18n.get('label_moderators')"
                            :type="editFormErrors['moderators'] != undefined ? 'is-danger' : ''" 
                            :message="editFormErrors['moderators'] != undefined ? editFormErrors['moderators'] : ''">
                        <help-button 
                                :title="$i18n.getHelperTitle('collections', 'moderators_ids')" 
                                :message="$i18n.getHelperMessage('collections', 'moderators_ids')"/>
                        <b-autocomplete
                                id="tainacan-text-moderators-input"
                                :placeholder="$i18n.get('instruction_moderators')"
                                :data="users"
                                @select="onAddModerator($event)"
                                :loading="isFetchingModerators"
                                @input="fecthModerators($event)"
                                @focus="clearErrors('moderators')">
                            <template slot-scope="props">
                                {{ props.option.name }}
                            </template>
                            <template slot="empty">{{ $i18n.get('info_no_user_found') }}</template>
                        </b-autocomplete>
                        <ul
                                class="selected-list-box"
                                v-if="moderators != undefined && moderators.length > 0">
                            <li
                                    :key="index"
                                    v-for="(moderator, index) of moderators">
                                <b-tag
                                        attached
                                        closable
                                        @close="removeModerator(index)">
                                   {{ moderator.name }}
                                </b-tag>
                            </li>
                        </ul>
                        <div 
                                class="moderators-empty-list"
                                v-else>
                            {{ $i18n.get('info_no_moderator_on_collection') }}
                        </div>
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
                                :class="{'has-content': form.slug != undefined && form.slug != ''}"
                                id="tainacan-text-slug"
                                v-model="form.slug"
                                @focus="clearErrors('slug')"/>
                    </b-field>

                    <!-- Parent Collection -------------------------------- --> 
                    <b-field
                            :addons="false" 
                            :label="$i18n.get('label_parent_collection')"
                            :type="editFormErrors['parent'] != undefined ? 'is-danger' : ''" 
                            :message="editFormErrors['parent'] != undefined ? editFormErrors['parent'] : ''">
                        <help-button 
                                :title="$i18n.getHelperTitle('collections', 'parent')" 
                                :message="$i18n.getHelperMessage('collections', 'parent')"/>
                        <b-select
                                id="tainacan-select-parent"
                                v-model="form.parent"
                                @focus="clearErrors('parent')"
                                :loading="isFetchingCollections"
                                :placeholder="$i18n.get('instruction_select_a_parent_collection')">
                            <option value="0">{{ $i18n.get('label_no_parent_collection') }}</option>
                            <option
                                    v-if="collection.id != anotherCollection.id"
                                    v-for="anotherCollection of collections"
                                    :key="anotherCollection.id"
                                    :value="anotherCollection.id">{{ anotherCollection.name }}
                            </option>
                        </b-select>
                    </b-field>

                </div>
            </div>

            <!-- Form submit -------------------------------- --> 
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
import { mapActions } from 'vuex';
import wpMediaFrames from '../../js/wp-media-frames';

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
                thumbnail: '',
                header_image: '',
                files:[],
                moderators_ids: []
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
            coverPage: '',
            coverPageTitle: '',
            coverPageEditPath: '',
            editFormErrors: {},
            formErrorMessage: '',
            isNewCollection: false,
            thumbPlaceholderPath: tainacan_plugin.base_url + '/admin/images/placeholder_square.png',
            headerPlaceholderPath: tainacan_plugin.base_url + '/admin/images/placeholder_rectangle.png',
            isFetchingModerators: false,
            users: [],
            moderators: [],
            collections: [],
            isFetchingCollections: true,
            thumbnailMediaFrame: undefined,
            headerImageMediaFrame: undefined
        }
    },
    methods: {
        ...mapActions('collection', [
            'sendCollection',
            'updateCollection',
            'fetchCollection',
            'sendAttachment',
            'updateThumbnail',
            'updateHeaderImage',
            'fetchPages',
            'fetchPage',
            'fetchUsers',
            'fetchCollectionsForParent'
        ]),
        ...mapActions('fields', [
            'fetchFields'
        ]),
        onSubmit() {
            this.isLoading = true;

            this.form.moderators_ids = [];
            for (let moderator of this.moderators)
                this.form.moderators_ids.push(moderator.id);

            let data = { 
                collection_id: this.collectionId, 
                name: this.form.name, 
                description: this.form.description,
                enable_cover_page: this.form.enable_cover_page, 
                cover_page_id: this.form.cover_page_id,
                slug: this.form.slug, 
                status: this.form.status,
                moderators_ids: this.form.moderators_ids,
                parent: this.form.parent
            };
            this.updateCollection(data).then(updatedCollection => {    
                
                this.collection = updatedCollection;

                // Fill this.form data with current data.
                this.form.name = this.collection.name;
                this.form.slug = this.collection.slug;
                this.form.description = this.collection.description;
                this.form.status = this.collection.status;
                this.form.cover_page_id = this.collection.cover_page_id;
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
        createNewCollection() {
            // Puts loading on Draft Collection creation
            this.isLoading = true;

            // Creates draft Collection
            let data = { name: '', description: '', status: 'auto-draft'};
            this.sendCollection(data).then(res => {

                this.collectionId = res.id;
                this.collection = res;

                // Initializes Media Frames now that collectonId exists
                this.initializeMediaFrames();

                // Fill this.form data with current data.
                this.form.name = this.collection.name;
                this.form.description = this.collection.description;
                this.form.enable_cover_page = this.collection.enable_cover_page;
                this.form.cover_page_id = this.collection.cover_page_id;
                this.form.slug = this.collection.slug;
                this.form.parent = this.collection.parent;
                this.moderators = [];
                
                // Pre-fill status with publish to incentivate it
                this.form.status = 'publish';

                // Generates options for parent collection
                this.isFetchingCollections = true;
                this.fetchCollectionsForParent()
                .then((collections) => {
                    this.collections = collections;
                    this.isFetchingCollections = false;
                })
                .catch((error) => {
                    this.$console.error(error);
                    this.isFetchingCollections = false;
                }); 

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
        fecthCoverPages(search) {
            this.isFetchingPages = true;
            this.fetchPages(search)
                .then((pages) => {
                    this.coverPages = pages;
                    this.isFetchingPages = false;
                })
                .catch((error) => {
                    this.$console.error(error);
                    this.isFetchingPages = false;
                });
        },
        onSelectCoverPage(selectedPage) { 
            this.form.cover_page_id = selectedPage.id; 
            this.coverPage = selectedPage;
            this.coverPageTitle = this.coverPage.title.rendered;
            this.coverPageEditPath = tainacan_plugin.admin_url + '/post.php?post=' + selectedPage.id + '&action=edit';
        },
        fecthModerators(search) {
            this.isFetchingModerators = true;

            let exceptions = [];
            for (let user of this.moderators)
                exceptions.push(parseInt(user.id));
            exceptions.push(this.collection.author_id);

            this.fetchUsers({ search: search, exceptions: exceptions})
                .then((users) => {
                    this.users = users;
                    this.isFetchingModerators = false;
                })
                .catch((error) => {
                    this.$console.error(error);
                    this.isFetchingPages = false;
                });
        },
        onAddModerator(user) { 
            this.moderators.push({'id': user.id, 'name': user.name}); 
        },
        removeModerator(moderatorIndex) { 
            this.moderators.splice(moderatorIndex, 1);
        },
        removeCoverPage() {
            this.coverPage = {};
            this.coverPageTitle = '';
            this.form.cover_page_id = '';
        },
        deleteThumbnail() {

            this.updateThumbnail({collectionId: this.collectionId, thumbnailId: 0})
            .then(() => {
                this.collection.thumbnail = false;
            })
            .catch((error) => {
                this.$console.error(error);
            });    
        },
        deleteHeaderImage() {

            this.updateHeaderImage({collectionId: this.collectionId, headerImageId: 0})
            .then(() => {
                this.collection.header_image = false;
            })
            .catch((error) => {
                this.$console.error(error);
            });    
        },
        initializeMediaFrames() {

            this.thumbnailMediaFrame = new wpMediaFrames.thumbnailControl(
                'my-thumbnail-media-frame', {
                    button_labels: {
                        frame_title: this.$i18n.get('instruction_select_collection_thumbnail'),
                    },
                    relatedPostId: this.collectionId,
                    onSave: (mediaId) => {
                        this.updateThumbnail({collectionId: this.collectionId, thumbnailId: mediaId})
                        .then((res) => {
                            this.collection.thumbnail = res.thumbnail;
                        })
                        .catch(error => this.$console.error(error));
                    }
                }
            );

            this.headerImageMediaFrame = new wpMediaFrames.headerImageControl(
                'my-header-image-media-frame', {
                    button_labels: {
                        frame_title: this.$i18n.get('instruction_select_collection_header_image'),
                    },
                    relatedPostId: this.collectionId,
                    onSave: (media) => {
                        this.updateHeaderImage({collectionId: this.collectionId, headerImageId: media.id})
                        .then((res) => {
                            this.collection.header_image = res.header_image;
                        })
                        .catch(error => this.$console.error(error));
                    }
                }
            );
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

                // Initializes Media Frames now that collectonId exists
                this.initializeMediaFrames();

                // Fill this.form data with current data.
                this.form.name = this.collection.name;
                this.form.description = this.collection.description;
                this.form.slug = this.collection.slug;
                this.form.status = this.collection.status;
                this.form.enable_cover_page = this.collection.enable_cover_page;
                this.form.cover_page_id = this.collection.cover_page_id;
                this.form.parent = this.collection.parent;

                this.moderators = JSON.parse(JSON.stringify(this.collection.moderators));
                 
                // Generates CoverPage from current cover_page_id info
                if (this.form.cover_page_id != undefined && this.form.cover_page_id != '') {
                    
                    this.isFetchingPages = true;
                    
                    this.fetchPage(this.form.cover_page_id)
                    .then((page) => {
                        this.coverPage = page;
                        this.coverPageTitle = this.coverPage.title.rendered;
                        this.coverPageEditPath = tainacan_plugin.admin_url + '/post.php?post=' + page.id + '&action=edit';
                        this.isFetchingPages = false;
                    })
                    .catch((error) => {
                        this.$console.error(error);
                        this.isFetchingPages = false;
                    }); 
                }

                // Generates options for parent collection
                this.isFetchingCollections = true;
                this.fetchCollectionsForParent()
                .then((collections) => {
                    this.collections = collections;
                    this.isFetchingCollections = false;
                })
                .catch((error) => {
                    this.$console.error(error);
                    this.isFetchingCollections = false;
                }); 

                this.isLoading = false; 
            });
        }
    }

}
</script>

<style lang="scss" scoped>

    @import "../../scss/_variables.scss";

    .tainacan-form>.columns>.column {
        overflow: auto;
        .field {
            position: relative;
        }
    }
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
        #button-edit-thumbnail, #button-edit-header-image {

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
                margin-top: 1px;
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
    .selected-cover-page {
        background-color: $tainacan-input-background;
        padding: 8px;
        font-size: .85rem;
        .span { vertical-align: middle;}

        .selected-cover-page-control {
            float: right;
        }

    }
    .moderators-empty-list { 
        color: gray;
        font-size: 0.85rem;
     }

</style>


