<template>
    <div :class="{ 'is-flex is-flex-wrap-wrap': itemMetadatum.metadatum.multiple != 'yes' || maxtags != undefined }">
        <b-tabs
                size="is-small"
                animated
                v-model="activeTab">
            <b-tab-item 
                    style="margin: 0 -0.75rem;"
                    :label="$i18n.get('label_insert_items')">
                <b-taginput
                        expanded
                        :disabled="disabled"
                        :id="relationshipInputId"
                        size="is-small"
                        icon="magnify"
                        :value="selected"
                        @input="onInput"
                        @blur="onBlur"
                        :data="options"
                        :maxtags="maxtags != undefined ? maxtags : (itemMetadatum.metadatum.multiple == 'yes' || allowNew === true ? null : 1)"
                        autocomplete
                        :remove-on-keys="[]"
                        :dropdown-position="isLastMetadatum ? 'top' :'auto'"
                        attached
                        :placeholder="$i18n.get('instruction_type_existing_item')"
                        :loading="isLoading"
                        :aria-close-label="$i18n.get('remove_value')"
                        :class="{'has-selected': selected != undefined && selected != []}"
                        field="label"
                        @typing="search"
                        check-infinite-scroll
                        @infinite-scroll="searchMore"
                        :has-counter="false">
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
                        <div class="tainacan-relationship-group">
                            <div v-html="props.option.valuesAsHtml" />
                        </div>
                    </template>
                    <template 
                            v-if="!isLoading"
                            slot="empty">
                        {{ $i18n.get('info_no_item_found') }}
                    </template>
                    <template
                            v-if="currentUserCanEditItems && !($route && $route.query.iframemode)" 
                            slot="footer">
                        <a @click="editItemModalOpen = true">
                            {{ $i18n.get('label_crate_new_item') + ' "' + searchQuery + '"' }}
                        </a>
                    </template>
                </b-taginput>
            </b-tab-item>
            <b-tab-item
                    v-if="itemMetadatum && isDisplayingRelatedItemMetadata"
                    style="min-height: 56px;"
                    :label="( itemMetadatum.value.length == 1 ? $i18n.get('label_selected_item') : $i18n.get('label_selected_items') ) + ' (' + itemMetadatum.value.length + ')'">
                <div class="tainacan-relationship-results-container">
                    <div
                            v-html="itemMetadatum.value_as_html" 
                            :ref="relationshipInputId + '_results-container'"
                        />
                </div>
            </b-tab-item>
        </b-tabs>
        <a
                v-if="currentUserCanEditItems && itemMetadatum.item && itemMetadatum.item.id"
                :disabled="!$route || $route.query.iframemode"
                @click="editItemModalOpen = !editItemModalOpen"
                class="add-link">
            <span class="icon is-small">
                <i class="tainacan-icon has-text-secondary tainacan-icon-add"/>
            </span>
            &nbsp;{{ $i18n.get('label_crate_new_item') }}
        </a>
        <b-modal 
                :width="1200"
                :active.sync="editItemModalOpen"
                custom-class="tainacan-modal">
            <iframe 
                    :id="relationshipInputId + '_item-edition-modal'"
                    width="100%"
                    style="height: 85vh"
                    :src="itemModalSrc" />
        </b-modal>
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
            isLastMetadatum: false
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
                adminFullURL: tainacan_plugin.admin_url + 'admin.php?page=tainacan_admin#',
                currentUserCanEditItems: false
            }
        },
        computed: {
            collection() {
                return this.getCollection();
            },
            itemModalSrc() {
                if (this.editingItemId)
                    return this.adminFullURL + this.$routerHelper.getItemEditPath(this.collectionId, this.editingItemId) + '?iframemode=true';
                else
                    return this.adminFullURL + this.$routerHelper.getNewItemPath(this.collectionId) + '?iframemode=true&newmetadatumid=' + this.itemMetadatum.metadatum.metadata_type_options.search + '&newitemtitle=' + this.searchQuery;
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
            }
        },
        watch: {
            editItemModalOpen() {
                if (this.editItemModalOpen)
                    window.addEventListener('message', this.updateItemFromModal, false);
                else
                    window.removeEventListener('message', this.updateItemFromModal);
            },
            itemMetadatum(newValue, oldValue) {
                if (newValue.value_as_html !== oldValue.value_as_html)
                    this.$nextTick(() => this.renderValuesAsHtml()); 
            }
        },
        created() {
            this.collectionId = ( this.itemMetadatum && this.itemMetadatum.metadatum.metadata_type_options && this.itemMetadatum.metadatum.metadata_type_options.collection_id ) ? this.itemMetadatum.metadatum.metadata_type_options.collection_id : '';
            
            if (this.itemMetadatum.value && (Array.isArray( this.itemMetadatum.value ) ? this.itemMetadatum.value.length > 0 : true )) {
                let query = qs.stringify({ postin: ( Array.isArray( this.itemMetadatum.value ) ) ? this.itemMetadatum.value : [ this.itemMetadatum.value ]  });
                query += this.itemMetadatum.metadatum.metadata_type_options.search ? ('&fetch_only_meta=' + this.itemMetadatum.metadatum.metadata_type_options.search + (this.isDisplayingRelatedItemMetadata ? this.itemMetadatum.metadatum.metadata_type_options.display_related_item_metadata.filter(metadatumId => metadatumId !== 'thumbnail') : '')) : '';
                axios.get('/collection/' + this.collectionId + '/items?' + query + '&nopaging=1&fetch_only=title,document_mimetype,thumbnail&order=asc')
                    .then( res => {
                        if (res.data.items) {
                            for (let item of res.data.items)
                                this.selected.push({
                                    label: this.getItemLabel(item),
                                    value: item.id,
                                    img: this.$thumbHelper.getSrc(item['thumbnail'], 'tainacan-small', item.document_mimetype)
                                });
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
        mounted() {
            this.renderValuesAsHtml();
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
                                    if (this.isDisplayingRelatedItemMetadata) {
                                        this.options.push({
                                            label: this.getItemLabel(item),
                                            value: item.id,
                                            valuesAsHtml: this.getItemMetadataValuesAsHtml(item)
                                        });
                                    } else {
                                        this.options.push({
                                            label: this.getItemLabel(item),
                                            value: item.id,
                                            img: this.$thumbHelper.getSrc(item['thumbnail'], 'tainacan-small', item.document_mimetype)
                                        });
                                    }
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
                query['fetch_only'] = 'title,thumbnail';
                query['fetch_only_meta'] = this.itemMetadatum.metadatum.metadata_type_options.search + (this.isDisplayingRelatedItemMetadata ? this.itemMetadatum.metadatum.metadata_type_options.display_related_item_metadata.filter(metadatumId => metadatumId !== 'thumbnail') : '');
                query['perpage'] = 12;
                query['paged'] = this.page;
                query['order'] = 'asc';

                if (this.selected.length > 0)
                    query['exclude'] = this.selected.map((item) => item.value);

                return qs.stringify(query);
            },
            updateItemFromModal(event) {
                const message = event.message ? 'message' : 'data';
                const data = event[message];

                if (data.type == 'itemEditionMessage') {
                    this.editItemModalOpen = false;

                    // An item is being edited from the modal
                    if (this.editingItemId) {
                        this.onInput(this.selected);

                    // An item is being created from the modal
                    } else {

                        if (data.itemId) {
                            this.searchQuery = '';
                            this.selected.push({
                                label: data.itemTitle,
                                value: data.itemId,
                                img: data.itemThumbnail ? data.itemThumbnail : ''
                            });
                            this.onInput(this.selected);
                        }
                    }
                }
            },
            getItemMetadataValuesAsHtml(item) {
                
                let valuesAsHtml = '';
                valuesAsHtml += `<div class="tainacan-relationship-metadatum" data-item-id="${ item.id }">
                    <div class="tainacan-relationship-metadatum-header">
                        <img src="${ this.$thumbHelper.getSrc(item['thumbnail'], 'tainacan-small', item.document_mimetype) }" class="attachment-tainacan-small size-tainacan-small" alt="${ item.thumbnail_alt }" loading="lazy" width="40" height="40">
                        <h4 class="label">
                            <span data-id="${ item.id }">${ item.title }</span>
                        </h4>
                    </div>
                    `;
                Object.values(item.metadata).forEach(metadatumValue => {
                    valuesAsHtml += `<div class="tainacan-metadatum">
                        <h5 class="label">
                            ${ metadatumValue.name }
                        </h5>
                        <p>
                            ${ metadatumValue.value_as_html }
                        </p>
                    </div>`;
                });
                valuesAsHtml += `</div>`;

                return valuesAsHtml;
            },
            renderValuesAsHtml() {
                if (
                    this.itemMetadatum &&
                    this.itemMetadatum.metadatum &&
                    this.$refs[this.relationshipInputId + '_results-container']
                ) {
                    let valuesAsHtml = this.$refs[this.relationshipInputId + '_results-container'];
                    
                    if (
                        valuesAsHtml &&
                        valuesAsHtml.childNodes &&
                        valuesAsHtml.childNodes.length &&
                        valuesAsHtml.childNodes[0] &&
                        valuesAsHtml.childNodes[0].childNodes &&
                        valuesAsHtml.childNodes[0].childNodes.length
                    ) {
                        valuesAsHtml.childNodes[0].childNodes.forEach(element => {
                            if ( element.classList && element.classList.contains('tainacan-relationship-metadatum') ) {
                                const relatedItemId = element.getAttribute('data-item-id');
                           
                                if (this.currentUserCanEditItems && this.$route && !this.$route.query.iframemode) {
                                    const editButton = document.createElement('a');
                                    editButton.classList.add('relationship-value-button--edit');
                                    editButton.innerHTML = '<span class="icon"><i class="tainacan-icon tainacan-icon-edit"></i></span>';
                                    editButton.addEventListener('click', (event) => {
                                        event.preventDefault();
                                        this.editingItemId = relatedItemId;
                                        this.editItemModalOpen = true;
                                    });
                                    element.appendChild(editButton);
                                }

                                const removeButton = document.createElement('a');
                                removeButton.classList.add('relationship-value-button--remove');
                                removeButton.innerHTML = '<span class="icon"><i class="tainacan-icon tainacan-icon-close"></i></span>';
                                removeButton.addEventListener('click', (event) => {
                                    event.preventDefault();
                                    const indexOfRemovedItem = this.selected.findIndex(itemValue => itemValue.value == relatedItemId);

                                    if (indexOfRemovedItem >= 0) {
                                        this.selected.splice(indexOfRemovedItem, 1);
                                        this.onInput(this.selected);
                                    }
                                });
                                
                                element.appendChild(removeButton);
                            }
                        });
                    }
                }
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
    }
    /deep/ .b-tabs .tab-content {
        border: 1px solid var(--tainacan-gray1);
        padding: 0;
    }
    .tainacan-relationship-results-container {
        background-color: var(--tainacan-white);
        margin-top: -1px;
        display: flex;
        overflow: auto;
        padding: 12px 12px 24px 12px;
        max-height: 40vh;
        transition: heigth 0.5s ease, min-height 0.5s ease;

        /deep/ .tainacan-relationship-group .tainacan-relationship-metadatum {
             .tainacan-metadatum .label {
                font-size: 0.75em;
            }
            .tainacan-relationship-metadatum-header {
                padding-right: 64px;
                .label{
                    font-size: 0.875em;
                }
            }
        }
    }
    /deep/ .tainacan-relationship-group {
        width: 100%;
        .tainacan-relationship-metadatum {
            position: relative;
            .label {
                font-size: 1em;
            }
        }
        .tainacan-relationship-metadatum-header .label {
            font-size: 1.125em;
        }
    }
    /deep/ .relationship-value-button--edit,
    /deep/ .relationship-value-button--remove {
        position: absolute;
        top: 4px;
        right: 4px;
        background-color: var(--tainacan-white);
        border-radius: 100%;
        padding: 2px;
    }
    /deep/ .relationship-value-button--edit {
        right: 34px;
    }
</style>
