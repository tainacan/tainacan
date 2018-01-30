<template>
    <div>
        <el-table
                ref="multipleTable"
                :data="collections"
                style="width: 100%"
                @selection-change="handleSelectionChange"
                stripe>
            <el-table-column type="selection" width="55">
            </el-table-column>
            <el-table-column width="55">
                <template v-if="scope.row.featured_image" slot-scope="scope">
                    <img class="table-thumb" :src="`${scope.row.featured_image}`"/>
                </template>
            </el-table-column>
            <el-table-column label="Nome" sortable prop="{{ scope.row.name }}" show-overflow-tooltip>
                <template slot-scope="scope"><router-link :to="`/collections/${scope.row.id}`" tag="a">{{ scope.row.name }}</router-link></template>
            </el-table-column>
            <el-table-column property="description" label="Descrição" show-overflow-tooltip>
            </el-table-column>
            <el-table-column label="Ações" width="120">
                <template slot-scope="scope">
                    <el-button size="small" type="text" @click.native="shareCollection(scope.row.id)"><i class="material-icons md-18">share</i></el-button>
                    <el-button size="small" type="text" @click.native="showMoreCollection(scope.row.id)"><i class="material-icons md-18">more_vert</i></el-button>
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

    .table-thumb {
        max-height: 38px !important;
        vertical-align: middle !important;
    }

</style>


