<template>
    <div>
        <section class="section">
            <button v-if="selectedItems.length > 0" class="button field is-danger" @click="deleteSelectedItems()"><span>Deletar itens selecionados </span><b-icon icon="delete"></b-icon></button>
            <b-table
                    ref="itemsTable"
                    :data="items"
                    @selection-change="handleSelectionChange"
                    :checked-rows.sync="selectedItems"
                    :loading="isLoading"
                    checkable>
                <template slot-scope="props">

                    <b-table-column field="featured_image" width="55">
                        <template v-if="props.row.featured_image" slot-scope="scope">
                            <img class="table-thumb" :src="`${props.row.featured_image}`"/>
                        </template>
                    </b-table-column>

                    <b-table-column label="Nome" field="title" show-overflow-tooltip>
                        <router-link
                                :to="`/collections/${collectionId}/items/${props.row.id}`" tag="a">{{ props.row.title }}
                        </router-link>
                    </b-table-column>

                    <b-table-column field="description" label="Descrição">
                        {{ props.row.description }}
                    </b-table-column>


                    <b-table-column label="Ações">
                        <router-link :to="`/collections/${collectionId}/items/${props.row.id}/edit`" tag="a"><b-icon icon="pencil"></router-link>
                        <a><b-icon icon="delete" @click.native="deleteOneItem(props.row.id)"></a>
                        <a><b-icon icon="dots-vertical" @click.native="showMoreItem(props.row.id)"></a> 
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
                            <router-link tag="button" class="button is-primary"
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
            isLoading: Boolean
        }
    },
    props: {
        collectionId: Number
    },
    methods: {
        ...mapActions('collection', [
            'fetchItems',
            'deleteItem'
        ]),
        ...mapGetters('collection', [
            'getItems'
        ]),
        deleteOneItem(itemId) {
            this.$dialog.confirm({
                message: 'Deseja realmente deletar este Item?',
                onConfirm: () => {
                    this.deleteItem(itemId).then(() =>
                        this.$toast.open({
                            duration: 3000,
                            message: `Item deletado`,
                            position: 'is-bottom',
                            type: 'is-secondary',
                            queue: true
                        })
                    ).catch(() =>
                        this.$toast.open({
                            duration: 3000,
                            message: `Erro ao deletar item`,
                            position: 'is-bottom',
                            type: 'is-danger',
                            queue: true
                        })
                    );
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
                            this.$toast.open({
                                duration: 3000,
                                message: `Item deletado`,
                                position: 'is-bottom',
                                type: 'is-secondary',
                                queue: false
                            })                            
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
        showMoreItem(itemId) {
        }
    },
    computed: {
        items(){
            return this.getItems();
        }
    },
    created(){
        this.isLoading = true;
        this.fetchItems(this.collectionId)
            .then(res => this.isLoading = false)
            .catch((error) => {
                this.isLoading = false;
            });
    }

}
</script>

<style scoped>

    .table-thumb {
        max-height: 55px !important;
        vertical-align: middle !important;
    }

</style>


