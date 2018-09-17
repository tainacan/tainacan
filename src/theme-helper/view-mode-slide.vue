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

            <b-loading
                    :is-full-page="false"
                    :active.sync="isLoadingMetadata"/>

            <h3 class="has-text-white has-text-weight-semibold">{{ $i18n.get('metadata') }}</h3>
            
            <a
                    v-if="!isLoadingMetadata && metadata.length > 0"
                    class="collapse-all is-size-7"
                    @click="collapseAll = !collapseAll">
                {{ collapseAll ? $i18n.get('label_collapse_all') : $i18n.get('label_expand_all') }}
                <b-icon
                        type="is-secondary"
                        :icon=" collapseAll ? 'menu-down' : 'menu-right'" />
            </a>

            <br>
            <br>

        </aside>
        <div 
                class="table-container"
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
                            @click="prevSlide()"
                            :style="{ visibility: slideIndex > 0 ? 'visible' : 'hidden' }"
                            class="slide-control-arrow">
                        <span class="icon is-large">
                            <icon class="mdi mdi-48px mdi-chevron-left"/>
                        </span>
                    </button>
                    <transition 
                            mode="out-in"
                            :name="goingRight ? 'slide-right' : 'slide-left'" >
                        <div
                                :key="index"
                                v-for="(item, index) of items"
                                v-if="index == slideIndex"
                                class="slide-main-content">
                            <a :href="item.url" >
                                <img :src="item.thumbnail != undefined && item['thumbnail'].full ? item['thumbnail'].full : thumbPlaceholderPath">  
                            </a>
                        </div>
                    </transition>
                    <button 
                            @click="nextSlide()"
                            :style="{ visibility: slideIndex < items.length - 1 ? 'visible' : 'hidden' }"
                            class="slide-control-arrow">
                        <span class="icon is-large has-text-turoquoise5">
                            <icon class="mdi mdi-48px mdi-chevron-right"/>
                        </span>
                    </button>
                </section>
                <section 
                        v-if="items[slideIndex] != undefined"
                        class="slide-title-area">
                    <h1>{{ items[slideIndex].title }}</h1>
                </section>

                <!-- SLIDE ITEMS LIST -->
                <div class="tainacan-slide-container">
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
            collapseAll: false,
            isLoadingMetadata: false,
            isMetadataCompressed: true,
            metadata: [],
            slideIndex: 0,
            thumbPlaceholderPath: tainacan_plugin.base_url + '/admin/images/placeholder_square.png'
        }
    },
    methods: {
        nextSlide() {
            this.goingRight = true;
            this.slideIndex++;
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


