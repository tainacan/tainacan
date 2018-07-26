<template>
    <div
            id="tainacan-subheader" 
            class="level secondary-page">
        <div class="level-left">
            <div class="level-item">
                <div class="back-button">
                    <button     
                            @click="$router.go(-1)"
                            class="button is-turquoise4">
                        <span class="icon">
                            <i class="mdi mdi-chevron-left"/>
                        </span>
                    </button>
                </div>
                <nav class="breadcrumbs">
                    <router-link 
                            tag="a" 
                            :to="$routerHelper.getCollectionsPath()">{{ $i18n.get('repository') }}</router-link> > 
                    <span 
                            v-for="(pathItem, index) in arrayRealPath" 
                            :key="index">
                        <router-link
                                v-if="index < arrayRealPath.length - 1" 
                                tag="a" 
                                :to="'/' + arrayRealPath.slice(0, index + 1).join('/')">
                            {{ arrayViewPath[index] }}
                        </router-link>
                        <span v-if="index == arrayRealPath.length - 1">{{ arrayViewPath[index] }}</span>
                        <span v-if="index != arrayRealPath.length - 1 && arrayViewPath[index]"> > </span>
                    </span>   
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
                    <span class="icon">
                        <i class="mdi mdi-file-multiple"/>
                    </span>
                    <span class="menu-text">{{ $i18n.get('items') }}</span>
                </router-link>
            </li>
            <li 
                    :class="activeRoute == 'CollectionEditionForm' ? 'is-active':''" 
                    class="level-item">
                <router-link
                        tag="a" 
                        :to="{ path: $routerHelper.getCollectionEditPath(id) }" 
                        :aria-label="$i18n.get('label_settings')">
                    <span class="icon">
                        <i class="mdi mdi-settings"/>
                    </span>
                    <span class="menu-text">{{ $i18n.get('label_settings') }}</span>
                </router-link>
            </li>
            <li 
                    :class="activeRoute == 'MetadataList' ? 'is-active':''"
                    class="level-item">
                <router-link 
                        tag="a" 
                        :to="{ path: $routerHelper.getCollectionMetadataPath(id) }"
                        :aria-label="$i18n.get('label_collection_metadata')">
                    <span class="icon">
                        <i class="mdi mdi-format-list-bulleted-type"/>
                    </span>
                    <span class="menu-text">{{ $i18n.getFrom('metadata', 'name') }}</span>
                </router-link>
            </li>
            <li 
                    :class="activeRoute == 'FiltersList' ? 'is-active':''" 
                    class="level-item">
                <router-link
                        tag="a" 
                        :to="{ path: $routerHelper.getCollectionFiltersPath(id) }" 
                        :aria-label="$i18n.get('label_collection_filters')">
                    <span class="icon">
                        <i class="mdi mdi-filter"/>
                    </span>
                    <span class="menu-text">{{ $i18n.getFrom('filters', 'name') }}</span>
                </router-link>
            </li>
            <li 
                    :class="activeRoute == 'CollectionEventsPage' ? 'is-active':''"
                    class="level-item">
                <router-link 
                        tag="a" 
                        :to="{ path: $routerHelper.getCollectionEventsPath(id) }" 
                        :aria-label="$i18n.get('label_collection_events')">
                    <activities-icon />
                    <span class="menu-text">{{ $i18n.get('events') }}</span>
                </router-link>
            </li>
          
        </ul>
    </div>
</template>

<script>
import { mapActions, mapGetters } from 'vuex';
import ActivitiesIcon from '../other/activities-icon.vue';

export default {
    name: 'TainacanCollectionSubheader',
    data(){
        return {
            activeRoute: 'ItemsList',
            pageTitle: '',
            arrayRealPath: [],
            arrayViewPath: [],
            activeRouteName: '',
        }
    },
    components: {
        ActivitiesIcon
    },
    props: {
        id: Number,
    },
    watch: {
        '$route' (to) {

            this.activeRoute = to.name;

            this.pageTitle = this.$route.meta.title;

            this.arrayRealPath = to.path.split("/");
            this.arrayRealPath = this.arrayRealPath.filter((item) => item.length != 0);
            
            this.generateViewPath();
        }
    },
    methods: {
        ...mapActions('collection', [
            'fetchCollectionName'
        ]),
        ...mapGetters('collection', [
            'getCollectionName',
            'getCollection'
        ]),
        ...mapActions('item', [
            'fetchItemTitle'
        ]),
        ...mapGetters('item', [
            'getItemTitle'
        ]),
        ...mapActions('taxonomy', [
            'fetchTaxonomyName'
        ]),
        ...mapGetters('taxonomy', [
            'getTaxonomyName'
        ]),
        ...mapActions('event', [
            'fetchEventTitle'
        ]),
        generateViewPath() {

            for (let i = 0; i < this.arrayRealPath.length; i++) {
                
                this.arrayViewPath.push('');
                
                if (!isNaN(this.arrayRealPath[i]) && i > 0) {
                    
                    switch(this.arrayRealPath[i-1]) {
                        case 'collections':
                            this.fetchCollectionName(this.arrayRealPath[i])
                                .then(collectionName => this.arrayViewPath.splice(i, 1, collectionName))
                                .catch((error) => this.$console.error(error));

                            break;
                        case 'items':
                            this.fetchItemTitle(this.arrayRealPath[i])
                                .then(itemTitle => this.arrayViewPath.splice(i, 1, itemTitle))
                                .catch((error) => this.$console.error(error));
                            break;
                        case 'taxonomies':
                            this.fetchTaxonomyName(this.arrayRealPath[i])
                                .then(taxonomyName => this.arrayViewPath.splice(i, 1, taxonomyName))
                                .catch((error) => this.$console.error(error));
                            break;
                        case 'events':
                            this.fetchEventTitle(this.arrayRealPath[i])
                                .then(eventName => this.arrayViewPath.splice(i, 1, eventName))
                                .catch((error) => this.$console.error(error));
                            break;
                    }
                    
                } else {
                    if(this.arrayRealPath[i] == 'undefined'){
                        this.arrayViewPath.splice(i, 1, '');
                    } else {
                        this.arrayViewPath.splice(i, 1, this.$i18n.get(this.arrayRealPath[i]));
                    }
                }
                
            }
        }
    },
    created() {
        this.activeRoute = this.$route.name;

        this.pageTitle = this.$route.meta.title;

        this.arrayRealPath = this.$route.path.split("/");
        this.arrayRealPath = this.arrayRealPath.filter((item) => item.length != 0);

        this.generateViewPath();
    }
}
</script>

<style lang="scss" scoped>

    @import "../../scss/_variables.scss";
    
    // Tainacan Header
    #tainacan-subheader {
        background-color: $gray2;
        height: $subheader-height;
        max-height: $subheader-height;
        width: 100%;
        overflow-y: hidden;
        padding-top: 18px;
        padding-bottom: 18px;
        padding-right: $page-side-padding;
        padding-left: 0;
        margin: 0px;
        vertical-align: middle; 
        left: 0;
        right: 0;
        z-index: 9;
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
            -webkit-transition: margin-bottom 0.2s linear; /* Safari */
            transition: margin-bottom 0.2s linear; 
        }

        .back-button {
            padding: 0;
            margin: 0 12px 0 0;
            height: 42px;
            width: 42px;
            background-color: $turquoise4;
            color: white;
            display: flex;
            align-items: center;

            button, 
            button:hover, 
            button:focus, 
            button:active{
                color: white;
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
        }

        li{
            margin-right: 0px;
            transition: max-width 0.4s linear, width 0.4s linear;
            -webkit-transition: max-width 0.4s linear, width 0.4s linear;
            overflow: hidden;
            max-width: 50px;

            &.is-active {
                background-color: $turquoise4;
                a { 
                    background-color: $turquoise4;
                    color: white;
                    text-decoration: none;
                }
                svg.activities-icon {
                    fill: white !important;
                }
            }
            &:hover {
                max-width: 100%;
                transition: max-width 0.4s linear, width 0.4s linear;
                -webkit-transition: max-width 0.4s linear, width 0.4s linear;
                 a {
                     background-color: transparent;
                     text-decoration: none; 
                 }
                .menu-text {
                    opacity: 1.0;
                    width: 100%;
                    visibility: visible;
                    transition: opacity 0.2s linear, visibility 0.2s linear, width 0.4s linear;
                    -webkit-transition: opacity 0.2s linear, visibility 0.2s linear, width 0.4s linear;
                }
            }
            a {
                color: $gray4;
                text-align: center;
                white-space: nowrap;
                padding: 1.0em 10px;
                min-width: 50px;
                line-height: 22px;
                border-radius: 0px;
                position: relative;
            }
            a:focus{
                box-shadow: none;
            }
            .icon {
                margin: 0;
                padding: 0;

                i {
                    font-size: 19px !important;
                }
            }
            .menu-text {
                font-size: 14px;
                display: inline-flex;
                width: 0px;
                opacity: 0.0;
                visibility: hidden;
                transition: opacity 0.2s linear, visibility 0.2s linear, width 0.4s linear;
                -webkit-transition: opacity 0.2s linear, visibility 0.2s linear, width 0.4s linear;
            }
        }

        @media screen and (max-width: 769px) {
            width: 100% !important;
            max-width: 100% !important;
            height: 143px;
            max-height: 143px;
            
            ul { 
                margin-top: 12px;
                flex-flow: wrap;
                display: flex;
                align-items: baseline;
                justify-content: space-between;

                a { 
                    padding: 0.5em 0.7em !important; 
                    text-align: center;
                }
                .menu-text {
                    padding-left: 0.3em !important;
                }
            }
      
            .level-left {
                margin-left: 0px !important;
            }
            top: 206px;
            margin-bottom: 0px !important;
        }

    }
</style>


