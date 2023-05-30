<template>
    <div 
            :style="customStyle"
            :class="className + ' has-mounted'">
        <div v-if="showCollectionHeader">
            <div
                    v-if="isLoadingCollection"
                    class="carousel-items-collection-header skeleton" 
                    :style="{ height: '165px' }"/>
            <a
                    v-else
                    :href="collection.url ? collection.url : ''"
                    class="carousel-items-collection-header">
                <div
                        :style="{
                            backgroundColor: collectionBackgroundColor ? collectionBackgroundColor : '', 
                            paddingRight: collection && collection.thumbnail && (collection.thumbnail['tainacan-medium'] || collection.thumbnail['medium']) ? '' : '20px',
                            paddingTop: (!collection || !collection.thumbnail || (!collection.thumbnail['tainacan-medium'] && !collection.thumbnail['medium'])) ? '1em' : '',
                            width: collection && collection.header_image ? '' : '100%'
                        }"
                        :class="
                            'collection-name ' + 
                            ((!collection || !collection.thumbnail || (!collection.thumbnail['tainacan-medium'] && !collection.thumbnail['medium'])) && (!collection || !collection.header_image) ? 'only-collection-name' : '') 
                        ">
                    <h3 :style="{ color: collectionTextColor ? collectionTextColor : '' }">
                        <span
                                v-if="showCollectionLabel"
                                class="label">
                            {{ $root.__('Collection', 'tainacan') }}
                            <br>
                        </span>
                        {{ collection && collection.name ? collection.name : '' }}
                    </h3>
                </div>
                <div
                    v-if="collection && collection.thumbnail && (collection.thumbnail['tainacan-medium'] || collection.thumbnail['medium'])"   
                    class="collection-thumbnail"
                    :style="{ 
                        backgroundImage: 'url(' + (collection.thumbnail['tainacan-medium'] != undefined ? (collection.thumbnail['tainacan-medium'][0]) : (collection.thumbnail['medium'][0])) + ')',
                    }"/>
                <div
                        class="collection-header-image"
                        :style="{
                            backgroundImage: collection.header_image ? 'url(' + collection.header_image + ')' : '',
                            minHeight: collection && collection.header_image ? '' : '80px',
                            display: !(collection && collection.thumbnail && (collection.thumbnail['tainacan-medium'] || collection.thumbnail['medium'])) ? collection && collection.header_image ? '' : 'none' : ''  
                        }"/>
            </a>   
        </div>
        <div  
                :class="'tainacan-carousel ' + (arrowsPosition ? ' has-arrows-' + arrowsPosition : '') + (largeArrows ? ' has-large-arrows' : '') "
                :style="{ '--spaceAroundCarousel': !isNaN(spaceAroundCarousel) ? (spaceAroundCarousel + 'px') : '50px' }"
                v-if="items.length > 0 || isLoading">
            <div 
                    class="swiper"
                    :id="blockId + '-carousel'">
                <ul 
                        v-if="isLoading"
                        role="list"
                        class="swiper-wrapper"
                        :style="{
                            marginTop: showCollectionHeader ? '1.35em' : '0px'
                        }">
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
                        class="swiper-wrapper"
                        :style="{
                            marginTop: showCollectionHeader ? '1.35em' : '0px'
                        }">
                    <li
                            role="listitem"
                            :key="index"
                            v-for="(item, index) of items"
                            class="swiper-slide item-list-item"
                            :class="{ 'is-forced-square': ['tainacan-medium', 'tainacan-small'].indexOf(imageSize) > -1 }">
                        <a 
                                :id="isNaN(item.id) ? item.id : 'item-id-' + item.id"
                                :href="item.url">
                            <blur-hash-image
                                    :height="$thumbHelper.getHeight(item['thumbnail'], imageSize)"
                                    :width="$thumbHelper.getWidth(item['thumbnail'], imageSize)"
                                    :src="$thumbHelper.getSrc(item['thumbnail'], imageSize, item['document_mimetype'])"
                                    :srcset="$thumbHelper.getSrcSet(item['thumbnail'], imageSize, item['document_mimetype'])"
                                    :hash="$thumbHelper.getBlurhashString(item['thumbnail'], imageSize)"
                                    :alt="item.thumbnail_alt ? item.thumbnail_alt : (item && item.title ? item.title : $root.__( 'Thumbnail', 'tainacan' ))"
                                    :transition-duration="500" />
                            <span v-if="!hideTitle">{{ item.title ? item.title : '' }}</span>
                        </a>
                    </li>
                </ul>
            </div>
            <button 
                    class="swiper-button-prev" 
                    :id="blockId + '-prev'" 
                    slot="button-prev"
                    :style="hideTitle ? 'top: calc(50% - 21px)' : 'top: calc(50% - ' + (largeArrows ? '60' : '42') + 'px)'">
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
                    :style="hideTitle ? 'top: calc(50% - 21px)' : 'top: calc(50% - ' + (largeArrows ? '60' : '42') + 'px)'">
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
                v-else-if="items.length <= 0 && !isLoading"
                class="spinner-container">
            {{ $root.__('No items found.', 'tainacan') }}
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
    name: "CarouselItemsListTheme",
    props: {
        blockId: String,
        collectionId: String,  
        searchURL: String,
        selectedItems: Array,
        loadStrategy: String,
        maxItemsNumber: Number,
        maxItemsPerScreen: Number,
        spaceBetweenItems: Number,
        spaceAroundCarousel: Number,
        arrowsPosition: String,
        largeArrows: Boolean,
        arrowsStyle: String,
        autoPlay: false,
        autoPlaySpeed: Number,
        loopSlides: Boolean,
        hideTitle: Boolean,
        imageSize: String,
        showCollectionHeader: Boolean,
        showCollectionLabel: Boolean,
        collectionBackgroundColor: String,
        collectionTextColor: String,
        tainacanApiRoot: String,
        tainacanBaseUrl: String,
        className: String,
        customStyle: String
    },
    data() {
        return {
            items: [],
            collection: undefined,
            itemsRequestSource: undefined,
            isLoading: false,
            isLoadingCollection: false,
            localMaxItemsNumber: undefined,
            localOrder: undefined,
            tainacanAxios: undefined,
            paged: undefined,
            totalItems: 0,
            swiper: {},
            apiRoot: '',
            errorMessage: 'No items found.'
        }
    },
    created() {
        
        this.apiRoot = (tainacan_blocks && tainacan_blocks.root && !this.tainacanApiRoot) ? tainacan_blocks.root : this.tainacanApiRoot;
            
        this.tainacanAxios = axios.create({ baseURL: this.apiRoot });
        if (tainacan_blocks && tainacan_blocks.nonce)
            this.tainacanAxios.defaults.headers.common['X-WP-Nonce'] = tainacan_blocks.nonce;
  
        if (this.showCollectionHeader)
            this.fetchCollectionForHeader();

        this.fetchItems();
    },
    beforeDestroy() {
        if (typeof this.swiper.destroy == 'function')
            this.swiper.destroy();
    },
    methods: {
        fetchItems() {
 
            this.isLoading = true;
            this.errorMessage = 'No items found.';

            this.$nextTick(() => {
                this.mountCarousel();
            });
            
            if (this.itemsRequestSource != undefined && typeof this.itemsRequestSource == 'function')
                this.itemsRequestSource.cancel('Previous items search canceled.');

            this.itemsRequestSource = axios.CancelToken.source();

            if (this.loadStrategy == 'parent') {

                for (let item of this.selectedItems)
                    this.items.push(item);

                    this.isLoading = false;
                    this.totalItems = this.items.length;

                    this.$nextTick(() => {
                        this.mountCarousel();
                    });

            } else if (this.loadStrategy == 'selection') {
                let endpoint = '/collection/' + this.collectionId + '/items?' + qs.stringify({ postin: this.selectedItems, perpage: this.selectedItems.length }) + '&orderby=post__in&fetch_only=title,url,thumbnail';
                
                this.tainacanAxios.get(endpoint, { cancelToken: this.itemsRequestSource.token })
                    .then(response => {

                        for (let item of response.data.items)
                            this.items.push(item);

                        this.isLoading = false;
                        this.totalItems = response.headers['x-wp-total'];

                        this.$nextTick(() => {
                            this.mountCarousel();
                        });

                    }).catch((error) => { 
                        this.isLoading = false;
                        if (error.response && error.response.status && error.response.status == 401)
                            this.errorMessage = 'Not allowed to see these items.'
                    });
            } else {

                this.items = [];
                
                let endpoint = this.searchURL != undefined ? ('/collection' + this.searchURL.split('#')[1].split('/collections')[1]) : '';
                let query = endpoint.split('?')[1];
                let queryObject = qs.parse(query);

                // Set up max items to be shown
                if (this.maxItemsNumber != undefined && Number(this.maxItemsNumber) > 0)
                    queryObject.perpage = this.maxItemsNumber;
                else if (queryObject.perpage != undefined && queryObject.perpage > 0)
                    this.localMaxItemsNumber = queryObject.perpage;
                else {
                    queryObject.perpage = 12;
                    this.localMaxItemsNumber = 12;
                }

                // Set up paging
                if (this.paged != undefined)
                    queryObject.paged = this.paged;
                else if (queryObject.paged != undefined)
                    this.paged = queryObject.paged;
                else
                    this.paged = 1;

                // Remove unecessary queries
                delete queryObject.admin_view_mode;
                delete queryObject.fetch_only_meta;
                
                endpoint = endpoint.split('?')[0] + '?' + qs.stringify(queryObject) + '&fetch_only=title,url,thumbnail';
                
                this.tainacanAxios.get(endpoint, { cancelToken: this.itemsRequestSource.token })
                    .then(response => {

                        for (let item of response.data.items)
                            this.items.push(item);

                        this.isLoading = false;
                        this.totalItems = response.headers['x-wp-total'];

                        this.$nextTick(() => {
                            this.mountCarousel();
                        });

                    }).catch((error) => { 
                        this.isLoading = false;
                        if (error.response && error.response.status && error.response.status == 401)
                            this.errorMessage = 'Not allowed to see these items.'
                    });
            }
        },
        fetchCollectionForHeader() {
            if (this.showCollectionHeader) {

                this.isLoadingCollection = true;

                this.tainacanAxios.get('/collections/' + this.collectionId + '?fetch_only=name,thumbnail,header_image')
                    .then(response => {
                        this.collection = response.data;
                        this.isLoadingCollection = false;      
                    });
            }
        },
        mountCarousel() {
            const self = this;
            const spaceBetween = Number(self.spaceBetweenItems);
            const slidesPerView = Number(self.maxItemsPerScreen);
            this.swiper = new Swiper('#' + self.blockId + '-carousel', {
                watchOverflow: true,
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
                breakpoints: (!isNaN(self.maxItemsPerScreen) && self.maxItemsPerScreen != 6) ? {
                    498:  { slidesPerView: slidesPerView - 4 > 0 ? slidesPerView - 4 : 1, spaceBetween: spaceBetween }, 
                    768:  { slidesPerView: slidesPerView - 3 > 0 ? slidesPerView - 3 : 1, spaceBetween: spaceBetween },
                    1024: { slidesPerView: slidesPerView - 2 > 0 ? slidesPerView - 2 : 1, spaceBetween: spaceBetween },
                    1366: { slidesPerView: slidesPerView - 1 > 0 ? slidesPerView - 1 : 1, spaceBetween: spaceBetween },
                    1600: { slidesPerView: slidesPerView > 0 ? slidesPerView : 1, spaceBetween: spaceBetween },
                } : {
                    498:  { slidesPerView: 2, spaceBetween: spaceBetween }, 
                    768:  { slidesPerView: 3, spaceBetween: spaceBetween },
                    1024: { slidesPerView: 4, spaceBetween: spaceBetween },
                    1366: { slidesPerView: 5, spaceBetween: spaceBetween },
                    1600: { slidesPerView: 6, spaceBetween: spaceBetween }
                },
                autoplay: self.autoPlay ? { delay: self.autoPlaySpeed*1000 } : false,
                loop: self.loopSlides ? self.loopSlides : false,
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
