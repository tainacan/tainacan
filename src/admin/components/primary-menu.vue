<template>
    <nav id="primary-menu" :class="isCompressed ? 'is-compressed' : ''" role="navigation" :aria-label="$i18n.get('label_main_menu')" class="column is-sidebar-menu">
        <aside class="menu">
            <div class="menu-header">
                <ul class="menu-list"><li><router-link tag="a" to="/">
                    <b-icon size="is-medium" icon="chevron-left"></b-icon>
                    <img class="tainacan-logo" alt="Tainacan Logo" :src="logoHeader"/>
                </router-link></li></ul> 
            </div>
            
            <ul class="menu-list">
                <li><router-link tag="a" to="/collections" :class="activeRoute == 'CollectionsPage' ? 'is-active':''">
                    <b-icon size="is-small" icon="folder-multiple"></b-icon> <span class="menu-text">{{ $i18n.get('collections')}}</span>
                </router-link></li>
                <li><router-link tag="a" to="/items" :class="activeRoute == 'ItemsPage' ? 'is-active':''">
                    <b-icon size="is-small" icon="note-multiple"></b-icon> <span class="menu-text">{{ $i18n.get('items')}}</span>
                </router-link></li>
                <li class="separator"></li>
                <li><router-link tag="a" to="/fields" :class="activeRoute == 'FieldsPage' ? 'is-active':''">
                    <b-icon size="is-small" icon="format-list-checks"></b-icon> <span class="menu-text">{{ $i18n.get('fields')}}</span>
                </router-link></li>
                <li><router-link tag="a" to="/filters" :class="activeRoute == 'FiltersPage' ? 'is-active':''">
                    <b-icon size="is-small" icon="filter"></b-icon> <span class="menu-text">{{ $i18n.get('filters')}}</span>
                </router-link></li>
                <li><router-link tag="a" to="/categories" :class="activeRoute == 'CategoriesPage' ? 'is-active':''">
                    <b-icon size="is-small" icon="shape"></b-icon> <span class="menu-text">{{ $i18n.get('categories')}}</span>
                </router-link></li>
                <li><router-link tag="a" to="/events" :class="activeRoute == 'EventsPage' ? 'is-active':''">
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
            logoHeader: '../wp-content/plugins/tainacan/admin/images/tainacan_logo_header.png',
            wordpressAdmin: window.location.origin + window.location.pathname.replace('admin.php', ''),
            isCompressed: false,
            activeRoute: '/collections'
        }
    },
    watch: {
        '$route' (to, from) {
            this.isCompressed = (to.params.id != undefined);
            this.activeRoute = to.name;
        }
    },
    created () {
        this.isCompressed = (this.$route.params.id != undefined);
        this.activeRoute = this.$route.name;
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

        .menu-header {
            background-color: $primary-darker;
            height: 62px;
            a { padding: 1em 2.5em }
            .icon {
                position: absolute;
                opacity: 0;
                visibility: hidden;
                transition: opacity 0.2s linear, visibility 0.2s linear;
                -webkit-transition: opacity 0.2s linear, visibility 0.2s linear;  
            }
            .tainacan-logo {
                max-height: 28px;
                opacity: 1;
                visibility: visible;
                transition: opacity 0.15s linear, visibility 0.15s linear;
                -webkit-transition: opacity 0.15s linear, visibility 0.15s linear;
            }
        }
        .separator {
            height: 2px;
            background-color: $primary-darker;
            width: 100%;
        }
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

            .menu-header {
                a { padding: 1.2em 0.3em }
                .icon {
                    visibility: visible; 
                    opacity: 1;
                }   
                .tainacan-logo {   
                    visibility: hidden; 
                    opacity: 0;
                }
            }
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

            &.is-compressed {

                .menu-header {
                    .icon {
                        visibility: hidden !important; 
                        opacity: 0 !important;
                    }   
                    .tainacan-logo {   
                        visibility: visible !important; 
                        opacity: 1 !important;
                    }
                }
            }
            .separator {
                width: 2px;
                height: auto;
            }
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


