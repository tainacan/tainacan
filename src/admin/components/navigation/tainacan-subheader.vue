<template>
    <div
            id="tainacan-subheader" 
            class="level" 
            :class="{'secondary-page': onSecondaryPage}">
        <div class="level-left">
            <div class="level-item">
                <h1 class="has-text-weight-bold is-uppercase has-text-tertiary"><b-icon 
                        size="is-small" 
                        :icon="currentIcon"/>{{ pageTitle }}</h1>
                <nav class="breadcrumbs">
                    <router-link 
                            tag="a" 
                            :to="$routerHelper.getCollectionsPath()">{{ $i18n.get('repository') }}</router-link> > 
                    <span 
                            v-for="(pathItem, index) in arrayRealPath" 
                            :key="index">
                        <router-link 
                                tag="a" 
                                :to="'/' + arrayRealPath.slice(0, index + 1).join('/')">
                            {{ arrayViewPath[index] }}
                        </router-link>
                        <span v-if="index != arrayRealPath.length - 1"> > </span>
                    </span>   
                </nav>
            </div>
        </div>
        <ul class="menu-list level-right">
            <li class="level-item"><router-link  
                    tag="a" 
                    :to="{ path: $routerHelper.getCollectionItemsPath(id, '') }" 
                    :class="activeRoute == 'ItemPage' || activeRoute == 'CollectionItemsPage' || activeRoute == 'ItemEditionForm' || activeRoute == 'ItemCreatePage' ? 'is-active':''" 
                    :aria-label="$i18n.get('label_collection_fields')">
                <b-icon 
                        size="is-small" 
                        icon="file-multiple"/>
                <br>
                <span class="menu-text">{{ $i18n.getFrom('items', 'name') }}</span>
            </router-link></li>
            <li class="level-item"><router-link 
                    tag="a" 
                    :to="{ path: $routerHelper.getCollectionEditPath(id) }" 
                    :class="activeRoute == 'CollectionEditionForm' ? 'is-active':''" 
                    :aria-label="$i18n.getFrom('collections','edit_item')">
                <b-icon 
                        size="is-small" 
                        icon="pencil"/>
                <br>
                <span class="menu-text">{{ $i18n.get('edit') }}</span>
            </router-link></li>
            <li class="level-item"><router-link 
                    tag="a" 
                    :to="{ path: $routerHelper.getCollectionFieldsPath(id) }" 
                    :class="activeRoute == 'FieldsList' ? 'is-active':''" 
                    :aria-label="$i18n.get('label_collection_fields')">
                <b-icon 
                    size="is-small" 
                    icon="format-list-checks"/>
                <br>
                <span class="menu-text">{{ $i18n.getFrom('fields', 'name') }}</span>
            </router-link></li>
            <li class="level-item"><router-link 
                    tag="a" 
                    :to="{ path: $routerHelper.getCollectionFiltersPath(id) }" 
                    :class="activeRoute == 'FiltersList' ? 'is-active':''" 
                    :aria-label="$i18n.get('label_collection_filters')">
                <b-icon 
                        size="is-small" 
                        icon="filter"/>
                <br>
                <span class="menu-text">{{ $i18n.getFrom('filters', 'name') }}</span>
            </router-link></li>
          
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
            currentIcon: ''
        }
    },
    props: {
        id: Number
    },
    watch: {
        '$route' (to) {
            this.activeRoute = to.name;

            this.onSecondaryPage = (to.params.collectionId != undefined);
            this.pageTitle = this.$route.meta.title;
            this.currentIcon = this.$route.meta.icon;

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

        this.onSecondaryPage = (this.$route.params.collectionId != undefined);
        this.pageTitle = this.$route.meta.title;
        this.currentIcon = this.$route.meta.icon;

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
        border-bottom: 0.5px solid #ddd;
        padding: 1.0em;
        margin: 0px;
        vertical-align: middle; 
        left: 0;
        right: 0;
        z-index: 9;

        .icon {
            padding-right: 1.3em;
            margin-left: -1.3em;
        }

        .breadcrumbs {
            font-size: 0.85em;
        }

        .level-left {
            .level-item {
                display: inline-block;
                margin-left: 45px;
            }  
        }

        li{
            a {
                color: $secondary;
                text-align: center;
                white-space: nowrap;
                overflow: hidden;
                padding: 1.0em 1.8em;
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
                background-color: rgba(255,255,255,0.6);
                color: $tertiary;
            }
            .icon {
                margin: 0;
                padding: 0;
            }
            .menu-text {
                opacity: 1;
                visibility: visible;
                transition: opacity 0.3s linear, visibility 0.3s linear;
                -webkit-transition: opacity 0.3s linear, visibility 0.3s linear;
            }
        }

        @media screen and (max-width: 769px) {
            width: 100% !important;
            max-width: 100% !important;
            ul { 
                flex-flow: wrap;
                display: flex;
                align-items: stretch;
                justify-content: space-evenly;
                a { 
                    padding: 0.5em 0.7em !important; 
                    text-align: center;
                }
                .menu-text {
                    padding-left: 0.3em !important;
                }
            }
        }

        @media screen and (max-width: 769px) {
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


