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
            this.field = ( this.field_id ) ? this.field_id : this.filter.field.field_id ;
            this.type = ( this.filter_type ) ? this.filter_type : this.filter.field.field_type;

            axios.get('/collection/'+ this.collection +'/fields/' + this.field + '?context=edit')
                .then( res => {
                    let field = res.data;
                    this.selectedValues( field.field_type_options.taxonomy_id );
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
                field: '',
                taxonomy: ''
            }
        },
        props: {
            filter: {
                type: Object // concentrate all attributes field id and type
            },
            field_id: [Number], // not required, but overrides the filter field id if is set
            collection_id: [Number], // not required, but overrides the filter field id if is set
            filter_type: [String],  // not required, but overrides the filter field type if is set
            id: '',
            query: {
                type: Object // concentrate all attributes field id and type
            }
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
                    field_id: ( this.field_id ) ? this.field_id : this.filter.field,
                    collection_id: ( this.collection_id ) ? this.collection_id : this.filter.collection_id,
                    terms: values
                });
            }
        },
        methods: {
            search( query ){
                let promise = null;
                this.options = [];
                const q = query;
                
                axios.get('/collection/'+ this.collection +'/fields/' + this.field + '?context=edit')
                    .then( res => {
                        let field = res.data;
                        promise = this.getValuesCategory( field.field_type_options.taxonomy_id, q );
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
            getValuesCategory( taxonomy, query ){
                return axios.get('/taxonomy/' + taxonomy + '/terms?hideempty=0' ).then( res => {
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

                let index = this.query.taxquery.findIndex(newField => newField.taxonomy === this.taxonomy );
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
              return axios.get('/taxonomy/' + taxonomy + '/terms/' + id ).then( res => {
                  this.$console.log(res);
              })
              .catch(error => {
                  this.$console.log(error);
              });
            }
        }
    }
</script>
