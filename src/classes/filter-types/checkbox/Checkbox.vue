<template>
    <div class="block">
        <div
                v-for="option,index in options"
                :key="index"
                class="field">
            <b-checkbox
                    v-model="selected"
                    :native-value="option.value"
            >{{ option.label }}</b-checkbox>
        </div>
    </div>
</template>

<script>
    import axios from '../../../js/axios/axios'

    export default {
        created(){
            this.collection = ( this.collection_id ) ? this.collection_id : this.filter.collection_id;
            this.field = ( this.field_id ) ? this.field_id : this.filter.collection_id;
            this.type = ( this.filter_type ) ? this.filter_type : this.filter.field.field_type;
            this.loadOptions();
        },
        data(){
            return {
                isLoading: false,
                options: [],
                type: '',
                collection: '',
                field: '',
                selected: [],
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
            checked: function(val){
                this.checked = val;
                this.onSelect();
            }
        },
        methods: {
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

                } else {
                    promise = this.getValuesPlainText( this.field );
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
                this.$emit('input', {
                    filter: 'selectbox',
                    field_id: ( this.field_id ) ? this.field_id : this.filter.field,
                    collection_id: ( this.collection_id ) ? this.collection_id : this.filter.collection_id,
                    value: this.selected
                });
            }
        }
    }
</script>