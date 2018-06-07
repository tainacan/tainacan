<template>
    <div class="table-container">

        <div class="selection-control">
            <div class="field select-all is-pulled-left">
                <span>
                    <b-checkbox
                            @click.native="selectAllItemsOnPage()"
                            :value="allItemsOnPageSelected">{{ $i18n.get('label_select_all_items_page') }}</b-checkbox>
                </span>
            </div>
            <div class="field is-pulled-right">
                <b-dropdown
                        :mobile-modal="false"
                        position="is-bottom-left"
                        v-if="items.length > 0 && items[0].current_user_can_edit"
                        :disabled="!isSelectingItems"
                        id="bulk-actions-dropdown">
                    <button
                            class="button is-white"
                            slot="trigger">
                        <span>{{ $i18n.get('label_bulk_actions') }}</span>
                        <b-icon icon="menu-down"/>
                    </button> 

                    <b-dropdown-item 
                            @click="deleteSelectedItems()"
                            id="item-delete-selected-items">
                        {{ $i18n.get('label_delete_selected_items') }}
                    </b-dropdown-item>
                    <b-dropdown-item disabled>{{ $i18n.get('label_edit_selected_items') + ' (Not ready)' }}
                    </b-dropdown-item>
                </b-dropdown>
            </div>
        </div>

        <div class="table-wrapper">
            
            <!-- GRID VIEW MODE -->
            <div
                    class="tainacan-grid-container"
                    v-if="viewMode == 'grid'">
                <div 
                        :key="index"
                        v-for="(item, index) of items"
                        :class="{ 'selected-grid-item': selectedItems[index] }"
                        class="tainacan-grid-item">
                    
                    <!-- Checkbox -->
                    <div 
                            :class="{ 'is-selecting': isSelectingItems }"
                            class="grid-item-checkbox">
                        <b-checkbox 
                                size="is-small"
                                v-model="selectedItems[index]"/> 
                    </div>

                    <!-- Title -->
                    <p 
                            v-for="(column, index) in tableFields"
                            :key="index"
                            v-if="column.display && column.field_type_object != undefined && (column.field_type_object.related_mapped_prop == 'title')"
                            class="metadata-title"
                            @click="goToItemPage(item)"
                            v-html="item.metadata != undefined ? renderMetadata(item.metadata, column) : ''"/>                             
                    
                    <!-- Thumbnail -->
                    <a
                            v-if="item.thumbnail != undefined"
                            @click="goToItemPage(item)">
                        <img :src="item['thumbnail'].medium ? item['thumbnail'].medium : thumbPlaceholderPath">
                    </a>

                    <!-- Actions -->
                    <div 
                            v-if="item.current_user_can_edit"
                            class="actions-area"
                            :label="$i18n.get('label_actions')">
                        <a 
                                id="button-edit"   
                                :aria-label="$i18n.getFrom('items','edit_item')" 
                                @click.prevent.stop="goToItemEditPage(item.id)">
                            <b-icon
                                    type="is-secondary" 
                                    icon="pencil"/>
                        </a>
                        <a 
                                id="button-delete" 
                                :aria-label="$i18n.get('label_button_delete')" 
                                @click.prevent.stop="deleteOneItem(item.id)">
                            <b-icon 
                                    type="is-secondary" 
                                    :icon="!isOnTrash ? 'delete' : 'delete-forever'"/>
                        </a>
                    </div>
            
                </div>
            </div>

            <!-- CARDS VIEW MODE -->
            <div
                    class="tainacan-cards-container"
                    v-if="viewMode == 'cards'">
                <div 
                        :key="index"
                        v-for="(item, index) of items"
                        :class="{ 'selected-card': selectedItems[index] }"
                        class="tainacan-card">
                    
                    <!-- Checkbox -->
                    <div 
                            :class="{ 'is-selecting': isSelectingItems }"
                            class="card-checkbox">
                        <b-checkbox 
                                size="is-small"
                                v-model="selectedItems[index]"/> 
                    </div>
                    
                    <!-- Title -->
                    <p 
                            v-for="(column, index) in tableFields"
                            :key="index"
                            v-if="column.display && column.field_type_object != undefined && (column.field_type_object.related_mapped_prop == 'title')"
                            class="metadata-title"
                            @click="goToItemPage(item)"
                            v-html="item.metadata != undefined ? renderMetadata(item.metadata, column) : ''" />                             
                    
                    <!-- Actions -->
                    <div 
                            v-if="item.current_user_can_edit"
                            class="actions-area"
                            :label="$i18n.get('label_actions')">
                        <a 
                                id="button-edit"   
                                :aria-label="$i18n.getFrom('items','edit_item')" 
                                @click.prevent.stop="goToItemEditPage(item.id)">
                            <b-icon
                                    type="is-secondary" 
                                    icon="pencil"/>
                        </a>
                        <a 
                                id="button-delete" 
                                :aria-label="$i18n.get('label_button_delete')" 
                                @click.prevent.stop="deleteOneItem(item.id)">
                            <b-icon 
                                    type="is-secondary" 
                                    :icon="!isOnTrash ? 'delete' : 'delete-forever'"/>
                        </a>
                    </div>
                    
                    <!-- Remaining metadata -->  
                    <div    
                            class="media"
                            @click="goToItemPage(item)">
                        <a 
                                v-if="item.thumbnail != undefined"
                                @click="goToItemPage(item)">
                            <img :src="item['thumbnail'].medium_large ? item['thumbnail'].medium_large : thumbPlaceholderPath">  
                        </a>

                        <div class="list-metadata media-body">
                            <span 
                                    v-for="(column, index) in tableFields"
                                    :key="index"
                                    v-if="column.display && column.slug != 'thumbnail' && column.field_type_object != undefined && (column.field_type_object.related_mapped_prop != 'title')">
                                <h3 class="metadata-label">{{ column.name }}</h3>
                                <p 
                                        v-html="item.metadata != undefined ? renderMetadata(item.metadata, column) : ''"
                                        class="metadata-value"/>
                            </span>
                        </div>
                    </div>
               
                </div>
            </div>
            
            <!-- TABLE VIEW MODE -->
            <table 
                    v-if="viewMode == 'table'"
                    class="tainacan-table">
                <thead>
                    <tr>
                        <!-- Checking list -->
                        <th>
                            &nbsp;
                            <!-- nothing to show on header for checkboxes -->
                        </th>
                        <!-- Displayed Fields -->
                        <th 
                                v-for="(column, index) in tableFields"
                                :key="index"
                                v-if="column.display"
                                class="column-default-width"
                                :class="{
                                        'thumbnail-cell': column.field == 'row_thumbnail', 
                                        'column-small-width' : column.field_type_object != undefined ? (column.field_type_object.className == 'Tainacan\\Field_Types\\Date' || column.field_type_object.className == 'Tainacan\\Field_Types\\Numeric') : false,
                                        'column-medium-width' : column.field_type_object != undefined ? (column.field_type_object.className == 'Tainacan\\Field_Types\\Selectbox' || column.field_type_object.className == 'Tainacan\\Field_Types\\Category' || column.field_type_object.className == 'Tainacan\\Field_Types\\Compound') : false,
                                        'column-large-width' : column.field_type_object != undefined ? (column.field_type_object.className == 'Tainacan\\Field_Types\\Textarea') : false,
                                }"
                                :custom-key="column.slug">
                            <div class="th-wrap">{{ column.name }}</div>
                        </th>
                        <th class="actions-header">
                            &nbsp;
                            <!-- nothing to show on header for actions cell-->
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr     
                            :class="{ 'selected-row': selectedItems[index] }"
                            :key="index"
                            v-for="(item, index) of items">
                        <!-- Checking list -->
                        <td 
                                :class="{ 'is-selecting': isSelectingItems }"
                                class="checkbox-cell">
                            <b-checkbox 
                                    size="is-small"
                                    v-model="selectedItems[index]"/> 
                        </td>

                        <!-- Item Displayed Metadata -->
                        <td 
                                :key="index"    
                                v-for="(column, index) in tableFields"
                                v-if="column.display"
                                :label="column.name" 
                                class="column-default-width"
                                :class="{
                                        'thumbnail-cell': column.field == 'row_thumbnail',
                                        'column-main-content' : column.field_type_object != undefined ? (column.field_type_object.related_mapped_prop == 'title') : false,
                                        'column-needed-width column-align-right' : column.field_type_object != undefined ? (column.field_type_object.className == 'Tainacan\\Field_Types\\Numeric') : false,
                                        'column-small-width' : column.field_type_object != undefined ? (column.field_type_object.className == 'Tainacan\\Field_Types\\Date' || column.field_type_object.className == 'Tainacan\\Field_Types\\Numeric') : false,
                                        'column-medium-width' : column.field_type_object != undefined ? (column.field_type_object.className == 'Tainacan\\Field_Types\\Selectbox' || column.field_type_object.className == 'Tainacan\\Field_Types\\Category' || column.field_type_object.className == 'Tainacan\\Field_Types\\Compound') : false,
                                        'column-large-width' : column.field_type_object != undefined ? (column.field_type_object.className == 'Tainacan\\Field_Types\\Textarea') : false,
                                }"
                                @click="goToItemPage(item)">

                            <!-- <data-and-tooltip
                                    v-if="column.field !== 'row_thumbnail' &&
                                            column.field !== 'row_actions' &&
                                            column.field !== 'row_creation'"
                                    :data="renderMetadata(item.metadata, column)"/> -->
                            <p
                                    v-tooltip="{
                                        content: renderMetadata(item.metadata, column),
                                        html: true,
                                        autoHide: false,
                                        placement: 'auto-start'
                                    }"
                                    v-if="item.metadata != undefined &&
                                          column.field !== 'row_thumbnail' &&
                                          column.field !== 'row_actions' &&
                                          column.field !== 'row_creation' &&
                                          column.field !== 'row_author'"
                                    v-html="renderMetadata(item.metadata, column)"/>

                            <span v-if="column.field == 'row_thumbnail'">
                                <img 
                                        class="table-thumb" 
                                        :src="item['thumbnail'].thumb ? item['thumbnail'].thumb : thumbPlaceholderPath">
                            </span> 
                            <p 
                                    v-tooltip="{
                                        content: item[column.slug],
                                        html: true,
                                        autoHide: false,
                                        placement: 'auto-start'
                                    }"
                                    v-if="column.field == 'row_author' || column.field == 'row_creation'">
                                    {{ item[column.slug] }}
                            </p>

                        </td>

                        <!-- Actions -->
                        <td 
                                v-if="item.current_user_can_edit"
                                class="actions-cell"
                                :label="$i18n.get('label_actions')">
                            <div class="actions-container">
                                <a 
                                        id="button-edit"   
                                        :aria-label="$i18n.getFrom('items','edit_item')" 
                                        @click.prevent.stop="goToItemEditPage(item.id)">
                                    <b-icon
                                            type="is-secondary" 
                                            icon="pencil"/>
                                </a>
                                <a 
                                        id="button-delete" 
                                        :aria-label="$i18n.get('label_button_delete')" 
                                        @click.prevent.stop="deleteOneItem(item.id)">
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
    name: 'ItemsList',
    data(){
        return {
            allItemsOnPageSelected: false,
            isSelectingItems: false,
            selectedItems: [],
            thumbPlaceholderPath: tainacan_plugin.base_url + '/admin/images/placeholder_square.png',
        }
    },
    props: {
        collectionId: Number,
        tableFields: Array,
        items: Array,
        isLoading: false,
        isOnTrash: false,
        viewMode: 'table'
    },
    mounted() {
        this.selectedItems = [];
        for (let i = 0; i < this.items.length; i++)
            this.selectedItems.push(false);
    },
    watch: {
        selectedItems() {
            let allSelected = true;
            let isSelecting = false;
            for (let i = 0; i < this.selectedItems.length; i++) {
                if (this.selectedItems[i] == false) {
                    allSelected = false;
                } else {
                    isSelecting = true;
                }
            }
            this.allItemsOnPageSelected = allSelected;
            this.isSelectingItems = isSelecting;
        }
    },
    methods: {
        ...mapActions('collection', [
            'deleteItem',
        ]),
        selectAllItemsOnPage() {
            for (let i = 0; i < this.selectedItems.length; i++) 
                this.selectedItems.splice(i, 1, !this.allItemsOnPageSelected);
        },
        deleteOneItem(itemId) {
            this.$modal.open({
                parent: this,
                component: CustomDialog,
                props: {
                    icon: 'alert',
                    title: this.$i18n.get('label_warning'),
                    message: this.isOnTrash ? this.$i18n.get('info_warning_item_delete') : this.$i18n.get('info_warning_item_trash'),
                    onConfirm: () => {
                        this.deleteItem({ itemId: itemId, isPermanently: this.isOnTrash })
            //         .then(() => {
            //         //     this.$toast.open({
            //         //         duration: 3000,
            //         //         message: this.$i18n.get('info_item_deleted'),
            //         //         position: 'is-bottom',
            //         //         type: 'is-secondary',
            //         //         queue: true
            //         //     });
            //             for (let i = 0; i < this.selectedItems.length; i++) {
            //                 if (this.selectedItems[i].id == itemId)
            //                     this.selectedItems.splice(i, 1);
            //             }
            //         }).catch(() => {

            //         //     this.$toast.open({ 
            //         //         duration: 3000,
            //         //         message: this.$i18n.get('info_error_deleting_item'),
            //         //         position: 'is-bottom',
            //         //         type: 'is-danger',
            //         //         queue: true
            //         //     })
            //         });
                    }
                }
            });
        },
        deleteSelectedItems() {
            this.$modal.open({
                parent: this,
                component: CustomDialog,
                props: {
                    icon: 'alert',
                    title: this.$i18n.get('label_warning'),
                    message: this.isOnTrash ? this.$i18n.get('info_warning_selected_items_delete') : this.$i18n.get('info_warning_selected_items_trash'),
                    onConfirm: () => {

                        for (let i = 0; i < this.selectedItems.length; i++) {
                            if (this.selectedItems[i]) {
                                this.deleteItem({ itemId: this.items[i].id, isPermanently: this.isOnTrash })
                                .then(() => {
                                //     this.$toast.open({
                                //         duration: 3000,
                                //         message: this.$i18n.get('info_item_deleted'),
                                //         position: 'is-bottom',
                                //         type: 'is-secondary',
                                //         queue: false
                                //     });
                                    for (let i = 0; i < this.selectedItems.length; i++) {
                                        if (this.selectedItems[i].id == this.itemId)
                                            this.selectedItems.splice(i, 1);
                                    }
                                                            
                                }).catch(() => { 
                                //     this.$toast.open({
                                //         duration: 3000,
                                //         message: this.$i18n.get('info_error_deleting_item'),
                                //         position: 'is-bottom',
                                //         type: 'is-danger',
                                //         queue: false
                                //     });
                                });
                            }
                        }
                        this.allItemsOnPageSelected = false;
                    }
                }
            });
        },
        goToItemPage(item) {
            this.$router.push(this.$routerHelper.getItemPath(this.collectionId, item.id));
        },
        goToItemEditPage(itemId) {
            this.$router.push(this.$routerHelper.getItemEditPath(this.collectionId, itemId));
        },
        renderMetadata(itemMetadata, column) {

            let metadata = itemMetadata[column.slug] != undefined ? itemMetadata[column.slug] : false;

            if (!metadata) {
                return '';
            } else if (metadata.date_i18n) {
                return metadata.date_i18n;
            } else {
                return metadata.value_as_html;
            }
        }
    }
}
</script>

<style lang="scss" scoped>

    @import "../../scss/_variables.scss";
    @import "../../scss/_view-mode-grid.scss";
    @import "../../scss/_view-mode-cards.scss";

    .selection-control {
        
        padding: 6px 0px 0px 13px;
        background: white;
        height: 40px;

        .select-all {
            color: $gray-light;
            font-size: 14px;
            &:hover {
                color: $gray-light;
            }
        }
    }

</style>


