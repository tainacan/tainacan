<template>
    <div class="block">
        <b-autocomplete
                icon="magnify"
                size="is-small"
                :id="id"
                v-model="selected"
                :data="options"
                expanded
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
                                width="24"
                                :src="`${props.option.img}`">
                    </div>
                    <div class="media-content">
                        {{ props.option.label }}
                    </div>
                </div>
            </template>
        </b-autocomplete>
    </div>
</template>

<script>
    import { tainacan as axios } from '../../../js/axios/axios'
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

            
            this.$eventBusSearch.$on('removeFromFilterTag', (filterTag) => {
                if (filterTag.filterId == this.filter.id)
                    this.cleanSearch();
            })
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
            search( query ){
                if (query != '') {
                    let promise = null;
                    this.options = [];
                    if ( this.type === 'Tainacan\\Metadata_Types\\Relationship' ) {
                        let collectionTarget = ( this.metadatum_object && this.metadatum_object.metadata_type_options.collection_id ) ?
                            this.metadatum_object.metadata_type_options.collection_id : this.collection_id;
                        promise = this.getValuesRelationship( collectionTarget, query );

                    } else {
                        promise = this.getValuesPlainText( this.metadatum, query, this.isRepositoryLevel );
                    }

                    promise.catch( error => {
                        this.$console.log('error select', error );
                    });
                } else {
                    this.cleanSearch();
                }
            },
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