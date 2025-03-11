<template>
    <div 
            id="tainacan-admin-app" 
            class="has-mounted is-fullheight"
            :class="{ 
                'tainacan-admin-mobile-app-mode': $adminOptions.mobileAppMode
            }">
        <template v-if="hasPermalinksStructure">
            <tainacan-header v-if="!$adminOptions.hideTainacanHeader" />
            <tainacan-repository-subheader
                    v-if="!$adminOptions.hideRepositorySubheader" 
                    :is-repository-level="isRepositoryLevel"
                    :is-menu-compressed="isMenuCompressed"
                    :active-route="activeRoute" />
            <div 
                    id="repository-container"
                    class="column is-main-content">  
                <router-view /> 
            </div>
        </template>
    </div>
</template>

<script>
    import { mapGetters } from 'vuex';
    import TainacanHeader from './components/navigation/tainacan-header.vue';
    import TainacanRepositorySubheader from './components/navigation/tainacan-repository-subheader.vue';
    import CustomDialog from './components/other/custom-dialog.vue';

    export default { 
        name: "AdminPage",
        components: {
            TainacanHeader,
            TainacanRepositorySubheader
        },
        emits: [
            'openProcessesPopup'
        ],
        data(){
            return {
                isMenuCompressed: false,
                isRepositoryLevel : true,
                activeRoute: '/collections',
                hasPermalinksStructure: false
            }
        },
        computed: {
            ...mapGetters('collection', {
                'collection': 'getCollection'
            })
        },
        watch: {
            '$route': {
                handler(to, from) {
                    this.isMenuCompressed = (to.params.collectionId != undefined);
                    this.activeRoute = to.name;
                    this.isRepositoryLevel = this.$route.params.collectionId == undefined;

                    if ( to.path !== from.path && this.isRepositoryLevel ) {
                        wp.hooks.doAction('tainacan_navigation_path_updated', { currentRoute: to, adminOptions: this.$adminOptions, collection: this.collection });
                    }
                },
                deep: true
            }
        },
        created() {
            
            wp.hooks.doAction('tainacan_navigation_path_updated', { currentRoute: this.$route, adminOptions: this.$adminOptions, collection: this.collection });

            this.hasPermalinksStructure = tainacan_plugin.has_permalinks_structure;

            if ( this.hasPermalinksStructure ) {
                this.$statusHelper.loadStatuses(); 
                this.$userPrefs.init();
                this.isMenuCompressed = (this.$route.params.collectionId != undefined);
                this.activeRoute = this.$route.name;
                this.isRepositoryLevel = this.$route.params.collectionId == undefined;

                if (jQuery && jQuery( document )) {
                    jQuery( document ).ajaxError(this.onHeartBitError);
                }
            } else {
                this.onPermalinksError();   
            }
        },
        methods: {
            onPermalinksError() {
                this.$buefy.modal.open({
                    component: CustomDialog,
                    props: {
                        title: this.$i18n.get('error_permalinks_label'),
                        message: this.$i18n.getWithVariables('error_permalinks_detail', [ '<a href="' + tainacan_plugin.admin_url + 'options-permalink.php">', '</a>' ]),
                        hideCancel: true,
                        confirmText: this.$i18n.get('label_go_to_permalinks'),
                        onConfirm: () => {
                            window.location.href = tainacan_plugin.admin_url + 'options-permalink.php';
                        }
                    },
                    ariaRole: 'alertdialog',
                    ariaModal: true,
                    customClass: 'tainacan-modal',
                    canCancel: false,
                });
            },
            onHeartBitError(event, jqxhr, settings) {
                if (settings && settings.url == tainacan_plugin.admin_url + 'admin-ajax.php') {
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
                                canCancel: ['escape', 'outside']
                            });
                        }
                    });
                }
            }
        }
    }
</script>

<style lang="scss">
    @import url('floating-vue/dist/style.css');

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
        height: calc(100% -  var(--tainacan-admin-header-height, 3.25em));

        @media screen and (max-width: 769px) {
            overflow-y: visible;
        } 
        .columns {
            margin-left: 0px;
            margin-right: 0px;
        }
    }

    .is-secondary-content {
        max-width: 100%;
        padding: 0;

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
            z-index: inherit;

            .modal-background,
            .modal-close {
                display: none;
            }
        }
    }

</style>

