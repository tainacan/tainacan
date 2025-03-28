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
                            :model-value="allCollectionsOnPageSelected" 
                            @update:model-value="selectAllCollectionsOnPage()">
                        {{ $i18n.get('label_select_all_collections_page') }}
                    </b-checkbox>
                </span>
            </div>
            <div class="field is-pulled-right">
                <b-dropdown
                        id="bulk-actions-dropdown"
                        position="is-bottom-left"
                        :disabled="!isSelectingCollections"
                        aria-role="list"
                        trap-focus>
                    <template #trigger>
                        <button class="button is-white">
                            <span>{{ $i18n.get('label_bulk_actions') }}</span>
                            <span class="icon">
                                <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-arrowdown" />
                            </span>
                        </button> 
                    </template>
                    <b-dropdown-item
                            id="item-delete-selected-items"
                            aria-role="listitem"
                            @click="deleteSelectedCollections()">
                        {{ $i18n.get('label_delete_selected_collections') }}
                    </b-dropdown-item>
                    <!-- <b-dropdown-item 
                            disabled
                            aria-role="listitem">{{ $i18n.get('label_edit_selected_collections') + ' (Not ready)' }}
                    </b-dropdown-item> -->
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
                        class="context-menu-backdrop"
                        @click.left="clearContextMenu()"
                        @click.right="clearContextMenu()" /> 

                <b-dropdown 
                        inline
                        :style="{ top: cursorPosY + 'px', left: cursorPosX + 'px' }"
                        trap-focus>
                    <b-dropdown-item
                            v-if="!isOnTrash" 
                            @click="openCollection()">
                        {{ $i18n.getFrom('collections', 'view_item') }}
                    </b-dropdown-item>
                    <b-dropdown-item
                            v-if="!isOnTrash"
                            @click="openCollectionOnNewTab()">
                        {{ $i18n.get('label_open_collection_new_tab') }}
                    </b-dropdown-item>
                    <b-dropdown-item 
                            v-if="contextMenuIndex != null"
                            @click="selectCollection()">
                        {{ !selectedCollections[contextMenuIndex] ? $i18n.get('label_select_collection') : $i18n.get('label_unselect_collection') }}
                    </b-dropdown-item>
                    <b-dropdown-item
                            v-if="contextMenuCollection != null && (collections[contextMenuIndex] && collections[contextMenuIndex].current_user_can_edit)"
                            @click="goToCollectionEditPage(contextMenuCollection)">
                        {{ $i18n.getFrom('collections', 'edit_item') }}
                    </b-dropdown-item>
                    <b-dropdown-item
                            v-if="contextMenuCollection != null && (collections[contextMenuIndex] && collections[contextMenuIndex].current_user_can_delete)"
                            @click="deleteOneCollection(contextMenuCollection)">
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
                        <!-- Status icon -->
                        <th v-if="isOnAllCollectionsTab">
                            &nbsp;
                        </th>
                        <!-- Thumbnail -->
                        <th class="thumbnail-cell">
                            <div class="th-wrap">
                                {{ $i18n.get('label_thumbnail') }}
                            </div>
                        </th>
                        <!-- Name -->
                        <th>
                            <div class="th-wrap">
                                {{ $i18n.get('label_name') }}
                            </div>
                        </th>
                        <!-- Description -->
                        <th>
                            <div class="th-wrap">
                                {{ $i18n.get('label_description') }}
                            </div>
                        </th>
                        <!-- Modification Date -->
                        <th>
                            <div class="th-wrap">
                                {{ $i18n.get('label_modification_date') }}
                            </div>
                        </th>
                        <!-- Creation Date -->
                        <th>
                            <div class="th-wrap">
                                {{ $i18n.get('label_creation_date') }}
                            </div>
                        </th>
                        <!-- Created By -->
                        <th>
                            <div class="th-wrap">
                                {{ $i18n.get('label_created_by') }}
                            </div>
                        </th>
                        <!-- Total Items -->
                        <th v-if="!isOnTrash">
                            <div class="th-wrap total-items-header">
                                {{ $i18n.get('label_total_items') }}
                            </div>
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
                            v-for="(collection, index) of collections"
                            :key="index"
                            :class="{ 'selected-row': selectedCollections[index] }">
                        <!-- Checking list -->
                        <td 
                                v-if="$userCaps.hasCapability('tnc_rep_delete_collections')"
                                :class="{ 'is-selecting': isSelectingCollections }"
                                class="checkbox-cell">
                            <b-checkbox v-model="selectedCollections[index]" /> 
                        </td>
                        <!-- Status icon -->
                        <td 
                                v-if="isOnAllCollectionsTab"
                                class="status-cell">
                            <span 
                                    v-if="$statusHelper.hasIcon(collection.status)"
                                    v-tooltip="{
                                        content: $i18n.get('status_' + collection.status),
                                        autoHide: true,
                                        html: true,
                                        popperClass: ['tainacan-tooltip', 'tooltip', 'tainacan-repository-tooltip'],
                                        placement: 'auto-start'
                                    }"
                                    class="icon has-text-gray">
                                <i 
                                        class="tainacan-icon tainacan-icon-1em"
                                        :class="$statusHelper.getIcon(collection.status)"
                                    />
                            </span>
                        </td>
                        <!-- Thumbnail -->
                        <td 
                                class="thumbnail-cell column-default-width"
                                :label="$i18n.get('label_thumbnail')"
                                :aria-label="$i18n.get('label_thumbnail')"
                                @click.left="onClickCollection($event, collection.id, index)" 
                                @click.right="onRightClickCollection($event, collection.id, index)">
                            <span>
                                <img 
                                        :alt="$i18n.get('label_thumbnail')"
                                        class="table-thumb" 
                                        :src="$thumbHelper.getSrc(collection['thumbnail'], 'tainacan-small')">
                            </span>
                        </td>
                        <!-- Name -->
                        <td 
                                class="column-default-width column-main-content"
                                :label="$i18n.get('label_name')"
                                :aria-label="$i18n.get('label_name') + ': ' + collection.name"
                                @click.left="onClickCollection($event, collection.id, index)" 
                                @click.right="onRightClickCollection($event, collection.id, index)">
                            <p
                                    v-tooltip="{
                                        delay: {
                                            show: 500,
                                            hide: 300,
                                        },
                                        content: collection.name,
                                        autoHide: false,
                                        html: true,
                                        popperClass: ['tainacan-tooltip', 'tooltip', 'tainacan-repository-tooltip'],
                                        placement: 'auto-start'
                                    }">
                                {{ collection.name }}</p>
                        </td>
                        <!-- Description -->
                        <td
                                class="column-large-width" 
                                :label="$i18n.get('label_description')"
                                :aria-label="$i18n.get('label_description') + ': ' + (collection.description != undefined && collection.description != '') ? collection.description : `<span class='has-text-gray is-italic'>` + $i18n.get('label_description_not_provided') + `</span>`"
                                @click.left="onClickCollection($event, collection.id, index)" 
                                @click.right="onRightClickCollection($event, collection.id, index)">
                            <p
                                    v-tooltip="{
                                        delay: {
                                            show: 500,
                                            hide: 300,
                                        },
                                        content: (collection.description != undefined && collection.description != '') ? collection.description : `<span class='has-text-gray is-italic'>` + $i18n.get('label_description_not_provided') + `</span>`,
                                        autoHide: false,
                                        html: true,
                                        popperClass: ['tainacan-tooltip', 'tooltip', 'tainacan-repository-tooltip'],
                                        placement: 'auto-start'
                                    }" 
                                    v-html="(collection.description != undefined && collection.description != '') ? collection.description : `<span class='has-text-gray is-italic'>` + $i18n.get('label_description_not_provided') + `</span>`" />
                        </td>
                        <!-- Modification Date -->
                        <td
                                class="table-modification column-default-width"
                                :label="$i18n.get('label_modification_date')"
                                :aria-label="$i18n.get('label_modification_date') + ': ' + collection.modification_date" 
                                @click.left="onClickCollection($event, collection.id, index)" 
                                @click.right="onRightClickCollection($event, collection.id, index)">
                            <p
                                    v-tooltip="{
                                        delay: {
                                            show: 500,
                                            hide: 300,
                                        },
                                        content: collection.modification_date,
                                        autoHide: false,
                                        html: true,
                                        popperClass: ['tainacan-tooltip', 'tooltip', 'tainacan-repository-tooltip'],
                                        placement: 'auto-start'
                                    }" 
                                    v-html="collection.modification_date" />
                        </td>
                        <!-- Creation Date -->
                        <td
                                class="table-creation column-default-width"
                                :label="$i18n.get('label_creation_date')"
                                :aria-label="$i18n.get('label_creation_date') + ': ' + collection.creation_date" 
                                @click.left="onClickCollection($event, collection.id, index)" 
                                @click.right="onRightClickCollection($event, collection.id, index)">
                            <p
                                    v-tooltip="{
                                        delay: {
                                            show: 500,
                                            hide: 300,
                                        },
                                        content: collection.creation_date,
                                        autoHide: false,
                                        html: true,
                                        popperClass: ['tainacan-tooltip', 'tooltip', 'tainacan-repository-tooltip'],
                                        placement: 'auto-start'
                                    }" 
                                    v-html="collection.creation_date" />
                        </td>
                        <!-- Created by -->
                        <td
                                class="table-creation column-default-width"
                                :label="$i18n.get('label_created_by')"
                                :aria-label="$i18n.get('label_created_by') + ': ' + collection.author_name" 
                                @click.left="onClickCollection($event, collection.id, index)" 
                                @click.right="onRightClickCollection($event, collection.id, index)">
                            <p
                                    v-tooltip="{
                                        delay: {
                                            show: 500,
                                            hide: 300,
                                        },
                                        content: collection.author_name,
                                        autoHide: false,
                                        html: true,
                                        popperClass: ['tainacan-tooltip', 'tooltip', 'tainacan-repository-tooltip'],
                                        placement: 'auto-start'
                                    }" 
                                    v-html="collection.author_name" />
                        </td>
                        <!-- Total items -->
                        <td
                                v-if="collection.total_items != undefined"
                                class="column-small-width column-align-right"
                                :label="$i18n.get('label_total_items')" 
                                :aria-label="$i18n.get('label_total_items') + ': ' + getTotalItems(collection.total_items)" 
                                @click.left="onClickCollection($event, collection.id, index)"
                                @click.right="onRightClickCollection($event, collection.id, index)">
                            <p
                                    v-tooltip="{
                                        delay: {
                                            show: 500,
                                            hide: 300,
                                        },
                                        content: getTotalItemsDetailed(collection.total_items),
                                        autoHide: false,
                                        html: true,
                                        popperClass: ['tainacan-tooltip', 'tooltip', 'tainacan-repository-tooltip'],
                                        placement: 'auto-start'
                                    }" 
                                    v-html="getTotalItems(collection.total_items)" />
                        </td>
                        <!-- Actions -->
                        <td  
                                v-if="collection.current_user_can_edit || collection.current_user_can_delete"
                                class="column-default-width"
                                :class="{ 'actions-cell': collection.current_user_can_edit || collection.current_user_can_delete }"
                                :label="$i18n.get('label_actions')"  
                                @click="onClickCollection($event, collection.id, index)">
                            <div class="actions-container">
                                <a 
                                        v-if="collection.current_user_can_edit" 
                                        id="button-edit"
                                        :aria-label="$i18n.getFrom('collections','edit_item')" 
                                        @click.prevent.stop="goToCollectionEditPage(collection.id)">                      
                                    <span 
                                            v-tooltip="{
                                                content: $i18n.get('edit'),
                                                autoHide: true,
                                                popperClass: ['tainacan-tooltip', 'tooltip', 'tainacan-repository-tooltip'],
                                                placement: 'auto',
                                                html: true
                                            }"
                                            class="icon">
                                        <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-settings" />
                                    </span>
                                </a>
                                <a 
                                        v-if="collection.current_user_can_delete"
                                        id="button-delete"
                                        :aria-label="$i18n.get('label_button_delete')" 
                                        @click.prevent.stop="deleteOneCollection(collection.id)">
                                    <span 
                                            v-tooltip="{
                                                content: $i18n.get('delete'),
                                                autoHide: true,
                                                popperClass: ['tainacan-tooltip', 'tooltip', 'tainacan-repository-tooltip'],
                                                placement: 'auto'
                                            }"
                                            class="icon">
                                        <i 
                                                :class="{ 'tainacan-icon-delete': !isOnTrash, 'tainacan-icon-deleteforever': isOnTrash }"
                                                class="tainacan-icon tainacan-icon-1-25em" />
                                    </span>
                                </a>
                                <a 
                                        id="button-open-external" 
                                        :aria-label="$i18n.getFrom('collections','view_item')"
                                        target="_blank" 
                                        :href="collection.url"
                                        @click.stop="">                      
                                    <span 
                                            v-tooltip="{
                                                content: $i18n.get('label_view_collection_on_website'),
                                                autoHide: true,
                                                popperClass: ['tainacan-tooltip', 'tooltip', 'tainacan-repository-tooltip'],
                                                placement: 'auto',
                                                html: true
                                            }"
                                            class="icon">
                                        <i class="tainacan-icon tainacan-icon-1-125em tainacan-icon-openurl" />
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
        status: ''
    },
    data(){
        return {
            selectedCollections: [],
            allCollectionsOnPageSelected: false,
            isSelectingCollections: false,
            cursorPosX: -1,
            cursorPosY: -1,
            contextMenuIndex: null,
            contextMenuCollection: null
        }
    },
    computed: {
        isOnTrash() {
            return this.status == 'trash';
        },
        isOnAllCollectionsTab() {
            return !this.status || (this.status.indexOf(',') > 0);
        }
    },
    watch: {
        collections: {
            handler() {
                this.selectedCollections = [];
                for (let i = 0; i < this.collections.length; i++)
                    this.selectedCollections.push(false);    
            },
            deep: true
        },
        selectedCollections: {
            handler() {
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
            },
            deep: true
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
            return Number(total_items['publish']) + Number(total_items['private']) + Number(total_items['pending']) + Number(total_items['draft']);
        },
        getTotalItemsDetailed(total_items) {
            return this.$i18n.get('status_public') + ': ' + total_items['publish'] + '<br> ' +
                   this.$i18n.get('status_private') + ': ' + total_items['private'] + '<br> ' +
                   this.$i18n.get('status_pending') + ': ' + total_items['pending'] + '<br> ' +
                   this.$i18n.get('status_draft') + ': ' + total_items['draft'];
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
                trapFocus: true,
                customClass: 'tainacan-modal',
                closeButtonAriaLabel: this.$i18n.get('close')
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
                trapFocus: true,
                customClass: 'tainacan-modal',
                closeButtonAriaLabel: this.$i18n.get('close')
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
            if (this.contextMenuIndex != null)
                Object.assign( this.selectedCollections, { [this.contextMenuIndex]: !this.selectedCollections[this.contextMenuIndex] });
            
            this.clearContextMenu();
        },
        onClickCollection($event, collectionId, index) {
            if ($event.ctrlKey)
                Object.assign( this.selectedCollections, { [index]: !this.selectedCollections[index] }); 
            else
                this.$router.push(this.$routerHelper.getCollectionPath(collectionId));
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

    @import "../../scss/_tables.scss";

    .selection-control {
        
        padding: 6px 0px 0px 12px;
        background: var(--tainacan-background-color);
        height: 40px;

        .select-all {
            color: var(--tainacan-info-color);
            font-size: 0.875em;
            &:hover {
                color: var(--tainacan-info-color);
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


