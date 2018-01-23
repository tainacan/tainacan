<template>
    <div>
        <el-row v-if="collection != null">
            <el-card :body-style="{ padding: '0px' }">
                <img src="" class="image" :alt="collection.name">
                <div style="padding: 14px;">
                    <span>{{ collection.name }}</span>
                    <div class="bottom clearfix">
                        <time class="time">{{collection.description}}</time>
                        <el-button type="text" >Lista de Itens</el-button>
                    </div>

                </div>
            </el-card>     
        </el-row>
    </div>
</template>

<script>
import { mapActions, mapGetters } from 'vuex'

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
        ])
    },
    computed: {
        collection(){
            return this.getCollection();
        }
    },
    created(){
        this.collectionId = this.$route.params.id;
        console.log(this.collectionId);
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

    .button {
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


