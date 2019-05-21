<template>
    <div>
        <b-input
                :aria-labelledby="labelId"
                size="is-small"
                type="number"
                step="any"
                autocomplete="off"
                @input="emit()"
                v-model="value"/>
    </div>
</template>

<script>
    import { tainacan as axios } from '../../../js/axios/axios';
    import { wpAjax } from "../../../admin/js/mixins";

    export default {
        mixins: [ wpAjax ],
        created() {
            this.collection = ( this.collection_id ) ? this.collection_id : this.filter.collection_id;
            this.metadatum = ( this.metadatum_id ) ? this.metadatum_id : this.filter.metadatum.metadatum_id;

            let in_route = '/collection/' + this.collection + '/metadata/' +  this.metadatum;

            if(this.isRepositoryLevel || this.collection == 'filter_in_repository'){
                in_route = '/metadata/'+ this.metadatum;
            }

            axios.get(in_route)
                .then( res => {
                    let result = res.data;
                    if( result && result.metadata_type ){
                        this.metadatum_object = result;
                        this.selectedValues();
                    }
                })
                .catch(error => {
                    this.$console.log(error);
                });

            this.$eventBusSearch.$on('removeFromFilterTag', this.cleanSearchFromTags);
        },
        data(){
            return {
                value: null,
                isValid: false,
                clear: false,
                collection: '',
                metadatum: '',
                metadatum_object: {},
            }
        },
        props: {
            filter: {
                type: Object // concentrate all attributes metadatum id and type
            },
            metadatum_id: [Number], // not required, but overrides the filter metadatum id if is set
            collection_id: [Number], // not required, but overrides the filter metadatum id if is set
            labelId: '',
            query: Object,
            isRepositoryLevel: Boolean,
        },
        methods: {
            selectedValues(){
                if ( !this.query || !this.query.metaquery || !Array.isArray( this.query.metaquery ) )
                    return false;

                let index = this.query.metaquery.findIndex(newMetadatum => newMetadatum.key === this.metadatum );
                if ( index >= 0){
                    let metadata = this.query.metaquery[ index ];
                    if ( metadata.value && metadata.value.length > 0){
                        this.value = metadata.value[0];
                        this.isValid = true;
                    }

                    if (metadata.value[0] != undefined) {
                        this.$eventBusSearch.$emit( 'sendValuesToTags', {
                            filterId: this.filter.id,
                            value: metadata.value[0]
                        });
                    }

                } else {
                    return false;
                }
            },
            cleanSearchFromTags(filterTag) {
                if (filterTag.filterId == this.filter.id)
                    this.clearSearch();
            },
            clearSearch(){

                this.clear = true;

                this.$emit('input', {
                    filter: 'numeric',
                    metadatum_id: this.metadatum,
                    collection_id: ( this.collection_id ) ? this.collection_id : this.filter.collection_id,
                    value: ''
                });

                this.value = null;
            },
            // emit the operation for listeners
            emit() {

                if ( this.value === null || this.value === '')
                    return;
                
                this.$emit('input', {
                    filter: 'numeric',
                    metadatum_id: this.metadatum,
                    collection_id: ( this.collection_id ) ? this.collection_id : this.filter.collection_id,
                    value: this.value
                });

                this.$eventBusSearch.$emit( 'sendValuesToTags', {
                    filterId: this.filter.id,
                    value: this.value
                });
                
            }
        },
        beforeDestroy() {
            this.$eventBusSearch.$off('removeFromFilterTag', this.cleanSearchFromTags);
        }
    }
</script>
