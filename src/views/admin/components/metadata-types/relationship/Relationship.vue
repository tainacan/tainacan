<template>
    <div :class="{ 'is-flex is-flex-wrap-wrap': itemMetadatum.metadatum.multiple != 'yes' || maxtags != undefined }">
        <b-tabs
                size="is-small"
                animated
                v-model="activeTab">
            <b-tab-item :label="$i18n.get('label_insert_items')">
                <b-taginput
                        expanded
                        :disabled="disabled"
                        :id="relationshipInputId"
                        size="is-small"
                        icon="magnify"
                        :value="selected"
                        @input="onInput"
                        @blur="onBlur"
                        @add="onAdd"
                        @remove="onRemove"
                        :data="options"
                        :maxtags="maxtags != undefined ? maxtags : (itemMetadatum.metadatum.multiple == 'yes' || allowNew === true ? (maxMultipleValues !== undefined ? maxMultipleValues : null) : '1')"
                        autocomplete
                        :remove-on-keys="[]"
                        :dropdown-position="isLastMetadatum ? 'top' :'auto'"
                        attached
                        :placeholder="itemMetadatum.metadatum.placeholder ? itemMetadatum.metadatum.placeholder : $i18n.get('instruction_type_existing_item')"
                        :loading="isLoading"
                        :aria-close-label="$i18n.get('remove_value')"
                        :class="{'has-selected': selected != undefined && selected != []}"
                        field="label"
                        @typing="search"
                        check-infinite-scroll
                        @infinite-scroll="searchMore"
                        :has-counter="false"
                        @focus="onMobileSpecialFocus">
                    <template slot-scope="props">
                        <div 
                                v-if="!isDisplayingRelatedItemMetadata"
                                class="media">
                            <div 
                                    v-if="props.option.img"
                                    class="media-left">
                                <img 
                                        width="28"
                                        :src="props.option.img">
                            </div>
                            <div class="media-content">
                                {{ props.option.label }}
                            </div>
                        </div>
                        <div 
                                v-else
                                class="tainacan-relationship-group">
                            <div v-html="props.option.valuesAsHtml" />
                        </div>
                    </template>
                    <template 
                            v-if="!isLoading"
                            slot="empty">
                        {{ $i18n.get('info_no_item_found') }}
                    </template>
                    <template
                            v-if="currentUserCanEditItems && (!$adminOptions.itemEditionMode || $adminOptions.allowItemEditionModalInsideModal)" 
                            slot="footer">
                        <a @click="editItemModalOpen = true">
                            {{ $i18n.get('label_create_new_item') + ' "' + searchQuery + '"' }}
                        </a>
                    </template>
                </b-taginput>
            </b-tab-item>
            <b-tab-item
                    v-if="itemMetadatum && itemMetadatum.value !== undefined"
                    style="min-height: 56px;"
                    :label="( itemMetadatum.value.length == 1 || itemMetadatum.metadatum.multiple != 'yes' ) ? $i18n.get('label_selected_item') : ( $i18n.get('label_selected_items') + ' (' + itemMetadatum.value.length + ')' )">
                <div class="tainacan-relationship-results-container">
                    <div 
                            v-if="itemMetadatum.value && itemMetadatum.value.length"
                            class="tainacan-relationship-group">
                        <div 
                                v-for="(itemValue, index) of selected"
                                :key="index"
                                style="position: relative;">
                            <div v-html="itemValue.valuesAsHtml" />
                            <a 
                                    v-if="currentUserCanEditItems && (!$adminOptions.itemEditionMode || $adminOptions.allowItemEditionModalInsideModal)"
                                    @click="editSelected(itemValue.value)"
                                    class="relationship-value-button--edit">
                                <span class="icon">
                                    <i class="tainacan-icon tainacan-icon-edit" />
                                </span>
                            </a>
                            <a 
                                    @click="removeFromSelected(itemValue.value)"
                                    class="relationship-value-button--remove">
                                <span class="icon">
                                    <i class="tainacan-icon tainacan-icon-close" />
                                </span>
                            </a>
                            <span
                                    v-if="index < selected.length - 1"
                                    class="multivalue-separator"> | </span>
                        </div>
                    </div>
                    <div v-else>
                        <p
                                class="has-text-gray"
                                style="font-size: 0.875em;">
                            {{ $i18n.get('info_no_item_found') }}
                        </p>
                    </div>
                </div>
            </b-tab-item>
        </b-tabs>
        <template 
                v-if="currentUserCanEditItems && 
                    itemMetadatum.item &&
                    itemMetadatum.item.id">
            <a
                    v-if="(maxMultipleValues === undefined || maxMultipleValues > selected.length) &&
                            (itemMetadatum.metadatum.multiple === 'yes' || !selected.length )"
                    :disabled="$adminOptions.itemEditionMode && !$adminOptions.allowItemEditionModalInsideModal"
                    @click="editItemModalOpen = !editItemModalOpen"
                    class="add-link">
                <span class="icon is-small">
                    <i class="tainacan-icon has-text-secondary tainacan-icon-add"/>
                </span>
                &nbsp;{{ $i18n.get('label_create_new_item') }}
            </a>
            <b-modal 
                    :width="1200"
                    :active.sync="editItemModalOpen"
                    :custom-class="'tainacan-modal' + (collection && collection.id ? ' tainacan-modal-item-edition--collection-' + collection.id : '')"
                    :close-button-aria-label="$i18n.get('close')">
                <iframe 
                        :id="relationshipInputId + '_item-edition-modal'"
                        width="100%"
                        :style="{ height: (isMobileScreen ? '100vh' : '85vh') }"
                        :src="itemModalSrc" />
            </b-modal>
        </template>
    </div>
</template>

<script>
    import { tainacan as axios } from '../../../js/axios';
    import { mapGetters } from 'vuex';
    import qs from 'qs';

    export default {
        props: {
            itemMetadatum: Object,
            maxtags: undefined,
            disabled: false,
            allowNew: true,
            isLastMetadatum: false,
            isMobileScreen: false
        },
        data() {
            return {
                selected:[],
                options: [],
                isLoading: false,
                collectionId: '',
                searchQuery: '',
                editingItemId: '',
                totalItems: 0,
                page: 1,
                activeTab: 0,
                editItemModalOpen: false,
                adminURL: tainacan_plugin.admin_url + 'admin.php?',
                currentUserCanEditItems: false,
                selectedValuesAsHtml: []
            }
        },
        computed: {
            collection() {
                return this.getCollection();
            },
            maxMultipleValues() {
                return (
                    this.itemMetadatum &&
                    this.itemMetadatum.metadatum &&
                    this.itemMetadatum.metadatum.cardinality &&
                    !isNaN(this.itemMetadatum.metadatum.cardinality) &&
                    this.itemMetadatum.metadatum.cardinality > 1
                ) ? this.itemMetadatum.metadatum.cardinality : undefined;
            },
            itemModalSrc() {
                if (this.editingItemId)
                    return this.adminURL + 'itemEditionMode=true' + (this.$adminOptions.mobileAppMode ? '&mobileAppMode=true' : '') + '&page=tainacan_admin#' + this.$routerHelper.getItemEditPath(this.collectionId, this.editingItemId);
                else
                    return this.adminURL + 'itemEditionMode=true' + (this.$adminOptions.mobileAppMode ? '&mobileAppMode=true' : '') + '&page=tainacan_admin#' + this.$routerHelper.getNewItemPath(this.collectionId) + '?newmetadatumid=' + this.itemMetadatum.metadatum.metadata_type_options.search + '&newitemtitle=' + this.searchQuery;
            },
            relationshipInputId() {
                if (this.itemMetadatum && this.itemMetadatum.metadatum)
                    return 'tainacan-item-metadatum_id-' + this.itemMetadatum.metadatum.id + (this.itemMetadatum.parent_meta_id ? ('_parent_meta_id-' + this.itemMetadatum.parent_meta_id) : '');
                else
                    return '';
            },
            isDisplayingRelatedItemMetadata() {
                return this.itemMetadatum &&
                       this.itemMetadatum.metadatum &&
                       this.itemMetadatum.metadatum.metadata_type_options &&
                       this.itemMetadatum.metadatum.metadata_type_options.display_related_item_metadata &&
                       this.itemMetadatum.metadatum.metadata_type_options.display_related_item_metadata.length &&
                       this.itemMetadatum.metadatum.metadata_type_options.display_related_item_metadata.length > 1;
            },
            isAcceptingDraftItems() {
                return this.itemMetadatum &&
                       this.itemMetadatum.metadatum &&
                       this.itemMetadatum.metadatum.metadata_type_options &&
                       this.itemMetadatum.metadatum.metadata_type_options.accept_draft_items === 'yes';
            }
        },
        watch: {
            editItemModalOpen() {
                if (this.editItemModalOpen)
                    window.addEventListener('message', this.updateItemFromModal, false);
                else
                    window.removeEventListener('message', this.updateItemFromModal);
            }
        },
        created() {
            this.collectionId = ( this.itemMetadatum && this.itemMetadatum.metadatum.metadata_type_options && this.itemMetadatum.metadatum.metadata_type_options.collection_id ) ? this.itemMetadatum.metadatum.metadata_type_options.collection_id : '';

            if (this.itemMetadatum.value && (Array.isArray( this.itemMetadatum.value ) ? this.itemMetadatum.value.length > 0 : true )) {
                let query = [];
                
                query['postin'] = Array.isArray( this.itemMetadatum.value ) ? this.itemMetadatum.value : [ this.itemMetadatum.value ];
                query['nopaging'] = 1;
                query['order'] = 'asc';
                query['fetch_only'] = 'title,document_mimetype,thumbnail';
                query['fetch_only_meta'] = this.isDisplayingRelatedItemMetadata ? (this.itemMetadatum.metadatum.metadata_type_options.display_related_item_metadata.filter(metadatumId => metadatumId !== 'thumbnail') + '') : (this.itemMetadatum.metadatum.metadata_type_options.search ? this.itemMetadatum.metadatum.metadata_type_options.search : '');
                if (this.isAcceptingDraftItems)
                    query['status'] = ['publish','private','draft'];

                axios.get('/collection/' + this.collectionId + '/items?' + qs.stringify(query) )
                    .then( res => {
                        if (res.data.items) {
                            for (let item of res.data.items) {
                                this.selected.push({
                                    label: this.getItemLabel(item),
                                    value: item.id,
                                    valuesAsHtml: this.getItemMetadataValuesAsHtml(item),
                                    img: this.$thumbHelper.getSrc(item['thumbnail'], 'tainacan-small', item.document_mimetype)
                                });
                            }
                        }
                    })
                    .catch(error => {
                        this.$console.log(error);
                    });
            }

            // Checks if current user can edit itens on the related collection to offer modal
            if (this.collection && this.collection.id == this.collectionId)
                this.currentUserCanEditItems = this.collection.current_user_can_edit_items;
            else {
                axios.get('/collections/' + this.collectionId + '?fetch_only=name,url,allow_comments&context=edit')
                    .then(res => this.currentUserCanEditItems = res.data.current_user_can_edit_items )
                    .catch(() => this.currentUserCanEditItems = false );
            }
        },
        methods: {
            ...mapGetters('collection', [
                'getCollection'
            ]),
            onInput(newSelected) {
                // First we reset the input
                this.search('');

                this.selected = newSelected;
                this.$emit('input', newSelected.map((item) => item.value));
            },
            onBlur() {
                this.$emit('blur');
            },
            search: _.debounce(function(query) {

                // String update
                if (query != this.searchQuery) {
                    this.searchQuery = query;
                    this.options = [];
                    this.page = 1;
                } 
                
                // String cleared
                if (!query.length) {
                    this.searchQuery = query;
                    this.options = [];
                    this.page = 1;
                }

                // No need to load more
                if (this.page > 1 && this.options.length > this.totalItems*12)
                    return;

                // There is already one value set and is not multiple
                if (this.selected.length > 0 && this.itemMetadatum.metadatum.multiple === 'no')
                    return;

                if (this.searchQuery !== '') {
                    this.isLoading = true;

                    axios.get('/collection/' + this.collectionId + '/items?' + this.getQueryString(this.searchQuery))
                        .then( res => {

                            if (res.data.items) {
                                for (let item of res.data.items) {
                                    this.options.push({
                                        label: this.getItemLabel(item),
                                        value: item.id,
                                        valuesAsHtml: this.getItemMetadataValuesAsHtml(item),
                                        img: this.$thumbHelper.getSrc(item['thumbnail'], 'tainacan-small', item.document_mimetype)
                                    });
                                }
                            }
                            if (res.headers['x-wp-total'])
                                this.totalItems = res.headers['x-wp-total'];
                            
                            this.page++;

                            this.isLoading = false;
                        })
                        .catch(error => {
                            this.isLoading = false;
                            this.$console.log(error);
                        });
                }

            }, 500),
            searchMore: _.debounce(function () {
                this.search(this.searchQuery)
            }, 250),
            getItemLabel(item) {
                let label = '';
                for (let m in item.metadata) {
                    if (item.metadata[m].id == this.itemMetadatum.metadatum.metadata_type_options.search)
                        label = item.metadata[m].value_as_string;
                }
                if (label != '' && label != item.title && item.title != '')
                    label += ' (' + item.title + ')';
                else if (label == '')
                    label = item.title;
                
                return label;
            },
            getQueryString( search ) {
                let query = [];
                if (this.itemMetadatum.metadatum.metadata_type_options &&
                    this.itemMetadatum.metadatum.metadata_type_options.search)
                {
                    if (this.itemMetadatum.metadatum.metadata_type_options.search_by_tax) {
                        query['taxquery'] = [];

                        query['taxquery'][0] = {
                            taxonomy: `tnc_tax_${this.itemMetadatum.metadatum.metadata_type_options.search_by_tax}`,
                            operator: 'LIKE',
                            taxonomy_id : this.itemMetadatum.metadatum.metadata_type_options.search_by_tax,
                            terms: search
                        }
                    } else {
                        query['metaquery'] = [];
                    
                        query['metaquery'][0] = {
                            key: this.itemMetadatum.metadatum.metadata_type_options.search,
                            value: search,
                            compare: 'LIKE'
                        }

                        // Sorting options depend on metadata type. Notice that this won't work with taxonomies
                        switch(this.itemMetadatum.metadatum.metadata_type_options.related_primitive_type) {
                            case 'float':
                            case 'int':
                                query['orderby'] = 'meta_value_num';
                                query['metakey'] = this.itemMetadatum.metadatum.metadata_type_options.search;
                            break;
                            case 'date':
                                query['orderby'] = 'meta_value';
                                query['metakey'] = this.itemMetadatum.metadatum.metadata_type_options.search;
                                query['metatype'] = 'DATETIME';
                            break;
                            default:
                                query['orderby'] = 'meta_value';
                                query['metakey'] = this.itemMetadatum.metadatum.metadata_type_options.search;
                        }
                    }
                    
                } else {
                    query['search'] = search;
                }
                query['fetch_only'] = 'title,thumbnail,document_mimetype';
                query['fetch_only_meta'] = this.isDisplayingRelatedItemMetadata ? (this.itemMetadatum.metadatum.metadata_type_options.display_related_item_metadata.filter(metadatumId => metadatumId !== 'thumbnail') + '') : (this.itemMetadatum.metadatum.metadata_type_options.search ? this.itemMetadatum.metadatum.metadata_type_options.search : '');
                query['perpage'] = 12;
                query['paged'] = this.page;
                query['order'] = 'asc';

                if (this.isAcceptingDraftItems)
                    query['status'] = ['publish','private','draft'];

                if (this.selected.length > 0)
                    query['exclude'] = this.selected.map((item) => item.value);

                return qs.stringify(query);
            },
            updateItemFromModal(event) {
                const message = event.message ? 'message' : 'data';
                const data = event[message];

                if (data.type == 'itemEditionMessage' && data.item !== null) {
                    this.editItemModalOpen = false;

                    // An item is being edited from the modal
                    if (this.editingItemId) {
                        if (data.item && data.item.id) {
                            const existingItemIndex = this.selected.findIndex(anItemValue => anItemValue.value == data.item.id); 
                            
                            if (existingItemIndex >= 0) {
                                this.selected.splice(existingItemIndex, 1, {
                                    label: this.getItemLabel(data.item),
                                    value: data.item.id,
                                    valuesAsHtml: this.getItemMetadataValuesAsHtml(data.item),
                                    img: data.item.thumbnail ? data.item.thumbnail : ''
                                });
                            }
                            this.onInput(this.selected);
                        }
                        this.editingItemId = '';

                    // An item is being created from the modal
                    } else {
                        
                        if (data.item && data.item.id) {
                            this.searchQuery = '';

                            this.selected.push({
                                label: this.getItemLabel(data.item),
                                value: data.item.id,
                                valuesAsHtml: this.getItemMetadataValuesAsHtml(data.item),
                                img: data.item.thumbnail ? data.item.thumbnail : ''
                            });

                            this.onInput(this.selected);
                        }
                    }
                }
            },
            getItemMetadataValuesAsHtml(item) {
                
                let valuesAsHtml = '';
                valuesAsHtml += `<div class="tainacan-relationship-metadatum" data-item-id="${ item.id }">
                    <div class="tainacan-relationship-metadatum-header">`;
                    if (
                        this.isDisplayingRelatedItemMetadata &&
                        this.itemMetadatum.metadatum.metadata_type_options.display_related_item_metadata &&
                        this.itemMetadatum.metadatum.metadata_type_options.display_related_item_metadata.indexOf('thumbnail') >= 0
                    )
                        valuesAsHtml += `<img src="${ this.$thumbHelper.getSrc(item['thumbnail'], 'tainacan-small', item.document_mimetype) }" class="attachment-tainacan-small size-tainacan-small" alt="${ item.thumbnail_alt }" loading="lazy" width="40" height="40">`;
                
                    valuesAsHtml += `<h4 class="label">
                        ${ this.getItemLabel(item) }
                    </h4>`;
                valuesAsHtml += `</div>`;
                
                Object.values(item.metadata).forEach(metadatumValue => {
                    if (
                        metadatumValue.id != this.itemMetadatum.metadatum.metadata_type_options.search &&
                        this.itemMetadatum.metadatum.metadata_type_options.display_related_item_metadata &&
                        this.itemMetadatum.metadatum.metadata_type_options.display_related_item_metadata.indexOf(metadatumValue.id) >= 0 &&
                        metadatumValue.value_as_html
                    ) {
                        valuesAsHtml += `<div class="tainacan-metadatum">
                            <h5 class="label">
                                ${ metadatumValue.name }
                            </h5>
                            <p>
                                ${ metadatumValue.value_as_html }
                            </p>
                        </div>`;
                    }
                });
                valuesAsHtml += `</div>`;

                return valuesAsHtml;
            },
            editSelected(itemId) {
                this.editingItemId = itemId;
                this.editItemModalOpen = true;
            },
            removeFromSelected(itemId) {
                const indexOfRemovedItem = this.selected.findIndex(itemValue => itemValue.value == itemId);

                if (indexOfRemovedItem >= 0) {
                    this.selected.splice(indexOfRemovedItem, 1);
                    this.onInput(this.selected);
                }
            },
            onMobileSpecialFocus() {
                this.$emit('mobileSpecialFocus');
            }
        }
    }
</script>

<style lang="scss" scoped>
    div.is-flex {
        justify-content: flex-start;
    }
    .add-link {
        font-size: 0.75em;
    }
    .b-tabs {
        margin-bottom: 0;
        width: 100%;
    }
    /deep/ .b-tabs .tab-content {
        padding: 0.5em 0px !important;
    }
    /deep/ .tabs {
        margin-bottom: 0 !important;

        ul {
            padding: 0;
        }
    }
    /deep/ .tainacan-relationship-results-container {
        border: 1px solid var(--tainacan-gray1);
        background-color: var(--tainacan-white);
        margin-top: calc(-1 * (0.5em + 1px));
        margin-bottom: calc(-1 * (0.5em + 1px));
        display: flex;
        overflow: auto;
        padding: 12px;
        max-height: 40vh;
        transition: height 0.5s ease, min-height 0.5s ease;

        .tainacan-relationship-group {
            padding-bottom: 12px;
            .tainacan-relationship-metadatum {
                .tainacan-metadatum .label {
                    font-size: 0.75em;
                }
                a {
                    pointer-events: auto;
                }
                .tainacan-relationship-metadatum-header {
                    padding-right: 64px;
                    .label{
                        font-size: 0.875em;
                    }
                }
            }
            &>div>.multivalue-separator {
                display: block;
                max-height: 1px;
                width: calc(100% - 40px);
                background: var(--tainacan-gray2);
                content: none;
                color: transparent;
                margin: 0.5em 0 0.5em 40px;
            }
        }
    }
    /deep/ .tainacan-relationship-group {
        width: 100%;
        .tainacan-relationship-metadatum {
            .label {
                font-size: 1em;
            }
            a {
                pointer-events: none;
            }
            p {
                font-size: 0.875em;
                margin-bottom: 0;
                margin-top: 0;
            }
        }
        .tainacan-relationship-metadatum-header {
            .label {
                font-size: 1.125em;
                font-weight: normal;
            }
            img {
                max-width: 28px !important;
                max-height: 28px;
                margin-right: 10px;
            }
        }
        .relationship-value-button--edit,
        .relationship-value-button--remove {
            position: absolute;
            top: 0px;
        }
    }
    /deep/ .relationship-value-button--edit,
    /deep/ .relationship-value-button--remove {
        right: 4px;
        background-color: var(--tainacan-white);
        border-radius: 100%;
        padding: 2px;

        &:hover {
            background-color: var(--tainacan-gray0);
        }
    }
    /deep/ .relationship-value-button--edit {
        right: 34px;
    }
</style>
