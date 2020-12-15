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
                        :data-tainacan-item-id="item.id"
                        :href="getItemLink(item.url, index)"
                        :key="index"
                        v-for="(item, index) of items"
                        class="tainacan-list">

                    <!-- Title -->
                    <div class="metadata-title">
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
                                v-if="column.display && column.metadata_type_object != undefined && (column.metadata_type_object.related_mapped_prop == 'title')"
                                v-html="item.metadata != undefined && collectionId ? renderMetadata(item.metadata, column) : (item.title ? item.title :`<span class='has-text-gray3 is-italic'>` + $i18n.get('label_value_not_provided') + `</span>`)" />                 
                        <span 
                                v-if="isSlideshowViewModeEnabled"
                                v-tooltip="{
                                    delay: {
                                        show: 500,
                                        hide: 100,
                                    },
                                    content: $i18n.get('label_see_on_fullscreen'),
                                    placement: 'auto-start'
                                }"          
                                @click.prevent="starSlideshowFromHere(index)"
                                class="icon slideshow-icon">
                            <i class="tainacan-icon tainacan-icon-viewgallery tainacan-icon-1-125em"/>
                        </span> 
                    </div>

                    <!-- Remaining metadata -->  
                    <div class="media">
                         <div 
                                class="tainacan-list-thumbnail"
                                v-if="item.thumbnail != undefined">
                            <img 
                                    :alt="item.thumbnail_alt ? item.thumbnail_alt : $i18n.get('label_thumbnail')"
                                    :src="$thumbHelper.getSrc(item['thumbnail'], 'tainacan-medium-full')">  
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
import { viewModesMixin } from '../js/view-modes-mixin.js';

export default {
    name: 'ViewModeRecords',
    mixins: [
        viewModesMixin
    ]
}
</script>

<style  lang="scss" scoped>

    @import "../../admin/scss/view-mode-list.scss";

    .tainacan-list-container .tainacan-list .metadata-title {
        padding: 0.5em 0.875em;
        margin-bottom: 0px;
    }
</style>


