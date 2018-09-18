<template>
    <div class="home-page page-container">
        <b-loading :active.sync="isLoadingCollections"/>
        <section class="home-section">
            <div class="home-section-header repository-section-header">
                <div class="home-section-icon">
                    <b-icon icon="archive"/>
                </div>
                <h1>{{ $i18n.get('repository') }}</h1>
            </div>
            <nav>
                <ul class="repository-menu-list">
                    <li>
                        <router-link
                                tag="a"
                                to="/collections">
                            <b-icon
                                    size="is-small"
                                    icon="folder-multiple"/>
                            <span class="menu-text">{{ $i18n.getFrom('collections', 'name') }}</span>
                        </router-link>
                    </li>
                    <li>
                        <router-link
                                tag="a"
                                to="/items">
                            <b-icon
                                    size="is-small"
                                    icon="file-multiple"/>
                            <span class="menu-text">{{ $i18n.getFrom('items', 'name') }}</span>
                        </router-link>
                    </li>
                    <li>
                        <router-link
                                tag="a"
                                to="/metadata">
                            <span class="icon">
                                <i class="mdi mdi-format-list-bulleted-type"/>
                            </span>
                            <span class="menu-text">{{ $i18n.getFrom('metadata', 'name') }}</span>
                        </router-link>
                    </li>
                    <li>
                        <router-link
                                tag="a"
                                to="/filters">
                            <span class="icon">
                                <i class="mdi mdi-filter"/>
                            </span>
                            <span class="menu-text">{{ $i18n.getFrom('filters', 'name') }}</span>
                        </router-link>
                    </li>
                    <li>
                        <router-link
                                tag="a"
                                to="/taxonomies">
                            <taxonomies-icon />
                            <span class="menu-text">{{ $i18n.getFrom('taxonomies', 'name') }}</span>
                        </router-link>
                    </li>
                    <li>
                        <router-link
                                tag="a"
                                to="/events">
                            <activities-icon /><span class="menu-text">{{ $i18n.get('events') }}</span>
                        </router-link>
                    </li>
                    <li>
                        <router-link
                                tag="a"
                                to="/importers">
                            <span class="icon">
                                <i class="mdi mdi-24px mdi-import"/>
                            </span>
                            <span class="menu-text menu-text-import">{{ $i18n.get('importers') }}</span>
                        </router-link>
                    </li>
                </ul>
            </nav>
        </section>

        <section class="home-section">
            <div class="home-section-header collections-section-header">
                <div class="home-section-icon">
                    <b-icon icon="folder-multiple"/>
                </div>
                <h1>{{ $i18n.get('collections') }}</h1>
            </div>
            <collections-home-list
                    :is-loading="isLoading"
                    :collections="collections"/> 
        </section>

    </div>   
</template>

<script>
import CollectionsHomeList from '../../components/lists/collections-home-list.vue';
import ActivitiesIcon from '../../components/other/activities-icon.vue';
import TaxonomiesIcon from '../../components/other/taxonomies-icon.vue';
import { mapActions, mapGetters } from 'vuex';

export default {
    name: 'HomePage',
    data(){
        return {
            isLoadingCollections: false,
        }
    },
    components: {
        CollectionsHomeList,
        ActivitiesIcon,
        TaxonomiesIcon
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
        background-color: $gray1;
        height: calc(100% - 52px);

        .home-section {
            .home-section-header {
                width: 100%;
                margin: 1rem 0;
                display: flex;
                align-items: center;
                height: 52px;

                .home-section-icon {
                    background-color: white;
                    padding: 0.75rem;
                    height: 52px;
                    width: 52px;
                    text-align: center;
                }

                h1 {
                    color: white;
                    font-size: 1.375rem;
                    font-weight: bold;
                    padding: 0.75rem 1.375rem;
                }

                &.repository-section-header {
                    background-color: $blue5;
                    .home-section-icon {
                        color: $blue5;
                    }
                }
                &.collections-section-header {
                    background-color: $turquoise5;
                    .home-section-icon {
                        color: $turquoise5;
                    }
                }
            }
        }

        .repository-menu-list {
            display: flex;
            width: 100%;
            justify-content: space-between;

            li {
                padding: 0.75rem;
                display: flex;
                background-color: $blue5;
                height: 90px;
                flex-grow: 1;
                margin: 1rem;

                &:first-of-type { margin-left: 0px; }
                &:last-of-type { margin-right: 0px; }

                a { color: white; }
            }
        }

    }

</style>


