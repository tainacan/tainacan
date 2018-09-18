<template>
    <div class="tainacan-cards-container">
        <router-link
                tag="a" 
                :to="$routerHelper.getNewCollectionPath()"
                class="tainacan-card">
            <b-icon 
                    size="is-large"
                    icon="folder-plus" />
        </router-link>
        <router-link
                tag="a"
                :to="$routerHelper.getCollectionPath(collection.id)"
                v-if="collections.length > 0 && !isLoading" 
                :key="index"
                v-for="(collection, index) of collections"
                class="tainacan-card">                                
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
            <!-- Remaining metadata -->  
            <div class="media">

                <img 
                        v-if="collection.thumbnail != undefined"
                        :src="collection['thumbnail'].tainacan_medium ? collection['thumbnail'].tainacan_medium : (collection['thumbnail'].medium ? collection['thumbnail'].medium : thumbPlaceholderPath)">  

                <div class="list-metadata media-body">
                    <!-- Description -->
                    <p 
                            v-tooltip="{
                                content: collection.description != undefined && collection.description != '' ? collection.description : `<span class='has-text-gray is-italic'>` + $i18n.get('label_description_not_informed') + `</span>`,
                                html: true,
                                autoHide: false,
                                placement: 'auto-start'
                            }"   
                            class="metadata-description"
                            v-html="collection.description != undefined && collection.description != '' ? getLimitedDescription(collection.description) : `<span class='has-text-gray is-italic'>` + $i18n.get('label_description_not_informed') + `</span>`" />                                                        
                    <br>                    
                </div>
            </div>
            <ul class="menu-list level-right">
                <li class="level-item">
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
                <li class="level-item">
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
                <li class="level-item">
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
                <li class="level-item">
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
                <li class="level-item">
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
        </router-link>
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
    },
    methods: {
        getLimitedDescription(description) {
            let maxCharacter = (window.innerWidth || document.documentElement.clientWidth || document.body.clientWidth) <= 480 ? 155 : 330;
            return description.length > maxCharacter ? description.substring(0, maxCharacter - 3) + '...' : description;
        }
    }
}
</script>

<style lang="scss" scoped>

    @import "../../scss/_variables.scss";
    @import "../../scss/_collection-home-cards.scss";

    
</style>


