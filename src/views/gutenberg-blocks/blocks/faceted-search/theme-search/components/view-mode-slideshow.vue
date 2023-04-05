<template>
    <div 
            :class="{ 
                'hide-controls': hideControls
            }">

        <!-- HELP BUTTON -->
        <button
                v-tooltip="{
                    delay: {
                        shown: 500,
                        hide: 300,
                    },
                    content: $i18n.get('label_slides_help'),
                    autoHide: false,
                    placement: 'auto-start',
                    popperClass: ['tainacan-tooltip', 'tooltip']
                }"  
                id="slides-help-button"
                class="is-hidden-mobile"
                @click="openSlidesHelpModal">
            <span class="icon">
                <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-help"/>
            </span>
        </button>

        <!-- METADATA BUTTON -->
        <button
                v-tooltip="{
                    delay: {
                        shown: 500,
                        hide: 300,
                    },
                    content: isMetadataCompressed ? $i18n.get('label_show_metadata') : $i18n.get('label_hide_metadata'),
                    autoHide: false,
                    placement: 'auto-start',
                    popperClass: ['tainacan-tooltip', 'tooltip']
                }"  
                id="metedata-panel-button"
                :class="{ 'is-hidden-mobile': !isMetadataCompressed }"
                @click="isMetadataCompressed = !isMetadataCompressed">
            <span class="icon">
                <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-metadata"/>
            </span>
        </button>

        <!-- ITEM PAGE BUTTON -->
        <a
                v-tooltip="{
                    delay: {
                        shown: 500,
                        hide: 300,
                    },
                    content: $i18n.get('label_item_page'),
                    autoHide: false,
                    placement: 'auto-start',
                    popperClass: ['tainacan-tooltip', 'tooltip']
                }"  
                id="item-page-button"
                v-if="slideItems && swiper && swiper.activeIndex != undefined && slideItems[swiper.activeIndex]"
                :class="{ 'is-hidden-mobile': !isMetadataCompressed }"
                :href="getItemLink(slideItems[swiper.activeIndex].url, swiper.activeIndex)">
            <span class="icon">
                <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-see"/>
            </span>
        </a>

        <!-- CLOSE BUTTON -->
        <button
                v-tooltip="{
                    delay: {
                        shown: 500,
                        hide: 300,
                    },
                    content: $i18n.get('close'),
                    autoHide: false,
                    placement: 'auto-start',
                    popperClass: ['tainacan-tooltip', 'tooltip']
                }"  
                id="close-fullscren-button"
                :class="{ 'is-hidden-mobile': !isMetadataCompressed }"
                @click="closeSlideViewMode()">
            <span class="icon">
                <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-close"/>
            </span>
        </button>

        <!-- METADATA LIST -->
        <button
                v-tooltip="{
                    delay: {
                        shown: 500,
                        hide: 300,
                    },
                    content: isMetadataCompressed ? $i18n.get('label_show_metadata') : $i18n.get('label_hide_metadata'),
                    autoHide: false,
                    placement: 'auto-start',
                    popperClass: ['tainacan-tooltip', 'tooltip']
                }"  
                id="metadata-compress-button"
                @click="isMetadataCompressed = !isMetadataCompressed">
            <span class="icon">
                <i 
                        :class="{ 'tainacan-icon-arrowleft' : isMetadataCompressed, 'tainacan-icon-arrowright' : !isMetadataCompressed }"
                        class="tainacan-icon tainacan-icon-1-25em"/>
            </span>
        </button>

        <div 
                :class="{ 'fullscreen-spaced-to-left': !isMetadataCompressed }"
                @keyup.left.prevent="swiper.activeIndex > 0 ? prevSlide() : null"
                @keyup.right.prevent="swiper.activeIndex < slideItems.length - 1 ? nextSlide() : null">
            <div class="table-wrapper">

                <!-- SLIDE MAIN VIEW-->
                <section 
                        @click.prevent.stop="onHideControls()"
                        class="tainacan-slide-main-view">
                    <button 
                            @click.stop.prevent="prevSlide()"
                            :style="{ visibility: (page > 1 && swiper.activeIndex <= 0) || swiper.activeIndex > 0 ? 'visible' : 'hidden' }"
                            class="slide-control-arrow arrow-left"
                            :aria-label="$i18n.get('previous')">
                        <span
                                v-tooltip="{
                                    content: $i18n.get('previous'),
                                    autoHide: true,
                                    placement: 'auto',
                                    popperClass: ['tainacan-tooltip', 'tooltip']
                                }"
                                class="icon is-large">
                            <i class="tainacan-icon tainacan-icon-48px tainacan-icon-previous"/>
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

                            <!-- JS-side hook for extra content -->
                            <div 
                                    v-if="hasBeforeHook()"
                                    class="faceted-search-hook faceted-search-hook-item-before"
                                    v-html="getBeforeHook(item)" />

                            <div 
                                    v-if="!isLoadingItem && slideItems.length > 0 && (item.document != undefined && item.document != undefined && item.document != '')"
                                    v-html="item.document_as_html" />  
                            <div v-else>
                                <div class="empty-document">
                                    <p>{{ $i18n.get('label_document_empty') }}</p>
                                    <img 
                                            :alt="$i18n.get('label_document_empty')" 
                                            :src="$thumbHelper.getEmptyThumbnailPlaceholder(item.document_mimetype)">
                                </div>
                            </div>

                            <!-- JS-side hook for extra content -->
                            <div 
                                    v-if="hasAfterHook()"
                                    class="faceted-search-hook faceted-search-hook-item-after"
                                    v-html="getAfterHook(item)" />
                        </transition>
                    </div>
                    <button 
                            @click.stop.prevent="nextSlide()"
                            :style="{ visibility: (swiper.activeIndex < slideItems.length - 1) || page < totalPages ? 'visible' : 'hidden' }"
                            class="slide-control-arrow arrow-right"
                            :aria-label="$i18n.get('next')">
                        <span
                                v-tooltip="{
                                    content: $i18n.get('next'),
                                    autoHide: true,
                                    placement: 'auto',
                                    popperClass: ['tainacan-tooltip', 'tooltip']
                                }"
                                class="icon is-large has-text-turoquoise5">
                            <i class="tainacan-icon tainacan-icon-48px tainacan-icon-next"/>
                        </span>
                    </button>
                </section>

                <!-- SLIDE ITEMS LIST --> 
                <div class="tainacan-slides-list">
                    <section 
                            v-if="slideItems[swiper.activeIndex]"
                            @click.prevent="onHideControls()"
                            class="slide-title-area">
                        <h1 v-html="slideItems[swiper.activeIndex].title" />
                        <button 
                                :disabled="(swiper.activeIndex == slideItems.length - 1 && page == totalPages)"
                                class="play-button"
                                @click.stop.prevent="isPlaying = !isPlaying">
                            <span 
                                    v-tooltip="{
                                        content: isPlaying ? $i18n.get('label_pause_slide_transition') : $i18n.get('label_begin_slide_transition'),
                                        autoHide: true,
                                        placement: 'auto',
                                        popperClass: ['tainacan-tooltip', 'tooltip']
                                    }"
                                    class="icon">
                                <i 
                                        :class="{ 'tainacan-icon-pausefill' : isPlaying, 'tainacan-icon-playfill' : !isPlaying }"
                                        class="has-text-secondary tainacan-icon tainacan-icon-30px"/>
                            </span>
                            <circular-counter 
                                    v-if="isPlaying"
                                    :time="slideTimeout/1000" />
                        </button>
                    </section>

                    <!-- The Swiper slider itself -->
                    <div   
                            id="tainacan-slide-container"
                            class="swiper">
                        <div 
                                role="list"
                                class="swiper-wrapper" />
                    </div>

                    <!-- List loading -->
                    <span 
                            v-if="isLoading"
                            :style="{ left: !goingRight ? '' : '25%', right: !goingRight ? '25%' : '' }"
                            class="icon loading-icon">
                        <div class="control has-icons-right is-loading is-clearfix" />
                    </span>
                </div>
            </div> 
        </div>

        <aside
                v-if="!isMetadataCompressed"
                class="metadata-menu tainacan-form">

            <div class="metadata-menu-header is-hidden-tablet">
                <h2>{{ item.title }}</h2>
                <button
                        id="close-metadata-button"
                        @click="isMetadataCompressed = true">
                    <span class="icon">
                        <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-close"/>
                    </span>
                </button>
                <hr>
            </div>

            <h3 class="has-text-white has-text-weight-semibold">{{ $i18n.get('metadata') }}</h3>
            
            <a
                    v-if="!isLoadingItem && Object.keys(item.metadata).length > 0"
                    style="font-size: 0.75em;"
                    class="collapse-all"
                    @click="collapseAll = !collapseAll">
                {{ collapseAll ? $i18n.get('label_collapse_all') : $i18n.get('label_expand_all') }}
                <span class="icon">
                    <i 
                            :class="{ 'tainacan-icon-arrowdown' : collapseAll, 'tainacan-icon-arrowright' : !collapseAll}"
                            class="tainacan-icon tainacan-icon-1-25em"/>
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
                                    class="has-text-secondary tainacan-icon tainacan-icon-1-25em"/>
                        </span>
                        <span 
                                v-tooltip="{
                                    delay: {
                                        shown: 500,
                                        hide: 300,
                                    },
                                    content: metadatum.name,
                                    autoHide: false,
                                    placement: 'auto-start',
                                    popperClass: ['tainacan-tooltip', 'tooltip']
                                }"  
                                class="ellipsed-name">
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
    </div>
</template>

<script>
import { mapActions, mapGetters } from 'vuex';
import axios from '../../../../../admin/js/axios';
import 'swiper/css';
import 'swiper/css/mousewheel';
import 'swiper/css/navigation';
import 'swiper/css/virtual';
import Swiper, {Navigation, Virtual, Mousewheel } from 'swiper';
import CircularCounter from './circular-counter.vue';
import SlidesHelpModal from './slides-help-modal.vue'
import { viewModesMixin } from '../js/view-modes-mixin.js';
 
export default {
    name: 'ViewModeSlideshow',
    components: {
        CircularCounter
    },
    mixins: [
        viewModesMixin
    ],
    props: {
        initialItemPosition: null
    },  
    data () {
        return {
            virtualData: {
                slides: [],
            },
            slideItems: [],
            swiper: {},
            goingRight: true,
            isPlaying: false,
            hideControls: false,
            slideTimeout: 5000, 
            intervalId: 0, 
            collapseAll: false,
            isLoadingItem: true,
            itemRequestCancel: undefined,
            isMetadataCompressed: true,
            minPage: 1,
            maxPage: 1,
            preloadedItem: {},
            itemPosition: null
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
        'swiper.activeIndex': {
            handler(currentIndex, previousIndex) { 
                this.updateSliderBasedOnIndex(currentIndex, previousIndex);
            },
            immediate: true
        }, 
        isLoading: {
            handler(val, oldValue) {
                if (val === false && oldValue === true && this.swiper && this.items && this.items.length) {
                    let updatedSlideIndex = (this.swiper.activeIndex != undefined ? (JSON.parse(JSON.stringify(this.swiper.activeIndex)) + 0) : 0);

                    for (let newItem of ((this.goingRight === true || this.goingRight === undefined) ? JSON.parse(JSON.stringify(this.items)) : JSON.parse(JSON.stringify(this.items)).reverse())) {
                        let existingItemIndex = this.slideItems.findIndex(anItem => anItem.id == newItem.id);
                        if (existingItemIndex < 0 && this.swiper.virtual) {
                            if ( this.goingRight === true || this.goingRight === undefined) {
                                this.swiper.virtual.appendSlide(newItem);
                                //this.slideItems.push(newItem);
                            } else {
                                this.swiper.virtual.prependSlide(newItem);
                                //this.slideItems.unshift(newItem);
                            }
                        }
                    }

                    // Handles pausing auto play when reaches the end of the list.
                    if (updatedSlideIndex == this.slideItems.length - 1 && this.page == this.totalPages)
                        this.isPlaying = false;
                    
                    this.updateSliderBasedOnIndex(updatedSlideIndex);
                }
            },
            immediate: true
        },
        isPlaying() {
            if (this.isPlaying)
                this.intervalId = setInterval(() => this.nextSlide(), this.slideTimeout);
            else
                clearInterval(this.intervalId);
        }
    },
    mounted() {
        this.minPage = this.page;
        this.maxPage = this.page;

        // Adds keyup and keydown event listeners
        window.addEventListener('keyup', this.handleKeyboardKeys);

        // Passes props to data value of initial position, as we will modify it
        this.itemPosition = (this.initialItemPosition != null && this.initialItemPosition != undefined) ? this.initialItemPosition : 0;

        // Builds Swiper component
        const self = this;
        this.swiper = new Swiper('#tainacan-slide-container', {
            mousewheel: true,
            observer: true,
            preventInteractionOnTransition: true,
            slidesPerView: 24,
            spaceBetween: 12,
            centeredSlides: true,
            centerInsufficientSlides: false,
            slideToClickedSlide: true,
            breakpoints: {
                320: { slidesPerView: 4 },
                480: { slidesPerView: 5 },
                520: { slidesPerView: 6 },
                680: { slidesPerView: 7 },
                768: { slidesPerView: 8 },
                960: { slidesPerView: 9 },
                1024: { slidesPerView: 10 },
                1080: { slidesPerView: 11 },
                1280: { slidesPerView: 12 },
                1366: { slidesPerView: 13 },
                1406: { slidesPerView: 14 },
                1500: { slidesPerView: 15 },
                1600: { slidesPerView: 16 },
                1812: { slidesPerView: 17 },
                1920: { slidesPerView: 18 },
                2048: { slidesPerView: 19 },
                2160: { slidesPerView: 20 },
                2280: { slidesPerView: 21 },
                2400: { slidesPerView: 22 },
                2500: { slidesPerView: 23 }
            },
            virtual: {
                slides: self.slideItems,
                renderSlide(slideItem) {
                    return `<div data-tainacan-item-id="`+ slideItem.id + `" role="listitem" class="swiper-slide tainacan-slide-item">
                            <img 
                                    alt="` + (slideItem['thumbnail_alt'] ? slideItem['thumbnail_alt'] : (self.$i18n.get('label_thumbnail') + ': ' + slideItem.title) ) + `"
                                    class="thumbnail" 
                                    src="` + self.$thumbHelper.getSrc(slideItem['thumbnail'], 'tainacan-medium', slideItem.document_mimetype) + `">  
                        </div>`;
                },
                addSlidesBefore: 2,
                addSlidesAfter: 2
            },
            on: {
                observerUpdate: function () {
                    if (self.itemPosition != null && self.itemPosition != undefined) {
                        this.slideTo(self.itemPosition);
                        self.itemPosition = null;
                    }
                }
            },
            modules: [Navigation, Virtual, Mousewheel]
        });

        // Adds clipped class to root html
        document.documentElement.scrollTo(0,0);
        document.documentElement.classList.add('is-clipped');
    },
    beforeDestroy() {
        // Remove clipped class from root html
        document.documentElement.classList.remove('is-clipped');

        // Removes keyup and keydown event listeners
        window.removeEventListener('keyup', this.handleKeyboardKeys);

        clearInterval(this.intervalId);

        if (typeof this.swiper.destroy == 'function')
            this.swiper.destroy();
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
            this.hideControls = !this.hideControls;
        },
        closeSlideViewMode() {
            let currentQuery = this.$route.query;
            delete currentQuery['slideshow-from'];
            this.$router.replace({ query: currentQuery });

            // Sets the perpage and paged from previous configuration
            this.$eventBusSearch.setItemsPerPage(this.$parent.latestPerPageAfterViewModeWithoutPagination, true);
            this.$eventBusSearch.setPage(this.$parent.latestPageAfterViewModeWithoutPagination);
            this.$parent.onChangeViewMode(this.$parent.latestNonFullscreenViewMode ? this.$parent.latestNonFullscreenViewMode : this.$parent.defaultViewMode);
        },
        moveToClikedSlide(index) {
            if (this.swiper)
                this.swiper.slideTo(index);
        },
        nextSlide() {
            if (this.swiper)
                this.swiper.slideNext();
        },
        prevSlide() {
            if (this.swiper)
                this.swiper.slidePrev();
        },
        handleKeyboardKeys(event) {

            // Keys up and down toggle controls display
            if (event.keyCode === 38 || event.keyCode === 40 )
                this.onHideControls();
            
            // Space toggles play state
            else if (event.keyCode === 32 && !(this.swiper.activeIndex == this.slideItems.length - 1 && this.page == this.totalPages))
                this.isPlaying = !this.isPlaying;
            
            // Next and previous arrows navigate
            else if (event.keyCode === 39)
                this.nextSlide();
            else if (event.keyCode === 37)
                this.prevSlide();

            // ESC leaves the fullscreen viewmode
            else if (event.keyCode === 27)
                this.closeSlideViewMode(); 
            
        },
        updateSliderBasedOnIndex(currentIndex, previousIndex) {

            if (currentIndex < 0) {
                this.moveToClikedSlide(0);
            } else {
                // Handles direction information, used by animations
                if (previousIndex == undefined)
                    this.goingRight = undefined;    
                else if (currentIndex < previousIndex || (currentIndex == 0 && this.page == 1))
                    this.goingRight = false;
                else    
                    this.goingRight = true;

                // Handles loading main item info, displayed in the middle
                this.loadCurrentItem();

                // Handles requesting new page of items, either to left or right
                if (this.swiper) {

                    if (this.slideItems.length > 0) {
                        if (this.swiper.activeIndex == this.slideItems.length - 1 && (previousIndex == undefined ? this.page < this.totalPages : this.page < this.totalPages)) { 
                            previousIndex == undefined ? this.$eventBusSearch.setPage(this.page + 1) : this.$eventBusSearch.setPage(this.maxPage + 1);
                            this.goingRight = true;
                        } else if (this.swiper.activeIndex == 0 && this.page > 1 && this.slideItems.length < this.totalItems) {
                            previousIndex == undefined ? this.$eventBusSearch.setPage(this.page - 1) : this.$eventBusSearch.setPage(this.minPage - 1);
                            this.goingRight = false;
                        }
                    }
                }
            }
        },
        loadCurrentItem() {
            if ((this.slideItems && this.slideItems[this.swiper.activeIndex] && this.slideItems[this.swiper.activeIndex].id != undefined)) {

                this.isLoadingItem = true;

                // Checks if item is preloaded
                if (this.preloadedItem.id != undefined && this.preloadedItem.id == this.slideItems[this.swiper.activeIndex].id) {
                    this.replaceItem(this.preloadedItem);
                    this.$nextTick(() => this.isLoadingItem = false);
                } else {
                    // Cancels previous Request
                    if (this.itemRequestCancel != undefined)
                        this.itemRequestCancel.cancel('Item search Canceled.');

                    // Loads current item
                    this.fetchItem({ itemId: this.slideItems[this.swiper.activeIndex].id, contextEdit: true })
                        .then((resp) => {
                            resp.request.then(() => {
                                this.isLoadingItem = false;
                            })
                            .catch(() => {
                                this.isLoadingItem = false;
                            });
                            // Item resquest token for cancelling
                            this.itemRequestCancel = resp.source;
                        });
                }

                // Loads next item, just in case
                let nextIndex = (this.goingRight || this.goingRight == undefined) ? this.swiper.activeIndex + 1 : this.swiper.activeIndex - 1;
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
        openSlidesHelpModal() {
            this.$buefy.modal.open({
                component: SlidesHelpModal,
                width: 680,
                ariaRole: 'alertdialog',
                ariaModal: true,
                customClass: 'tainacan-modal slides-help-modal',
                closeButtonAriaLabel: this.$i18n.get('close'),
                onCancel: () => {
                    setTimeout(() => document.documentElement.classList.add('is-clipped'), 500); 
                }
            });
        }
    }
}
</script>

<style  lang="scss">

    @import "../scss/_view-mode-slideshow.scss";

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


