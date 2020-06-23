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
                    class="tainacan-slides-container">
                <div 
                        :key="item"
                        v-for="item in 12"
                        style="min-height: 200px"
                        class="skeleton tainacan-slide" />
            </div>

            <div
                    role="list"
                    class="tainacan-slides-container">
                <transition 
                        mode="out-in"
                        :name="goingRight ? 'slide-right' : 'slide-left'" >
                    <div
                            role="listitem"
                            :key="index"
                            v-for="(item, index) of items"
                            v-if="index == slideIndex"
                            class="tainacan-slide">

                        <div class="item-area">

                            <!-- Title -->
                            <div class="metadata-title">
                                <p 
                                        v-tooltip="{
                                            delay: {
                                                show: 500,
                                                hide: 300,
                                            },
                                            content: item.metadata != undefined ? renderMetadata(item.metadata, column) : '',
                                            html: true,
                                            autoHide: false,
                                            placement: 'auto-start'
                                        }"
                                        v-for="(column, columnIndex) in displayedMetadata"
                                        :key="columnIndex"
                                        v-if="collectionId != undefined && column.display && column.metadata_type_object != undefined && (column.metadata_type_object.related_mapped_prop == 'title')"
                                        @click="goToItemPage(item)"
                                        v-html="item.metadata != undefined ? renderMetadata(item.metadata, column) : ''" />
                                <p
                                        v-tooltip="{
                                            delay: {
                                                show: 500,
                                                hide: 300,
                                            },
                                            content: item.title != undefined ? item.title : '',
                                            html: true,
                                            autoHide: false,
                                            placement: 'auto-start'
                                        }"
                                        v-for="(column, columnIndex) in displayedMetadata"
                                        :key="columnIndex"
                                        v-if="collectionId == undefined && column.display && column.metadata_type_object != undefined && (column.metadata_type_object.related_mapped_prop == 'title')"
                                        @click="goToItemPage(item)"
                                        v-html="item.title != undefined ? item.title : ''" />
                            </div>

                            <!-- Item thumbnail -->
                            <div class="tainacan-slide-item">
                                <img
                                        :alt="$i18n.get('label_thumbnail')"
                                        v-if="item.thumbnail != undefined"
                                        :src="item['thumbnail']['full'] ? item['thumbnail']['full'][0] : (item['thumbnail'].full ? item['thumbnail'].full[0] : thumbPlaceholderPath)">
                            </div>
                        </div>

                        <!-- Remaining metadata -->
                        <div
                                class="aside-item"
                                @click="goToItemPage(item)">
                            <div class="list-metadata media-body">

                                <span
                                        v-for="(column, metadatumIndex) in displayedMetadata"
                                        :key="metadatumIndex"
                                        :class="{ 'metadata-type-textarea': column.metadata_type_object != undefined && column.metadata_type_object.component == 'tainacan-textarea' }"
                                        v-if="collectionId == undefined && column.display && column.metadata_type_object != undefined && (column.metadata_type_object.related_mapped_prop == 'description')">
                                    <h3 class="metadata-label">{{ $i18n.get('label_description') }}</h3>
                                    <p
                                            v-html="item.description != undefined ? item.description : ''"
                                            class="metadata-value"/>
                                </span>
                                <span
                                        v-for="(column, metadatumIndex) in displayedMetadata"
                                        :key="metadatumIndex"
                                        :class="{ 'metadata-type-textarea': column.metadata_type_object != undefined && column.metadata_type_object.component == 'tainacan-textarea' }"
                                        v-if="renderMetadata(item.metadata, column) != '' && column.display && column.slug != 'thumbnail' && column.metadata_type_object != undefined && (column.metadata_type_object.related_mapped_prop != 'title')">
                                    <h3 class="metadata-label">{{ column.name }}</h3>
                                    <p
                                            v-html="renderMetadata(item.metadata, column)"
                                            class="metadata-value"/>
                                </span>
                                <span
                                        v-for="(column, metadatumIndex) in displayedMetadata"
                                        :key="metadatumIndex"
                                        v-if="(column.metadatum == 'row_creation' || column.metadatum == 'row_author') && item[column.slug] != undefined">
                                    <h3 class="metadata-label">{{ column.name }}</h3>
                                    <p
                                            v-html="column.metadatum == 'row_creation' ? parseDateToNavigatorLanguage(item[column.slug]) : item[column.slug]"
                                            class="metadata-value"/>
                                </span>
                            </div>
                        </div>
                    </div>
                </transition>

                <swiper 
                        role="list"
                        @slideChange="onSlideChange()"
                        ref="mySwiper"
                        :options="swiperOption"
                        id="tainacan-slide-container">
                    <swiper-slide
                            role="listitem"
                            :ref="'thumb-' + item.id"
                            :key="index"
                            v-for="(item, index) of items"
                            class="tainacan-slide-thumbnail">
                        <!-- Item thumbnail -->                        
                        <img
                                :alt="$i18n.get('label_thumbnail')"
                                v-if="item.thumbnail != undefined"
                                :src="item['thumbnail']['tainacan-medium'] ? item['thumbnail']['tainacan-medium'][0] : (item['thumbnail']['tainacan-medium'] ? item['thumbnail']['tainacan-medium'][0] : thumbPlaceholderPath)">
                    </swiper-slide>

                    <!-- Swiper buttons are hidden as they actually swipe from slide to slide -->
                    <div    
                            style="background-image: none;"
                            class="swiper-button-prev" 
                            slot="button-prev">
                        <span
                                v-tooltip="{
                                    content: $i18n.get('previous'),
                                    autoHide: true,
                                    placement: 'auto'
                                }"
                                class="icon is-large has-text-secondary">
                            <icon class="tainacan-icon tainacan-icon-48px tainacan-icon-previous"/>
                        </span> 
                    </div>
                    <div 
                            style="background-image: none;"
                            class="swiper-button-next" 
                            slot="button-next">
                        <span
                                v-tooltip="{
                                    content: $i18n.get('next'),
                                    autoHide: true,
                                    placement: 'auto'
                                }"
                                class="icon is-large has-text-secondary">
                            <icon class="tainacan-icon tainacan-icon-48px tainacan-icon-next"/>
                        </span> 
                    </div>
                </swiper>

            </div>

        </div>
    </div>
</template>

<script>
import 'swiper/dist/css/swiper.css';
import { swiper, swiperSlide } from 'vue-awesome-swiper';

export default {
    name: 'ViewModeSlides',
    components: {
        swiper,
        swiperSlide,
    },
    props: {
        collectionId: Number,
        displayedMetadata: Array,
        items: Array,
        isLoading: false,
        isFiltersMenuCompressed: Boolean
    },
    data () {
        return {
            thumbPlaceholderPath: tainacan_plugin.base_url + '/assets/images/placeholder_square.png',
            slideIndex: 0,
            goingRight: true,
            swiperOption: {
                mousewheel: true,
                keyboard: true,
                observer: true,
                preventInteractionOnTransition: true,
                allowClick: true,
                allowTouchMove: true, 
                slidesPerView: 18,
                slidesPerGroup: 1,
                centeredSlides: true,
                spaceBetween: 12,
                slideToClickedSlide: true,
                navigation: {
                    nextEl: '.swiper-button-next',
                    prevEl: '.swiper-button-prev'
                },
                breakpoints: {
                    320: { slidesPerView: 4 },
                    480: { slidesPerView: 6 },
                    640: { slidesPerView: 8 },
                    768: { slidesPerView: 10 },
                    1024: { slidesPerView: 14 },
                    1366: { slidesPerView: 16 },
                    1406: { slidesPerView: 18 },
                    1600: { slidesPerView: 20 }
                }
            },
        }
    },
    watch: {
        slideIndex:{
            handler(val, oldVal) { 
                if (this.slideIndex < 0) {
                    this.slideIndex = 0;
                } else {
                    // Handles direction information, used by animations
                    if (oldVal == undefined)
                        this.goingRight = undefined;    
                    else if (val < oldVal || (this.slideIndex == 0 && this.page == 1))
                        this.goingRight = false;
                    else    
                        this.goingRight = true;
                }
            },
            immediate: true
        } 
    },
    methods: {
        goToItemPage(item) {
            window.location.href = item.url;   
        },
        renderMetadata(itemMetadata, column) {
            let metadata = (itemMetadata != undefined && itemMetadata[column.slug] != undefined) ? itemMetadata[column.slug] : false;

            if (!metadata) {
                return '';
            } else {
                return metadata.value_as_html;
            }
        },
        onSlideChange() {
            if (this.$refs.mySwiper.swiper != undefined)
                this.slideIndex = this.$refs.mySwiper.swiper.activeIndex;
        }
    }
}
</script>

<style lang="scss">
    @import "../../admin/scss/view-mode-slides.scss";

    .tainacan-slides-container .tainacan-slide .metadata-title {
        padding: 0.6em 0.875em;
        margin-bottom: 0px;
    }
</style>