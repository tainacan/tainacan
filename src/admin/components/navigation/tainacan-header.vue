<template>
    <div
            id="tainacan-header"
            class="level is-mobile">
        <div class="level-left">
            <div class="level-item home-area">
                <router-link
                        tag="a"
                        to="/">
                    <span
                            v-tooltip="{
                                content: $i18n.get('label_plugin_home_page'),
                                autoHide: true,
                                placement: 'auto',
                                classes: ['repository-header-tooltips']
                            }"
                            class="icon">
                        <i class="tainacan-icon tainacan-icon-home has-text-blue5"/>
                    </span>
                </router-link>
            </div>
            <div class="level-item logo-area">
                <router-link
                        tag="a"
                        to="/">
                    <img
                            class="tainacan-logo"
                            alt="Tainacan Logo"
                            :src="logoHeader">
                </router-link>
            </div>
        </div>
        <div class="level-right">
            <div class="is-hidden-tablet">
                <button
                        @click="$router.push($routerHelper.getItemsPath())"
                        class="button is-small is-white level-item">
                    <span class="icon">
                        <i class="tainacan-icon tainacan-icon-20px tainacan-icon-search"/>
                    </span>
                </button>
            </div>
            <div class="search-area is-hidden-mobile">
                <div class="control has-icons-right is-small is-clearfix">
                    <input
                            autocomplete="on"
                            :aria-label="$i18n.get('instruction_search_in_repository')"
                            :placeholder="$i18n.get('instruction_search_in_repository')"
                            class="input is-small search-header"
                            type="search"
                            :value="searchQuery"
                            @input="futureSearchQuery = $event.target.value"
                            @keyup.enter="updateSearch()">
                    <span class="icon is-right">
                        <i 
                                @click="updateSearch()"
                                class="tainacan-icon tainacan-icon-search"/>
                    </span>
                </div>
                <b-dropdown
                        ref="advancedSearchShortcut"
                        class="advanced-search-header-dropdown"
                        position="is-bottom-left"
                        aria-role="list">
                    <a
                            class="advanced-search-text"
                            slot="trigger">
                        {{ $i18n.get('advanced_search') }}
                    </a>
                    <b-dropdown-item aria-role="listitem">
                        <div :style="{'height': '25px'}">
                            <p class="is-pulled-left advanced-search-text-di">{{ $i18n.get('advanced_search') }}</p>
                            <span 
                                    style="margin-top: 5px; margin-right: -2px;"
                                    class="icon is-pulled-right">
                                <i class="tainacan-icon tainacan-icon-20px tainacan-icon-arrowup has-text-secondary"/>
                            </span>
                        </div>
                        <hr class="advanced-search-hr">
                    </b-dropdown-item>
                    <b-dropdown-item
                            style="padding-left: 0 !important; padding-right: 0 !important;"
                            :custom="true"
                            aria-role="listitem">
                        <advanced-search
                                :is-repository-level="true"
                                :is-header="true"/>
                    </b-dropdown-item>
                </b-dropdown>

            </div>
            <button
                    @click="showProcesses = !showProcesses"
                    class="button is-small is-white level-item">
                <span
                        v-tooltip="{
                            content: $i18n.get('processes'),
                            autoHide: true,
                            placement: 'auto',
                            classes: ['repository-header-tooltips']
                        }"
                        class="icon">
                    <i class="tainacan-icon tainacan-icon-20px tainacan-icon-processes"/>
                </span>
            </button>
            <processes-popup
                    v-if="showProcesses"
                    @closeProcessesPopup="showProcesses = false"/>
            <a
                    class="level-item"
                    :href="wordpressAdmin">
                <span
                        v-tooltip="{
                            content: $i18n.get('label_wordpress_admin_page'),
                            autoHide: true,
                            placement: 'auto',
                            classes: ['repository-header-tooltips']
                        }"
                        class="icon">
                    <i class="tainacan-icon tainacan-icon-wordpress"/>
                </span>
            </a>
        </div>
    </div>
</template>

<script>
    import AdvancedSearch from '../advanced-search/advanced-search.vue';
    import ProcessesPopup from '../other/processes-popup.vue';

    export default {
        name: 'TainacanHeader',
        data() {
            return {
                logoHeader: tainacan_plugin.base_url + '/admin/images/tainacan_logo_header.svg',
                wordpressAdmin: window.location.origin + window.location.pathname.replace('admin.php', ''),
                searchQuery: '',
                futureSearchQuery: '',
                showProcesses: false,
                hasNewProcess: false
            }
        },
        components: {
            AdvancedSearch,
            ProcessesPopup
        },
        methods: {
            // toItemsPage() {
            //     if(this.$route.path == '/items') {
            //         this.$root.$emit('openAdvancedSearch', true);
            //     }
            //
            //     if(this.$route.path != '/items') {
            //         this.$router.push({
            //             path: '/items',
            //             query: {
            //                 advancedSearch: true
            //             }
            //         });
            //     }
            // },
            updateSearch() {
                if (this.$route.path != '/items') {
                    this.$router.push({
                        path: '/items',
                    });
                }

                this.$eventBusSearch.setSearchQuery(this.futureSearchQuery);
            },
        },
        created(){
            this.$root.$on('closeAdvancedSearchShortcut', () => {
                this.$refs.advancedSearchShortcut.toggle();
            });
        },
        beforeDestroy() {
            this.$root.$off('closeAdvancedSearchShortcut');
        }
    }
</script>

<style lang="scss" scoped>

    @import "../../scss/_variables.scss";

    .repository-header-tooltips .tooltip-inner {
        color: white;
        background-color: $blue3;
        font-size: 0.75rem;
        font-weight: 400;
        padding: 10px 14px;
    }
    .repository-header-tooltips .tooltip-arrow {
        border-color: $blue3;
    }

    // Tainacan Header
    #tainacan-header {
        background-color: white;
        height: $header-height;
        max-height: $header-height;
        width: 100%;
        padding: 12px;
        vertical-align: middle;
        left: 0;
        right: 0;
        position: absolute;
        z-index: 999;
        color: $blue5;

        .level-left {
            margin-left: -12px;
            .home-area {
                font-size: 24px;
                width: 50px;
                height: $header-height;
                background-color: $gray1;
                padding-bottom: 0.4rem;
            }
            .logo-area {
                height: $header-height;
                width: $side-menu-width;
                cursor: pointer;

                &:focus {
                    box-shadow: none;
                }
                .tainacan-logo {
                    height: 24px;
                    padding: 0px;
                    // margin-left: 19px;
                }
            }
        }
        .level-right {
            padding-right: 14px;

            .button, a {
                color: $blue5 !important;
            }
            .button:hover, .button:active, .button:focus {
                background-color: white !important;
            }

            .tainacan-icon-wordpress {
                font-size: 26px;
            }
            
            .search-area {
                display: flex;
                align-items: center;
                margin-right: 28px;
                .control {
                    .search-header {
                        border: 1px solid $gray2;
                        height: 28px;
                        transition: width linear 0.15s;
                        -webkit-transition: width linear 0.15s;
                        width: 220px;
                        font-size: 0.75rem;
                    }
                    .search-header:focus, .search-header:active {
                        width: 372px !important;
                    }
                    .icon:not(.add-i) {
                        pointer-events: all;
                        color: $blue5;
                        cursor: pointer;
                        height: 27px;
                        font-size: 18px;
                        width: 30px !important;
                    }
                }
            }
        }

        @media screen and (max-width: 769px) {
            padding: 0;
            height: 104px;

            .level-left {
                margin-left: 0 !important;
                .level-item {
                    margin-left: 0;
                }
            }
            .level-right {
                margin-top: 0;
            }

            top: 0;
            margin-bottom: 0 !important;
        }

    }
</style>


