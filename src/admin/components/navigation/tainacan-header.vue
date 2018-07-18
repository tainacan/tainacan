<template>
    <div
            id="tainacan-header"
            class="level"
            :class="{'menu-compressed': isMenuCompressed}">
        <div class="level-left">
            <div class="level-item">
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
            <div class="search-area">
                <div class="control has-icons-right is-small is-clearfix">
                    <input
                            autocomplete="on"
                            :placeholder="$i18n.get('instruction_search_in_repository')"
                            class="input is-small search-header"
                            type="search"
                            :value="searchQuery"
                            @input="futureSearchQuery = $event.target.value"
                            @keyup.enter="updateSearch()">
                    <span class="icon is-right">
                        <i
                                @click="updateSearch()"
                                class="mdi mdi-magnify"/>
                    </span>
                </div>
                <b-dropdown
                        ref="advancedSearchShortcut"
                        class="advanced-search-header-dropdown"
                        position="is-bottom-left">
                    <a
                            class="advanced-search-text"
                            slot="trigger">
                        {{ $i18n.get('advanced_search') }}
                    </a>
                    <b-dropdown-item>
                        <div :style="{'height': '25px'}">
                            <p class="is-pulled-left advanced-search-text-di">{{ $i18n.get('advanced_search') }}</p>
                            <b-icon
                                    style="margin-top: 2px"
                                    type="is-secondary"
                                    icon="menu-up"
                                    class="is-pulled-right" />
                        </div>
                        <hr class="advanced-search-hr">
                    </b-dropdown-item>
                    <b-dropdown-item
                            style="padding-left: 0 !important; padding-right: 0 !important;"
                            :custom="true">
                        <advanced-search
                                :metadata="metadata"
                                :is-header="true"/>
                    </b-dropdown-item>
                </b-dropdown>

            </div>
            <button
                    @click="showProcesses = !showProcesses"
                    class="button is-small is-secondary level-item">
                <b-icon icon="swap-vertical"/>
            </button>
            <processes-popup
                    v-if="showProcesses"
                    @closeProcessesPopup="showProcesses = false"/>
            <a
                    :style="{color: 'white'}"
                    class="level-item"
                    :href="wordpressAdmin">
                <b-icon icon="wordpress"/>
            </a>
        </div>
    </div>
</template>

<script>
    import AdvancedSearch from '../advanced-search/advanced-search.vue';
    import ProcessesPopup from '../other/processes-popup.vue';
    import { mapActions } from 'vuex';

    export default {
        name: 'TainacanHeader',
        data() {
            return {
                logoHeader: tainacan_plugin.base_url + '/admin/images/tainacan_logo_header.png',
                wordpressAdmin: window.location.origin + window.location.pathname.replace('admin.php', ''),
                searchQuery: '',
                futureSearchQuery: '',
                metadata: Array,
                showProcesses: false,
                hasNewProcess: false
            }
        },
        components: {
            AdvancedSearch,
            ProcessesPopup
        },
        methods: {
            ...mapActions('metadata', [
                'fetchMetadata'
            ]),
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
        props: {
            isMenuCompressed: false
        },
        created(){

            this.$root.$on('closeAdvancedSearchShortcut', () => {
                this.$refs.advancedSearchShortcut.toggle();
            });

            this.fetchMetadata({
                collectionId: false,
                isRepositoryLevel: true,
                isContextEdit: false,
                includeDisabled: false,
            })
                .then((metadata) => {
                      this.metadata = metadata;
                });
        },
    }
</script>

<style lang="scss">

    @import "../../scss/_variables.scss";

    // Tainacan Header
    #tainacan-header {
        background-color: $secondary;
        height: $header-height;
        max-height: $header-height;
        width: 100%;
        padding: 12px;
        vertical-align: middle;
        left: 0;
        right: 0;
        position: absolute;
        z-index: 999;
        color: white;

        .level-left {
            margin-left: -12px;

            .level-item {
                height: $header-height;
                width: 180px;
                transition: width 0.15s, background-color 0.2s;
                -webkit-transition: width 0.15s background-color 0.2s;
                cursor: pointer;
                background-color: #257787;

                &:focus {
                    box-shadow: none;
                }
                .tainacan-logo {
                    max-height: 22px;
                    padding: 0 24px;
                    transition: padding 0.15s;
                    -webkit-transition: padding linear 0.15s;
                }
            }
        }
        .level-right {
            padding-right: 12px;
            .search-area {
                display: flex;
                align-items: center;
                margin-right: 36px;

                .control {
                    .search-header {
                        border-width: 0 !important;
                        height: 27px;
                        font-size: 11px;
                        color: $gray-light;
                        transition: width linear 0.15s;
                        -webkit-transition: width linear 0.15s;
                        width: 160px;
                    }
                    .search-header:focus, .search-header:active {
                        width: 220px !important;
                    }
                    .icon:not(.add-i) {
                        pointer-events: all;
                        color: $tertiary;
                        cursor: pointer;
                        height: 27px;
                        font-size: 18px;
                        width: 30px !important;
                    }
                }

                .advanced-search-header-dropdown {
                    height: 27px !important;

                    .dropdown-content {
                        width: 800px !important;
                    }

                    .dropdown-item:hover {
                        background-color: unset;
                    }

                    .dropdown-menu {
                        top: 0 !important;
                    }

                    .dropdown-item {
                        span.icon:not(.is-right) {
                            position: relative !important;
                        }
                    }

                    .advanced-search-text {
                        margin: 0 12px;
                        font-size: 12px;
                        color: white;
                    }

                    .advanced-search-text-di {
                        font-size: 14px;
                        font-weight: 500;
                        color: #01295c;
                        margin-top: 4px;
                    }

                    .advanced-search-hr {
                        height: 1px;
                        margin: 8px 0;
                        background-color: #298596;
                    }
                }
            }
        }
        &.menu-compressed {
            .level-left .level-item {
                width: 220px;
                background-color: $secondary;
                .tainacan-logo {
                    padding: 0 42px;
                }
            }

        }

        @media screen and (max-width: 769px) {
            padding: 0;
            display: flex;
            .level-left {
                display: inline-block;
                margin-left: 0 !important;
                .level-item {
                    margin-left: 0;
                }
            }
            .level-right {
                margin-top: 0;
                display: inline-block;
            }

            top: 0;
            margin-bottom: 0 !important;
        }

    }
</style>


