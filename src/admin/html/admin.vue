<template>
    <div id="tainacan-admin-app">
        <el-container>
            <el-header>
                Header
            </el-header>
            <el-container>
                <el-aside width="200px">
                    Aside
                </el-aside>
                <el-main>
                    <el-row>
                        <el-col :span="8" v-for="(collection, index) in collections" :key="collection.id" :offset="index > 0 ? 2 : 0">
                            <el-card :body-style="{ padding: '0px' }">
                                <img src="" class="image" :alt="collection.name">
                                <div style="padding: 14px;">
                                    <span>{{ collection.name }}</span>
                                    <div class="bottom clearfix">
                                        <time class="time">{{collection.description}}</time>
                                        <el-button type="text" class="button">Detalhes</el-button>
                                    </div>
                                </div>
                            </el-card>
                        </el-col>
                    </el-row>
                </el-main>
            </el-container>
        </el-container>
    </div>
</template>

<script>
    import { mapActions, mapGetters } from 'vuex'

    export default {
        name: "admin",
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
    .el-header, .el-footer {
        background-color: #B3C0D1;
        color: #333;
        line-height: 60px;
    }

    .el-aside {
        background-color: #D3DCE6;
        color: #333;
        line-height: 200px;
    }

    .el-main {
        background-color: #E9EEF3;
        color: #333;
        line-height: 160px;
    }

    body > .el-container {
        margin-bottom: 40px;
    }

    .el-container:nth-child(5) .el-aside,
    .el-container:nth-child(6) .el-aside {
        line-height: 260px;
    }

    .el-container:nth-child(7) .el-aside {
        line-height: 320px;
    }

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