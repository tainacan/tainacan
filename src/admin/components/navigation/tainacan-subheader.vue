<template>
    <div
            id="tainacan-subheader" 
            class="level secondary-page">
        <div class="level-left">
            <div class="level-item">
                <h1>{{ getCollectionName() }}</h1>
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
                        <span v-if="index != arrayRealPath.length - 1"> > </span>
                    </span>   
                </nav>
            </div>
        </div>
        <ul class="menu-list level-right">
            <li class="level-item">
                <router-link  
                        tag="a" 
                        :to="{ path: $routerHelper.getCollectionItemsPath(id, '') }" 
                        :class="activeRoute == 'ItemPage' || activeRoute == 'CollectionItemsPage' || activeRoute == 'ItemEditionForm' || activeRoute == 'ItemCreatePage' ? 'is-active':''" 
                        :aria-label="$i18n.get('label_collection_items')">
                    <b-icon 
                            size="is-small" 
                            icon="folder-outline"/>
                    <br>
                    <span class="menu-text">{{ $i18n.getFrom('collections', 'singular_name') }}</span>
                </router-link>
            </li>
            <li class="level-item">
                <router-link
                        tag="a" 
                        :to="{ path: $routerHelper.getCollectionEditPath(id) }" 
                        :class="activeRoute == 'CollectionEditionForm' ? 'is-active':''" 
                        :aria-label="$i18n.getFrom('collections','edit_item')">
                    <b-icon 
                            size="is-small" 
                            icon="pencil"/>
                    <br>
                    <span class="menu-text">{{ $i18n.get('edit') }}</span>
                </router-link>
            </li>
            <li class="level-item">
                <router-link 
                        tag="a" 
                        :to="{ path: $routerHelper.getCollectionFieldsPath(id) }" 
                        :class="activeRoute == 'FieldsList' ? 'is-active':''" 
                        :aria-label="$i18n.get('label_collection_fields')">
                    <b-icon 
                        size="is-small" 
                        icon="format-list-bulleted-type"/>
                    <br>
                    <span class="menu-text">{{ $i18n.getFrom('fields', 'name') }}</span>
                </router-link>
            </li>
            <li class="level-item">
                <router-link
                        tag="a" 
                        :to="{ path: $routerHelper.getCollectionFiltersPath(id) }" 
                        :class="activeRoute == 'FiltersList' ? 'is-active':''" 
                        :aria-label="$i18n.get('label_collection_filters')">
                    <b-icon 
                            size="is-small" 
                            icon="filter"/>
                    <br>
                    <span class="menu-text">{{ $i18n.getFrom('filters', 'name') }}</span>
                </router-link>
            </li>
            <li class="level-item">
                <router-link 
                        tag="a" 
                        :to="{ path: $routerHelper.getCollectionEventsPath(id) }" 
                        :class="activeRoute == 'CollectionEventsPage' ? 'is-active':''"
                        :aria-label="$i18n.get('label_collection_events')">
                    <b-icon 
                            size="is-small" 
                            icon="calendar-range"/>
                    <br>
                    <span class="menu-text">{{ $i18n.get('events') }}</span>
                </router-link>
            </li>
          
        </ul>
    </div>
</template>

<script>
import { mapActions, mapGetters } from 'vuex';

export default {
    name: 'TainacanSubheader',
    data(){
        return {
            activeRoute: 'ItemsList',
            pageTitle: '',
            arrayRealPath: [],
            arrayViewPath: [],
            activeRouteName: '',
        }
    },
    props: {
        id: Number
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
            'getCollectionName'
        ]),
        ...mapActions('item', [
            'fetchItemTitle'
        ]),
        ...mapGetters('item', [
            'getItemTitle'
        ]),
        ...mapActions('category', [
            'fetchCategoryName'
        ]),
        ...mapGetters('category', [
            'getCategoryName'
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
                        case 'categories':
                            this.fetchCategoryName(this.arrayRealPath[i])
                                .then(categoryName => this.arrayViewPath.splice(i, 1, categoryName))
                                .catch((error) => this.$console.error(error));
                            break;
                        case 'events':
                            this.fetchEventTitle(this.arrayRealPath[i])
                                .then(eventName => this.arrayViewPath.splice(i, 1, eventName))
                                .catch((error) => this.$console.error(error));
                            break;
                    }
                    
                } else {
                    this.arrayViewPath.splice(i, 1, this.$i18n.get(this.arrayRealPath[i])); 
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
        background-color: $primary-lighter;
        height: $subheader-height;
        max-height: $subheader-height;
        width: 100%;
        padding-top: 18px;
        padding-bottom: 18px;
        padding-right: $page-side-padding;
        padding-left: $page-side-padding;
        margin: 0px;
        vertical-align: middle; 
        left: 0;
        right: 0;
        z-index: 9;

        h1 {
            font-size: 18px;
            font-weight: 500;
            color: $tertiary;
            line-height: 22px;
            margin-bottom: 12px; 
            max-width: 450px;
            text-overflow: ellipsis;
            white-space: nowrap;
            overflow: hidden;  
        }

        .breadcrumbs {
            font-size: 12px;
            line-height: 12px;
            color: #1d1d1d;
        }

        .level-left {
            margin-left: 5px;
            .level-item {
                display: inline-block;
            }  
        }

        li{
            margin-right: 0px;
            a {
                color: $tertiary;
                text-align: center;
                white-space: nowrap;
                overflow: hidden;
                padding: 1.0em 10px;
                min-width: 75px;
                line-height: 1.5em;
                border-radius: 0px;
                -webkit-transition: padding 0.3s linear; /* Safari */
                transition: padding 0.3s linear; 
                position: relative;
                overflow: inherit;
            }
            a:hover, 
            a.is-active {
                background-color: #d1f1f2;
            }
            a:focus{
                box-shadow: none;
            }
            a.is-active:after {
                position: absolute;
                content: '';
                width: 0;
                height: 0;
                bottom: -1px;
                border-left: 10px solid transparent;
                border-right: 10px solid transparent;
                border-bottom: 11px solid white;
                left: calc(50% - 10px);
                -moz-transform: scale(0.999);
                -webkit-backface-visibility: hidden;   
            }
            .icon {
                margin: 0;
                padding: 0;
            }
            .menu-text {
                font-size: 14px;
                opacity: 1;
                visibility: visible;
                transition: opacity 0.3s linear, visibility 0.3s linear;
                -webkit-transition: opacity 0.3s linear, visibility 0.3s linear;
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
                .level-item {
                    margin-left: 30px;
                }
            }
            top: 206px;
            margin-bottom: 0px !important;
        }

    }
</style>


