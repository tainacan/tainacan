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
    import { tainacan as axios } from '../../../js/axios/axios';
    import qs from 'qs';

    export default {
        created(){
            this.collection = ( this.collection_id ) ? this.collection_id : this.filter.collection_id;
            this.field = ( this.field_id ) ? this.field_id : this.filter.field;
            const vm = this;
            axios.get('/collection/' + this.collection + '/fields/' +  this.field )
                .then( res => {
                    let result = res.data;
                    if( result && result.field_type ){
                        vm.field_object = result;
                        vm.type = result.field_type;
                    }
                })
                .catch(error => {
                    console.log(error);
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
                selected: '',
                field_object: {}
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
                    compare: 'IN',
                    field_id: this.field,
                    collection_id: this.collection,
                    value: values
                });
            }
        },
        methods: {
            search( query ){
                let promise = null;
                this.options = [];
                if ( this.type === 'Tainacan\\Field_Types\\Relationship' ) {
                    let collectionTarget = ( this.field_object && this.field_object.field_type_options.collection_id ) ?
                        this.field_object.field_type_options.collection_id : this.collection_id;
                    promise = this.getValuesRelationship( collectionTarget, query );

                } else {
                    promise = this.getValuesPlainText( this.field, query );
                }

                promise.then( data => {
                    this.isLoading = false;
                }).catch( error => {
                    console.log('error select', error );
                    this.isLoading = false;
                });
            },
            getValuesPlainText( field_id ){
                return axios.get( '/collection/' + this.collection  + '/fields/' + field_id + '?fetch=all_field_values')
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
            getValuesRelationship( collectionTarget, search ){
                return axios.get( '/collection/' + collectionTarget  + '/items?search=' + search )
                    .then( res => {
                        if( res.data.length > 0 ){
                            for (let item of res.data) {
                                this.options.push({ label: item.title, value: item.id, img: '' });
                            }
                        }
                    })
                    .catch(error => {
                        console.log(error);
                    });
            }
        }
    }
</script>