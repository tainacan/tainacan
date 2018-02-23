<template>
    <div>
        <b-loading :active.sync="isLoading" :canCancel="false">

        </b-loading>
        <div class="card">
            <div class="card-image" v-if="item.featured_image">
                <figure class="image is-4by3">
                    <img :src="item.featured_image" class="image" :alt="item.title">
                </figure>
            </div>
            <div class="card-content">
                <div class="media">
                    <div class="media-content">
                        <p class="title is-4">{{ item.title }}</p>
                        <!--p class="subtitle is-6">@johnsmith</p-->
                    </div>
                </div>

                <div class="content">
                    {{item.description}}
                </div>
            </div>
            <footer class="card-footer">
                <router-link
                        class="card-footer-item" :to="{ path: `/collections/${collectionId}`}">
                    Ver Coleção
                </router-link>
                <router-link
                        class="card-footer-item" :to="{ path: `/collections/${collectionId}/items/${itemId}/edit`}">
                    Editar Item
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
        this.collectionId = this.$route.params.collection_id;        
        this.itemId = this.$route.fullPath.split("/").pop();
    
        // Puts loading on Item Loading
        this.isLoading = true;
        let loadingInstance = this;

        // Obtains Item 
        this.fetchItem(this.itemId).then(res => {
            loadingInstance.isLoading = false;
        });
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

    el-button {
        padding: 0;
        float: right;
    }

    .image {
        width: 100%;
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


