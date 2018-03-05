<template>
    <div class="block">
        <b-autocomplete
                rounded
                icon="magnify"
                :id="id"
                v-model="selected"
                :data="options"
                @input="search"
                :loading="loading"
                field="label"
                @select="option => setResults(option) ">
        </b-autocomplete>
    </div>
</template>

<script>
    import axios from '../../../js/axios/axios'

    export default {
        created(){
            this.collection = ( this.collection_id ) ? this.collection_id : this.filter.collection_id;
            this.field = ( this.field_id ) ? this.field_id : this.filter.collection_id;
            this.type = ( this.filter_type ) ? this.filter_type : this.filter.field.field_type;
        },
        data(){
            return {
                results:'',
                selected:'',
                options: [],
                isLoading: false,
                type: '',
                collection: '',
                field: '',
                selected: '',
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
        methods: {
            setResults(option){
                if(!option)
                    return;
                this.results = option.value;
                this.onSelect()
            },
            onSelect(){
                let filter = null;
                if ( this.type ) {
                    filter = 'term';
                } else {
                    filter = 'selectbox';
                }

                this.$emit('input', {
                    filter: filter,
                    field_id: ( this.field_id ) ? this.field_id : this.filter.field,
                    collection_id: ( this.collection_id ) ? this.collection_id : this.filter.collection_id,
                    value: this.results
                });
            },
            search( query ){
                let promise = null;
                this.options = [];

                if ( this.type === 'Tainacan\Field_types\Relationship' ) {

                    let collectionTarget = ( this.filter && this.filter.field.field_type_options.collection_id ) ?
                        this.filter.field.field_type_options.collection_id : this.collection_id;
                    promise = this.getValuesRelationship( collectionTarget, query );

                } else if ( this.type === 'Tainacan\Field_types\Category' ) {

                    let collectionTarget = ( this.filter && this.filter.field.field_type_options.taxonomy ) ?
                        this.filter.field.field_type_options.taxonomy : this.taxonomy;
                    promise = this.getValuesCategory( collectionTarget, query );

                } else {
                    promise = this.getValuesPlainText( this.field, query );
                }

                promise.then( data => {
                    this.isLoading = false;
                })
                .catch( error => {
                    console.log('error select', error );
                    this.isLoading = false;
                });
            },
            getValuesPlainText( field_id ){
                return axios.get( '/collection/' + this.collection_id  + '/fields/' + field_id + '?fetch=all_field_values')
                    .then( res => {
                        for (let metadata of res.data) {
                            let index = this.options.findIndex(itemMetadata => itemMetadata.value === metadata.mvalue);
                            if( index < 0 ){
                                this.options.push({ label: metadata.mvalue, value: metadata.mvalue })
                            }

                        }
                    })
                    .catch(error => {
                        console.log(error);
                    });
            },
            getValuesCategory( taxonomy ){
                // TODO: get taxonomy terms
            },
            getValuesRelationship( collectionTarget, search ){
                return axios.get( '/collection/' + collectionTarget  + '/items?s=' + search )
                    .then( res => {
                        for (let item of res.data) {
                            this.options.push({ label: item.title, value: item.id })
                        }
                    })
                    .catch(error => {
                        console.log(error);
                    });
            },
        }
    }
</script>