<template>
    <div class="block">
        <b-taginput
                size="is-small"
                v-model="selected"
                :data="options"
                :loading="isLoading"
                autocomplete
                field="label"
                attached
                :class="{'has-selected': selected != undefined && selected != []}"
                @typing="search" />
    </div>
</template>

<script>
    import { tainacan as axios } from '../../../js/axios/axios'

    export default {
        created(){
            this.collection = ( this.collection_id ) ? this.collection_id : this.filter.collection_id;
            this.metadatum = ( this.metadatum_id ) ? this.metadatum_id : this.filter.metadatum.metadatum_id ;
            this.type = ( this.filter_type ) ? this.filter_type : this.filter.metadatum.metadata_type;

            let in_route = '/collection/' + this.collection + '/metadata/' +  this.metadatum;

            if(this.isRepositoryLevel || this.collection == 'filter_in_repository'){
                in_route = '/metadata/'+ this.metadatum;
            }

            axios.get(in_route)
                .then( res => {
                    let metadatum = res.data;
                    this.selectedValues( metadatum.metadata_type_options.taxonomy_id );
                });
            
            this.$eventBusSearch.$on('removeFromFilterTag', (filterTag) => {
               
                if (filterTag.filterId == this.filter.id) {

                    // let selectedIndex = this.selected.findIndex(option => option.value == filterTag.singleValue);
                    // if (selectedIndex >= 0) {

                        let values = [];
                        let labels = [];
                        if( this.selected.length > 0 ){
                            for(let val of this.selected){
                                if (val.label != filterTag.singleValue) {
                                    values.push( val.value );
                                    labels.push( val.label );
                                }
                            }
                        }
                        this.$emit('input', {
                            filter: 'taginput',
                            compare: 'IN',
                            taxonomy: this.taxonomy,
                            metadatum_id: ( this.metadatum_id ) ? this.metadatum_id : this.filter.metadatum,
                            collection_id: ( this.collection_id ) ? this.collection_id : this.filter.collection_id,
                            terms: values
                        });
                        this.$eventBusSearch.$emit( 'sendValuesToTags', {
                            filterId: this.filter.id,
                            value: labels
                        });
                        console.log(this.taxonomy)
                        this.selectedValues();
                   // }
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
                taxonomy: ''
            }
        },
        props: {
            filter: {
                type: Object // concentrate all attributes metadatum id and type
            },
            metadatum_id: [Number], // not required, but overrides the filter metadatum id if is set
            collection_id: [Number], // not required, but overrides the filter metadatum id if is set
            filter_type: [String],  // not required, but overrides the filter metadatum type if is set
            id: '',
            query: {
                type: Object // concentrate all attributes metadatum id and type
            },
            isRepositoryLevel: Boolean,
        },
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
                    taxonomy: this.taxonomy,
                    metadatum_id: ( this.metadatum_id ) ? this.metadatum_id : this.filter.metadatum,
                    collection_id: ( this.collection_id ) ? this.collection_id : this.filter.collection_id,
                    terms: values
                });

                let onlyLabels = this.selected.map((selected => selected.label))
                this.$eventBusSearch.$emit("sendValuesToTags", {
                    filterId: this.filter.id,
                    value: onlyLabels
                });
            }
        },
        methods: {
            search( query ){
                let promise = null;
                this.options = [];
                const q = query;
                const endpoint = this.isRepositoryLevel ? '/metadata/' + this.metadatum : '/collection/'+ this.collection +'/metadata/' + this.metadatum;

                axios.get(endpoint)
                    .then( res => {
                        let metadatum = res.data;
                        promise = this.getValuesTaxonomy( metadatum.metadata_type_options.taxonomy_id, q );
                        this.isLoading = true;
                        promise.then( () => {
                            this.isLoading = false;
                        })
                            .catch( error => {
                                this.$console.log('error select', error );
                                this.isLoading = false;
                            });
                    })
                    .catch(error => {
                        this.$console.log(error);
                    });
            },
            getValuesTaxonomy( taxonomy, query ){
                return axios.get('/taxonomy/' + taxonomy + '/terms?hideempty=0&order=asc' ).then( res => {
                    for (let term of res.data) {
                        if( term.name.toLowerCase().indexOf( query.toLowerCase() ) >= 0 ){
                            this.taxonomy = term.taxonomy;
                            this.options.push({label: term.name, value: term.id});
                        }
                    }
                })
                .catch(error => {
                    this.$console.log(error);
                });
            },
            selectedValues( taxonomy ){
                if ( !this.query || !this.query.taxquery || !Array.isArray( this.query.taxquery ) )
                    return false;

                let index = this.query.taxquery.findIndex(newMetadatum => newMetadatum.taxonomy === this.taxonomy );
                if ( index >= 0){
                    let metadata = this.query.taxquery[ index ];
                    for ( let id of metadata.terms ){
                       this.getTerm( taxonomy, id );
                    }
                } else {
                    return false;
                }
            },
            getTerm( taxonomy, id ){
              return axios.get('/taxonomy/' + taxonomy + '/terms/' + id + '?order=asc&hideempty=0' ).then( res => {
                  this.$console.log(res);
              })
              .catch(error => {
                  this.$console.log(error);
              });
            }
        }
    }
</script>
