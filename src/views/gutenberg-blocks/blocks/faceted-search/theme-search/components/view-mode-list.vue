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
                        v-for="item in 12"
                        :key="item"
                        style="min-height: 200px"
                        class="skeleton tainacan-list" />
            </div>
            
            <!-- LIST VIEW MODE -->
            <div
                    v-if="!isLoading && items.length > 0"
                    role="list"
                    class="tainacan-list-container">
                <div
                        v-for="(item, index) of items"
                        :key="index"
                        role="listitem"
                        :aria-setsize="totalItems"
                        :aria-posinset="getPosInSet(index)"
                        :data-tainacan-item-id="item.id"
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
                                    v-if="collectionId && titleItemMetadatum"
                                    v-tooltip="{
                                        delay: {
                                            shown: 500,
                                            hide: 300,
                                        },
                                        content: item.metadata != undefined ? renderMetadata(item, titleItemMetadatum) : '',
                                        html: true,
                                        autoHide: false,
                                        placement: 'auto-start',
                                        popperClass: ['tainacan-tooltip', 'tooltip']
                                    }"
                                    v-html="item.metadata != undefined ? renderMetadata(item, titleItemMetadatum) : ''" />                 
                            <p 
                                    v-if="!collectionId && titleItemMetadatum"
                                    v-tooltip="{
                                        delay: {
                                            shown: 500,
                                            hide: 300,
                                        },
                                        content: item.title != undefined ? item.title : (`<span class='has-text-gray3 is-italic'>` + $i18n.get('label_value_not_provided') + `</span>`),
                                        html: true,
                                        autoHide: false,
                                        placement: 'auto-start',
                                        popperClass: ['tainacan-tooltip', 'tooltip']
                                    }"
                                    v-html="item.title != undefined ? item.title : (`<span class='has-text-gray3 is-italic'>` + $i18n.get('label_value_not_provided') + `</span>`)" />
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
                                    class="icon slideshow-icon"
                                    @click.prevent="starSlideshowFromHere(index)">
                                <i class="tainacan-icon tainacan-icon-viewgallery tainacan-icon-1-125em" />
                            </span> 
                        </div>

                        
                        <!-- Remaining metadata -->  
                        <div class="media">
                            <div 
                                    v-if="item.thumbnail != undefined"
                                    class="tainacan-list-thumbnail">
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
                                <template 
                                        v-for="(column, metadatumIndex) in displayedMetadata"
                                        :key="metadatumIndex">
                                    <span 
                                            v-if="renderMetadata(item, column) != '' && column.display && column.slug != 'thumbnail' && column.metadata_type_object != undefined && (column.metadata_type_object.related_mapped_prop != 'title')"
                                            :class="{ 'metadata-type-textarea': column.metadata_type_object.component == 'tainacan-textarea' }">
                                        <h3 class="metadata-label">{{ column.name }}</h3>
                                        <p      
                                                class="metadata-value"
                                                v-html="renderMetadata(item, column)" />
                                    </span>
                                </template>
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


