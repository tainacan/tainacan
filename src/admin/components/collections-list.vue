<template>
    <div>
        <router-link tag="button" class="button is-primary"
                    :to="{ path: `/collections/new` }">
            Criar Coleção
        </router-link>
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

                <b-table-column label="Nome" field="props.row.name">
                    <router-link :to="`/collections/${props.row.id}`" tag="a">{{ props.row.name }}</router-link>
                </b-table-column>

                <b-table-column property="description" label="Descrição" show-overflow-tooltip field="props.row.description">
                    {{ props.row.description }}
                </b-table-column>


                <b-table-column label="Ações">
                    <router-link :to="`/collections/${props.row.id}/edit`" tag="a"><b-icon icon="pencil"></router-link>
                    <a @click.native="showMoreCollection(props.row.id)">
                        <b-icon icon="dots-vertical">
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


