<template>
    <div class="home-page page-container">
        <b-loading :active.sync="isLoadingCollections"/>
        <section class="home-section home-section-repository">
            <div 
                    class="section-connector" 
                    aria-hidden/>
            <div class="home-section-header repository-section-header">
                <div class="home-section-icon">
                    <span class="icon">
                        <i class="tainacan-icon tainacan-icon-repository"/>
                    </span>
                </div>
                
                <h1>{{ $i18n.get('repository') + ' ' }}<span class="has-text-weight-semibold">{{ repositoryName }}</span></h1>
                <a
                        target="_blank"
                        :href="themeItemsListURL">
                    <span class="icon">
                        <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-see"/>
                    </span>
                    <span class="menu-text">{{ $i18n.get('label_view_items_on_theme') }}</span>
                </a>
            </div>
            <nav>
                <ul class="repository-menu-list">
                    <!-- <li>
                        <router-link
                                tag="a"
                                to="/collections">
                            <span class="icon is-medium">
                                <i class="tainacan-icon tainacan-icon-36px tainacan-icon-collections"/>
                            </span>
                            <span class="menu-text">{{ $i18n.getFrom('collections', 'name') }}</span>
                        </router-link>
                    </li> -->
                    <!-- <li>
                        <router-link
                                tag="a"
                                to="/items">
                            <span class="icon is-medium">
                                <i class="tainacan-icon tainacan-icon-36px tainacan-icon-items"/>
                            </span>
                            <span class="menu-text">{{ $i18n.get('label_all_published_items') }}</span>
                        </router-link>
                    </li> -->
                    <li v-if="$userCaps.hasCapability('tnc_rep_edit_metadata')">
                        <router-link
                                tag="a"
                                to="/metadata">
                            <span class="icon is-medium">
                                <i class="tainacan-icon tainacan-icon-36px tainacan-icon-metadata"/>
                            </span>
                            <span class="menu-text">{{ $i18n.get('title_repository_metadata_page' ) }}</span>
                        </router-link>
                    </li>
                    <li v-if="$userCaps.hasCapability('tnc_rep_edit_filters')">
                        <router-link
                                tag="a"
                                to="/filters">
                            <span class="icon is-medium">
                                <i class="tainacan-icon tainacan-icon-36px tainacan-icon-filters"/>
                            </span>
                            <span class="menu-text">{{ $i18n.get('title_repository_filters_page') }}</span>
                        </router-link>
                    </li>
                    <li>
                        <router-link
                                tag="a"
                                to="/taxonomies">
                            <span class="icon is-medium">
                                <i class="tainacan-icon tainacan-icon-36px tainacan-icon-taxonomies"/>
                            </span>
                            <span class="menu-text">{{ $i18n.getFrom('taxonomies', 'name') }}</span>
                        </router-link>
                    </li>
                    <li>
                        <router-link
                                tag="a"
                                to="/activities">
                            <span class="icon is-medium">
                                <i class="tainacan-icon tainacan-icon-36px tainacan-icon-activities"/>
                            </span>
                            <span class="menu-text">{{ $i18n.get('title_repository_activities_page') }}</span>
                        </router-link>
                    </li>
                    <li>
                        <router-link
                                tag="a"
                                to="/importers">
                            <span class="icon is-medium">
                                <i class="tainacan-icon tainacan-icon-36px tainacan-icon-importers"/>
                            </span>
                            <span class="menu-text menu-text-import">{{ $i18n.get('importers') }}</span>
                        </router-link>
                    </li>
                    <li>
                        <router-link
                                tag="a"
                                to="/exporters">
                            <span class="icon is-medium">
                                <i class="tainacan-icon tainacan-icon-36px tainacan-icon-export"/>
                            </span>
                            <span class="menu-text">{{ $i18n.get('exporters') }}</span>
                        </router-link>
                    </li>
                </ul>
            </nav>
        </section>

        <section class="home-section home-section-collection">
            <div 
                    class="collection-section-connector" 
                    aria-hidden/>
            <div class="home-section-header collections-section-header">
                <div class="home-section-icon">
                    <router-link
                        tag="span"
                        class="icon"
                        to="/collections">
                        <i class="tainacan-icon tainacan-icon-collections"/>
                    </router-link>
                </div>
                <h1>{{ $i18n.get('label_recent_collections') }}</h1>
                <a
                        target="_blank"
                        :href="themeCollectionListURL"
                        style="position: relative">
                    <span class="icon">
                        <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-see"/>
                    </span>
                    <span class="menu-text">{{ $i18n.get('label_view_collections_on_theme') }}</span>
                </a>
            </div>
            <collections-home-list
                    :is-loading="isLoadingCollections"
                    :collections="collections"
                    :collections-total="collectionsTotal"/> 
            <router-link
                    class="collections-see-more"
                    tag="a"
                    to="/collections">
                <span class="icon">
                    <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-viewtable"/>
                </span>
                <span
                        v-if="collectionsTotal != undefined && collectionsTotal > 1"
                        class="menu-text">
                    {{ $i18n.getWithVariables('label_view_all_%s_collections', [collectionsTotal]) }}
                </span>
                <span
                        v-if="collectionsTotal != undefined && collectionsTotal <= 1"
                        class="menu-text">
                    {{ $i18n.get('label_view_collections_list') }}
                </span>
            </router-link>
        </section>

    </div>   
</template>

<script>
import CollectionsHomeList from '../components/lists/collections-home-list.vue';
import { mapActions, mapGetters } from 'vuex';

export default {
    name: 'HomePage',
    components: {
        CollectionsHomeList
    },
    data(){
        return {
            isLoadingCollections: false,
            repositoryName: tainacan_plugin.repository_name,
            themeCollectionListURL: tainacan_plugin.theme_collection_list_url,
            themeItemsListURL: tainacan_plugin.theme_items_list_url,
        }
    },
    computed: {
        collections() {
            return this.getCollections(); 
        }
    },
    mounted(){
        this.loadCollections();
    },
    methods: {
         ...mapActions('collection', [
            'fetchCollections',
            'cleanCollections'
        ]),
        ...mapGetters('collection', [
            'getCollections'
        ]),
        loadCollections() {
            this.cleanCollections();    
            this.isLoadingCollections = true;
            this.fetchCollections({ page: 1, collectionsPerPage: 9, status: undefined, contextEdit: true })
                .then((res) => {
                    this.collectionsTotal = res.total;
                    this.isLoadingCollections = false;
                }) 
                .catch(() => {
                    this.isLoadingCollections = false;
                });
        }
    }
}
</script>

<style lang="scss" scoped>

    @import '../../scss/_variables.scss';

    .home-page {
        margin-top: $header-height;
        background-color: var(--tainacan-white);
        height: calc(100% - 52px);
        padding: 25px 8.333333333% !important;
        width: 100vw;

        .home-section {
            
            &.home-section-repository{
                position: relative;
                &>nav {
                    padding-left: 52px;
                }
            }
            &.home-section-collection {
                position: relative;
                margin-left: 52px;
            }

            .section-connector {
                border-left: 1px solid var(--tainacan-gray2);
                border-bottom: 1px solid var(--tainacan-gray2);
                position: absolute;
                width: 42px;
                height: 100%;
                top: 43px;
                left: 26px;
            }
            .collection-section-connector {
                border-left: 1px solid var(--tainacan-gray2);
                border-bottom: 1px solid var(--tainacan-gray2);
                position: absolute;
                width: calc(100% + 26px);
                height: 100%;
                top: 0px;
                left: -26px;
            }

            .home-section-header {
                width: 100%;
                margin-top: 1em;
                margin-bottom: 0.5em;
                display: flex;
                align-items: center;
                height: 52px;

                .home-section-icon {
                    background-color: var(--tainacan-white);
                    padding: 0.75em;
                    height: 52px;
                    width: 52px;
                    font-size: 1.875em;
                    text-align: center;
                    z-index: 9;
                    display: flex;
                    align-items: center;
                    justify-content: center;

                    .icon:hover {
                        cursor: pointer;
                    }
                }

                h1 {
                    color: var(--tainacan-heading-color);
                    font-size: 1.375em;
                    font-weight: normal;
                    padding: 0.75em 1.375em;
                    margin-right: auto;
                    overflow: hidden;
                    text-overflow: ellipsis;
                    white-space: nowrap;
                }

                a {
                    margin-right: 2em;
                    display: inline-flex;
                    text-overflow: ellipsis;
                    white-space: nowrap;

                    .menu-text { margin-left: 0.5em;}
                }

                &.repository-section-header {
                    border-bottom: 1px solid var(--tainacan-blue5);
                    a {
                        color: var(--tainacan-blue5);
                    }
                    .home-section-icon {
                        background-color: var(--tainacan-blue5);
                        color: var(--tainacan-white);
                    }
                }
                &.collections-section-header {
                    border-bottom: 1px solid var(--tainacan-turquoise5);
                    a {
                        color: var(--tainacan-turquoise5);
                    }
                    .home-section-icon {
                        background-color: var(--tainacan-turquoise5);
                        color: var(--tainacan-white);
                    }
                }
            }
            a.collections-see-more {
                padding: 10px 16px;
                display: inline-flex;
                text-overflow: ellipsis;
                white-space: nowrap;
                background: var(--tainacan-gray1);
                color: var(--tainacan-turquoise5);
                float: right;
                width: calc(100% - 20px);
                transition: background-color 0.3s ease;

                @media screen and (min-width: 768px) {
                    width: calc(50% - 20px);
                }
                @media screen and (min-width: 1215px) {
                    width: calc(33.3333% - 20px);
                }
                @media screen and (min-width: 1408px) {
                    width: calc(25% - 20px);
                }
                @media screen and (min-width: 1920px) {
                    width: calc(20% - 20px);
                }

                .menu-text { margin-left: 0.5em;}
                &:hover {
                    background-color: var(--tainacan-gray2);
                }
            }
        }

        .repository-menu-list {
            display: flex;
            width: calc(100% + 1.25em);
            justify-content: space-between;
            flex-wrap: wrap;
            margin: 0 -0.75em;

            li {
                padding: 1em;
                display: flex;
                background-color: var(--tainacan-gray1);
                flex-grow: 1;
                margin: 0.75em;
                height: 120px; 
                min-width: 13%;
                flex-basis: 13%;
                max-width: 15%;
                transition: background-color 0.3s ease;

                @media screen and (max-width: 580px) {
                    max-width: calc(100% - 52px);
                    min-width: calc(100% - 52px);
                }
                @media screen and (min-width: 581px) and (max-width: 767px) {
                    min-width: calc(50% - 26px);
                    max-width: calc(50% - 26px);
                }
                @media screen and (min-width: 768px) and (max-width: 1023px) {
                    min-width: calc(33.33% - 26px);
                    max-width: calc(33.33% - 26px);
                }
                @media screen and (min-width: 1024px) and (max-width: 1280px) {
                    min-width: 30%;
                    max-width: 30%;
                }

                &:hover {
                    background-color: var(--tainacan-gray2);
                }

                a { 
                    width: 100%;
                    color: var(--tainacan-blue5); 
                    display: flex;
                    flex-wrap: wrap;
                    flex-direction: column;
                    align-items: center;
                    justify-content: space-evenly;
                }
                .menu-text {
                    text-align: center; 
                }
            }
        }

    }

</style>


