<template>
    <div 
            v-if="collections.length > 0 && !isLoading"
            class="table-container">
        <div 
                v-if="$userCaps.hasCapability('tnc_rep_delete_collections')"
                class="selection-control">
            <div class="field select-all is-pulled-left">
                <span>
                    <b-checkbox 
                            @click.native="selectAllCollectionsOnPage()" 
                            :value="allCollectionsOnPageSelected">{{ $i18n.get('label_select_all_collections_page') }}</b-checkbox>
                </span>
            </div>
            <div class="field is-pulled-right">
                <b-dropdown
                        position="is-bottom-left"
                        :disabled="!isSelectingCollections"
                        id="bulk-actions-dropdown"
                        aria-role="list"
                        trap-focus>
                    <button
                            class="button is-white"
                            slot="trigger">
                        <span>{{ $i18n.get('label_bulk_actions') }}</span>
                        <span class="icon">
                            <i class="tainacan-icon tainacan-icon-20px tainacan-icon-arrowdown"/>
                        </span>
                    </button> 

                    <b-dropdown-item
                            id="item-delete-selected-items"
                            @click="deleteSelectedCollections()"
                            aria-role="listitem">
                        {{ $i18n.get('label_delete_selected_collections') }}
                    </b-dropdown-item>
                    <b-dropdown-item 
                            disabled
                            aria-role="listitem">{{ $i18n.get('label_edit_selected_collections') + ' (Not ready)' }}
                    </b-dropdown-item>
                </b-dropdown>
            </div>
        </div>
        <div class="table-wrapper">

            <!-- Context menu for right click selection -->
            <div 
                    v-if="cursorPosY > 0 && cursorPosX > 0"
                    class="context-menu">

                <!-- Backdrop for escaping context menu -->
                <div 
                    @click.left="clearContextMenu()"
                    @click.right="clearContextMenu()"
                    class="context-menu-backdrop" /> 

                <b-dropdown 
                        inline
                        :style="{ top: cursorPosY + 'px', left: cursorPosX + 'px' }"
                        trap-focus>
                    <b-dropdown-item
                            @click="openCollection()" 
                            v-if="!isOnTrash">
                        {{ $i18n.getFrom('collections', 'view_item') }}
                    </b-dropdown-item>
                    <b-dropdown-item
                            @click="openCollectionOnNewTab()"
                            v-if="!isOnTrash">
                        {{ $i18n.get('label_open_collection_new_tab') }}
                    </b-dropdown-item>
                    <b-dropdown-item 
                            @click="selectCollection()"
                            v-if="contextMenuIndex != null">
                        {{ !selectedCollections[contextMenuIndex] ? $i18n.get('label_select_collection') : $i18n.get('label_unselect_collection') }}
                    </b-dropdown-item>
                    <b-dropdown-item
                            @click="goToCollectionEditPage(contextMenuCollection)"
                            v-if="contextMenuCollection != null && (collections[contextMenuIndex] && collections[contextMenuIndex].current_user_can_edit)">
                        {{ $i18n.getFrom('collections', 'edit_item') }}
                    </b-dropdown-item>
                    <b-dropdown-item
                            @click="deleteOneCollection(contextMenuCollection)"
                            v-if="contextMenuCollection != null && (collections[contextMenuIndex] && collections[contextMenuIndex].current_user_can_delete)">
                        {{ $i18n.get('label_delete_collection') }}
                    </b-dropdown-item>
                </b-dropdown>
            </div>
            <table class="tainacan-table">
                <thead>
                    <tr>
                        <!-- Checking list -->
                        <th v-if="$userCaps.hasCapability('tnc_rep_delete_collections')">
                            &nbsp;
                            <!-- nothing to show on header -->
                        </th>
                        <!-- Thumbnail -->
                        <th class="thumbnail-cell">
                            <div class="th-wrap">{{ $i18n.get('label_thumbnail') }}</div>
                        </th>
                        <!-- Name -->
                        <th>
                            <div class="th-wrap">{{ $i18n.get('label_name') }}</div>
                        </th>
                        <!-- Description -->
                        <th>
                            <div class="th-wrap">{{ $i18n.get('label_description') }}</div>
                        </th>
                        <!-- Creation Date -->
                        <th>
                            <div class="th-wrap">{{ $i18n.get('label_creation_date') }}</div>
                        </th>
                        <!-- Created By -->
                        <th>
                            <div class="th-wrap">{{ $i18n.get('label_created_by') }}</div>
                        </th>
                        <!-- Total Items -->
                        <th v-if="!isOnTrash">
                            <div class="th-wrap total-items-header">{{ $i18n.get('label_total_items') }}</div>
                        </th>
                        <th 
                                v-if="collections.findIndex((collection) => collection.current_user_can_edit || collection.current_user_can_delete) >= 0"
                                class="actions-header">
                            &nbsp;
                            <!-- nothing to show on header for actions cell-->
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr     
                            :class="{ 'selected-row': selectedCollections[index] }"
                            :key="index"
                            v-for="(collection, index) of collections">
                        <!-- Checking list -->
                        <td 
                                v-if="$userCaps.hasCapability('tnc_rep_delete_collections')"
                                :class="{ 'is-selecting': isSelectingCollections }"
                                class="checkbox-cell">
                            <b-checkbox 
                                    size="is-small"
                                    v-model="selectedCollections[index]"/> 
                        </td>
                        <!-- Thumbnail -->
                        <td 
                                class="thumbnail-cell column-default-width"
                                @click.left="onClickCollection($event, collection.id, index)"
                                @click.right="onRightClickCollection($event, collection.id, index)"
                                :label="$i18n.get('label_thumbnail')" 
                                :aria-label="$i18n.get('label_thumbnail')">
                            <span>
                                <img 
                                        :alt="$i18n.get('label_thumbnail')"
                                        class="table-thumb" 
                                        :src="(collection.thumbnail && collection.thumbnail.thumbnail ) ? collection.thumbnail.thumbnail[0] : thumbPlaceholderPath">
                            </span>
                        </td>
                        <!-- Name -->
                        <td 
                                class="column-default-width column-main-content"
                                @click.left="onClickCollection($event, collection.id, index)"
                                @click.right="onRightClickCollection($event, collection.id, index)"
                                :label="$i18n.get('label_name')" 
                                :aria-label="$i18n.get('label_name') + ': ' + collection.name">
                            <p
                                    v-tooltip="{
                                        delay: {
                                            show: 500,
                                            hide: 300,
                                        },
                                        content: collection.name,
                                        autoHide: false,
                                        classes: ['tooltip', 'repository-tooltip'],
                                        placement: 'auto-start'
                                    }">
                                {{ collection.name }}</p>
                        </td>
                        <!-- Description -->
                        <td
                                class="column-large-width" 
                                @click.left="onClickCollection($event, collection.id, index)"
                                @click.right="onRightClickCollection($event, collection.id, index)"
                                :label="$i18n.get('label_description')" 
                                :aria-label="$i18n.get('label_description') + ': ' + (collection.description != undefined && collection.description != '') ? collection.description : `<span class='has-text-gray is-italic'>` + $i18n.get('label_description_not_informed') + `</span>`">
                            <p
                                    v-tooltip="{
                                        delay: {
                                            show: 500,
                                            hide: 300,
                                        },
                                        content: (collection.description != undefined && collection.description != '') ? collection.description : `<span class='has-text-gray is-italic'>` + $i18n.get('label_description_not_informed') + `</span>`,
                                        autoHide: false,
                                        classes: ['tooltip', 'repository-tooltip'],
                                        placement: 'auto-start'
                                    }" 
                                    v-html="(collection.description != undefined && collection.description != '') ? collection.description : `<span class='has-text-gray is-italic'>` + $i18n.get('label_description_not_informed') + `</span>`"/>
                        </td>
                        <!-- Creation Date -->
                        <td
                                @click.left="onClickCollection($event, collection.id, index)"
                                @click.right="onRightClickCollection($event, collection.id, index)"
                                class="table-creation column-default-width" 
                                :label="$i18n.get('label_creation_date')" 
                                :aria-label="$i18n.get('label_creation_date') + ': ' + collection.creation_date">
                            <p
                                    v-tooltip="{
                                        delay: {
                                            show: 500,
                                            hide: 300,
                                        },
                                        content: collection.creation_date,
                                        autoHide: false,
                                        classes: ['tooltip', 'repository-tooltip'],
                                        placement: 'auto-start'
                                    }" 
                                    v-html="collection.creation_date" />
                        </td>
                        <!-- Created by -->
                        <td
                                @click.left="onClickCollection($event, collection.id, index)"
                                @click.right="onRightClickCollection($event, collection.id, index)"
                                class="table-creation column-default-width" 
                                :label="$i18n.get('label_created_by')" 
                                :aria-label="$i18n.get('label_created_by') + ': ' + collection.author_name">
                            <p
                                    v-tooltip="{
                                        delay: {
                                            show: 500,
                                            hide: 300,
                                        },
                                        content: collection.author_name,
                                        autoHide: false,
                                        classes: ['tooltip', 'repository-tooltip'],
                                        placement: 'auto-start'
                                    }" 
                                    v-html="collection.author_name" />
                        </td>
                        <!-- Total items -->
                        <td
                                @click.left="onClickCollection($event, collection.id, index)"
                                @click.right="onRightClickCollection($event, collection.id, index)"
                                class="column-small-width column-align-right" 
                                :label="$i18n.get('label_total_items')" 
                                v-if="collection.total_items != undefined"
                                :aria-label="$i18n.get('label_total_items') + ': ' + getTotalItems(collection.total_items)">
                            <p
                                    v-tooltip="{
                                        delay: {
                                            show: 500,
                                            hide: 300,
                                        },
                                        content: getTotalItems(collection.total_items),
                                        autoHide: false,
                                        classes: ['tooltip', 'repository-tooltip'],
                                        placement: 'auto-start'
                                    }" 
                                    v-html="getTotalItems(collection.total_items)" />
                        </td>
                        <!-- Actions -->
                        <td  
                                v-if="collection.current_user_can_edit || collection.current_user_can_delete"
                                @click="onClickCollection($event, collection.id, index)"
                                class="column-default-width"
                                :class="{ 'actions-cell': collection.current_user_can_edit || collection.current_user_can_delete }"  
                                :label="$i18n.get('label_actions')">
                            <div class="actions-container">
                                <a 
                                        id="button-edit" 
                                        v-if="collection.current_user_can_edit"
                                        :aria-label="$i18n.getFrom('collections','edit_item')" 
                                        @click.prevent.stop="goToCollectionEditPage(collection.id)">                      
                                    <span 
                                            v-tooltip="{
                                                content: $i18n.get('edit'),
                                                autoHide: true,
                                                classes: ['tooltip', 'repository-tooltip'],
                                                placement: 'auto'
                                            }"
                                            class="icon">
                                        <i class="tainacan-icon tainacan-icon-20px tainacan-icon-settings"/>
                                    </span>
                                </a>
                                <a 
                                        id="button-delete"
                                        v-if="collection.current_user_can_delete"
                                        :aria-label="$i18n.get('label_button_delete')" 
                                        @click.prevent.stop="deleteOneCollection(collection.id)">
                                    <span 
                                            v-tooltip="{
                                                content: $i18n.get('delete'),
                                                autoHide: true,
                                                classes: ['tooltip', 'repository-tooltip'],
                                                placement: 'auto'
                                            }"
                                            class="icon">
                                        <i 
                                                :class="{ 'tainacan-icon-delete': !isOnTrash, 'tainacan-icon-deleteforever': isOnTrash }"
                                                class="tainacan-icon tainacan-icon-20px"/>
                                    </span>
                                </a>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>

<script>
import { mapActions } from 'vuex';
import CustomDialog from '../other/custom-dialog.vue';

export default {
    name: 'CollectionsList',
    props: {
        isLoading: false,
        totalCollections: 0,
        page: 1,
        collectionsPerPage: 12,
        collections: Array,
        isOnTrash: false
    },
    data(){
        return {
            selectedCollections: [],
            allCollectionsOnPageSelected: false,
            isSelectingCollections: false,
            thumbPlaceholderPath: tainacan_plugin.base_url + '/assets/images/placeholder_square.png',
            cursorPosX: -1,
            cursorPosY: -1,
            contextMenuIndex: null,
            contextMenuCollection: null
        }
    },
    watch: {
        collections() {
            this.selectedCollections = [];
            for (let i = 0; i < this.collections.length; i++)
                this.selectedCollections.push(false);    
        },
        selectedCollections() {
            let allSelected = true;
            let isSelecting = false;
            for (let i = 0; i < this.selectedCollections.length; i++) {
                if (this.selectedCollections[i] == false) {
                    allSelected = false;
                } else {
                    isSelecting = true;
                }
            }
            this.allCollectionsOnPageSelected = allSelected;
            this.isSelectingCollections = isSelecting;
        }
    },
    methods: {
        ...mapActions('collection', [
            'deleteCollection'
        ]),
        selectAllCollectionsOnPage() {
            for (let i = 0; i < this.selectedCollections.length; i++) 
                this.selectedCollections.splice(i, 1, !this.allCollectionsOnPageSelected);
        },
        getTotalItems(total_items) {
            return Number(total_items['publish']) + Number(total_items['private']);
        },
        deleteOneCollection(collectionId) {
            this.$buefy.modal.open({
                parent: this,
                component: CustomDialog,
                props: {
                    icon: 'alert',
                    title: this.$i18n.get('label_warning'),
                    message: this.isOnTrash ? this.$i18n.get('info_warning_collection_delete') : this.$i18n.get('info_warning_collection_trash'),
                    onConfirm: () => {
                        this.deleteCollection({ collectionId: collectionId, isPermanently: this.isOnTrash })
                        .then(() => {
                        //     this.$buefy.toast.open({
                        //         duration: 3000,
                        //         message: this.$i18n.get('info_collection_deleted'),
                        //         position: 'is-bottom',
                        //         type: 'is-secondary',
                        //         queue: true
                        //     });
                            for (let i = 0; i < this.selectedCollections.length; i++) {
                                if (this.selectedCollections[i].id == collectionId)
                                    this.selectedCollections.splice(i, 1);
                            }
                        }).catch(() => {
                        //     this.$buefy.toast.open({
                        //         duration: 3000,
                        //         message: this.$i18n.get('info_error_deleting_collection'),
                        //         position: 'is-bottom',
                        //         type: 'is-danger',
                        //         queue: true
                        //     })
                        });
                    }
                },
                trapFocus: true
            });
            this.clearContextMenu();
        },
        deleteSelectedCollections() {
            this.$buefy.modal.open({
                parent: this,
                component: CustomDialog,
                props: {
                    icon: 'alert',
                    title: this.$i18n.get('label_warning'),
                    message: this.isOnTrash ? this.$i18n.get('info_warning_selected_collections_delete') : this.$i18n.get('info_warning_selected_collections_trash'),
                    onConfirm: () => {

                        for (let i = 0; i < this.collections.length; i++) {
                            if (this.selectedCollections[i]) {
                                this.deleteCollection({ collectionId: this.collections[i].id, isPermanently: this.isOnTrash })
                                .then(() => {
                                //     this.loadCollections();
                                //     this.$buefy.toast.open({
                                //         duration: 3000,
                                //         message: this.$i18n.get('info_collection_deleted'),
                                //         position: 'is-bottom',
                                //         type: 'is-secondary',
                                //         queue: false
                                //     })                            
                                }).catch(() => { 
                                //     this.$buefy.toast.open({
                                //         duration: 3000,
                                //         message: this.$i18n.get('info_error_deleting_collection'),
                                //         position: 'is-bottom',
                                //         type: 'is-danger',
                                //         queue: false
                                //     });
                                });
                            }
                        }
                        this.allCollectionsOnPageSelected = false;
                    }
                },
                trapFocus: true
            });
        },
        openCollection() {
            if (this.contextMenuCollection != null) {
                this.$router.push(this.$routerHelper.getCollectionPath(this.contextMenuCollection));
            }
            this.clearContextMenu();
        },
        openCollectionOnNewTab() {
            if (this.contextMenuCollection != null) {
                let routeData = this.$router.resolve(this.$routerHelper.getCollectionPath(this.contextMenuCollection));
                window.open(routeData.href, '_blank');
            }
            this.clearContextMenu();
        },
        selectCollection() {
            if (this.contextMenuIndex != null) {
                this.$set(this.selectedCollections, this.contextMenuIndex, !this.selectedCollections[this.contextMenuIndex]);
            }
            this.clearContextMenu();
        },
        onClickCollection($event, collectionId, index) {
            if ($event.ctrlKey) {
                this.$set(this.selectedCollections, index, !this.selectedCollections[index]); 
            } else {
                this.$router.push(this.$routerHelper.getCollectionPath(collectionId));
            }
        },
        goToCollectionEditPage(collectionId) {
            this.$router.push(this.$routerHelper.getCollectionEditPath(collectionId));
        },
        onRightClickCollection($event, collectionId, index) {
            $event.preventDefault();

            this.cursorPosX = $event.clientX;
            this.cursorPosY = $event.clientY;
            this.contextMenuCollection = collectionId;
            this.contextMenuIndex = index;
        },
        clearContextMenu() {
            this.cursorPosX = -1;
            this.cursorPosY = -1;
            this.contextMenuCollection = null;
            this.contextMenuIndex = null;
        }
    }
}
</script>

<style lang="scss" scoped>

    @import "../../scss/_variables.scss";

    .selection-control {
        
        padding: 6px 0px 0px 12px;
        background: white;
        height: 40px;

        .select-all {
            color: $gray4;
            font-size: 14px;
            &:hover {
                color: $gray4;
            }
        }
    }

    .total-items-header {
        text-align: right;
    }

    .context-menu {
        .dropdown {
            position: fixed;
            z-index: 99999999999;
        }
        .context-menu-backdrop {
            position: fixed;
            top: 0;
            right: 0;
            left: 0;
            border: 0;
            width: 100%;
            height: 100vh;
            z-index: 9999999;
        }
    }
    
</style>


