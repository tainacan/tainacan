<template>
    <div>
        <section class="section">
             <b-field grouped group-multiline>
                <button v-if="selectedItems.length > 0" class="button field is-danger" @click="deleteSelectedItems()"><span>Deletar itens selecionados </span><b-icon icon="delete"></b-icon></button>

                <b-dropdown>
                    <button class="button" slot="trigger">
                        <span>Campos da tabela</span>
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
                        v-model="itemsPerPage" 
                        @input="onChangeItemsPerPage" 
                        :disabled="items.length <= 0">
                    <option value="2">2 itens por página</option>
                    <option value="10">10 itens por página</option>
                    <option value="15">15 itens por página</option>
                    <option value="20">20 itens por página</option>
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
                        <template v-if="column.field != 'featured_image' && column.field != 'row_actions'">{{ 
                            props.row.metadata[column.slug].multiple == 'yes' ? props.row.metadata[column.slug].value.join(', ') : props.row.metadata[column.slug].value 
                        }}</template>
                        <template v-if="column.field == 'featured_image'">
                            <img class="table-thumb" :src="`${ props.row[column.slug] }`"/>
                        </template>
                        <template v-if="column.field == 'row_actions'">
                            <a id="button-view" @click.prevent.stop="goToItemPage(props.row.id)"><b-icon icon="eye"></a>
                            <a id="button-edit" @click.prevent.stop="goToItemEditPage(props.row.id)"><b-icon icon="pencil"></a>
                            <a id="button-delete" @click.prevent.stop="deleteOneItem(props.row.id)"><b-icon icon="delete"></a>
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
                            <p>Nenhum item ainda nesta coleção.</p>
                            <router-link
                                        id="button-create" 
                                        tag="button" class="button is-primary"
                                        :to="{ path: `/collections/${collectionId}/items/new` }">
                                Criar Item
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
                message: 'Deseja realmente deletar este Item?',
                onConfirm: () => {
                    this.deleteItem(itemId).then((res) => {
                        this.loadItems();
                        this.$toast.open({
                            duration: 3000,
                            message: `Item deletado`,
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
                            message: `Erro ao deletar item`,
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
                message: 'Deseja realmente todos os itens selecionados?',
                onConfirm: () => {

                    for (let item of this.selectedItems) {
                        this.deleteItem(item.id)
                        .then((res) => {
                            this.loadItems();
                            this.$toast.open({
                                duration: 3000,
                                message: `Item deletado`,
                                position: 'is-bottom',
                                type: 'is-secondary',
                                queue: false
                            });
                                                      
                        }).catch((err) => { 
                            this.$toast.open({
                                duration: 3000,
                                message: `Erro ao deletar item`,
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
            this.$router.push(`/collections/${this.collectionId}/items/${itemId}`);
        },
        goToItemEditPage(itemId) {
            this.$router.push(`/collections/${this.collectionId}/items/${itemId}/edit`);
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
            this.tableFields.push({ label: 'Ações', field: 'row_actions', slug: 'actions', visible: true });
        }).catch();
    }

}
</script>

<style scoped>

    .table-thumb {
        max-height: 55px !important;
        vertical-align: middle !important;
    }

    tr { cursor: pointer }

</style>


