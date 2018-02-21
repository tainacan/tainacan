<template>
    <div id="tainacan-admin-app" class="columns is-fullheight">
        <nav id="primary-menu" role="navigation" aria-label="main navigation" class="column is-2 is-sidebar-menu">
            <aside class="menu">
                <router-link to="/">
                    <img :src="logoHeader" alt="Tainacan Admin" height="32">
                </router-link>
                <ul class="menu-list">
                    <li><router-link tag="a" to="/collections">{{ $i18n.getString('menu', 'collections')}}</router-link></li>
                    <li><router-link tag="a" to="/items">{{ $i18n.getString('menu', 'items')}}</router-link></li>
                    <li><router-link tag="a" to="/fields">{{ $i18n.getString('menu', 'fields')}}</router-link></li>
                    <li><router-link tag="a" to="/filters">{{ $i18n.getString('menu', 'filters')}}</router-link></li>
                    <li><router-link tag="a" to="/categories">{{ $i18n.getString('menu', 'categories')}}</router-link></li>
                    <li><router-link tag="a" to="/events">{{ $i18n.getString('menu', 'events')}}</router-link></li>
                    <li><a class="navbar-item" :href="wordpressAdmin">Admin do Wordpress</a></li>
                </ul>
            </aside>
        </nav>
        <div class="container column is-main-content">
            <router-view></router-view>
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

    .is-fullheight {
        height: 100%;
    }

    #primary-menu {
        background-color: $primary;

        li{
            a {color: white !important;}
            a:hover {color: $primary !important;}
        }
    }

</style>

