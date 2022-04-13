<template>
    <div 
            id="tainacan-admin-app" 
            class="has-mounted columns is-fullheight"
            :class="{ 
                'tainacan-admin-mobile-app-mode': $adminOptions.mobileAppMode
            }">
        <template v-if="activeRoute == 'HomePage'">
            <tainacan-header v-if="!$adminOptions.hideTainacanHeader" />
            <router-view /> 
        </template>
        <template v-else>
            <primary-menu
                    v-if="!$adminOptions.hidePrimaryMenu" 
                    :active-route="activeRoute"
                    :is-menu-compressed="isMenuCompressed"/>
            <button 
                    v-if="!$adminOptions.hidePrimaryMenu && !$adminOptions.hidePrimaryMenuCompressButton" 
                    class="is-hidden-mobile"
                    id="menu-compress-button"
                    :style="{ top: menuCompressButtonTop }"
                    @click="isMenuCompressed = !isMenuCompressed"      
                    :aria-label="$i18n.get('label_shrink_menu')">    
                <span
                        v-tooltip="{
                            content: $i18n.get('label_shrink_menu'),
                            autoHide: true,
                            placement: 'auto-end',
                            popperClass: ['tainacan-tooltip', 'tooltip', 'tainacan-repository-tooltip']     
                        }"
                        class="icon">
                    <i 
                            :class="{ 'tainacan-icon-arrowleft' : !isMenuCompressed, 'tainacan-icon-arrowright' : isMenuCompressed }"
                            class="tainacan-icon tainacan-icon-1-25em"/>
                </span>
            </button>
            <tainacan-header v-if="!$adminOptions.hideTainacanHeader" />
            <tainacan-repository-subheader
                    v-if="!$adminOptions.hideRepositorySubheader" 
                    :is-repository-level="isRepositoryLevel"
                    :is-menu-compressed="isMenuCompressed"/>
            <div 
                    id="repository-container"
                    class="column is-main-content"
                    :style="$adminOptions.hidePrimaryMenu ? '--tainacan-sidebar-width: 0px' : ''">  
                <router-view /> 
            </div>
        </template>
    </div>
</template>

<script>
    import PrimaryMenu from './components/navigation/primary-menu.vue';
    import TainacanHeader from './components/navigation/tainacan-header.vue';
    import TainacanRepositorySubheader from './components/navigation/tainacan-repository-subheader.vue';
    import CustomDialog from './components/other/custom-dialog.vue';
    import "floating-vue/dist/style.css";

    export default { 
        name: "AdminPage",
        components: {
            PrimaryMenu,
            TainacanHeader,
            TainacanRepositorySubheader
        },
        data(){
            return {
                isMenuCompressed: false,
                isRepositoryLevel : true,
                activeRoute: '/collections'
            }
        },
        computed: {
            menuCompressButtonTop() {
                let amountOfElementsAbove = [
                    this.$adminOptions.hidePrimaryMenuRepositoryButton,
                    this.$adminOptions.hidePrimaryMenuCollectionsButton,
                    this.$adminOptions.hidePrimaryMenuItemsButton
                ].filter(Boolean).length;

                switch (amountOfElementsAbove) {
                    case 3:
                        return 'calc(2.05em + 12px)';
                    case 2:
                        return 'calc(4.65em + 12px)';
                    case 1:
                        return 'calc(7.5em + 12px)';
                    case 0:
                    default:
                        return 'calc(10.125em + 12px)';
                }
            }
        },
        watch: {
            '$route' (to) {
                this.isMenuCompressed = (to.params.collectionId != undefined);
                this.activeRoute = to.name;
                this.isRepositoryLevel = this.$route.params.collectionId == undefined;
            }
        },
        created() {
            this.$statusHelper.loadStatuses(); 
            this.$userPrefs.init();
            this.isMenuCompressed = (this.$route.params.collectionId != undefined);
            this.activeRoute = this.$route.name;
            this.isRepositoryLevel = this.$route.params.collectionId == undefined;

            if (jQuery && jQuery( document )) {
                jQuery( document ).ajaxError(this.onHeartBitError);
            }
        },
        methods: {
            onHeartBitError(event, jqxhr, settings) {
                if (settings && settings.url == '/wp-admin/admin-ajax.php') {
                    this.$buefy.snackbar.open({
                        message: this.$i18n.get('error_connectivity'),
                        type: 'is-danger',
                        duration: 5000,
                        actionText: this.$i18n.get('label_know_more'),
                        onAction: () => {
                            this.$buefy.modal.open({
                                component: CustomDialog,
                                props: {
                                    title: this.$i18n.get('error_connectivity_label'),
                                    message: this.$i18n.get('error_connectivity_detail'),
                                    hideCancel: true
                                },
                                ariaRole: 'alertdialog',
                                ariaModal: true,
                                customClass: 'tainacan-modal',
                                closeButtonAriaLabel: this.$i18n.get('close')
                            });
                        }
                    });
                }
            }
        }
    }
</script>

<style lang="scss">

    .is-fullheight {
        height: 100%;
        margin-bottom: 0px;
        margin-top: 0px;
    }  

    @media screen and (max-width: 769px) {
        .is-fullheight:not(.tainacan-admin-mobile-app-mode):not(.tainacan-admin-collection-mobile-app-mode) {
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
    #primary-menu.is-compressed~.is-main-content {
        --tainacan-sidebar-width: 3.0em;
    }
    #primary-menu:not(.is-compressed)~.is-main-content {
        --tainacan-sidebar-width: 10em;
    }

    .is-secondary-content {
        padding: 0px !important;
        margin: 5.4em auto 0 auto;
        position: relative;
        overflow-y: hidden;
        overflow-x: hidden;
        height: calc(100vh - 5.4em);

        @media screen and (max-width: 769px) {
            overflow-y: visible;
            margin: 38px auto 0 auto;
            
        } 

        .columns {
            margin-left: 0px;
            margin-right: 0px;
        }
    }

    #menu-compress-button {
        position: absolute;
        z-index: 999;
        top: calc(10.125em + 12px);
        left: 0px;
        max-width: 1.5625em;
        height: 1.5625em;
        width: 1.5625em;
        border: none;
        background-color: var(--tainacan-blue5);
        color: var(--tainacan-white);
        padding: 0px;
        border-top-right-radius: 2px;
        border-bottom-right-radius: 2px;
        cursor: pointer;

        .icon {
            margin-top: -2px;
        }
    }
    .filter-tags-list {
        padding-top: 0;
    }

    @media screen and (min-width: 769px) {
        .filters-menu {
            .modal-background,
            .modal-close {
                display: none;
            }
        }
    }

</style>

