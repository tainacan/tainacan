<template>
    <div 
            class="page-container"
            :class="{'repository-level-page' : isNewCollection }">
        <tainacan-title />
        <form 
                v-if="collection != null && collection != undefined" 
                class="tainacan-form" 
                label-width="120px">

            <!-- Header Page -------------------------------- --> 
            <b-field :addons="false">
                <label class="label">{{ $i18n.get('label_header_image') }}</label>
                <div class="header-field">
                    <figure class="image">
                        <span 
                                v-if="collection.header_image == undefined || collection.header_image == false"
                                class="image-placeholder">{{ $i18n.get('label_empty_header_image') }}</span>
                        <img  
                                :alt="$i18n.get('label_thumbnail')" 
                                :src="(collection.header_image == undefined || collection.header_image == false) ? headerPlaceholderPath : collection.header_image">
                    </figure>
                    <div class="header-buttons-row">
                        <a 
                                class="button is-rounded is-secondary"
                                id="button-edit-header-image" 
                                :aria-label="$i18n.get('label_button_edit_header_image')"
                                @click="headerImageMediaFrame.openFrame($event)">
                            <b-icon 
                                    size="is-small"
                                    icon="pencil" />
                        </a>
                        <a 
                                class="button is-rounded is-secondary"
                                id="button-delete-header-image" 
                                :aria-label="$i18n.get('label_button_delete_thumb')" 
                                @click="deleteHeaderImage()">
                            <b-icon 
                                    size="is-small"
                                    icon="delete" />
                        </a>
                    </div>     
                </div>
            </b-field>

            <div class="columns">
                <div class="column is-4">

                    <!-- Thumbnail -------------------------------- --> 
                    <b-field :addons="false">
                        <label class="label">{{ $i18n.get('label_thumbnail') }}</label>
                        <div class="thumbnail-field">
                            <file-item
                                    v-if="collection.thumbnail != undefined && ((collection.thumbnail.tainacan_medium != undefined && collection.thumbnail.tainacan_medium != false) || (collection.thumbnail.medium != undefined && collection.thumbnail.medium != false))"
                                    :show-name="false"
                                    :size="178"
                                    :file="{ 
                                        media_type: 'image', 
                                        guid: { rendered: collection.thumbnail.tainacan_medium ? collection.thumbnail.tainacan_medium : collection.thumbnail.medium },
                                        title: { rendered: $i18n.get('label_thumbnail')},
                                        description: { rendered: `<img alt='Thumbnail' src='` + collection.thumbnail.full + `'/>` }}"/>
                          <figure 
                                    v-if="collection.thumbnail == undefined || ((collection.thumbnail.medium == undefined || collection.thumbnail.medium == false) && (collection.thumbnail.tainacan_medium == undefined || collection.thumbnail.tainacan_medium == false))"
                                    class="image">
                                <span class="image-placeholder">{{ $i18n.get('label_empty_thumbnail') }}</span>
                                <img  
                                        :alt="$i18n.get('label_thumbnail')" 
                                        :src="thumbPlaceholderPath">
                            </figure>
                            <div class="thumbnail-buttons-row">
                                <a 
                                        class="button is-rounded is-secondary"
                                        id="button-edit-thumbnail" 
                                        :aria-label="$i18n.get('label_button_edit_thumb')"
                                        @click.prevent="thumbnailMediaFrame.openFrame($event)">
                                    <b-icon 
                                            size="is-small"
                                            icon="pencil" />
                                </a>
                                <a 
                                        class="button is-rounded is-secondary"
                                        id="button-delete-header-image" 
                                        :aria-label="$i18n.get('label_button_delete_thumb')" 
                                        @click="deleteThumbnail()">
                                    <b-icon 
                                            size="is-small"
                                            icon="delete" />
                                </a>
                            </div>
                        </div>
                    </b-field>

                    <!-- Cover Page -------------------------------- --> 
                    <b-field
                            :addons="false" 
                            :label="$i18n.get('label_cover_page')"
                            :type="editFormErrors['cover_page_id'] != undefined ? 'is-danger' : ''" 
                            :message="editFormErrors['cover_page_id'] != undefined ? editFormErrors['cover_page_id'] : ''">
                        <b-switch
                                id="tainacan-checkbox-cover-page" 
                                size="is-small"
                                true-value="yes" 
                                false-value="no"
                                v-model="form.enable_cover_page" />
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
                                v-if="coverPage == undefined || coverPage.title == undefined"
                                :disabled="form.enable_cover_page != 'yes'">
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
                                        target="_blank"
                                        @click.prevent="removeCoverPage()">
                                    <b-icon 
                                            size="is-small"
                                            icon="close"/>
                                </a>
                            </span>
                        </div>
                        <span 
                                :class="{'disabled': form.enable_cover_page != 'yes' || coverPage == undefined || coverPage.title == undefined}"
                                class="selected-cover-page-buttons">
                            <a 
                                    target="_blank" 
                                    :href="coverPage.link">
                                <b-icon 
                                        size="is-small"
                                        icon="eye"/>
                            </a>
                            &nbsp;&nbsp;
                            <a 
                                    target="blank" 
                                    :href="coverPageEditPath">
                                <b-icon 
                                        size="is-small"
                                        icon="pencil"/>
                            </a>
                        </span>
                        <br>
                        <a
                                class="is-inline add-link"   
                                :class="{'disabled': form.enable_cover_page != 'yes'}"
                                target="_blank"  
                                :href="newPagePath">
                            <b-icon
                                    icon="plus-circle"
                                    size="is-small"
                                    type="is-secondary"/>
                                {{ $i18n.get('label_create_new_page') }}</a>                        
                    </b-field>

                    <!-- Enabled View Modes ------------------------------- --> 
                    <div class="field">
                        <label class="label">{{ $i18n.get('label_view_modes_available') }}</label>
                        <help-button 
                                    :title="$i18n.getHelperTitle('collections', 'enabled_view_modes')" 
                                    :message="$i18n.getHelperMessage('collections', 'enabled_view_modes')"/>
                        <div class="control">
                            <b-dropdown
                                    class="two-columns-dropdown"
                                    ref="enabledViewModesDropdown"
                                    :mobile-modal="true"
                                    :disabled="Object.keys(registeredViewModes).length < 0">
                                <button
                                        class="button is-white"
                                        slot="trigger"
                                        position="is-top-right"
                                        type="button">
                                    <span>{{ $i18n.get('label_enabled_view_modes') }}</span>
                                    <b-icon icon="menu-down"/>
                                </button>
                                <b-dropdown-item
                                        v-for="(viewMode, index) in Object.keys(registeredViewModes)"
                                        :key="index"
                                        class="control"
                                        custom>
                                    <b-checkbox
                                            v-if="registeredViewModes[viewMode] != undefined"
                                            @input="updateViewModeslist(viewMode)"
                                            :value="checkIfViewModeEnabled(viewMode)">
                                        {{ registeredViewModes[viewMode].label }}
                                    </b-checkbox>
                                </b-dropdown-item>   
                            </b-dropdown>
                        </div>
                    </div>

                    <!-- Default View Mode -------------------------------- --> 
                    <b-field
                            v-if="form.enabled_view_modes.length > 0"
                            :addons="false" 
                            :label="$i18n.get('label_default_view_mode')"
                            :type="editFormErrors['default_view_mode'] != undefined ? 'is-danger' : ''" 
                            :message="editFormErrors['default_view_mode'] != undefined ? editFormErrors['default_view_mode'] : ''">
                        <help-button 
                                :title="$i18n.getHelperTitle('collections', 'default_view_mode')" 
                                :message="$i18n.getHelperMessage('collections', 'default_view_mode')"/>
                        <b-select
                                expanded
                                id="tainacan-select-default_view_mode"
                                v-model="form.default_view_mode"
                                @focus="clearErrors('default_view_mode')">
                            <option
                                    v-for="(viewMode, index) of form.enabled_view_modes"
                                    v-if="registeredViewModes[viewMode] != undefined"
                                    :key="index"
                                    :value="viewMode">{{ registeredViewModes[viewMode].label }}
                            </option>
                        </b-select>
                    </b-field>
                    <!-- Comment Status ------------------------ --> 
                    <b-field
                            :addons="false" 
                            :label="$i18n.get('label_comment_status')">
                        <b-switch
                                id="tainacan-checkbox-comment-status" 
                                size="is-small"
                                true-value="open" 
                                false-value="closed"
                                v-model="form.comment_status" />
                        <help-button 
                                :title="$i18n.getHelperTitle('collections', 'comment_status')" 
                                :message="$i18n.getHelperMessage('collections', 'comment_status')"/>
                    </b-field>
                </div>
                <div class="column is-1" />
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
                        <div class="status-radios">
                            <b-radio
                                    v-model="form.status"
                                    v-for="statusOption in statusOptions"
                                    :key="statusOption.value"
                                    :native-value="statusOption.value">
                                <span class="icon has-text-gray">
                                    <i 
                                        class="mdi mdi-18px"
                                        :class="'mdi-' + getStatusIcon(statusOption.value)"/>
                                </span>
                                {{ statusOption.label }}
                            </b-radio>
                        </div>
                    </b-field>
            
                    <!-- Name -------------------------------- --> 
                    <b-field 
                            :addons="false"
                            :label="$i18n.get('label_name')"
                            :type="editFormErrors['name'] != undefined ? 'is-danger' : ''" 
                            :message="editFormErrors['name'] != undefined ? editFormErrors['name'] : ''">
                        <span class="required-metadatum-asterisk">*</span>
                        <help-button 
                                :title="$i18n.getHelperTitle('collections', 'name')" 
                                :message="$i18n.getHelperMessage('collections', 'name')"/>
                        <b-input
                                id="tainacan-text-name"
                                v-model="form.name"
                                @blur="updateSlug"
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
                                id="tainacan-text-description"
                                type="textarea"
                                v-model="form.description"
                                @focus="clearErrors('description')"/>
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
                                expanded
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
                                id="tainacan-text-slug"
                                @input="updateSlug"
                                v-model="form.slug"
                                @focus="clearErrors('slug')"/>
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
import FileItem from '../other/file-item.vue';
import { wpAjax } from '../../js/mixins';

export default {
    name: 'CollectionEditionForm',
    mixins: [ wpAjax ],
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
                moderators_ids: [],
                enabled_view_modes: [],
                default_view_mode: [],
                comment_status: ''
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
            isMapped: false,
            mapper: false,
            thumbPlaceholderPath: tainacan_plugin.base_url + '/admin/images/placeholder_square.png',
            headerPlaceholderPath: tainacan_plugin.base_url + '/admin/images/placeholder_rectangle.png',
            isFetchingModerators: false,
            users: [],
            moderators: [],
            collections: [],
            isFetchingCollections: true,
            thumbnailMediaFrame: undefined,
            headerImageMediaFrame: undefined,
            registeredViewModes: tainacan_plugin.registered_view_modes,
            viewModesList: [],
            fromImporter: '',
            newPagePath: tainacan_plugin.admin_url + 'post-new.php?post_type=page',
            isUpdatingSlug: false,
        }
    },
    components: {
        FileItem
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
        ...mapActions('metadata', [
            'fetchMetadata'
        ]),
        updateSlug: _.debounce(function() {
            if(!this.form.name || this.form.name.length <= 0){
                return;
            }

            this.isUpdatingSlug = true;

            this.getSamplePermalink(this.collectionId, this.form.name, this.form.slug)
                .then((res) => {
                    this.form.slug = res.data.slug;

                    this.isUpdatingSlug = false;
                    this.formErrorMessage = '';
                    this.editFormErrors = {};
                })
                .catch(errors => {
                    this.$console.error(errors);

                    this.isUpdatingSlug = false;
                });
        }, 500),
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
                parent: this.form.parent,
                enabled_view_modes: this.form.enabled_view_modes,
                default_view_mode: this.form.default_view_mode,
                comment_status: this.form.comment_status
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
                this.form.enabled_view_modes = this.collection.enabled_view_modes;
                this.form.default_view_mode = this.collection.default_view_mode;
                this.form.comment_status = this.collection.comment_status;

                this.isLoading = false;
                this.formErrorMessage = '';
                this.editFormErrors = {};

                if (this.fromImporter)
                    this.$router.go(-1);
                else
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
            let data = { name: '', description: '', status: 'auto-draft', mapper: (this.isMapped && this.mapper != false ? this.mapper : false ) };
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
                this.form.default_view_mode = this.collection.default_view_mode;
                this.form.enabled_view_modes = [];
                this.moderators = [];
                this.form.comment_status = this.collection.comment_status;

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
            if (this.fromImporter)
                this.$router.go(-1);
            else
                this.$router.push(this.$routerHelper.getCollectionsPath());
        },
        updateViewModeslist(viewMode) {
        
            let index = this.form.enabled_view_modes.findIndex(aViewMode => aViewMode == viewMode);
            if (index > -1)
                this.form.enabled_view_modes.splice(index, 1);
            else    
                this.form.enabled_view_modes.push(viewMode);
        },
        checkIfViewModeEnabled(viewMode) {
            let index = this.form.enabled_view_modes.findIndex(aViewMode => aViewMode == viewMode);
            return index > -1;
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
            this.coverPageEditPath = tainacan_plugin.admin_url + 'post.php?post=' + selectedPage.id + '&action=edit';
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
        },
        getStatusIcon(status) {
            switch(status) {
                case 'publish': return 'earth';
                case 'private': return 'lock';
                case 'draft': return 'clipboard-text';
                case 'trash': return 'delete';
                default: return 'file';
            }
        }
    },
    created(){

        if (this.$route.query.fromImporter != undefined) 
            this.fromImporter = this.$route.query.fromImporter;

        if (this.$route.path.split("/").pop() == "new") {
            this.createNewCollection();
            this.isNewCollection = true;
        } else if (this.$route.path.split("/").pop() == "settings") {

            this.isLoading = true;

            // Obtains current Collection ID from URL
            this.pathArray = this.$route.path.split("/").reverse(); 
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
                this.form.default_view_mode = this.collection.default_view_mode;
                this.form.enabled_view_modes = JSON.parse(JSON.stringify(this.collection.enabled_view_modes));
                this.moderators = JSON.parse(JSON.stringify(this.collection.moderators));
                this.form.comment_status = this.collection.comment_status;
                 
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
        } else {
            var tmppath = this.$route.fullPath.split("/");
            var mapper = tmppath.pop();
            if(tmppath.pop() == 'new') {
                this.isNewCollection = true;
                this.isMapped = true;
                this.mapper = mapper;
                this.createNewCollection();
            }
        }
    }

}
</script>

<style lang="scss" scoped>

    @import "../../scss/_variables.scss";

    .column {
        padding: 0;
    }

    .field {
        position: relative;
    }

    .section-label {
        font-size: 16px !important;
        font-weight: 500 !important;
        color: $blue5 !important;
        line-height: 1.2em;
    }

    #button-edit-thumbnail, 
    #button-edit-header-image,
    #button-delete-thumbnail, 
    #button-delete-header-image {

        border-radius: 100px !important;
        height: 30px !important;
        width: 30px !important;
        z-index: 99;
        margin-left: 12px !important;
        
        .icon {
            display: inherit;
            padding: 0;
            margin: 0;
            margin-top: 1px;
            font-size: 18px;
        }
    }
    .header-field {  

        .image-placeholder {
            position: absolute;
            left: 30%;
            right: 30%;
            top: 40%;
            font-size: 2.0rem;
            font-weight: bold;
            z-index: 99;
            text-align: center;
            color: $gray4;
            
            @media screen and (max-width: 769px) {
                font-size: 1.2rem;
            }
            
        }
        .header-buttons-row {
            text-align: right;
            top: -15px;
            position: relative;
        }
    }
    .thumbnail-field {  
        // padding: 26px;
        // margin-top: 16px;
        // margin-bottom: 38px;

        .content {
            padding: 10px;
            font-size: 0.8em;
        }
        img {
            height: 178px;
            width: 178px;
        }
        .image-placeholder {
            position: absolute;
            margin-left: 45px;
            margin-right: 45px;
            font-size: 0.8rem;
            font-weight: bold;
            z-index: 99;
            text-align: center;
            color: $gray4;
            top: 70px;
            max-width: 90px;
        }
        .thumbnail-buttons-row {
            position: relative;
            left: 86px;
            bottom: 20px;
        }
    }
    .selected-cover-page {
        border: 1px solid $gray2;
        padding: 8px;
        font-size: .75rem;
        .span { vertical-align: middle;}

        .selected-cover-page-control {
            float: right;
        }
    }
    .selected-cover-page-buttons {
        float: right;
        padding: 4px 6px;
        .icon { font-size: 20px; }
        &.disabled {
            pointer-events: none;
            cursor: not-allowed;
           
           .icon { color: $gray2; }
        }
    }
    .status-radios {
        display: flex;

        .control-lable {
            display: flex;
            align-items: center;
        }
    }
    .moderators-empty-list { 
        color: $gray4;
        font-size: 0.85rem;
    }

</style>


