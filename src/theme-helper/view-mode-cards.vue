<template>
    <div class="table-container">
        <div class="table-wrapper">
            <!-- CARDS VIEW MODE -->
            <div class="tainacan-cards-container">
                <div 
                        :key="index"
                        v-for="(item, index) of items"
                        class="tainacan-card">
                    
                    <!-- Title -->
                    <p 
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
                                <h3 class="metadata-label">{{ column.name }}</h3>
                                <p 
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
    name: 'ViewModeCards',
    props: {
        collectionId: Number,
        displayedMetadata: Array,
        items: Array,
        isLoading: false
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
    $primary-lighter: #e6f6f8;
    $primary-lighter-hover: #d1e6e6;
    $tainacan-input-color: #1d1d1d;
    $tainacan-input-background: #e5e5e5;
    $tainacan-placeholder-color: #898d8f;
    $gray-hover: #dcdcdc;
    $gray-light: #898d8f; 

    @import "../../src/admin/scss/_view-mode-cards.scss";

    .tainacan-cards-container .tainacan-card .metadata-title {
        padding: 0.75rem;
        margin-bottom: 0px;
    }
</style>


