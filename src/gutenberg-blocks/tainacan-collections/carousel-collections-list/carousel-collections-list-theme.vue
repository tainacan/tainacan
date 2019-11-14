<template>
    <div :class="className">
        <div v-if="!isLoading">
            <div  
                    :class="'tainacan-carousel has-arrows-' + arrowsPosition"
                    v-if="collections.length > 0">
                <swiper 
                        role="list"
                        ref="myCollectionSwiper"
                        :options="swiperOptions">
                    <swiper-slide 
                            role="listitem"
                            :key="index"
                            v-for="(collection, index) of collections"
                            :class="'collection-list-item ' + (!showCollectionThumbnail ? 'collection-list-item-grid' : '')">      
                        <a 
                                v-if="showCollectionThumbnail"
                                :id="isNaN(collection.id) ? collection.id : 'collection-id-' + collection.id"
                                :href="collection.url"
                                target="_blank">
                            <img
                                :src=" 
                                    collection.thumbnail && collection.thumbnail['tainacan-medium'][0] && collection.thumbnail['tainacan-medium'][0] 
                                        ?
                                    collection.thumbnail['tainacan-medium'][0] 
                                        :
                                    (collection.thumbnail && collection.thumbnail['thumbnail'][0] && collection.thumbnail['thumbnail'][0]
                                        ?    
                                    collection.thumbnail['thumbnail'][0] 
                                        : 
                                    `${tainacanBaseUrl}/admin/images/placeholder_square.png`)
                                "
                                :alt="collection.name ? collection.name : $root.__('Thumbnail', 'tainacan')">
                            <span v-if="!hideName">{{ collection.name ? collection.name : '' }}</span>
                        </a>
                        <a 
                                v-else
                                :id="isNaN(collection.id) ? collection.id : 'collection-id-' + collection.id"
                                :href="collection.url"
                                target="_blank">
                            <div class="collection-items-grid">
                                <img 
                                    :src="
                                        collectionItems[collection.id][0] && collectionItems[collection.id][0].thumbnail && collectionItems[collection.id][0].thumbnail['tainacan-medium'][0] && collectionItems[collection.id][0].thumbnail['tainacan-medium'][0] 
                                            ?
                                        collectionItems[collection.id][0].thumbnail['tainacan-medium'][0] 
                                            :
                                        (collectionItems[collection.id][0] && collectionItems[collection.id][0].thumbnail && collectionItems[collection.id][0].thumbnail['thumbnail'][0] && collectionItems[collection.id][0].thumbnail['thumbnail'][0]
                                            ?    
                                        collectionItems[collection.id][0].thumbnail['thumbnail'][0] 
                                            : 
                                        `${tainacanBaseUrl}/admin/images/placeholder_square.png`)
                                    "
                                    :alt="collectionItems[collection.id][0] && collectionItems[collection.id][0].name ? collectionItems[collection.id][0].name : $root.__( 'Thumbnail', 'tainacan' ) ">
                                <img
                                    :src=" 
                                        collectionItems[collection.id][1] && collectionItems[collection.id][1].thumbnail && collectionItems[collection.id][1].thumbnail['tainacan-medium'][0] && collectionItems[collection.id][1].thumbnail['tainacan-medium'][0] 
                                            ?
                                        collectionItems[collection.id][1].thumbnail['tainacan-medium'][0] 
                                            :
                                        (collectionItems[collection.id][1] && collectionItems[collection.id][1].thumbnail && collectionItems[collection.id][1].thumbnail['thumbnail'][0] && collectionItems[collection.id][1].thumbnail['thumbnail'][0]
                                            ?    
                                        collectionItems[collection.id][1].thumbnail['thumbnail'][0] 
                                            : 
                                        `${tainacanBaseUrl}/admin/images/placeholder_square.png`)
                                    "
                                    :alt="collectionItems[collection.id][1] && collectionItems[collection.id][1].name ? collectionItems[collection.id][1].name : $root.__( 'Thumbnail', 'tainacan' ) ">
                                <img
                                    :src=" 
                                        collectionItems[collection.id][2] && collectionItems[collection.id][2].thumbnail && collectionItems[collection.id][2].thumbnail['tainacan-medium'][0] && collectionItems[collection.id][2].thumbnail['tainacan-medium'][0] 
                                            ?
                                        collectionItems[collection.id][2].thumbnail['tainacan-medium'][0] 
                                            :
                                        (collectionItems[collection.id][2] && collectionItems[collection.id][2].thumbnail && collectionItems[collection.id][2].thumbnail['thumbnail'][0] && collectionItems[collection.id][2].thumbnail['thumbnail'][0]
                                            ?    
                                        collectionItems[collection.id][2].thumbnail['thumbnail'][0] 
                                            : 
                                        `${tainacanBaseUrl}/admin/images/placeholder_square.png`)
                                    "
                                    :alt="collectionItems[collection.id][2] && collectionItems[collection.id][2].name ? collectionItems[collection.id][2].name : $root.__( 'Thumbnail', 'tainacan' ) ">
                            </div>
                            <span v-if="!hideName">{{ collection.name ? collection.name : '' }}</span>
                        </a>
                    </swiper-slide>
                </swiper>
                <button 
                        class="swiper-button-prev" 
                        :id="blockId + '-prev'" 
                        slot="button-prev"
                        :style="hideName ? 'top: calc(50% - 21px)' : 'top: calc(50% - 42px)'">
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
                        :style="hideName ? 'top: calc(50% - 21px)' : 'top: calc(50% - 42px)'">
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
                {{ $root.__('No collections found.', 'tainacan') }}
            </div>
            <!-- Swiper buttons are hidden as they actually swipe from slide to slide -->
        </div>
        <div v-else-if="isLoading && !autoPlay && !loopSlides">
            <div :class="'tainacan-carousel has-arrows-' + arrowsPosition">
                <swiper 
                        role="list"
                        ref="myCollectionSwiper"
                        :options="swiperOptions">
                    <swiper-slide 
                            role="listitem"
                            :key="index"
                            v-for="(collection, index) of 18"
                            class="collection-list-item skeleton">      
                        <a>
                            <img>
                            <span v-if="!hideName" />
                        </a>
                    </swiper-slide>
                </swiper>
                <button 
                        class="swiper-button-prev" 
                        :id="blockId + '-prev'" 
                        slot="button-prev"
                        :style="hideName ? 'top: calc(50% - 21px)' : 'top: calc(50% - 42px)'">
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
                        :style="hideName ? 'top: calc(50% - 21px)' : 'top: calc(50% - 42px)'">
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
    name: "CarouselCollectionsListTheme",
    data() {
        return {
            collections: [],
            collectionItems: {},
            collection: undefined,
            collectionsRequestSource: undefined,
            isLoading: false,
            isLoadingCollection: false,
            localMaxCollectionsNumber: undefined,
            localOrder: undefined,
            tainacanAxios: undefined,
            paged: undefined,
            totalCollections: 0,
            swiperOptions: {
                mousewheel: true,
                observer: true,
                preventInteractionOnTransition: true,
                allowClick: true,
                allowTouchMove: true, 
                slidesPerView: this.showCollectionThumbnail ? 6 : 5,
                slidesPerGroup: 1,
                spaceBetween: this.showCollectionThumbnail ? 32 : 16,
                slideToClickedSlide: true,
                navigation: {
                    nextEl: '#' + this.blockId + '-next',
                    prevEl: '#' + this.blockId + '-prev',
                },
                breakpoints: {
                    498:  { slidesPerView: this.showCollectionThumbnail ? 1 : 1 },
                    768:  { slidesPerView: this.showCollectionThumbnail ? 2 : 1 },
                    1024: { slidesPerView: this.showCollectionThumbnail ? 3 : 2 },
                    1366: { slidesPerView: this.showCollectionThumbnail ? 4 : 3 },
                    1600: { slidesPerView: this.showCollectionThumbnail ? 5 : 4 },
                },
                autoplay: this.autoPlay ? { delay: this.autoPlaySpeed*1000 } : false,
                loop: this.loopSlides
            },
            errorMessage: 'No collections found.'
        }
    },
    components: {
        swiper,
        swiperSlide
    },
    props: {
        blockId: String,
        selectedCollections: Array,
        maxCollectionsNumber: Number,
        arrowsPosition: String,
        autoPlay: false,
        autoPlaySpeed: Number,
        loopSlides: Boolean,
        hideName: Boolean,
        showCollectionThumbnail: Boolean,
        tainacanApiRoot: String,
        tainacanBaseUrl: String,
        className: String
    },
    methods: {
        fetchCollections() {
 
            this.isLoading = true;
            this.errorMessage = 'No collections found.';
            
            if (this.collectionsRequestSource != undefined && typeof this.collectionsRequestSource == 'function')
                this.collectionsRequestSource.cancel('Previous collections search canceled.');

            this.collectionsRequestSource = axios.CancelToken.source();

            let endpoint = '/collections?'+ qs.stringify({ postin: this.selectedCollections, perpage: this.selectedCollections.length }) + '&fetch_only=name,url,thumbnail';

            this.tainacanAxios.get(endpoint, { cancelToken: this.collectionsRequestSource.token })
                .then(response => {

                    if (this.showCollectionThumbnail) {
                        for (let collection of response.data)
                            this.collections.push(collection);

                        this.isLoading = false;
                    } else {
                        let promises = [];
                        for (let collection of response.data) {  
                            promises.push(
                                this.tainacanAxios.get('/collection/' + collection.id + '/items?perpage=3&fetch_only=name,url,thumbnail')
                                    .then(response => { return({ collectionId: collection.id, collectionItems: response.data.items }) })
                            );    
                            this.collections.push(collection);                  
                        }
                        axios.all(promises).then((results) => {
                            for (let result of results) {
                                this.collectionItems[result.collectionId] = result.collectionItems;
                            }
                            
                            this.isLoading = false;
                        }) 
                    }
                    
                    this.totalCollections = response.headers['x-wp-total'];

                }).catch((error) => { 
                    this.isLoading = false;
                     if (error.response && error.response.status && error.response.status == 401)
                            this.errorMessage = 'Not allowed to see these collections.'

                });
            
        },
    },
    created() {
        this.tainacanAxios = axios.create({ baseURL: this.tainacanApiRoot });
        this.fetchCollections();
    },
}
</script>

<style lang="scss">

    @import './carousel-collections-list.scss';

</style>
