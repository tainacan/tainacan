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
                @infinite-scroll="searchMore">
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
        </b-taginput>
    </div>
</template>

<script>
    import { tainacan as axios } from '../../../js/axios';
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
                page: 1
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
        },
        methods: {
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
                    query['metaquery'] = [];
                    
                    query['metaquery'][0] = {
                        key: this.itemMetadatum.metadatum.metadata_type_options.search,
                        value: search,
                        compare: 'LIKE'
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
            }
        }
    }
</script>

<style>
    .help.counter {
        display: none;
    }
    div.is-flex {
        justify-content: flex-start;
    }
</style>
