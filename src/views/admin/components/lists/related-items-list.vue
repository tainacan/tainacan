<template>
    <div>
        <div class="table-container">
            <b-loading
                    is-full-page="false" 
                    :active.sync="displayLoading" />
            <div class="table-wrapper">
                <div class="related-items-list">
                    <div 
                            v-for="(relatedItemGroup, index) of relatedItemsArray"
                            :key="index"
                            class="related-item-group">
                        <div class="columns is-vcentered is-multiline">
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
                            <div 
                                    v-if="relatedItemGroup.total_items && relatedItemGroup.total_items > 1"
                                    style="margin-left: auto;"
                                    class="column is-narrow">
                                <div class="section-status">
                                    <div class="field">
                                        <b-button
                                                class="button is-secondary"
                                                tag="router-link"
                                                :to="$routerHelper.getCollectionItemsPath(collectionId, { metaquery: [{ key: relatedItemGroup.metadata_id, value: itemId, compare: 'IN' }] })">
                                            {{ $i18n.getWithVariables('label_view_all_%s_related_items', [relatedItemGroup.total_items]) }}
                                        </b-button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <ul class="related-item-group__items-list">
                            <li
                                    v-for="(relatedItem, itemIndex) of relatedItemGroup.items"
                                    :key="itemIndex">
                                
                                <div
                                        class="status-cell"
                                        @click="openItemOnNewTab(relatedItem)">
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
                                </div>
                                <div @click="openItemOnNewTab(relatedItem)">
                                    <span class="table-thumb">
                                        <blur-hash-image
                                                :width="$thumbHelper.getWidth(relatedItem['thumbnail'], 'tainacan-small', 40)"
                                                :height="$thumbHelper.getHeight(relatedItem['thumbnail'], 'tainacan-small', 40)"
                                                :hash="$thumbHelper.getBlurhashString(relatedItem['thumbnail'], 'tainacan-small')"
                                                :src="$thumbHelper.getSrc(relatedItem['thumbnail'], 'tainacan-small', relatedItem.document_mimetype)"
                                                :alt="relatedItem.thumbnail_alt ? relatedItem.thumbnail_alt : $i18n.get('label_thumbnail')"
                                                :transition-duration="500"
                                        />
                                    </span>
                                </div>
                                <div 
                                        @click="openItemOnNewTab(relatedItem)"
                                        style="width: 100%">
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
                                            v-html="(relatedItem.title != undefined && relatedItem.title != '') ? relatedItem.title : `<span class='has-text-gray3 is-italic'>` + $i18n.get('label_value_not_provided') + `</span>`"/>
                                </div>
                                <div 
                                        v-if="isEditable && relatedItem.current_user_can_edit"
                                        class="actions-cell"
                                        :label="$i18n.get('label_actions')">
                                    <div class="actions-container">
                                        <a
                                                v-if="!relatedItem.status != 'trash'"
                                                id="button-edit"
                                                @click.prevent.stop="editItemModal = true; editItemId = relatedItem.id; editMetadataId = relatedItemGroup.metadata_id;"
                                                :aria-label="$i18n.getFrom('items','edit_item')">
                                            <span
                                                    v-tooltip="{
                                                        content: $i18n.get('edit'),
                                                        autoHide: true,
                                                        placement: 'auto'
                                                    }"
                                                    class="icon">
                                                <i class="has-text-secondary tainacan-icon tainacan-icon-1-25em tainacan-icon-edit" />
                                            </span>
                                        </a>
                                    </div>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
                <b-modal 
                        :width="1200"
                        :active.sync="editItemModal"
                        @close="reloadRelatedItems"
                        custom-class="tainacan-modal"
                        :close-button-aria-label="$i18n.get('close')">
                    <iframe 
                            width="100%"
                            :style="{ height: (isMobileScreen ? '100vh' : '85vh') }"
                            :src="adminFullURL + $routerHelper.getItemEditPath(collectionId, editItemId) + '?iframemode=true&editingmetadata=' + editMetadataId + (isMobileMode ? '&mobilemode=true' : '')" />
                </b-modal>
            </div>
        </div>
    </div>
</template>

<script>
    import { mapActions } from 'vuex';
    export default {
        name: 'RelatedItemsList',
        props: {
            relatedItems: Object,
            isLoading: Boolean,
            isEditable: Boolean,
            itemId: String,
            collectionId: String,
            isMobileScreen: Boolean
        },
        data() {
            return {
                editMetadataId: false,
                editItemId: false,
                editItemModal: false,
                adminFullURL: tainacan_plugin.admin_url + 'admin.php?page=tainacan_admin#',
                isUpdatingRelatedItems: false
            }
        },
        computed: {
            relatedItemsArray() {
                return this.relatedItems ? Object.values(this.relatedItems).filter((aRelatedItemGroup) => aRelatedItemGroup.total_items) : [];
            },
            displayLoading() {
                return this.isLoading || this.isUpdatingRelatedItems;
            },
            isMobileMode() {
                return this.$route && this.$route.query && this.$route.query.mobilemode;
            }
        },
        watch: {
            editItemModal() {
                if (this.editItemModal) {
                    window.addEventListener('message', this.updateReloadItemsAfterModal, false);
                } else {
                    this.editItemId = false;
                    this.editMetadataId = false;
                    window.removeEventListener('message', this.updateReloadItemsAfterModal);
                }
            }
        },
        methods: {
            ...mapActions('item', [
                'fetchOnlyRelatedItems'
            ]),
            openItemOnNewTab(relatedItem) {
                if (relatedItem && relatedItem.id) {
                    let routeData = this.$router.resolve(this.$routerHelper.getItemPath(this.collectionId, relatedItem.id));
                    window.open(routeData.href, '_blank');
                }
            },
            reloadRelatedItems() {
                this.isUpdatingRelatedItems = true;
                this.fetchOnlyRelatedItems({
                    itemId: this.itemId,
                    contextEdit: true
                })
                .then(() => this.isUpdatingRelatedItems = false)
                .catch((error) => {
                    this.$console.error(error);
                    this.isUpdatingRelatedItems = false;
                });
            },
            updateReloadItemsAfterModal(event) {
                const message = event.message ? 'message' : 'data';
                const data = event[message];

                if (data.type == 'itemEditionMessage') {
                    this.editItemModal = false;
                }  
            }
        }
    }
</script>
        
<style lang="scss" scoped>
    .section-status {
        margin-left: -0.875rem;
        font-size: 0.875em;

        .field {

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
                margin-bottom: 2rem;
            }
            
            .related-item-group__items-list {
                list-style: none;
                padding: 0px;
                margin-bottom: 1rem;
                 -moz-column-count: 2;
                -webkit-column-count: 2;
                column-count: 2;

                @media screen and (max-width: 768px) {
                    -moz-column-count: 1;
                    -webkit-column-count: 1;
                    column-count: 1;
                }

                li {
                    display: flex;
                    align-items: center;
                    padding: 8px 32px 8px 6px;
                    width: 100%;
                    position: relative;
                    overflow-x: hidden;
                    
                    &:hover {
                        cursor: pointer;
                        background-color: var(--tainacan-item-hover-background-color);
                        .actions-cell {
                            opacity: 1;
                            visibility: 1;
                            right: 0;
                        }
                    }
                }
                .actions-cell {
                    position: absolute;
                    right: -32px;
                    padding: 0 6px;
                    min-height: 28px;
                    min-width: 28px;
                    opacity: 0;
                    visibility: 0;
                    transition: right 0.3s ease;
                }
                .table-thumb {
                    display: block;
                    min-height: 28px;
                    min-width: 28px;
                    margin-left: 2px;
                    margin-right: 8px;
                }
            }
        }
    }
</style>
