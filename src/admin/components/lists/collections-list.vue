<template>
    <div 
            v-if="collections.length > 0 && !isLoading"
            class="table-container">
        <div class="selection-control">
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
                        v-if="$userCaps.hasCapability('delete_tainacan-collections')"
                        :disabled="!isSelectingCollections"
                        id="bulk-actions-dropdown">
                    <button
                            class="button is-white"
                            slot="trigger">
                        <span>{{ $i18n.get('label_bulk_actions') }}</span>
                        <b-icon icon="menu-down"/>
                    </button> 

                    <b-dropdown-item
                            id="item-delete-selected-items"
                            @click="deleteSelectedCollections()">
                        {{ $i18n.get('label_delete_selected_collections') }}
                    </b-dropdown-item>
                    <b-dropdown-item disabled>{{ $i18n.get('label_edit_selected_collections') + ' (Not ready)' }}
                    </b-dropdown-item>
                </b-dropdown>
            </div>
        </div>
        <div class="table-wrapper">
            <table class="tainacan-table">
                <thead>
                    <tr>
                        <!-- Checking list -->
                        <th>
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
                        <th class="actions-header">
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
                                :class="{ 'is-selecting': isSelectingCollections }"
                                class="checkbox-cell">
                            <b-checkbox 
                                    size="is-small"
                                    v-model="selectedCollections[index]"/> 
                        </td>
                        <!-- Thumbnail -->
                        <td 
                                class="thumbnail-cell column-default-width"
                                @click="onClickCollection($event, collection.id, index)"
                                :label="$i18n.get('label_thumbnail')" 
                                :aria-label="$i18n.get('label_thumbnail')">
                            <span>
                                <img 
                                        class="table-thumb" 
                                        :src="collection.thumbnail.thumb">
                            </span>
                        </td>
                        <!-- Name -->
                        <td 
                                class="column-default-width column-main-content"
                                @click="onClickCollection($event, collection.id, index)"
                                :label="$i18n.get('label_name')" 
                                :aria-label="$i18n.get('label_name') + ': ' + collection.name">
                            <p
                                    v-tooltip="{
                                        content: collection.name,
                                        autoHide: false,
                                        placement: 'auto-start'
                                    }">
                                {{ collection.name }}</p>
                        </td>
                        <!-- Description -->
                        <td
                                class="column-large-width" 
                                @click="onClickCollection($event, collection.id, index)"
                                :label="$i18n.get('label_description')" 
                                :aria-label="$i18n.get('label_description') + ': ' + (collection.description != undefined && collection.description != '') ? collection.description : `<span class='has-text-gray is-italic'>` + $i18n.get('label_description_not_informed') + `</span>`">
                            <p
                                    v-tooltip="{
                                        content: (collection.description != undefined && collection.description != '') ? collection.description : `<span class='has-text-gray is-italic'>` + $i18n.get('label_description_not_informed') + `</span>`,
                                        autoHide: false,
                                        placement: 'auto-start'
                                    }" 
                                    v-html="(collection.description != undefined && collection.description != '') ? collection.description : `<span class='has-text-gray is-italic'>` + $i18n.get('label_description_not_informed') + `</span>`"/>
                        </td>
                        <!-- Creation Date -->
                        <td
                                @click="onClickCollection($event, collection.id, index)"
                                class="table-creation column-default-width" 
                                :label="$i18n.get('label_creation_date')" 
                                :aria-label="$i18n.get('label_creation_date') + ': ' + collection.creation_date">
                            <p
                                    v-tooltip="{
                                        content: collection.creation_date,
                                        autoHide: false,
                                        placement: 'auto-start'
                                    }" 
                                    v-html="collection.creation_date" />
                        </td>
                        <!-- Created by -->
                        <td
                                @click="onClickCollection($event, collection.id, index)"
                                class="table-creation column-default-width" 
                                :label="$i18n.get('label_created_by')" 
                                :aria-label="$i18n.get('label_created_by') + ': ' + collection.author_name">
                            <p
                                    v-tooltip="{
                                        content: collection.author_name,
                                        autoHide: false,
                                        placement: 'auto-start'
                                    }" 
                                    v-html="collection.author_name" />
                        </td>
                        <!-- Total items -->
                        <td
                                @click="onClickCollection($event, collection.id, index)"
                                class="column-small-width column-align-right" 
                                :label="$i18n.get('label_total_items')" 
                                v-if="collection.total_items != undefined"
                                :aria-label="$i18n.get('label_total_items') + ': ' + getTotalItems(collection.total_items)">
                            <p
                                    v-tooltip="{
                                        content: getTotalItems(collection.total_items),
                                        autoHide: false,
                                        placement: 'auto-start'
                                    }" 
                                    v-html="getTotalItems(collection.total_items)" />
                        </td>
                        <!-- Actions -->
                        <td 
                                @click="onClickCollection($event, collection.id, index)"
                                class="actions-cell column-default-width" 
                                :label="$i18n.get('label_actions')">
                            <div class="actions-container">
                                <a 
                                        id="button-edit" 
                                        :aria-label="$i18n.getFrom('collections','edit_item')" 
                                        @click.prevent.stop="goToCollectionEditPage(collection.id)">
                                    <b-icon 
                                            type="is-secondary" 
                                            icon="settings"/>
                                </a>
                                <a 
                                        id="button-delete" 
                                        :aria-label="$i18n.get('label_button_delete')" 
                                        @click.prevent.stop="deleteOneCollection(collection.id)">
                                    <b-icon 
                                            type="is-secondary" 
                                            :icon="!isOnTrash ? 'delete' : 'delete-forever'"/>
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
    data(){
        return {
            selectedCollections: [],
            allCollectionsOnPageSelected: false,
            isSelectingCollections: false
        }
    },
    props: {
        isLoading: false,
        totalCollections: 0,
        page: 1,
        collectionsPerPage: 12,
        collections: Array,
        isOnTrash: false
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
            this.$modal.open({
                parent: this,
                component: CustomDialog,
                props: {
                    icon: 'alert',
                    title: this.$i18n.get('label_warning'),
                    message: this.isOnTrash ? this.$i18n.get('info_warning_collection_delete') : this.$i18n.get('info_warning_collection_trash'),
                    onConfirm: () => {
                        this.deleteCollection({ collectionId: collectionId, isPermanently: this.isOnTrash })
                        .then(() => {
                        //     this.$toast.open({
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
                        //     this.$toast.open({
                        //         duration: 3000,
                        //         message: this.$i18n.get('info_error_deleting_collection'),
                        //         position: 'is-bottom',
                        //         type: 'is-danger',
                        //         queue: true
                        //     })
                        });
                    }
                
                }
            });
        },
        deleteSelectedCollections() {
            this.$modal.open({
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
                                //     this.$toast.open({
                                //         duration: 3000,
                                //         message: this.$i18n.get('info_collection_deleted'),
                                //         position: 'is-bottom',
                                //         type: 'is-secondary',
                                //         queue: false
                                //     })                            
                                }).catch(() => { 
                                //     this.$toast.open({
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
                    },
                }
            });
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
        }
    },
    mounted() {
        // COLUMN RESIZE
        // This feature is not implemented as it would require whitespace 
        // on table cells to be 'wrap' instead of 'no-wrap'. Once the table
        // needs a scroll, the minimum size for the columns would be reached
        // text would start to ellipsis, but the column is not resizible anymore.
        // (function () {
        //     var thElm;
        //     var startOffset;

        //     Array.prototype.forEach.call(
        //     document.querySelectorAll("table th"),
        //     function (th) {
        //         th.style.position = 'relative';

        //         var grip = document.createElement('div');
        //         grip.innerHTML = "&nbsp;";
        //         grip.style.top = 0;
        //         grip.style.right = 0;
        //         grip.style.bottom = 0;
        //         grip.style.width = '5px';
        //         grip.style.position = 'absolute';
        //         grip.style.cursor = 'col-resize';
        //         grip.addEventListener('mousedown', function (e) {
        //             thElm = th;
        //             startOffset = th.offsetWidth - e.pageX;
        //         });

        //         th.appendChild(grip);
        //     });

        //     document.addEventListener('mousemove', function (e) {
        //     if (thElm) {
        //         thElm.style.width = startOffset + e.pageX + 'px';
        //     }
        //     });

        //     document.addEventListener('mouseup', function () {
        //         thElm = undefined;
        //     });
        // })();  
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
    
</style>


