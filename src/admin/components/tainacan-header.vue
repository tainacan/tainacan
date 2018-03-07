<template>
    <div id="tainacan-header" class="level">
        <div class="level-left">
            <div class="level-item">
                <h1 class="has-text-weight-bold is-uppercase has-text-primary">{{pageTitle}}</h1>
                <nav class="breadcums">
                    <router-link tag="a" :to="$routerHelper.getCollectionsPath()">{{ $i18n.get('repository') }}</router-link> > 
                    <span v-for="(pathItem, index) in arrayPath" :key="index">
                        <router-link 
                            tag="a" 
                            :to="'/' + arrayPath.slice(0, index + 1).join('/')">
                                {{ isNaN(pathItem) ? $i18n.get(pathItem) : pathItem }}
                        </router-link>
                        <span v-if="index != arrayPath.length - 1"> > </span>
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
export default {
    name: 'TainacanHeader',
    data(){
        return {
            wordpressAdmin: window.location.origin + window.location.pathname.replace('admin.php', ''),
            onSecondaryPage: false,
            pageTitle: '',
            arrayPath: [],
        }
    },
    watch: {
        '$route' (to, from) {
            this.onSecondaryPage = (to.params.collectionId != undefined);
            this.arrayPath = to.path.split("/");
            this.arrayPath = this.arrayPath.filter((item) => item.length != 0);
            this.pageTitle = this.$route.meta.title;
        }
    },
    created () {
        this.onSecondaryPage = (this.$route.params.collectionId != undefined);
        this.arrayPath = this.$route.path.split("/");
        this.arrayPath = this.arrayPath.filter((item) => item.length != 0);
        this.pageTitle = this.$route.meta.title;
    }
}
</script>

<style lang="scss" scoped>

    @import "../scss/_variables.scss";
    
    // Tainacan Header
    #tainacan-header{
        background-color: $light;
        height: 78px;
        width: 100%;
        border-bottom: 1px solid #ddd;
        padding: 1.0em;
        vertical-align: middle; 
        left: 0;
        right: 0;
        position: absolute;
        z-index: 9;

        .level-left {
            .level-item {
                display: inline-block;
                margin-left: 222px;
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


