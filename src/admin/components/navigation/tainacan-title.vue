<template>
    <div 
            class="tainacan-page-title">
        <h1>{{ pageTitle }} <span class="is-italic has-text-weight-semibold">{{ isRepositoryLevel ? '' : entityName }}</span></h1>
        <a 
                @click="$router.go(-1)"
                class="back-link has-text-secondary">
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
            'fetchCollectionNameAndURL'
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
        ...mapActions('taxonomy', [
            'fetchTaxonomyName'
        ]),
        ...mapGetters('taxonomy', [
            'getTaxonomyName'
        ]),
        ...mapActions('event', [
            'fetchEventTitle'
        ]),
        ...mapActions('importer', [
            'fetchAvailableImporters'
        ]),
        generateViewPath() {

            for (let i = 0; i < this.arrayRealPath.length; i++) {
                
                this.arrayViewPath.push('');

                if (!isNaN(this.arrayRealPath[i]) && i > 0) {
                    
                    switch(this.arrayRealPath[i-1]) {
                        case 'collections':
                            this.fetchCollectionNameAndURL(this.arrayRealPath[i])
                                .then(collection => { this.arrayViewPath.splice(i, 1, collection.name); this.entityName = collection.name; })
                                .catch((error) => this.$console.error(error));
                            break;
                        case 'items':
                            this.fetchItemTitle(this.arrayRealPath[i])
                                .then(itemName => { this.arrayViewPath.splice(i, 1, itemName); this.entityName = itemName; })
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
                    
                } else if (this.arrayRealPath[i-1] == 'importers' && i > 0){
                    this.fetchAvailableImporters()
                        .then(importers => { 
                            this.arrayViewPath.splice(i, 1, importers[this.arrayRealPath[i]].name);
                            if (i != this.arrayRealPath.length - 1)
                                this.arrayRealPath.pop();
                        })
                        .catch((error) => this.$console.error(error));
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
    
    .tainacan-page-title {
        margin-bottom: 40px;
        display: flex;
        flex-wrap: wrap;
        align-items: flex-end;
        justify-content: space-between;

        h1, h2 {
            font-size: 20px;
            font-weight: 500;
            color: $gray5;
            display: inline-block;
            width: 80%;
            flex-shrink: 1;
            flex-grow: 1;
        }
        a.back-link{
            font-weight: 500;
            float: right;
            margin-top: 5px;
        }
        hr{
            margin: 3px 0px 4px 0px; 
            height: 1px;
            background-color: $secondary;
            width: 100%;
        }
        .breadcrumbs {
            font-size: 12px;
            width: 100%;
            span {
                text-overflow: ellipsis;
                white-space: nowrap;
                overflow-x: hidden;
                max-width: 75%;
                margin: 0 0.1rem;
                display: inline-block;
                vertical-align: bottom;
            }
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


