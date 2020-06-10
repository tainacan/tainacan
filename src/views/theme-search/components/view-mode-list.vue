<template>
    <div class="table-container">
        <div class="table-wrapper">

            <!-- Empty result placeholder -->
            <section
                    v-if="!isLoading && items.length <= 0"
                    class="section">
                <div class="content has-text-gray4 has-text-centered">
                    <p>
                        <span class="icon is-large">
                            <i class="tainacan-icon tainacan-icon-36px tainacan-icon-items" />
                        </span>
                    </p>
                    <p>{{ $i18n.get('info_no_item_found') }}</p>
                </div>
            </section>

            <!-- SKELETON LOADING -->
            <div
                    v-if="isLoading"                  
                    class="tainacan-list-container">
                <div 
                        :key="item"
                        v-for="item in 12"
                        style="min-height: 200px"
                        class="skeleton tainacan-list" />
            </div>
            
            <!-- LIST VIEW MODE -->
            <div
                    role="list"
                    v-if="!isLoading && items.length > 0"
                    class="tainacan-list-container">
                <a 
                        role="listitem"
                        :href="item.url"
                        :key="index"
                        v-for="(item, index) of items"
                        class="tainacan-list">

                    <!-- Title -->
                    <p 
                            v-tooltip="{
                                delay: {
                                    show: 500,
                                    hide: 300,
                                },
                                content: item.metadata != undefined ? renderMetadata(item.metadata, column) : '',
                                html: true,
                                autoHide: false,
                                placement: 'auto-start'
                            }"
                            v-for="(column, metadatumIndex) in displayedMetadata"
                            :key="metadatumIndex"
                            class="metadata-title"
                            v-if="column.display && column.metadata_type_object != undefined && (column.metadata_type_object.related_mapped_prop == 'title')"
                            v-html="item.metadata != undefined && collectionId ? renderMetadata(item.metadata, column) : (item.title ? item.title :`<span class='has-text-gray3 is-italic'>` + $i18n.get('label_value_not_informed') + `</span>`)" />                 
            

                    <!-- Remaining metadata -->  
                    <div class="media">
                         <div 
                                class="tainacan-list-thumbnail"
                                v-if="item.thumbnail != undefined">
                            <img 
                                    :alt="$i18n.get('label_thumbnail')"
                                    :src="item['thumbnail']['tainacan-medium-full'] ? item['thumbnail']['tainacan-medium-full'][0] : (item['thumbnail'].medium_large ? item['thumbnail'].medium_large[0] : thumbPlaceholderPath)">  
                        </div>
                        <div class="list-metadata media-body">
                            <span 
                                    v-for="(column, metadatumIndex) in displayedMetadata"
                                    :key="metadatumIndex"
                                    :class="{ 'metadata-type-textarea': column.metadata_type_object.component == 'tainacan-textarea' }"
                                    v-if="renderMetadata(item.metadata, column) != '' && column.display && column.slug != 'thumbnail' && column.metadata_type_object != undefined && (column.metadata_type_object.related_mapped_prop != 'title')">
                                <h3 class="metadata-label">{{ column.name }}</h3>
                                <p      
                                        v-html="renderMetadata(item.metadata, column)"
                                        class="metadata-value"/>
                            </span>
                        </div>
                    </div>
                </a>
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
        isFiltersMenuCompressed: Boolean
    },
    data () {
        return {
            thumbPlaceholderPath: tainacan_plugin.base_url + '/assets/images/placeholder_square.png'
        }
    },
    computed: {
        amountOfDisplayedMetadata() {
            return this.displayedMetadata.filter((metadata) => metadata.display).length;
        }
    },
    methods: {
        goToItemPage(item) {
            window.location.href = item.url;   
        },
        renderMetadata(itemMetadata, column) {

            let metadata = (itemMetadata != undefined && itemMetadata[column.slug] != undefined) ? itemMetadata[column.slug] : false;

            if (!metadata) {
                return '';
            } else {
                return metadata.value_as_html;
            }
        }
    }
}
</script>

<style  lang="scss" scoped>

    @import "../scss/view-mode-list.scss";

    .tainacan-list-container .tainacan-list .metadata-title {
        padding: 0.6em 0.875em;
        margin-bottom: 0px;
    }
</style>


