<template>
    <div class="block">
        <b-taginput
                size="is-small"
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
    import { filter_type_mixin } from '../filter-types-mixin'
    import qs from 'qs';

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
                        vm.selectedValues();
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
                field_object: {}
            }
        },
        mixins: [filter_type_mixin],
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
            selectedValues(){
                const instance = this;
                if ( !this.query || !this.query.metaquery || !Array.isArray( this.query.metaquery ) )
                    return false;

                let index = this.query.metaquery.findIndex(newField => newField.key === this.field );
                if ( index >= 0){
                    let metadata = this.query.metaquery[ index ];
                    let collectionTarget = ( this.field_object && this.field_object.field_type_options.collection_id ) ?
                        this.field_object.field_type_options.collection_id : this.collection_id;


                    if ( this.type === 'Tainacan\\Field_Types\\Relationship' ) {
                        let query = qs.stringify({ postin: metadata.value  });

                        axios.get('/collection/' + collectionTarget + '/items?' + query)
                            .then( res => {
                                for (let item of res.data) {
                                    instance.selected.push({ label: item.title, value: item.id, img: '' });
                                }
                            })
                            .catch(error => {
                                console.log(error);
                            });
                    } else {
                        for (let item of metadata.value) {
                            instance.selected.push({ label: item, value: item, img: '' });
                        }
                    }
                } else {
                    return false;
                }
            }
        }
    }
</script>