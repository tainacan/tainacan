<template>
    <nav
            id="primary-menu"
            :class="isMenuCompressed ? 'is-compressed' : ''"
            role="navigation"
            :aria-label="$i18n.get('label_main_menu')"
            class="column is-sidebar-menu">
        <aside class="menu">

            <ul class="menu-list">
                <li class="repository-label"><span>{{ $i18n.get('repository') }}</span></li>
                <li>
                    <router-link
                            tag="a"
                            to="/collections"
                            :class="activeRoute == 'CollectionsPage' || $route.params.collectionId != undefined ? 'is-active':''">
                        <b-icon
                                size="is-small"
                                icon="folder-multiple"/>
                        <span class="menu-text">{{ $i18n.getFrom('collections', 'name') }}</span>
                    </router-link>
                </li>
                <li>
                    <router-link
                            tag="a"
                            to="/items"
                            :class="activeRoute == 'ItemsPage' ? 'is-active':''">
                        <b-icon
                                size="is-small"
                                icon="file-multiple"/>
                        <span class="menu-text">{{ $i18n.getFrom('items', 'name') }}</span>
                    </router-link>
                </li>
                <li class="separator"/>
                <li>
                    <router-link
                            tag="a"
                            to="/metadata"
                            :class="activeRoute == 'MetadataPage' ? 'is-active':''">
                        <b-icon
                                size="is-small"
                                icon="format-list-bulleted-type"/>
                        <span class="menu-text">{{ $i18n.getFrom('metadata', 'name') }}</span>
                    </router-link>
                </li>
                <li>
                    <router-link
                            tag="a"
                            to="/filters"
                            :class="activeRoute == 'FiltersPage' ? 'is-active':''">
                        <b-icon
                                size="is-small"
                                icon="filter"/>
                        <span class="menu-text">{{ $i18n.getFrom('filters', 'name') }}</span>
                    </router-link>
                </li>
                <li>
                    <router-link
                            tag="a"
                            to="/taxonomies"
                            :class="activeRoute == 'Page' ? 'is-active':''">
                        <b-icon
                                size="is-small"
                                icon="shape"/>
                        <span class="menu-text">{{ $i18n.getFrom('taxonomies', 'name') }}</span>
                    </router-link>
                </li>
                <li>
                    <router-link
                            tag="a"
                            to="/events"
                            :class="activeRoute == 'EventsPage' ? 'is-active':''">
                        <activities-icon />
                        <span class="menu-text">{{ $i18n.get('events') }}</span>
                    </router-link>
                </li>
            </ul>
        </aside>
    </nav>
</template>

<script>
import ActivitiesIcon from '../other/activities-icon.vue';
export default {
    name: 'PrimaryMenu',
    props: {
        isMenuCompressed: false,
        activeRoute: '/collections'
    },
    components: {
        ActivitiesIcon
    },
}
</script>

<style lang="scss" scoped>

    @import "../../scss/_variables.scss";

    #primary-menu {
        background-color: $blue4;
        padding: 52px 0px 0px 0px;
        -webkit-transition: max-width 0.2s linear; /* Safari */
        transition: max-width 0.2s linear;
        max-width: $side-menu-width;
        z-index: 99;

        a:hover {
            text-decoration: none;
        }

        .menu {
            padding-top: 0px;
        }
        .repository-label {
            background-color: $blue5;
            font-weight: bold;
            font-size: 16px;
            text-transform: uppercase;
            color: white;
            padding: 11px;
            text-align: center;
            opacity: 1;
            visibility: visible;
            transition: opacity 0.2s linear, visibility 0.2s linear;
            -webkit-transition: opacity 0.2s linear, visibility 0.2s linear;
        }
        .separator {
            height: 2px;
            background-color: transparent;
            width: 100%;
            margin: 24px 0;
        }
        li {
            a {
                color: white;
                white-space: nowrap;
                overflow: hidden;
                padding: 9px 15px;
                line-height: 1.5em;
                border-radius: 0px;
                -webkit-transition: padding 0.2s linear; /* Safari */
                transition: padding 0.2s linear;

                .activities-icon {
                    fill: white;
                }
            }

            a:hover, a.is-active {
                background-color: $blue3;
            }
            a:focus {
                box-shadow: none;
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
            max-width: 50px;
            .menu-text, .repository-label>span {
                visibility: hidden;
                opacity: 0;
            }
        }

        @media screen and (max-width: 769px) {
            width: 100% !important;
            max-width: 100% !important;
            padding-top: $header-height;
            .menu {
                padding-top: 0px;
            }
            ul {
                flex-flow: wrap;
                display: flex;
                align-items: stretch;
                justify-content: space-evenly;
                .separator, .repository-label {
                    display: none;
                }
                a {
                    padding: 0.8em !important;
                    text-align: center;
                }
                .menu-text {
                    display: none;
                }
            }
        }
    }
</style>


