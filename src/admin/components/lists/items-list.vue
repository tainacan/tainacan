<template>
    <div class="table-container">

        <div 
                v-if="!isOnTheme"
                class="selection-control">
            <div class="field select-all is-pulled-left">
                <span>
                    <b-checkbox
                            @click.native="selectAllItemsOnPage()"
                            :value="allItemsOnPageSelected">{{ $i18n.get('label_select_all_items_page') }}</b-checkbox>
                </span>
            </div>
            <div class="field is-pulled-right">
                <b-dropdown
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
            <table 
                    :class="{'selectable-table': !isOnTheme }"
                    class="table">
                <thead>
                    <tr>
                        <!-- Checking list -->
                        <th v-if="!isOnTheme">
                            &nbsp;
                            <!-- nothing to show on header for checkboxes -->
                        </th>
                        <!-- Displayed Fields -->
                        <th 
                                v-for="(column, index) in tableFields"
                                :key="index"
                                v-if="column.field != 'row_actions' && column.display"
                                :class="{'thumbnail-cell': column.field == 'row_thumbnail'}"
                                :custom-key="column.slug">
                            <div class="th-wrap">{{ column.name }}</div>
                        </th>
                        <th     
                                class="actions-header"
                                v-if="!isOnTheme">
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
                                v-if="!isOnTheme"
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
                                :aria-label="column.field != 'row_thumbnail' &&
                                 column.field != 'row_actions' &&
                                  column.field != 'row_creation' ? column.name + '' + item.metadata[column.slug].value_as_string : ''"
                                class="column-default-width"
                                :class="{
                                        'thumbnail-cell': column.field == 'row_thumbnail', 
                                        'table-creation': column.field == 'row_creation'}"
                                @click="goToItemPage(item)">

                            <data-and-tooltip
                                    v-if="column.field !== 'row_thumbnail' &&
                                            column.field !== 'row_actions' &&
                                            column.field !== 'row_creation'"
                                    :data="renderMetadata( item.metadata[column.slug] )"/>

                            <span v-if="column.field == 'row_thumbnail'">
                                <img 
                                        class="table-thumb" 
                                        :src="item[column.slug].thumb">
                            </span> 
                            <p 
                                    v-if="column.field == 'row_creation'"
                                    v-html="getCreationHtml(item)" />
                        </td>

                        <!-- Actions -->
                        <td 
                                v-if="item.current_user_can_edit && !isOnTheme"
                                class="column-default-width actions-cell"
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
                                            icon="delete"/>
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
import DataAndTooltip from '../other/data-and-tooltip.vue'

export default {
    name: 'ItemsList',
    data(){
        return {
            allItemsOnPageSelected: false,
            isSelectingItems: false,
            selectedItems: [],
        }
    },
    props: {
        collectionId: Number,
        tableFields: Array,
        items: Array,
        isLoading: false,
        isOnTheme: false
    },
    components: {
        DataAndTooltip
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
            this.$dialog.confirm({
                message: this.$i18n.get('info_warning_item_delete'),
                onConfirm: () => {
                    this.deleteItem(itemId)
                    .then(() => {
                    //     this.$toast.open({
                    //         duration: 3000,
                    //         message: this.$i18n.get('info_item_deleted'),
                    //         position: 'is-bottom',
                    //         type: 'is-secondary',
                    //         queue: true
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
                    //         queue: true
                    //     })
                    });
                }
            });
        },
        deleteSelectedItems() {
            this.$dialog.confirm({
                message: this.$i18n.get('info_warning_selected_items_delete'),
                onConfirm: () => {

                    for (let i = 0; i < this.selectedItems.length; i++) {
                        if (this.selectedItems[i]) {
                            this.deleteItem(this.items[i].id)
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
            });
        },
        goToItemPage(item) {
            if (this.isOnTheme)
                window.location.href = item.url;   
            else
                this.$router.push(this.$routerHelper.getItemPath(this.collectionId, item.id));
        },
        goToItemEditPage(itemId) {
            this.$router.push(this.$routerHelper.getItemEditPath(this.collectionId, itemId));
        },
        renderMetadata(metadata) {

            if (!metadata) {
                return '';
            } else if (metadata.date_i18n) {
                return metadata.date_i18n;
            } else {
                return metadata.value_as_html;
            }
        },
        getCreationHtml(item) {
            return this.$i18n.get('info_created_by') + item['author_name'] + '<br>' + this.$i18n.get('info_date') + item['creation_date'];
        },
        getDecodedURI(url) {
            return decodeURIComponent(url);
        }
    }
}
</script>

<style lang="scss" scoped>

    @import "../../scss/_variables.scss";

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

    .table {
        width: 100%;
        border-collapse: separate;

        th {
            position: sticky;
            position: -webkit-sticky;
            background-color: white;
            border-bottom: 1px solid $tainacan-input-background;
            top: 0px;
            z-index: 9;
            padding: 10px;
            vertical-align: bottom;

            &.actions-header {
                min-width: 8.333333333%;
            }
        }

        .checkbox-cell {
            min-width: 40px;
            width: 40px;
            padding: 0;
            position: sticky !important;
            position: -webkit-sticky !important;
            left: 0;
            top: auto;
            display: table-cell;

            &::before {
                box-shadow: inset 50px 0 10px -12px #222;
                content: " ";
                width: 50px;
                height: 100%;
                position: absolute;
                left: 0;
                top: 0;
                visibility: hidden;
            }

            label.checkbox {  
                border-radius: 0px;
                background-color: white;
                padding: 0;
                width: 100%;
                height: 100%; 
                display: flex;
                justify-content: center;
                visibility: hidden;
            }
            label.control-label {
                display: none;
            }
            &.is-selecting {
                .checkbox { visibility: visible; } 
                &::before { visibility: visible !important; }
            }
        }
        // Only to be used in case we can implement Column resizing
        // th:not(:last-child) {
        //     border-right: 1px solid $tainacan-input-background !important;
        // }

        .thumbnail-cell {
            width: 60px;
        }
  
        tbody {
            tr {
                cursor: pointer;
                background-color: transparent;

                &.selected-row { 
                    background-color: $primary-lighter; 
                    .checkbox-cell .checkbox, .actions-cell .actions-container {
                        background-color: $primary-lighter;
                    }
                }
                td {
                    height: 60px;
                    max-height: 60px;
                    padding: 10px;
                    vertical-align: middle;
                    line-height: 12px;
                    border: none;
                    p { 
                        font-size: 14px;
                        margin: 0px; 
                    }
                    
                }
                td.column-default-width {
                    max-width: 300px;
                    p {
                        text-overflow: ellipsis;
                        overflow-x: hidden;
                        white-space: nowrap;
                    }
                }
                img.table-thumb {
                    max-height: 37px !important;
                    border-radius: 3px;
                }

                td.table-creation p {
                    color: $gray-light;
                    font-size: 11px;
                    line-height: 1.5;
                }

                td.actions-cell {
                    padding: 0px;
                    position: sticky !important;
                    position: -webkit-sticky !important;
                    right: 0px;
                    top: auto;
                    width: 80px;

                    .actions-container {
                        visibility: hidden;
                        display: flex;
                        position: relative;
                        padding: 0;
                        height: 100%;
                        width: 80px;
                        z-index: 9;
                        background-color: transparent; 
                        float: right;
                    }

                    a {
                        margin: auto;
                        font-size: 18px !important;
                    }

                }

                &:hover {
                    background-color: $tainacan-input-background !important;
                    cursor: pointer;

                    .checkbox-cell {
                        &::before { visibility: visible; }
                        .checkbox { 
                            visibility: visible; 
                            background-color: $tainacan-input-background !important; 
                        }
                    }
                    .actions-cell {
                        .actions-container {
                            visibility: visible;
                            background: $tainacan-input-background !important;
                        }

                        &::after {
                            box-shadow: inset -97px 0 17px -21px #222;
                            content: " ";
                            width: 100px;
                            height: 100%;
                            position: absolute;
                            right: 0px;
                            top: 0;
                        }
                    }

                }
            }
        }
    }

</style>


