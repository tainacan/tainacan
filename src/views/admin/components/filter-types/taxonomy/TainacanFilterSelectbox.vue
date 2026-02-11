<template>
    <div class="block">
        <b-select
                ref="filterSelect"
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
                    :value="option.value">
                {{ getUnescapedLabel(option.label) + ( option.total_items ? (' (' + option.total_items + ')') : '' ) }}
                </option>
        </b-select>
    </div>
</template>

<script>
    import { isCancel } from '../../../js/axios';
    import { filterTypeMixin, dynamicFilterTypeMixin } from '../../../js/filter-types-mixin';

    export default {
        mixins: [ filterTypeMixin, dynamicFilterTypeMixin ],
        emits: [
            'input',
            'update-parent-collapse'
        ],
        data(){
            return {
                selected: '',
                options: [],
                taxonomy: '',
                taxonomyId: ''
            }
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
            getFocusRestoreElement() {
                const root = this.$refs.filterSelect && this.$refs.filterSelect.$el;
                return root ? (root.querySelector('select') || root) : null;
            },
            getUnescapedLabel(label) {
                return typeof _.unescape === 'function' ? _.unescape(label) : label;
            },
            reloadOptions(shouldReload) {
                if ( !this.isUsingElasticSearch && shouldReload )
                    this.loadOptions();
            },
            loadOptions() {
                if (this.getOptionsValuesCancel != undefined)
                    this.getOptionsValuesCancel.cancel('Facet search Canceled.');

                const promise = this.getValuesTaxonomy({
                    metadatumId: this.metadatumId,
                    isRepositoryLevel: this.isRepositoryLevel,
                    number: this.filter.max_options
                });

                promise.request
                    .then((res) => {
                        if (res && res.data && res.data.values)
                            this.$emit('update-parent-collapse', res.data.values.length > 0);
                        this.$nextTick(() => this.tryRestoreFocus());
                    })
                    .catch((error) => {
                        if (!isCancel(error))
                            this.$console.log('Error on facets request: ', error);
                    });

                this.getOptionsValuesCancel = promise.source;
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
