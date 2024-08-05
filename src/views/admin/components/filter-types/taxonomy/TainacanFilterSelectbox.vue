<template>
    <div class="block">
        <b-select
                :loading="isLoadingOptions"
                :disabled="!isLoadingOptions && options.length <= 0"
                :model-value="selected"
                :aria-labelledby="'filter-label-id-' + filter.id"
                :placeholder="filter.placeholder ? filter.placeholder : $i18n.get('label_selectbox_init')"
                expanded
                @update:model-value="($event) => { resetPage(); onSelect($event) }">
            <option value="">
                {{ filter.placeholder ? filter.placeholder : $i18n.get('label_selectbox_init') }}
            </option>
            <option
                    v-for="(option, index) in options"
                    :key="index"
                    :label="option.label + ( option.total_items ? (' (' + option.total_items + ')') : '' )"
                    :value="option.value">
                <span 
                        v-if="option.total_items != undefined"
                        class="has-text-gray">{{ "(" + option.total_items + ")" }}</span>  
            </option>
        </b-select>
    </div>
</template>

<script>
    import qs from 'qs';
    import { tainacanApi, CancelToken, isCancel } from '../../../js/axios';
    import { mapGetters } from 'vuex';
    import { filterTypeMixin } from '../../../js/filter-types-mixin';
    
    export default {
        mixins: [ filterTypeMixin ],
        emits: [
            'input',
            'update-parent-collapse'
        ],
        data(){
            return {
                isLoadingOptions: false,
                getOptionsValuesCancel: undefined,
                selected: '',
                options: [],
                taxonomy: '',
                taxonomyId: ''
            }
        },
        computed: {
            ...mapGetters('search', {
                'facetsFromItemSearch': 'getFacets'
            }),
        },
        watch: {
            facetsFromItemSearch: {
                handler() {
                    if (this.isUsingElasticSearch)
                        this.loadOptions();
                },
                immediate: true,
                deep:true
            }, 
            isLoadingItems: {
                handler() {
                    if ( this.isUsingElasticSearch )
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
        mounted() {
            if (!this.isUsingElasticSearch)
                this.loadOptions(); 
        },
        created() {
            if (this.filter.metadatum && 
                this.filter.metadatum.metadata_type_object && 
                this.filter.metadatum.metadata_type_object.options &&
                this.filter.metadatum.metadata_type_object.options.taxonomy &&
                this.filter.metadatum.metadata_type_object.options.taxonomy_id
            ) {
                this.taxonomyId = this.filter.metadatum.metadata_type_object.options.taxonomy_id;
                this.taxonomy = this.filter.metadatum.metadata_type_object.options.taxonomy;
            }

            this.$eventBusSearchEmitter.on('hasToReloadFacets', this.reloadOptions);
        },
        beforeUnmount() {
                        
            // Cancels previous Request
            if (this.getOptionsValuesCancel != undefined)
                this.getOptionsValuesCancel.cancel('Facet search Canceled.');

            this.$eventBusSearchEmitter.off('hasToReloadFacets', this.reloadOptions); 
        },
        methods: {
            reloadOptions(shouldReload) {
                if ( !this.isUsingElasticSearch && shouldReload )
                    this.loadOptions();
            },
            loadOptions() {
                if (!this.isUsingElasticSearch) {
                    let promise = null;
                    const source = CancelToken.source();

                    // Cancels previous Request
                    if (this.getOptionsValuesCancel != undefined)
                        this.getOptionsValuesCancel.cancel('Facet search Canceled.');

                    this.isLoadingOptions = true;
                    let query_items = { 'current_query': this.query };

                    let route = '';
                    
                    if (this.isRepositoryLevel)
                        route = `/facets/${this.metadatumId}?getSelected=1&order=asc&parent=0&number=${this.filter.max_options}&` + qs.stringify(query_items);
                    else {
                        if (this.filter.collection_id == 'default' && this.currentCollectionId)
                            route = `/collection/${this.currentCollectionId}/facets/${this.metadatumId}?getSelected=1&order=asc&number=${this.filter.max_options}&` + qs.stringify(query_items);
                        else
                            route = `/collection/${this.filter.collection_id}/facets/${this.metadatumId}?getSelected=1&order=asc&number=${this.filter.max_options}&` + qs.stringify(query_items);
                    }

                    this.options = [];

                    promise = new Object({
                        request:
                            new Promise((resolve, reject) => {
                                tainacanApi.get(route, { cancelToken: source.token})
                                    .then( res => {
                                        resolve(res)
                                    })
                                    .catch(error => {
                                        reject(error)
                                    });
                            }),
                        source: source
                    });
                    promise.request
                        .then((res) => {
                            this.isLoadingOptions = false;
                            this.prepareOptionsForTaxonomy(res.data.values ? res.data.values : res.data);

                            if (res && res.data && res.data.values)
                                this.$emit('update-parent-collapse', res.data.values.length > 0 );
                        })
                        .catch( error => {
                            if (isCancel(error)) {
                                this.$console.log('Request canceled: ' + error.message);
                            } else {
                                this.$console.log('Error on facets request: ', error);
                                this.isLoadingOptions = false;
                            }
                        });
                    
                    // Search Request Token for cancelling
                    this.getOptionsValuesCancel = promise.source;  

                } else {
                    for (const facet in this.facetsFromItemSearch) {
                        if (facet == this.filter.id) {
                            if (Array.isArray(this.facetsFromItemSearch[facet])) {
                                this.prepareOptionsForTaxonomy(this.facetsFromItemSearch[facet]);
                                this.$emit('update-parent-collapse', this.facetsFromItemSearch[facet].length > 0 );
                            } else {
                                this.prepareOptionsForTaxonomy(Object.values(this.facetsFromItemSearch[facet]));
                                this.$emit('update-parent-collapse', Object.values(this.facetsFromItemSearch[facet]).length > 0 );
                            }
                        }    
                    }
                }
            },
            updateSelectedValues() {
                
                if ( !this.query || !this.query.taxquery || !Array.isArray( this.query.taxquery ) )
                    return false;
                    
                // Cleared either way, we might be coming from a situation where all the filters were removed.
                this.selected = '';

                const index = this.query.taxquery.findIndex(newMetadatum => newMetadatum.taxonomy == this.taxonomy);
                if (index >= 0) {
                    const metadata = this.query.taxquery[ index ];
                    if (this.selected != metadata.terms)
                        this.selected = metadata.terms;
                }
            },
            onSelect(selection) {
                this.$emit('input', {
                    filter: 'selectbox',
                    taxonomy: this.taxonomy,
                    metadatum_id: this.metadatumId,
                    collection_id: this.collectionId,
                    terms: selection
                });
            },
            prepareOptionsForTaxonomy(items) {
                this.options = [];
                this.options = items.slice(); // copy array.
                this.updateSelectedValues();
            }
        }
    }
</script>
