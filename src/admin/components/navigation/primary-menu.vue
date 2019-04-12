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
                        <span
                                v-tooltip="{                                     
                                    offset: 4,
                                    content: isMenuCompressed ? $i18n.getFrom('collections', 'name') : '',
                                    autoHide: true,
                                    classes: ['tooltip', 'repository-tooltip'],
                                    placement: 'auto'
                                }"
                                class="icon">
                            <i class="tainacan-icon tainacan-icon-20px tainacan-icon-collections"/>
                        </span>
                        <span class="menu-text">{{ $i18n.getFrom('collections', 'name') }}</span>
                    </router-link>
                </li>
                <li>
                    <router-link
                            tag="a"
                            to="/items"
                            :class="activeRoute == 'ItemsPage' ? 'is-active':''">
                        <span
                                v-tooltip="{                                     
                                    offset: 4,
                                    content: isMenuCompressed ? $i18n.getFrom('items', 'name') : '',
                                    autoHide: true,
                                    classes: ['tooltip', 'repository-tooltip'],
                                    placement: 'auto'
                                }"
                                class="icon">
                            <i class="tainacan-icon tainacan-icon-20px tainacan-icon-items"/>
                        </span>
                        <span class="menu-text">{{ $i18n.getFrom('items', 'name') }}</span>
                    </router-link>
                </li>
                <li class="separator"/>
                <li>
                    <router-link
                            tag="a"
                            to="/metadata"
                            :class="activeRoute == 'MetadataPage' ? 'is-active':''">
                        <span
                                v-tooltip="{                                     
                                    offset: 4,
                                    content: isMenuCompressed ? $i18n.get('metadata') : '',
                                    autoHide: true,
                                    classes: ['tooltip', 'repository-tooltip'],
                                    placement: 'auto'
                                }"
                                class="icon">
                            <i class="tainacan-icon tainacan-icon-20px tainacan-icon-metadata"/>
                        </span>
                        <span class="menu-text">{{ $i18n.get('metadata') }}</span>
                    </router-link>
                </li>
                <li>
                    <router-link
                            tag="a"
                            to="/filters"
                            :class="activeRoute == 'FiltersPage' ? 'is-active':''">
                        <span
                                v-tooltip="{                                     
                                    offset: 4,
                                    content: isMenuCompressed ? $i18n.getFrom('filters', 'name') : '',
                                    autoHide: true,
                                    classes: ['tooltip', 'repository-tooltip'],
                                    placement: 'auto'
                                }"
                                class="icon">
                            <i class="tainacan-icon tainacan-icon-20px tainacan-icon-filters"/>
                        </span>
                        <span class="menu-text">{{ $i18n.getFrom('filters', 'name') }}</span>
                    </router-link>
                </li>
                <li>
                    <router-link
                            tag="a"
                            to="/taxonomies"
                            :class="activeRoute == 'Page' ? 'is-active':''">
                        <span
                                v-tooltip="{                                    
                                    offset: 4,
                                    content: isMenuCompressed ? $i18n.getFrom('taxonomies', 'name') : '',
                                    autoHide: true,
                                    classes: ['tooltip', 'repository-tooltip'],
                                    placement: 'auto'
                                }"
                                class="icon">
                            <i class="tainacan-icon tainacan-icon-20px tainacan-icon-taxonomies"/>
                        </span>
                        <span class="menu-text">{{ $i18n.getFrom('taxonomies', 'name') }}</span>
                    </router-link>
                </li>
                <li>
                    <router-link
                            tag="a"
                            to="/activities"
                            :class="activeRoute == 'ActivitiesPage' ? 'is-active':''">
                        <span
                                v-tooltip="{                                     
                                    offset: 4,
                                    content: isMenuCompressed ? $i18n.get('activities') : '',
                                    autoHide: true,
                                    classes: ['tooltip', 'repository-tooltip'],
                                    placement: 'auto'
                                }"
                                class="icon">
                            <i class="tainacan-icon tainacan-icon-20px tainacan-icon-activities"/>
                        </span>
                        <span class="menu-text">{{ $i18n.get('activities') }}</span>
                    </router-link>
                </li>
                <li>
                    <router-link
                            tag="a"
                            to="/importers"
                            :class="(
                                activeRoute == 'AvailableImportersPage' ||
                                activeRoute == 'ImporterEditionForm' ||
                                activeRoute == 'ImporterCreationForm' ||
                                activeRoute == 'ImporterMappingForm' ) ? 'is-active':''">
                        <span
                                v-tooltip="{                                     
                                    offset: 4,
                                    content: isMenuCompressed ? $i18n.get('importers') : '',
                                    autoHide: true,
                                    classes: ['tooltip', 'repository-tooltip'],
                                    placement: 'auto'
                                }"
                                class="icon">
                            <i class="tainacan-icon tainacan-icon-20px tainacan-icon-importers"/>
                        </span>
                        <span class="menu-text menu-text-import">{{ $i18n.get('importers') }}</span>
                    </router-link>
                </li>
                <li>
                    <router-link
                            tag="a"
                            to="/exporters"
                            :class="(
                                activeRoute == 'ExportersPage' ||
                                activeRoute == 'ExporterEditionForm') ? 'is-active':''">
                        <span
                                v-tooltip="{
                                    offset: 4,
                                    content: isMenuCompressed ? $i18n.get('exporters') : '',
                                    autoHide: true,
                                    classes: ['tooltip', 'repository-tooltip'],
                                    placement: 'auto'
                                }"
                                class="icon">
                            <i class="tainacan-icon tainacan-icon-20px tainacan-icon-export"/>
                        </span>
                        <span class="menu-text">{{ $i18n.get('exporters') }}</span>
                    </router-link>
                </li>
            </ul>
        </aside>
    </nav>
</template>

<script>
export default {
    name: 'PrimaryMenu',
    props: {
        isMenuCompressed: false,
        activeRoute: '/collections'
    }
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

                .icon {
                    height: auto;
                    width: auto;
                    // i, i::before {
                    //     font-size: 18px !important;
                    // }
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
                top: 1px;
                position: relative;
                visibility: visible;
                transition: opacity 0.2s linear, visibility 0.2s linear;
                -webkit-transition: opacity 0.2s linear, visibility 0.2s linear;
            }
            .menu-text-import {
                position: relative;
                top: 1px;
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
            padding-top: 52px;

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


