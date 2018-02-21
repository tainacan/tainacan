<template>
    <div id="tainacan-admin-app">
        <div class="columns is-fullheight">
            <nav id="primary-menu" role="navigation" aria-label="main navigation" class="column is-2 is-sidebar-menu is-hidden-mobile">
                <aside class="menu">
                    <router-link to="/">
                        <img :src="logoHeader" alt="Tainacan Admin" height="32">
                    </router-link>
                    <ul class="menu-list">
                        <li><router-link tag="a" to="/collections">{{ $i18n.getString('header', 'collections')}}</router-link></li>
                        <li><a class="navbar-item">Items</a></li>
                        <li><a class="navbar-item">Campos</a></li>
                        <li><a class="navbar-item">Filtros</a></li>
                        <li><a class="navbar-item">Atividades</a></li>
                        <li><a class="navbar-item" :href="wordpressAdmin">Admin do Wordpress</a></li>
                    </ul>
                </aside>
            </nav>
            <div class="container column is-main-content">
                <router-view></router-view>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        name: "AdminPage",
        data(){
            return {
                logoHeader: '../wp-content/plugins/tainacan/admin/images/tainacan_logo_header.png',
                wordpressAdmin: window.location.origin + window.location.pathname.replace('admin.php', ''),
            }
        }
    }
</script>

<style lang="scss">

    @import "./scss/_variables.scss";

    #primary-menu {
        background-color: $primary;
        li{
            a {color: white !important;}
            a:hover {color: $primary !important;}
        }
    }

</style>

