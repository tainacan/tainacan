<template>
    <div class="tainacan-page-title">
    
        <h1>{{ pageTitle }} <span class="is-italic has-text-weight-semibold">{{ !isRepositoryLevel && collection && collection.name ? collection.name : '' }}</span></h1>
        <a 
                @click="$router.go(-1)"
                class="back-link has-text-secondary">
            {{ $i18n.get('back') }}
        </a>
        <hr>

        <nav 
                v-if="isRepositoryLevel"
                class="breadcrumbs">
            <router-link 
                    tag="a" 
                    :to="$routerHelper.getCollectionsPath()">{{ $i18n.get('repository') }}</router-link>
            <template v-for="(breadCrumbItem, index) of breadCrumbItems">
                <span :key="index">&nbsp;>&nbsp;</span>
                <router-link    
                        :key="index"
                        v-if="breadCrumbItem.path != ''"
                        tag="a"
                        :to="breadCrumbItem.path">{{ breadCrumbItem.label }}</router-link>
                <span 
                        :key="index"
                        v-else>{{ breadCrumbItem.label }}</span>
            </template>   
        </nav>
        <nav 
                v-else
                class="breadcrumbs">
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
</template>

<script>
import { mapGetters } from 'vuex';

export default {
    name: 'TainacanTitle',
    props: {
        breadCrumbItems: Array
    },
    data() {
        return {
            isRepositoryLevel: true,
            pageTitle: '',
            activeRouteName: '',
            breadCrumbItem: {},
            childrenBreadCrumbItems: []
        }
    },
    computed: {
        collection() {
            return this.getCollection();
        },
         collectionBreadCrumbItem() {
            return { 
                url: this.collection && this.collection.id ? this.$routerHelper.getCollectionPath(this.collection.id) : '',
                name: this.collection && this.collection.name ? this.collection.name : ''
            };
        }
    },
    watch: {
        '$route' (to, from) {
            if (to.path != from.path) {
                this.isRepositoryLevel = (to.params.collectionId == undefined);

                this.activeRoute = to.name;
                this.pageTitle = this.$route.meta.title;
            }
        }
    },
    created() {
        this.isRepositoryLevel = (this.$route.params.collectionId == undefined);

        document.title = this.$route.meta.title;
        this.pageTitle = document.title;

        this.$root.$on('onCollectionBreadCrumbUpdate', this.collectionBreadCrumbUpdate);
    },
    beforeDestroy() {
        this.$root.$on('onCollectionBreadCrumbUpdate', this.collectionBreadCrumbUpdate);
    },
    methods: {
        ...mapGetters('collection', [
            'getCollection'
        ]),
        collectionBreadCrumbUpdate(breadCrumbItems) {
            this.childrenBreadCrumbItems = breadCrumbItems;
        }
    }
}
</script>

<style lang="scss" scoped>

    @import "../../scss/_variables.scss";
    
    .tainacan-page-title {
        margin-bottom: 35px;
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
            a {
                text-overflow: ellipsis;
                white-space: nowrap;
                overflow: hidden;
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


