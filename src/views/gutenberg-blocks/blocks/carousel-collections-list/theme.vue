<template>
    <div 
            :style="customStyle"
            :class="className + ' has-mounted'">
        <div  
                :class="'tainacan-carousel ' + (arrowsPosition ? ' has-arrows-' + arrowsPosition : '') + (largeArrows ? ' has-large-arrows' : '') "
                :style="{ '--spaceAroundCarousel': !isNaN(spaceAroundCarousel) ? (spaceAroundCarousel + 'px') : '50px' }"
                v-if="collections.length > 0 || isLoading">
            <div 
                    class="swiper"
                    :id="blockId + '-carousel'">
                <ul 
                        v-if="isLoading"
                        role="list"
                        class="swiper-wrapper">
                    <li 
                            role="listitem"
                            :key="index"
                            v-for="index in 18"
                            class="swiper-slide collection-list-item skeleton">
                        <a>
                            <img>
                            <span v-if="!hideName" />
                        </a>
                    </li>
                </ul>
                <ul 
                        v-else
                        role="list"
                        class="swiper-wrapper">
                    <li 
                            role="listitem"
                            :key="index"
                            v-for="(collection, index) of collections"
                            :class="'swiper-slide collection-list-item ' + (!showCollectionThumbnail ? 'collection-list-item-grid' : '')">      
                        <a 
                                v-if="showCollectionThumbnail"
                                :id="isNaN(collection.id) ? collection.id : 'collection-id-' + collection.id"
                                :href="collection.url">
                            <img
                                :src=" 
                                    collection.thumbnail && collection.thumbnail[imageSize] && collection.thumbnail[imageSize][0] 
                                        ?
                                    collection.thumbnail[imageSize][0] 
                                        :
                                    (collection.thumbnail && collection.thumbnail['thumbnail'] && collection.thumbnail['thumbnail'][0]
                                        ?    
                                    collection.thumbnail['thumbnail'][0] 
                                        : 
                                    `${tainacanBaseUrl}/assets/images/placeholder_square.png`)
                                "
                                :data-src=" 
                                    collection.thumbnail && collection.thumbnail[imageSize] && collection.thumbnail[imageSize][0] 
                                        ?
                                    collection.thumbnail[imageSize][0] 
                                        :
                                    (collection.thumbnail && collection.thumbnail['thumbnail'] && collection.thumbnail['thumbnail'][0]
                                        ?    
                                    collection.thumbnail['thumbnail'][0] 
                                        : 
                                    `${tainacanBaseUrl}/assets/images/placeholder_square.png`)
                                "
                                :alt="collection.name ? collection.name : $root.__('Thumbnail', 'tainacan')">
                            <span v-if="!hideName">{{ collection.name ? collection.name : '' }}</span>
                        </a>
                        <a 
                                v-else
                                :id="isNaN(collection.id) ? collection.id : 'collection-id-' + collection.id"
                                :href="collection.url">
                            <div class="collection-items-grid">
                                <blur-hash-image
                                        :height="collectionItems[collection.id][0] ? $thumbHelper.getHeight(collectionItems[collection.id][0]['thumbnail'], imageSize) : 275"
                                        :width="collectionItems[collection.id][0] ? $thumbHelper.getWidth(collectionItems[collection.id][0]['thumbnail'], imageSize) : 275"
                                        :src="collectionItems[collection.id][0] ? $thumbHelper.getSrc(collectionItems[collection.id][0]['thumbnail'], imageSize, collectionItems[collection.id][0]['document_mimetype']) :`${tainacanBaseUrl}/assets/images/placeholder_square.png`"
                                        :srcset="collectionItems[collection.id][0] ? $thumbHelper.getSrcSet(collectionItems[collection.id][0]['thumbnail'], imageSize, collectionItems[collection.id][0]['document_mimetype']) :`${tainacanBaseUrl}/assets/images/placeholder_square.png`"
                                        :hash="collectionItems[collection.id][0] ? $thumbHelper.getBlurhashString(collectionItems[collection.id][0]['thumbnail'], imageSize) : 'V4P?:h00Rj~qM{of%MRjWBRjD%%MRjayofj[%M-;RjRj'"
                                        :alt="collectionItems[collection.id][0] && collectionItems[collection.id][0].thumbnail_alt ? collectionItems[collection.id][0].thumbnail_alt : (collectionItems[collection.id][0] && collectionItems[collection.id][0].name ? collectionItems[collection.id][0].name : $root.__( 'Thumbnail', 'tainacan' ))"
                                        :transition-duration="500" />
                                <blur-hash-image
                                        :height="collectionItems[collection.id][1] ? $thumbHelper.getHeight(collectionItems[collection.id][1]['thumbnail'], imageSize) : 275"
                                        :width="collectionItems[collection.id][1] ? $thumbHelper.getWidth(collectionItems[collection.id][1]['thumbnail'], imageSize) : 275"
                                        :src="collectionItems[collection.id][1] ? $thumbHelper.getSrc(collectionItems[collection.id][1]['thumbnail'], imageSize, collectionItems[collection.id][1]['document_mimetype']) :`${tainacanBaseUrl}/assets/images/placeholder_square.png`"
                                        :srcset="collectionItems[collection.id][1] ? $thumbHelper.getSrcSet(collectionItems[collection.id][1]['thumbnail'], imageSize, collectionItems[collection.id][1]['document_mimetype']) :`${tainacanBaseUrl}/assets/images/placeholder_square.png`"
                                        :hash="collectionItems[collection.id][1] ? $thumbHelper.getBlurhashString(collectionItems[collection.id][1]['thumbnail'], imageSize) : 'V4P?:h00Rj~qM{of%MRjWBRjD%%MRjayofj[%M-;RjRj'"
                                        :alt="collectionItems[collection.id][1] && collectionItems[collection.id][1].thumbnail_alt ? collectionItems[collection.id][1].thumbnail_alt : (collectionItems[collection.id][1] && collectionItems[collection.id][1].name ? collectionItems[collection.id][1].name : $root.__( 'Thumbnail', 'tainacan' ))"
                                        :transition-duration="500" />
                                <blur-hash-image
                                        :height="collectionItems[collection.id][2] ? $thumbHelper.getHeight(collectionItems[collection.id][2]['thumbnail'], imageSize) : 275"
                                        :width="collectionItems[collection.id][2] ? $thumbHelper.getWidth(collectionItems[collection.id][2]['thumbnail'], imageSize) : 275"
                                        :src="collectionItems[collection.id][2] ? $thumbHelper.getSrc(collectionItems[collection.id][2]['thumbnail'], imageSize, collectionItems[collection.id][2]['document_mimetype']) :`${tainacanBaseUrl}/assets/images/placeholder_square.png`"
                                        :srcset="collectionItems[collection.id][2] ? $thumbHelper.getSrcSet(collectionItems[collection.id][2]['thumbnail'], imageSize, collectionItems[collection.id][2]['document_mimetype']) :`${tainacanBaseUrl}/assets/images/placeholder_square.png`"
                                        :hash="collectionItems[collection.id][2] ? $thumbHelper.getBlurhashString(collectionItems[collection.id][2]['thumbnail'], imageSize) : 'V4P?:h00Rj~qM{of%MRjWBRjD%%MRjayofj[%M-;RjRj'"
                                        :alt="collectionItems[collection.id][2] && collectionItems[collection.id][2].thumbnail_alt ? collectionItems[collection.id][2].thumbnail_alt : (collectionItems[collection.id][2] && collectionItems[collection.id][2].name ? collectionItems[collection.id][2].name : $root.__( 'Thumbnail', 'tainacan' ))"
                                        :transition-duration="500" />
                            </div>
                            <span v-if="!hideName">{{ collection.name ? collection.name : '' }}</span>
                        </a>
                    </li>
                </ul>
            </div>
            <button 
                    class="swiper-button-prev" 
                    :id="blockId + '-prev'" 
                    slot="button-prev"
                    :style="hideName ? 'top: calc(50% - 21px)' : 'top: calc(50% - ' + (largeArrows ? '60' : '42') + 'px)'">
                <svg
                        :width="largeArrows ? 60 : 42"
                        :height="largeArrows ? 60 : 42"
                        viewBox="0 0 24 24">
                    <path
                            v-if="arrowsStyle === 'type-2'"
                            d="M 10.694196,6 12.103795,7.4095983 8.5000002,11.022321 H 19.305804 v 1.955358 H 8.5000002 L 12.103795,16.590402 10.694196,18 4.6941962,12 Z"/>
                    <path 
                            v-else
                            d="M15.41 7.41L14 6l-6 6 6 6 1.41-1.41L10.83 12z"/>
                    <path
                            d="M0 0h24v24H0z"
                            fill="none"/>
                </svg>
            </button>
            <button 
                    class="swiper-button-next" 
                    :id="blockId + '-next'" 
                    slot="button-next"
                    :style="hideName ? 'top: calc(50% - 21px)' : 'top: calc(50% - ' + (largeArrows ? '60' : '42') + 'px)'">
                <svg
                        :width="largeArrows ? 60 : 42"
                        :height="largeArrows ? 60 : 42"
                        viewBox="0 0 24 24">
                    <path
                            v-if="arrowsStyle === 'type-2'"
                            d="M 13.305804,6 11.896205,7.4095983 15.5,11.022321 H 4.6941964 v 1.955358 H 15.5 L 11.896205,16.590402 13.305804,18 l 6,-6 z"/>
                    <path 
                            v-else
                            d="M10 6L8.59 7.41 13.17 12l-4.58 4.59L10 18l6-6z"/>
                    <path
                            d="M0 0h24v24H0z"
                            fill="none"/>
                </svg>
            </button>
        </div>
        <div
                v-else-if="collections.length <= 0 && !isLoading"
                class="spinner-container">
            {{ $root.__('No collections found.', 'tainacan') }}
        </div>
    </div>
</template>
 
<script>
import axios from 'axios';
import qs from 'qs';
import 'swiper/css';
import 'swiper/css/a11y';
import 'swiper/css/autoplay';
import 'swiper/css/navigation';
import Swiper, { Autoplay, Navigation, A11y } from 'swiper';

export default {
    name: "CarouselCollectionsListTheme",
    props: {
        blockId: String,
        selectedCollections: Array,
        maxCollectionsNumber: Number,
        arrowsPosition: String,
        autoPlay: false,
        autoPlaySpeed: Number,
        loopSlides: Boolean,
        maxCollectionsPerScreen: Number,
        spaceBetweenCollections: Number,
        spaceAroundCarousel: Number,
        hideName: Boolean,
        largeArrows: Boolean,
        arrowsStyle: String,
        imageSize: String,
        showCollectionThumbnail: Boolean,
        tainacanApiRoot: String,
        tainacanBaseUrl: String,
        className: String,
        customStyle: String
    },
    data() {
        return {
            collections: [],
            collectionItems: {},
            collectionsRequestSource: undefined,
            isLoading: false,
            isLoadingCollection: false,
            localMaxCollectionsNumber: undefined,
            localOrder: undefined,
            tainacanAxios: undefined,
            paged: undefined,
            totalCollections: 0,
            apiRoot: '',
            errorMessage: 'No collections found.',
            swiper: {}
        }
    },
    created() {
        
        this.apiRoot = (tainacan_blocks && tainacan_blocks.root && !this.tainacanApiRoot) ? tainacan_blocks.root : this.tainacanApiRoot;
            
        this.tainacanAxios = axios.create({ baseURL: this.apiRoot });
        if (tainacan_blocks && tainacan_blocks.nonce)
            this.tainacanAxios.defaults.headers.common['X-WP-Nonce'] = tainacan_blocks.nonce;

        this.fetchCollections();
    },
    beforeDestroy() {
        if (typeof this.swiper.destroy == 'function')
            this.swiper.destroy();
    },
    methods: {
        fetchCollections() {
            this.isLoading = true;
            this.errorMessage = 'No collections found.';

            this.$nextTick(() => {
                this.mountCarousel();
            });
            
            if (this.collectionsRequestSource != undefined && typeof this.collectionsRequestSource == 'function')
                this.collectionsRequestSource.cancel('Previous collections search canceled.');

            this.collectionsRequestSource = axios.CancelToken.source();

            let endpoint = '/collections?'+ qs.stringify({ postin: this.selectedCollections, perpage: this.selectedCollections.length, fetch_preview_image_items: this.showCollectionThumbnail ? 0 : 3 }) + '&orderby=post__in&fetch_only=name,url,thumbnail';

            this.tainacanAxios.get(endpoint, { cancelToken: this.collectionsRequestSource.token })
                .then(response => {

                    for (let collection of response.data) {
                        this.collections.push(collection);
                        if (!this.showCollectionThumbnail)
                            this.collectionItems[collection.id] = collection.preview_image_items ? collection.preview_image_items : [];
                    }

                    this.isLoading = false;
                    this.totalCollections = response.headers['x-wp-total'];

                    this.$nextTick(() => {
                        this.mountCarousel();
                    });

                }).catch((error) => { 
                    this.isLoading = false;
                    if (error.response && error.response.status && error.response.status == 401)
                        this.errorMessage = 'Not allowed to see these collections.'

                });
        },
        mountCarousel() {
            const self = this;
            const spaceBetween = Number(self.spaceBetweenCollections);
            const slidesPerView = Number(self.maxCollectionsPerScreen);
            this.swiper = new Swiper('#' + self.blockId + '-carousel', {
                mousewheel: {
                    forceToAxis: true
                },
                observer: true,
                preventInteractionOnTransition: true,
                allowClick: true,
                allowTouchMove: true, 
                slidesPerView: 1,
                slidesPerGroup: 1,
                spaceBetween: spaceBetween,
                slideToClickedSlide: true,
                navigation: {
                    nextEl: '#' + self.blockId + '-next',
                    prevEl: '#' + self.blockId + '-prev',
                },
                breakpoints: !isNaN(self.maxCollectionsPerScreen) ? {
                    498:  { slidesPerView: self.showCollectionThumbnail ? 1 : 1, spaceBetween: spaceBetween },
                    768:  { slidesPerView: self.showCollectionThumbnail ? 2 : 1, spaceBetween: spaceBetween },
                    1024: { slidesPerView: self.showCollectionThumbnail ? 3 : 2, spaceBetween: spaceBetween },
                    1366: { slidesPerView: self.showCollectionThumbnail ? 4 : 3, spaceBetween: spaceBetween },
                    1600: { slidesPerView: self.showCollectionThumbnail ? 5 : 4, spaceBetween: spaceBetween },
                } : {
                    498:  { slidesPerView: slidesPerView - 4 > 0 ? slidesPerView - 4 : 1, spaceBetween: spaceBetween }, 
                    768:  { slidesPerView: slidesPerView - 3 > 0 ? slidesPerView - 3 : 1, spaceBetween: spaceBetween },
                    1024: { slidesPerView: slidesPerView - 2 > 0 ? slidesPerView - 2 : 1, spaceBetween: spaceBetween },
                    1366: { slidesPerView: slidesPerView - 1 > 0 ? slidesPerView - 1 : 1, spaceBetween: spaceBetween },
                    1600: { slidesPerView: slidesPerView > 0 ? slidesPerView : 1, spaceBetween: spaceBetween },
                },
                autoplay: (self.autoPlay && !self.isLoading) ? { delay: self.autoPlaySpeed*1000 } : false,
                loop: self.loopSlides && !self.isLoading,
                a11y: {
                    prevSlideMessage: self.$root.__( 'Previous slide', 'tainacan'),
                    nextSlideMessage: self.$root.__( 'Next slide', 'tainacan'),
                    firstSlideMessage: self.$root.__('This is the first slide', 'tainacan'),
                    lastSlideMessage: self.$root.__('This is the last slide', 'tainacan')
                },
                modules: [Autoplay, Navigation, A11y]
            });
        }
    }
}
</script>

<style lang="scss">

    @import './style.scss';

</style>
