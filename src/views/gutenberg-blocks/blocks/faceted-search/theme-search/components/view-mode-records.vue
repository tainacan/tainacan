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
                        :key="item"
                        v-for="item in 12"
                        :style="{'min-height': randomHeightForRecordsItem() + 'px' }"
                        class="skeleton" />
            </div>
            
            <!-- RECORDS VIEW MODE -->
            <ul
                    v-if="!isLoading"
                    class="tainacan-records-container">
                <li
                        role="listitem"
                        :aria-setsize="totalItems"
                        :aria-posinset="getPosInSet(index)"
                        :data-tainacan-item-id="item.id"
                        :key="index"
                        v-for="(item, index) of items"
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
                            <div class="list-metadata media-body">
                                <div 
                                        class="tainacan-record-thumbnail"
                                        v-if="item.thumbnail != undefined">
                                    <blur-hash-image
                                            @click.left="onClickItem($event, item)"
                                            @click.right="onRightClickItem($event, item)"
                                            v-if="item.thumbnail != undefined"
                                            class="tainacan-record-item-thumbnail"
                                            :width="$thumbHelper.getWidth(item['thumbnail'], 'tainacan-medium-full', 120)"
                                            :height="$thumbHelper.getHeight(item['thumbnail'], 'tainacan-medium-full', 120)"
                                            :hash="$thumbHelper.getBlurhashString(item['thumbnail'], 'tainacan-medium-full')"
                                            :src="$thumbHelper.getSrc(item['thumbnail'], 'tainacan-medium-full', item.document_mimetype)"
                                            :srcset="$thumbHelper.getSrcSet(item['thumbnail'], 'tainacan-medium-full', item.document_mimetype)"
                                            :alt="item.thumbnail_alt ? item.thumbnail_alt : $i18n.get('label_thumbnail')"
                                            :transition-duration="500"
                                        />
                                    <div 
                                            :style="{ 
                                                minHeight: getItemImageHeight(item['thumbnail']['tainacan-medium-full'] ? item['thumbnail']['tainacan-medium-full'][1] : (item['thumbnail'].medium_large ? item['thumbnail'].medium_large[1] : 120), item['thumbnail']['tainacan-medium-full'] ? item['thumbnail']['tainacan-medium-full'][2] : (item['thumbnail'].medium_large ? item['thumbnail'].medium_large[2] : 120)) + 'px',
                                                marginTop: '-' + getItemImageHeight(item['thumbnail']['tainacan-medium-full'] ? item['thumbnail']['tainacan-medium-full'][1] : (item['thumbnail'].medium_large ? item['thumbnail'].medium_large[1] : 120), item['thumbnail']['tainacan-medium-full'] ? item['thumbnail']['tainacan-medium-full'][2] : (item['thumbnail'].medium_large ? item['thumbnail'].medium_large[2] : 120)) + 'px'
                                            }" />
                                </div>
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
                </li>
            </ul>
        </div> 
    </div>
</template>

<script>
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
                    this.$nextTick(() => {
                        if (this.masonry !== false)
                            this.masonry.destroy();
                        
                        this.masonry = new Masonry( '.tainacan-records-container', {
                            itemSelector: 'li',
                            columnWidth: '.tainacan-records-grid-sizer',
                            gutter: 30,
                            percentPosition: true
                        });
                    });
                }
            },
            immediate: true
        }
    },
    beforeDestroy() {
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


