<template>
    <div>
        <b-field 
                grouped 
                group-multiline>
                    <button 
                            v-if="selectedItems.length > 0" 
                            class="button field is-danger" 
                            @click="deleteSelectedItems()">
                        <span>{{ $i18n.get('instruction_delete_selected_items') }} </span><b-icon icon="delete"/>
                    </button>
        </b-field>
        <b-table 
                ref="itemsTable"
                :data="items"
                @selection-change="handleSelectionChange"
                :checked-rows.sync="selectedItems"
                checkable
                :loading="isLoading"
                hoverable
                striped
                selectable
                backend-sorting>
            <template slot-scope="props">
                <b-table-column 
                        v-for="(column, index) in tableFields"
                        :key="index"
                        :custom-key="column.slug"
                        :label="column.name"
                        :visible="column.visible"
                        :class="column.field == 'row_creation' ? 'row-creation' : ''"
                        :width="column.field == 'row_actions' ? 78 : column.field == 'row_thumbnail' ? 55 : undefined ">
                        
                    <template>
                        <span
                                class="clickable-row" 
                                @click.prevent="goToItemPage(props.row.id)"
                                v-if="column.field != 'row_thumbnail' && column.field != 'row_actions' && column.field != 'row_creation'"
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
                        <!-- <a id="button-view" @click.prevent.stop="goToItemPage(props.row.id)"><b-icon icon="eye"></a> -->
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
    </div>
</template>

<script>
import { mapActions } from 'vuex';
import moment from 'moment';

export default {
    name: 'ItemsList',
    data(){
        return {
            selectedItems: []
        }
    },
    props: {
        collectionId: Number,
        tableFields: Array,
        items: Array,
        isLoading: false
    },
    methods: {
        ...mapActions('collection', [
            'deleteItem',
        ]),
        deleteOneItem(itemId) {
            this.$dialog.confirm({
                message: this.$i18n.get('info_warning_item_delete'),
                onConfirm: () => {
                    this.deleteItem(itemId).then(() => {
                        this.loadItems();
                        this.$toast.open({
                            duration: 3000,
                            message: this.$i18n.get('info_item_deleted'),
                            position: 'is-bottom',
                            type: 'is-secondary',
                            queue: true
                        });
                        for (let i = 0; i < this.selectedItems.length; i++) {
                            if (this.selectedItems[i].id == this.itemId)
                                this.selectedItems.splice(i, 1);
                        }
                    }).catch(() => {

                        this.$toast.open({ 
                            duration: 3000,
                            message: this.$i18n.get('info_error_deleting_item'),
                            position: 'is-bottom',
                            type: 'is-danger',
                            queue: true
                        })
                    });
                }
            });
        },
        deleteSelectedItems() {
            this.$dialog.confirm({
                message: this.$i18n.get('info_selected_items_delete'),
                onConfirm: () => {

                    for (let item of this.selectedItems) {
                        this.deleteItem(item.id)
                        .then(() => {
                            this.loadItems();
                            this.$toast.open({
                                duration: 3000,
                                message: this.$i18n.get('info_item_deleted'),
                                position: 'is-bottom',
                                type: 'is-secondary',
                                queue: false
                            });
                                                      
                        }).catch(() => { 
                            this.$toast.open({
                                duration: 3000,
                                message: this.$i18n.get('info_error_deleting_item'),
                                position: 'is-bottom',
                                type: 'is-danger',
                                queue: false
                            });
                        });
                    }

                    this.selectedItems =  [];
                }
            });
        },
        handleSelectionChange() {
        },
        goToItemPage(itemId) {
            this.$router.push(this.$routerHelper.getItemPath(this.collectionId, itemId));
        },
        goToItemEditPage(itemId) {
            this.$router.push(this.$routerHelper.getItemEditPath(this.collectionId, itemId));
        },
        renderMetadata( metadata ){

            if( !metadata )
                return '';
            else
                return metadata.value_as_html;
        },
        getCreationHtml(item) {
            return this.$i18n.get('info_created_by') + item['author_name'] + '<br>' + this.$i18n.get('info_date') + moment( item['creation_date'], 'YYYY-MM-DD').format('DD/MM/YYYY');
        }
    }
}
</script>

<style lang="scss" scoped>

    @import "../../scss/_variables.scss";

    .table-thumb {
        max-height: 55px !important;
    }
    td {
        vertical-align: middle !important;
    }

    .row-creation span {
        color: $gray-light;
        font-size: 0.75em;
        line-height: 1.5
    }

    .clickable-row{ cursor: pointer !important }

</style>


