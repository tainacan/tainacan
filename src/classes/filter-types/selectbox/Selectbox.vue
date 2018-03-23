<template>
    <div class="block">
        <b-select
                :id = "id"
                :laoding = "isLoading"
                v-model = "selected"
                @input = "onSelect()"
                expanded>
            <option value="">{{ $i18n.get('label_selectbox_init') }}...</option>
            <option
                    v-for="option,index in options"
                    :key="index"
                    :label="option.label"
                    :value="option.value"
                    border>{{ option.label }}</option>
        </b-select>
    </div>
</template>

<script>
    import { tainacan as axios } from '../../../js/axios/axios';
    import { filter_type_mixin } from '../filter-types-mixin'

    export default {
        created(){
            this.collection = ( this.collection_id ) ? this.collection_id : this.filter.collection_id;
            this.field = ( this.field_id ) ? this.field_id : this.filter.field.field_id;
            const vm = this;
            axios.get('/collection/' + this.collection + '/fields/' +  this.field )
                .then( res => {
                    let result = res.data;
                    if( result && result.field_type ){
                        vm.field_object = result;
                        vm.type = result.field_type;
                        vm.loadOptions();
                    }
                })
                .catch(error => {
                    console.log(error);
                });
        },
        data(){
            return {
                isLoading: false,
                options: [],
                type: '',
                collection: '',
                field: '',
                selected: '',
            }
        },
        mixins: [filter_type_mixin],
        methods: {
            loadOptions(){
                let promise = null;
                this.isLoading = true;
                let instance = this;

                if ( this.type === 'Tainacan\\Field_types\\Relationship' ) {

                    let collectionTarget = ( this.filter && this.filter.field.field_type_options.collection_id ) ?
                        this.filter.field.field_type_options.collection_id : this.collection_id;
                    promise = this.getValuesRelationship( collectionTarget );

                } else {
                    promise = this.getValuesPlainText( this.field );
                }

                promise.then( data => {
                    this.isLoading = false;
                    instance.selectedValues();
                })
                .catch( error => {
                    console.log('error select', error );
                    this.isLoading = false;
                });
            },
            onSelect(){
                this.$emit('input', {
                    filter: 'selectbox',
                    field_id: this.field,
                    collection_id: ( this.collection_id ) ? this.collection_id : this.filter.collection_id,
                    value: this.selected
                });
            },
            selectedValues(){
                if ( !this.query || !this.query.metaquery || !Array.isArray( this.query.metaquery ) )
                    return false;

                let index = this.query.metaquery.findIndex(newField => newField.key === this.field );
                if ( index >= 0){
                    let metadata = this.query.metaquery[ index ];
                    this.selected = metadata.value;
                } else {
                    return false;
                }
            }
        }
    }
</script>