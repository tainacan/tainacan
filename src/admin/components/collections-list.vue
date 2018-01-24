<template>
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
                    <el-button size="small" type="text" @click.native="shareCollection(scope.row.id)"><share-variant-icon></share-variant-icon></el-button>
                    <el-button size="small" type="text" @click.native="showMoreCollection(scope.row.id)"><dots-horizontal-icon></dots-horizontal-icon></el-button>
                </template>
            </el-table-column>
    </el-table>
</template>

<script>
import { mapActions, mapGetters } from 'vuex'
import ShareVariantIcon from "vue-material-design-icons/share-variant.vue"
import DotsHorizontalIcon from "vue-material-design-icons/dots-horizontal.vue"

export default {
    name: 'CollectionsList',
    data(){
        return {
            multipleSelection: []
        }
    },
    components: {
        ShareVariantIcon,
        DotsHorizontalIcon
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


