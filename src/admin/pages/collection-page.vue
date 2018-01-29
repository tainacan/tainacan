<template>
    <div>
        <el-row v-if="collection != null">
            <el-card :body-style="{ padding: '0px' }">
                <img :src="collection.featured_image" class="image" :alt="collection.name">
                <div style="padding: 14px;">
                    <span>{{ collection.name }}</span>
                    <div class="bottom clearfix">
                        <time class="time">{{collection.description}}</time>
                        <el-button type="text" class="element-button">Lista de Itens</el-button>
                        <router-link type="success" class="element-button" tag="el-button" :to="{ path: `/collections/${collection.id}/items/create`, params: { collection_id: collection.id }}">Criar Item</router-link>
                    </div>
                    <items-list collectionId="collectionId"></items-list>
                </div>
            </el-card>
        </el-row>
    </div>
</template>

<script>
import { mapActions, mapGetters } from 'vuex'
import ItemsList from '../components/items-list.vue';

export default {
    name: 'CollectionPage',
    data(){
        return {
            collectionId: Number
        }
    },
    methods: {
        ...mapActions('collection', [
            'fetchCollection'
        ]),
        ...mapGetters('collection', [
            'getCollection'
        ]),
        createItem() {
            
        }
    },
    components: {
        'items-list': ItemsList
    },
    computed: {
        collection(){
            return this.getCollection();
        }
    },
    created(){
        this.collectionId = this.$route.params.id;
        this.fetchCollection(this.collectionId);
    }

}
</script>

<style scoped>

    .time {
        font-size: 13px;
        color: #999;
    }

    .bottom {
        margin-top: 13px;
        line-height: 12px;
    }

    .element-button {
        float: right;
    }

    .image {
        max-width: 100%;
        display: block;
    }

    .clearfix:before,
    .clearfix:after {
        display: table;
        content: "";
    }

    .clearfix:after {
        clear: both
    }
</style>


