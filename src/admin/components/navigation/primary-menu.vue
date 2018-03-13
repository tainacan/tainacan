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
                <li class="search-area">
                    <b-field>
                        <b-input 
                            :placeholder="$i18n.get('search')"
                            type="search"
                            size="is-small"
                            icon="magnify">
                        </b-input>
                    </b-field>
                    <router-link tag="a" to="">
                        <b-icon size="is-small" icon="magnify"></b-icon> <span class="menu-text">{{ $i18n.get('advanced_search')}}</span>
                    </router-link>
                </li>
                <li class="separator"></li>
                <li><router-link tag="a" to="/collections" :class="activeRoute == 'CollectionsPage' || isCompressed ? 'is-active':''">
                    <b-icon size="is-small" icon="folder-multiple"></b-icon> <span class="menu-text">{{ $i18n.getFrom('collections', 'name') }}</span>
                </router-link></li>
                <li><router-link tag="a" to="/items" :class="activeRoute == 'ItemsPage' ? 'is-active':''">
                    <b-icon size="is-small" icon="file-multiple"></b-icon> <span class="menu-text">{{ $i18n.getFrom('items', 'name') }}</span>
                </router-link></li>
                <li class="separator"></li>
                <li><router-link tag="a" to="/fields" :class="activeRoute == 'FieldsPage' ? 'is-active':''">
                    <b-icon size="is-small" icon="format-list-checks"></b-icon> <span class="menu-text">{{ $i18n.getFrom('fields', 'name') }}</span>
                </router-link></li>
                <li><router-link tag="a" to="/filters" :class="activeRoute == 'FiltersPage' ? 'is-active':''">
                    <b-icon size="is-small" icon="filter"></b-icon> <span class="menu-text">{{ $i18n.getFrom('filters', 'name') }}</span>
                </router-link></li>
                <li><router-link tag="a" to="/categories" :class="activeRoute == 'CategoriesPage' ? 'is-active':''">
                    <b-icon size="is-small" icon="shape"></b-icon> <span class="menu-text">{{ $i18n.getFrom('categories', 'name') }}</span>
                </router-link></li>
                <li><router-link tag="a" to="/events" :class="activeRoute == 'EventsPage' ? 'is-active':''">
                    <b-icon size="is-small" icon="bell"></b-icon> <span class="menu-text">{{ $i18n.getFrom('events', 'name') }}</span>
                </router-link></li>
            </ul>
        </aside>
    </nav>
</template>

<script>
export default {
    name: 'PrimaryMenu',
    data(){
        return {
            logoHeader: tainacan_plugin.base_url + '/admin/images/tainacan_logo_header.png',
            isCompressed: false,
            activeRoute: '/collections'
        }
    },
    watch: {
        '$route' (to, from) {
            this.isCompressed = (to.params.collectionId != undefined);
            this.activeRoute = to.name;
        }
    },
    created () {
        this.isCompressed = (this.$route.params.collectionId != undefined);
        this.activeRoute = this.$route.name;
    }
}
</script>

<style lang="scss" scoped>

    @import "../../scss/_variables.scss";

    #primary-menu {
        background-color: $secondary;
        padding: 0px; 
        -webkit-transition: max-width 0.2s linear; /* Safari */
        transition: max-width 0.2s linear; 
        max-width: $side-menu-width;
        z-index: 99;

        .menu-header {
            background-color: rgba(0,0,0,0.1);
            height: $header-height; 
            a { padding: 1.45em 2.5em }
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
            background-color: $separator-color;
            width: 100%;
            margin: 1.75em 0;
        }
        li{
            &.search-area {
                visibility: visible;
                opacity: 1;
                padding-top: 1.8em;
                .field { padding: 0 1.8em 0.5em; }
                .menu-text { font-size: 0.85em; }
            }
            a {
                color: white;
                white-space: nowrap;
                overflow: hidden;
                padding: 0.75em 1.8em;
                line-height: 1.5em;
                border-radius: 0px;
                -webkit-transition: padding 0.2s linear; /* Safari */
                transition: padding 0.2s linear; 
            }
            a:hover {
                background-color: $primary;
                color: $tertiary
            }
            a.is-active {
                background-color: $primary;
                color: $tertiary;
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
                a { padding: 1.67em 0.3em }
                .icon {
                    visibility: visible; 
                    opacity: 1;
                }   
                .tainacan-logo {   
                    visibility: hidden; 
                    opacity: 0;
                }
            }
            .search-area {   
                visibility: hidden; 
                opacity: 0;
            }
            a { 
                padding-left: 0.8em;
                padding-right: 0.8em;
                color: rgba(255,255,255,0.4);
            }
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
                margin: 0;
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


