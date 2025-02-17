<template>
    <div class="table-container">
        <div
                ref="masonryWrapper"
                class="table-wrapper">

            <!-- Empty result placeholder, rendered in the parent component -->
            <slot />

            <!-- SKELETON LOADING -->
            <div 
                    v-if="isLoading"
                    class="tainacan-records-container--skeleton">
                <div 
                        v-for="item in 12"
                        :key="item"
                        :style="{'min-height': randomHeightForRecordsItem() + 'px' }"
                        class="skeleton" />
            </div>
            
            <!-- RECORDS VIEW MODE -->
            <ul
                    v-if="!isLoading"
                    class="tainacan-records-container">
                <li
                        v-for="(item, index) of items"
                        :key="index"
                        role="listitem"
                        :aria-setsize="totalItems"
                        :aria-posinset="getPosInSet(index)"
                        :data-tainacan-item-id="item.id"
                        :class="{ 'tainacan-records-grid-sizer': index == 0 }">
                    <a 
                            :href="getItemLink(item.url, index)"
                            class="tainacan-record">

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
                                            show: 500,
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
                                            show: 500,
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
                                            show: 500,
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
                            <div class="list-metadata media-body">
                                <div 
                                        v-if="item.thumbnail != undefined"
                                        class="tainacan-record-thumbnail">
                                    <blur-hash-image
                                            v-if="item.thumbnail != undefined"
                                            class="tainacan-record-item-thumbnail"
                                            :width="$thumbHelper.getWidth(item['thumbnail'], 'tainacan-medium-full', 120)"
                                            :height="$thumbHelper.getHeight(item['thumbnail'], 'tainacan-medium-full', 120)"
                                            :hash="$thumbHelper.getBlurhashString(item['thumbnail'], 'tainacan-medium-full')"
                                            :src="$thumbHelper.getSrc(item['thumbnail'], 'tainacan-medium-full', item.document_mimetype)"
                                            :srcset="$thumbHelper.getSrcSet(item['thumbnail'], 'tainacan-medium-full', item.document_mimetype)"
                                            :alt="item.thumbnail_alt ? item.thumbnail_alt : $i18n.get('label_thumbnail')"
                                            :transition-duration="500"
                                            @click.left="onClickItem($event, item)"
                                        />
                                    <div 
                                            :style="{ 
                                                minHeight: getItemImageHeight(item['thumbnail']['tainacan-medium-full'] ? item['thumbnail']['tainacan-medium-full'][1] : (item['thumbnail'].medium_large ? item['thumbnail'].medium_large[1] : 120), item['thumbnail']['tainacan-medium-full'] ? item['thumbnail']['tainacan-medium-full'][2] : (item['thumbnail'].medium_large ? item['thumbnail'].medium_large[2] : 120)) + 'px',
                                                marginTop: '-' + getItemImageHeight(item['thumbnail']['tainacan-medium-full'] ? item['thumbnail']['tainacan-medium-full'][1] : (item['thumbnail'].medium_large ? item['thumbnail'].medium_large[1] : 120), item['thumbnail']['tainacan-medium-full'] ? item['thumbnail']['tainacan-medium-full'][2] : (item['thumbnail'].medium_large ? item['thumbnail'].medium_large[2] : 120)) + 'px'
                                            }" />
                                </div>
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
                </li>
            </ul>
        </div> 
    </div>
</template>

<script>
import { nextTick } from 'vue';
import { viewModesMixin } from '../js/view-modes-mixin.js';
import Masonry from 'masonry-layout';

export default {
    name: 'ViewModeRecords',
    mixins: [
        viewModesMixin
    ],
    data () {
        return {
            masonry: false
        }
    },
    computed: {
        amountOfDisplayedMetadata() {
            return this.displayedMetadata.filter((metadata) => metadata.display).length;
        }
    },
    watch: {
        isLoading: { 
            handler() {
                if (this.items && this.items.length > 0 && !this.isLoading) {
                    nextTick(() => {
                        if (this.masonry !== false)
                            this.masonry.destroy();
                        
                        this.masonry = new Masonry( this.containerId ? ( '#' + this.containerId + ' .tainacan-records-container' ) : '.tainacan-records-container', {
                            itemSelector: 'li',
                            columnWidth: '.tainacan-records-grid-sizer',
                            gutter: 30,
                            percentPosition: true,
                            transitionDuration: '0.2s'
                        });
                    });
                }
            },
            immediate: true
        },
        filtersModalStateHasChanged: {
            handler() {
                if (this.masonry !== false)
                    this.masonry.layout();
            },
            immediate: true
        }
    },
    beforeUnmount() {
        if (this.masonry !== false)
            this.masonry.destroy();
    },
    methods: {
        randomHeightForRecordsItem() {
            let min = (70*this.amountOfDisplayedMetadata)*0.8;
            let max = (70*this.amountOfDisplayedMetadata)*1.2;
            return Math.floor(Math.random()*(max-min+1)+min);
        },
        getItemImageHeight(imageWidth, imageHeight) {  
            let itemWidth = 120;
            return (imageHeight*itemWidth)/imageWidth;
        }
    }
}
</script>

<style  lang="scss" scoped>

    @import "../../../../../admin/scss/_view-mode-records.scss";

    .tainacan-records-container .tainacan-record .metadata-title {
        padding: 0.6em 0.875em;
    }
</style>


