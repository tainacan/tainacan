<template>
    <div class="block">
        <b-taginput
                size="is-small"
                icon="magnify"
                v-model="selected"
                :data="options"
                autocomplete
                :loading="isLoading"
                expanded
                :remove-on-keys="[]"
                field="label"
                attached
                :aria-labelledby="labelId"
                :class="{'has-selected': selected != undefined && selected != []}"
                @typing="search"
                :placeholder="$i18n.get('info_type_to_add_terms')">
            <template slot-scope="props">
                <div class="media">
                    <div class="media-content">
                        <span class="ellipsed-text">{{ props.option.label }}</span>
                        <span 
                                v-if="props.option.total_items != undefined"
                                class="has-text-gray">{{ "(" + props.option.total_items + ")" }}</span>
                    </div>
                </div>
            </template>
            <template 
                    v-if="!isLoading" 
                    slot="empty">
                {{ $i18n.get('info_no_options_found'	) }}
            </template>
        </b-taginput>
    </div>
</template>

<script>
    import qs from 'qs';
    import { tainacan as axios } from '../../../js/axios/axios';

    export default {
        created(){
            this.collection = ( this.collection_id ) ? this.collection_id : this.filter.collection_id;
            this.metadatum = ( this.metadatum_id ) ? this.metadatum_id : this.filter.metadatum.metadatum_id ;
            this.type = this.filter.metadatum.metadata_type;

            let in_route = '/collection/' + this.collection + '/metadata/' +  this.metadatum;

            if(this.isRepositoryLevel || this.collection == 'filter_in_repository'){
                in_route = '/metadata/'+ this.metadatum;
            }

            axios.get(in_route)
                .then( res => {
                    let metadatum = res.data;
                    this.selectedValues( metadatum.metadata_type_options.taxonomy_id );
                });
            
            this.$eventBusSearch.$on('removeFromFilterTag', this.cleanSearchFromTag);
        },
        data(){
            return {
                results:'',
                selected:[],
                options: [],
                isLoading: false,
                type: '',
                collection: '',
                metadatum: '',
                taxonomy: '',
                isUsingElasticSearch: tainacan_plugin.wp_elasticpress == "1" ? true : false
            }
        },
        props: {
            filter: {
                type: Object // concentrate all attributes metadatum id and type
            },
            metadatum_id: [Number], // not required, but overrides the filter metadatum id if is set
            collection_id: [Number], // not required, but overrides the filter metadatum id if is set
            labelId: '',
            query: {
                type: Object // concentrate all attributes metadatum id and type
            },
            isRepositoryLevel: Boolean,
        },
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
                    taxonomy: this.taxonomy,
                    metadatum_id: ( this.metadatum_id ) ? this.metadatum_id : this.filter.metadatum,
                    collection_id: ( this.collection_id ) ? this.collection_id : this.filter.collection_id,
                    terms: values
                });

                this.$eventBusSearch.$emit("sendValuesToTags", {
                    filterId: this.filter.id,
                    value: labels
                });
            }
        },
        methods: {
            search: _.debounce( function(query) {
                this.isLoading = true;
                this.options = [];
                
                let query_items = { 
                    'current_query': this.query, 
                    'search': query
                };

                let endpoint = this.isRepositoryLevel ? '/facets/' + this.metadatum : '/collection/'+ this.collection +'/facets/' + this.metadatum;

                endpoint += '?order=asc&' + qs.stringify(query_items);
                let valuesToIgnore = [];
                for(let val of this.selected){
                    valuesToIgnore.push( val.value );
                }

                return axios.get(endpoint).then( res => {
                    for (let term of res.data.values) {   
                          
                        this.taxonomy = term.taxonomy;

                        if (valuesToIgnore != undefined && valuesToIgnore.length > 0) {
                            let indexToIgnore = valuesToIgnore.findIndex(value => value == term.value);
                            if (indexToIgnore < 0) {
                                if( term.label.toLowerCase().indexOf( query.toLowerCase() ) >= 0 ){
                                    this.options.push({
                                        label: term.label, 
                                        value: term.value,
                                        total_items: term.total_items
                                    });
                                }
                            }
                        } else {
                            if( term.label.toLowerCase().indexOf( query.toLowerCase() ) >= 0 ){
                                this.options.push({
                                    label: term.label,
                                    value: term.value,    
                                    total_items: term.total_items
                                });
                            }
                        }                                       
                    }
                    this.isLoading = false;
                })
                .catch(error => {
                    this.isLoading = false;
                    this.$console.log(error);
                });
            }, 500),
            selectedValues( taxonomyId ){
                if ( !this.query || !this.query.taxquery || !Array.isArray( this.query.taxquery ) )
                    return false;

                this.taxonomy = 'tnc_tax_' + taxonomyId;

                let index = this.query.taxquery.findIndex(newMetadatum => newMetadatum.taxonomy == this.taxonomy );
                if ( index >= 0){
                    let metadata = this.query.taxquery[ index ];

                    for ( let id of metadata.terms ){
                       this.getTerm( taxonomyId, id );
                    }
                } else {
                    return false;
                }
            },
            getTerm( taxonomy, id ){
                //getting a specific value from api, does not need be in fecat api
                return axios.get('/taxonomy/' + taxonomy + '/terms/' + id + '?order=asc' )
                    .then( res => {
                        this.selected.push({ label: res.data.name, value: res.data.id });
                    })
                    .catch(error => {
                        this.$console.log(error);
                    });
            },
            cleanSearchFromTag(filterTag) {
                               
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
                            taxonomy: this.taxonomy,
                            metadatum_id: ( this.metadatum_id ) ? this.metadatum_id : this.filter.metadatum,
                            collection_id: ( this.collection_id ) ? this.collection_id : this.filter.collection_id,
                            terms: values
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
