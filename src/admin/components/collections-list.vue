<template>
    <div>
        <b-table
                ref="multipleTable"
                :data="collections"
                style="width: 100%"
                @selection-change="handleSelectionChange"
                stripe>
            <template slot-scope="props">

                <b-table-column field="featured_image" width="55">
                    <template v-if="props.row.featured_image" slot-scope="scope">
                        <img class="table-thumb" :src="`${props.row.featured_image}`"/>
                    </template>
                </b-table-column>


                <b-table-column label="Nome" field="props.row.name" sortable show-overflow-tooltip>
                    <router-link :to="`/collections/${props.row.id}`" tag="a">{{ props.row.name }}</router-link>
                </b-table-column>

                <b-table-column property="description" label="Descrição" field="props.row.description">
                    {{ props.row.description }}
                </b-table-column>


                <b-table-column label="Ações">
                    <a class="button is-large"
                       @click.native="shareCollection(scope.row.id)"><i class="material-icons md-18">share</i>
                    </a>
                    <a class="button is-large" @click.native="showMoreCollection(scope.row.id)">
                        <i class="material-icons md-18">more_vert</i>
                    </a>
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


