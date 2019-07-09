<template>
    <div
            style="min-height: initial; position: relative"
            class="tainacan-cards-container">
        <template v-if="collections.length <= 0 && !isLoading">
            <ul class="new-collection-menu">
                <li>
                    <router-link
                            tag="a" 
                            :to="$routerHelper.getNewCollectionPath()"
                            class="first-card">
                        <div class="list-metadata">
                            <span class="icon is-large">
                                <i class="tainacan-icon tainacan-icon-36px tainacan-icon-addcollection"/>
                            </span>
                            <div>{{ $i18n.get('label_create_collection') }}</div>
                        </div>                         
                    </router-link>
                </li>
                <li>
                    <router-link 
                            tag="a" 
                            :to="{ path: $routerHelper.getNewCollectionPath() }" 
                            :aria-label="$i18n.get('label_collection_items')">
                        <span
                                v-tooltip="{
                                    content: $i18n.get('label_collection_items'),
                                    autoHide: true,
                                    placement: 'auto'
                                }"
                                class="icon is-medium">
                            <i class="tainacan-icon tainacan-icon-36px tainacan-icon-items"/>
                        </span>
                        <span class="menu-text">{{ $i18n.get('items') }}</span>
                    </router-link>
                </li>
                <li>
                    <router-link  
                            tag="a" 
                            :to="{ path: $routerHelper.getNewCollectionPath() }"
                            :aria-label="$i18n.get('label_collection_metadata')">
                        <span
                                v-tooltip="{
                                    content: $i18n.get('label_collection_metadata'),
                                    autoHide: true,
                                    placement: 'auto'
                                }"
                                class="icon is-medium">
                            <i class="tainacan-icon tainacan-icon-36px tainacan-icon-metadata"/>
                        </span>
                        <span class="menu-text">{{ $i18n.getFrom('metadata', 'name') }}</span>
                    </router-link>
                </li>
                <li>
                    <router-link 
                            tag="a" 
                            :to="{ path: $routerHelper.getNewCollectionPath() }" 
                            :aria-label="$i18n.get('label_collection_filters')">
                        <span
                                v-tooltip="{
                                    content: $i18n.get('label_collection_filters'),
                                    autoHide: true,
                                    placement: 'auto'
                                }"
                                class="icon is-medium">
                            <i class="tainacan-icon tainacan-icon-36px tainacan-icon-filters"/>
                        </span>
                        <span class="menu-text">{{ $i18n.getFrom('filters', 'name') }}</span>
                    </router-link>
                </li>
            </ul>
        </template>
        <template v-if="collections.length > 0 && !isLoading">
            <masonry 
                    :cols="{ default: 5, 1919: 4, 1407: 3, 1215: 2, 1023: 2, 767: 1 }"
                    :gutter="25"
                    style="width:100%;">
                <router-link
                        tag="a" 
                        :to="$routerHelper.getNewCollectionPath()"
                        class="tainacan-card new-card">
                    <div class="list-metadata">
                        <span class="icon is-large">
                            <i class="tainacan-icon tainacan-icon-36px tainacan-icon-addcollection"/>
                        </span>
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
                                        <i class="tainacan-icon tainacan-icon-20px tainacan-icon-items"/>
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
                                        <i class="tainacan-icon tainacan-icon-20px tainacan-icon-metadata"/>
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
                                        <i class="tainacan-icon tainacan-icon-20px tainacan-icon-filters"/>
                                    </span>
                                </b-tooltip>
                                <!-- <span class="menu-text">{{ $i18n.getFrom('filters', 'name') }}</span> -->
                            </router-link>
                        </li>
                    </ul>
                </router-link>
                <div
                        v-if="collections.length > 0 && !isLoading" 
                        :key="index"
                        v-for="(collection, index) of collections"
                        class="tainacan-card">       
                    <ul class="menu-list">
                        <li>
                            <router-link 
                                    tag="a" 
                                    :to="{ path: $routerHelper.getCollectionItemsPath(collection.id, '') }" 
                                    :aria-label="$i18n.get('label_collection_items')">
                                <b-tooltip 
                                        :label="$i18n.get('items')"
                                        position="is-bottom">
                                    <span class="icon">
                                        <i class="tainacan-icon tainacan-icon-20px tainacan-icon-items"/>
                                    </span>
                                </b-tooltip>
                                <!-- <span class="menu-text">{{ $i18n.get('items') }}</span> -->
                            </router-link>
                        </li>
                        <li v-if="collection.current_user_can_edit">
                            <router-link
                                    tag="a" 
                                    :to="{ path: $routerHelper.getCollectionEditPath(collection.id) }" 
                                    :aria-label="$i18n.get('label_settings')">
                                <b-tooltip 
                                        :label="$i18n.get('label_settings')"
                                        position="is-bottom">
                                    <span class="icon">
                                        <i class="tainacan-icon tainacan-icon-20px tainacan-icon-settings"/>
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
                                        <i class="tainacan-icon tainacan-icon-20px tainacan-icon-metadata"/>
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
                                        <i class="tainacan-icon tainacan-icon-20px tainacan-icon-filters"/>
                                    </span>
                                </b-tooltip>
                                <!-- <span class="menu-text">{{ $i18n.getFrom('filters', 'name') }}</span> -->
                            </router-link>
                        </li>
                        <li>
                            <router-link 
                                    tag="a" 
                                    :to="{ path: $routerHelper.getCollectionActivitiesPath(collection.id) }"
                                    :aria-label="$i18n.get('label_collection_activities')">
                                <b-tooltip 
                                        :label="$i18n.get('activities')"
                                        position="is-bottom">
                                    <span class="icon">
                                        <i class="tainacan-icon tainacan-icon-20px tainacan-icon-activities"/>
                                    </span>
                                </b-tooltip>
                                <!-- <span class="menu-text">{{ $i18n.get('activities') }}</span> -->
                            </router-link> 
                        </li>
                        <li>
                            <a 
                                    :href="collection.url"
                                    target="_blank" 
                                    :aria-label="$i18n.get('label_view_collection')">
                                <b-tooltip 
                                        :label="$i18n.get('label_view_collection')"
                                        position="is-bottom">
                                    <span class="icon">
                                        <i class="tainacan-icon tainacan-icon-20px tainacan-icon-see"/>
                                    </span>
                                </b-tooltip>
                            </a>
                        </li>
                    </ul>
                    <router-link
                            tag="a"
                            :to="$routerHelper.getCollectionPath(collection.id)"
                            class="card-body">
                        <img 
                            :alt="$i18n.get('label_thumbnail')"
                            v-if="collection.thumbnail != undefined"
                            :src="collection['thumbnail']['tainacan-medium'] ? collection['thumbnail']['tainacan-medium'][0] : (collection['thumbnail'].medium ? collection['thumbnail'].medium[0] : thumbPlaceholderPath)">  
                        
                        <!-- Name -->
                        <div class="metadata-title">
                            <p>{{ collection.name != undefined ? collection.name : '' }}</p>                            
                        </div>
                    </router-link>      
                </div>
            </masonry>
        </template>
    </div>
</template>

<script>
export default {
    name: 'CollectionsHomeList',
    data(){
        return {
            thumbPlaceholderPath: tainacan_plugin.base_url + '/admin/images/placeholder_square.png'
        }
    },
    props: {
        isLoading: false,
        collections: Array,
        collectionsTotal: Number
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
        flex-wrap: nowrap;
        margin: 0 -0.75rem;

        @media screen and (max-width: 768px) {
            flex-wrap: wrap;
        }

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
            }

            a { 
                width: 100%;
                color: $turquoise5; 
                display: flex;
                flex-wrap: wrap;
                flex-direction: column;
                align-items: center;
                justify-content: space-evenly;

            }
        }
    }
    
</style>


