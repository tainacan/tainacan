<template>
    <div class="block">
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
    </div>
</template>

<script>
    import { tainacan as axios } from '../../../js/axios/axios'

    export default {
        created(){
            this.collection = ( this.collection_id ) ? this.collection_id : this.filter.collection_id;
            this.field = ( this.field_id ) ? this.field_id : this.filter.collection_id;
            this.loadOptions();
        },
        data(){
            return {
                isLoading: false,
                options: [],
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
            getValuesCategory( taxonomy ){
                // TODO: get taxonomy terms
            },
            loadOptions(){
                let promise = null;
                this.isLoading = true;
                let collectionTarget = ( this.filter && this.filter.field.field_type_options.taxonomy_id ) ?
                    this.filter.field.field_type_options.taxonomy_id : this.taxonomy_id;
                promise = this.getValuesCategory( collectionTarget );

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
                    filter: 'term',
                    field_id: ( this.field_id ) ? this.field_id : this.filter.field,
                    collection_id: ( this.collection_id ) ? this.collection_id : this.filter.collection_id,
                    value: this.selected
                });
            }
        }
    }
</script>