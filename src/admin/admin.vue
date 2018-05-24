<template>
    <div 
            id="tainacan-admin-app" 
            class="columns is-fullheight">
        <primary-menu 
                :active-route="activeRoute"
                :is-menu-compressed="isMenuCompressed"/>
        <button 
                id="menu-compress-button"
                @click="isMenuCompressed = !isMenuCompressed">
            <b-icon :icon="isMenuCompressed ? 'menu-right' : 'menu-left'" />
        </button>
        <tainacan-header :is-menu-compressed="isMenuCompressed"/>
        <div class="column is-main-content">  
            <router-view/> 
        </div>
    </div>
</template>

<script>
    import PrimaryMenu from './components/navigation/primary-menu.vue';
    import TainacanHeader from './components/navigation/tainacan-header.vue';

    export default {
        name: "AdminPage",
        data(){
            return {
                isMenuCompressed: false,
                activeRoute: '/collections'
            }
        },
        components: {
            PrimaryMenu,
            TainacanHeader
        },
        created() {
            this.$userPrefs.init();
            this.isMenuCompressed = (this.$route.params.collectionId != undefined);
            this.activeRoute = this.$route.name;
        },
        watch: {
            '$route' (to) {
                this.isMenuCompressed = (to.params.collectionId != undefined);
                this.activeRoute = to.name;
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
        padding: 0px;
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
        padding: 0px;
        margin: $header-height auto 0 auto;
        position: relative;
        overflow-y: hidden;
        height: calc(100% - 53px);

        @media screen and (max-width: 769px) {
            
            overflow-y: visible;
            margin: 0 auto;
            
        } 

        .columns {
            margin-left: 0px;
            margin-right: 0px;
        }
    }

    #menu-compress-button {
        position: absolute;
        z-index: 99;
        top: 70px;
        max-width: 23px;
        height: 21px;
        width: 23px;
        border: none;
        background-color: #c1dae0;
        color: $secondary;
        padding: 0px;
        border-top-right-radius: 2px;
        border-bottom-right-radius: 2px;
        cursor: pointer;

        .icon {
            margin-top: -1px;
        }
    }

</style>

