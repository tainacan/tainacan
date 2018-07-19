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
                            v-tooltip="{
                                content: item.metadata != undefined ? renderMetadata(item.metadata, column) : '',
                                html: true,
                                autoHide: false,
                                placement: 'auto-start'
                            }"
                            v-for="(column, index) in displayedMetadata"
                            :key="index"
                            v-if="column.metadata_type_object != undefined && (column.metadata_type_object.related_mapped_prop == 'title')"
                            class="metadata-title"
                            @click="goToItemPage(item)"
                            v-html="item.metadata != undefined ? renderMetadata(item.metadata, column) : ''" />                             
                    
                    <!-- Remaining metadata -->  
                    <div    
                            class="media"
                            @click="goToItemPage(item)">

                        <img 
                                v-if="item.thumbnail != undefined"
                                :src="item['thumbnail'].medium ? item['thumbnail'].medium : thumbPlaceholderPath">  

                        <div class="list-metadata media-body">
                            <!-- Description -->
                            <p 
                                    v-tooltip="{
                                        content: item.metadata != undefined ? renderMetadata(item.metadata, column) : '',
                                        html: true,
                                        autoHide: false,
                                        placement: 'auto-start'
                                    }"   
                                    v-if="
                                        column.metadata_type_object != undefined && 
                                        (column.metadata_type_object.related_mapped_prop == 'description')"
                                    v-for="(column, index) in displayedMetadata"
                                    :key="index"
                                    class="metadata-description"
                                    v-html="(item.metadata != undefined && item.metadata[column.slug] != undefined) ? getLimitedDescription(item.metadata[column.slug].value_as_string) : ''" /> 
                            <br>
                            <!-- Author and Creation Date-->
                            <p 
                                    v-tooltip="{
                                        content: column.metadatum == 'row_author' || column.metadatum == 'row_creation',
                                        html: false,
                                        autoHide: false,
                                        placement: 'auto-start'
                                    }"   
                                    v-for="(column, index) in displayedMetadata"
                                    :key="index"
                                    v-if="column.metadatum == 'row_author' || column.metadatum == 'row_creation'"
                                    class="metadata-author-creation">   
                                {{ column.metadatum == 'row_author' ? $i18n.get('info_created_by') + ' ' + item[column.slug] : $i18n.get('info_date') + ' ' + item[column.slug] }}
                            </p>                          
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
        },
        getLimitedDescription(description) {
            return description.length > 120 ? description.substring(0, 117) + '...' : description;
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


