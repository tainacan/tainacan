<template>
    <div>
        <el-row v-if="item != null">
            <el-card :body-style="{ padding: '0px' }">
                <img src="" class="image" :alt="item.title">
                <div style="padding: 14px;">
                    <span>{{ item.title }}</span>
                    <div class="bottom clearfix">
                        <time class="time">{{item.description}}</time>
                        <router-link tag="el-button" class="primary" :to="{ path: `/collection/${collectionId}/items/${itemId}/edit`}">Editar Item</router-link>
                    </div>
                </div>
            </el-card>     
        </el-row>
    </div>
</template>

<script>
import { mapActions, mapGetters } from 'vuex'

export default {
    name: 'ItemPage',
    data(){
        return {
            collectionId: Number,
            itemId: Number
        }
    },
    methods: {
        ...mapActions('item', [
            'fetchItem'
        ]),
        ...mapGetters('item', [
            'getItem'
        ]),
        createItem() {
            
        }
    },
    computed: {
        item(){
            return this.getItem();
        }
    },
    created(){
        // Obtains item and collection ID
        this.collectionId = this.$route.params.collection_id;        
        this.itemId = this.$route.params.id;

        // Puts loading on Item Loading
        let loadingInstance = this.$loading({ text: 'Carregando item...' });
        
        // Obtains Item 
        this.fetchItem(this.itemId).then(res => {
            loadingInstance.close();
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


