<template>
    <nav id="primary-menu" :class="isCompressed ? 'is-compressed' : ''" role="navigation" aria-label="main navigation" class="column is-sidebar-menu">
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
            console.log(to);
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
        background-color: $primary-dark;
        padding: 0px; 
        -webkit-transition: max-width 0.2s linear; /* Safari */
        transition: max-width 0.2s linear; 
        max-width: 222px;

        li{
            a {
                color: white;
                white-space: nowrap;
                overflow: hidden;
                padding: 1em 1.8em;
                line-height: 1.5em;
                border-radius: 0px;
                -webkit-transition: padding 0.2s linear; /* Safari */
                transition: padding 0.2s linear; 
            }
            a:hover {
                background-color: $primary-light;
                color: $secondary
            }
            a.is-active {
                background-color: $primary;
                color: $secondary;
            }
            .menu-text {
                padding-left: 0.7em;
                opacity: 1;
                visibility: visible;
                transition: opacity 0.2s linear, visibility 0.2s linear;
                -webkit-transition: opacity 0.2s linear, visibility 0.2s linear;
            }
        }

        &.is-compressed {
            max-width: 42px;
            a{ padding: 1em 0.8em; }
            .menu-text {
                visibility: hidden;
                opacity: 0;
            }
            box-shadow: -3px 0px 10px #111;
            z-index: 10;
        }

        @media screen and (max-width: 769px) {
            width: 100% !important;
            max-width: 100% !important;
            a{ padding: 1em 0.8em !important;}
            .menu-text {
                display: none !important;
            }
            ul { 
                display: flex;
                align-items: stretch;
                justify-content: space-evenly; 
            }
        }
    }
</style>


