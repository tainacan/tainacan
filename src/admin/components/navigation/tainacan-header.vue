<template>
    <div id="tainacan-header" class="level">
        <div class="level-left">
            <div class="level-item" :class="{'secondary-page': onSecondaryPage}">
                <h1 class="has-text-weight-bold is-uppercase has-text-primary"><b-icon size="is-small" :icon="currentIcon"></b-icon>{{pageTitle}}</h1>
                <nav class="breadcrumbs">
                    <router-link tag="a" :to="$routerHelper.getCollectionsPath()">{{ $i18n.get('repository') }}</router-link> > 
                    <span v-for="(pathItem, index) in arrayRealPath" :key="index">
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
        <div class="level-right">
            <a class="level-item" :href="wordpressAdmin">
                <b-icon icon="close"></b-icon>
            </a>
        </div>
    </div>
</template>

<script>
import { mapActions, mapGetters } from 'vuex';

export default {
    name: 'TainacanHeader',
    data(){
        return {
            wordpressAdmin: window.location.origin + window.location.pathname.replace('admin.php', ''),
            onSecondaryPage: false,
            pageTitle: '',
            arrayRealPath: [],
            arrayViewPath: [],
            activeRouteName: '',
            currentIcon: ''
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
        generateViewPath() {

            for (let i = 0; i < this.arrayRealPath.length; i++) {
                
                this.arrayViewPath.push('');
                
                if (!isNaN(this.arrayRealPath[i]) && i > 0) {
                    
                    switch(this.arrayRealPath[i-1]) {
                        case 'collections':
                            this.fetchCollectionName(this.arrayRealPath[i])
                                .then(collectionName => this.arrayViewPath.splice(i, 1, collectionName))
                                .catch((error) => console.log(error));
                        break;
                        case 'items':
                            this.fetchItemTitle(this.arrayRealPath[i])
                                .then(itemTitle => this.arrayViewPath.splice(i, 1,itemTitle))
                                .catch((error) => console.log(error));
                        break;
                    }
                    
                } else {
                    this.arrayViewPath.splice(i, 1, this.$i18n.get(this.arrayRealPath[i])); 
                }
                
            }
        }
    },
    watch: {
        '$route' (to, from) {
            this.onSecondaryPage = (to.params.collectionId != undefined);
            this.pageTitle = this.$route.meta.title;
            this.currentIcon = this.$route.meta.icon;

            this.arrayRealPath = to.path.split("/");
            this.arrayRealPath = this.arrayRealPath.filter((item) => item.length != 0);
            
            this.generateViewPath();
        }
    },
    created() {
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
    #tainacan-header{
        background-color: $header-color;
        height: $header-height;
        max-height: $header-height;
        width: 100%;
        border-bottom: 0.5px solid #ddd;
        padding: 1.0em;
        vertical-align: middle; 
        left: 0;
        right: 0;
        position: absolute;
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
                margin-left: 268px;
            }
            .secondary-page {
                margin-left: 310px;
            }
            
        }

        @media screen and (max-width: 769px) {
            .level-left {
                .level-item {
                    margin-left: 0px;
                }
            }
            .level-right {
                display: none;
            }

            position: relative !important;
            margin-bottom: 0px !important;
        }

    }
</style>


