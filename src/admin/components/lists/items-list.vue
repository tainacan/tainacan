<template>
    <div>
        <b-field grouped group-multiline>
            <button v-if="selectedItems.length > 0" class="button field is-danger" @click="deleteSelectedItems()"><span>{{$i18n.get('instruction_delete_selected_items')}} </span><b-icon icon="delete"></b-icon></button>
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

                <b-table-column v-for="(column, index) in tableFields"
                    :key="index"
                    :label="column.label"
                    :visible="column.visible"
                    :width="column.field == 'row_actions' ? 78 : column.field == 'featured_image' ? 55 : undefined ">
                    
                    <router-link tag="span" class="clickable-row" :to="{path: $routerHelper.getItemPath(collectionId, props.row.id)}">
                        <template v-if="column.field != 'featured_image' && column.field != 'row_actions'">
                            {{ props.row.metadata[column.slug].multiple == 'yes' ? props.row.metadata[column.slug].value.join(', ') : props.row.metadata[column.slug].value}}
                        </template>
                    </router-link>
                    
                    <template v-if="column.field == 'featured_image'">
                        <router-link tag="img" class="table-thumb clickable-row" :to="{path: $routerHelper.getItemPath(collectionId, props.row.id)}" :src="props.row[column.slug]"></router-link>
                    </template>
                         
                    <template v-if="column.field == 'row_actions'">
                        <!-- <a id="button-view" @click.prevent.stop="goToItemPage(props.row.id)"><b-icon icon="eye"></a> -->
                        <a id="button-edit" :aria-label="$i18n.getFrom('items','edit_item')" @click="goToItemEditPage(props.row.id)"><b-icon icon="pencil"></a>
                        <a id="button-delete" :aria-label="$i18n.get('label_button_delete')" @click="deleteOneItem(props.row.id)"><b-icon icon="delete"></a>
                    </template>
                </b-table-column>

            </template>
            <!-- Empty state image -->
            <template slot="empty">
                <section class="section">
                    <div class="content has-text-grey has-text-centered">
                        <p>
                            <b-icon
                                icon="inbox"
                                size="is-large">
                            </b-icon>
                        </p>
                        <p>{{$i18n.get('info_no_item_created')}}</p>
                        <router-link
                                    id="button-create" 
                                    tag="button" class="button is-primary"
                                    :to="{ path: $routerHelper.getNewItemPath(collectionId) }">
                            {{ $i18n.getFrom('items', 'new_item') }}
                        </router-link>
                    </div>
                </section>
            </template>
        </b-table>
    </div>
</template>

<script>
import { mapActions, mapGetters } from 'vuex';

export default {
    name: 'ItemsList',
    data(){
        return {
            selectedItems: []
        }
    },
    props: {
        collectionId: Number,
        tableFields: [],
        prefTableFields: [],
        totalItems: 0,
        page: 1,
        itemsPerPage: 12,
        items: [],
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
                    this.deleteItem(itemId).then((res) => {
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
                    }).catch(( error ) => {

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
                        .then((res) => {
                            this.loadItems();
                            this.$toast.open({
                                duration: 3000,
                                message: this.$i18n.get('info_item_deleted'),
                                position: 'is-bottom',
                                type: 'is-secondary',
                                queue: false
                            });
                                                      
                        }).catch((err) => { 
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
    }
}
</script>

<style scoped>

    .table-thumb {
        max-height: 55px !important;
        vertical-align: middle !important;
    }
    .clickable-row{ cursor: pointer !important }

</style>


