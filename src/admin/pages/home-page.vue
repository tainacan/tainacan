<template>
    <div class="home-page page-container">
        <b-loading :active.sync="isLoadingCollections"/>
        <section class="home-section home-section-repository">
            <div 
                    class="section-connector" 
                    aria-hidden/>
            <div class="home-section-header repository-section-header">
                <div class="home-section-icon">
                    <b-icon icon="archive"/>
                </div>
                <h1>{{ $i18n.get('repository') + ' ' }}<span class="has-text-weight-semibold">{{ repositoryName }}</span></h1>
                <a
                        target="_blank"
                        :href="baseURL + '/collections'">
                    <b-icon
                            icon="eye"/>
                    <span class="menu-text">{{ $i18n.get('label_view_on_theme') }}</span>
                </a>
            </div>
            <nav>
                <ul class="repository-menu-list">
                    <li>
                        <router-link
                                tag="a"
                                to="/collections">
                            <b-icon
                                    size="is-medium"
                                    icon="folder-multiple"/>
                            <span class="menu-text">{{ $i18n.getFrom('collections', 'name') }}</span>
                        </router-link>
                    </li>
                    <li>
                        <router-link
                                tag="a"
                                to="/items">
                            <b-icon
                                    size="is-medium"
                                    icon="file-multiple"/>
                            <span class="menu-text">{{ $i18n.getFrom('items', 'name') }}</span>
                        </router-link>
                    </li>
                    <li>
                        <router-link
                                tag="a"
                                to="/metadata">
                            <span class="icon is-medium">
                                <i class="mdi mdi-36px mdi-format-list-bulleted-type"/>
                            </span>
                            <span class="menu-text">{{ $i18n.getFrom('metadata', 'name') }}</span>
                        </router-link>
                    </li>
                    <li>
                        <router-link
                                tag="a"
                                to="/filters">
                            <span class="icon is-medium">
                                <i class="mdi mdi-36px mdi-filter"/>
                            </span>
                            <span class="menu-text">{{ $i18n.getFrom('filters', 'name') }}</span>
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
                                to="/events">
                            <span class="icon is-medium">
                                <i class="tainacan-icon tainacan-icon-36px tainacan-icon-activities"/>
                            </span>
                            <span class="menu-text">{{ $i18n.get('events') }}</span>
                        </router-link>
                    </li>
                    <li>
                        <router-link
                                tag="a"
                                to="/importers">
                            <span class="icon is-medium">
                                <i class="mdi mdi-36px mdi-import"/>
                            </span>
                            <span class="menu-text menu-text-import">{{ $i18n.get('importers') }}</span>
                        </router-link>
                    </li>
                </ul>
            </nav>
        </section>

        <section class="home-section home-section-collection">
            <div class="home-section-header collections-section-header">
                <div class="home-section-icon">
                    <b-icon icon="folder-multiple"/>
                </div>
                <h1>{{ $i18n.get('collections') }}</h1>
                 <router-link
                        tag="a"
                        to="/collections">
                    <b-icon
                            icon="view-list"/>
                    <span class="menu-text">{{ $i18n.get('label_view_all_collections') }}</span>
                </router-link>
            </div>
            <collections-home-list
                    :is-loading="isLoading"
                    :collections="collections"/> 
        </section>

    </div>   
</template>

<script>
import CollectionsHomeList from '../components/lists/collections-home-list.vue';
import { mapActions, mapGetters } from 'vuex';

export default {
    name: 'HomePage',
    data(){
        return {
            isLoadingCollections: false,
            repositoryName: tainacan_plugin.repository_name,
            baseURL: tainacan_plugin.base_url,
        }
    },
    components: {
        CollectionsHomeList
    },
    computed: {
        collections() {
            return this.getCollections(); 
        }
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
            this.fetchCollections({ 'page': 1, 'collectionsPerPage': 5, 'status': 'publish' })
            .then(() => {
                this.isLoadingCollections = false;
            }) 
            .catch(() => {
                this.isLoadingCollections = false;
            });
        }
    },
    mounted(){
        this.loadCollections();
    }
}
</script>

<style lang="scss" scoped>

    @import '../../scss/_variables.scss';

    .home-page {
        margin-top: $header-height;
        background-color: white;
        height: calc(100% - 52px);
        padding: 25px 8.333333333% !important;
        width: 100vw;

        .home-section {
            
            &.home-section-repository{
                position: relative;
                &>nav {
                    padding-left: $page-side-padding;
                }
            }
            &.home-section-collection {
                margin-left: $page-side-padding;
            }

            .section-connector {
                border-left: 1px solid $gray2;
                border-bottom: 1px solid $gray2;
                position: absolute;
                width: 42px;
                height: 100%;
                top: 43px;
                left: 26px;
            }

            .home-section-header {
                width: 100%;
                margin-top: 1rem;
                margin-bottom: 0.5rem;
                display: flex;
                align-items: center;
                height: 52px;

                .home-section-icon {
                    background-color: white;
                    padding: 0.75rem;
                    height: 52px;
                    width: 52px;
                    text-align: center;
                    z-index: 9;
                }

                h1 {
                    color: $gray5;
                    font-size: 1.375rem;
                    font-weight: normal;
                    padding: 0.75rem 1.375rem;
                    margin-right: auto;
                    overflow: hidden;
                    text-overflow: ellipsis;
                    white-space: nowrap;
                }

                a {
                    margin-right: 2rem;
                    display: inline-flex;
                    overflow: hidden;
                    text-overflow: ellipsis;
                    white-space: nowrap;

                    .menu-text { margin-left: 0.5rem;}
                }

                &.repository-section-header {
                    border-bottom: 1px solid $blue5;
                    a {
                        color: $blue5;
                    }
                    .home-section-icon {
                        background-color: $blue5;
                        color: white;
                    }
                }
                &.collections-section-header {
                    border-bottom: 1px solid $turquoise5;
                    a {
                        color: $turquoise5;
                    }
                    .home-section-icon {
                        background-color: $turquoise5;
                        color: white;
                    }
                }
            }
        }

        .repository-menu-list {
            display: flex;
            width: calc(100% + 1.25rem);
            justify-content: space-between;
            flex-wrap: wrap;
            margin: 0 -0.75rem;

            li {
                padding: 0.75rem;
                display: flex;
                background-color: $gray0;
                flex-grow: 1;
                margin: 0.75rem;
                height: 120px;
                min-width: 140px; 

                a { 
                    width: 100%;
                    color: $blue5; 
                    display: flex;
                    flex-wrap: wrap;
                    flex-direction: column;
                    align-items: center;
                    justify-content: space-evenly;
                }
            }
        }

    }

</style>


