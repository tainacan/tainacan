<template>
    <div class="table-container">
        <div class="table-wrapper">
            
            <!-- Empty result placeholder, rendered in the parent component -->
            <slot />
    
            <!-- SKELETON LOADING -->
            <table 
                    v-if="isLoading"
                    :summary="$i18n.get('label_table_of_items')"
                    class="tainacan-table tainacan-table-skeleton"
                    tabindex="0">
                <thead>
                    <th 
                            v-for="(column, metadatumIndex) in displayedMetadata"
                            :key="metadatumIndex"
                            v-if="column.display"
                            class="column-default-width"
                            :class="{
                                    'thumbnail-cell': column.metadatum == 'row_thumbnail',
                                    'column-small-width' : column.metadata_type_object != undefined ? (column.metadata_type_object.primitive_type == 'date' || 
                                                                                                        column.metadata_type_object.primitive_type == 'float' ||
                                                                                                        column.metadata_type_object.primitive_type == 'int') : false,
                                    'column-medium-width' : column.metadata_type_object != undefined ? (column.metadata_type_object.primitive_type == 'term' || 
                                                                                                        column.metadata_type_object.primitive_type == 'item' || 
                                                                                                        column.metadata_type_object.primitive_type == 'compound') : false,
                                    'column-large-width' : column.metadata_type_object != undefined ? (column.metadata_type_object.primitive_type == 'long_string' || column.metadata_type_object.related_mapped_prop == 'description') : false,
                            }">
                        <div class="th-wrap">{{ column.name }}</div>
                    </th>
                </thead>
                <tbody>
                    <tr   
                            :key="item"
                            v-for="item in 12">
                        <td 
                                v-for="(column, metadatumIndex) in displayedMetadata"
                                :key="metadatumIndex"
                                v-if="column.display"
                                :class="{ 'thumbnail-cell': metadatumIndex == 0 }"
                                class="column-default-width skeleton"/>
                    </tr>
                </tbody>
            </table>

            <!-- TABLE VIEW MODE -->
            <table 
                    v-if="!isLoading && items.length > 0"
                    class="tainacan-table">
                <thead>
                    <tr>
                        <!-- Displayed Metadata -->
                        <th 
                                v-for="(column, index) in displayedMetadata"
                                :key="index"
                                v-if="column.display"
                                class="column-default-width"
                                :class="{
                                        'thumbnail-cell': column.metadatum == 'row_thumbnail',
                                        'column-needed-width column-align-right' : column.metadata_type_object != undefined ? (column.metadata_type_object.primitive_type == 'float' || 
                                                                                                                               column.metadata_type_object.primitive_type == 'int' ) : false,
                                        'column-small-width' : column.metadata_type_object != undefined ? (column.metadata_type_object.primitive_type == 'date' || 
                                                                                                           column.metadata_type_object.primitive_type == 'float' ||
                                                                                                           column.metadata_type_object.primitive_type == 'int') : false,
                                        'column-medium-width' : column.metadata_type_object != undefined ? (column.metadata_type_object.primitive_type == 'term' || 
                                                                                                            column.metadata_type_object.primitive_type == 'item') : false,
                                        'column-large-width' : column.metadata_type_object != undefined ? (column.metadata_type_object.primitive_type == 'long_string' ||
                                                                                                           column.metadata_type_object.related_mapped_prop == 'description' || 
                                                                                                           column.metadata_type_object.primitive_type == 'compound') : false,
                                }">
                            <div class="th-wrap">{{ column.name }}</div>
                        </th>

                        <th 
                                v-if="isSlideshowViewModeEnabled"
                                class="actions-header">
                            &nbsp;
                            <span class="sr-only">
                                {{ $i18n.get('label_actions_column') }}
                            </span>
                            <!-- nothing to show on header for actions cell-->
                        </th>
                    </tr>
                </thead>
                <tbody role="list">
                    <tr     
                            :data-tainacan-item-id="item.id"
                            :aria-setsize="totalItems"
                            :aria-posinset="getPosInSet(index)"
                            role="listitem"
                            :key="index"
                            v-for="(item, index) of items">
                        
                        <!-- JS-side hook for extra content -->
                        <td 
                                v-if="hasBeforeHook()"
                                class="faceted-search-hook faceted-search-hook-item-before"
                                v-html="getBeforeHook(item)" />
                                
                        <!-- Item Displayed Metadata -->
                        <td 
                                :key="metadatumIndex"    
                                v-for="(column, metadatumIndex) in displayedMetadata"
                                v-if="column.display"
                                class="column-default-width"
                                :class="{
                                        'metadata-type-textarea': column.metadata_type_object != undefined && column.metadata_type_object.component == 'tainacan-textarea',
                                        'thumbnail-cell': column.metadatum == 'row_thumbnail',
                                        'column-main-content' : column.metadata_type_object != undefined ? (column.metadata_type_object.related_mapped_prop == 'title') : false,
                                        'column-needed-width column-align-right' : column.metadata_type_object != undefined ? (column.metadata_type_object.primitive_type == 'float' || 
                                                                                                                               column.metadata_type_object.primitive_type == 'int' ) : false,
                                        'column-small-width' : column.metadata_type_object != undefined ? (column.metadata_type_object.primitive_type == 'date' || 
                                                                                                           column.metadata_type_object.primitive_type == 'int' ||
                                                                                                           column.metadata_type_object.primitive_type == 'float') : false,
                                        'column-medium-width' : column.metadata_type_object != undefined ? (column.metadata_type_object.primitive_type == 'item' || 
                                                                                                            column.metadata_type_object.primitive_type == 'term' ) : false,
                                        'column-large-width' : column.metadata_type_object != undefined ? (column.metadata_type_object.primitive_type == 'long_string' ||
                                                                                                           column.metadata_type_object.primitive_type == 'compound' ||
                                                                                                           column.metadata_type_object.related_mapped_prop == 'description') : false,
                                }">
                            <a :href="getItemLink(item.url, index)">
                                <p
                                        v-tooltip="{
                                            delay: {
                                                shown: 500,
                                                hide: 300,
                                            },
                                            content: item.title != undefined && item.title != '' ? item.title : `<span class='has-text-gray3 is-italic'>` + $i18n.get('label_value_not_provided') + `</span>`,
                                            html: true,
                                            autoHide: false,
                                            placement: 'auto-start',
                                            popperClass: ['tainacan-tooltip', 'tooltip']
                                        }"
                                        :aria-label="column.name + ': ' + (item.title != undefined && item.title != '' ? item.title : $i18n.get('label_value_not_provided'))"
                                        v-if="!collectionId &&
                                            column.metadata_type_object != undefined && 
                                            column.metadata_type_object.related_mapped_prop == 'title'"
                                        v-html="`<span class='sr-only'>` + column.name + ': </span>' + (item.title != undefined && item.title != '' ? item.title : `<span class='has-text-gray3 is-italic'>` + $i18n.get('label_value_not_provided') + `</span>`)"/>
                                <p
                                        v-tooltip="{
                                            delay: {
                                                shown: 500,
                                                hide: 300,
                                            },
                                            content: item.description != undefined && item.description != '' ? item.description : `<span class='has-text-gray3 is-italic'>` + $i18n.get('label_value_not_provided') + `</span>`,
                                            html: true,
                                            autoHide: false,
                                            placement: 'auto-start',
                                            popperClass: ['tainacan-tooltip', 'tooltip']
                                        }"
                                        v-if="!collectionId &&
                                            column.metadata_type_object != undefined && 
                                            column.metadata_type_object.related_mapped_prop == 'description'"
                                        v-html="`<span class='sr-only'>` + column.name + ': </span>' + (item.description != undefined && item.description != '' ? item.description : `<span class='has-text-gray3 is-italic'>` + $i18n.get('label_value_not_provided') + `</span>`)"/>
                                <p
                                        v-tooltip="{
                                            delay: {
                                                shown: 500,
                                                hide: 300,
                                            },
                                            popperClass: [ 'tainacan-tooltip', 'tooltip', column.metadata_type_object != undefined && column.metadata_type_object.component == 'tainacan-textarea' ? 'metadata-type-textarea' : '' ],
                                            content: renderMetadataWithLabel(item.metadata, column) != '' ? renderMetadataWithLabel(item.metadata, column) : `<span class='has-text-gray3 is-italic'>` + $i18n.get('label_value_not_provided') + `</span>`,
                                            html: true,
                                            autoHide: false,
                                            placement: 'auto-start'
                                        }"
                                        v-if="item.metadata != undefined &&
                                            column.metadatum !== 'row_thumbnail' &&
                                            column.metadatum !== 'row_actions' &&
                                            column.metadatum !== 'row_creation' &&
                                            column.metadatum !== 'row_author' &&
                                            column.metadatum !== 'row_title' &&
                                            column.metadatum !== 'row_description'"
                                        v-html="renderMetadataWithLabel(item.metadata, column) != '' ? renderMetadataWithLabel(item.metadata, column) : `<span class='has-text-gray3 is-italic'>` + $i18n.get('label_value_not_provided') + `</span>`"/>

                                <span v-if="column.metadatum == 'row_thumbnail'">
                                    <img 
                                            :alt="item.thumbnail_alt ? item.thumbnail_alt : $i18n.get('label_thumbnail')"
                                            class="table-thumb" 
                                            :src="$thumbHelper.getSrc(item['thumbnail'], 'tainacan-small', item.document_mimetype)">
                                    <div class="skeleton"/>
                                </span> 
                            </a>
                        </td>

                        <!-- Actions -->
                        <td 
                                v-if="isSlideshowViewModeEnabled"
                                class="actions-cell"
                                :label="$i18n.get('label_actions')">
                            <div class="actions-container">
                                <span 
                                        v-tooltip="{
                                            delay: {
                                                shown: 500,
                                                hide: 100,
                                            },
                                            content: $i18n.get('label_see_on_fullscreen'),
                                            placement: 'auto-start',
                                            popperClass: ['tainacan-tooltip', 'tooltip']
                                        }"          
                                        @click.prevent="starSlideshowFromHere(index)"
                                        class="icon slideshow-icon">
                                    <i class="tainacan-icon tainacan-icon-viewgallery tainacan-icon-1-125em"/>
                                </span> 
                            </div>
                        </td>
                
                        <!-- JS-side hook for extra content -->
                        <td 
                                v-if="hasAfterHook()"
                                class="faceted-search-hook faceted-search-hook-item-after"
                                v-html="getAfterHook(item)" />
                    </tr>
                </tbody>
            </table>
        </div> 
    </div>
</template>

<script>
import { viewModesMixin } from '../js/view-modes-mixin.js';

export default {
    name: 'ViewModeTable',
    mixins: [
        viewModesMixin
    ],
    methods: {
        renderMetadataWithLabel(itemMetadata, column) {

            let metadata = (itemMetadata != undefined && itemMetadata[column.slug] != undefined) ? itemMetadata[column.slug] : false;

            if (!metadata) {
                return '';
            } else {
                return '<span class="sr-only">' + column.name + ': </span>' + metadata.value_as_html;
            }
        }
    }
}
</script>

<style lang="scss" scoped>
    
    tr .actions-cell {
        opacity: 0;
        .slideshow-icon {
            transform: scale(0.25);
            transition: transform 0.2s ease;
        }
    }
    tr:hover .actions-cell {
        opacity: 1;
        .slideshow-icon {
            transform: scale(1.0);  
        }
    }

</style>