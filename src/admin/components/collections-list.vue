<template>
    <div>
        <el-table
                ref="multipleTable"
                :data="collections"
                style="width: 100%"
                @selection-change="handleSelectionChange">
            <el-table-column type="selection" width="55">
            </el-table-column>
            <el-table-column label="Nome" show-overflow-tooltip>
                <template slot-scope="scope"><router-link :to="`/collections/${scope.row.id}`" tag="a">{{ scope.row.name }}</router-link></template>
            </el-table-column>
            <el-table-column property="description" label="Descrição" show-overflow-tooltip>
            </el-table-column>
            <el-table-column label="Ações" width="120">
                <template slot-scope="scope">
                    <el-button size="small" type="text" @click.native="shareCollection(scope.row.id)"><i class="material-icons">share</i></el-button>
                    <el-button size="small" type="text" @click.native="showMoreCollection(scope.row.id)"><i class="material-icons">more_vert</i></el-button>
                </template>
            </el-table-column>
        </el-table>
    </div>
</template>

<script>
import { mapActions, mapGetters } from 'vuex'

export default {
    name: 'CollectionsList',
    data(){
        return {
            multipleSelection: []
        }
    },
    methods: {
        ...mapActions('collection', [
            'fetchCollections'
        ]),
        ...mapGetters('collection', [
            'getCollections'
        ]),
        handleSelectionChange(value) {
            this.multipleSelection = value;
        },
        shareCollection(collectionId) {

        },
        showMoreCollection(collectionId) {

        }
    },
    computed: {
        collections(){
            return this.getCollections();
        }
    },
    created(){
        this.fetchCollections();
    }

}
</script>

<style scoped>

</style>


