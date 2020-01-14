<template>
    <div class="tainacan-page-title">
    
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

    </div>
</template>

<script>
export default {
    name: 'TainacanTitle',
    data() {
        return {
            isRepositoryLevel: true,
            pageTitle: '',
            activeRouteName: '',
            breadCrumbItem: {}
        }
    },
    props: {
        breadCrumbItems: Array
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


