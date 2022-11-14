<template>
    <div
            id="tainacan-header"
            class="level is-mobile">
        <div class="level-left">
            <div 
                    v-if="!$adminOptions.hideTainacanHeaderHomeButton"
                    class="level-item home-area">
                <router-link
                        tag="a"
                        to="/"
                        :aria-label="$i18n.get('label_plugin_home_page')">
                    <span
                            v-tooltip="{
                                content: $i18n.get('label_plugin_home_page'),
                                autoHide: true,
                                placement: 'auto',
                                popperClass: ['tainacan-tooltip', 'tainacan-repository-header-tooltip', 'tooltip']
                            }"
                            class="icon">
                        <i class="tainacan-icon tainacan-icon-home has-text-blue5"/>
                    </span>
                </router-link>
            </div>
            <div class="level-item logo-area">
                <router-link
                        tag="a"
                        to="/"
                        :aria-label="$i18n.get('label_plugin_home_page')">
                    <h1>
                        <img
                                class="tainacan-logo"
                                alt="Tainacan Logo"
                                :src="logoHeader">
                        <span
                                v-if="$adminOptions.tainacanHeaderExtraLabel"
                                v-text="$adminOptions.tainacanHeaderExtraLabel" />
                    </h1>
                </router-link>
            </div>
        </div>
        <div class="level-right">
            <div 
                    v-if="!$adminOptions.hideTainacanHeaderSearchInput"
                    class="is-hidden-tablet">
                <button
                        @click="$router.push($routerHelper.getItemsPath())"
                        class="button is-small is-white level-item"
                        :aria-label="$i18n.get('search')">
                    <span class="icon">
                        <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-search"/>
                    </span>
                </button>
            </div>
            <div class="search-area is-hidden-mobile">
                <b-input
                        v-if="!$adminOptions.hideTainacanHeaderSearchInput"
                        type="search"
                        autocomplete="on"
                        :aria-label="$i18n.get('instruction_search_in_repository')"
                        :placeholder="$i18n.get('instruction_search_in_repository')"
                        class="search-header"
                        size="is-small"
                        :value="searchQuery"
                        @input.native="futureSearchQuery = $event.target.value"
                        @keyup.enter.native="updateSearch()"
                        icon-right="magnify"
                        icon-right-clickable
                        @icon-right-click="updateSearch()" />
                <router-link
                        v-if="!$adminOptions.hideTainacanHeaderAdvancedSearch"
                        class="advanced-search-text"
                        :to="$routerHelper.getItemsPath({ advancedSearch: true })">
                    {{ $i18n.get('advanced_search') }}
                </router-link>

            </div>
            <button
                    v-if="!$adminOptions.hideTainacanHeaderProcessesPopup"
                    @click="showProcesses = !showProcesses"
                    class="button is-small is-white level-item"
                    :aria-label="$i18n.get('processes')">
                <span
                        v-tooltip="{
                            content: $i18n.get('processes'),
                            autoHide: true,
                            placement: 'auto',
                            popperClass: ['tainacan-tooltip', 'tainacan-repository-header-tooltip', 'tooltip']
                        }"
                        class="icon">
                    <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-processes"/>
                </span>
            </button>
            <processes-popup
                    v-if="showProcesses"
                    @closeProcessesPopup="showProcesses = false"/>
            <a
                    class="level-item"
                    :href="wordpressAdmin"
                    :aria-label="$i18n.get('label_wordpress_admin_page')">
                <span
                        v-tooltip="{
                            content: $i18n.get('label_wordpress_admin_page'),
                            autoHide: true,
                            placement: 'auto',
                            popperClass: ['tainacan-tooltip', 'tainacan-repository-header-tooltip', 'tooltip']
                        }"
                        class="icon">
                    <i class="tainacan-icon tainacan-icon-wordpress"/>
                </span>
            </a>
        </div>
    </div>
</template>

<script>
    import ProcessesPopup from '../other/processes-popup.vue';

    export default {
        name: 'TainacanHeader',
        components: {
            ProcessesPopup
        },
        data() {
            return {
                logoHeader: tainacan_plugin.base_url + '/assets/images/tainacan_logo_header.svg',
                wordpressAdmin: window.location.origin + window.location.pathname.replace('admin.php', ''),
                searchQuery: '',
                futureSearchQuery: '',
                showProcesses: false,
                hasNewProcess: false
            }
        },
        created(){
            this.$root.$on('openProcessesPopup', () => {
                this.showProcesses = true;
            });
        },
        beforeDestroy() {
            this.$root.$off('openProcessesPopup');
        },
        methods: {
            updateSearch() {
                if (this.$route.path !== '/items') {
                    this.$router.push({
                        path: '/items',
                    });
                }
                this.$eventBusSearch.setSearchQuery(this.futureSearchQuery);
            }
        }
    }
</script>

<style lang="scss" scoped>

    @import "../../scss/_variables.scss";

    .repository-header-tooltips .tooltip-inner {
        color: var(--tainacan-white);
        background-color: var(--tainacan-blue3);
        font-size: 0.75em;
        font-weight: 400;
        padding: 10px 14px;
    }
    .repository-header-tooltips .tooltip-arrow {
        border-color: var(--tainacan-blue3);
    }

    // Tainacan Header
    #tainacan-header {
        background-color: var(--tainacan-white);
        height: $header-height;
        max-height: $header-height;
        width: 100%;
        padding: 12px;
        margin-bottom: 0px;
        vertical-align: middle;
        left: 0;
        right: 0;
        position: absolute;
        z-index: 9;
        color: var(--tainacan-blue5);

        .level-left {
            margin-left: -12px;
            max-height: 3.25em;
            overflow: hidden;
            
            .home-area { 
                width: $header-height;
                height: $header-height;
                background-color: var(--tainacan-gray1);
                padding-bottom: 0.4em;
                transition: background-color 0.2s ease;

                &:hover {
                    background-color: var(--tainacan-gray2);
                }
                a {
                    font-size: 1.5em;
                }
            }
            .logo-area {
                height: $header-height;
                min-width: 10em;
                cursor: pointer;

                h1 {
                    display: flex;
                    align-items: center;
                    white-space: nowrap;
                    margin: 0px;
                    
                    span {
                        color: var(--tainacan-blue5);
                        text-decoration-color: var(--tainacan-blue5);
                        text-transform: uppercase;
                        overflow: hidden;
                        text-overflow: ellipsis;
                        font-weight: bold;
                        font-size: 1.25rem;
                        margin: 0px 0.5rem 0px 0.75rem;
                    }
                }
                .tainacan-logo {
                    height: 1.5em;
                    padding: 0px;
                    // margin-left: 19px;
                }
            }
        }
        .level-right {
            padding-right: 14px;

            .button, a {
                color: var(--tainacan-blue5) !important;
            }
            .button:hover, .button:active, .button:focus {
                background-color: var(--tainacan-white) !important;
            }

            .tainacan-icon-wordpress {
                font-size: 1.6em;
            }
            .tainacan-icon-processes {
                font-size: 1.45em;
            }
            
            .search-area {
                display: flex;
                align-items: center;
                margin-right: 28px;
                .advanced-search-text {
                    font-size: 0.75em;
                    margin-left: 12px;
                }
                .control {
                    .search-header {
                        border: 1px solid var(--tainacan-gray2);
                        height: 28px;
                        transition: width linear 0.15s;
                        -webkit-transition: width linear 0.15s;
                        width: 220px;
                        font-size: 0.75em;
                    }
                    .search-header:focus, .search-header:active {
                        width: 372px !important;
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


