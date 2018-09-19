<template>
    <div>
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
                @keyup.left="slideIndex > 0 ? prevSlide() : null"
                @keyup.right="slideIndex < items.length - 1 ? nextSlide() : null">
            <div class="table-wrapper">
                <!-- Empty result placeholder -->
                <section
                        v-if="!isLoading && items.length <= 0"
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

                <!-- SLIDE MAIN VIEW-->
                <section class="tainacan-slide-main-view">
                    <button 
                            ref="prevArrow"
                            id='prevArrow'
                            @click="prevSlide()"
                            :style="{ visibility: slideIndex > 0 ? 'visible' : 'hidden' }"
                            class="slide-control-arrow arrow-left">
                        <span class="icon is-large">
                            <icon class="mdi mdi-48px mdi-chevron-left"/>
                        </span> 
                    </button>
                    <div class="slide-main-content">

                        <transition 
                                mode="out-in"
                                :name="goingRight ? 'slide-right' : 'slide-left'" >
                            <span 
                                    v-if="isLoadingItem"
                                    class="icon is-large loading-icon">
                                <div class="is-large control has-icons-right is-loading is-clearfix" />
                            </span>
                            <div 
                                    v-if="!isLoadingItem && (item.document != undefined && item.document != null)"
                                    v-html="item.document_as_html" />  
                            <div v-else>
                                <img 
                                        :alt="$i18n.get('label_document_empty')" 
                                        :src="thumbPlaceholderPath">
                            </div>
                        </transition>
                    </div>
                    <button 
                            ref="nextArrow"
                            id='nextArrow'
                            @click="nextSlide()"
                            :style="{ visibility: slideIndex < items.length - 1 ? 'visible' : 'hidden' }"
                            class="slide-control-arrow arrow-right">
                        <span class="icon is-large has-text-turoquoise5">
                            <icon class="mdi mdi-48px mdi-chevron-right"/>
                        </span>
                    </button>
                </section>
                <section 
                        v-if="items[slideIndex] != undefined"
                        class="slide-title-area">
                    <h1>{{ items[slideIndex].title }}</h1>
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
                <div 
                        class="is-hidden-mobile"
                        id="tainacan-slide-container">
                    <a 
                            @click="slideIndex = index"
                            :key="index"
                            v-for="(item, index) of items"
                            class="tainacan-slide-item"
                            :class="{'active-item': slideIndex == index}"
                            @keyup.left="slideIndex > 0 ? prevSlide() : null"
                            @keyup.right="slideIndex < items.length - 1 ? nextSlide() : null">

                        <!-- thumbnail -->  
                        <div 
                                class="thumbnail"
                                v-if="item.thumbnail != undefined">
                            <img :src="item['thumbnail']['tainacan_small'] ? item['thumbnail']['tainacan_small'] : (item['thumbnail'].thumb ? item['thumbnail'].thumb : thumbPlaceholderPath)">  
                        </div>           
                        
                    </a>
                </div>
            </div> 
        </div>
    </div>
</template>

<script>
import {mapActions, mapGetters} from 'vuex'

export default {
    name: 'ViewModeSlide',
    props: {
        collectionId: Number,
        displayedMetadata: Array,
        items: Array
    },
    data () {
        return {
            isLoading: false,
            goingRight: true,
            isPlaying: false,
            slideTimeout: 5000, 
            intervalId: 0, 
            collapseAll: false,
            isLoadingItem: false,
            isMetadataCompressed: true,
            slideIndex: 0,
            thumbPlaceholderPath: tainacan_plugin.base_url + '/admin/images/placeholder_square.png'
        }
    },
    computed: {
        item() {
            return this.getItem();
        }
    },
    watch: {
        slideIndex() {
            if (this.items && this.items[this.slideIndex] && this.items[this.slideIndex].id != undefined) {
                
                this.isLoadingItem = true;

                this.fetchItem(this.items[this.slideIndex].id)
                    .then(() => {
                        this.isLoadingItem = false;
                    })
                    .catch(() => {
                        this.isLoadingItem = false;
                    });
            }
        }, 
        isPlaying() {
            if (this.isPlaying) {
                this.intervalId = setInterval(() => {
                    this.$refs.nextArrow.click()
                }, this.slideTimeout)
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
            'getItem',
        ]),
        nextSlide() {
            this.goingRight = true;
            if (this.slideIndex < this.items.length - 1)
                this.slideIndex++;
            else
                this.isPlaying = false;
        },
        prevSlide() {
            this.goingRight = false;
            if (this.slideIndex > 0)
                this.slideIndex--;
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

        let carrousel = jQuery('#tainacan-slide-container');
        carrousel.slick({
            acessibility: false,
            arrows: true,
            centerMode: true,
            focusOnSelect: true,
            infinite: false,
            slidesToShow: 12,
            slidesToScroll: 12,
            nextArrow: '#nextArrow',
            prevArrow: '#prevArrow',
            responsive: [
                {
                    breakpoint: 1408,
                    settings: {
                        slidesToShow: 9,
                        slidesToScroll: 9,
                    }
                },
                {
                    breakpoint: 1024,
                    settings: {
                        slidesToShow: 6,
                        slidesToScroll: 6,
                    }
                },
                {
                    breakpoint: 768,
                    settings: {
                        slidesToShow: 3,
                        slidesToScroll: 3,
                    }
                },
                {
                    breakpoint: 348,
                    settings: {
                        slidesToShow: 2,
                        slidesToScroll: 2,
                    }
                }
            ]
        });
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


