<template>
    <div>
        <div class="table-container">
            <b-loading
                    v-model="displayLoading" 
                    :is-full-page="false" />
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
                                                <i class="tainacan-icon tainacan-icon-collection" />
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
                                                <i class="tainacan-icon tainacan-icon-metadata" />
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
                                                :to="$routerHelper.getCollectionItemsPath(relatedItemGroup.collection_id, { metaquery: [{ key: relatedItemGroup.metadata_id, value: [itemId], compare: 'IN' }] })">
                                            {{ $i18n.getWithVariables('label_view_all_%s_related_items', [relatedItemGroup.total_items]) }}
                                        </b-button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <ul class="related-item-group__items-list tainacan-compact-items-list">
                            <li
                                    v-for="(relatedItem, itemIndex) of relatedItemGroup.items"
                                    :key="itemIndex">
                                
                                <div
                                        class="status-cell"
                                        @click="openItemOnNewTab(relatedItem)">
                                    <span 
                                            v-if="$statusHelper.hasIcon(relatedItem.status)"
                                            v-tooltip="{
                                                content: $i18n.get('status_' + relatedItem.status),
                                                autoHide: true,
                                                placement: 'top',
                                                popperClass: ['tainacan-tooltip', 'tooltip']
                                            }"
                                            class="icon has-text-gray">
                                        <i 
                                                class="tainacan-icon tainacan-icon-1em"
                                                :class="$statusHelper.getIcon(relatedItem.status)"
                                            />
                                    </span>
                                </div>
                                <div @click="openItemOnNewTab(relatedItem)">
                                    <span class="item-thumb">
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
                                        style="width: 100%"
                                        @click="openItemOnNewTab(relatedItem)">
                                    <p
                                            v-tooltip="{
                                                delay: {
                                                    show: 500,
                                                    hide: 300,
                                                },
                                                content: relatedItem.title != undefined && relatedItem.title != '' ? relatedItem.title : `<span class='has-text-gray3 is-italic'>` + $i18n.get('label_value_not_provided') + `</span>`,
                                                html: true,
                                                autoHide: false,
                                                placement: 'top',
                                                popperClass: ['tainacan-tooltip', 'tooltip']
                                            }"
                                            v-html="(relatedItem.title != undefined && relatedItem.title != '') ? relatedItem.title : `<span class='has-text-gray3 is-italic'>` + $i18n.get('label_value_not_provided') + `</span>`" />
                                </div>
                                <div 
                                        v-if="isEditable && relatedItem.current_user_can_edit"
                                        class="actions-area"
                                        :label="$i18n.get('label_actions')">
                                    <div class="actions-container">
                                        <a
                                                v-if="!relatedItem.status != 'trash'"
                                                id="button-edit"
                                                :aria-label="$i18n.getFrom('items','edit_item')"
                                                @click.prevent.stop="setItemForEdit(relatedItem, relatedItemGroup)">
                                            <span
                                                    v-tooltip="{
                                                        delay: {
                                                            show: 500,
                                                            hide: 100,
                                                        },
                                                        content: $i18n.get('edit'),
                                                        autoHide: true,
                                                        placement: 'auto',
                                                        popperClass: ['tainacan-tooltip', 'tooltip']
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
                        v-model="editItemModal"
                        :width="1200"
                        custom-class="tainacan-modal"
                        :close-button-aria-label="$i18n.get('close')"
                        @after-leave="reloadRelatedItems">
                    <iframe 
                            width="100%"
                            :style="{ height: (isMobileScreen ? '100vh' : '85vh') }"
                            :src="adminURL + 'itemEditionMode=true' + ($adminOptions.mobileAppMode ? '&mobileAppMode=true' : '') + '&page=tainacan_admin#' + $routerHelper.getItemEditPath(editCollectionId, editItemId) + '?editingmetadata=' + editMetadataId" />
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
            collectionId: [String, Number],
            isMobileScreen: Boolean
        },
        data() {
            return {
                editMetadataId: false,
                editItemId: false,
                editCollectionId: false,
                editItemModal: false,
                adminURL: tainacan_plugin.admin_url + '?',
                isUpdatingRelatedItems: false
            }
        },
        computed: {
            relatedItemsArray() {
                return this.relatedItems ? Object.values(this.relatedItems).filter((aRelatedItemGroup) => aRelatedItemGroup.total_items) : [];
            },
            displayLoading() {
                return this.isLoading || this.isUpdatingRelatedItems;
            }
        },
        watch: {
            editItemModal() {
                if (this.editItemModal) {
                    window.addEventListener('message', this.updateReloadItemsAfterModal, false);
                } else {
                    this.editItemId = false;
                    this.editCollectionId = false;
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
                if (relatedItem && relatedItem.id && relatedItem.collection_id) {
                    let routeData = this.$router.resolve(this.$routerHelper.getItemPath(relatedItem.collection_id, relatedItem.id));
                    window.open(routeData.href, '_blank');
                }
            },
            setItemForEdit(aRelatedItem, aRelatedItemGroup) {
                this.editItemId = aRelatedItem.id;
                this.editCollectionId = aRelatedItem.collection_id;
                this.editMetadataId = aRelatedItemGroup.metadata_id;
                this.editItemModal = true;
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

                if (data.type == 'itemEditionMessage' && data.item !== null) {
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
                border-bottom: 1px dashed var(--tainacan-gray3);
                margin-bottom: 2rem;
            }
        }
    }
</style>
