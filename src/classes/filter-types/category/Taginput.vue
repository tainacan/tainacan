<template>
    <div class="block">
        <b-taginput
                rounded
                icon="magnify"
                v-model="selected"
                :data="options"
                autocomplete
                :loading="loading"
                field="label"
                @typing="search">
        </b-taginput>
    </div>
</template>

<script>
    import { tainacan as axios } from '../../../js/axios/axios'

    export default {
        created(){
            this.collection = ( this.collection_id ) ? this.collection_id : this.filter.collection_id;
            this.field = ( this.field_id ) ? this.field_id : this.filter.collection_id;
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
                promise = this.getValuesCategory( this.field, query );

                promise.then( data => {
                    this.isLoading = false;
                })
                    .catch( error => {
                        console.log('error select', error );
                        this.isLoading = false;
                    });
            },
            getValuesCategory( taxonomy ){
                // TODO: get taxonomy terms
            },
        }
    }
</script>