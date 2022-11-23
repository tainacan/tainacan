<template>
    <div class="table-container">
        <div class="table-wrapper">

            <!-- Empty result placeholder, rendered in the parent component -->
            <slot />

            <!-- SKELETON LOADING -->
            <div 
                    v-if="isLoading"
                    :class="{
                        'tainacan-masonry-container--legacy': shouldUseLegacyMasonyCols
                    }"
                    class="tainacan-masonry-container--skeleton">
                <div 
                        :key="item"
                        v-for="item in 12"
                        :style="{'min-height': randomHeightForMasonryItem() + 'px' }"
                        class="skeleton" />
            </div>

            <!-- MASONRY VIEW MODE -->
            <ul 
                    v-if="!isLoading"
                    :class="{
                        'tainacan-masonry-container--legacy': shouldUseLegacyMasonyCols
                    }"
                    class="tainacan-masonry-container">
                <li
                        :data-tainacan-item-id="item.id"
                        :aria-setsize="totalItems"
                        :aria-posinset="getPosInSet(index)"
                        :key="index"
                        v-for="(item, index) of items"
                        :class="{ 'tainacan-masonry-grid-sizer': index == 0 }">
                    <a 
                            
                            class="tainacan-masonry-item" 
                            :href="getItemLink(item.url, index)">

                        <!-- JS-side hook for extra content -->
                        <div 
                                v-if="hasBeforeHook()"
                                class="faceted-search-hook faceted-search-hook-item-before"
                                v-html="getBeforeHook(item)" />

                        <!-- Title -->
                        <div class="metadata-title">
                            <p v-html="item.title != undefined ? item.title : ''" />
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

                        <!-- Thumbnail -->
                        <blur-hash-image
                                v-if="item.thumbnail != undefined"
                                class="tainacan-masonry-item-thumbnail"
                                :width="$thumbHelper.getWidth(item['thumbnail'], shouldUseLegacyMasonyCols ? 'tainacan-medium-full' : 'tainacan-large-full', 280)"
                                :height="$thumbHelper.getHeight(item['thumbnail'], shouldUseLegacyMasonyCols ? 'tainacan-medium-full' : 'tainacan-large-full', 280)"
                                :hash="$thumbHelper.getBlurhashString(item['thumbnail'], shouldUseLegacyMasonyCols ? 'tainacan-medium-full' : 'tainacan-large-full')"
                                :src="$thumbHelper.getSrc(item['thumbnail'], shouldUseLegacyMasonyCols ? 'tainacan-medium-full' : 'tainacan-large-full', item.document_mimetype)"
                                :srcset="$thumbHelper.getSrcSet(item['thumbnail'], shouldUseLegacyMasonyCols ? 'tainacan-medium-full' : 'tainacan-large-full', item.document_mimetype)"
                                :alt="item.thumbnail_alt ? item.thumbnail_alt : $i18n.get('label_thumbnail')"
                                :transition-duration="500"
                            />

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
    name: 'ViewModeMasonry',
    mixins: [
        viewModesMixin
    ],
    data () {
        return {
            masonry: false,
            shouldUseLegacyMasonyCols: false
        }
    },
    watch: {
        isLoading: { 
            handler() {
                if (this.items && this.items.length > 0 && !this.isLoading) {
                    this.$nextTick(() => {
                        if (this.masonry !== false)
                            this.masonry.destroy();
                        
                        this.masonry = new Masonry( '.tainacan-masonry-container', {
                            itemSelector: 'li',
                            columnWidth: '.tainacan-masonry-grid-sizer',
                            gutter: 25,
                            percentPosition: true
                        });
                    });
                }
            },
            immediate: true
        }
    },
    created() {
        this.shouldUseLegacyMasonyCols = wp !== undefined && wp.hooks !== undefined && wp.hooks.hasFilter('tainacan_use_legacy_masonry_view_mode_cols') && wp.hooks.applyFilters('tainacan_use_legacy_masonry_view_mode_cols', false);
    },
    beforeDestroy() {
        if (this.masonry !== false)
            this.masonry.destroy();
    },
    methods: {
        randomHeightForMasonryItem() {
            let min = 120;
            let max = 380;

            return Math.floor(Math.random()*(max-min+1)+min);
        }
    }
}
</script>

<style  lang="scss" scoped>

    @import "../../../../../admin/scss/_view-mode-masonry.scss";

    .tainacan-masonry-container .tainacan-masonry-item .metadata-title {
        margin: 0px 3px;
    }
</style>


