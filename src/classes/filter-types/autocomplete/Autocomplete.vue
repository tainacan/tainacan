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
        <br>
        <div class="field has-text-centered">
            <b-tag v-if="results !== ''"
                   type="is-primary"
                   size="is-small"
                   closable
                   @close="clearSearch()">
                {{ selected }}
            </b-tag>
        </div>
    </div>
</template>

<script>
    import { tainacan as axios } from '../../../js/axios/axios'
    import { filter_type_mixin } from '../filter-types-mixin'

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
                selected:'',
                options: [],
                isLoading: false,
                type: '',
                collection: '',
                field: '',
            }
        },
        mixins: [filter_type_mixin],
        methods: {
            setResults(option){
                if(!option)
                    return;
                this.results = option.value;
                this.onSelect()
            },
            onSelect(){
                this.$emit('input', {
                    filter: 'autocomplete',
                    field_id: this.field,
                    collection_id: this.collection,
                    value: this.results
                });
            },
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
            },
            clearSearch(){
                this.results = '';
                this.selected = '';
                this.$emit('input', {
                    filter: 'autocomplete',
                    field_id: ( this.field_id ) ? this.field_id : this.filter.field,
                    collection_id: ( this.collection_id ) ? this.collection_id : this.filter.collection_id,
                    value: ''
                });
            },
        }
    }
</script>