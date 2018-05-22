<template>
    <div 
            class="tainacan-page-title"
            id="title-row">
        <h1>{{ pageTitle }} <span class="is-italic">{{ isRepositoryLevel ? '' : entityName }}</span></h1>
        <a 
                @click="$router.go(-1)"
                class="back-link is-secondary">
            {{ $i18n.get('back') }}
        </a>
        <hr>
        <nav 
                v-show="isRepositoryLevel"
                class="breadcrumbs">
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
</template>

<script>
import { mapActions, mapGetters } from 'vuex';

export default {
    name: 'TainacanTitle',
    data(){
        return {
            wordpressAdmin: window.location.origin + window.location.pathname.replace('admin.php', ''),
            isRepositoryLevel: true,
            pageTitle: '',
            arrayRealPath: [],
            arrayViewPath: [],
            activeRouteName: '',
            entityName: ''
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
                                .then(collectionName => { this.arrayViewPath.splice(i, 1, collectionName); this.entityName = collectionName; })
                                .catch((error) => this.$console.error(error));
                            break;
                        case 'items':
                            this.fetchItemTitle(this.arrayRealPath[i])
                                .then(itemName => { this.arrayViewPath.splice(i, 1, itemName); this.entityName = itemName; })
                                .catch((error) => this.$console.error(error));
                            break;
                        case 'taxonomies':
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
    watch: {
        '$route' (to) {
            this.isRepositoryLevel = (to.params.collectionId == undefined);
            this.pageTitle = this.$route.meta.title;

            this.arrayRealPath = to.path.split("/");
            this.arrayRealPath = this.arrayRealPath.filter((item) => item.length != 0);
            
            this.generateViewPath();
        }
    },
    created() {
        this.isRepositoryLevel = (this.$route.params.collectionId == undefined);
        document.title = this.$route.meta.title;
        this.pageTitle = document.title;

        this.arrayRealPath = this.$route.path.split("/");
        this.arrayRealPath = this.arrayRealPath.filter((item) => item.length != 0);
        
        this.generateViewPath();
    }
}
</script>

<style lang="scss" scoped>

    @import "../../scss/_variables.scss";
    
    // Tainacan Header
    #title-row {

        .breadcrumbs {
            font-size: 12px;
        }

        .level-left {
            .level-item {
                display: inline-block;
                margin-left: 268px;
            }  
        }

        @media screen and (max-width: 769px) {
            .level-left {
                margin-left: 0px !important;
                .level-item {
                    margin-left: 30px;
                }
            }
            .level-right {
                display: none;
            }

            top: 206px;
            margin-bottom: 0px !important;
        }

    }
</style>


