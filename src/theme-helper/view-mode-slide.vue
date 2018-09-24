<template>

    <div :class="{ 'hide-controls': hideControls }">
        <!-- CLOSE BUTTON -->
        <button
                v-tooltip="{
                    content: $i18n.get('close'),
                    autoHide: false,
                    placement: 'auto-start'
                }"  
                id="close-fullscren-button"
                @click="closeSlideViewMode()">
            <b-icon icon="close" />
        </button>

        <!-- METADATA LIST -->
        <button
                v-tooltip="{
                    content: isMetadataCompressed ? $i18n.get('label_show_metadata') : $i18n.get('label_hide_metadata'),
                    autoHide: false,
                    placement: 'auto-start'
                }"  
                class="is-hidden-mobile"
                id="metadata-compress-button"
                @click="isMetadataCompressed = !isMetadataCompressed">
            <b-icon :icon="isMetadataCompressed ? 'menu-right' : 'menu-left'" />
        </button>

        <aside
                v-if="!isMetadataCompressed"
                class="metadata-menu tainacan-form is-hidden-mobile">

            <h3 class="has-text-white has-text-weight-semibold">{{ $i18n.get('metadata') }}</h3>
            
            <a
                    v-if="!isLoadingItem && Object.keys(item.metadata).length > 0"
                    class="collapse-all is-size-7"
                    @click="collapseAll = !collapseAll">
                {{ collapseAll ? $i18n.get('label_collapse_all') : $i18n.get('label_expand_all') }}
                <b-icon
                        type="is-secondary"
                        :icon=" collapseAll ? 'menu-down' : 'menu-right'" />
            </a>

            <span 
                    v-if="isLoadingItem"
                    style="width: 100%;"
                    class="icon is-large loading-icon">
                <div class="is-large control has-icons-right is-loading is-clearfix" />
            </span>

            <div
                    v-for="(metadatum, index) of item.metadata"
                    :key="index"
                    class="field">
                <b-collapse :open="!collapseAll">
                    <label
                            class="label has-text-white"
                            slot="trigger"
                            slot-scope="props">
                        <b-icon
                                type="is-secondary"
                                :icon="props.open ? 'menu-down' : 'menu-right'"
                        />
                        {{ metadatum.name }}
                    </label>
                    <div class="content">
                        <p  
                            class="has-text-white"
                            v-html="metadatum.value_as_html != '' ? metadatum.value_as_html : `<span class='has-text-gray is-italic'>` + $i18n.get('label_value_not_informed') + `</span>`"/>
                    </div>
                </b-collapse>

            </div>

            <br>
            <br>

        </aside>
        <div 
                :class="{ 'spaced-to-right': !isMetadataCompressed }"
                @keyup.left.prevent="slideIndex > 0 ? prevSlide() : null"
                @keyup.right.prevent="slideIndex < slideItems.length - 1 ? nextSlide() : null">
            <div class="table-wrapper">

                <!-- SLIDE MAIN VIEW-->
                <section class="tainacan-slide-main-view">
                    <button 
                            @click.prevent="prevSlide()"
                            :style="{ visibility: slideIndex > 0 ? 'visible' : 'visible' }"
                            class="slide-control-arrow arrow-left">
                        <span class="icon is-large">
                            <icon class="mdi mdi-48px mdi-chevron-left"/>
                        </span> 
                    </button>
                    <div 
                            @click.prevent="hideControls = !hideControls"
                            class="slide-main-content">

                        <transition 
                                mode="out-in"
                                :name="goingRight ? 'slide-right' : 'slide-left'" >
                            <span 
                                    v-if="isLoadingItem || isLoadingItem"
                                    class="icon is-large loading-icon">
                                <div class="is-large control has-icons-right is-loading is-clearfix" />
                            </span>
                            <!-- Empty result placeholder -->
                            <section
                                    v-if="!isLoading && !isLoadingItem && items.length <= 0"
                                    class="section">
                                <div class="content has-text-gray4 has-text-centered">
                                    <p>
                                        <b-icon
                                                icon="file-multiple"
                                                size="is-large"/>
                                    </p>
                                    <p>{{ $i18n.get('info_no_item_found') }}</p>
                                </div>
                            </section>
                            <div 
                                    v-if="!isLoadingItem && slideItems.length > 0 && (item.document != undefined && item.document != null)"
                                    v-html="item.document_as_html" />  
                            <div v-else>
                                <img 
                                        :alt="$i18n.get('label_document_empty')" 
                                        :src="thumbPlaceholderPath">
                            </div>
                        </transition>
                    </div>
                    <button 
                            @click.prevent="nextSlide()"
                            :style="{ visibility: slideIndex < slideItems.length - 1 ? 'visible' : 'visible' }"
                            class="slide-control-arrow arrow-right">
                        <span class="icon is-large has-text-turoquoise5">
                            <icon class="mdi mdi-48px mdi-chevron-right"/>
                        </span>
                    </button>
                </section>
                <section 
                        v-if="slideItems[slideIndex] != undefined"
                        class="slide-title-area">
                    <h1>{{ slideItems[slideIndex].title }}</h1>
                    <button 
                            class="play-button"
                            @click="isPlaying = !isPlaying">
                        <b-icon
                                type="is-secondary" 
                                size="is-medium"
                                :icon="isPlaying ? 'pause-circle' : 'play-circle' "/>
                    </button>
                </section>

                <!-- SLIDE ITEMS LIST --> 
                <div class="tainacan-slides-list">
                    <swiper 
                            @slideChange="onSlideChange()"
                            ref="mySwiper"
                            :options="swiperOption"
                            id="tainacan-slide-container">
                        <swiper-slide 
                                :ref="'thumb-' + item.id"
                                @click="slideIndex = index;"
                                :key="index"
                                v-for="(item, index) of slideItems"
                                class="tainacan-slide-item"
                                :class="{'active-item': slideIndex == index}">
                            <img 
                                    class="thumnail" 
                                    :src="item['thumbnail']['tainacan_small'] ? item['thumbnail']['tainacan_small'] : (item['thumbnail'].thumb ? item['thumbnail'].thumb : thumbPlaceholderPath)">  
                            
                        </swiper-slide>
                        <!-- Swiper buttons are hidden as they actually swipe from slide to slide -->
                        <div    
                                style="visibility: hidden; display: none;"
                                class="swiper-button-prev" 
                                slot="button-prev"/>
                        <div 
                                style="visibility: hidden; display: none;"
                                class="swiper-button-next" 
                                slot="button-next"/>
                    </swiper>
                    <!-- Extra buttons for sliding groups of slides -->
                    <button 
                            @click.prevent="prevGroupOfSlides()"
                            :style="{ visibility: slideIndex >= 0 ? 'visible' : 'visible' }"
                            class="slide-control-arrow slide-group-arrow arrow-left">
                        <span class="icon is-medium has-text-white">
                            <icon class="mdi mdi-24px mdi-chevron-left"/>
                        </span>
                    </button>
                    <button 
                            @click.prevent="nextGroupOfSlides()"
                            :style="{ visibility: slideIndex < slideItems.length - 1 ? 'visible' : 'visible' }"
                            class="slide-control-arrow slide-group-arrow arrow-right">
                        <span class="icon is-medium has-text-white">
                            <icon class="mdi mdi-24px mdi-chevron-right"/>
                        </span>
                    </button>
                </div>
            </div> 
        </div>
    </div>
</template>

<script>
import {mapActions, mapGetters} from 'vuex';
import 'swiper/dist/css/swiper.css';
import { swiper, swiperSlide } from 'vue-awesome-swiper';

export default {
    name: 'ViewModeSlide',
    props: {
        collectionId: Number,
        displayedMetadata: Array,
        items: Array,
        totalItems: Number,
        hideControls: true,
    },  
    components: {
        swiper,
        swiperSlide
    },
    data () {
        return {
            slideItems: [],
            isLoading: false,
            goingRight: true,
            isPlaying: false,
            slideTimeout: 5000, 
            intervalId: 0, 
            collapseAll: false,
            isLoadingItem: false,
            isMetadataCompressed: true,
            slideIndex: 0,
            swiperOption: {
                preventInteractionOnTransition: true,
                normalizeSlideIndex: true,
                allowClick: true,
                allowTouchMove: true,
                slidesPerView: 18,
                slidesPerGroup: 1,
                centeredSlides: true,
                watchSlidesVisibility: true,
                spaceBetween: 12,
                slideToClickedSlide: true,
                navigation: {
                    nextEl: '.swiper-button-next',
                    prevEl: '.swiper-button-prev'
                },
                breakpoints: {
                    320: { slidesPerView: 4 },
                    480: { slidesPerView: 5 },
                    640: { slidesPerView: 6 },
                    768: { slidesPerView: 8 },
                    1024: { slidesPerView: 10 },
                    1366: { slidesPerView: 12 },
                    1406: { slidesPerView: 14 },
                    1600: { slidesPerView: 16 }
                }
            },
            thumbPlaceholderPath: tainacan_plugin.base_url + '/admin/images/placeholder_square.png'
        }
    },
    computed: {
        item() {
            return this.getItem();
        }
    },
    watch: {
        items: {
            handler () {
                let updatedSlideIndex = this.slideIndex;

                for (let newItem of (this.goingRight ? this.items : JSON.parse(JSON.stringify(this.items)).reverse())) {
                    let existingItemIndex = this.slideItems.findIndex(anItem => anItem.id == newItem.id);
                    if (existingItemIndex < 0) {
                        if (this.goingRight) {
                            this.slideItems.push(newItem);
                        } else {
                            this.slideItems.unshift(newItem);
                            updatedSlideIndex++;
                        }
                    } else {
                        this.$set(this.slideItems, existingItemIndex, newItem);
                    }
                    this.$nextTick(() => {
                        this.$refs.mySwiper.swiper.update();
                        this.$refs.mySwiper.swiper.slideTo(updatedSlideIndex);
                    });
                }       
            },
            immediate: true
        },
        slideIndex:{
            handler(val, oldVal) { 
                if (val >= oldVal)
                    this.goingRight = true;
                else    
                    this.goingRight = false;
                
                if (this.slideItems && this.slideItems[this.slideIndex] && this.slideItems[this.slideIndex].id != undefined) {
                    
                    this.isLoadingItem = true;

                    this.fetchItem(this.slideItems[this.slideIndex].id)
                        .then(() => {
                            this.isLoadingItem = false;
                        })
                        .catch(() => {
                            this.isLoadingItem = false;
                        });
                }

                if (this.slideIndex == this.slideItems.length - 1 && this.slideItems.length < this.totalItems)
                    this.$eventBusSearch.setPage(this.getPage() + 1);
                else if (this.slideIndex == 0 && this.getPage() > 1 && this.slideItems.length < this.totalItems)
                    this.$eventBusSearch.setPage(this.getPage() - 1);
            },
            immediate: true
        }, 
        isPlaying() {
            if (this.isPlaying) {
                this.intervalId = setInterval(() => {
                    this.$refs.mySwiper.swiper.navigation.nextEl.click();
                }, this.slideTimeout);
            } else {
                clearInterval(this.intervalId);
            }
        }
    },
    methods: {
        ...mapActions('item', [
            'fetchItem'
        ]),
        ...mapGetters('item', [
            'getItem'
        ]),
         ...mapGetters('search', [
            'getTotalItems',
            'getPage'
        ]),
        onSlideChange() {
            this.slideIndex = this.$refs.mySwiper.swiper.activeIndex;
        },
        nextSlide() { 
            this.$refs.mySwiper.swiper.slideNext();    
        },
        prevSlide() {
            this.$refs.mySwiper.swiper.slidePrev();
        },
        nextGroupOfSlides() { 
            let screenWidth = (window.innerWidth || document.documentElement.clientWidth || document.body.clientWidth);
            let amountToSkip = 0;

            if (screenWidth <= 480) amountToSkip = 1;
            else if (screenWidth > 480 && screenWidth <= 640) amountToSkip = 2;
            else if (screenWidth > 640 && screenWidth <= 768) amountToSkip = 4;
            else if (screenWidth > 768 && screenWidth <= 1366) amountToSkip = 5;
            else if (screenWidth > 1366 && screenWidth <= 1600) amountToSkip = 6;
            else if (screenWidth > 1600) amountToSkip = 7;
            
            this.$refs.mySwiper.swiper.slideTo(this.slideIndex + amountToSkip);    
        },
        prevGroupOfSlides() {
             let screenWidth = (window.innerWidth || document.documentElement.clientWidth || document.body.clientWidth);
            let amountToSkip = 0;

            if (screenWidth <= 480) amountToSkip = 1;
            else if (screenWidth > 480 && screenWidth <= 640) amountToSkip = 2;
            else if (screenWidth > 640 && screenWidth <= 768) amountToSkip = 4;
            else if (screenWidth > 768 && screenWidth <= 1366) amountToSkip = 5;
            else if (screenWidth > 1366 && screenWidth <= 1600) amountToSkip = 6;
            else if (screenWidth > 1600) amountToSkip = 7;
            
            this.$refs.mySwiper.swiper.slideTo(this.slideIndex - amountToSkip);  
        },
        renderMetadata(itemMetadata, column) {

            let metadata = itemMetadata[column.slug] != undefined ? itemMetadata[column.slug] : false;

            if (!metadata) {
                return '';
            } else if (metadata.date_i18n) {
                return metadata.date_i18n;
            } else {
                return metadata.value_as_html;
            }
        },
        closeSlideViewMode() {
            this.$parent.onChangeViewMode(this.$parent.defaultViewMode);
        }
    },
    mounted() {
        this.$refs.mySwiper.swiper.initialSlide = this.slideIndex;
    },
    beforeDestroy() {
        clearInterval(this.intervalId);
    }
}
</script>

<style  lang="scss" scoped>
    $turquoise1: #e6f6f8;
    $turquoise2: #d1e6e6;
    $turquoise5: #298596;
    $tainacan-input-color: #1d1d1d;
    $gray1: #f2f2f2; 
    $gray2: #e5e5e5;
    $gray3: #dcdcdc;
    $gray4: #898d8f;
    $gray5: #454647; 
    $page-small-side-padding: 25px;
    $page-side-padding: 4.166666667%; 

    @import "../../src/admin/scss/_view-mode-slide.scss";

    .table-wrapper {
        overflow-x: hidden !important;
    }

</style>


