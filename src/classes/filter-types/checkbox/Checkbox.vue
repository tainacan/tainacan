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
                    if( result && result.metadata_type ){
                        vm.metadatum_object = result;
                        vm.type = result.metadata_type;
                        vm.loadOptions();
                    }
                })
                .catch(error => {
                    this.$console.log(error);
                });

            this.$eventBusSearch.$on('removeFromFilterTag', (filterTag) => {
                if (filterTag.filterId == this.filter.id) {

                    let selectedIndex = this.selected.findIndex(option => option == filterTag.singleValue);
                    if (selectedIndex >= 0) {

                        let newSelected = this.selected.slice();
                        newSelected.splice(selectedIndex, 1); 

                        this.$emit('input', {
                            filter: 'checkbox',
                            compare: 'IN',
                            metadatum_id: this.metadatum,
                            collection_id: ( this.collection_id ) ? this.collection_id : this.filter.collection_id,
                            value: newSelected
                        });

                        this.$eventBusSearch.$emit( 'sendValuesToTags', {
                            filterId: this.filter.id,
                            value: newSelected
                        });

                        this.selectedValues();
                    }
                }
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

                if ( this.type === 'Tainacan\\Metadata_Types\\Relationship' ) {
                    let collectionTarget = ( this.metadatum_object && this.metadatum_object.metadata_type_options.collection_id ) ?
                        this.metadatum_object.metadata_type_options.collection_id : this.collection_id;
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

                this.$eventBusSearch.$emit( 'sendValuesToTags', {
                    filterId: this.filter.id,
                    value: this.selected
                });
            },
            selectedValues(){
                if ( !this.query || !this.query.metaquery || !Array.isArray( this.query.metaquery ) )
                    return false;

                let index = this.query.metaquery.findIndex(newMetadatum => newMetadatum.key === this.metadatum );
                if ( index >= 0){
                    let query = this.query.metaquery.slice();
                    this.selected = query[ index ].value;
                } else {
                    this.selected = [];
                    return false;
                }
            }
        }
    }
</script>