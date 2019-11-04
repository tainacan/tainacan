<template>
    <div class="block">
        <b-taginput
                size="is-small"
                icon="magnify"
                v-model="selected"
                :data="options"
                autocomplete
                :loading="isLoadingOptions"
                expanded
                :remove-on-keys="[]"
                field="label"
                attached
                :aria-close-label="$i18n.get('remove_value')"
                :aria-labelledby="'filter-label-id-' + filter.id"
                :class="{'has-selected': selected != undefined && selected != []}"
                @typing="search"
                @input="onSelect"
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
                    v-if="!isLoadingOptions" 
                    slot="empty">
                {{ $i18n.get('info_no_options_found'	) }}
            </template>
        </b-taginput>
    </div>
</template>

<script>
    import qs from 'qs';
    import { tainacan as axios } from '../../../js/axios/axios';
    import { filterTypeMixin, dynamicFilterTypeMixin } from '../filter-types-mixin';
    
    export default {
        mixins: [ filterTypeMixin, dynamicFilterTypeMixin ],
        created() {
            if (this.filter.metadatum && 
                this.filter.metadatum.metadata_type_object && 
                this.filter.metadatum.metadata_type_object.options &&
                this.filter.metadatum.metadata_type_object.options.taxonomy &&
                this.filter.metadatum.metadata_type_object.options.taxonomy_id) {
                    this.taxonomyId = this.filter.metadatum.metadata_type_object.options.taxonomy_id;
                    this.taxonomy = this.filter.metadatum.metadata_type_object.options.taxonomy;
                }
        },
        watch: {
            'query.taxquery'() {
                this.updateSelectedValues();
            }
        },
        mounted() {
            this.updateSelectedValues();
        },
        data(){
            return {
                results:'',
                selected:[],
                options: [],
                taxonomy: '',
                taxonomyId: ''
            }
        },
        methods: {
            search: _.debounce( function(query) {
                this.isLoadingOptions = true;
                this.options = [];
                
                let query_items = { 
                    'current_query': this.query, 
                    'search': query
                };

                let endpoint = this.isRepositoryLevel ? '/facets/' + this.metadatumId : '/collection/'+ this.collectionId +'/facets/' + this.metadatumId;

                endpoint += '?order=asc&' + qs.stringify(query_items);
                
                let valuesToIgnore = [];
                for(let val of this.selected)
                    valuesToIgnore.push( val.value );
                
                return axios.get(endpoint).then( res => {
                    for (let term of res.data.values) {   

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
                    this.isLoadingOptions = false;
                })
                .catch(error => {
                    this.isLoadingOptions = false;
                    this.$console.log(error);
                });
            }, 500),
            updateSelectedValues(){
                if ( !this.query || !this.query.taxquery || !Array.isArray( this.query.taxquery ) )
                    return false;

                let index = this.query.taxquery.findIndex(newMetadatum => newMetadatum.taxonomy == this.taxonomy);

                if (index >= 0) {
                    let metadata = this.query.taxquery[ index ];
                    this.selected = [];

                    if (metadata.terms && metadata.terms.length) {
                        this.getTerms(metadata)
                            .then(() => {
                                this.$emit( 'sendValuesToTags', { 
                                    label: this.selected.map((option) => option.label), 
                                    value: this.selected.map((option) => option.value),
                                    taxonomy: this.taxonomy
                                });
                            });
                    }
                } else {
                    this.selected = [];
                }
            },
            onSelect() {
                this.$emit('input', {
                    filter: 'taginput',
                    compare: 'IN',
                    taxonomy: this.taxonomy,
                    metadatum_id: this.metadatumId,
                    collection_id: this.collectionId,
                    terms: this.selected.map((option) => option.value)
                });
            },
            getTerms(metadata) {

                let params = { 
                    'include': metadata.terms, 
                    'order': 'asc',
                    'fetchonly': 0
                };

                return axios.get('/taxonomy/' + this.taxonomyId + '/terms/?' + qs.stringify(params) )
                    .then( res => {
                        this.selected = res.data.map(term => { return { label: term.name, value: term.id } });
                    })
                    .catch(error => {
                        this.$console.log(error);
                    })
            }
        }
    }
</script>
