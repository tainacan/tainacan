<template>
    <div class="table-container">
        <div 
                ref="masonryWrapper"
                class="table-wrapper">

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
            <!-- <masonry
                    v-if="isLoading"
                    :cols="masonryCols"
                    :gutter="25"                    
                    class="tainacan-masonry-container">
                <div 
                        :key="item"
                        v-for="item in 12"
                        :style="{'min-height': randomHeightForMasonryItem() + 'px' }"
                        class="skeleton tainacan-masonry-item" />
            </masonry> -->

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
                                :width="$thumbHelper.getWidth(item['thumbnail'], 'tainacan-medium-full', 360)"
                                :height="$thumbHelper.getHeight(item['thumbnail'], 'tainacan-medium-full', 360)"
                                :hash="$thumbHelper.getBlurhashString(item['thumbnail'], 'tainacan-medium-full')"
                                :src="$thumbHelper.getSrc(item['thumbnail'], 'tainacan-medium-full', item.document_mimetype)"
                                :srcset="$thumbHelper.getSrcSet(item['thumbnail'], 'tainacan-medium-full', item.document_mimetype)"
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
            containerWidthDiscount: Number,
            masonryCols: { default: 5, 2559: 5, 1919: 4, 1407: 4, 1215: 3, 1023: 3, 767: 2, 343: 1 },
            masonry: false
        }
    },
    watch: {
        isFiltersMenuCompressed() {
            if (this.$refs.masonryWrapper != undefined && 
                this.$refs.masonryWrapper.children[0] != undefined && 
                this.$refs.masonryWrapper.children[0].children[0] != undefined && 
                this.$refs.masonryWrapper.children[0].children[0].clientWidth != undefined) {
                if ((window.innerWidth || document.documentElement.clientWidth || document.body.clientWidth))
                    this.containerWidthDiscount = (window.innerWidth || document.documentElement.clientWidth || document.body.clientWidth) - this.$refs.masonryWrapper.clientWidth;
            }
            this.$forceUpdate();
        },
        containerWidthDiscount() {
            let obj = {};
            obj['default'] = 5;
            obj[2560 - this.containerWidthDiscount] = 5;
            obj[1980 - this.containerWidthDiscount] = 4;
            obj[1460 - this.containerWidthDiscount] = 4;
            obj[1275 - this.containerWidthDiscount] = 3;
            obj[1080 - this.containerWidthDiscount] = 3;
            obj[828 - this.containerWidthDiscount] = 2;
            obj[400] = 1;
            this.masonryCols = obj;
        },
        isLoading: { 
            handler() {
                if (this.items && this.items.length > 0) {
                    this.$nextTick(() => {
                        console.log(this.items[0]['thumbnail']) // update based on existence of large-medium
                        console.log(this.masonry)
                        if (this.masonry !== false)
                            this.masonry.destroy();
                        
                        this.masonry = new Masonry( '.tainacan-masonry-container', {
                            itemSelector: 'li',
                            columnWidth: 400,
                            gutter: 25
                        });
                    });
                }
            },
            immediate: true
        }
    },
    methods: {
        randomHeightForMasonryItem() {
            let min = 120;
            let max = 380;

            return Math.floor(Math.random()*(max-min+1)+min);
        }
    },
    beforeDestroy() {
        if (this.masonry !== false)
            this.masonry.destroy();
    }
}
</script>

<style  lang="scss" scoped>

    @import "../../../../../admin/scss/_view-mode-masonry.scss";

    .tainacan-masonry-container .tainacan-masonry-item .metadata-title {
        margin: 0px 3px;
    }
</style>


