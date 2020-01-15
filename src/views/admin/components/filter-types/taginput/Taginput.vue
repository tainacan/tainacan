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
                @input="onSelect"
                @typing="search"
                :aria-close-label="$i18n.get('remove_value')"
                :aria-labelledby="'filter-label-id-' + filter.id"
                :placeholder="(metadatumType == 'Tainacan\\Metadata_Types\\Relationship') ? $i18n.get('info_type_to_add_items') : $i18n.get('info_type_to_add_metadata')">
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
    import { tainacan as axios, isCancel } from '../../../js/axios';
    import { filterTypeMixin, dynamicFilterTypeMixin } from '../../../js/filter-types-mixin';
    import qs from 'qs';

    export default {
        mixins: [filterTypeMixin, dynamicFilterTypeMixin],
        data(){
            return {
                results:'',
                selected:[],
                options: [],
                relatedCollectionId: ''
            }
        },
        watch: {
            'query.metaquery'() {
                this.updateSelectedValues();
            },
            'query.taxquery'() {
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
                let promise = null;
                this.options = [];
                let valuesToIgnore = [];

                for(let val of this.selected)
                    valuesToIgnore.push( val.value );

                // Cancels previous Request
                if (this.getOptionsValuesCancel != undefined)
                    this.getOptionsValuesCancel.cancel('Facet search Canceled.');

                if ( this.metadatumType === 'Tainacan\\Metadata_Types\\Relationship' )
                    promise = this.getValuesRelationship( query, this.isRepositoryLevel, valuesToIgnore );
                else
                    promise = this.getValuesPlainText( this.metadatumId, query, this.isRepositoryLevel, valuesToIgnore );

                promise.request
                    .catch( error => {
                        if (isCancel(error))
                            this.$console.log('Request canceled: ' + error.message);
                        else
                            this.$console.error( error );
                    });

                // Search Request Token for cancelling
                this.getOptionsValuesCancel = promise.source;
                
            }, 500),
            updateSelectedValues() {

                if ( !this.query || !this.query.metaquery || !Array.isArray( this.query.metaquery ) )
                    return false;

                let index = this.query.metaquery.findIndex(newMetadatum => newMetadatum.key == this.metadatumId );
                if (index >= 0) {
                    let metadata = this.query.metaquery[ index ];

                    if ( this.metadatumType === 'Tainacan\\Metadata_Types\\Relationship' ) {
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