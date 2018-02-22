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
                logoHeader: '../wp-content/plugins/tainacan/admin/images/tainacan_logo_header.png',
                wordpressAdmin: window.location.origin + window.location.pathname.replace('admin.php', ''),
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
    }

    #secondary-menu {
        background-color: $secondary;

        li{ 
            a {color: white !important;}
            a:hover {color: $secondary !important;}
        }
    }

</style>

