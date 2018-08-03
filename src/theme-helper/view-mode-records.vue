<template>
    <div class="table-container">
        <div class="table-wrapper">
            <!-- RECORDS VIEW MODE -->
            <div class="tainacan-records-container">
                <div 
                        :key="index"
                        v-for="(item, index) of items"
                        class="tainacan-record">
                    
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
                            v-if="column.display && column.metadata_type_object != undefined && (column.metadata_type_object.related_mapped_prop == 'title')"
                            class="metadata-title"
                            @click="goToItemPage(item)"
                            v-html="item.metadata != undefined ? renderMetadata(item.metadata, column) : ''" />                             
                    
                    <!-- Remaining metadata -->  
                    <div    
                            class="media"
                            @click="goToItemPage(item)">
                        <a 
                                v-if="item.thumbnail != undefined"
                                @click="goToItemPage(item)">
                            <img :src="item['thumbnail'].medium_large ? item['thumbnail'].medium_large : thumbPlaceholderPath">  
                        </a>

                        <div class="list-metadata media-body">
                            <span 
                                    v-for="(column, index) in displayedMetadata"
                                    :key="index"
                                    v-if="column.display && column.slug != 'thumbnail' && column.metadata_type_object != undefined && (column.metadata_type_object.related_mapped_prop != 'title')">
                                <h3 
                                        v-tooltip="{
                                            content: column.name,
                                            html: false,
                                            autoHide: false,
                                            placement: 'auto-start'
                                        }"
                                        class="metadata-label">{{ column.name }}</h3>
                                <p 
                                        v-tooltip="{
                                            content: item.metadata != undefined ? renderMetadata(item.metadata, column) : '',
                                            html: true,
                                            autoHide: false,
                                            placement: 'auto-start'
                                        }"
                                        v-html="item.metadata != undefined ? renderMetadata(item.metadata, column) : ''"
                                        class="metadata-value"/>
                            </span>
                        </div>
                    </div>
               
                </div>
            </div>
        </div> 
    </div>
</template>

<script>

export default {
    name: 'ViewModeRecords',
    props: {
        collectionId: Number,
        displayedMetadata: Array,
        items: Array,
        isLoading: false,
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
    $gray2: #e5e5e5;
    $gray4: #898d8f;
    $gray3: #dcdcdc;
    $gray4: #898d8f; 

    @import "../../src/admin/scss/_view-mode-records.scss";

    .tainacan-records-container .tainacan-record .metadata-title {
        padding: 0.75rem;
        margin-bottom: 0px;
    }
</style>


