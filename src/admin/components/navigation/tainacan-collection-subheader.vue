<template>
    <div
            id="tainacan-subheader" 
            class="secondary-page">
           
        <div class="back-button is-hidden-mobile">
            <button     
                    @click="$router.go(-1)"
                    class="button is-turquoise4">
                <span class="icon">
                    <i class="mdi mdi-chevron-left"/>
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
                            <i class="mdi mdi-chevron-left"/>
                        </span>
                    </button>
                </div>
                <div class="level-item">
                    <nav class="breadcrumbs">
                        <router-link 
                                tag="a" 
                                :to="$routerHelper.getCollectionsPath()">{{ $i18n.get('repository') }}</router-link>
                        &nbsp;>&nbsp; 
                        <router-link 
                                tag="a" 
                                :to="$routerHelper.getCollectionsPath()">{{ $i18n.get('collections') }}</router-link>
                        &nbsp;>&nbsp; 
                        <router-link 
                                tag="a" 
                                :to="{ path: collectionBreadCrumbItem.url, query: { fromBreadcrumb: true }}">{{ collectionBreadCrumbItem.name }}</router-link> 
                        <template v-for="(childBreadCrumbItem, index) of childrenBreadCrumbItems">
                            <span :key="index">&nbsp;>&nbsp;</span>
                            <router-link    
                                    :key="index"
                                    v-if="childBreadCrumbItem.path != ''"
                                    tag="a"
                                    :to="childBreadCrumbItem.path">{{ childBreadCrumbItem.label }}</router-link>
                            <span 
                                    :key="index"
                                    v-else>{{ childBreadCrumbItem.label }}</span>
                        </template>
                    </nav>
                </div>
            </div>
    
            <ul class="menu-list level-right">
                <li     
                        :class="activeRoute == 'ItemPage' || activeRoute == 'CollectionItemsPage' || activeRoute == 'ItemEditionForm' || activeRoute == 'ItemCreatePage' ? 'is-active':''" 
                        class="level-item">
                    <router-link 
                            tag="a" 
                            :to="{ path: $routerHelper.getCollectionItemsPath(id, '') }" 
                            :aria-label="$i18n.get('label_collection_items')">
                        <b-tooltip 
                                :label="$i18n.get('items')"
                                position="is-bottom">
                            <span class="icon">
                                <i class="mdi mdi-file-multiple"/>
                            </span>
                        </b-tooltip>
                        <!-- <span class="menu-text">{{ $i18n.get('items') }}</span> -->
                    </router-link>
                </li>
                <li 
                        :class="activeRoute == 'CollectionEditionForm' ? 'is-active':''" 
                        class="level-item">
                    <router-link
                            tag="a" 
                            :to="{ path: $routerHelper.getCollectionEditPath(id) }" 
                            :aria-label="$i18n.get('label_settings')">
                        <b-tooltip 
                                :label="$i18n.get('label_settings')"
                                position="is-bottom">
                            <span class="icon">
                                <i class="mdi mdi-settings"/>
                            </span>
                        </b-tooltip>
                        <!-- <span class="menu-text">{{ $i18n.get('label_settings') }}</span> -->
                    </router-link>
                </li>
                <li 
                        :class="activeRoute == 'MetadataList' ? 'is-active':''"
                        class="level-item">
                    <router-link  
                            tag="a" 
                            :to="{ path: $routerHelper.getCollectionMetadataPath(id) }"
                            :aria-label="$i18n.get('label_collection_metadata')">
                        <b-tooltip 
                                :label="$i18n.getFrom('metadata', 'name')"
                                position="is-bottom">
                            <span class="icon">
                                <i class="mdi mdi-format-list-bulleted-type"/>
                            </span>
                        </b-tooltip>
                        <!-- <span class="menu-text">{{ $i18n.getFrom('metadata', 'name') }}</span> -->
                    </router-link>
                </li>
                <li 
                        :class="activeRoute == 'FiltersList' ? 'is-active':''" 
                        class="level-item">
                    <router-link 
                            tag="a" 
                            :to="{ path: $routerHelper.getCollectionFiltersPath(id) }" 
                            :aria-label="$i18n.get('label_collection_filters')">
                        <b-tooltip 
                                animated
                                :label="$i18n.getFrom('filters', 'name')"
                                position="is-bottom">
                            <span class="icon">
                                <i class="mdi mdi-filter"/>
                            </span>
                        </b-tooltip>
                        <!-- <span class="menu-text">{{ $i18n.getFrom('filters', 'name') }}</span> -->
                    </router-link>
                </li>
                <li 
                        :class="activeRoute == 'CollectionEventsPage' ? 'is-active':''"
                        class="level-item">
                    <router-link 
                            tag="a" 
                            :to="{ path: $routerHelper.getCollectionEventsPath(id) }" 
                            :aria-label="$i18n.get('label_collection_events')">
                        <b-tooltip 
                                :label="$i18n.get('events')"
                                position="is-bottom">
                            <activities-icon />
                        </b-tooltip>
                        <!-- <span class="menu-text">{{ $i18n.get('events') }}</span> -->
                    </router-link>
                   
                </li>
            
            </ul>
        </div>
    </div>
</template>

<script>
import { mapActions } from 'vuex';
import ActivitiesIcon from '../other/activities-icon.vue';

export default {
    name: 'TainacanCollectionSubheader',
    data(){
        return {
            activeRoute: 'ItemsList',
            pageTitle: '',
            activeRouteName: '',
            collectionNameRequestCancel: undefined,
            collectionBreadCrumbItem: {},
            childrenBreadCrumbItems: []
        }
    },
    components: {
        ActivitiesIcon
    },
    props: {
        id: Number,
    },
    watch: {
        '$route' (to, from) {
            if (to.path != from.path) {
                this.activeRoute = to.name;

                this.pageTitle = this.$route.meta.title;
            }
        }
    },
    methods: {
        ...mapActions('collection', [
            'fetchCollectionNameAndURL'
        ]),
        collectionBreadCrumbUpdate(breadCrumbItems) {
            this.childrenBreadCrumbItems = breadCrumbItems;
        }
    },
    created() {
        this.activeRoute = this.$route.name;

        this.pageTitle = this.$route.meta.title;

        this.$root.$on('onCollectionBreadCrumbUpdate', this.collectionBreadCrumbUpdate);
    },
    mounted() {

        // Cancels previous Request
        if (this.collectionNameRequestCancel != undefined)
            this.collectionNameRequestCancel.cancel('Collection name Canceled.');
            
        this.fetchCollectionNameAndURL(this.id)
            .then((resp) => {
                resp.request
                    .then(collection => this.collectionBreadCrumbItem = { url: this.$routerHelper.getCollectionPath(this.id), name: collection.name })
                    .catch((error) => this.$console.error(error));
                this.collectionNameRequestCancel = resp.source;
            })
            .catch((error) => this.$console.error(error));

    },
    beforeDestroy() {

        // Cancels previous Request
        if (this.collectionNameRequestCancel != undefined)
            this.collectionNameRequestCancel.cancel('Collection name Canceled.');

        this.$root.$on('onCollectionBreadCrumbUpdate', this.collectionBreadCrumbUpdate);
    }
}
</script>

<style lang="scss" scoped>

    @import "../../scss/_variables.scss";
    
    // Tainacan Header
    #tainacan-subheader {
        background-color: $gray1;
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
        justify-content: space-between;
        
        transition: padding 0.3s, height 0.3s;

        h1 {
            font-size: 18px;
            font-weight: 500;
            color: $blue5;
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
            background-color: $gray1;
            color: $turquoise4;
            display: flex;
            align-items: center;

            button, 
            button:hover, 
            button:focus, 
            button:active {
                width: 100%;
                color: $turquoise4;
                background-color: transparent !important;
                border: none;
                .icon i {
                    font-size: 34px;
                }
            }
        }

        .breadcrumbs {
            font-size: 12px;
            line-height: 12px;
            color: #1d1d1d;
            span {
                text-overflow: ellipsis;
                white-space: nowrap;
                overflow: hidden;
                max-width: 115px;
                margin: 0 0.1rem;
                display: inline-block;
                vertical-align: bottom;
            }
        }

        .level {
            width: 100%;
        }

        li{
            margin-right: 0px;
            transition: background-color 0.2s ease;
            // transition: max-width 0.4s ease-in , width 0.4s ease-in ;
            // -webkit-transition: max-width 0.4s ease-in, width 0.4s ease-in ;
            // overflow: hidden;
            // max-width: 50px;

            svg.activities-icon {
                top: 3px;
                position: relative;
            }

            &.is-active {
                background-color: $turquoise4;
                a { 
                    background-color: $turquoise4;
                    transition: color 0.2s ease;
                    color: white;
                    text-decoration: none;
                }
                svg.activities-icon {
                    transition: fill 0.2s ease;
                    fill: white !important;
                }
            }
            &:hover:not(.is-active) {
                // max-width: 100%;
                // transition: max-width 0.4s ease-out  0.2s, width 0.4s ease-out  0.2s;
                // -webkit-transition: max-width 0.4s ease-out  0.2s, width 0.4s ease-out  0.2s;
                a {
                    background-color: transparent;
                    text-decoration: none; 
                    color: $turquoise5;
                }
                svg.activities-icon {
                    fill: $turquoise5 !important;
                }
                // .menu-text {
                //     opacity: 1.0;
                //     width: 100%;
                //     right: 0%;
                //     visibility: visible;
                //     transition: opacity 0.4s ease-out 0.2s, visibility 0.4s ease-out  0.2s, width 0.4s ease-out  0.2s, right 0.4s ease-out  0.2s;
                //     -webkit-transition: opacity 0.4s ease-out  0.2s , visibility 0.4s ease-out  0.2s, width 0.4s ease-out  0.2s, right 0.4s ease-out  0.2s;
                // }
            }
            a {
                color: $gray4;
                text-align: center;
                white-space: nowrap;
                padding: 9px;
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
                    font-size: 18px !important;
                }
            }
            .menu-text {
                margin-left: 8px;
                font-size: 14px;
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
                padding: 0 1rem;
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
            background-color: $turquoise1;
            color: $turquoise5;
        }
        .tooltip.is-primary::before {
            border-bottom-color: $turquoise1;
        }

    }
</style>


