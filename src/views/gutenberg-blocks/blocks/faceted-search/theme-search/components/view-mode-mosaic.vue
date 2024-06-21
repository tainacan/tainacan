<template>
    <div class="table-container">
        <div class="table-wrapper">

            <!-- Empty result placeholder, rendered in the parent component -->
            <slot />

            <!-- SKELETON LOADING -->
            <div 
                    v-if="isLoading"
                    class="tainacan-mosaic-container--skeleton">
                <div 
                        v-for="item in 12"
                        :key="item"
                        :style="{
                            '--tainacan-mosaic-item-width': randomHeightForMosaicItem(),
                            '--tainacan-mosaic-item-height': randomHeightForMosaicItem()
                        }"
                        class="skeleton" />
            </div>

            <!-- MOSAIC VIEW MODE -->
            <ul 
                    v-if="!isLoading"
                    class="tainacan-mosaic-container">
                <li
                        v-for="(item, index) of items"
                        :key="index"
                        :style="{
                            '--tainacan-mosaic-item-width': getAcceptableWidthBasedOnRatio(item['thumbnail'], 'tainacan-large-full', 300),
                            '--tainacan-mosaic-item-height': $thumbHelper.getHeight(item['thumbnail'], 'tainacan-large-full', 300)
                        }"
                        :data-tainacan-item-id="item.id"
                        :aria-setsize="totalItems"
                        :aria-posinset="getPosInSet(index)">
                    <a 
                            
                            class="tainacan-mosaic-item" 
                            :href="getItemLink(item.url, index)">

                        <!-- JS-side hook for extra content -->
                        <div 
                                v-if="hasBeforeHook()"
                                class="faceted-search-hook faceted-search-hook-item-before"
                                v-html="getBeforeHook(item)" />

                        <!-- Thumbnail -->
                        <blur-hash-image
                                v-if="item.thumbnail != undefined"
                                class="tainacan-mosaic-item-thumbnail"
                                :width="$thumbHelper.getWidth(item['thumbnail'], 'tainacan-large-full', 320)"
                                :height="$thumbHelper.getHeight(item['thumbnail'], 'tainacan-large-full', 320)"
                                :hash="$thumbHelper.getBlurhashString(item['thumbnail'], 'tainacan-large-full')"
                                :src="$thumbHelper.getSrc(item['thumbnail'], 'tainacan-large-full', item.document_mimetype)"
                                :srcset="$thumbHelper.getSrcSet(item['thumbnail'], 'tainacan-large-full', item.document_mimetype)"
                                :alt="item.thumbnail_alt ? item.thumbnail_alt : $i18n.get('label_thumbnail')"
                                :transition-duration="500"
                            />


                        <!-- Title -->
                        <div
                                class="metadata-title"
                                :style="isSlideshowViewModeEnabled ? 'padding-right: 2rem;' : ''">
                            <p 
                                    v-tooltip="{
                                        delay: {
                                            show: 750,
                                            hide: 100,
                                        },
                                        content: item.title != undefined ? item.title : '',
                                        html: true,
                                        placement: 'auto-start',
                                        popperClass: ['tainacan-tooltip', 'tooltip']
                                    }" 
                                    v-html="item.title != undefined ? item.title : ''" />
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

export default {
    name: 'ViewModeMosaic',
    mixins: [
        viewModesMixin
    ],
    methods: {
        randomHeightForMosaicItem() {
            let min = 120;
            let max = 280;

            return Math.floor(Math.random()*(max-min+1)+min);
        },
        getAcceptableWidthBasedOnRatio(thumbnail, size, defaultSize) {
            const width = this.$thumbHelper.getWidth(thumbnail, size, defaultSize);
            const height = this.$thumbHelper.getHeight(thumbnail, size, defaultSize);

            return (width / height) > 0.7 ? width : ( height * 0.7 );
        }
    }
}
</script>

<style  lang="scss" scoped>

    @import "../../../../../admin/scss/_view-mode-mosaic.scss";

</style>


