<template>
    <div 
            :style="style"
            :class="className + ' has-mounted'">
        <div v-if="!isLoading">
            <div  
                    :class="'tainacan-carousel ' + (arrowsPosition ? ' has-arrows-' + arrowsPosition : '') + (largeArrows ? ' has-large-arrows' : '')"
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
                                <blur-hash-image
                                        :height="termItems[term.id][2] ? $thumbHelper.getHeight(termItems[term.id][0]['thumbnail'], 'tainacan-medium') : 275"
                                        :width="termItems[term.id][0] ? $thumbHelper.getWidth(termItems[term.id][0]['thumbnail'], 'tainacan-medium') : 275"
                                        :src="termItems[term.id][0] ? $thumbHelper.getSrc(termItems[term.id][0]['thumbnail'], 'tainacan-medium', termItems[term.id][0]['document_mimetype']) :`${tainacanBaseUrl}/assets/images/placeholder_square.png`"
                                        :srcset="termItems[term.id][0] ? $thumbHelper.getSrcSet(termItems[term.id][0]['thumbnail'], 'tainacan-medium', termItems[term.id][0]['document_mimetype']) :`${tainacanBaseUrl}/assets/images/placeholder_square.png`"
                                        :hash="termItems[term.id][0] ? $thumbHelper.getBlurhashString(termItems[term.id][0]['thumbnail'], 'tainacan-medium') : 'V4P?:h00Rj~qM{of%MRjWBRjD%%MRjayofj[%M-;RjRj'"
                                        :alt="termItems[term.id][0] && termItems[term.id][0].thumbnail_alt ? termItems[term.id][0].thumbnail_alt : (termItems[term.id][0] && termItems[term.id][0].name ? termItems[term.id][0].name : $root.__( 'Thumbnail', 'tainacan' ))"
                                        :transition-duration="500" />
                                <blur-hash-image
                                        :height="termItems[term.id][1] ? $thumbHelper.getHeight(termItems[term.id][1]['thumbnail'], 'tainacan-medium') : 275"
                                        :width="termItems[term.id][1] ? $thumbHelper.getWidth(termItems[term.id][1]['thumbnail'], 'tainacan-medium') : 275"
                                        :src="termItems[term.id][1] ? $thumbHelper.getSrc(termItems[term.id][1]['thumbnail'], 'tainacan-medium', termItems[term.id][1]['document_mimetype']) :`${tainacanBaseUrl}/assets/images/placeholder_square.png`"
                                        :srcset="termItems[term.id][1] ? $thumbHelper.getSrcSet(termItems[term.id][1]['thumbnail'], 'tainacan-medium', termItems[term.id][1]['document_mimetype']) :`${tainacanBaseUrl}/assets/images/placeholder_square.png`"
                                        :hash="termItems[term.id][1] ? $thumbHelper.getBlurhashString(termItems[term.id][1]['thumbnail'], 'tainacan-medium') : 'V4P?:h00Rj~qM{of%MRjWBRjD%%MRjayofj[%M-;RjRj'"
                                        :alt="termItems[term.id][1] && termItems[term.id][1].thumbnail_alt ? termItems[term.id][1].thumbnail_alt : (termItems[term.id][1] && termItems[term.id][1].name ? termItems[term.id][1].name : $root.__( 'Thumbnail', 'tainacan' ))"
                                        :transition-duration="500" />
                                <blur-hash-image
                                        :height="termItems[term.id][2] ? $thumbHelper.getHeight(termItems[term.id][2]['thumbnail'], 'tainacan-medium') : 275"
                                        :width="termItems[term.id][2] ? $thumbHelper.getWidth(termItems[term.id][2]['thumbnail'], 'tainacan-medium') : 275"
                                        :src="termItems[term.id][2] ? $thumbHelper.getSrc(termItems[term.id][2]['thumbnail'], 'tainacan-medium', termItems[term.id][2]['document_mimetype']) :`${tainacanBaseUrl}/assets/images/placeholder_square.png`"
                                        :srcset="termItems[term.id][2] ? $thumbHelper.getSrcSet(termItems[term.id][2]['thumbnail'], 'tainacan-medium', termItems[term.id][2]['document_mimetype']) :`${tainacanBaseUrl}/assets/images/placeholder_square.png`"
                                        :hash="termItems[term.id][2] ? $thumbHelper.getBlurhashString(termItems[term.id][2]['thumbnail'], 'tainacan-medium') : 'V4P?:h00Rj~qM{of%MRjWBRjD%%MRjayofj[%M-;RjRj'"
                                        :alt="termItems[term.id][2] && termItems[term.id][2].thumbnail_alt ? termItems[term.id][2].thumbnail_alt : (termItems[term.id][2] && termItems[term.id][2].name ? termItems[term.id][2].name : $root.__( 'Thumbnail', 'tainacan' ))"
                                        :transition-duration="500" />
                            </div>
                            <span v-if="!hideName">{{ term.name ? term.name : '' }}</span>
                        </a>
                    </swiper-slide>
                </swiper>
                <button 
                        class="swiper-button-prev" 
                        :id="blockId + '-prev'" 
                        slot="button-prev"
                        :style="hideName ? 'top: calc(50% - 21px)' : 'top: calc(50% - ' + (largeArrows ? '60' : '42') + 'px)'">
                    <svg
                            :width="largeArrows ? 60 : 42"
                            :height="largeArrows ? 60 : 42"
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
                        :style="hideName ? 'top: calc(50% - 21px)' : 'top: calc(50% - ' + (largeArrows ? '60' : '42') + 'px)'">
                    <svg
                            :width="largeArrows ? 60 : 42"
                            :height="largeArrows ? 60 : 42"
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
            <div :class="'tainacan-carousel ' + (arrowsPosition ? ' has-arrows-' + arrowsPosition : '') + (largeArrows ? ' has-large-arrows' : '') ">
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
                        :style="hideName ? 'top: calc(50% - 21px)' : 'top: calc(50% - ' + (largeArrows ? '60' : '42') + 'px)'">
                    <svg
                            :width="largeArrows ? 60 : 42"
                            :height="largeArrows ? 60 : 42"
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
                        :style="hideName ? 'top: calc(50% - 21px)' : 'top: calc(50% - ' + (largeArrows ? '60' : '42') + 'px)'">
                    <svg
                            :width="largeArrows ? 60 : 42"
                            :height="largeArrows ? 60 : 42"
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
import 'swiper/css/swiper.min.css';
import { Swiper, SwiperSlide } from 'vue-awesome-swiper';

export default {
    name: "CarouselTermsListTheme",
    components: {
        Swiper,
        SwiperSlide
    },
    props: {
        blockId: String,
        selectedTerms: Array,
        maxTermsNumber: Number,
        arrowsPosition: String,
        autoPlay: false,
        autoPlaySpeed: Number,
        loopSlides: Boolean,
        maxTermsPerScreen: Number,
        hideName: Boolean,
        largeArrows: Boolean,
        showTermThumbnail: Boolean,
        tainacanApiRoot: String,
        tainacanBaseUrl: String,
        className: String,
        taxonomyId: String,
        style: String
    },
    data() {
        return {
            terms: [],
            termItems: {},
            termsRequestSource: undefined,
            isLoading: false,
            isLoadingTerm: false,
            localMaxTermsNumber: undefined,
            localOrder: undefined,
            tainacanAxios: undefined,
            paged: undefined,
            totalTerms: 0,
            swiperOptions: {
                watchOverflow: true,
                mousewheel: true,
                observer: true,
                preventInteractionOnTransition: true,
                allowClick: true,
                allowTouchMove: true, 
                slidesPerView: 1,
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
        if (tainacan_blocks && tainacan_blocks.nonce)
            this.tainacanAxios.defaults.headers.common['X-WP-Nonce'] = tainacan_blocks.nonce;
            
        this.fetchTerms();

        if (!isNaN(this.maxTermsPerScreen)) {
            this.swiperOptions.breakpoints = {
                498:  { slidesPerView: this.maxTermsPerScreen - 4 > 0 ? this.maxTermsPerScreen - 4 : 1 }, 
                768:  { slidesPerView: this.maxTermsPerScreen - 3 > 0 ? this.maxTermsPerScreen - 3 : 1 },
                1024: { slidesPerView: this.maxTermsPerScreen - 2 > 0 ? this.maxTermsPerScreen - 2 : 1 },
                1366: { slidesPerView: this.maxTermsPerScreen - 1 > 0 ? this.maxTermsPerScreen - 1 : 1 },
                1600: { slidesPerView: this.maxTermsPerScreen > 0 ? this.maxTermsPerScreen : 1 },
            }
            this.swiperOptions.slidesPerView = 1;
        }
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

    @import './style.scss';

</style>
