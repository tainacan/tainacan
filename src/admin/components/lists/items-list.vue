<template>
    <div class="table-container">
        <!-- <b-field 
                grouped 
                group-multiline>
                    <button 
                            v-if="selectedItems.length > 0 && items.length > 0 && items[0].current_user_can_edit" 
                            class="button field is-danger" 
                            @click="deleteSelectedItems()">
                        <span>{{ $i18n.get('instruction_delete_selected_items') }} </span><b-icon icon="delete"/>
                    </button>
        </b-field> -->
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
                        id="mass-actions-dropdown">
                    <button
                            class="button is-white"
                            slot="trigger">
                        <span>{{ $i18n.get('label_mass_actions') }}</span>
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
                        <th 
                                v-if="!isOnTheme"
                                class="checkbox-cell">
                            &nbsp;
                            <!-- nothing to show on header -->
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

                            <p
                                    v-if="column.field !== 'row_thumbnail' &&
                                     column.field !== 'row_actions' &&
                                      column.field !== 'row_creation'"

                                    v-html="renderMetadata( item.metadata[column.slug] )"/>

                            <span v-if="column.field == 'row_thumbnail'">
                                <img 
                                        class="table-thumb" 
                                        :src="item[column.slug]">
                            </span> 
                            <p 
                                    v-if="column.field == 'row_creation'"
                                    v-html="getCreationHtml(item)" />
                        </td>

                        <!-- Actions -->
                        <td 
                                v-if="item.current_user_can_edit && !isOnTheme"
                                class="column-default-width actions-cell">
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
<!--        
        <b-table 
                ref="itemsTable"
                :data="items"
                @selection-change="handleSelectionChange"
                :checked-rows.sync="selectedItems"
                :checkable="!isOnTheme"
                :loading="isLoading"
                hoverable
                :selectable="!isOnTheme"
                backend-sorting>
            <template slot-scope="props">
                <b-table-column 
                        v-for="(column, index) in tableFields"
                        v-if="column.field != 'row_actions' || (column.field == 'row_actions' && props.row.current_user_can_edit && !isOnTheme)"
                        :key="index"
                        :custom-key="column.slug"
                        :label="column.name"
                        :visible="column.display"
                        :class="column.field == 'row_creation' ? 'row-creation' : ''"
                        :width="column.field == 'row_actions' ? 78 : column.field == 'row_thumbnail' ? 55 : undefined ">
                        
                    <template v-if="column.field != 'row_thumbnail' && column.field != 'row_actions' && column.field != 'row_creation'">
                        <span
                                class="clickable-row"
                                v-if="!isOnTheme && props.row.metadata[column.slug].value_as_html == props.row.metadata[column.slug].value_as_string" 
                                @click.prevent="goToItemPage(props.row.id)"
                                v-html="renderMetadata( props.row.metadata[column.slug] )" />
                        <span
                                class="clickable-row"
                                v-if="!isOnTheme && props.row.metadata[column.slug].value_as_html != props.row.metadata[column.slug].value_as_string"  
                                v-html="renderMetadata( props.row.metadata[column.slug] )" />
                        <a 
                            v-if="isOnTheme"
                            :href="getDecodedURI(props.row.url)"
                            v-html="renderMetadata( props.row.metadata[column.slug] )" />
       
                    </template>
                    
                    <template v-if="column.field == 'row_thumbnail'">
                        <router-link 
                                tag="img" 
                                class="table-thumb clickable-row" 
                                :to="{path: $routerHelper.getItemPath(collectionId, props.row.id)}" 
                                :src="props.row[column.slug]"/>
                    </template>

                    <template 
                            class="row-creation" 
                            v-if="column.field == 'row_creation'">
                        <router-link 
                                class="clickable-row" 
                                v-html="getCreationHtml(props.row)"
                                tag="span" 
                                :to="{path: $routerHelper.getItemPath(collectionId, props.row.id)}"/>
                    </template>
                         
                    <template v-if="column.field == 'row_actions'">
                    
                        <a 
                                id="button-edit" 
                                :aria-label="$i18n.getFrom('items','edit_item')" 
                                @click="goToItemEditPage(props.row.id)">
                            <b-icon 
                                    type="is-gray" 
                                    icon="pencil"/></a>
                        <a 
                                id="button-delete" 
                                :aria-label="$i18n.get('label_button_delete')" 
                                @click="deleteOneItem(props.row.id)">
                            <b-icon 
                                    type="is-gray" 
                                    icon="delete"/></a>
                    </template>
                </b-table-column>
            </template>
        </b-table> 
-->
    </div>
</template>

<script>
import { mapActions } from 'vuex';

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
            console.log("OI")
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
        
        padding: 6px 14px 0px 14px;
        z-index: 9999;
        position: relative;
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
        }

        &.selectable-table th:nth-child(2), &.selectable-table td:nth-child(2) {
            padding-left: 54px;
        }
        
        .checkbox-cell {
            width: 44px;
            height: 58px;
            padding: 0;
            position: absolute !important;
            left: 8.333333%;
            visibility: hidden;
            display: flex;
            justify-content: space-around;
            z-index: 9;

            &::before {
                box-shadow: inset 53px 0 10px -12px #222;
                content: " ";
                width: 64px;
                height: 100%;
                position: absolute;
                left: 0;
            }

            .checkbox {  
                border-radius: 0px;
                background-color: white;
                padding: 10px 10px 10px 14px;
                width: 100%;
                height: 100%; 
            }
            &.is-selecting {
                visibility: visible; 
            }
        }
        // Only to be used in case we can implement Column resizing
        // th:not(:last-child) {
        //     border-right: 1px solid $tainacan-input-background !important;
        // }

        .thumbnail-cell {
            width: 58px;
        }
  
        tbody {
            tr {
                cursor: pointer;
                background-color: transparent;

                &.selected-row { 
                    background-color: $primary-lighter !important; 
                    .checkbox-cell .checkbox, .actions-cell .actions-container {
                        background-color: $primary-lighter !important;
                    }
                }
                td {
                    height: 58px;
                    max-height: 58px;
                    padding: 10px;
                    vertical-align: middle;
                    line-height: 12px;
                    border: none;
                    p { 
                        font-size: 14px;
                        margin: 0px; 
                    }
                    
                }
                td.column-default-width{
                    max-width: 350px;
                    p {
                        text-overflow: ellipsis;
                        overflow-x: hidden;
                        white-space: nowrap;
                    }
                }
                img.table-thumb {
                    max-height: 38px !important;
                    border-radius: 3px;
                }

                td.table-creation p {
                    color: $gray-light;
                    font-size: 11px;
                    line-height: 1.5;
                }

                td.actions-cell {
                    padding: 0px;
                    visibility: hidden;
                    position: absolute;
                    right: 8.333333%;
                    display: none;
                    
                    .actions-container {
                        position: relative;
                        padding: 10px;
                        height: 100%;
                        z-index: 9;
                        background-color: $tainacan-input-background; 
                    }

                    a .icon {
                        margin: 8px;
                    }

                }

                &:hover {
                    background-color: $tainacan-input-background;
                    cursor: pointer;

                    .checkbox-cell {
                        visibility: visible; 
                        .checkbox { background-color: $tainacan-input-background; }
                    }
                    .actions-cell {
                        visibility: visible;
                        display: block;

                        &::after {
                            box-shadow: inset -113px 0 17px -17px #222;
                            content: " ";
                            width: 125px;
                            height: 100%;
                            position: absolute;
                            right: 0;
                            top: 0;
                        }
                    }

                }
            }
        }
    }

</style>


