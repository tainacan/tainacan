<template>
    <div>
        <b-field 
                grouped 
                group-multiline>
                    <button 
                            v-if="selectedItems.length > 0 && items.length > 0 && items[0].current_user_can_edit" 
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
        isLoading: false,
        isOnTheme: false
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
        },
        getDecodedURI(url) {
            return decodeURIComponent(url);
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


