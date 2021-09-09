<template>
    <transition name="filter-item">
        <div v-if="filterTags != undefined && filterTags.length > 0">
            <p style="margin-bottom: 0;">
                <strong>{{ totalItems }}</strong>
                {{ ' ' + ( totalItems == 1 ? $i18n.get('info_item_found') : $i18n.get('info_items_found') ) + ', ' }}
                <strong>{{ filterTags.length }}</strong>
                {{ ' ' + ( filterTags.length == 1 ? $i18n.get('info_applied_filter') : $i18n.get('info_applied_filters') ) + ': ' }}
                &nbsp;&nbsp;
            </p>
            <swiper 
                        role="list"
                        ref="tainacanFilterTagsSwiper"
                        :options="swiperOptions">
                <swiper-slide 
                        v-for="(filterTag, index) of filterTags"
                        :key="index">
                    <b-tag
                            attached
                            closable
                            @close="removeMetaQuery(filterTag)">
                        {{ filterTag.singleLabel != undefined ? filterTag.singleLabel : filterTag.label }}
                    </b-tag>
                </swiper-slide>
                <button 
                        class="swiper-button-prev" 
                        id="tainacan-filter-tags-prev" 
                        slot="button-prev">
                    <svg
                            width="24"
                            height="24"
                            viewBox="0 0 24 24">
                        <path d="M15.41 7.41L14 6l-6 6 6 6 1.41-1.41L10.83 12z"/>
                        <path
                                d="M0 0h24v24H0z"
                                fill="none"/>
                    </svg>
                </button>
                <button 
                        class="swiper-button-next" 
                        id="tainacan-filter-tags-next" 
                        slot="button-next">
                    <svg
                            width="24"
                            height="24"
                            viewBox="0 0 24 24">
                        <path d="M10 6L8.59 7.41 13.17 12l-4.58 4.59L10 18l6-6z"/>
                        <path
                                d="M0 0h24v24H0z"
                                fill="none"/>
                    </svg>
                </button>
            </swiper>
            <button 
                    @click="clearAllFilters()"
                    id="button-clear-all"
                    class="button is-outlined">
                {{ $i18n.get('label_clear_filters') }}
            </button>
        </div>
    </transition>
</template>

<script>
    import { mapGetters } from 'vuex';
    import { Swiper, SwiperSlide } from 'vue-awesome-swiper';

    export default {
        name: 'FiltersTagsList',
        components: {
            Swiper,
            SwiperSlide
        },
        data() {
            return {
                swiperOptions: {
                    watchOverflow: true,
                    mousewheel: true,
                    observer: true,
                    preventInteractionOnTransition: true,
                    allowClick: true,
                    allowTouchMove: true,
                    slideToClickedSlide: true,
                    slidesPerView: 'auto',
                    navigation: {
                        nextEl: '#tainacan-filter-tags-next',
                        prevEl: '#tainacan-filter-tags-prev',
                    }
                },
            }
        },
        computed: {
            filterTags() {
                let tags = this.getFilterTags();
                
                let flattenTags = [];
                for (let tag of tags) {
                    if (Array.isArray(tag.label)) {
                        for (let i = 0; i < tag.label.length; i++) 
                            flattenTags.push({filterId: tag.filterId, label: tag.label, singleLabel: tag.label[i], value: tag.value[i], taxonomy: tag.taxonomy, metadatumId: tag.metadatumId}); 
                    } else {
                        flattenTags.push(tag);
                    }
                }
                return flattenTags;
            },
            totalItems() {
                return this.getTotalItems()
            }
        },
        methods: {
            ...mapGetters('search',[
                'getFilterTags',
                'getTotalItems'
            ]),
            removeMetaQuery({ filterId, value, singleLabel, label, taxonomy, metadatumId }) {
                this.$eventBusSearch.resetPageOnStore();
                this.$eventBusSearch.removeMetaFromFilterTag({ 
                    filterId: filterId,
                    singleLabel: singleLabel,
                    label: label,
                    value: value, 
                    taxonomy: taxonomy,
                    metadatumId: metadatumId 
                });
            },
            clearAllFilters() {
                this.$eventBusSearch.resetPageOnStore();
                for (let tag of this.filterTags) {
                    this.removeMetaQuery(tag);
                }
            }
        }
    }
</script>

<style lang="scss" scoped>

    .filter-tags-list {
        width: 100%;
        padding: 1em var(--tainacan-one-column) 1em var(--tainacan-one-column);
        font-size: 0.75em;
        display: flex;
        justify-content: flex-start;
        align-items: baseline;

        @media only screen and (max-width: 768px) { 
            padding-top: 1em;
            flex-wrap: wrap;
        }
        &>p {
            margin: 0
        }

        .swiper-container {
            position: relative;
            margin: 1em 0 0.5em 0;
            --swiper-navigation-size: 2em;
            --swiper-navigation-color: var(--tainacan-secondary);

            .swiper-slide {
                width: auto;
            }
            .swiper-button-next,
            .swiper-button-prev {
                padding: 24px 32px;
                border: none;
                background-color: transparent;
                position: absolute;
                top: 0;
                bottom: 0;
            }
            .swiper-button-prev::after,
            .swiper-container-rtl .swiper-button-next::after {
                content: 'previous';
            }
            .swiper-button-next,
            .swiper-container-rtl .swiper-button-prev {
                right: 0;
                background-image: linear-gradient(90deg, rgba(255,255,255,0) 0%, var(--tainacan-background-color) 40%);
            }
            .swiper-button-prev,
            .swiper-container-rtl .swiper-button-next {
                left: 0;
                background-image: linear-gradient(90deg, var(--tainacan-background-color) 50%, rgba(255,255,255,0) 0%);
            }
            .swiper-button-next.swiper-button-disabled,
            .swiper-button-prev.swiper-button-disabled {
                display: none;
                visibility: hidden;
            }
            .swiper-button-next::after,
            .swiper-button-prev::after {
                font-family: "TainacanIcons";
                opacity: 0.7;
                transition: opacity ease 0.2s;
            }
        }

        #button-clear-all {
            margin-left: auto;
            font-size: 1em !important;
        }
    }

</style>
