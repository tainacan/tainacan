<template>
    <div>
        <section class="section" v-if="collection != null">
            <div class="card">
                <div class="card-image" v-if="collection.featured_image">
                    <figure class="image is-4by3">
                        <img :src="collection.featured_image" class="image" :alt="collection.name">
                    </figure>
                </div>
                <div class="card-content">
                    <div class="media">
                        <div class="media-content">
                            <p class="title is-4">{{ collection.name }}</p>
                        </div>
                    </div>

                    <div class="content">
                        {{collection.description}}
                    </div>
                </div>
                <footer class="card-footer">
                    <router-link
                            tag="a"
                            class="card-footer-item"
                            :to="{ path: `/collections/${collection.id}/edit` }">
                        Editar Coleção
                    </router-link>
                    <router-link
                            tag="a"
                            class="card-footer-item"
                            :to="{ path: `/collections/${collection.id}/items/new`, params: { collection_id: collection.id }}">
                        Criar Item
                    </router-link>
                    <router-link
                            tag="a"
                            class="card-footer-item"
                            :to="{ path: `/collection/${collection.id}/items/`, params: { collection_id: collection.id }}">
                        Ver todos os itens
                    </router-link>
                </footer>
            </div>
            <items-list :collectionId="collectionId"></items-list>
        </section>
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
        this.collectionId = new Number(this.$route.params.id);
        this.fetchCollection(this.collectionId);
    }

}
</script>

<style scoped>
</style>


