<template>
    <div class="columns is-fullheight">
        <secondary-menu :id="collectionId"></secondary-menu>
        <section class="container column is-main-content" v-if="collection != null">
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
                            :to="{ path: `/collections/${collection.id}/items/new`, params: { collection_id: collection.id }}">
                        Criar Item
                    </router-link>
                </footer>
            </div>
            <router-view></router-view>
        </section>
    </div>
</template>

<script>
import { mapActions, mapGetters } from 'vuex'
import SecondaryMenu from '../../components/secondary-menu.vue';

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
        SecondaryMenu
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


