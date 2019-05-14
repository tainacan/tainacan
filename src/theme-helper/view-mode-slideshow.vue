<template>

    <div :class="{ 'hide-controls': hideControls }">
        <!-- CLOSE BUTTON -->
        <button
                v-tooltip="{
                    delay: {
                        show: 500,
                        hide: 300,
                    },
                    content: $i18n.get('close'),
                    autoHide: false,
                    placement: 'auto-start'
                }"  
                id="close-fullscren-button"
                :class="{ 'is-hidden-mobile': !isMetadataCompressed }"
                @click="closeSlideViewMode()">
            <span class="icon">
                <i class="tainacan-icon tainacan-icon-20px tainacan-icon-close"/>
            </span>
        </button>

        <!-- METADATA LIST -->
        <button
                v-tooltip="{
                    delay: {
                        show: 500,
                        hide: 300,
                    },
                    content: isMetadataCompressed ? $i18n.get('label_show_metadata') : $i18n.get('label_hide_metadata'),
                    autoHide: false,
                    placement: 'auto-start'
                }"  
                id="metadata-compress-button"
                @click="isMetadataCompressed = !isMetadataCompressed">
            <span class="icon">
                <i 
                        :class="{ 'tainacan-icon-arrowright' : isMetadataCompressed, 'tainacan-icon-arrowleft' : !isMetadataCompressed }"
                        class="tainacan-icon tainacan-icon-20px"/>
            </span>
        </button>

        <aside
                v-if="!isMetadataCompressed"
                class="metadata-menu tainacan-form">

            <div class="metadata-menu-header is-hidden-tablet">
                <h2>{{ item.title }}</h2>
                <button
                        id="close-metadata-button"
                        @click="isMetadataCompressed = true">
                    <span class="icon">
                        <i class="tainacan-icon tainacan-icon-20px tainacan-icon-close"/>
                    </span>
                </button>
                <hr>
            </div>

            <h3 class="has-text-white has-text-weight-semibold">{{ $i18n.get('metadata') }}</h3>
            
            <a
                    v-if="!isLoadingItem && Object.keys(item.metadata).length > 0"
                    class="collapse-all is-size-7"
                    @click="collapseAll = !collapseAll">
                {{ collapseAll ? $i18n.get('label_collapse_all') : $i18n.get('label_expand_all') }}
                <span class="icon">
                    <i 
                            :class="{ 'tainacan-icon-arrowdown' : collapseAll, 'tainacan-icon-arrowright' : !collapseAll}"
                            class="tainacan-icon tainacan-icon-20px"/>
                </span>
            </a>

            <span 
                    v-if="isLoadingItem"
                    style="width: 100%;"
                    class="icon is-large loading-icon">
                <div class="is-large control has-icons-right is-loading is-clearfix" />
            </span>

            <div
                    v-for="(metadatum, index) of item.metadata"
                    v-if="metadatum.value_as_html != undefined && metadatum.value_as_html != ''"
                    :key="index"
                    class="field">
                <b-collapse 
                        aria-id="metadata-collapse-for-slideshow"
                        :open="!collapseAll">
                    <label
                            class="label has-text-white"
                            slot="trigger"
                            slot-scope="props">
                        <span class="icon">
                            <i 
                                    :class="{ 'tainacan-icon-arrowdown' : props.open, 'tainacan-icon-arrowright' : !props.open}"
                                    class="has-text-secondary tainacan-icon tainacan-icon-20px"/>
                        </span>
                        <span 
                                v-tooltip="{
                                    delay: {
                                        show: 500,
                                        hide: 300,
                                    },
                                    content: metadatum.name,
                                    autoHide: false,
                                    placement: 'auto-start'
                                }"  
                                class="ellipsed-text">
                            {{ metadatum.name }}
                        </span>
                    </label>
                    <div class="content">
                        <p  
                            class="has-text-white"
                            v-html="metadatum.value_as_html"/>
                    </div>
                </b-collapse>

            </div>

            <br>
            <br>

        </aside>
        <div 
                :class="{ 'fullscreen-spaced-to-right': !isMetadataCompressed }"
                @keyup.left.prevent="slideIndex > 0 ? prevSlide() : null"
                @keyup.right.prevent="slideIndex < slideItems.length - 1 ? nextSlide() : null">
            <div class="table-wrapper">

                <!-- SLIDE MAIN VIEW-->
                <section 
                        @click.prevent.stop="onHideControls()"
                        class="tainacan-slide-main-view">
                    <button 
                            @click.stop.prevent="prevSlide()"
                            :style="{ visibility: (page > 1 && slideIndex <= 0) || slideIndex > 0 ? 'visible' : 'hidden' }"
                            class="slide-control-arrow arrow-left"
                            :aria-label="$i18n.get('previous')">
                        <span
                                v-tooltip="{
                                    content: $i18n.get('previous'),
                                    autoHide: true,
                                    placement: 'auto'
                                }"
                                class="icon is-large">
                            <icon class="tainacan-icon tainacan-icon-48px tainacan-icon-previous"/>
                        </span> 
                    </button>
                    <div     
                            class="slide-main-content">

                        <transition 
                                mode="out-in"
                                :name="goingRight ? 'slide-right' : 'slide-left'" >
                            <span 
                                    v-if="isLoadingItem"
                                    class="icon is-large loading-icon">
                                <div class="is-large control has-icons-right is-loading is-clearfix" />
                            </span>
                            <!-- Empty result placeholder -->
                            <section
                                    v-if="!isLoading && !isLoadingItem && items.length <= 0"
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
                            <div 
                                    v-if="!isLoadingItem && slideItems.length > 0 && (item.document != undefined && item.document != undefined && item.document != '')"
                                    v-html="item.document_as_html" />  
                            <div v-else>
                                <div class="empty-document">
                                    <p>{{ $i18n.get('label_document_empty') }}</p>
                                    <img 
                                            :alt="$i18n.get('label_document_empty')" 
                                            :src="thumbPlaceholderPath">
                                </div>
                            </div>
                        </transition>
                    </div>
                    <button 
                            @click.stop.prevent="nextSlide()"
                            :style="{ visibility: (slideIndex < slideItems.length - 1) || page < totalPages ? 'visible' : 'hidden' }"
                            class="slide-control-arrow arrow-right"
                            :aria-label="$i18n.get('next')">
                        <span
                                v-tooltip="{
                                    content: $i18n.get('next'),
                                    autoHide: true,
                                    placement: 'auto'
                                }"
                                class="icon is-large has-text-turoquoise5">
                            <icon class="tainacan-icon tainacan-icon-48px tainacan-icon-next"/>
                        </span>
                    </button>
                </section>


                <!-- SLIDE ITEMS LIST --> 
                <div class="tainacan-slides-list">
                    <section 
                            @click.prevent="onHideControls()"
                            v-if="slideItems[slideIndex] != undefined" 
                            class="slide-title-area">
                        <h1>{{ slideItems[slideIndex].title }}</h1>
                        <button 
                                :disabled="(slideIndex == slideItems.length - 1 && page == totalPages)"
                                class="play-button"
                                @click.stop.prevent="isPlaying = !isPlaying">
                            <span 
                                    v-tooltip="{
                                        content: isPlaying ? $i18n.get('label_pause_slide_transition') : $i18n.get('label_begin_slide_transition'),
                                        autoHide: true,
                                        placement: 'auto'
                                    }"
                                    class="icon">
                                <i 
                                        :class="{ 'tainacan-icon-pausefill' : isPlaying, 'tainacan-icon-playfill' : !isPlaying }"
                                        class="has-text-secondary tainacan-icon tainacan-icon-30px"/>
                            </span>
                            <circular-counter 
                                    v-if="isPlaying"
                                    :time="this.slideTimeout/1000" />
                        </button>
                    </section>
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
                                v-for="(item, index) of slideItems"
                                class="tainacan-slide-item"
                                :class="{'active-item': slideIndex == index}">
                            <img 
                                    :alt="$i18n.get('label_thumbnail') + ': ' + item.title"
                                    class="thumnail" 
                                    :src="item['thumbnail']['tainacan-small'] ? item['thumbnail']['tainacan-small'][0] : (item['thumbnail'].thumbnail ? item['thumbnail'].thumbnail[0] : thumbPlaceholderPath)">  
                            
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
                    <!-- List loading -->
                    <span 
                            v-if="isLoading"
                            :style="{ left: !goingRight ? '' : '25%', right: !goingRight ? '25%' : '' }"
                            class="icon loading-icon">
                        <div class="control has-icons-right is-loading is-clearfix" />
                    </span>
                    <!-- Extra buttons for sliding groups of slides -->
                    <button 
                            @click.prevent="prevGroupOfSlides()"
                            :style="{ visibility: (page > 1 && slideIndex <= 0) || slideIndex > 0 ? 'visible' : 'hidden' }"
                            class="slide-control-arrow slide-group-arrow arrow-left"
                            :aria-label="$i18n.get('label_previous_group_slides')">
                        <span
                                v-tooltip="{
                                    content: $i18n.get('label_previous_group_slides'),
                                    autoHide: true,
                                    placement: 'auto'
                                }"
                                class="icon is-medium has-text-white">
                            <icon class="tainacan-icon tainacan-icon-20px tainacan-icon-previous"/>
                        </span>
                    </button>
                    <button 
                            @click.prevent="nextGroupOfSlides()"
                            :style="{ visibility: (slideIndex < slideItems.length - 1) || page < totalPages ? 'visible' : 'hidden' }"
                            class="slide-control-arrow slide-group-arrow arrow-right"
                            :aria-label="$i18n.get('label_previous_group_slides')">
                        <span 
                                v-tooltip="{
                                    content: $i18n.get('label_previous_group_slides'),
                                    autoHide: true,
                                    placement: 'auto'
                                }"
                                class="icon is-medium has-text-white">
                            <icon class="tainacan-icon tainacan-icon-20px tainacan-icon-next"/>
                        </span>
                    </button>
                </div>
            </div> 
        </div>
    </div>
</template>

<script>
import { mapActions, mapGetters } from 'vuex';
import axios from '../js/axios/axios.js';
import 'swiper/dist/css/swiper.css';
import { swiper, swiperSlide } from 'vue-awesome-swiper';
import CircularCounter from '../admin/components/other/circular-counter.vue';
 
export default {
    name: 'ViewModeSlideshow',
    props: {
        collectionId: Number,
        displayedMetadata: Array,
        items: Array,
        isLoading: Boolean,
        totalItems: Number,
        hideControls: true,
        isSwiping: false
    },  
    components: {
        swiper,
        swiperSlide,
        CircularCounter
    },
    data () {
        return {
            slideItems: [],
            goingRight: true,
            isPlaying: false,
            slideTimeout: 5000, 
            intervalId: 0, 
            collapseAll: false,
            isLoadingItem: true,
            isMetadataCompressed: true,
            slideIndex: 0,
            minPage: 1,
            maxPage: 1,
            readjustedSlideIndex: 0,
            preloadedItem: {},
            swiperOption: {
                mousewheel: true,
                observer: true,
                keyboard: true,
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
        },
        page() {
            this.setMaxAndMinPages();
            return this.getPage();
        },
        totalPages() {
            return this.getTotalPages();
        }
    },
    watch: {
        items: {
            handler () {
                if (this.items.length > 0) {
                    let updatedSlideIndex = this.slideIndex != undefined ? (this.slideIndex + 0) : 0;

                    // Loops through new items list. Depending on direction, goes from start or end of list.
                    for (let newItem of ((this.goingRight) ? this.items : JSON.parse(JSON.stringify(this.items)).reverse())) {
                        let existingItemIndex = this.slideItems.findIndex(anItem => anItem.id == newItem.id);
                        if (existingItemIndex < 0) {
                            if ( this.goingRight || this.slideIndex == undefined) {
                                this.slideItems.push(newItem);
                            } else {
                                this.slideItems.unshift(newItem);
                                updatedSlideIndex++; 
                            }
                        } else {
                            this.$set(this.slideItems, existingItemIndex, newItem);
                        }
                    }   

                    // Checks if list got too big. In this case we remove items from a page that is far from index
                    if (this.slideItems.length > 36) {
                        if (this.goingRight) {
                            this.slideItems.splice(0, this.getItemsPerPage());
                            this.minPage++;
                            updatedSlideIndex = this.slideItems.length - 1 - this.items.length;
                  
                        } else {
                            this.slideItems.splice(-this.getItemsPerPage());
                            this.maxPage--;
                            updatedSlideIndex = this.getItemsPerPage();
                        }
                    }
                        
                    if (this.goingRight == undefined && updatedSlideIndex == 0) {
                        this.slideIndex = -1; // Used to force reload of index when page has not loaded slideItems yet
                    } else {   
                        if (this.$refs.mySwiper != undefined && this.$refs.mySwiper.swiper != undefined) {
                            // if (updatedSlideIndex != undefined && this.$refs.mySwiper.swiper.slides[updatedSlideIndex] != undefined) 
                                // this.$refs.mySwiper.swiper.slides[updatedSlideIndex].click();
                            
                            this.$refs.mySwiper.swiper.activeIndex = this.slideIndex;
                            this.slideIndex = updatedSlideIndex;
                                                
                            // console.log("Após: " + this.slideIndex + " " + this.$refs.mySwiper.swiper.activeIndex);
                        }
                    
                    }

                }
            },
            immediate: true
        },
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

                    // Handles loading main item info, displayed in the middle
                    this.loadCurrentItem();
                    
                    // Handles requesting new page of items, either to left or right
                    if (this.$refs.mySwiper != undefined && this.$refs.mySwiper.swiper != undefined) {

                        if (this.slideIndex != this.$refs.mySwiper.swiper.activeIndex) {
                            if (this.slideIndex != undefined && this.$refs.mySwiper.swiper.slides[this.slideIndex] != undefined) 
                                this.$refs.mySwiper.swiper.slides[this.slideIndex].click();

                            this.readjustedSlideIndex = this.slideIndex;
                            this.$refs.mySwiper.swiper.activeIndex = this.slideIndex + 0;

                        } else if (this.slideItems.length > 0) {
                            if (this.$refs.mySwiper.swiper.activeIndex == this.slideItems.length - 1 && this.page < this.totalPages) { 
                                oldVal == undefined ? this.$eventBusSearch.setPage(this.page + 1) : this.$eventBusSearch.setPage(this.maxPage + 1);
                            } else if (this.$refs.mySwiper.swiper.activeIndex == 0 && this.page > 1 && this.slideItems.length < this.totalItems) {
                                oldVal == undefined ? this.$eventBusSearch.setPage(this.page - 1) : this.$eventBusSearch.setPage(this.minPage - 1);
                            }
                        }

                        // Handles pausing auto play when reaches the end of the list.
                        if (this.$refs.mySwiper.swiper.activeIndex == this.slideItems.length - 1 && this.page == this.totalPages)
                            this.isPlaying = false;
                    }
                }
            },
            immediate: true
        }, 
        isPlaying() {
            if (this.isPlaying) {
                this.intervalId = setInterval(() => {
                    if (this.$refs.mySwiper.swiper != undefined)
                        this.$refs.mySwiper.swiper.navigation.nextEl.click();
                }, this.slideTimeout);
            } else {
                clearInterval(this.intervalId);
            }
        }
    },
    methods: {
        ...mapActions('item', [
            'fetchItem',
            'replaceItem'
        ]),
        ...mapGetters('item', [
            'getItem'
        ]),
         ...mapGetters('search', [
            'getTotalPages',
            'getPage',
            'getItemsPerPage'
        ]),
        setMaxAndMinPages () {
            this.minPage = JSON.parse(JSON.stringify(this.getPage() < this.minPage ? this.getPage() : this.minPage));
            this.maxPage = JSON.parse(JSON.stringify(this.getPage() > this.maxPage ? this.getPage() : this.maxPage));
        },
        onHideControls() {
            if (this.isSwiping == undefined || this.isSwiping == false)
                this.hideControls = !this.hideControls;
        },
        onSwipeFiltersMenu($event) {
            this.isSwiping = true;
            if ($event.offsetDirection == 2) {
                this.nextSlide();
            } else if ($event.offsetDirection == 4) {
                this.prevSlide();
            }
            setTimeout(() => {
                this.isSwiping = false;
            }, 500);
        },
        onSlideChange() {

            if (this.$refs.mySwiper.swiper != undefined)
                this.slideIndex = this.$refs.mySwiper.swiper.activeIndex;

            this.$nextTick(() => {
                if (this.readjustedSlideIndex != undefined) {

                    if (this.slideIndex != undefined && this.$refs.mySwiper.swiper.slides[this.readjustedSlideIndex] != undefined) 
                        this.$refs.mySwiper.swiper.slides[this.readjustedSlideIndex].click();

                    this.readjustedSlideIndex = undefined;
                }
            });
     
        },
        nextSlide() { 
            if (this.$refs.mySwiper.swiper != undefined)
                this.$refs.mySwiper.swiper.slideNext();    
        },
        prevSlide() {
            if (this.$refs.mySwiper.swiper != undefined)
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
            
            if (this.$refs.mySwiper.swiper != undefined) {
                if (this.slideIndex != undefined && this.$refs.mySwiper.swiper.slides[this.slideIndex + amountToSkip] != undefined)
                    this.$refs.mySwiper.swiper.slideTo(this.slideIndex + amountToSkip);  
                else      
                    this.$refs.mySwiper.swiper.slideTo(this.$refs.mySwiper.swiper.slides.length - 1)                  
            }
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
            
            if (this.$refs.mySwiper.swiper != undefined) {
                if (this.slideIndex != undefined && this.$refs.mySwiper.swiper.slides[this.slideIndex - amountToSkip] != undefined)
                    this.$refs.mySwiper.swiper.slideTo(this.slideIndex - amountToSkip); 
                else
                    this.$refs.mySwiper.swiper.slideTo(0);
            } 
        },
        loadCurrentItem() {
            if ((this.slideItems && this.slideItems[this.slideIndex] && this.slideItems[this.slideIndex].id != undefined)) {

                this.isLoadingItem = true;

                // Checks if item is preloaded
                if (this.preloadedItem.id != undefined && this.preloadedItem.id == this.slideItems[this.slideIndex].id) {
                    this.replaceItem(this.preloadedItem);
                    this.$nextTick(() => this.isLoadingItem = false);
                } else {
                    // Loads current item
                    this.fetchItem({ itemId: this.slideItems[this.slideIndex].id, contextEdit: true })
                        .then(() => {
                            this.isLoadingItem = false;
                        })
                        .catch(() => {
                            this.isLoadingItem = false;
                        });
                }

                // Loads next item, just in case
                let nextIndex = (this.goingRight || this.goingRight == undefined) ? this.slideIndex + 1 : this.slideIndex - 1;
                if (this.slideItems[nextIndex] != undefined && this.slideItems[nextIndex].id != undefined) {
                    axios.tainacan.get('/items/' + this.slideItems[nextIndex].id)
                        .then(res => {
                            this.preloadedItem = res.data;
                        })
                        .catch(error => {
                            this.$console.log( error );
                        });
                }
            }
        },
        renderMetadata(itemMetadata, column) {

            let metadata = itemMetadata[column.slug] != undefined ? itemMetadata[column.slug] : false;

            if (!metadata) {
                return '';
            } else {
                return metadata.value_as_html;
            }
        },
        closeSlideViewMode() {
            this.$parent.onChangeViewMode(this.$parent.defaultViewMode);
        }
    },
    mounted() {
        this.minPage = this.page;
        this.maxPage = this.page;

        if (this.$refs.mySwiper.swiper != undefined) {
            this.$refs.mySwiper.swiper.initialSlide = this.slideIndex;
        }

        // Adds clipped class to root html
        document.documentElement.scrollTo(0,0);
        document.documentElement.classList.add('is-clipped');
    },
    beforeDestroy() {
        clearInterval(this.intervalId);
        if (this.$refs.mySwiper.swiper)
            this.$refs.mySwiper.swiper.destroy();

        // Remove clipped class from root html
        document.documentElement.classList.remove('is-clipped');
    }
}
</script>

<style  lang="scss">
    $turquoise1: #e6f6f8;
    $turquoise2: #d1e6e6;
    $turquoise5: #298596;
    $tainacan-input-color: #1d1d1d;
    $gray1: #f2f2f2; 
    $gray2: #e5e5e5;
    $gray3: #dcdcdc;
    $gray4: #555758;
    $gray5: #454647; 
    $page-small-side-padding: 25px;
    $page-side-padding: 4.166666667%; 

    @import "../../src/admin/scss/_view-mode-slideshow.scss";

    .is-fullscreen .table-wrapper {
        overflow: hidden !important;
        display: flex;
        flex-wrap: nowrap;
        flex-direction: column;
        justify-content: space-between;
        width: 100%;
        height: 100%;
        height: 100vh;
        width: 100vw;
    }

</style>


