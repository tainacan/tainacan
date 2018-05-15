<template>
    <div class="block">
        <b-autocomplete
                icon="magnify"
                size="is-small"
                :id="id"
                v-model="selected"
                :data="options"
                @input="search"
                :loading="isLoading"
                field="label"
                @select="option => setResults(option) ">
            <template slot-scope="props">
                <div class="media">
                    <div
                            class="media-left"
                            v-if="props.option.img">
                        <img
                                width="32"
                                :src="`${props.option.img}`">
                    </div>
                    <div class="media-content">
                        {{ props.option.label }}
                    </div>
                </div>
            </template>
        </b-autocomplete>
        <!-- <ul 
                class="selected-list-box"
                v-if="selected !== '' && selected !== undefined">
            <li>
                <b-tag 
                        attached
                        closable
                        @close="clearSearch()">
                    {{ label }}
                </b-tag>
            </li>
        </ul> -->
    </div>
</template>

<script>
    import { tainacan as axios } from '../../../js/axios/axios'
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
                    this.$console.log(error);
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
                label: ''
            }
        },
        mixins: [filter_type_mixin],
        methods: {
            setResults(option){
                if(!option)
                    return;
                this.results = option.value;
                this.label = option.label;
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
                    promise = this.getValuesRelationship( collectionTarget );

                } else {
                    promise = this.getValuesPlainText( this.field, query );
                }

                promise.then( () => {
                    this.isLoading = false;
                }).catch( error => {
                    this.$console.log('error select', error );
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
                                   // instance.selected.push({ label: item.title, value: item.id, img: '' });
                                    this.$console.log(item.title);
                                    instance.results = item.title;
                                    instance.label = item.title;
                                }
                            })
                            .catch(error => {
                                this.$console.log(error);
                            });
                    } else {
                        instance.results = metadata.value;
                    }
                } else {
                    return false;
                }
            },
            clearSearch(){
                this.results = '';
                this.label = '';
                this.selected = '';
                this.$emit('input', {
                    filter: 'autocomplete',
                    field_id: this.field,
                    collection_id: ( this.collection_id ) ? this.collection_id : this.filter.collection_id,
                    value: ''
                });
            },
        }
    }
</script>
<style scoped>
    #profileImage {
        width: 32px;
        height: 32px;
        font-size: 35px;
        color: #fff;
        text-align: center;
        line-height: 150px;
        margin: 20px 0;
    }
</style>