<template>
    <div class="block">
        <b-taginput
                size="is-small"
                icon="magnify"
                :data="options"
                autocomplete
                :open-on-focus="true"
                :loading="isLoadingOptions"
                expanded
                :remove-on-keys="[]"
                field="label"
                attached
                :aria-close-label="$i18n.get('remove_value')"
                :aria-labelledby="'filter-label-id-' + filter.id"
                :class="{'has-selected': selected != undefined && selected != []}"
                :placeholder="filter.placeholder ? filter.placeholder : $i18n.get('info_type_to_add_terms')"
                check-infinite-scroll
                @typing="search"
                @focus="($event) => { searchQuery = $event.target.value; performSearch(searchQuery) }"
                @update:model-value="($event) => { resetPage(); onSelect($event) }"
                @infinite-scroll="searchMore">
            <template #default="props">
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
                    #empty>
                {{ $i18n.get('info_no_options_found'	) }}
            </template>
        </b-taginput>
    </div>
</template>

<script>
    import qs from 'qs';
    import { tainacanApi } from '../../../js/axios';
    import { filterTypeMixin } from '../../../js/filter-types-mixin';
    
    export default {
        mixins: [ filterTypeMixin ],
        emits: [
            'input',
        ],
        data(){
            return {
                isLoadingOptions: false,
                results:'',
                selected:[], // Simple array of IDs, no more objects and not bound to the taginput
                options: [],
                taxonomy: '',
                taxonomyId: '',
                searchQuery: '',
                totalFacets: 0,
                offset: 0
            }
        },
        watch: {
            isLoadingItems: {
                handler() {
                    this.isLoadingOptions = this.isLoadingItems;
                },
                immediate: true
            },
            'query': {
                handler() {
                    this.updateSelectedValues();
                },
                deep: true
            }
        },
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
        mounted() {
            this.updateSelectedValues();
        },
        methods: {
            performSearch(query) {
                // String update
                if (query != this.searchQuery) {
                    this.searchQuery = query;
                    this.options = [];
                    this.offset = 0;
                } 
                
                // String cleared
                if (!query.length) {
                    this.searchQuery = query;
                    this.options = [];
                    this.offset = 0;
                }

                // No need to load more
                if (this.offset > 0 && this.options.length >= this.totalFacets)
                    return

                this.isLoadingOptions = true;
                
                let query_items = { 
                    'current_query': this.query, 
                    'search': this.searchQuery,
                    'offset': this.offset,
                    'number': 12
                };

                let endpoint = '';
                
                if (this.isRepositoryLevel) 
                    endpoint += '/facets/' + this.metadatumId;
                else {
                    if (this.filter.collection_id == 'default' && this.currentCollectionId)
                        endpoint += '/collection/' + this.currentCollectionId +'/facets/' + this.metadatumId;
                    else
                        endpoint += '/collection/' + this.filter.collection_id + '/facets/' + this.metadatumId;
                }

                endpoint += '?order=asc&' + qs.stringify(query_items);
                
                const valuesToIgnore = JSON.parse(JSON.stringify(this.selected));

                return tainacanApi.get(endpoint).then( res => {
                    for (let term of res.data.values) {   

                        if (valuesToIgnore != undefined && valuesToIgnore.length > 0) {
                            let indexToIgnore = valuesToIgnore.findIndex(value => value == term.value);
                            if (indexToIgnore < 0) {
                                if (term.label.toLowerCase().indexOf( query.toLowerCase() ) >= 0){
                                    this.options.push({
                                        label: term.label, 
                                        value: term.value,
                                        total_items: term.total_items
                                    });
                                }
                            }
                        } else {
                            if (term.label.toLowerCase().indexOf( query.toLowerCase() ) >= 0){
                                this.options.push({
                                    label: term.label,
                                    value: term.value,    
                                    total_items: term.total_items
                                });
                            }
                        } 
                        
                    }
                    
                    this.totalFacets = res.headers['x-wp-total'];
                    this.offset += 12;
                    this.isLoadingOptions = false;
                })
                .catch(error => {
                    this.isLoadingOptions = false;
                    this.$console.log(error);
                });
            },
            search: _.debounce( function(query) {
                this.performSearch(query);
            }, 500),
            searchMore: _.debounce(function () {
                this.performSearch(this.searchQuery)
            }, 250),
            updateSelectedValues() {
                
                if ( !this.query || !this.query.taxquery || !Array.isArray( this.query.taxquery ) )
                    return false;
                    
                // Cleared either way, we might be coming from a situation where all the filters were removed.
                this.selected = [];

                const index = this.query.taxquery.findIndex(newMetadatum => newMetadatum.taxonomy == this.taxonomy);
                if (index >= 0) {
                    const metadata = this.query.taxquery[ index ];
                    for (let termId of metadata.terms)
                        this.selected.push(termId);
                }
            },
            onSelect(selection) {
                this.$emit('input', {
                    filter: 'taginput',
                    compare: 'IN',
                    taxonomy: this.taxonomy,
                    metadatum_id: this.metadatumId,
                    collection_id: this.collectionId,
                    terms: _.union(this.selected, selection.map(anOption => anOption.value))
                });
            }
        }
    }
</script>
