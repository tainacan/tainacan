<template>
    <div class="block">
        <b-taginput
                size="is-small"
                rounded
                icon="magnify"
                v-model="selected"
                :data="options"
                autocomplete
                field="label"
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
        },
        data(){
            return {
                results:'',
                selected:[],
                options: [],
                isLoading: false,
                type: '',
                collection: '',
                field: ''
            }
        },
        props: {
            filter: {
                type: Object // concentrate all attributes field id and type
            },
            field_id: [Number], // not required, but overrides the filter field id if is set
            collection_id: [Number], // not required, but overrides the filter field id if is set
            filter_type: [String],  // not required, but overrides the filter field type if is set
            id: ''
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
                    field_id: ( this.field_id ) ? this.field_id : this.filter.field,
                    collection_id: ( this.collection_id ) ? this.collection_id : this.filter.collection_id,
                    value: values
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
                        if( term.name.toLowerCase().indexOf( query.toLowerCase() ) >= 0 )
                            this.options.push({label: term.name, value: term.id});
                    }
                })
                .catch(error => {
                    this.$console.log(error);
                });
            }
        }
    }
</script>