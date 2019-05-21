<template>
    <div class="block">
        <b-autocomplete
                icon="magnify"
                size="is-small"
                :aria-labelledby="labelId"
                v-model="selected"
                :data="options"
                expanded
                :loading="isLoadingOptions"
                @input="search"
                field="label"
                @select="option => setResults(option) "
                :placeholder="(type == 'Tainacan\\Metadata_Types\\Relationship') ? $i18n.get('info_type_to_search_items') : $i18n.get('info_type_to_search_metadata')">
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
    import { filter_type_mixin } from '../filter-types-mixin'
    // import qs from 'qs';

    export default {
        created(){
            this.collection = ( this.collection_id ) ? this.collection_id : this.filter.collection_id;
            this.metadatum = ( this.metadatum_id ) ? this.metadatum_id : this.filter.metadatum.metadatum_id;
            const vm = this;

            let in_route = '/collection/' + this.collection + '/metadata/' +  this.metadatum;

            if(this.isRepositoryLevel || this.collection == 'filter_in_repository'){
                in_route = '/metadata/'+ this.metadatum;
            }

            axios.get(in_route)
                .then( res => {
                    let result = res.data;
                    if( result && result.metadata_type ){
                        vm.metadatum_object = result;
                        vm.type = result.metadata_type;
                        vm.selectedValues();
                    }
                })
                .catch(error => {
                    this.$console.log(error);
                });

            this.$eventBusSearch.$on('removeFromFilterTag', this.cleanSearchFromTags);
        },
        data(){
            return {
                results:'',
                selected:'',
                options: [],
                type: '',
                collection: '',
                metadatum: '',
                metadatum_object: {},
                label: ''
            }
        },
        props: {
            isRepositoryLevel: Boolean,
            labelId: String
        },
        mixins: [filter_type_mixin],
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
                    metadatum_id: this.metadatum,
                    collection_id: this.collection,
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

                    if ( this.type === 'Tainacan\\Metadata_Types\\Relationship' )
                        promise = this.getValuesRelationship( query, this.isRepositoryLevel );
                    else
                        promise = this.getValuesPlainText( this.metadatum, query, this.isRepositoryLevel );
                    
                    promise.request.catch( error => {
                        if (isCancel(error))
                            this.$console.log('Request canceled: ', error.message);
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
                const instance = this;
                if ( !this.query || !this.query.metaquery || !Array.isArray( this.query.metaquery ) )
                    return false;

                let index = this.query.metaquery.findIndex(newMetadatum => newMetadatum.key === this.metadatum );
                if ( index >= 0){
                    let metadata = this.query.metaquery[ index ];
                    // let collectionTarget = ( this.metadatum_object && this.metadatum_object.metadata_type_options.collection_id ) ?
                        // this.metadatum_object.metadata_type_options.collection_id : this.collection_id;

                    if ( this.type === 'Tainacan\\Metadata_Types\\Relationship' ) {
                        // let query = qs.stringify({ postin: metadata.value  });
                        
                        axios.get('/items/' + metadata.value)
                            .then( res => {
      
                                let item = res.data;
                                instance.results = item.title;
                                instance.label = item.title;
                                instance.selected = item.title;
         
                                this.$eventBusSearch.$emit( 'sendValuesToTags', {
                                    filterId: instance.filter.id,
                                    value: instance.label
                                });
                            })
                            .catch(error => {
                                this.$console.log(error);
                            });
                    } else {
                        instance.results = metadata.value;
                        instance.label = metadata.value;
                        instance.selected = metadata.value;

                        this.$eventBusSearch.$emit( 'sendValuesToTags', {
                            filterId: instance.filter.id,
                            value: metadata.value
                        });
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
                    metadatum_id: this.metadatum,
                    collection_id: ( this.collection_id ) ? this.collection_id : this.filter.collection_id,
                    value: ''
                });
            },
        },
        beforeDestroy() {
            this.$eventBusSearch.$off('removeFromFilterTag', this.cleanSearchFromTags);
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