<template>
    <div id="tainacan-admin-app" class="columns is-fullheight">
        <primary-menu></primary-menu>
        <div class="column is-main-content">
            <router-view></router-view> 
        </div>
    </div>
</template>

<script>
    import PrimaryMenu from './components/primary-menu.vue';
    export default {
        name: "AdminPage",
        data(){
            return {
            }
        },
        components: {
            PrimaryMenu
        }
    }
</script>

<style lang="scss">

    @import "./scss/_variables.scss";

    .is-fullheight {
        height: 100%;
        margin-bottom: 0px;
        margin-top: 0px;
    }  

    .is-main-content {
        padding-bottom: 0px;
        padding-top: 0px;
        margin: 0 auto;
        position: relative;
        overflow-y: auto;
    }

</style>

