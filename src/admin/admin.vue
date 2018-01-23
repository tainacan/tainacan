<template>
    <div id="tainacan-admin-app">
        <el-container>
            <el-header>
                <router-link to="/">Home</router-link>
                Header
            </el-header>
            <el-container>
                <el-aside width="200px">
                    <router-link to="/collections">Coleções</router-link>
                </el-aside>
                <el-main>
                    <router-view></router-view>
                </el-main>
            </el-container>
        </el-container>
    </div>
</template>

<script>
    import { mapActions, mapGetters } from 'vuex'
    import CollectionsList from './components/collections-list.vue'
    import ItensList from './components/itens-list.vue'

    export default {
        name: "admin",
        components: {
            'collections-list': CollectionsList,
            'itens-list': ItensList
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

</style>