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
                    <template 
                            v-for="(column, metadatumIndex) in displayedMetadata"
                            :key="metadatumIndex">
                        <th 
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
                            <div class="th-wrap">
                                {{ column.name }}
                            </div>
                        </th>
                    </template>
                </thead>
                <tbody>
                    <tr   
                            v-for="item in 12"
                            :key="item">
                        <template 
                                v-for="(column, metadatumIndex) in displayedMetadata"
                                :key="metadatumIndex">
                            <td 
                                    v-if="column.display"
                                    :class="{ 'thumbnail-cell': metadatumIndex == 0 }"
                                    class="column-default-width skeleton" />
                        </template>
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
                        <template 
                                v-for="(column, index) in displayedMetadata"
                                :key="index">
                            <th 
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
                                <div class="th-wrap">
                                    {{ column.name }}
                                </div>
                            </th>
                        </template>
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
                            v-for="(item, index) of items"
                            :key="index"
                            :data-tainacan-item-id="item.id"
                            :aria-setsize="totalItems"
                            :aria-posinset="getPosInSet(index)"
                            role="listitem">
                        
                        <!-- JS-side hook for extra content -->
                        <td 
                                v-if="hasBeforeHook()"
                                class="faceted-search-hook faceted-search-hook-item-before"
                                v-html="getBeforeHook(item)" />
                                
                        <!-- Item Displayed Metadata -->
                        <template 
                                v-for="(column, metadatumIndex) in displayedMetadata"
                                :key="metadatumIndex">
                            <td     
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
                                            v-if="!collectionId &&
                                                column.metadata_type_object != undefined && 
                                                column.metadata_type_object.related_mapped_prop == 'title'"
                                            v-tooltip="{
                                                delay: {
                                                    show: 500,
                                                    hide: 300,
                                                },
                                                content: item.title != undefined && item.title != '' ? item.title : `<span class='has-text-gray3 is-italic'>` + $i18n.get('label_value_not_provided') + `</span>`,
                                                html: true,
                                                autoHide: false,
                                                placement: 'auto-start',
                                                popperClass: ['tainacan-tooltip', 'tooltip']
                                            }"
                                            :aria-label="column.name + ': ' + (item.title != undefined && item.title != '' ? item.title : $i18n.get('label_value_not_provided'))"
                                            v-html="`<span class='sr-only'>` + column.name + ': </span>' + (item.title != undefined && item.title != '' ? item.title : `<span class='has-text-gray3 is-italic'>` + $i18n.get('label_value_not_provided') + `</span>`)" />
                                    <p
                                            v-if="!collectionId &&
                                                column.metadata_type_object != undefined && 
                                                column.metadata_type_object.related_mapped_prop == 'description'"
                                            v-tooltip="{
                                                delay: {
                                                    show: 500,
                                                    hide: 300,
                                                },
                                                content: item.description != undefined && item.description != '' ? item.description : `<span class='has-text-gray3 is-italic'>` + $i18n.get('label_value_not_provided') + `</span>`,
                                                html: true,
                                                autoHide: false,
                                                placement: 'auto-start',
                                                popperClass: ['tainacan-tooltip', 'tooltip']
                                            }"
                                            v-html="`<span class='sr-only'>` + column.name + ': </span>' + (item.description != undefined && item.description != '' ? item.description : `<span class='has-text-gray3 is-italic'>` + $i18n.get('label_value_not_provided') + `</span>`)" />
                                    <p
                                            v-if="item.metadata != undefined &&
                                                column.metadatum !== 'row_thumbnail' &&
                                                column.metadatum !== 'row_actions' &&
                                                column.metadatum !== 'row_creation' &&
                                                column.metadatum !== 'row_author' &&
                                                column.metadatum !== 'row_title' &&
                                                column.metadatum !== 'row_description'"
                                            v-tooltip="{
                                                delay: {
                                                    show: 500,
                                                    hide: 300,
                                                },
                                                popperClass: [ 'tainacan-tooltip', 'tooltip', column.metadata_type_object != undefined && column.metadata_type_object.component == 'tainacan-textarea' ? 'metadata-type-textarea' : '' ],
                                                content: renderMetadataWithLabel(item.metadata, column) != '' ? renderMetadataWithLabel(item.metadata, column) : `<span class='has-text-gray3 is-italic'>` + $i18n.get('label_value_not_provided') + `</span>`,
                                                html: true,
                                                autoHide: false,
                                                placement: 'auto-start'
                                            }"
                                            v-html="renderMetadataWithLabel(item.metadata, column) != '' ? renderMetadataWithLabel(item.metadata, column) : `<span class='has-text-gray3 is-italic'>` + $i18n.get('label_value_not_provided') + `</span>`" />

                                    <span v-if="column.metadatum == 'row_thumbnail'">
                                        <img 
                                                :alt="item.thumbnail_alt ? item.thumbnail_alt : $i18n.get('label_thumbnail')"
                                                class="table-thumb" 
                                                :src="$thumbHelper.getSrc(item['thumbnail'], 'tainacan-small', item.document_mimetype)">
                                        <div class="skeleton" />
                                    </span> 
                                </a>
                            </td>
                        </template>

                        <!-- Actions -->
                        <td 
                                v-if="isSlideshowViewModeEnabled"
                                class="actions-cell"
                                :label="$i18n.get('label_actions')">
                            <div class="actions-container">
                                <span 
                                        v-tooltip="{
                                            delay: {
                                                show: 500,
                                                hide: 100,
                                            },
                                            content: $i18n.get('label_see_on_fullscreen'),
                                            placement: 'auto-start',
                                            popperClass: ['tainacan-tooltip', 'tooltip']
                                        }"          
                                        class="icon slideshow-icon"
                                        @click.prevent="starSlideshowFromHere(index)">
                                    <i class="tainacan-icon tainacan-icon-viewgallery tainacan-icon-1-125em" />
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

    @import '../../../../../admin/scss/_variables.scss';
    @import '../../../../../admin/scss/_tables.scss';
    
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
    .tainacan-table {
        .tainacan-relationship-group {
            .tainacan-relationship-metadatum {
                display: inline-block;
                .tainacan-relationship-metadatum-header {
                    img {
                        display: none;
                    }
                    .label {
                        font-weight: normal;
                        font-size: 1em !important;
                        margin-top: 0;
                        margin-left: 0;
                        margin-bottom: 0;
                        margin-right: 0;
                        padding: 0;
                    }
                }
                .tainacan-metadatum {
                    display: none;
                }
            }
            &>.multivalue-separator {
                display: inline-block;
                max-height: 100%;
                width: 1px;
                background: var(--tainacan-gray2);
                content: none;
                color: transparent;
                margin: 0 6px;
            }
        }
        .column-large-width {
            .tainacan-compound-group {
                display: inline-block;
                font-size: 1.125em;
                margin-top: -0.25em;
                text-overflow: ellipsis;
                white-space: nowrap;
                overflow: hidden;
                max-width: 100%;

                & * {
                    display: inline-block;
                }
                .label {
                    font-size: 1em !important;
                    padding: 0;
                    color: var(--tainacan-info-color);
                    &:not(:first-child)::before {
                        content: ', ';
                        font-size: 1em;
                        font-weight: normal;
                        color: var(--tainacan-info-color);
                        display: inline-block;
                        margin-right: 0.35em;
                        margin-left: -0.15em;
                    }
                    &::after {
                        content: ': ';
                        font-size: 1em;
                        color: var(--tainacan-info-color);
                        display: inline-block;
                        margin-right: 0.15em;
                    }
                }
                p {
                    font-size: 1em !important;
                    line-height: 1.65em !important;
                }
            }
        }
    }

</style>