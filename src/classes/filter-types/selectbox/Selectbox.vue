<template>
    <div 
            :class="{ 'skeleton': isLoadingOptions }"
            class="block">
        <b-select
                v-if="!isLoadingOptions"
                :value="selected"
                :aria-labelledby="labelId"
                @input="onSelect($event)"
                :placeholder="$i18n.get('label_selectbox_init')"
                expanded>
            <option value="">{{ $i18n.get('label_selectbox_init') }}...</option>
            <option
                    v-for="(option, index) in options"
                    :key="index"
                    :label="option.label"
                    :value="option.value">
                {{ option.label }}
                <span 
                        v-if="option.total_items != undefined"
                        class="has-text-gray">{{ "(" + option.total_items + ")" }}</span>    
            </option>
        </b-select>
    </div>
</template>

<script>
    import { tainacan as axios, isCancel } from '../../../js/axios/axios';
    import { filter_type_mixin } from '../filter-types-mixin'

    export default {
        created(){
            this.collection = ( this.collection_id ) ? this.collection_id : this.filter.collection_id;
            this.metadatum = ( this.metadatum_id ) ? this.metadatum_id : this.filter.metadatum.metadatum_id;
            const vm = this;

            let in_route = '/collection/' + this.collection + '/metadata/' +  this.metadatum;

            if(this.isRepositoryLevel || this.collection == 'filter_in_repository'){
                in_route = '/metadata/'+ this.metadatum;
            }

            axios.get(in_route)
                .then( res => {
                    let result = res.data;
                    if( result && result.metadata_type ){
                        vm.metadatum_object = result;
                        vm.type = result.metadata_type;
                        
                        if (!this.isUsingElasticSearch)
                            vm.loadOptions();
                    }
                })
                .catch(error => {
                    this.$console.error(error);
                });
            
            this.$eventBusSearch.$on('removeFromFilterTag', this.cleanSearchFromTags);

            if (this.isUsingElasticSearch) {
                this.$eventBusSearch.$on('isLoadingItems', isLoading => {
                    this.isLoadingOptions = isLoading;
                });
            }
        },
        props: {
            isRepositoryLevel: Boolean,
            labelId: String
        },
        data(){
            return {
                options: [],
                type: '',
                collection: '',
                metadatum: ''
            }
        },
        mixins: [filter_type_mixin],
        watch: {
            selected(value) {
                if (value) {
                    this.$eventBusSearch.$emit( 'sendValuesToTags', {
                        filterId: this.filter.id,
                        value: value
                    });
                }
            }
        },
        computed: {
            selected() {
                if ( this.query && this.query.metaquery && Array.isArray( this.query.metaquery ) ) {

                    let index = this.query.metaquery.findIndex(newMetadatum => newMetadatum.key === this.metadatum );
                    if ( index >= 0){
                        let metadata = this.query.metaquery[ index ];
                        return metadata.value;
                    }
                }
                return undefined;
            }
        }, 
        methods: {
            loadOptions(){
                // Cancels previous Request
                if (this.getOptionsValuesCancel != undefined)
                    this.getOptionsValuesCancel.cancel('Facet search Canceled.');

                let promise = null;
                promise = this.getValuesPlainText( this.metadatum, null, this.isRepositoryLevel );
                promise.request
                    .then(() => {
                    })
                    .catch( error => {
                        if (isCancel(error))
                            this.$console.log('Request canceled: ', error.message);
                        else
                            this.$console.error( error );
                    });

                // Search Request Token for cancelling
                this.getOptionsValuesCancel = promise.source;
            },
            onSelect(value){
                //this.selected = value;
                this.$emit('input', {
                    filter: 'selectbox',
                    metadatum_id: this.metadatum,
                    collection_id: ( this.collection_id ) ? this.collection_id : this.filter.collection_id,
                    value: ( value ) ? value : ''
                });
            },
            cleanSearchFromTags(filterTag) {
                if (filterTag.filterId == this.filter.id)
                    this.onSelect();
            }
        },
        beforeDestroy() {
            this.$eventBusSearch.$off('removeFromFilterTag', this.cleanSearchFromTags);

            if (this.isUsingElasticSearch)
                this.$eventBusSearch.$off('isLoadingItems');
        }
    }
</script>

<style scoped>
    .skeleton {
        min-height: 36px;
    }
</style>
