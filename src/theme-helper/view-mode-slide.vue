<template>
    <div class="table-container">
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
                <transition 
                        mode="out-in"
                        name="slide" >
                    <div
                            :key="index"
                            v-for="(item, index) of items"
                            v-if="index == slideIndex">
                        <a :href="item.url" >
                            <img :src="item.thumbnail != undefined && item['thumbnail'].full ? item['thumbnail'].full : thumbPlaceholderPath">  
                        </a>
                    </div>
                </transition>
            </section>
            <!-- SLIDE ITEMS LIST -->
            <div class="tainacan-slide-container">
                <a 
                        @click="slideIndex = index"
                        :key="index"
                        v-for="(item, index) of items"
                        class="tainacan-slide-item"
                        :class="{'active-item': slideIndex == index}">
                    <!-- <div :href="item.url"> -->
                        <!-- Title -->           
                        <p 
                                v-tooltip="{
                                    content: item.metadata != undefined ? renderMetadata(item.metadata, column) : '',
                                    html: true,
                                    autoHide: false,
                                    placement: 'auto-start'
                                }"
                                v-for="(column, index) in displayedMetadata"
                                :key="index"
                                class="metadata-title"
                                v-if="collectionId != undefined && column.display && column.metadata_type_object != undefined && (column.metadata_type_object.related_mapped_prop == 'title')"
                                v-html="item.metadata != undefined ? renderMetadata(item.metadata, column) : ''" />                             
                        <p 
                                v-tooltip="{
                                    content: item.title != undefined ? item.title : '',
                                    html: true,
                                    autoHide: false,
                                    placement: 'auto-start'
                                }"
                                v-for="(column, index) in tableMetadata"
                                :key="index"
                                v-if="collectionId == undefined && column.display && column.metadata_type_object != undefined && (column.metadata_type_object.related_mapped_prop == 'title')"
                                v-html="item.title != undefined ? item.title : ''" />                             

                        <!-- thumbnail -->  
                        <div 
                                class="thumbnail"
                                v-if="item.thumbnail != undefined">
                            <img :src="item['thumbnail']['tainacan_small'] ? item['thumbnail']['tainacan_small'] : (item['thumbnail'].thumb ? item['thumbnail'].thumb : thumbPlaceholderPath)">  
                        </div>           
                        
                    </a>
                <!-- </div> -->
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
        items: Array,
        isLoading: false,
    },
    data () {
        return {
            slideIndex: 0,
            thumbPlaceholderPath: tainacan_plugin.base_url + '/admin/images/placeholder_square.png'
        }
    },
    methods: {
        goToItemPage(item) {
            window.location.href = item.url;   
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

    @import "../../src/admin/scss/_view-mode-slide.scss";

    .tainacan-records-container .tainacan-record .metadata-title {
        padding: 0.75rem;
        margin-bottom: 0px;
    }
</style>


