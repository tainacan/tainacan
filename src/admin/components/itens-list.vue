<template>
    <div>
        <el-table
                ref="multipleTable"
                :data="itens"
                style="width: 100%"
                @selection-change="handleSelectionChange">
            <el-table-column type="selection" width="55">
            </el-table-column>
            <el-table-column label="Nome" show-overflow-tooltip>
                <template slot-scope="scope"><router-link :to="`/itens/${scope.row.id}`" tag="a">{{ scope.row.name }}</router-link></template>
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
    name: 'ItensList',
    data(){
        return {
            multipleSelection: []
        }
    },
    methods: {
        ...mapActions('item', [
            'fetchItens'
        ]),
        ...mapGetters('item', [
            'getItens'
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
        itens(){
            return this.getItens();
        }
    },
    created(){
        this.fetchItens();
    }

}
</script>

<style scoped>

</style>


