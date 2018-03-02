<template>
    <div>
        <section class="section">
             <b-field grouped group-multiline>
                <button v-if="selectedItems.length > 0" class="button field is-danger" @click="deleteSelectedItems()"><span>{{$i18n.get('instruction_delete_selected_items')}} </span><b-icon icon="delete"></b-icon></button>

                <b-dropdown>
                    <button class="button" slot="trigger" :disabled="items.length <= 0">
                        <span>{{$i18n.get('label_table_fields')}}</span>
                        <b-icon icon="menu-down"></b-icon>
                    </button>
                    <b-dropdown-item v-for="(column, index) in tableFields" 
                        :key="index"
                        class="control" custom>
                        <b-checkbox v-model="column.visible"
                            :native-value="column.field">
                            {{ column.label }}
                        </b-checkbox>
                    </b-dropdown-item>
                </b-dropdown>

                <b-select 
                        :label="$i18n.get('label_items_per_page')"
                        v-model="itemsPerPage" 
                        @input="onChangeItemsPerPage" 
                        :disabled="items.length <= 0">
                    <option value="2">2 {{ $i18n.get('label_per_page') }}</option>
                    <option value="10">10 {{ $i18n.get('label_per_page') }}</option>
                    <option value="15">15 {{ $i18n.get('label_per_page') }}</option>
                    <option value="20">20 {{ $i18n.get('label_per_page') }}</option>
                </b-select>
                
            </b-field>
            <b-table ref="itemsTable"
                    :data="items"
                    @selection-change="handleSelectionChange"
                    :checked-rows.sync="selectedItems"
                    checkable
                    :loading="isLoading"
                    hoverable
                    striped
                    selectable
                    paginated
                    backend-pagination
                    :total="totalItems"
                    :per-page="itemsPerPage"
                    @page-change="onPageChange"
                    backend-sorting>
                <template slot-scope="props">
                    <b-table-column v-for="(column, index) in tableFields"
                        :key="index"
                        :label="column.label"
                        :visible="column.visible"
                        :width="column.field == 'row_actions' ? 110 : column.field == 'featured_image' ? 55 : undefined ">
                        <router-link tag="span" class="clickable-row" :to="{path: $routerHelper.getItemPath(collectionId, props.row.id)}">
                        <template v-if="column.field != 'featured_image' && column.field != 'row_actions'">{{ 
                            props.row.metadata[column.slug].multiple == 'yes' ? props.row.metadata[column.slug].value.join(', ') : props.row.metadata[column.slug].value 
                        }}</template>
                        <template v-if="column.field == 'featured_image'">
                            <img class="table-thumb" :src="`${ props.row[column.slug] }`"/>
                        </template>
                        <template v-if="column.field == 'row_actions'">
                            <!-- <a id="button-view" @click.prevent.stop="goToItemPage(props.row.id)"><b-icon icon="eye"></a> -->
                            <a id="button-edit" @click.prevent.stop="goToItemEditPage(props.row.id)"><b-icon icon="pencil"></a>
                            <a id="button-delete" @click.prevent.stop="deleteOneItem(props.row.id)"><b-icon icon="delete"></a>
                        </template>
                        </router-link>
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
                                {{ $i18n.get('new') + ' ' + $i18n.get('item') }}
                            </router-link>
                        </div>
                    </section>
                </template>
            </b-table>
        </section>
    </div>
</template>

<script>
import { mapActions, mapGetters } from 'vuex'

export default {
    name: 'ItemsList',
    data(){
        return {
            selectedItems: [],
            tableFields: [],
            isLoading: false,
            totalItems: 0,
            page: 1,
            itemsPerPage: 2,
            collectionId: Number
        }
    },
    methods: {
        ...mapActions('collection', [
            'fetchItems',
            'deleteItem',
            'fetchFields'
        ]),
        ...mapGetters('collection', [
            'getItems',
            'getFields'
        ]),
        deleteOneItem(itemId) {
            this.$dialog.confirm({
                message: this.$i18n.get('info_warning_item_deleted'),
                onConfirm: () => {
                    this.deleteItem(itemId).then((res) => {
                        this.loadItems();
                        this.$toast.open({
                            duration: 3000,
                            message: this.$i18n.get('info_item_delete'),
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
        onChangeItemsPerPage(value) {
            this.itemsPerPage = value;
            this.loadItems();
        },
        goToItemPage(itemId) {
            this.$router.push(this.$routerHelper.getItemPath(this.collectionId, itemId));
        },
        goToItemEditPage(itemId) {
            this.$router.push(this.$routerHelper.getItemEditPath(this.collectionId, itemId));
        },
        onPageChange(page) {
            this.page = page;
            this.loadItems();
        },
        loadItems() {
            this.isLoading = true;
            this.fetchItems({ 'collectionId': this.collectionId, 'page': this.page, 'itemsPerPage': this.itemsPerPage })
            .then((res) => {
                this.isLoading = false;
                this.totalItems = res.total;
            })
            .catch((error) => {
                this.isLoading = false;
            });
        }
    },
    computed: {
        items(){
            return this.getItems();
        }
    },
    created() {
        this.collectionId = this.$route.params.id;
    },
    mounted(){
        this.loadItems();
        this.fetchFields(this.collectionId).then((res) => {
            let rawFields = res;
            for (let field of rawFields) {
                this.tableFields.push(
                    { label: field.name, field: field.description, slug: field.slug,  visible: true }
                );
            }
            this.tableFields.push({ label: this.$i18n.get('label_actions'), field: 'row_actions', slug: 'actions', visible: true });
        }).catch();
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


