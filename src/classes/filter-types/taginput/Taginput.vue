<template>
    <div class="block">
        <b-taginput
                size="is-small"
                v-model="selected"
                :data="options"
                autocomplete
                :loading="isLoading"
                field="label"
                attached
                @typing="search"/>
    </div>
</template>

<script>
    import { tainacan as axios } from '../../../js/axios/axios';
    import { filter_type_mixin } from '../filter-types-mixin'
    import qs from 'qs';

    export default {
        created(){
            this.collection = ( this.collection_id ) ? this.collection_id : this.filter.collection_id;
            this.metadatum = ( this.metadatum_id ) ? this.metadatum_id : this.filter.metadatum.metadatum_id;
            const vm = this;

            let in_route = '/collection/' + this.collection + '/metadata/' +  this.metadatum;

            if(this.isRepositoryLevel || this.collection == 'filter_in_repository'){
                in_route = '/metadata/'+ this.metadatum + '?nopaging=1';
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
               
                if (filterTag.filterId == this.filter.id) {

                    let values = [];
                    if( this.selected.length > 0 ){
                        for(let val of this.selected){
                            if (val.value != filterTag.singleValue)
                                values.push( val.value );
                        }
                    }
                    
                    this.$emit('input', {
                        filter: 'taginput',
                        compare: 'IN',
                        metadatum_id: this.metadatum,
                        collection_id: ( this.collection_id ) ? this.collection_id : this.filter.collection_id,
                        value: values
                    });

                    this.$eventBusSearch.$emit( 'sendValuesToTags', {
                        filterId: this.filter.id,
                        value: values
                    });
                }
            });
        },
        data(){
            return {
                results:'',
                selected:[],
                options: [],
                isLoading: false,
                type: '',
                collection: '',
                metadatum: '',
                metadatum_object: {}
            }
        },
        props: {
            isRepositoryLevel: Boolean
        },
        mixins: [filter_type_mixin],
        watch: {
            selected( value ){
                this.selected = value;
                let values = [];
                if( this.selected.length > 0 ){
                    for(let val of this.selected){
                        values.push( val.value );
                    }
                }
                this.$emit('input', {
                    filter: 'taginput',
                    compare: 'IN',
                    metadatum_id: this.metadatum,
                    collection_id: ( this.collection_id ) ? this.collection_id : this.filter.collection_id,
                    value: values
                });
            }
        },
        methods: {
            search( query ){
                let promise = null;
                this.options = [];
                if ( this.type === 'Tainacan\\Metadata_Types\\Relationship' ) {
                    let collectionTarget = ( this.metadatum_object && this.metadatum_object.metadata_type_options.collection_id ) ?
                        this.metadatum_object.metadata_type_options.collection_id : this.collection_id;
                    promise = this.getValuesRelationship( collectionTarget, query );

                } else {
                    promise = this.getValuesPlainText( this.metadatum, query, this.isRepositoryLevel );
                }
                this.isLoading = true;
                promise.then(() => {
                    this.isLoading = false;
                }).catch( error => {
                    this.$console.log('error select', error );
                    this.isLoading = false;
                });
            },
            selectedValues(){
                const instance = this;
                if ( !this.query || !this.query.metaquery || !Array.isArray( this.query.metaquery ) )
                    return false;

                let index = this.query.metaquery.findIndex(newMetadatum => newMetadatum.key === this.metadatum );
                if ( index >= 0){
                    let metadata = this.query.metaquery[ index ];
                    let collectionTarget = ( this.metadatum_object && this.metadatum_object.metadata_type_options.collection_id ) ?
                        this.metadatum_object.metadata_type_options.collection_id : this.collection_id;


                    if ( this.type === 'Tainacan\\Metadata_Types\\Relationship' ) {
                        let query = qs.stringify({ postin: metadata.value  });

                        axios.get('/collection/' + collectionTarget + '/items?' + query)
                            .then( res => {
                                for (let item of res.data) {
                                    instance.selected.push({ label: item.title, value: item.id, img: '' });
                                }

                                let onlyLabels = instance.selected.map((selected => selected.label))
                                this.$eventBusSearch.$emit( 'sendValuesToTags', {
                                    filterId: instance.filter.id,
                                    value: onlyLabels
                                });
                            })
                            .catch(error => {
                                this.$console.log(error);
                            });
                    } else {
                        for (let item of metadata.value) {
                            instance.selected.push({ label: item, value: item, img: '' });

                            let onlyValues = instance.selected.map((selected => selected.label))
                            this.$eventBusSearch.$emit( 'sendValuesToTags', {
                                filterId: instance.filter.id,
                                value: onlyValues
                            });
                        }
                    }
                } else {
                    return false;
                }
            }
        }
    }
</script>