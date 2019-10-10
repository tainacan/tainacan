<template>
    <div class="block">
        <b-autocomplete
                icon="magnify"
                size="is-small"
                :aria-labelledby="'filter-label-id-' + filter.id"
                v-model="selected"
                :data="options"
                expanded
                :loading="isLoadingOptions"
                @input="search"
                field="label"
                @select="option => setResults(option) "
                :placeholder="(metadatumType === 'Tainacan\\Metadata_Types\\Relationship') ? $i18n.get('info_type_to_search_items') : $i18n.get('info_type_to_search_metadata')">
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
        </b-autocomplete>
    </div>
</template>

<script>
    import { tainacan as axios, isCancel } from '../../../js/axios/axios'
    import { filterTypeMixin, dynamicFilterTypeMixin } from '../filter-types-mixin';
    // import qs from 'qs';

    export default {
        mixins: [filterTypeMixin, dynamicFilterTypeMixin],
        data(){
            return {
                results:'',
                selected:'',
                options: [],
                label: ''
            }
        },
        mounted() {
            this.selectedValues();
        },
        methods: {
            setResults(option){
                if(!option)
                    return;
                this.results = option.value;
                this.label = option.label;
                this.onSelect()
            },
            onSelect(){
                this.$emit('input', {
                    filter: 'autocomplete',
                    metadatum_id: this.metadatumId,
                    collection_id: this.collectionId,
                    value: this.results
                });
                this.selectedValues();
            },
            search: _.debounce( function(query) {
                if (query != '') {
                    let promise = null;
                    this.options = [];

                    // Cancels previous Request
                    if (this.getOptionsValuesCancel != undefined)
                        this.getOptionsValuesCancel.cancel('Facet search Canceled.');

                    if ( this.metadatumType === 'Tainacan\\Metadata_Types\\Relationship' )
                        promise = this.getValuesRelationship( query, this.isRepositoryLevel );
                    else
                        promise = this.getValuesPlainText( this.metadatumId, query, this.isRepositoryLevel );
                    
                    promise.request.catch( error => {
                        if (isCancel(error))
                            this.$console.log('Request canceled: ' + error.message);
                        else
                            this.$console.error( error );
                    });

                    // Search Request Token for cancelling
                    this.getOptionsValuesCancel = promise.source;
                
                } else {
                    this.cleanSearch();
                }
            }, 500),
            selectedValues(){

                if (!this.query || !this.query.metaquery || !Array.isArray( this.query.metaquery ))
                    return false;

                let index = this.query.metaquery.findIndex(newMetadatum => newMetadatum.key == this.metadatumId);
                if (index >= 0) {
                    let metadata = this.query.metaquery[ index ];

                    if (this.metadatumType === 'Tainacan\\Metadata_Types\\Relationship') {
                        
                        axios.get('/items/' + metadata.value + '?fetch_only=title,thumbnail')
                            .then( res => {
                                let item = res.data;
                                this.results = item.title;
                                this.label = item.title;
                                this.selected = item.title;
         
                                this.$emit( 'sendValuesToTags', { label: this.label, value: this.results });
                            })
                            .catch(error => {
                                this.$console.log(error);
                            });
                    } else {
                        this.results = metadata.value;
                        this.label = metadata.value;
                        this.selected = metadata.value;

                        this.$emit( 'sendValuesToTags', { label: this.label, value: this.results });
                    }
                } else {
                    return false;
                }
            },
            cleanSearchFromTags(filterTag) {
                if (filterTag.filterId == this.filter.id)
                    this.cleanSearch();
            },
            cleanSearch(){
                this.results = '';
                this.label = '';
                this.selected = '';
                this.$emit('input', {
                    filter: 'autocomplete',
                    metadatum_id: this.metadatumId,
                    collection_id: this.collectionId,
                    value: ''
                });
            },
        }
    }
</script>
<style scoped>
    #profileImage {
        width: 32px;
        height: 32px;
        font-size: 2.1875rem;
        color: #fff;
        text-align: center;
        line-height: 9.375rem;
        margin: 20px 0;
    }
</style>