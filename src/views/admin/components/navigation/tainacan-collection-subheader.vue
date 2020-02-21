<template>
    <div
            id="tainacan-subheader" 
            class="secondary-page">
           
        <div class="back-button is-hidden-mobile">
            <button     
                    @click="$router.go(-1)"
                    class="button is-turquoise4">
                <span class="icon">
                    <i class="tainacan-icon tainacan-icon-previous"/>
                </span>
            </button>
        </div>
        <div class="level">      
            <div class="level-left">
                <div class="back-button is-hidden-tablet level-item">
                    <button     
                            @click="$router.go(-1)"
                            class="button is-turquoise4">
                        <span class="icon">
                            <i class="tainacan-icon tainacan-icon-previous"/>
                        </span>
                    </button>
                </div>
            </div>
    
            <ul class="menu-list level-right">
                <li     
                        :class="activeRoute == 'ItemPage' || activeRoute == 'CollectionItemsPage' || activeRoute == 'ItemEditionForm' || activeRoute == 'ItemCreatePage' ? 'is-active':''" 
                        class="level-item"
                        v-tooltip="{
                            delay: {
                                show: 500,
                                hide: 100,
                            },
                            content: $i18n.get('items'),
                            autoHide: false,
                            placement: 'bottom-start',
                            classes: ['header-tooltips']
                        }">
                    <router-link 
                            tag="a" 
                            :to="{ path: collection && collection.id ? $routerHelper.getCollectionItemsPath(collection.id, '') : '' }" 
                            :aria-label="$i18n.get('label_collection_items')">               
                        <span class="icon">
                            <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-items"/>
                        </span>
                        <span class="menu-text">{{ $i18n.get('items') }}</span>
                    </router-link>
                </li>
                <li 
                        v-if="collection && collection.current_user_can_edit"
                        :class="activeRoute == 'CollectionEditionForm' ? 'is-active':''" 
                        class="level-item"
                        v-tooltip="{
                            delay: {
                                show: 500,
                                hide: 100,
                            },
                            content: $i18n.get('label_settings'),
                            autoHide: false,
                            placement: 'bottom-start',
                            classes: ['header-tooltips']
                        }">
                    <router-link
                            tag="a" 
                            :to="{ path: collection && collection.id ? $routerHelper.getCollectionEditPath(collection.id) : '' }" 
                            :aria-label="$i18n.get('label_settings')">
                        <span class="icon">
                            <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-settings"/>
                        </span>
                    
                    <span class="menu-text">{{ $i18n.get('label_settings') }}</span>
                    </router-link>
                </li>
                <li 
                        v-if="collection && collection.current_user_can_edit_metadata"
                        :class="activeRoute == 'CollectionMetadataPage' ? 'is-active':''"
                        class="level-item"
                        v-tooltip="{
                            delay: {
                                show: 500,
                                hide: 100,
                            },
                            content: $i18n.getFrom('metadata', 'name'),
                            autoHide: false,
                            placement: 'bottom-start',
                            classes: ['header-tooltips']
                        }">
                    <router-link  
                            tag="a" 
                            :to="{ path: collection && collection.id ? $routerHelper.getCollectionMetadataPath(collection.id) : '' }"
                            :aria-label="$i18n.get('label_collection_metadata')">
                        <span class="icon">
                            <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-metadata"/>
                        </span>
                    <span class="menu-text">{{ $i18n.getFrom('metadata', 'name') }}</span>
                    </router-link>
                </li>
                <li 
                        v-if="collection && collection.current_user_can_edit_filters"
                        :class="activeRoute == 'CollectionFiltersPage' ? 'is-active':''" 
                        class="level-item"
                        v-tooltip="{
                            delay: {
                                show: 500,
                                hide: 100,
                            },
                            content: $i18n.getFrom('filters', 'name'),
                            autoHide: false,
                            placement: 'bottom-start',
                            classes: ['header-tooltips']
                        }">
                    <router-link 
                            tag="a" 
                            :to="{ path: collection && collection.id ? $routerHelper.getCollectionFiltersPath(collection.id) : ''}" 
                            :aria-label="$i18n.get('label_collection_filters')">
                        <span class="icon">
                            <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-filters"/>
                        </span>
                    <span class="menu-text">{{ $i18n.getFrom('filters', 'name') }}</span>
                    </router-link>
                </li>
                <li 
                        v-if="$userCaps.hasCapability('tnc_rep_read_logs')"
                        :class="activeRoute == 'CollectionActivitiesPage' ? 'is-active':''"
                        class="level-item"
                        v-tooltip="{
                            delay: {
                                show: 500,
                                hide: 100,
                            },
                            content: $i18n.get('activities'),
                            autoHide: false,
                            placement: 'bottom-start',
                            classes: ['header-tooltips']
                        }">
                    <router-link 
                            tag="a" 
                            :to="{ path: collection && collection.id ? $routerHelper.getCollectionActivitiesPath(collection.id) : '' }"
                            :aria-label="$i18n.get('label_collection_activities')">
                        <span class="icon">
                            <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-activities"/>
                        </span>
                        <span class="menu-text">{{ $i18n.get('activities') }}</span>
                    </router-link>                
                </li>
                <li 
                        v-if="collection && collection.current_user_can_edit_users"
                        :class="activeRoute == 'CollectionCapabilitiesPage' ? 'is-active':''"
                        class="level-item"
                        v-tooltip="{
                            delay: {
                                show: 500,
                                hide: 100,
                            },
                            content: $i18n.get('capabilities'),
                            autoHide: false,
                            placement: 'bottom-start',
                            classes: ['header-tooltips']
                        }">
                    <router-link 
                            tag="a" 
                            :to="{ path: collection && collection.id ? $routerHelper.getCollectionCapabilitiesPath(collection.id) : '' }"
                            :aria-label="$i18n.get('label_collection_capabilities')">
                        <span class="icon">
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
            
            </ul>
        </div>
    </div>
</template>

<script>
import { mapGetters } from 'vuex';

export default {
    name: 'TainacanCollectionSubheader',
    data(){
        return {
            activeRoute: 'ItemsList',
            pageTitle: '',
            activeRouteName: '',
        }
    },
    computed: {
        collection() {
            return this.getCollection();
        }
    },
    watch: {
        '$route' (to, from) {
            if (to.path != from.path) {
                this.activeRoute = to.name;
                this.pageTitle = this.$route.meta.title;
            }
        }
    },
    created() {
        this.activeRoute = this.$route.name;

        this.pageTitle = this.$route.meta.title;   
    },
    methods: {
        ...mapGetters('collection', [
            'getCollection'
        ])
    }
}
</script>

<style lang="scss" scoped>

    @import "../../scss/_variables.scss";

    .header-tooltips .tooltip-inner {
        color: turquoise5;
        text-shadow: none;
        background-color: var(--tainacan-turquoise2);
        font-size: 0.75em;
        font-weight: 400;
        padding: 10px 14px;
    }
    .header-tooltips .tooltip-arrow {
        border-color: var(--tainacan-turquoise2);
    }
    
    // Tainacan Header
    #tainacan-subheader {
        background-color: var(--tainacan-turquoise5);
        height: $subheader-height;
        max-height: $subheader-height;
        width: 100%;
        padding-top: 18px;
        padding-bottom: 18px;
        padding-right: $page-side-padding;
        padding-left: 0;
        margin: 0px;
        vertical-align: middle; 
        left: 0;
        right: 0;
        z-index: 9;
        display: flex;
        align-items: center;
        transition: padding 0.3s, height 0.3s;

        h1 {
            font-size: 1.125em;
            font-weight: 500;
            color: var(--tainacan-white);
            line-height: 22px;
            margin-bottom: 12px; 
            max-width: 450px;
            text-overflow: ellipsis;
            white-space: nowrap;
            overflow: hidden;  
        }

        .back-button {
            padding: 0;
            margin: 0;
            height: 42px;
            width: $page-side-padding;
            min-width: $page-side-padding;
            color: var(--tainacan-white);
            display: flex;

            button, 
            button:hover, 
            button:focus, 
            button:active {
                width: 100%;
                color: var(--tainacan-white);
                background-color: transparent !important;
                border: none;
                height: 42px !important;
                .icon {
                    margin-top: -2px;
                    font-size: 22px;
                }
            }
        }

        li {
            margin-right: 0px;
            transition: background-color 0.2s ease;
            // transition: max-width 0.4s ease-in , width 0.4s ease-in ;
            // -webkit-transition: max-width 0.4s ease-in, width 0.4s ease-in ;
            // overflow: hidden;
            // max-width: 50px;

            &.is-active {
                background-color: var(--tainacan-turquoise4);
                a { 
                    background-color: var(--tainacan-turquoise4);
                    transition: color 0.2s ease;
                    color: var(--tainacan-white);
                    text-decoration: none;
                }
                svg {
                    fill: var(--tainacan-white) !important;
                }
            }
            &:hover:not(.is-active) {
                background-color: var(--tainacan-turquoise4);

                a {
                    background-color: transparent;
                    text-decoration: none; 
                }
            }
            a {
                color: var(--tainacan-white);
                text-align: center;
                white-space: nowrap;
                padding: 9px 11px;
                min-width: 50px;
                line-height: 22px;
                border-radius: 0px;
                position: relative;
                align-items: center;
                display: block;
            }
            a:focus{
                box-shadow: none;
            }
            .icon {
                margin: 0;
                padding: 0;
                i {
                    font-size: 1.125em !important;
                }
                svg {
                    position: relative;
                    top: 2px;
                    margin-bottom: 2px;
                    height: 16px;
                    fill: var(--tainacan-white);
                }
            }
            .menu-text {
                margin-left: 2px;
                font-size: 0.875em;
                display: inline-flex;
                // width: 0px;
                // right: 100%;
                // opacity: 0.0;
                // visibility: hidden;
                // transition: opacity 0.4s ease-in, visibility 0.4s ease-in , width 0.2s ease-in, right 0.2s ease-in;
                // -webkit-transition: opacity 0.4s ease-in, visibility 0.4s ease-in, width 0.2s ease-in, right 0.2s ease-in;
            }
        }

        @media screen and (max-width: 769px) {
            width: 100% !important;
            max-width: 100% !important;
            height: 85px;
            max-height: 85px;
            padding: 0;
            top: 206px;
            margin-bottom: 0px !important;
            
            .level-left {
                margin-left: 0px !important;
                display: flex;
                padding: 0 1em;
                .level-item {
                    display: inline-flex;
                }
            }

            .level-right { 
                margin-top: 0px;
                flex-flow: wrap;
                display: flex;
                align-items: baseline;
                justify-content: space-between;

                .level-item {
                    margin-bottom: 0;
                    a { 
                        padding: 0.5em 0.7em !important; 
                        text-align: center;
                    }
                    .menu-text {
                        display: none;
                    }
                }
            }
        }

        .tooltip.is-primary {
            z-index: 99;
        }
        .tooltip.is-primary::after {
            background-color: var(--tainacan-turquoise1);
            color: var(--tainacan-turquoise5);
        }
        .tooltip.is-primary::before {
            border-bottom-color: var(--tainacan-turquoise1);
        }

    }
</style>


