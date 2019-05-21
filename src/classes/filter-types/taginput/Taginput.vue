<template>
    <div class="block">
        <b-taginput
                icon="magnify"
                size="is-small"
                v-model="selected"
                :data="options"
                autocomplete
                expanded
                :loading="isLoadingOptions"
                :remove-on-keys="[]"
                field="label"
                attached
                @typing="search"
                :aria-labelledby="labelId"
                :placeholder="(type == 'Tainacan\\Metadata_Types\\Relationship') ? $i18n.get('info_type_to_search_items') : $i18n.get('info_type_to_add_metadata')">
            <template slot-scope="props">
                <div class="media">
                    <div
                            class="media-left"
                            v-if="props.option.img">
                        <img
                                :alt="$i18n.get('label_thumbnail')"
                                width="24"
                                :src="`${props.option.img}`">
                    </div>
                    <div class="media-content">
                        <span class="ellipsed-text">{{ props.option.label }}</span>
                        <span 
                                v-if="props.option.total_items != undefined"
                                class="has-text-gray">{{ "(" + props.option.total_items + ")" }}</span>
                    </div>
                </div>
            </template>
            <template 
                    v-if="!isLoadingOptions" 
                    slot="empty">
                {{ $i18n.get('info_no_options_found'	) }}
            </template>
        </b-taginput>
    </div>
</template>

<script>
    import { tainacan as axios, isCancel } from '../../../js/axios/axios';
    import { filter_type_mixin } from '../filter-types-mixin'
    import qs from 'qs';

    export default {
        created(){
            this.collection = ( this.collection_id ) ? this.collection_id : this.filter.collection_id;
            this.metadatum = ( this.metadatum_id ) ? this.metadatum_id : this.filter.metadatum.metadatum_id;
            const vm = this;

            let in_route = '/collection/' + this.collection + '/metadata/' +  this.metadatum;

            if(this.isRepositoryLevel || this.collection == 'filter_in_repository'){
                in_route = '/metadata/'+ this.metadatum + '?nopaging=1';
            }

            axios.get(in_route)
                .then( res => {
                    let result = res.data;
                    if( result && result.metadata_type ){
                        vm.metadatum_object = result;
                        vm.type = result.metadata_type;
                        vm.selectedValues();
                    }
                })
                .catch(error => {
                    this.$console.log(error);
                });
            
            this.$eventBusSearch.$on('removeFromFilterTag', this.cleanSearchFromTags);
        },
        data(){
            return {
                results:'',
                selected:[],
                options: [],
                type: '',
                collection: '',
                metadatum: '',
                metadatum_object: {}
            }
        },
        props: {
            isRepositoryLevel: Boolean,
            labelId: String
        },
        mixins: [filter_type_mixin],
        watch: {
            selected( value ){
                this.selected = value;

                let values = [];
                let labels = [];
                if( this.selected.length > 0 ){
                    for(let val of this.selected){
                        values.push( val.value );
                        labels.push( val.label );
                    }
                }
                this.$emit('input', {
                    filter: 'taginput',
                    compare: 'IN',
                    metadatum_id: this.metadatum,
                    collection_id: ( this.collection_id ) ? this.collection_id : this.filter.collection_id,
                    value: values
                });

                this.$eventBusSearch.$emit( 'sendValuesToTags', {
                    filterId: this.filter.id,
                    value: labels
                });
            }
        },
        methods: {
            search: _.debounce( function(query) {
                let promise = null;
                this.options = [];
                let valuesToIgnore = [];

                for(let val of this.selected)
                    valuesToIgnore.push( val.value );

                // Cancels previous Request
                if (this.getOptionsValuesCancel != undefined)
                    this.getOptionsValuesCancel.cancel('Facet search Canceled.');

                if ( this.type === 'Tainacan\\Metadata_Types\\Relationship' )
                    promise = this.getValuesRelationship( query, this.isRepositoryLevel, valuesToIgnore );
                else
                    promise = this.getValuesPlainText( this.metadatum, query, this.isRepositoryLevel, valuesToIgnore );

                promise.request
                    .catch( error => {
                        if (isCancel(error))
                            this.$console.log('Request canceled: ', error.message);
                        else
                            this.$console.error( error );
                    });

                // Search Request Token for cancelling
                this.getOptionsValuesCancel = promise.source;
                
            }, 500),
            selectedValues(){
                const instance = this;
                if ( !this.query || !this.query.metaquery || !Array.isArray( this.query.metaquery ) )
                    return false;

                let index = this.query.metaquery.findIndex(newMetadatum => newMetadatum.key == this.metadatum );
                if ( index >= 0){
                    let metadata = this.query.metaquery[ index ];
                    let collectionTarget = ( this.metadatum_object && this.metadatum_object.metadata_type_options.collection_id ) ?
                        this.metadatum_object.metadata_type_options.collection_id : this.collection_id;


                    if ( this.type === 'Tainacan\\Metadata_Types\\Relationship' ) {
                        let query = qs.stringify({ postin: metadata.value  });

                        axios.get('/collection/' + collectionTarget + '/items?' + query)
                            .then( res => {
                                if (res.data.items) {
                                    for (let item of res.data) {
                                        instance.selected.push({ label: item.title, value: item.id, img: item.thumbnail.thumbnail[0] });
                                    }
                                }
                            })
                            .catch(error => {
                                this.$console.log(error);
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
            cleanSearchFromTags(filterTag) {
                               
                if (filterTag.filterId == this.filter.id) {

                    let selectedIndex = this.selected.findIndex(option => option.label == filterTag.singleValue);
                    if (selectedIndex >= 0) {

                        this.selected.splice(selectedIndex, 1);

                        let values = [];
                        let labels = [];  
                        for(let val of this.selected){
                            values.push( val.value );
                            labels.push( val.label );
                        }
                        
                        this.$emit('input', {
                            filter: 'taginput',
                            compare: 'IN',
                            metadatum_id: this.metadatum,
                            collection_id: ( this.collection_id ) ? this.collection_id : this.filter.collection_id,
                            value: values
                        });

                        this.$eventBusSearch.$emit( 'sendValuesToTags', {
                            filterId: this.filter.id,
                            value: labels
                        });
                    }
                }
            }
        },
        beforeDestroy() {
            this.$eventBusSearch.$off('removeFromFilterTag', this.cleanSearchFromTags);
        }
    }
</script>