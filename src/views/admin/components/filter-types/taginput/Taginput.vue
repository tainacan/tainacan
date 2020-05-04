<template>
    <div class="block">
        <b-taginput
                icon="magnify"
                size="is-small"
                v-model="selected"
                :data="options"
                autocomplete
                expanded
                :loading="isLoadingOptions"
                :remove-on-keys="[]"
                field="label"
                attached
                @input="($event) => { resetPage(); onSelect($event) }"
                @typing="search"
                :aria-close-label="$i18n.get('remove_value')"
                :aria-labelledby="'filter-label-id-' + filter.id"
                :placeholder="getInputPlaceholder"
                check-infinite-scroll
                @infinite-scroll="searchMore">
            <template slot-scope="props">
                <div class="media">
                    <div
                            class="media-left"
                            v-if="props.option.img">
                        <img
                                :alt="$i18n.get('label_thumbnail')"
                                width="24"
                                :src="`${props.option.img}`">
                    </div>
                    <div class="media-content">
                        <span class="ellipsed-text">{{ props.option.label }}</span>
                        <span 
                                v-if="props.option.total_items != undefined"
                                class="has-text-gray">{{ "(" + props.option.total_items + ")" }}</span>
                    </div>
                </div>
            </template>
            <template 
                    v-if="!isLoadingOptions" 
                    slot="empty">
                {{ $i18n.get('info_no_options_found'	) }}
            </template>
        </b-taginput>
    </div>
</template>

<script>
    import { tainacan as axios, isCancel, wp as wpAxios } from '../../../js/axios';
    import { filterTypeMixin, dynamicFilterTypeMixin } from '../../../js/filter-types-mixin';
    import qs from 'qs';

    export default {
        mixins: [filterTypeMixin, dynamicFilterTypeMixin],
        data() {
            return {
                results:'',
                selected:[],
                options: [],
                relatedCollectionId: '',
                searchQuery: '',
                searchOffset: 0,
                searchNumber: 12,
                totalFacets: 0
            }
        },
        computed: {
            getInputPlaceholder() {
                if (this.metadatumType == 'Tainacan\\Metadata_Types\\Relationship') 
                    return this.$i18n.get('info_type_to_add_items');
                else if (this.metadatumType == 'Tainacan\\Metadata_Types\\User')
                    return this.$i18n.get('info_type_to_add_users');  
                else 
                    return this.$i18n.get('info_type_to_add_metadata');
            }
        },
        watch: {
            'query'() {
                this.updateSelectedValues();
            }
        },
        created() {
            if (this.metadatumType === 'Tainacan\\Metadata_Types\\Relationship' && 
                this.filter.metadatum && 
                this.filter.metadatum.metadata_type_object && 
                this.filter.metadatum.metadata_type_object.options &&
                this.filter.metadatum.metadata_type_object.options.collection_id) {
                    this.relatedCollectionId = this.filter.metadatum.metadata_type_object.options.collection_id;
                }
        },
        mounted() {
            this.updateSelectedValues();
        },
        methods: {
            search: _.debounce( function(query) {

                // String update
                if (query != this.searchQuery) {
                    this.searchQuery = query;
                    this.options = [];
                    this.searchOffset = 0;
                } 
                
                // String cleared
                if (!query.length) {
                    this.searchQuery = query;
                    this.options = [];
                    this.searchOffset = 0;
                }

                // No need to load more
                if (this.searchOffset > 0 && this.options.length >= this.totalFacets)
                    return;

                let promise = null;
                let valuesToIgnore = [];

                for (let val of this.selected)
                    valuesToIgnore.push( val.value );

                // Cancels previous Request
                if (this.getOptionsValuesCancel != undefined)
                    this.getOptionsValuesCancel.cancel('Facet search Canceled.');

                if ( this.metadatumType === 'Tainacan\\Metadata_Types\\Relationship' || this.metadatumType === 'Tainacan\\Metadata_Types\\User' )
                    promise = this.getValuesRelationship( this.searchQuery, this.isRepositoryLevel, valuesToIgnore, this.searchOffset, this.searchNumber );
                else
                    promise = this.getValuesPlainText( this.metadatumId, this.searchQuery, this.isRepositoryLevel, valuesToIgnore, this.searchOffset, this.searchNumber );

                promise.request
                    .then( res => {
                        this.totalFacets = res.headers['x-wp-total'];
                        this.searchOffset += this.searchNumber;
                    })
                    .catch( error => {
                        if (isCancel(error))
                            this.$console.log('Request canceled: ' + error.message);
                        else
                            this.$console.error( error );
                    });

                // Search Request Token for cancelling
                this.getOptionsValuesCancel = promise.source;
                
            }, 500),
            searchMore: _.debounce(function () {
                this.shouldAddOptions = true;
                this.search(this.searchQuery);
            }, 250),
            updateSelectedValues() {

                if ( !this.query || !this.query.metaquery || !Array.isArray( this.query.metaquery ) )
                    return false;

                let index = this.query.metaquery.findIndex(newMetadatum => newMetadatum.key == this.metadatumId );
                if (index >= 0) {
                    let metadata = this.query.metaquery[ index ];

                    if (this.metadatumType === 'Tainacan\\Metadata_Types\\Relationship') {
                        let query = qs.stringify({ postin: metadata.value, fetch_only: 'title,thumbnail', fetch_only_meta: '' });
                        let endpoint = '/items/';

                        if (this.relatedCollectionId != '')
                            endpoint = '/collection/' + this.relatedCollectionId + endpoint; 

                        axios.get(endpoint + '?' + query)
                            .then( res => {
                                if (res.data.items) {
                                    this.selected = [];
                                    for (let item of res.data.items) {
                                        let existingItem = this.selected.findIndex((anItem) => item.id == anItem.id);
                                        if (existingItem < 0) {
                                            this.selected.push({ 
                                                label: item.title, 
                                                value: item.id, 
                                                img: item.thumbnail && item.thumbnail.thumbnail && item.thumbnail.thumbnail[0] ? item.thumbnail.thumbnail[0] : null 
                                            });
                                        }
                                    }
                                    this.$emit( 'sendValuesToTags', { 
                                        label: this.selected.map((option) => option.label), 
                                        value: this.selected.map((option) => option.value)
                                    });
                                }
                            })
                            .catch(error => {
                                this.$console.log(error);
                            });
                    } else if (this.metadatumType === 'Tainacan\\Metadata_Types\\User') {
                        let query = qs.stringify({ include: metadata.value });
                        let endpoint = '/users/';

                        wpAxios.get(endpoint + '?' + query)
                            .then( res => {
                                if (res.data) {
                                    this.selected = [];
                                    for (let user of res.data) {
                                        let existingUser = this.selected.findIndex((anUser) => user.id == anUser.id);
                                        if (existingUser < 0) {
                                            this.selected.push({ 
                                                label: user.name, 
                                                value: user.id, 
                                                img: user.avatar_urls && user.avatar_urls['24'] ? user.avatar_urls['24'] : null 
                                            });
                                        }
                                    }
                                    this.$emit( 'sendValuesToTags', { 
                                        label: this.selected.map((option) => option.label), 
                                        value: this.selected.map((option) => option.value)
                                    });
                                }
                            })
                            .catch(error => {
                                this.$console.log(error);
                            });
                    } else {
                        this.selected = [];
                        for (let item of metadata.value)
                            this.selected.push({ label: item, value: item, img: null });
                        
                        this.$emit( 'sendValuesToTags', { 
                            label: this.selected.map((option) => option.label), 
                            value: this.selected.map((option) => option.value)
                        });
                    }
                } else {
                    this.selected = [];
                }
            },
            onSelect() {
                this.$emit('input', {
                    filter: 'taginput',
                    compare: 'IN',
                    metadatum_id: this.metadatumId,
                    collection_id: this.collectionId,
                    value: this.selected.map((option) => option.value)
                });
            }
        }
    }
</script>