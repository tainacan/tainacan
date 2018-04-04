<template>
    <div>
        <b-loading 
                :active.sync="isLoading" 
                :can-cancel="false"/>
        <div class="card">
            <div 
                    class="card-image" 
                    v-if="item.featured_image">
                <figure class="image">
                    <img 
                            :src="item.featured_image" 
                            :alt="item.title">
                </figure>
            </div>
            <div class="card-content">
                <div class="media">
                    <div class="media-content">
                        <p class="title is-4">{{ item.title }}</p>
                    </div>
                </div>

                <div class="content">
                    {{ item.description }}
                </div>
            </div>
            <footer class="card-footer">
                <router-link
                        class="card-footer-item" 
                        :to="{ path: $routerHelper.getCollectionPath(collectionId)}">
                    {{ $i18n.get('see') + ' ' + $i18n.get('collection') }}
                </router-link>
                <router-link
                        class="card-footer-item" 
                        :to="{ path: $routerHelper.getItemEditPath(collectionId, itemId)}">
                    {{ $i18n.get('edit') + ' ' + $i18n.get('item') }}
                </router-link>
            </footer>
        </div>
    </div>
</template>

<script>
import { mapActions, mapGetters } from 'vuex'

export default {
    name: 'ItemPage',
    data(){
        return {
            collectionId: Number,
            itemId: Number,
            isLoading: false
        }
    },
    methods: {
        ...mapActions('item', [
            'fetchItem'
        ]),
        ...mapGetters('item', [
            'getItem'
        ]),
    },
    computed: {
        item(){
            return this.getItem();
        }
    },
    created(){
        // Obtains item and collection ID
        this.collectionId = this.$route.params.collectionId;        
        this.itemId = this.$route.params.itemId;

        // Puts loading on Item Loading
        this.isLoading = true;
        let loadingInstance = this;

        // Obtains Item 
        this.fetchItem(this.itemId).then(() => {
            loadingInstance.isLoading = false;
        });
    }

}
</script>


