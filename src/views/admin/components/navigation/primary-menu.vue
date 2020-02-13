<template>
    <nav
            id="primary-menu"
            :class="isMenuCompressed ? 'is-compressed' : ''"
            role="navigation"
            :aria-label="$i18n.get('label_main_menu')"
            class="column is-sidebar-menu">
        <aside class="menu">

            <ul class="menu-list">
                <li class="repository-label">
                    <router-link
                            tag="a"
                            :to="$routerHelper.getCollectionsPath()">
                        <span v-if="!isMenuCompressed">{{ $i18n.get('repository') }}</span>
                        <span
                                v-if="isMenuCompressed"
                                v-tooltip="{                                     
                                    offset: 4,
                                    content: isMenuCompressed ? $i18n.get('repository') : '',
                                    autoHide: true,
                                    classes: ['tooltip', 'repository-tooltip'],
                                    placement: 'auto'
                                }"
                                class="icon">
                            <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-repository"/>
                        </span>
                    </router-link>
                </li>
                <li>
                    <router-link
                            tag="a"
                            :to="$routerHelper.getCollectionsPath()"
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
                            <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-collections"/>
                        </span>
                        <span class="menu-text">{{ $i18n.getFrom('collections', 'name') }}</span>
                    </router-link>
                </li>
                <li>
                    <router-link
                            tag="a"
                            :to="$routerHelper.getItemsPath()"
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
                            <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-items"/>
                        </span>
                        <span class="menu-text">{{ $i18n.getFrom('items', 'name') }}</span>
                    </router-link>
                </li>
                <li class="separator"/>
                <li v-if="$userCaps.hasCapability('tnc_rep_edit_metadata')">
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
                            <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-metadata"/>
                        </span>
                        <span class="menu-text">{{ $i18n.get('metadata') }}</span>
                    </router-link>
                </li>
                <li v-if="$userCaps.hasCapability('tnc_rep_edit_filters')">
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
                            <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-filters"/>
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
                            <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-taxonomies"/>
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
                            <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-activities"/>
                        </span>
                        <span class="menu-text">{{ $i18n.get('activities') }}</span>
                    </router-link>
                </li>
                <li v-if="$userCaps.hasCapability('tnc_rep_edit_users')">
                    <router-link
                            tag="a"
                            :to="this.$routerHelper.getCapabilitiesPath()"
                            :class="activeRoute == 'CapabilitiesPage' ? 'is-active':''">
                        <span
                                v-tooltip="{                                     
                                    offset: 4,
                                    content: isMenuCompressed ? $i18n.get('capabilities') : '',
                                    autoHide: true,
                                    classes: ['tooltip', 'repository-tooltip'],
                                    placement: 'auto'
                                }"
                                class="icon">
                            <svg
                                    xmlns:dc="http://purl.org/dc/elements/1.1/"
                                    xmlns:cc="http://creativecommons.org/ns#"
                                    xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
                                    xmlns:svg="http://www.w3.org/2000/svg"
                                    xmlns="http://www.w3.org/2000/svg"
                                    version="1.1"
                                    viewBox="0 0 833 750"
                                    data-name="Camada 1"
                                    id="Camada_1">
                                <defs
                                        id="defs11" />
                                <path
                                        id="path4"
                                        transform="translate(-83.5 -125)"
                                        d="M812.38,125H187.62A103.77,103.77,0,0,0,83.5,229.12V770.88A104.1,104.1,0,0,0,187.62,875H812.38A104,104,0,0,0,916.5,771V229.12A104.12,104.12,0,0,0,812.38,125ZM833.5,792h-666V209h666Z" />
                                <path
                                        id="path6"
                                        transform="translate(-83.5 -125)"
                                        d="M377.5,626a126,126,0,0,0,118.82-84H583.5v83h84V542h83V459H496.67A126,126,0,1,0,377.5,626Zm0-168a42,42,0,1,1-42,42A42,42,0,0,1,377.5,458Z" />
                            </svg>
                        </span>
                        <span class="menu-text">{{ $i18n.get('capabilities') }}</span>
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
                            <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-importers"/>
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
                            <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-export"/>
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
        activeRoute: '/collections',
    }
}
</script>

<style lang="scss" scoped>

    @import "../../scss/_variables.scss";

    #primary-menu {
        background-color: var(--tainacan-blue4);
        padding: 52px 0px 0px 0px;
        -webkit-transition: max-width 0.2s linear; /* Safari */
        transition: max-width 0.2s linear;
        max-width: $side-menu-width;
        z-index: 101;

        a:hover {
            text-decoration: none;
        }

        .menu {
            padding-top: 0px;
        }
        .repository-label {
            max-height: 42px;
            background-color: var(--tainacan-blue5);
            font-weight: bold;
            font-size: 1em;
            text-transform: uppercase;
            color: var(--tainacan-white);
            padding: 11px;
            text-align: center;
            opacity: 1;
            visibility: visible;
            transition: opacity 0.2s linear, visibility 0.2s linear;
            -webkit-transition: opacity 0.2s linear, visibility 0.2s linear;

            a {
                padding: 0;
                margin: 0;
                line-height: normal;
                &:hover {
                    background: transparent !important;
                }
            }
        }
        .separator {
            height: 2px;
            background-color: transparent;
            width: 100%; 
            margin: 24px 0;
        }
        li {
            a { 
                color: var(--tainacan-white);
                white-space: nowrap;
                overflow: hidden;
                padding: 8px 15px;
                line-height: 1.5em;
                border-radius: 0px;
                -webkit-transition: padding 0.2s linear, background-color 0.3s ease; /* Safari */
                transition: padding 0.2s linear, background-color 0.3s ease;

                .icon {
                    height: auto;
                    width: auto;
                    // i, i::before {
                    //     font-size: 1.125em !important;
                    // }
                }
            }
            svg {
                position: relative;
                top: 3px;
                height: 18px;
                width: 18px;
                fill: var(--tainacan-white);
            }

            a:hover, a.is-active {
                background-color: var(--tainacan-blue3);
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
            .menu-text {
                visibility: hidden;
                opacity: 0;
            }
            .repository-label {
                padding: 9px;
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


