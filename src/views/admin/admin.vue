<template>
    <div 
            class="has-mounted is-fullheight is-main-content"
            :class="{ 
                'tainacan-admin-mobile-app-mode': $adminOptions.mobileAppMode
            }">
        <router-view 
                v-if="hasPermalinksStructure"
                :key="$route.query.authorid" /> 
    </div>
</template>

<script>
    import { mapGetters } from 'vuex';
    import CustomDialog from './components/other/custom-dialog.vue';

    export default { 
        name: "AdminPage",
        data(){
            return {
                
                isRepositoryLevel : true,
                hasPermalinksStructure: false,
                pageScrollIntersectionObserver: null
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
                    this.isRepositoryLevel = this.$route.params.collectionId == undefined;

                    if ( to.path !== from.path && this.isRepositoryLevel ) {
                        wp.hooks.doAction('tainacan_navigation_path_updated', { currentRoute: to, adminOptions: this.$adminOptions });
                    }
                },
                deep: true
            }
        },
        created() {
            
            wp.hooks.doAction('tainacan_navigation_path_updated', { currentRoute: this.$route, adminOptions: this.$adminOptions });

            this.hasPermalinksStructure = tainacan_plugin.has_permalinks_structure;

            if ( this.hasPermalinksStructure ) {
                this.$statusHelper.loadStatuses(); 
                this.$userPrefs.init();
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

    .is-main-content {
        overflow: hidden;
        overflow: clip;
        margin: 0 auto;
        position: relative;
        height: 100%;

        .columns {
            margin-left: 0px;
            margin-right: 0px;
        }
    }

    .is-secondary-content {
        max-width: 100%;

        .columns {
            margin: 0;
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

