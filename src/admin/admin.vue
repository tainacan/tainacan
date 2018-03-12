<template>
    <div id="tainacan-admin-app" class="columns is-fullheight">
        <primary-menu></primary-menu>
        <tainacan-header></tainacan-header>
        <div class="column is-main-content">  
            <router-view></router-view> 
        </div>
    </div>
</template>

<script>
    import PrimaryMenu from './components/navigation/primary-menu.vue';
    import TainacanHeader from './components/navigation/tainacan-header.vue';

    export default {
        name: "AdminPage",
        data(){
            return {
            }
        },
        components: {
            PrimaryMenu,
            TainacanHeader
        },
        created() {
            this.$userPrefs.init()
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
        padding: 0px;
        margin: 0 auto;
        position: relative;
        overflow-y: auto;

        @media screen and (max-width: 769px) {
            & {
                overflow-y: visible;
            }
        } 
        .columns {
            margin-left: 0px;
            margin-right: 0px;
        }
    }

    .is-secondary-content {
        padding: 0px;
        margin: 78px auto 0 auto;
        position: relative;
        overflow-y: auto;

        @media screen and (max-width: 769px) {
            & {
                overflow-y: visible;
            }
        } 
    }

</style>

