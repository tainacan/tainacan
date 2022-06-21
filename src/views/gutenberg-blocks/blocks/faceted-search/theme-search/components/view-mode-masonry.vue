<template>
    <div class="table-container">
        <div class="table-wrapper">

            <!-- Empty result placeholder -->
            <section
                    v-if="!isLoading && items.length <= 0"
                    class="section">
                <div class="content has-text-gray4 has-text-centered">
                    <p>
                        <span class="icon is-large">
                            <i class="tainacan-icon tainacan-icon-36px tainacan-icon-items" />
                        </span>
                    </p>
                    <p>{{ $i18n.get('info_no_item_found') }}</p>
                </div>
            </section>

            <!-- SKELETON LOADING -->
            <div 
                    v-if="isLoading"
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
                                :width="$thumbHelper.getWidth(item['thumbnail'], 'tainacan-large-full', 280)"
                                :height="$thumbHelper.getHeight(item['thumbnail'], 'tainacan-large-full', 280)"
                                :hash="$thumbHelper.getBlurhashString(item['thumbnail'], 'tainacan-large-full')"
                                :src="$thumbHelper.getSrc(item['thumbnail'], 'tainacan-large-full', item.document_mimetype)"
                                :srcset="$thumbHelper.getSrcSet(item['thumbnail'], 'tainacan-large-full', item.document_mimetype)"
                                :alt="item.thumbnail_alt ? item.thumbnail_alt : $i18n.get('label_thumbnail')"
                                :transition-duration="500"
                            />

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
            masonry: false
        }
    },
    watch: {
        isLoading: { 
            handler() {
                if (this.items && this.items.length > 0) {
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


