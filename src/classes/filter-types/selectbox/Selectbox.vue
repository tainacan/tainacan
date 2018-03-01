<template>
    <div class="block">
        <b-field>
            <b-select
                    :id = "id"
                    :laoding = "isLoading"
                    v-model = "selected"
                    @input = "onSelect()"
                    expanded>
                <option
                        v-for="option,index in options"
                        :key="index"
                        :label="option.label"
                        :value="option.value"
                        border>{{ option.label }}</option>
            </b-select>
        </b-field>
    </div>
</template>

<script>
    import axios from '../../../js/axios/axios'

    export default {
        created(){
            this.collection = ( this.collection_id ) ? this.collection_id : this.filter.collection_id;
            this.loadOptions();
        },
        data(){
            return {
                isLoading: false,
                options: [],
                type: '',
                collection: '',
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
            getValuesPlainText( field_id ){
                // TODO: get values from items
            },
            getValuesCategory( taxonomy ){
                // TODO: get taxonomy terms
            },
            getValuesRelationship( collectionTarget ){
                 return axios.get( '/collection/' + collectionTarget  + '/items' )
                    .then( res => {

                        for (let item of res.data) {
                            this.options.push({ label: item.title, value: item.id })
                        }

                    })
                    .catch(error => {
                        console.log(error);
                    });
            },
            loadOptions(){
                let promise = null;
                this.isLoading = true;

                if ( this.type === 'Tainacan\Field_types\Relationship' ) {

                    let collectionTarget = ( this.filter && this.filter.field.field_type_options.collection_id ) ?
                        this.filter.field.field_type_options.collection_id : this.collection_id;
                    promise = this.getValuesRelationship( collectionTarget );

                } else if ( this.type === 'Tainacan\Field_types\Category' ) {

                    let collectionTarget = ( this.filter && this.filter.field.field_type_options.taxonomy ) ?
                        this.filter.field.field_type_options.taxonomy : this.taxonomy;
                    promise = this.getValuesCategory( collectionTarget );

                } else {
                    promise = this.getValuesPlainText( this.filter.field.id );
                }

                promise.then( data => {
                    this.isLoading = false;
                })
                .catch( error => {
                    console.log('error select', error );
                    this.isLoading = false;
                });
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
                    value: this.selected
                });
            }
        }
    }
</script>