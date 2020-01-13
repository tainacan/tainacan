<template>
    <div 
            id="tainacan-admin-app" 
            class="columns is-fullheight"
            :class="{ 
                'tainacan-admin-iframe-mode': $route.query.iframemode, 
                'tainacan-admin-read-mode': $route.query.readmode
            }">
        <template v-if="activeRoute == 'HomePage'">
            <tainacan-header />
            <router-view /> 
        </template>
        <template v-else>
            <primary-menu 
                    :active-route="activeRoute"
                    :is-menu-compressed="isMenuCompressed"/>
            <button 
                    class="is-hidden-mobile"
                    id="menu-compress-button"
                    @click="isMenuCompressed = !isMenuCompressed">          
                <span
                        v-tooltip="{
                            content: $i18n.get('label_shrink_menu'),
                            autoHide: true,
                            placement: 'auto-end',
                            classes: ['tooltip', 'repository-tooltip']     
                        }"
                        class="icon">
                    <i 
                            :class="{ 'tainacan-icon-arrowleft' : !isMenuCompressed, 'tainacan-icon-arrowright' : isMenuCompressed }"
                            class="tainacan-icon tainacan-icon-20px"/>
                </span>
            </button>
            <tainacan-header />
            <tainacan-repository-subheader 
                    :is-repository-level="isRepositoryLevel"
                    :is-menu-compressed="isMenuCompressed"/>
            <div 
                    id="repository-container"
                    class="column is-main-content">  
                <router-view /> 
            </div>
        </template>
    </div>
</template>

<script>
    import PrimaryMenu from './components/navigation/primary-menu.vue';
    import TainacanHeader from './components/navigation/tainacan-header.vue';
    import TainacanRepositorySubheader from './components/navigation/tainacan-repository-subheader.vue';

    export default { 
        name: "AdminPage",
        data(){
            return {
                isMenuCompressed: false,
                isRepositoryLevel : true,
                activeRoute: '/collections'
            }
        },
        components: {
            PrimaryMenu,
            TainacanHeader,
            TainacanRepositorySubheader
        },
        created() {
            this.$statusHelper.loadStatuses(); 
            this.$userPrefs.init();
            this.isMenuCompressed = (this.$route.params.collectionId != undefined);
            this.activeRoute = this.$route.name;
            this.isRepositoryLevel = this.$route.params.collectionId == undefined;
        },
        watch: {
            '$route' (to) {
                this.isMenuCompressed = (to.params.collectionId != undefined);
                this.activeRoute = to.name;
                this.isRepositoryLevel = this.$route.params.collectionId == undefined;
            }
        }
    }
</script>

<style lang="scss">

    @import "./scss/_variables.scss";

    .is-fullheight {
        height: 100%;
        margin-bottom: 0px;
        margin-top: 0px;

        @media screen and (max-width: 769px) {
            height: auto;
        }
    }  

    .is-main-content {
        padding: 0px !important;
        margin: 0 auto;
        position: relative;
        overflow-y: auto;
        height: 100%;

        @media screen and (max-width: 769px) {
            overflow-y: visible;
        } 
        .columns {
            margin-left: 0px;
            margin-right: 0px;
        }
    }

    .is-secondary-content {
        padding: 0px !important;
        margin: 94px auto 0 auto;
        position: relative;
        overflow-y: hidden;
        overflow-x: hidden;
        height: calc(100% - 94px);

        @media screen and (max-width: 769px) {
            overflow-y: visible;
            margin: 40px auto 0 auto;
            
        } 

        .columns {
            margin-left: 0px;
            margin-right: 0px;
        }
    }

    #menu-compress-button {
        position: absolute;
        z-index: 99;
        top: 192px;
        left: 0px;
        max-width: 25px;
        height: 25px;
        width: 25px;
        border: none;
        background-color: $blue5;
        color: white;
        padding: 0px;
        border-top-right-radius: 2px;
        border-bottom-right-radius: 2px;
        cursor: pointer;

        .icon {
            margin-top: -2px;
        }
    }

</style>

