<template>
    <div class="block">
        <b-taginput
                ref="filterTaginput"
                v-a11y-autocomplete
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
                :maxtags="1"
                :has-counter="false"
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
                                class="has-text-dark">{{ "(" + props.option.total_items + ")" }}</span>
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
    import { isCancel } from '../../../js/axios';
    import { filterTypeMixin, dynamicFilterTypeMixin } from '../../../js/filter-types-mixin';
    
    export default {
        mixins: [ filterTypeMixin, dynamicFilterTypeMixin ],
        emits: [
            'input',
        ],
        data(){
            return {
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
            getFocusRestoreElement() {
                const root = this.$refs.filterTaginput && this.$refs.filterTaginput.$el;
                return root ? (root.querySelector('input') || root) : null;
            },
            performSearch(query) {
                if (query != this.searchQuery) {
                    this.searchQuery = query;
                    this.options = [];
                    this.offset = 0;
                }
                if (!query.length) {
                    this.searchQuery = query;
                    this.options = [];
                    this.offset = 0;
                }

                if (this.offset > 0 && this.options.length >= this.totalFacets)
                    return;

                if (this.getOptionsValuesCancel != undefined)
                    this.getOptionsValuesCancel.cancel('Facet search Canceled.');

                const promise = this.getValuesTaxonomy({
                    metadatumId: this.metadatumId,
                    isRepositoryLevel: this.isRepositoryLevel,
                    number: 12,
                    search: this.searchQuery,
                    offset: this.offset,
                    valuesToIgnore: this.selected
                });

                promise.request
                    .then((res) => {
                        this.totalFacets = res.headers['x-wp-total'];
                        this.offset += 12;
                    })
                    .catch((error) => {
                        if (!isCancel(error))
                            this.$console.log(error);
                    });

                this.getOptionsValuesCancel = promise.source;
            },
            prepareOptionsForTaxonomySearch(res, search, valuesToIgnore) {
                const query = (search != null && search !== undefined) ? String(search).toLowerCase() : '';
                const values = res.data && res.data.values ? res.data.values : (Array.isArray(res.data) ? res.data : []);
                for (const term of values) {
                    const skipByIgnore = valuesToIgnore && valuesToIgnore.length > 0 &&
                        valuesToIgnore.findIndex((value) => value == term.value) >= 0;
                    if (skipByIgnore)
                        continue;
                    const matchesSearch = !query || (term.label && term.label.toLowerCase().indexOf(query) >= 0);
                    if (matchesSearch)
                        this.options.push({
                            label: term.label,
                            value: term.value,
                            total_items: term.total_items
                        });
                }
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
