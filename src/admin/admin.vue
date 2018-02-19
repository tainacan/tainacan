<template>
    <div id="tainacan-admin-app">
        <nav class="navbar is-secondary" role="navigation" aria-label="main navigation">
            <div class="navbar-brand">
                <router-link class="navbar-item" to="/">
                    <img :src="logoHeader" alt="Tainacan Admin" height="32">
                </router-link>
                <router-link class="navbar-item" to="/collections">{{ $i18n.getString('header', 'collections')}}</router-link>
            </div>
            <a :href="wordpressAdmin" class="navbar-end">
                <i class="mdi mdi-close mdi-36px mdi-light"></i>
            </a>
        </nav>
        <div class="container">
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
