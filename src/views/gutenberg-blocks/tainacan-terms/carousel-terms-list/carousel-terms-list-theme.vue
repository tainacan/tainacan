<template>
    <div :class="className">
        <div v-if="!isLoading">
            <div  
                    :class="'tainacan-carousel has-arrows-' + arrowsPosition"
                    v-if="terms.length > 0">
                <swiper 
                        role="list"
                        ref="myTermSwiper"
                        :options="swiperOptions">
                    <swiper-slide 
                            role="listitem"
                            :key="index"
                            v-for="(term, index) of terms"
                            :class="'term-list-item ' + (!showTermThumbnail ? 'term-list-item-grid' : '')">      
                        <a 
                                v-if="showTermThumbnail"
                                :id="isNaN(term.id) ? term.id : 'term-id-' + term.id"
                                :href="term.url"
                                target="_blank">
                            <img
                                :src="term.header_image ? term.header_image : `${tainacanBaseUrl}/assets/images/placeholder_square.png`"
                                :alt="term.name ? term.name : $root.__('Thumbnail', 'tainacan')" >
                            <span v-if="!hideName">{{ term.name ? term.name : '' }}</span>
                        </a>
                        <a 
                                v-else
                                :id="isNaN(term.id) ? term.id : 'term-id-' + term.id"
                                :href="term.url"
                                target="_blank">
                            <div class="term-items-grid">
                                <img 
                                    :src="
                                        termItems[term.id][0] && termItems[term.id][0].thumbnail && termItems[term.id][0].thumbnail['tainacan-medium'][0] && termItems[term.id][0].thumbnail['tainacan-medium'][0] 
                                            ?
                                        termItems[term.id][0].thumbnail['tainacan-medium'][0] 
                                            :
                                        (termItems[term.id][0] && termItems[term.id][0].thumbnail && termItems[term.id][0].thumbnail['thumbnail'][0] && termItems[term.id][0].thumbnail['thumbnail'][0]
                                            ?    
                                        termItems[term.id][0].thumbnail['thumbnail'][0] 
                                            : 
                                        `${tainacanBaseUrl}/assets/images/placeholder_square.png`)
                                    "
                                    :alt="termItems[term.id][0] && termItems[term.id][0].name ? termItems[term.id][0].name : $root.__( 'Thumbnail', 'tainacan' ) ">
                                <img
                                    :src=" 
                                        termItems[term.id][1] && termItems[term.id][1].thumbnail && termItems[term.id][1].thumbnail['tainacan-medium'][0] && termItems[term.id][1].thumbnail['tainacan-medium'][0] 
                                            ?
                                        termItems[term.id][1].thumbnail['tainacan-medium'][0] 
                                            :
                                        (termItems[term.id][1] && termItems[term.id][1].thumbnail && termItems[term.id][1].thumbnail['thumbnail'][0] && termItems[term.id][1].thumbnail['thumbnail'][0]
                                            ?    
                                        termItems[term.id][1].thumbnail['thumbnail'][0] 
                                            : 
                                        `${tainacanBaseUrl}/assets/images/placeholder_square.png`)
                                    "
                                    :alt="termItems[term.id][1] && termItems[term.id][1].name ? termItems[term.id][1].name : $root.__( 'Thumbnail', 'tainacan' ) ">
                                <img
                                    :src=" 
                                        termItems[term.id][2] && termItems[term.id][2].thumbnail && termItems[term.id][2].thumbnail['tainacan-medium'][0] && termItems[term.id][2].thumbnail['tainacan-medium'][0] 
                                            ?
                                        termItems[term.id][2].thumbnail['tainacan-medium'][0] 
                                            :
                                        (termItems[term.id][2] && termItems[term.id][2].thumbnail && termItems[term.id][2].thumbnail['thumbnail'][0] && termItems[term.id][2].thumbnail['thumbnail'][0]
                                            ?    
                                        termItems[term.id][2].thumbnail['thumbnail'][0] 
                                            : 
                                        `${tainacanBaseUrl}/assets/images/placeholder_square.png`)
                                    "
                                    :alt="termItems[term.id][2] && termItems[term.id][2].name ? termItems[term.id][2].name : $root.__( 'Thumbnail', 'tainacan' ) ">
                            </div>
                            <span v-if="!hideName">{{ term.name ? term.name : '' }}</span>
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
                {{ $root.__('No terms found.', 'tainacan') }}
            </div>
            <!-- Swiper buttons are hidden as they actually swipe from slide to slide -->
        </div>
        <div v-else-if="isLoading && !autoPlay && !loopSlides">
            <div :class="'tainacan-carousel has-arrows-' + arrowsPosition">
                <swiper 
                        role="list"
                        ref="myTermSwiper"
                        :options="swiperOptions">
                    <swiper-slide 
                            role="listitem"
                            :key="index"
                            v-for="(term, index) of 18"
                            class="term-list-item skeleton">      
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
    name: "CarouselTermsListTheme",
    components: {
        swiper,
        swiperSlide
    },
    props: {
        blockId: String,
        selectedTerms: Array,
        maxTermsNumber: Number,
        arrowsPosition: String,
        autoPlay: false,
        autoPlaySpeed: Number,
        loopSlides: Boolean,
        hideName: Boolean,
        showTermThumbnail: Boolean,
        tainacanApiRoot: String,
        tainacanBaseUrl: String,
        className: String,
        taxonomyId: String
    },
    data() {
        return {
            terms: [],
            termItems: {},
            term: undefined,
            termsRequestSource: undefined,
            isLoading: false,
            isLoadingTerm: false,
            localMaxTermsNumber: undefined,
            localOrder: undefined,
            tainacanAxios: undefined,
            paged: undefined,
            totalTerms: 0,
            swiperOptions: {
                mousewheel: true,
                observer: true,
                preventInteractionOnTransition: true,
                allowClick: true,
                allowTouchMove: true, 
                slidesPerView: this.showTermThumbnail ? 6 : 5,
                slidesPerGroup: 1,
                spaceBetween: this.showTermThumbnail ? 32 : 16,
                slideToClickedSlide: true,
                navigation: {
                    nextEl: '#' + this.blockId + '-next',
                    prevEl: '#' + this.blockId + '-prev',
                },
                breakpoints: {
                    498:  { slidesPerView: this.showTermThumbnail ? 1 : 1 },
                    768:  { slidesPerView: this.showTermThumbnail ? 2 : 1 },
                    1024: { slidesPerView: this.showTermThumbnail ? 3 : 2 },
                    1366: { slidesPerView: this.showTermThumbnail ? 4 : 3 },
                    1600: { slidesPerView: this.showTermThumbnail ? 5 : 4 },
                },
                autoplay: this.autoPlay ? { delay: this.autoPlaySpeed*1000 } : false,
                loop: this.loopSlides
            },
            errorMessage: 'No terms found.'
        }
    },
    created() {
        this.tainacanAxios = axios.create({ baseURL: this.tainacanApiRoot });
        this.fetchTerms();
    },
    methods: {
        fetchTerms() {
            this.isLoading = true;
            this.errorMessage = 'No terms found.';
            
            if (this.termsRequestSource != undefined && typeof this.termsRequestSource == 'function')
                this.termsRequestSource.cancel('Previous terms search canceled.');

            this.termsRequestSource = axios.CancelToken.source();

            let endpoint = '/taxonomy/' + this.taxonomyId + '/terms/?'+ qs.stringify({ hideempty: 0, include: this.selectedTerms }) + '&order=asc&fetch_only=id,name,url,header_image';

            this.tainacanAxios.get(endpoint, { cancelToken: this.termsRequestSource.token })
                .then(response => {

                    if (this.showTermThumbnail) {
                        for (let term of response.data)
                            this.terms.push(term);

                        this.isLoading = false;
                    } else {
                        let promises = [];
                        for (let term of response.data) {  
                            promises.push(
                                this.tainacanAxios.get('/items/?perpage=3&fetch_only=name,url,thumbnail&taxquery[0][taxonomy]=tnc_tax_' + this.taxonomyId + '&taxquery[0][terms][0]=' + term.id + '&taxquery[0][compare]=IN')
                                    .then(response => { return({ termId: term.id, termItems: response.data.items }) })
                            );    
                            this.terms.push(term);                  
                        }
                        axios.all(promises).then((results) => {
                            for (let result of results) {
                                this.termItems[result.termId] = result.termItems;
                            }
                            
                            this.isLoading = false;
                        }) 
                    }
                    
                    this.totalTerms = response.headers['x-wp-total'];

                }).catch((error) => { 
                    this.isLoading = false;
                     if (error.response && error.response.status && error.response.status == 401)
                            this.errorMessage = 'Not allowed to see these terms.'

                });
            
        },
    }
}
</script>

<style lang="scss">

    @import './carousel-terms-list.scss';

</style>
