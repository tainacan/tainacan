<template>
    <div>
        <b-table
                ref="multipleTable"
                :data="items"
                @selection-change="handleSelectionChange"
                stripe>
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

            <!--b-table-column type="selection" width="55">
            </b-table-column -->

        </b-table>
    </div>
</template>

<script>
import { mapActions, mapGetters } from 'vuex'

export default {
    name: 'ItemsList',
    data(){
        return {
            multipleSelection: []
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
        handleSelectionChange(value) {
            this.multipleSelection = value;
        },
        deleteOneItem(itemId) {
            this.$dialog.confirm({
                message: 'Deseja realmente deletar este Item?',
                onConfirm: () => this.deleteItem(itemId)
            });
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
        this.fetchItems(this.collectionId);
    }

}
</script>

<style scoped>

    .table-thumb {
        max-height: 55px !important;
        vertical-align: middle !important;
    }

</style>


