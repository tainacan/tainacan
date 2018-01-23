<template>
    <div>
        <el-row>
            <el-col :span="8" v-for="(collection, index) in collections" :key="collection.id" :offset="index > 0 ? 2 : 0">
                <el-card :body-style="{ padding: '0px' }">
                    <img src="http://lorempixel.com/200/200" class="image" :alt="collection.name">
                    <div style="padding: 14px;">
                        <span>{{ collection.name }}</span>
                        <div class="bottom clearfix">
                            <time class="time">{{collection.description}}</time>
                            <router-link :to="`/collections/${collection.id}`">
                                <el-button type="text">
                                    Ver Coleção
                                </el-button>
                            </router-link>
                        </div>
                    </div>
                </el-card>
            </el-col>
        </el-row>
    </div>
</template>

<script>
import { mapActions, mapGetters } from 'vuex'

export default {
    name: 'CollectionsList',
    data(){
        return {
        }
    },
    methods: {
        ...mapActions('collection', [
            'fetchCollections'
        ]),
        ...mapGetters('collection', [
            'getCollections'
        ])
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

    .time {
        font-size: 13px;
        color: #999;
    }

    .bottom {
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


