<template>
    <div>
        <el-table
                ref="multipleTable"
                :data="items"
                style="width: 100%"
                @selection-change="handleSelectionChange">
            <el-table-column type="selection" width="55">
            </el-table-column>
            <el-table-column width="55">
                <template v-if="scope.row.featured_image" slot-scope="scope">
                    <img class="table-thumb" :src="`${scope.row.featured_image}`"/>
                </template>
            </el-table-column>
            <el-table-column label="Nome" show-overflow-tooltip>
                <template slot-scope="scope"><router-link :to="`/items/${scope.row.id}`" tag="a">{{ scope.row.title }}</router-link></template>
            </el-table-column>
            <el-table-column property="description" label="Descrição" show-overflow-tooltip>
            </el-table-column>
            <el-table-column label="Ações" width="120">
                <template slot-scope="scope">
                    <el-button size="small" type="text" @click.native="shareItem(scope.row.id)"><i class="material-icons">share</i></el-button>
                    <el-button size="small" type="text" @click.native="showMoreItem(scope.row.id)"><i class="material-icons">more_vert</i></el-button>
                </template>
            </el-table-column>
        </el-table>
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
            'fetchItems'
        ]),
        ...mapGetters('collection', [
            'getItems'
        ]),
        handleSelectionChange(value) {
            this.multipleSelection = value;
        },
        shareItem(itemId) {

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


