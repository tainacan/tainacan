<template>
    <nav id="primary-menu" :class="isCompressed ? 'is-compressed' : ''" role="navigation" aria-label="main navigation" class="column is-2 is-sidebar-menu">
        <aside class="menu">
            <ul class="menu-list">
                <li><router-link tag="a" to="/collections" :class="activeRoute == '/collections' ? 'is-active':''">
                    <b-icon size="is-small" icon="folder-multiple"></b-icon> <span class="menu-text">{{ $i18n.get('collections')}}</span>
                </router-link></li>
                <li><router-link tag="a" to="/items" :class="activeRoute == '/items' ? 'is-active':''">
                    <b-icon size="is-small" icon="note-multiple"></b-icon> <span class="menu-text">{{ $i18n.get('items')}}</span>
                </router-link></li>
                <li><router-link tag="a" to="/fields" :class="activeRoute == '/fields' ? 'is-active':''">
                    <b-icon size="is-small" icon="format-list-checks"></b-icon> <span class="menu-text">{{ $i18n.get('fields')}}</span>
                </router-link></li>
                <li><router-link tag="a" to="/filters" :class="activeRoute == '/filters' ? 'is-active':''">
                    <b-icon size="is-small" icon="filter"></b-icon> <span class="menu-text">{{ $i18n.get('filters')}}</span>
                </router-link></li>
                <li><router-link tag="a" to="/categories" :class="activeRoute == '/categories' ? 'is-active':''">
                    <b-icon size="is-small" icon="shape"></b-icon> <span class="menu-text">{{ $i18n.get('categories')}}</span>
                </router-link></li>
                <li><router-link tag="a" to="/events" :class="activeRoute == '/events' ? 'is-active':''">
                    <b-icon size="is-small" icon="calendar"></b-icon> <span class="menu-text">{{ $i18n.get('events')}}</span>
                </router-link></li>
                <li><a class="navbar-item" :href="wordpressAdmin">
                    <b-icon size="is-small" icon="wordpress"></b-icon> <span class="menu-text">Wordpress</span>
                </a></li>
            </ul>
        </aside>
    </nav>
</template>

<script>
export default {
    name: 'PrimaryMenu',
    data(){
        return {
            isCompressed: false,
            activeRoute: '/collections'
        }
    },
    watch: {
        '$route' (to, from) {
            this.isCompressed = (to.params.id != undefined);
            this.activeRoute = to.path;
        }
    },
    created () {
        this.isCompressed = (this.$route.params.id != undefined);
        this.activeRoute = this.$route.path;
    }
}
</script>

<style lang="scss" scoped>

    @import "../scss/_variables.scss";

    #primary-menu {
        background-color: $primary;
        -webkit-transition: width 0.8s; /* Safari */
        transition: all 0.1s;

        li{
            a {color: white}
            a:hover {color: $primary}
            a.is-active {
                background-color: white;
                color: $secondary;
                color: $primary;
            }
        }

        &.is-compressed {
            width: 64px;
            max-width: 64px;
            .menu-text {
                display: none;
            }
            box-shadow: -1px 0px 5px #111 inset;
        }

        @media screen and (max-width: 769px) {
            width: 100% !important;
            max-width: 100% !important;
            .menu-text {
                display: none !important;
            }
            ul { 
                display: flex;
                align-items: stretch;
                justify-content: space-between;
            }
        }
    }
</style>


