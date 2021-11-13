<template>
    <transition name="filter-item">
        <div v-if="filterTags != undefined && filterTags.length > 0">
            <p class="filter-tags-info">
                <span style="margin-right: 1em">
                    <strong>{{ totalItems }}</strong>
                    {{ ' ' + ( totalItems == 1 ? $i18n.get('info_item_found') : $i18n.get('info_items_found') ) }}
                </span>
                <span>
                    <strong>{{ filterTags.length }}</strong>
                    {{ ' ' + ( filterTags.length == 1 ? $i18n.get('info_applied_filter') : $i18n.get('info_applied_filters') ) }}
                    &nbsp;
                    <a 
                            @click="clearAllFilters()"
                            id="button-clear-all">
                        {{ $i18n.get('label_clear_filters') }}
                    </a>
                </span>
            </p>
            <swiper 
                        role="list"
                        ref="tainacanFilterTagsSwiper"
                        :options="swiperOptions">
                <swiper-slide 
                        v-for="(filterTag, index) of filterTags"
                        :key="index"
                        class="filter-tag"
                        :class="{ 'is-readonly': !filterTag.filterId && filterTag.argType != 'postin' }">
                    <span class="">
                        <div class="filter-tag-metadatum-name">
                            {{ filterTag.metadatumName }}
                        </div>
                        <div
                                class="filter-tag-metadatum-value"
                                v-html="filterTag.singleLabel != undefined ? filterTag.singleLabel : filterTag.label"/>
                    </span>
                    <a
                            v-if="filterTag.filterId || filterTag.argType == 'postin'"
                            role="button"
                            tabindex="0"
                            class="tag is-delete"
                            @click="removeMetaQuery(filterTag)" />
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

                tags.forEach( tag => {
                    if (Array.isArray(tag.label)) {
                        for (let i = 0; i < tag.label.length; i++) {
                            flattenTags.push({
                                singleLabel: tag.label[i],
                                value: tag.value[i],
                                filterId: tag.filterId,
                                label: tag.argType != 'postin' ? tag.label : ( tag.label + ' ' + (tag.label == 1 ? this.$i18n.get('item') : this.$i18n.get('items') )),
                                taxonomy: tag.taxonomy,
                                metadatumName: this.getMetadatumName(tag),
                                metadatumId: tag.metadatumId,
                                argType: tag.argType
                            });
                        }
                    } else {
                        flattenTags.push({
                            value: tag.value,
                            filterId: tag.filterId,
                            label: tag.argType != 'postin' ? tag.label : ( tag.label + ' ' + (tag.label == 1 ? this.$i18n.get('item') : this.$i18n.get('items') )),
                            taxonomy: tag.taxonomy,
                            metadatumName: this.getMetadatumName(tag),
                            metadatumId: tag.metadatumId,
                            argType: tag.argType
                        });
                    }
                });
                
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
            removeMetaQuery({ filterId, value, singleLabel, label, taxonomy, metadatumId, metadatumName, argType }) {
                this.$eventBusSearch.resetPageOnStore();
                this.$eventBusSearch.removeMetaFromFilterTag({ 
                    filterId: filterId,
                    singleLabel: singleLabel,
                    label: label,
                    value: value, 
                    taxonomy: taxonomy,
                    metadatumId: metadatumId,
                    metadatumName:metadatumName,
                    argType: argType
                });
            },
            clearAllFilters() {
                this.$eventBusSearch.resetPageOnStore();
                this.$eventBusSearch.clearAllFilters();
            },
            getMetadatumName(tag) {
                if (tag.argType == 'postin')
                    return this.$i18n.get('label_items_selection');
                else if (tag.argType == 'collection')
                    return this.$i18n.get('collection');
                else
                 return tag.metadatumName;
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

        .filter-tags-info {
            margin: 0 1.25em 4px 0;
            white-space: nowrap;
            display: flex;
            flex-direction: column;
        }

        .filter-tag {
            border-radius: 3px;
            padding: 3px 8px 2px 8px;
            position: relative;
            background-color: var(--tainacan-input-background-color);
            border: solid 1px var(--tainacan-input-border-color);
            margin-bottom: 0 !important;
            margin-right: 4px !important;
            max-width: calc(100% - 21px);
            animation-name: appear;
            animation-duration: 0.3s;

            .filter-tag-metadatum-name {
                font-size: 0.9375em;
                white-space: nowrap;
                padding-right: 20px;
            }
            .filter-tag-metadatum-value {
                font-size: 1.0625em;
                font-weight: 500;
                white-space: nowrap;
            }

            .tag.is-delete {
                position: absolute;
                right: 2px;
                top: 2px;
                border-radius: 50px;

                &:not(:hover) {
                    background-color: transparent;
                }
            }
            &.is-readonly {
                border-style: dashed;
            }
        }

        .swiper-container {
            width: 100%;
            position: relative;
            margin: 0;
            --swiper-navigation-size: 2em;
            --swiper-navigation-color: var(--tainacan-secondary);

            .swiper-slide {
                width: auto;
            }
            .swiper-button-next,
            .swiper-button-prev {
                padding: 34px 26px;
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
                background-image: linear-gradient(90deg, var(--tainacan-background-color) 0%, rgba(255,255,255,0) 60%);
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
            white-space: nowrap;
        }

        @media only screen and (max-width: 768px) { 
            padding-top: 1em;
            flex-wrap: wrap;

            .filter-tags-info {
                margin: 0 0 4px 0;
                flex-direction: row;
                justify-content: space-between;
            }
            .swiper-container {
                margin-top: 1em;

                .swiper-button-next,
                .swiper-container-rtl .swiper-button-prev {
                    padding-right: 8px;
                }
                .swiper-button-prev,
                .swiper-container-rtl .swiper-button-next {
                    padding-left: 8px;
                }
            }
        }
    }

</style>
