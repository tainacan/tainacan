<template>
    <div>
        <div class="table-container">
            <b-loading
                    is-full-page="false" 
                    :active.sync="isLoading" />
            <div class="table-wrapper">
                <div class="related-items-list">
                    <div 
                            v-for="(relatedItemGroup, index) of relatedItemsArray"
                            :key="index"
                            class="related-item-group">
                        <div class="columns">
                            <div class="column is-narrow">
                                <div class="section-status">
                                    <div class="field has-addons">
                                        <span>
                                            <span class="icon">
                                                <i class="tainacan-icon tainacan-icon-collection"/>
                                            </span>
                                            {{ relatedItemGroup.collection_name ? relatedItemGroup.collection_name : '' }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="column is-narrow">
                                <div class="section-status">
                                    <div class="field has-addons">
                                        <span>
                                            <span class="icon">
                                                <i class="tainacan-icon tainacan-icon-metadata"/>
                                            </span>
                                            {{ relatedItemGroup.metadata_name ? relatedItemGroup.metadata_name : '' }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <table class="tainacan-table">
                            <thead>
                                <tr>

                                    <!-- Status -->
                                    <th>
                                        &nbsp;
                                    </th>

                                    <!-- Displayed Metadata -->
                                    <th
                                            v-for="(column, columnIndex) in displayedMetadata"
                                            :key="columnIndex"
                                            class="column-default-width"
                                            :class="{
                                                    'thumbnail-cell': column.metadatum == 'row_thumbnail',
                                                    'column-small-width' : column.metadata_type_object != undefined ? (column.metadata_type_object.primitive_type == 'date' ||
                                                                                                                    column.metadata_type_object.primitive_type == 'float' ||
                                                                                                                    column.metadata_type_object.primitive_type == 'int') : false,
                                                    'column-medium-width' : column.metadata_type_object != undefined ? (column.metadata_type_object.primitive_type == 'term' ||
                                                                                                                        column.metadata_type_object.primitive_type == 'item') : false,
                                                    'column-large-width' : column.metadata_type_object != undefined ? (column.metadata_type_object.primitive_type == 'long_string' ||
                                                                                                                    column.metadata_type_object.primitive_type == 'compound' ||
                                                                                                                    column.metadata_type_object.related_mapped_prop == 'description') : false,
                                            }">
                                        <div class="th-wrap">{{ column.name }}</div>
                                    </th>
                                    <th
                                            v-if="isEditable && relatedItemGroup.items.findIndex((relatedItem) => relatedItem.current_user_can_edit || relatedItem.current_user_can_delete) >= 0"
                                            class="actions-header">
                                        &nbsp;
                                        <!-- nothing to show on header for actions cell-->
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                        v-for="(relatedItem, itemIndex) of relatedItemGroup.items"
                                        :key="itemIndex">
                                    <!-- Checking list -->
                                    
                                    <td class="status-cell">
                                        <span 
                                                v-if="$statusHelper.hasIcon(relatedItem.status)"
                                                class="icon has-text-gray"
                                                v-tooltip="{
                                                    content: $i18n.get('status_' + relatedItem.status),
                                                    autoHide: true,
                                                    placement: 'auto-start'
                                                }">
                                            <i 
                                                    class="tainacan-icon tainacan-icon-1em"
                                                    :class="$statusHelper.getIcon(relatedItem.status)"
                                                    />
                                        </span>
                                    </td>
                                    <!-- Item Displayed Metadata -->
                                    <td
                                            :key="columnIndex"
                                            v-for="(column, columnIndex) in displayedMetadata"
                                            class="column-default-width"
                                            :class="{ 'metadata-type-textarea': column.metadata_type_object != undefined && column.metadata_type_object.component == 'tainacan-textarea',
                                                    'thumbnail-cell': column.metadatum == 'row_thumbnail',
                                                    'column-main-content' : column.metadata_type_object != undefined ? (column.metadata_type_object.related_mapped_prop == 'title') : false,
                                                    'column-needed-width column-align-right' : column.metadata_type_object != undefined ? (column.metadata_type_object.primitive_type == 'float' ||
                                                                                                                                        column.metadata_type_object.primitive_type == 'int' ) : false,
                                                    'column-small-width' : column.metadata_type_object != undefined ? (column.metadata_type_object.primitive_type == 'date' ||
                                                                                                                    column.metadata_type_object.primitive_type == 'int' ||
                                                                                                                    column.metadata_type_object.primitive_type == 'float') : false,
                                                    'column-medium-width' : column.metadata_type_object != undefined ? (column.metadata_type_object.primitive_type == 'item' ||
                                                                                                                        column.metadata_type_object.primitive_type == 'term') : false,
                                                    'column-large-width' : column.metadata_type_object != undefined ? (column.metadata_type_object.primitive_type == 'long_string' ||
                                                                                                                    column.metadata_type_object.primitive_type == 'compound' ||
                                                                                                                    column.metadata_type_object.related_mapped_prop == 'description') : false,
                                            }">

                                        <p
                                                v-tooltip="{
                                                    delay: {
                                                        show: 500,
                                                        hide: 300,
                                                    },
                                                    content: relatedItem.title != undefined && relatedItem.title != '' ? relatedItem.title : `<span class='has-text-gray3 is-italic'>` + $i18n.get('label_value_not_provided') + `</span>`,
                                                    html: true,
                                                    autoHide: false,
                                                    placement: 'auto-start'
                                                }"
                                                v-if="collectionId == undefined &&
                                                    column.metadata_type_object != undefined &&
                                                    column.metadata_type_object.related_mapped_prop == 'title'"
                                                v-html="`<span class='sr-only'>` + column.name + ': </span>' + ((relatedItem.title != undefined && relatedItem.title != '') ? relatedItem.title : `<span class='has-text-gray3 is-italic'>` + $i18n.get('label_value_not_provided') + `</span>`)"/>
                                        
                                        <span
                                                v-if="column.metadatum == 'row_thumbnail'"
                                                class="table-thumb">
                                            <blur-hash-image
                                                    :width="$thumbHelper.getWidth(relatedItem['thumbnail'], 'tainacan-small', 40)"
                                                    :height="$thumbHelper.getHeight(relatedItem['thumbnail'], 'tainacan-small', 40)"
                                                    :hash="$thumbHelper.getBlurhashString(relatedItem['thumbnail'], 'tainacan-small')"
                                                    :src="$thumbHelper.getSrc(relatedItem['thumbnail'], 'tainacan-small', relatedItem.document_mimetype)"
                                                    :alt="relatedItem.thumbnail_alt ? relatedItem.thumbnail_alt : $i18n.get('label_thumbnail')"
                                                    :transition-duration="500"
                                            />
                                        </span>

                                    </td>

                                    <!-- Actions -->
                                    <td 
                                            v-if="isEditable && (relatedItem.current_user_can_edit || relatedItem.current_user_can_delete)"
                                            class="actions-cell"
                                            :label="$i18n.get('label_actions')">
                                        <div class="actions-container">
                                            <a
                                                    v-if="!relatedItem.status != 'trash'"
                                                    id="button-edit"
                                                    :aria-label="$i18n.getFrom('items','edit_item')">
                                                <span
                                                        v-tooltip="{
                                                            content: $i18n.get('edit'),
                                                            autoHide: true,
                                                            placement: 'auto'
                                                        }"
                                                        class="icon">
                                                    <i class="has-text-secondary tainacan-icon tainacan-icon-1-25em tainacan-icon-edit"/>
                                                </span>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        name: 'RelatedItemsList',
        props: {
            relatedItems: Object,
            isLoading: Boolean,
            isEditable: Boolean,
        },
        data() {
            return {
                displayedMetadata: [
                    {
                        name: this.$i18n.get('label_thumbnail'),
                        metadatum: 'row_thumbnail',
                        slug: 'thumbnail',
                    },
                    {
                        name: this.$i18n.get('label_title'),
                        metadatum: 'row_title',
                        metadata_type_object: {core: true, related_mapped_prop: 'title'},
                        slug: 'title',
                    }
                ]
            }
        },
        computed: {
            relatedItemsArray() {
                return this.relatedItems ? Object.values(this.relatedItems).filter((aRelatedItemGroup) => aRelatedItemGroup.total_items) : [];
            }
        }
    }
</script>
        
<style lang="scss" scoped>
    .section-status {
        padding-bottom: 16px;
        margin-left: -0.875rem;
        font-size: 0.875em;

        .field {
            padding: 10px 0 14px 0px !important;

            .icon  {
                font-size: 1.125em !important;
                color: var(--tainacan-info-color);
            }
        }
    }
    .related-items-list {
        .related-item-group {
            
            &:not(:last-child) {
                border-bottom: 1px dashed var(--tainacan-info-color);
            }

            .tainacan-table {
                margin-top: -2rem;
                margin-bottom: 1rem;
            }
        }
    }
</style>
