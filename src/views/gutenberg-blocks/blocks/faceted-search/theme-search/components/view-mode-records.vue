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
            <masonry
                    v-if="isLoading"
                    :cols="masonryCols"
                    :gutter="30"                    
                    class="tainacan-records-container">
                <div 
                        :key="item"
                        v-for="item in 12"
                        :style="{'min-height': randomHeightForRecordsItem() + 'px' }"
                        class="skeleton tainacan-record" />
            </masonry>
            
            <!-- RECORDS VIEW MODE -->
            <masonry 
                    role="list"
                    v-if="!isLoading && items.length > 0"
                    :cols="masonryCols"
                    :gutter="30"
                    class="tainacan-records-container">
                <a 
                        role="listitem"
                        :data-tainacan-item-id="item.id"
                        :href="getItemLink(item.url, index)"
                        :key="index"
                        v-for="(item, index) of items"
                        class="tainacan-record">
                    <!-- <div :href="getItemLink(item.url, index)"> -->
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
                </a>
                <!-- </div> -->
            </masonry>
        </div> 
    </div>
</template>

<script>
import { viewModesMixin } from '../js/view-modes-mixin.js';

export default {
    name: 'ViewModeRecords',
    mixins: [
        viewModesMixin
    ],
    data () {
        return {
            masonryCols: {default: 4, 1919: 3, 1407: 2, 1215: 2, 1023: 1, 767: 1, 343: 1}
        }
    },
    computed: {
        amountOfDisplayedMetadata() {
            return this.displayedMetadata.filter((metadata) => metadata.display).length;
        }
    },
    watch: {
        isFiltersMenuCompressed() {
            if (this.$refs.masonryWrapper != undefined && 
                this.$refs.masonryWrapper.children[0] != undefined && 
                this.$refs.masonryWrapper.children[0].children[0] != undefined && 
                this.$refs.masonryWrapper.children[0].children[0].clientWidth != undefined) {
                this.containerWidthDiscount = (window.innerWidth || document.documentElement.clientWidth || document.body.clientWidth) - this.$refs.masonryWrapper.clientWidth;
            }
            this.$forceUpdate();
        },
        containerWidthDiscount() {
            let obj = {};
            obj['default'] = 4;
            obj[1980 - this.containerWidthDiscount] = 3;
            obj[1460 - this.containerWidthDiscount] = 2;
            obj[1275 - this.containerWidthDiscount] = 2;
            obj[1080 - this.containerWidthDiscount] = 1;
            obj[828 - this.containerWidthDiscount] = 1;
            obj[400] = 1;
            this.masonryCols = obj;
        }
    },
    mounted() {
        if (this.$refs.masonryWrapper != undefined && 
            this.$refs.masonryWrapper.children[0] != undefined && 
            this.$refs.masonryWrapper.children[0].children[0] != undefined && 
            this.$refs.masonryWrapper.children[0].children[0].clientWidth != undefined) {
                this.itemColumnWidth = this.$refs.masonryWrapper.children[0].children[0].clientWidth;
                this.recalculateContainerWidth();
            } else
                this.itemColumnWidth = 202;
    },
    created() {
        window.addEventListener('resize', this.recalculateContainerWidth);  
    },
    beforeDestroy() {
        window.removeEventListener('resize', this.recalculateContainerWidth);
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
        },
        recalculateContainerWidth: _.debounce( function() {
            if (this.$refs.masonryWrapper != undefined && 
                this.$refs.masonryWrapper.children[0] != undefined && 
                this.$refs.masonryWrapper.children[0].children[0] != undefined && 
                this.$refs.masonryWrapper.children[0].children[0].clientWidth != undefined) {
                if (window.innerWidth || document.documentElement.clientWidth || document.body.clientWidth)
                    this.containerWidthDiscount = (window.innerWidth || document.documentElement.clientWidth || document.body.clientWidth) - this.$refs.masonryWrapper.clientWidth;
            }
            this.$forceUpdate();
        }, 500)
    }
}
</script>

<style  lang="scss" scoped>

    @import "../../../../../admin/scss/_view-mode-records.scss";

    .tainacan-records-container .tainacan-record .metadata-title {
        padding: 0.6em 0.875em;
    }
</style>


