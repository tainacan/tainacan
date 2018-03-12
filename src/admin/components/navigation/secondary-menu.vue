<template>
    <nav id="secondary-menu" role="navigation" :aria-label="$i18n.get('label_collection_menu')" class="column is-sidebar-menu">
        <aside class="menu">
            <div class="menu-header">
                <ul class="menu-list"><li><router-link tag="a" to="/" target='_blank'>
                    <b-icon size="is-medium" icon="chevron-left"></b-icon>
                    <img class="tainacan-logo"  alt="Tainacan Logo" :src="logoHeader"/>
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
                <li><router-link  
                        tag="a" 
                        :to="{ path: $routerHelper.getCollectionItemsPath(id, '') }" 
                        :class="activeRoute == 'ItemPage' || activeRoute == 'CollectionItemsPage' || activeRoute == 'ItemEditionForm' || activeRoute == 'ItemCreatePage' ? 'is-active':''" 
                        :aria-label="$i18n.get('collection') + ' ' + $i18n.get('items')">
                    <b-icon size="is-small" icon="file-multiple"></b-icon> <span class="menu-text">{{ $i18n.get('items')}}</span>
                </router-link></li>
                <li><router-link 
                        tag="a" 
                        :to="{ path: $routerHelper.getCollectionEditPath(id) }" 
                        :class="activeRoute == 'CollectionEditionForm' ? 'is-active':''" 
                        :aria-label="$i18n.get('edit') + ' ' + $i18n.get('collection')">
                    <b-icon size="is-small" icon="pencil"></b-icon> <span class="menu-text">{{ $i18n.get('edit')}}</span>
                </router-link></li>
                <li><router-link 
                        tag="a" 
                        :to="{ path: $routerHelper.getCollectionFieldsPath(id) }" 
                        :class="activeRoute == 'FieldsList' ? 'is-active':''" 
                        :aria-label="$i18n.get('collection') + ' ' + $i18n.get('fields')">
                    <b-icon size="is-small" icon="format-list-checks"></b-icon> <span class="menu-text">{{ $i18n.get('fields')}}</span>
                </router-link></li>
                <li><router-link 
                        tag="a" 
                        :to="{ path: $routerHelper.getCollectionFiltersPath(id) }" 
                        :class="activeRoute == 'FiltersList' ? 'is-active':''" 
                        :aria-label="$i18n.get('collection') + ' ' + $i18n.get('filters')">
                    <b-icon size="is-small" icon="filter"></b-icon> <span class="menu-text">{{ $i18n.get('filters')}}</span>
                </router-link></li>
            </ul>
        </aside>
    </nav>
</template>

<script>
export default {
    name: 'SecondaryMenu',
    data(){
        return {
            logoHeader: tainacan_plugin.base_url + '/admin/images/tainacan_logo_header.png',
            activeRoute: 'ItemsList'
        }
    },
    props: {
        id: Number
    },
    watch: {
        '$route' (to, from) {
            this.activeRoute = to.name;
        }
    },
    created () {
        this.activeRoute = this.$route.name;
    }

}
</script>

<style lang="scss" scoped>

    @import "../../scss/_variables.scss";

    #secondary-menu {
        background-color: $primary;
        padding: 0px;
        -webkit-transition: max-width 0.3s linear; /* Safari */
        transition: max-width 0.3s linear;
        max-width: $side-menu-width; 
        z-index: 9;

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
            background-color: rgba(0,0,0,0.15);
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
                -webkit-transition: padding 0.3s linear; /* Safari */
                transition: padding 0.3s linear; 
            }
            a:hover {
                background-color: rgba(255,255,255,0.4);
                color: $tertiary;
            }
            a.is-active {
                background-color: rgba(255,255,255,0.4);
                color: $tertiary;
            }
            .menu-text {
                padding-left: 0.7em;
                opacity: 1;
                visibility: visible;
                transition: opacity 0.3s linear, visibility 0.3s linear;
                -webkit-transition: opacity 0.3s linear, visibility 0.3s linear;
            }
        }

        @media screen and (max-width: 769px) {
            width: 100% !important;
            max-width: 100% !important;
            a{ padding: 1em 0.8em !important; }
            .menu-header { display: none;
            }
            .menu-text {
                padding-left: 0.3em !important;
            }
            ul { 
                display: flex;
                align-items: stretch;
                justify-content: space-evenly;
                
                .separator, li.search-area {
                    display: none;
                }
            }
        }
    }

</style>


