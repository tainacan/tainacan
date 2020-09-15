<template>
    <div :class="{ 'is-flex': itemMetadatum.metadatum.multiple != 'yes' || maxtags != undefined }">
        <b-taginput
                expanded
                :disabled="disabled"
                :id="'tainacan-item-metadatum_id-' + itemMetadatum.metadatum.id + (itemMetadatum.parent_meta_id ? ('_parent_meta_id-' + itemMetadatum.parent_meta_id) : '')"
                size="is-small"
                icon="magnify"
                :value="selected"
                @input="onInput"
                @blur="onBlur"
                :data="options"
                :maxtags="maxtags != undefined ? maxtags : (itemMetadatum.metadatum.multiple == 'yes' || allowNew === true ? 100 : 1)"
                autocomplete
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
                <div class="media">
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
            </template>
            <template 
                    v-if="!isLoading"
                    slot="empty">
                {{ $i18n.get('info_no_item_found') }}
            </template>
            <template
                    v-if="currentUserCanEditItems && !$route.query.iframemode" 
                    slot="footer">
                 <a @click="createNewItemModal = true">
                    {{ $i18n.get('label_crate_new_item') + ' "' + searchQuery + '"' }}
                </a>
            </template>
        </b-taginput>
        <a
                v-if="currentUserCanEditItems"
                :disabled="$route.query.iframemode"
                @click="createNewItemModal = !createNewItemModal"
                class="add-link">
            <span class="icon is-small">
                <i class="tainacan-icon has-text-secondary tainacan-icon-add"/>
            </span>
            &nbsp;{{ $i18n.get('label_crate_new_item') }}
        </a>
        <b-modal 
                :width="1200"
                :active.sync="createNewItemModal">
            <iframe 
                    :id="newItemFrame"
                    width="100%"
                    style="height: 85vh"
                    :src="adminFullURL + $routerHelper.getNewItemPath(collectionId) + '?iframemode=true&newmetadatumid=' + itemMetadatum.metadatum.metadata_type_options.search + '&newitemtitle=' + searchQuery" />
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
            allowNew: true
        },
        data() {
            return {
                selected:[],
                options: [],
                isLoading: false,
                collectionId: '',
                inputValue: null,
                queryObject: {},
                itemsFound: [],
                searchQuery: '',
                totalItems: 0,
                page: 1,
                createNewItemModal: false,
                adminFullURL: tainacan_plugin.admin_url + 'admin.php?page=tainacan_admin#',
                currentUserCanEditItems: false
            }
        },
        computed: {
            collection() {
                return this.getCollection();
            }
        },
        watch: {
            createNewItemModal() {
                if (this.createNewItemModal)
                    window.addEventListener('message', this.createNewItemFromModal, false);
                else
                    window.removeEventListener('message', this.createNewItemFromModal);
            }
        },
        created() {
            this.collectionId = ( this.itemMetadatum && this.itemMetadatum.metadatum.metadata_type_options && this.itemMetadatum.metadatum.metadata_type_options.collection_id ) ? this.itemMetadatum.metadatum.metadata_type_options.collection_id : '';
            
            if (this.itemMetadatum.value && (Array.isArray( this.itemMetadatum.value ) ? this.itemMetadatum.value.length > 0 : true )) {
                let query = qs.stringify({ postin: ( Array.isArray( this.itemMetadatum.value ) ) ? this.itemMetadatum.value : [ this.itemMetadatum.value ]  });
                query += this.itemMetadatum.metadatum.metadata_type_options.search ? '&fetch_only_meta=' + this.itemMetadatum.metadatum.metadata_type_options.search : '';
                axios.get('/collection/' + this.collectionId + '/items?' + query + '&nopaging=1&fetch_only=title,thumbnail')
                    .then( res => {
                        if (res.data.items) {
                            for (let item of res.data.items)
                                this.selected.push({
                                    label: this.getItemLabel(item),
                                    value: item.id,
                                    img: item.thumbnail && item.thumbnail['tainacan-small'] && item.thumbnail['tainacan-small'][0] ? item.thumbnail['tainacan-small'][0] : ''
                                });
                        }
                    })
                    .catch(error => {
                        this.$console.log(error);
                    });
            }

            // Checks if current user can edit itens on the related collection to offer modal
            if (this.collection.id == this.collectionId)
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
                                for (let item of res.data.items)
                                    this.options.push({
                                        label: this.getItemLabel(item),
                                        value: item.id,
                                        img: item.thumbnail && item.thumbnail['tainacan-small'] && item.thumbnail['tainacan-small'][0] ? item.thumbnail['tainacan-small'][0] : ''
                                    })
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
                    }
                    
                } else {
                    query['search'] = search;
                }
                query['fetch_only'] = 'title,thumbnail';
                query['fetch_only_meta'] = this.itemMetadatum.metadatum.metadata_type_options.search;
                query['perpage'] = 12;
                query['paged'] = this.page;

                if (this.selected.length > 0)
                    query['exclude'] = this.selected.map((item) => item.value);

                return qs.stringify(query);
            },
            createNewItemFromModal(event) {
                const message = event.message ? 'message' : 'data';
                const data = event[message];

                if (data.type == 'itemCreationMessage') {
                    this.createNewItemModal = false;

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
        }
    }
</script>

<style scoped>
    div.is-flex {
        justify-content: flex-start;
    }
    .add-link {
        font-size: 0.75em;
    }
</style>
