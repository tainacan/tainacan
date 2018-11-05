<template>
    <div class="tainacan-cards-container">
        <template v-if="collections.length <= 0 && !isLoading">
            <ul class="new-collection-menu">
                <li>
                    <router-link
                            tag="a" 
                            :to="$routerHelper.getNewCollectionPath()"
                            class="first-card">
                        <div class="list-metadata">
                            <b-icon 
                                    size="is-large"
                                    icon="folder-plus" />
                            <div>{{ $i18n.get('label_create_collection') }}</div>
                        </div>
                    </router-link>
                </li>
                <li>
                    <router-link 
                            tag="a" 
                            :to="{ path: $routerHelper.getNewCollectionPath() }" 
                            :aria-label="$i18n.get('label_collection_items')">
                        <span class="icon is-medium">
                            <i class="mdi mdi-36px mdi-file-multiple"/>
                        </span>
                        <span class="menu-text">{{ $i18n.get('items') }}</span>
                    </router-link>
                </li>
                <li>
                    <router-link  
                            tag="a" 
                            :to="{ path: $routerHelper.getNewCollectionPath() }"
                            :aria-label="$i18n.get('label_collection_metadata')">
                        <span class="icon is-medium">
                            <i class="mdi mdi-36px mdi-format-list-bulleted-type"/>
                        </span>
                        <span class="menu-text">{{ $i18n.getFrom('metadata', 'name') }}</span>
                    </router-link>
                </li>
                <li>
                    <router-link 
                            tag="a" 
                            :to="{ path: $routerHelper.getNewCollectionPath() }" 
                            :aria-label="$i18n.get('label_collection_filters')">
                        <span class="icon is-medium">
                            <i class="mdi mdi-36px mdi-filter"/>
                        </span>
                        <span class="menu-text">{{ $i18n.getFrom('filters', 'name') }}</span>
                    </router-link>
                </li>
            </ul>
        </template>
        <template v-else>
            <masonry 
                    :cols="{ default: 6, 1919: 5, 1407: 4, 1215: 3, 1023: 2, 767: 1 }"
                    :gutter="25"
                    style="width=100%;">
                <router-link
                        tag="a" 
                        :to="$routerHelper.getNewCollectionPath()"
                        class="tainacan-card new-card">
                    <div class="list-metadata">
                        <b-icon 
                                size="is-large"
                                icon="folder-plus" />
                        <div>{{ $i18n.get('label_create_collection') }}</div>
                    </div>
                    <ul class="menu-list">
                        <li>
                            <router-link 
                                    tag="a" 
                                    :to="{ path: $routerHelper.getNewCollectionPath() }" 
                                    :aria-label="$i18n.get('label_collection_items')">
                                <b-tooltip 
                                        :label="$i18n.get('items')"
                                        position="is-bottom">
                                    <span class="icon">
                                        <i class="mdi mdi-file-multiple"/>
                                    </span>
                                </b-tooltip>
                                <!-- <span class="menu-text">{{ $i18n.get('items') }}</span> -->
                            </router-link>
                        </li>
                        <li>
                            <router-link  
                                    tag="a" 
                                    :to="{ path: $routerHelper.getNewCollectionPath() }"
                                    :aria-label="$i18n.get('label_collection_metadata')">
                                <b-tooltip 
                                        :label="$i18n.getFrom('metadata', 'name')"
                                        position="is-bottom">
                                    <span class="icon">
                                        <i class="mdi mdi-format-list-bulleted-type"/>
                                    </span>
                                </b-tooltip>
                                <!-- <span class="menu-text">{{ $i18n.getFrom('metadata', 'name') }}</span> -->
                            </router-link>
                        </li>
                        <li>
                            <router-link 
                                    tag="a" 
                                    :to="{ path: $routerHelper.getNewCollectionPath() }" 
                                    :aria-label="$i18n.get('label_collection_filters')">
                                <b-tooltip 
                                        animated
                                        :label="$i18n.getFrom('filters', 'name')"
                                        position="is-bottom">
                                    <span class="icon">
                                        <i class="mdi mdi-filter"/>
                                    </span>
                                </b-tooltip>
                                <!-- <span class="menu-text">{{ $i18n.getFrom('filters', 'name') }}</span> -->
                            </router-link>
                        </li>
                    </ul>
                </router-link>
                <router-link
                        tag="a"
                        :to="$routerHelper.getCollectionPath(collection.id)"
                        v-if="collections.length > 0 && !isLoading" 
                        :key="index"
                        v-for="(collection, index) of collections"
                        class="tainacan-card">                                 
                    <img 
                            v-if="collection.thumbnail != undefined"
                            :src="collection['thumbnail'].tainacan_medium ? collection['thumbnail'].tainacan_medium : (collection['thumbnail'].medium ? collection['thumbnail'].medium : thumbPlaceholderPath)">  
                    <ul class="menu-list">
                        <li>
                            <a 
                                    :href="collection.url" 
                                    :aria-label="$i18n.get('label_view_collection')">
                                <b-tooltip 
                                        :label="$i18n.get('label_view_collection')"
                                        position="is-bottom">
                                    <span class="icon">
                                        <i class="mdi mdi-eye"/>
                                    </span>
                                </b-tooltip>
                            </a>
                        </li>
                        <li>
                            <router-link 
                                    tag="a" 
                                    :to="{ path: $routerHelper.getCollectionItemsPath(collection.id, '') }" 
                                    :aria-label="$i18n.get('label_collection_items')">
                                <b-tooltip 
                                        :label="$i18n.get('items')"
                                        position="is-bottom">
                                    <span class="icon">
                                        <i class="mdi mdi-file-multiple"/>
                                    </span>
                                </b-tooltip>
                                <!-- <span class="menu-text">{{ $i18n.get('items') }}</span> -->
                            </router-link>
                        </li>
                        <li>
                            <router-link
                                    tag="a" 
                                    :to="{ path: $routerHelper.getCollectionEditPath(collection.id) }" 
                                    :aria-label="$i18n.get('label_settings')">
                                <b-tooltip 
                                        :label="$i18n.get('label_settings')"
                                        position="is-bottom">
                                    <span class="icon">
                                        <i class="mdi mdi-settings"/>
                                    </span>
                                </b-tooltip>
                                <!-- <span class="menu-text">{{ $i18n.get('label_settings') }}</span> -->
                            </router-link>
                        </li>
                        <li>
                            <router-link  
                                    tag="a" 
                                    :to="{ path: $routerHelper.getCollectionMetadataPath(collection.id) }"
                                    :aria-label="$i18n.get('label_collection_metadata')">
                                <b-tooltip 
                                        :label="$i18n.getFrom('metadata', 'name')"
                                        position="is-bottom">
                                    <span class="icon">
                                        <i class="mdi mdi-format-list-bulleted-type"/>
                                    </span>
                                </b-tooltip>
                                <!-- <span class="menu-text">{{ $i18n.getFrom('metadata', 'name') }}</span> -->
                            </router-link>
                        </li>
                        <li>
                            <router-link 
                                    tag="a" 
                                    :to="{ path: $routerHelper.getCollectionFiltersPath(collection.id) }" 
                                    :aria-label="$i18n.get('label_collection_filters')">
                                <b-tooltip 
                                        animated
                                        :label="$i18n.getFrom('filters', 'name')"
                                        position="is-bottom">
                                    <span class="icon">
                                        <i class="mdi mdi-filter"/>
                                    </span>
                                </b-tooltip>
                                <!-- <span class="menu-text">{{ $i18n.getFrom('filters', 'name') }}</span> -->
                            </router-link>
                        </li>
                        <li>
                            <router-link 
                                    tag="a" 
                                    :to="{ path: $routerHelper.getCollectionEventsPath(collection.id) }" 
                                    :aria-label="$i18n.get('label_collection_events')">
                                <b-tooltip 
                                        :label="$i18n.get('events')"
                                        position="is-bottom">
                                    <activities-icon />
                                </b-tooltip>
                                <!-- <span class="menu-text">{{ $i18n.get('events') }}</span> -->
                            </router-link> 
                        </li>
                    </ul>

                    <!-- Name -->
                    <div class="metadata-title">
                        <p 
                                v-tooltip="{
                                    content: collection.name != undefined ? collection.name : '',
                                    html: true,
                                    autoHide: false,
                                    placement: 'auto-start'
                                }">
                            {{ collection.name != undefined ? collection.name : '' }}
                        </p>                            
                    </div>
                </router-link>
            </masonry>
        </template>
    </div>
</template>

<script>
import ActivitiesIcon from '../other/activities-icon.vue';

export default {
    name: 'CollectionsHomeList',
    data(){
        return {
            thumbPlaceholderPath: tainacan_plugin.base_url + '/admin/images/placeholder_square.png'
        }
    },
    components: {
        ActivitiesIcon
    },
    props: {
        isLoading: false,
        collections: Array,
    }
}
</script>

<style lang="scss" scoped>

    @import "../../scss/_variables.scss";
    @import "../../scss/_collection-home-cards.scss";

    .new-collection-menu {
        display: flex;
        width: calc(100% + 1.25rem);
        justify-content: space-between;
        flex-wrap: wrap;
        margin: 0 -0.75rem;

        li {
            padding: 0.75rem;
            display: flex;
            background-color: $gray1;
            flex-grow: 1;
            margin: 0.75rem;
            height: 120px;
            min-width: 140px; 
            text-align: center;

            &:first-of-type {
                width: 56.7%;
                max-width: 56.7%;
            }

            a { 
                width: 100%;
                color: $turquoise5; 
                display: flex;
                flex-wrap: wrap;
                flex-direction: column;
                align-items: center;
                justify-content: space-evenly;

                svg.taxonomies-icon,
                svg.activities-icon {
                    fill: $turquoise5;
                    height: 34px;
                    width: 34px;
                }
            }
        }
    }
    
</style>


