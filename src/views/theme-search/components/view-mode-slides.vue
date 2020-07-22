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
                        
                        <div class="item-heading">
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
                                        v-html="(item.title != undefined && item.title != '') ? item.title : `<span class='has-text-gray3 is-italic'>` + $i18n.get('label_value_not_informed') + `</span>`" />
                            </div>
                        </div>

                        <div class="item-area">

                            <!-- Item thumbnail -->
                            <div 
                                    v-if="item['thumbnail']['full'] && item['thumbnail']['full'][0]"
                                    class="tainacan-slide-item">
                                <img
                                        :alt="$i18n.get('label_thumbnail')"
                                        v-if="item.thumbnail != undefined"
                                        :src="item['thumbnail']['full'] ? item['thumbnail']['full'][0] : (item['thumbnail'].full ? item['thumbnail'].full[0] : '')">
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
                    </div>
                </transition>

                <div 
                        v-tooltip="{
                                content: $i18n.get('label_next_page'),
                                autoHide: true,
                                placement: 'auto'
                            }"
                        class="swiper-button-prev swiper-button-page" 
                        :class="{ 'swiper-button-disabled': page <= 1 }"
                        style="background-image: none;"
                        @click="previousPage()">
                    <span class="icon is-large has-text-secondary">
                        <i class="tainacan-icon tainacan-icon-48px tainacan-icon-previous"/>
                        <i class="tainacan-icon tainacan-icon-48px tainacan-icon-previous"/>
                    </span> 
                </div>

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
                            <i class="tainacan-icon tainacan-icon-48px tainacan-icon-previous"/>
                        </span> 
                    </div>
                    <div 
                            style="background-image: none;"
                            class="swiper-button-next" 
                            slot="button-next">
                        <span
                                v-tooltip="{
                                    content: $i18n.get('label_next_page'),
                                    autoHide: true,
                                    placement: 'auto'
                                }"
                                class="icon is-large has-text-secondary">
                            <i class="tainacan-icon tainacan-icon-48px tainacan-icon-next"/>
                        </span> 
                    </div>
                </swiper>

                <div 
                        v-tooltip="{
                                content: $i18n.get('label_next_page'),
                                autoHide: true,
                                placement: 'auto'
                            }"
                        class="swiper-button-next swiper-button-page"
                        :class="{ 'swiper-button-disabled': page >= totalPages }"
                        style="background-image: none;" 
                        @click="nextPage()">
                    <span class="icon is-large has-text-secondary">
                        <i class="tainacan-icon tainacan-icon-48px tainacan-icon-next"/>
                        <i class="tainacan-icon tainacan-icon-48px tainacan-icon-next"/>
                    </span> 
                </div>

            </div>

        </div>
    </div>
</template>

<script>
import 'swiper/dist/css/swiper.css';
import { mapGetters } from 'vuex';
import { swiper, swiperSlide } from 'vue-awesome-swiper';
import { viewModesMixin } from '../js/view-modes-mixin.js';

export default {
    name: 'ViewModeSlides',
    mixins: [
        viewModesMixin
    ],
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
    computed: {
        page() {
            return this.getPage();
        },
        totalPages() {
            return this.getTotalPages();
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
         ...mapGetters('search', [
            'getTotalPages',
            'getPage',
            'getItemsPerPage'
        ]),
        goToItemPage(item) {
            window.location.href = item.url;   
        },
        onSlideChange() {
            if (this.$refs.mySwiper.swiper != undefined)
                this.slideIndex = this.$refs.mySwiper.swiper.activeIndex;
        },
        nextPage() {
            if (this.page < this.totalPages)
                this.$eventBusSearch.setPage(this.page + 1)
        },
        previousPage() {
            if (this.page > 1)
                this.$eventBusSearch.setPage(this.page + 1)
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