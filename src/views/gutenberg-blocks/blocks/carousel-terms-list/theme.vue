<template>
    <div 
            :style="customStyle"
            :class="className + ' has-mounted'">

        <div  
                :class="'tainacan-carousel ' + (arrowsPosition ? ' has-arrows-' + arrowsPosition : '') + (largeArrows ? ' has-large-arrows' : '')"
                :style="{ '--spaceAroundCarousel': !isNaN(spaceAroundCarousel) ? (spaceAroundCarousel + 'px') : '50px' }"
                v-if="terms.length > 0 || isLoading">
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
                            class="swiper-slide term-list-item skeleton">
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
                            v-for="(term, index) of terms"
                            :class="'swiper-slide term-list-item ' + (!showTermThumbnail ? 'term-list-item-grid' : '')">      
                        <a 
                                v-if="showTermThumbnail"
                                :id="isNaN(term.id) ? term.id : 'term-id-' + term.id"
                                :href="term.url">
                            <img
                                :src=" 
                                    term.thumbnail && term.thumbnail[imageSize] && term.thumbnail[imageSize][0] 
                                        ?
                                    term.thumbnail[imageSize][0] 
                                        :
                                    (term.thumbnail && term.thumbnail['thumbnail'] && term.thumbnail['thumbnail'][0]
                                        ?    
                                    term.thumbnail['thumbnail'][0] 
                                        : 
                                    `${tainacanBaseUrl}/assets/images/placeholder_square.png`)
                                "
                                :data-src=" 
                                    term.thumbnail && term.thumbnail[imageSize] && term.thumbnail[imageSize][0] 
                                        ?
                                    term.thumbnail[imageSize][0] 
                                        :
                                    (term.thumbnail && term.thumbnail['thumbnail'] && term.thumbnail['thumbnail'][0]
                                        ?    
                                    term.thumbnail['thumbnail'][0] 
                                        : 
                                    `${tainacanBaseUrl}/assets/images/placeholder_square.png`)
                                "
                                :alt="term.thumbnail_alt ? term.thumbnail_alt : (term.name ? term.name : $root.__('Thumbnail', 'tainacan'))" >
                            <span v-if="!hideName">{{ term.name ? term.name : '' }}</span>
                        </a>
                        <a 
                                v-else
                                :id="isNaN(term.id) ? term.id : 'term-id-' + term.id"
                                :href="term.url">
                            <div class="term-items-grid">
                                <blur-hash-image
                                        :height="termItems[term.id][0] ? $thumbHelper.getHeight(termItems[term.id][0]['thumbnail'], 'tainacan-medium') : 275"
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
                v-else-if="terms.length <= 0 && !isLoading"
                class="spinner-container">
            {{ $root.__('No terms found.', 'tainacan') }}
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
    name: "CarouselTermsListTheme",
    props: {
        blockId: String,
        selectedTerms: Array,
        maxTermsNumber: Number,
        arrowsPosition: String,
        autoPlay: false,
        autoPlaySpeed: Number,
        loopSlides: Boolean,
        maxTermsPerScreen: Number,
        spaceBetweenTerms: Number,
        spaceAroundCarousel: Number,
        hideName: Boolean,
        largeArrows: Boolean,
        arrowsStyle: String,
        imageSize: String,
        showTermThumbnail: Boolean,
        tainacanApiRoot: String,
        tainacanBaseUrl: String,
        className: String,
        taxonomyId: String,
        customStyle: String
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
            swiper: {},
            apiRoot: '',
            errorMessage: 'No terms found.'
        }
    },
    created() {
        
        this.apiRoot = (tainacan_blocks && tainacan_blocks.root && !this.tainacanApiRoot) ? tainacan_blocks.root : this.tainacanApiRoot;
            
        this.tainacanAxios = axios.create({ baseURL: this.apiRoot });
        if (tainacan_blocks && tainacan_blocks.nonce)
            this.tainacanAxios.defaults.headers.common['X-WP-Nonce'] = tainacan_blocks.nonce;
            
        this.fetchTerms();
    },
    beforeDestroy() {
        if (typeof this.swiper.destroy == 'function')
            this.swiper.destroy();
    },
    methods: {
        fetchTerms() {
            this.isLoading = true;
            this.errorMessage = 'No terms found.';

            this.$nextTick(() => {
                this.mountCarousel();
            });
            
            if (this.termsRequestSource != undefined && typeof this.termsRequestSource == 'function')
                this.termsRequestSource.cancel('Previous terms search canceled.');

            this.termsRequestSource = axios.CancelToken.source();

            let endpoint = '/taxonomy/' + this.taxonomyId + '/terms/?'+ qs.stringify({ hideempty: 0, include: this.selectedTerms, fetch_preview_image_items: this.showTermThumbnail ? 0 : 3 }) + '&order=asc';

            this.tainacanAxios.get(endpoint, { cancelToken: this.termsRequestSource.token })
                .then(response => {

                    for (let term of response.data) {
                        this.terms.push(term);
                        if (!this.showTermThumbnail)
                            this.termItems[term.id] = term.preview_image_items ? term.preview_image_items : [];
                    }
                    
                    this.isLoading = false;
                    this.totalTerms = response.headers['x-wp-total'];

                    this.$nextTick(() => {
                        this.mountCarousel();
                    });

                }).catch((error) => { 
                    this.isLoading = false;
                    if (error.response && error.response.status && error.response.status == 401)
                        this.errorMessage = 'Not allowed to see these terms.'

                });
        },
        mountCarousel() {
            const self = this;
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
                spaceBetween: self.spaceBetweenTerms,
                slideToClickedSlide: true,
                navigation: {
                    nextEl: '#' + self.blockId + '-next',
                    prevEl: '#' + self.blockId + '-prev',
                },
                breakpoints: !isNaN(self.maxTermsPerScreen) ? {
                    498:  { slidesPerView: self.maxTermsPerScreen - 4 > 0 ? self.maxTermsPerScreen - 4 : 1, spaceBetween: self.spaceBetweenTerms }, 
                    768:  { slidesPerView: self.maxTermsPerScreen - 3 > 0 ? self.maxTermsPerScreen - 3 : 1, spaceBetween: self.spaceBetweenTerms },
                    1024: { slidesPerView: self.maxTermsPerScreen - 2 > 0 ? self.maxTermsPerScreen - 2 : 1, spaceBetween: self.spaceBetweenTerms },
                    1366: { slidesPerView: self.maxTermsPerScreen - 1 > 0 ? self.maxTermsPerScreen - 1 : 1, spaceBetween: self.spaceBetweenTerms },
                    1600: { slidesPerView: self.maxTermsPerScreen > 0 ? self.maxTermsPerScreen : 1, spaceBetween: self.spaceBetweenTerms },
                } : {
                    498:  { slidesPerView: self.showTermThumbnail ? 1 : 1, spaceBetween: self.spaceBetweenTerms },
                    768:  { slidesPerView: self.showTermThumbnail ? 2 : 1, spaceBetween: self.spaceBetweenTerms },
                    1024: { slidesPerView: self.showTermThumbnail ? 3 : 2, spaceBetween: self.spaceBetweenTerms },
                    1366: { slidesPerView: self.showTermThumbnail ? 4 : 3, spaceBetween: self.spaceBetweenTerms },
                    1600: { slidesPerView: self.showTermThumbnail ? 5 : 4, spaceBetween: self.spaceBetweenTerms },
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
