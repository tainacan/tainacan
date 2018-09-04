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
            <div class="tainacan-records-container">
                <a 
                        :href="item.url"
                        :key="index"
                        v-for="(item, index) of items"
                        class="tainacan-record">
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

                        <!-- Remaining metadata -->  
                        <div class="media">

                            <div class="list-metadata media-body">
                                <div 
                                        class="thumbnail"
                                        v-if="item.thumbnail != undefined">
                                    <img :src="item['thumbnail'].tainacan_full ? item['thumbnail'].tainacan_full : (item['thumbnail'].full ? item['thumbnail'].full : thumbPlaceholderPath)">  
                                </div>
                                <span 
                                        v-for="(column, index) in tableMetadata"
                                        :key="index"
                                        v-if="collectionId == undefined && column.display && column.metadata_type_object != undefined && (column.metadata_type_object.related_mapped_prop == 'description')">
                                    <h3 class="metadata-label">{{ $i18n.get('label_description') }}</h3>
                                    <p 
                                            v-html="item.description != undefined ? item.description : ''"
                                            class="metadata-value"/>
                                </span>
                                <span 
                                        v-for="(column, index) in displayedMetadata"
                                        :key="index"
                                        v-if="column.display && column.slug != 'thumbnail' && column.metadata_type_object != undefined && (column.metadata_type_object.related_mapped_prop != 'title')">
                                    <h3 class="metadata-label">{{ column.name }}</h3>
                                    <p      
                                            v-html="item.metadata != undefined ? renderMetadata(item.metadata, column) : ''"
                                            class="metadata-value"/>
                                </span>
                            </div>
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
    },
    mounted() {
        this.$modal.open(
            `<div class="main-picture">
                <img src="` + (this.items[0]['thumbnail'].full ? this.items[0]['thumbnail'].full : this.thumbPlaceholderPath) + `">  
            </div>`       
        );
    }
}
</script>

<style  lang="scss" scoped>
    $turquoise1: #e6f6f8;
    $turquoise2: #d1e6e6;
    $tainacan-input-color: #1d1d1d;
    $gray1: #f2f2f2; 
    $gray2: #e5e5e5;
    $gray3: #dcdcdc;
    $gray4: #898d8f;
    $gray5: #454647; 

    @import "../../src/admin/scss/_view-mode-records.scss";

    .tainacan-records-container .tainacan-record .metadata-title {
        padding: 0.75rem;
        margin-bottom: 0px;
    }
</style>


