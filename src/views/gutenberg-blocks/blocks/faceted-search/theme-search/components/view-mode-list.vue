<template>
    <div class="table-container">
        <div class="table-wrapper">

            <!-- Empty result placeholder, rendered in the parent component -->
            <slot />

            <!-- SKELETON LOADING -->
            <div
                    v-if="isLoading"                  
                    class="tainacan-list-container">
                <div 
                        :key="item"
                        v-for="item in 12"
                        style="min-height: 200px"
                        class="skeleton tainacan-list" />
            </div>
            
            <!-- LIST VIEW MODE -->
            <div
                    role="list"
                    v-if="!isLoading && items.length > 0"
                    class="tainacan-list-container">
                <div
                        role="listitem"
                        :aria-setsize="totalItems"
                        :aria-posinset="getPosInSet(index)"
                        :data-tainacan-item-id="item.id"
                        :key="index"
                        v-for="(item, index) of items"
                        class="tainacan-list">
                    <a :href="getItemLink(item.url, index)">

                        <!-- JS-side hook for extra content -->
                        <div 
                                v-if="hasBeforeHook()"
                                class="faceted-search-hook faceted-search-hook-item-before"
                                v-html="getBeforeHook(item)" />

                        <!-- Title -->
                        <div class="metadata-title">
                            <p 
                                    v-tooltip="{
                                        delay: {
                                            shown: 500,
                                            hide: 300,
                                        },
                                        content: item.metadata != undefined ? renderMetadata(item, column) : '',
                                        html: true,
                                        autoHide: false,
                                        placement: 'auto-start',
                                        popperClass: ['tainacan-tooltip', 'tooltip']
                                    }"
                                    v-for="(column, metadatumIndex) in displayedMetadata"
                                    :key="metadatumIndex"
                                    v-if="column.display && column.metadata_type_object != undefined && (column.metadata_type_object.related_mapped_prop == 'title')"
                                    v-html="item.metadata != undefined && collectionId ? renderMetadata(item, column) : (item.title ? item.title :`<span class='has-text-gray3 is-italic'>` + $i18n.get('label_value_not_provided') + `</span>`)" />                 
                            <span 
                                    v-if="isSlideshowViewModeEnabled"
                                    v-tooltip="{
                                        delay: {
                                            shown: 500,
                                            hide: 100,
                                        },
                                        content: $i18n.get('label_see_on_fullscreen'),
                                        placement: 'auto-start',
                                        popperClass: ['tainacan-tooltip', 'tooltip']
                                    }"          
                                    @click.prevent="starSlideshowFromHere(index)"
                                    class="icon slideshow-icon">
                                <i class="tainacan-icon tainacan-icon-viewgallery tainacan-icon-1-125em"/>
                            </span> 
                        </div>

                        
                        <!-- Remaining metadata -->  
                        <div class="media">
                                <div 
                                    class="tainacan-list-thumbnail"
                                    v-if="item.thumbnail != undefined">
                                <blur-hash-image
                                        v-if="item.thumbnail != undefined"
                                        class="tainacan-list-item-thumbnail"
                                        :width="$thumbHelper.getWidth(item['thumbnail'], 'tainacan-medium-full', 120)"
                                        :height="$thumbHelper.getHeight(item['thumbnail'], 'tainacan-medium-full', 120)"
                                        :hash="$thumbHelper.getBlurhashString(item['thumbnail'], 'tainacan-medium-full')"
                                        :src="$thumbHelper.getSrc(item['thumbnail'], 'tainacan-medium-full', item.document_mimetype)"
                                        :srcset="$thumbHelper.getSrcSet(item['thumbnail'], 'tainacan-medium-full', item.document_mimetype)"
                                        :alt="item.thumbnail_alt ? item.thumbnail_alt : $i18n.get('label_thumbnail')"
                                        :transition-duration="500"
                                    />
                                <!-- <img 
                                        :alt="item.thumbnail_alt ? item.thumbnail_alt : $i18n.get('label_thumbnail')"
                                        :src="$thumbHelper.getSrc(item['thumbnail'], 'tainacan-medium-full', item.document_mimetype)">   -->
                            </div>
                            <div class="list-metadata media-body">
                                <span 
                                        v-for="(column, metadatumIndex) in displayedMetadata"
                                        :key="metadatumIndex"
                                        :class="{ 'metadata-type-textarea': column.metadata_type_object.component == 'tainacan-textarea' }"
                                        v-if="renderMetadata(item, column) != '' && column.display && column.slug != 'thumbnail' && column.metadata_type_object != undefined && (column.metadata_type_object.related_mapped_prop != 'title')">
                                    <h3 class="metadata-label">{{ column.name }}</h3>
                                    <p      
                                            v-html="renderMetadata(item, column)"
                                            class="metadata-value"/>
                                </span>
                            </div>
                        </div>

                        <!-- JS-side hook for extra content -->
                        <div 
                                v-if="hasAfterHook()"
                                class="faceted-search-hook faceted-search-hook-item-after"
                                v-html="getAfterHook(item)" />
                                
                    </a>
                </div>
            </div>
        </div> 
    </div>
</template>

<script>
import { viewModesMixin } from '../js/view-modes-mixin.js';

export default {
    name: 'ViewModeList',
    mixins: [
        viewModesMixin
    ]
}
</script>

<style  lang="scss" scoped>

    @import "../../../../../admin/scss/view-mode-list.scss";

    .tainacan-list-container .tainacan-list .metadata-title {
        padding: 0.5em 0.875em;
    }
</style>


