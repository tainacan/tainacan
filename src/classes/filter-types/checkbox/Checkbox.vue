<template>
    <div class="block">
        <div
                v-for="(option, index) in options"
                :key="index"
                class="metadatum">
            <b-checkbox
                    v-model="selected"
                    :native-value="option.value"
            >{{ option.label }}</b-checkbox>
        </div>
    </div>
</template>

<script>
    import { tainacan as axios } from '../../../js/axios/axios';
    import { filter_type_mixin } from '../filter-types-mixin'

    export default {
        created(){
            this.collection = ( this.collection_id ) ? this.collection_id : this.filter.collection_id;
            this.metadatum = ( this.metadatum_id ) ? this.metadatum_id : this.filter.metadatum.metadatum_id;
            const vm = this;

            let in_route = '/collection/' + this.collection + '/metadata/' +  this.metadatum +'?nopaging=1';

            if(this.isRepositoryLevel || this.collection == 'filter_in_repository'){
                in_route = '/metadata?nopaging=1';
            }

            axios.get(in_route)
                .then( res => {
                    let result = res.data;
                    if( result && result.metadatum_type ){
                        vm.metadatum_object = result;
                        vm.type = result.metadatum_type;
                        vm.loadOptions();
                    }
                })
                .catch(error => {
                    this.$console.log(error);
                });
        },
        props: {
            isRepositoryLevel: Boolean,
        },
        data(){
            return {
                isLoading: false,
                options: [],
                type: '',
                collection: '',
                metadatum: '',
                selected: [],
                metadatum_object: {}
            }
        },
        mixins: [filter_type_mixin],
        watch: {
            selected: function(val){
                this.selected = val;
                this.onSelect();
            }
        },
        methods: {
            loadOptions(){
                let promise = null;
                this.isLoading = true;

                if ( this.type === 'Tainacan\\Metadatum_Types\\Relationship' ) {
                    let collectionTarget = ( this.metadatum_object && this.metadatum_object.metadatum_type_options.collection_id ) ?
                        this.metadatum_object.metadatum_type_options.collection_id : this.collection_id;
                    promise = this.getValuesRelationship( collectionTarget );

                } else {
                    promise = this.getValuesPlainText( this.metadatum, null, this.isRepositoryLevel );
                }

                promise.then(() => {
                    this.isLoading = false;
                    this.selectedValues()
                })
                .catch( error => {
                    this.$console.log('error select', error );
                    this.isLoading = false;
                });
            },
            onSelect(){
                this.$emit('input', {
                    filter: 'checkbox',
                    compare: 'IN',
                    metadatum_id: this.metadatum,
                    collection_id: ( this.collection_id ) ? this.collection_id : this.filter.collection_id,
                    value: this.selected
                });
            },
            selectedValues(){
                if ( !this.query || !this.query.metaquery || !Array.isArray( this.query.metaquery ) )
                    return false;

                let index = this.query.metaquery.findIndex(newMetadatum => newMetadatum.key === this.metadatum );
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