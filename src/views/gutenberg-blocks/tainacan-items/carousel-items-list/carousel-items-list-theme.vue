<template>
    <div :class="className">
        <div v-if="showCollectionHeader">
            <div
                    v-if="isLoadingCollection"
                    class="carousel-items-collection-header skeleton" 
                    :style="{ height: '165px' }"/>
            <a
                    v-else
                    target="_blank"
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
        <div v-if="!isLoading">
            <div  
                    :class="'tainacan-carousel has-arrows-' + arrowsPosition"
                    v-if="items.length > 0">
                <swiper 
                        role="list"
                        :options="swiperOptions"
                        ref="myItemSwiper"
                        :style="{
                            marginTop: showCollectionHeader ? '1.35em' : '0px'
                        }">
                    <swiper-slide 
                            role="listitem"
                            ref="myItemSwiperSlide"
                            :key="index"
                            v-for="(item, index) of items"
                            class="item-list-item">      
                        <a 
                                :id="isNaN(item.id) ? item.id : 'item-id-' + item.id"
                                :href="item.url"
                                target="_blank">
                            <img
                                :src=" 
                                    item.thumbnail && item.thumbnail['tainacan-medium'][0] && item.thumbnail['tainacan-medium'][0] 
                                        ?
                                    item.thumbnail['tainacan-medium'][0] 
                                        :
                                    (item.thumbnail && item.thumbnail['thumbnail'][0] && item.thumbnail['thumbnail'][0]
                                        ?    
                                    item.thumbnail['thumbnail'][0] 
                                        : 
                                    `${tainacanBaseUrl}/assets/images/placeholder_square.png`)
                                "
                                :alt="item.title ? item.title : $root.__('Thumbnail', 'tainacan')">
                            <span v-if="!hideTitle">{{ item.title ? item.title : '' }}</span>
                        </a>
                    </swiper-slide>
                </swiper>
                <button 
                        class="swiper-button-prev" 
                        :id="blockId + '-prev'" 
                        slot="button-prev"
                        :style="hideTitle ? 'top: calc(50% - 21px)' : 'top: calc(50% - 42px)'">
                    <svg
                            width="42"
                            height="42"
                            viewBox="0 0 24 24">
                        <path d="M15.41 7.41L14 6l-6 6 6 6 1.41-1.41L10.83 12z"/>
                        <path
                                d="M0 0h24v24H0z"
                                fill="none"/>                         
                    </svg>
                </button>
                <button 
                        class="swiper-button-next" 
                        :id="blockId + '-next'" 
                        slot="button-next"
                        :style="hideTitle ? 'top: calc(50% - 21px)' : 'top: calc(50% - 42px)'">
                    <svg
                            width="42"
                            height="42"
                            viewBox="0 0 24 24">
                        <path d="M10 6L8.59 7.41 13.17 12l-4.58 4.59L10 18l6-6z"/>
                        <path
                                d="M0 0h24v24H0z"
                                fill="none"/>                        
                    </svg>
                </button>
            </div>
            <div
                    v-else
                    class="spinner-container">
                {{ $root.__(errorMessage, 'tainacan') }}
            </div>
            <!-- Swiper buttons are hidden as they actually swipe from slide to slide -->
        </div>
        <div v-else-if="isLoading && !autoPlay && !loopSlides">
            <div :class="'tainacan-carousel has-arrows-' + arrowsPosition">
                <swiper 
                        role="list"
                        :options="swiperOptions"
                        ref="myItemSwiperSkeleton"
                        :style="{
                            marginTop: showCollectionHeader ? '1.35em' : '0px'
                        }">
                    <swiper-slide 
                            role="listitem"
                            :key="index"
                            ref="myItemSwiperSlideSkeleton"
                            v-for="(item, index) of 18"
                            class="item-list-item skeleton">      
                        <a>
                            <img>
                            <span v-if="!hideTitle" />
                        </a>
                    </swiper-slide>
                </swiper>
                <button 
                        class="swiper-button-prev" 
                        :id="blockId + '-prev'" 
                        slot="button-prev"
                        :style="hideTitle ? 'top: calc(50% - 21px)' : 'top: calc(50% - 42px)'">
                    <svg
                            width="42"
                            height="42"
                            viewBox="0 0 24 24">
                        <path d="M15.41 7.41L14 6l-6 6 6 6 1.41-1.41L10.83 12z"/>
                        <path
                                d="M0 0h24v24H0z"
                                fill="none"/>                         
                    </svg>
                </button>
                <button 
                        class="swiper-button-next" 
                        :id="blockId + '-next'"   
                        slot="button-next"
                        :style="hideTitle ? 'top: calc(50% - 21px)' : 'top: calc(50% - 42px)'">
                    <svg
                            width="42"
                            height="42"
                            viewBox="0 0 24 24">
                        <path d="M10 6L8.59 7.41 13.17 12l-4.58 4.59L10 18l6-6z"/>
                        <path
                                d="M0 0h24v24H0z"
                                fill="none"/>                        
                    </svg>
                </button>
            </div>
        </div>
    </div>
</template>
 
<script>
import axios from 'axios';
import qs from 'qs';
import 'swiper/dist/css/swiper.css';
import { swiper, swiperSlide } from 'vue-awesome-swiper';

export default {
    name: "CarouselItemsListTheme",
    components: {
        swiper,
        swiperSlide
    },
    props: {
        blockId: String,
        collectionId: String,  
        searchURL: String,
        selectedItems: Array,
        loadStrategy: String,
        maxItemsNumber: Number,
        arrowsPosition: String,
        autoPlay: false,
        autoPlaySpeed: Number,
        loopSlides: Boolean,
        hideTitle: Boolean,
        showCollectionHeader: Boolean,
        showCollectionLabel: Boolean,
        collectionBackgroundColor: String,
        collectionTextColor: String,
        tainacanApiRoot: String,
        tainacanBaseUrl: String,
        className: String
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
            swiperOptions: {
                mousewheel: true,
                observer: true,
                preventInteractionOnTransition: true,
                allowClick: true,
                allowTouchMove: true, 
                slidesPerView: 7,
                slidesPerGroup: 1,
                spaceBetween: 32,
                slideToClickedSlide: true,
                navigation: {
                    nextEl: '#' + this.blockId + '-next',
                    prevEl: '#' + this.blockId + '-prev',
                }, 
                breakpoints: {
                    498:  { slidesPerView: 2 }, 
                    768:  { slidesPerView: 3 },
                    1024: { slidesPerView: 4 },
                    1366: { slidesPerView: 5 },
                    1600: { slidesPerView: 6 },
                },
                autoplay: this.autoPlay ? { delay: this.autoPlaySpeed*1000 } : false,
                loop: this.loopSlides ? this.loopSlides : false
            },
            errorMessage: 'No items found.'
        }
    },
    created() {
        this.tainacanAxios = axios.create({ baseURL: this.tainacanApiRoot });
  
        if (this.showCollectionHeader)
            this.fetchCollectionForHeader();

        this.fetchItems();
    },
    methods: {
        fetchItems() {
 
            this.isLoading = true;
            this.errorMessage = 'No items found.';
            
            if (this.itemsRequestSource != undefined && typeof this.itemsRequestSource == 'function')
                this.itemsRequestSource.cancel('Previous items search canceled.');

            this.itemsRequestSource = axios.CancelToken.source();

            if (this.loadStrategy == 'selection') {
                let endpoint = '/collection/' + this.collectionId + '/items?' + qs.stringify({ postin: this.selectedItems, perpage: this.selectedItems.length }) + '&fetch_only=title,url,thumbnail';
                
                this.tainacanAxios.get(endpoint, { cancelToken: this.itemsRequestSource.token })
                    .then(response => {

                        for (let item of response.data.items)
                            this.items.push(item);

                        this.isLoading = false;
                        this.totalItems = response.headers['x-wp-total'];

                    }).catch((error) => { 
                        this.isLoading = false;
                        if (error.response && error.response.status && error.response.status == 401)
                            this.errorMessage = 'Not allowed to see these items.'
                    });
            } else {

                this.items = [];

                let endpoint = '/collection' + this.searchURL.split('#')[1].split('/collections')[1];
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
                delete queryObject.readmode;
                delete queryObject.iframemode;
                delete queryObject.admin_view_mode;
                delete queryObject.fetch_only_meta;
                
                endpoint = endpoint.split('?')[0] + '?' + qs.stringify(queryObject) + '&fetch_only=title,url,thumbnail';
                
                this.tainacanAxios.get(endpoint, { cancelToken: this.itemsRequestSource.token })
                    .then(response => {

                        for (let item of response.data.items)
                            this.items.push(item);

                        this.isLoading = false;
                        this.totalItems = response.headers['x-wp-total'];

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
        }
    }
}
</script>

<style lang="scss">

    @import './carousel-items-list.scss';

</style>
