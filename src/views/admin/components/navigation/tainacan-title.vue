<template>
    <div class="tainacan-page-title">
        <slot />
        <h1 v-if="!slotPassed">
            {{ pageTitle }} <span class="is-italic has-text-weight-semibold">{{ !isRepositoryLevel && collection && collection.name ? collection.name : '' }}</span>
        </h1>
        <a 
                @click="$router.go(-1)"
                class="back-link has-text-secondary">
            {{ $i18n.get('back') }}
        </a>
        <hr>

        <nav 
                v-if="isRepositoryLevel"
                class="breadcrumbs">
            <router-link :to="$routerHelper.getCollectionsPath()">{{ $i18n.get('repository') }}</router-link>
            <template 
                    v-for="(breadCrumbItem, index) of breadCrumbItems"
                    :key="index">
                <span>&nbsp;>&nbsp;</span>
                <router-link    
                        v-if="breadCrumbItem.path != ''"
                        :to="breadCrumbItem.path">{{ breadCrumbItem.label }}</router-link>
                <span v-else>{{ breadCrumbItem.label }}</span>
            </template>   
        </nav>
        <nav 
                v-else
                class="breadcrumbs">
            <router-link 
                    :to="$routerHelper.getCollectionsPath()">{{ $i18n.get('repository') }}</router-link>
            &nbsp;>&nbsp; 
            <router-link  
                    :to="$routerHelper.getCollectionsPath()">{{ $i18n.get('collections') }}</router-link>
            &nbsp;>&nbsp; 
            <router-link  
                    :to="{ path: collectionBreadCrumbItem.url, query: { fromBreadcrumb: true }}">{{ collectionBreadCrumbItem.name }}</router-link> 
            <template 
                    v-for="(childBreadCrumbItem, index) of childrenBreadCrumbItems"
                    :key="index">
                <span>&nbsp;>&nbsp;</span>
                <router-link    
                        v-if="childBreadCrumbItem.path != ''"
                        :to="{ path: childBreadCrumbItem.path, query: index === $i18n.get('items') ? { fromBreadcrumb: true } : null }">
                    {{ childBreadCrumbItem.label }}
                </router-link>
                <span v-else>{{ childBreadCrumbItem.label }}</span>
            </template>
        </nav>

    </div>
</template>

<script>
import { mapGetters } from 'vuex';
import { useSlots } from 'vue';

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
            childrenBreadCrumbItems: []
        }
    },
    computed: {
        slotPassed() {
            const slots = useSlots();
            return !!slots['default'];
        },
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
        '$route': {
            handler(to, from) {
                if (to.path != from.path) {
                    this.isRepositoryLevel = (to.params.collectionId == undefined);

                    this.activeRoute = to.name;
                    this.pageTitle = this.$route.meta.title;
                }
            },
            deep: true
        }
    },
    created() {
        this.isRepositoryLevel = (this.$route.params.collectionId == undefined);

        document.title = this.$route.meta.title;
        this.pageTitle = document.title;

        this.$emitter.on('onCollectionBreadCrumbUpdate', this.collectionBreadCrumbUpdate);
    },
    beforeUnmount() {
        this.$emitter.on('onCollectionBreadCrumbUpdate', this.collectionBreadCrumbUpdate);
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

    .tainacan-page-title {
        margin-bottom: 28px;
        display: flex;
        flex-wrap: wrap;
        align-items: flex-end;
        justify-content: space-between;

        :deep(h1),
        :deep(h2) {
            font-size: 1.25em;
            font-weight: 500;
            color: var(--tainacan-heading-color);
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
            background-color: var(--tainacan-secondary);
            width: 100%;
        }
        .breadcrumbs {
            font-size: 0.75em;
            width: 100%;
            a {
                text-overflow: ellipsis;
                white-space: nowrap;
                overflow: hidden;
                max-width: 75%;
                margin: 0 0.1em;
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


