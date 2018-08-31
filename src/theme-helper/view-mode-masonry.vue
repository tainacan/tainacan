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
            <!-- RECORDS VIEW MODE -->
            <masonry 
                    :cols="{default: 7, 1919: 6, 1407: 5, 1215: 4, 1023: 3, 767: 2, 343: 1}"
                    :gutter="25"
                    class="tainacan-masonry-container">
                <a 
                        :key="index"
                        v-for="(item, index) of items"
                        class="tainacan-masonry-item"
                        :href="item.url">

                    <!-- Title -->
                    <div class="metadata-title">
                        <p>{{ item.title != undefined ? item.title : '' }}</p>                             
                    </div>

                    <!-- Thumbnail -->
                    <div 
                            v-if="item.thumbnail != undefined"
                            class="thumbnail"
                            :style="{ backgroundImage: 'url(' + (item['thumbnail'].tainacan_medium_full ? item['thumbnail'].tainacan_medium_full : (item['thumbnail'].medium_large ? item['thumbnail'].medium_large : thumbPlaceholderPath)) + ')' }">  
                        <img :src="item['thumbnail'].tainacan_medium_full ? item['thumbnail'].tainacan_medium_full : (item['thumbnail'].medium_large ? item['thumbnail'].medium_large : thumbPlaceholderPath)">  
                    </div>
                </a>
            </masonry>
        </div> 
    </div>
</template>

<script>

export default {
    name: 'ViewModeMasonry',
    props: {
        collectionId: Number,
        displayedMetadata: Array,
        items: Array,
        isLoading: false
    },
    data () {
        return {
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
    $tainacan-input-color: #1d1d1d;
    $gray1: #f2f2f2;
    $gray2: #e5e5e5;
    $gray4: #898d8f;
    $gray3: #dcdcdc;

    @import "../../src/admin/scss/_view-mode-masonry.scss";

    .tainacan-masonry-container .tainacan-masonry-item .metadata-title {
        margin: 0px 6px;
    }
</style>


