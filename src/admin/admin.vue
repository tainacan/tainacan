<template>
    <div id="tainacan-admin-app" class="columns is-fullheight">
        <nav id="primary-menu" role="navigation" aria-label="main navigation" class="column is-2 is-sidebar-menu">
            <aside class="menu">
                <h1 class="menu-level-title">{{ $i18n.get('repository') }}</h1>
                <ul class="menu-list">
                    <li><router-link tag="a" to="/collections">{{ $i18n.get('collections')}}</router-link></li>
                    <li><router-link tag="a" to="/items">{{ $i18n.get('items')}}</router-link></li>
                    <li><router-link tag="a" to="/fields">{{ $i18n.get('fields')}}</router-link></li>
                    <li><router-link tag="a" to="/filters">{{ $i18n.get('filters')}}</router-link></li>
                    <li><router-link tag="a" to="/categories">{{ $i18n.get('categories')}}</router-link></li>
                    <li><router-link tag="a" to="/events">{{ $i18n.get('events')}}</router-link></li>
                    <li><a class="navbar-item" :href="wordpressAdmin">Wordpress</a></li>
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
        margin-bottom: 0px;
        margin-top: 0px;
    }

    #primary-menu {
        background-color: $primary;

        li{
            a {color: white !important;}
            a:hover {color: $primary !important;}
        }
    }

    .is-main-content {
        padding-bottom: 0px;
        padding-top: 0px;
    }

    #secondary-menu {
        background-color: $secondary;

        li{
            a {color: white !important;}
            a:hover {color: $secondary !important;}
        }
    }

    

</style>

