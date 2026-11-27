<template>
    <div class="block">
        <template v-if="!filtersAsModal">
            <div
                    :class="{ 'skeleton': isLoadingOptions && !isLoadingMore }"
                    :aria-busy="isLoadingMore ? 'true' : 'false'"
                    class="filter-options-wrap">
                <div
                        v-for="option in options"
                        :key="option.value"
                        class="metadatum">
                    <label 
                            v-if="!option.isChild"
                            class="b-checkbox checkbox is-small">
                        <input 
                                v-model="selected"
                                :value="option.value"
                                :data-filter-option-value="String(option.value)"
                                type="checkbox"
                                @input="resetPage"> 
                        <span class="check" /> 
                        <span class="control-label">
                            <span 
                                    v-tooltip="{
                                        delay: {
                                            show: 800,
                                            hide: 100,
                                        },
                                        content: getOptionHierarchyPath(option) ? (getOptionHierarchyPath(option) + ' > ' + option.label) : option.label,
                                        autoHide: true,
                                        popperClass: ['tainacan-tooltip', 'tooltip', isRepositoryLevel ? 'tainacan-repository-tooltip' : ''],
                                        placement: 'auto-start'
                                    }"
                                    class="checkbox-label-text"
                                    :class="{ 'has-hierarchy-path': getOptionHierarchyPath(option) }">
                                <span
                                        v-if="getOptionHierarchyPath(option)"
                                        class="hierarchy-path">
                                    {{ getOptionHierarchyPath(option) }}
                                    <span class="sr-only">&gt;</span>
                                </span>
                                {{ option.label }}
                            </span> 
                            <span 
                                    v-if="option.total_items != undefined"
                                    class="facet-item-count has-text-dark">&nbsp;{{ "(" + option.total_items + ")" }}</span>
                        </span>
                    </label>
                </div>
                <button
                        v-if="showViewMoreButton"
                        ref="viewMoreButton"
                        class="view-all-button link-style"
                        :disabled="isLoadingMore"
                        :class="{ 'is-loading': isLoadingMore }"
                        @click="loadMoreOptions()"> 
                    {{ $i18n.get('label_view_more') }}
                </button>
                <button
                        v-if="showViewAllButton"
                        ref="viewAllButton"
                        class="view-all-button link-style"
                        @click="openCheckboxModal()"> 
                    {{ $i18n.get('label_view_all') }}
                </button>
                <p
                        aria-live="polite"
                        aria-atomic="true"
                        class="sr-only">
                    {{ viewMoreLiveMessage }}
                </p>
                <p 
                        v-if="options.length != undefined && options.length <= 0"
                        class="no-options-placeholder">
                    {{ $i18n.get('info_no_options_available_filtering') }}
                </p>
            </div>
        </template>
        <template v-else>
            <checkbox-radio-filter-input
                    :is-modal="false" 
                    :filter="filter"
                    :taxonomy-id="taxonomyId"
                    :selected="selected"
                    :metadatum-id="metadatumId"
                    :taxonomy="taxonomy"
                    :collection-id="collectionId"
                    :is-taxonomy="true"
                    :query="query"
                    :current-collection-id="currentCollectionId"
                    @input="(newSelected) => {
                        const existingValue = selected.indexOf(newSelected); 
                        if (existingValue >= 0)
                            selected.splice(existingValue, 1);
                        else
                            selected.push(newSelected);
                    }" />
        </template>
    </div>
</template>

<script>
    import CheckboxRadioFilterInput from '../../../components/other/checkbox-radio-filter-input.vue';
    import { filterTypeMixin, dynamicFilterTypeMixin, progressiveCheckboxMixin } from '../../../js/filter-types-mixin';

    export default {
        components: { CheckboxRadioFilterInput },
        mixins: [ filterTypeMixin, dynamicFilterTypeMixin, progressiveCheckboxMixin ],
        props: {
            isRepositoryLevel: Boolean,
            filtersAsModal: Boolean
        },
        emits: [
            'input',
            'update-parent-collapse'
        ],
        data(){
            return {
                isLoadingOptions: true,
                options: [],
                selected: [],
                taxonomy: '',
                taxonomyId: ''
            }
        },
        computed: {
            someOptionHasChildren() {
                return this.options.some(option => option.total_children > 0);
            },
            showHierarchyPath() {
                const options = this.filterTypeOptions && !Array.isArray(this.filterTypeOptions)
                    ? this.filterTypeOptions
                    : (this.filter && this.filter.filter_type_options ? this.filter.filter_type_options : {});
                return options.show_hierarchy_path === 'yes';
            }
        },
        watch: {
            selected: {
                handler(newVal, oldVal) {
                    const isEqual = (Array.isArray(newVal) && Array.isArray(oldVal) && (newVal.length == oldVal.length)) && newVal.every((element, index) => {
                        return element === oldVal[index]; 
                    });
                    if (!isEqual)
                        this.onSelect();
                },
                deep: true
            },
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
                    if (!this.filtersAsModal && this.isUsingElasticSearch)
                        this.isLoadingOptions = this.isLoadingItems;
                },
                immediate: true
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
            this.$eventBusSearchEmitter.on('hasToReloadFacets', this.reloadOptions); 
        },
        mounted(){
            if (!this.isUsingElasticSearch)
                this.loadOptions();
        },
        beforeUnmount() {
            
            // Cancels previous Request
            if (this.getOptionsValuesCancel != undefined)
                this.getOptionsValuesCancel.cancel('Facet search Canceled.');

            this.$eventBusSearchEmitter.off('hasToReloadFacets', this.reloadOptions); 
        }, 
        methods: {
            getOptionHierarchyPath(option) {
                // Root terms have an empty path, so only child terms get one
                if (!this.showHierarchyPath || !option || !option.hierarchy_path)
                    return '';

                // The API path ends with a separator, as in "Parent>Child>"
                return option.hierarchy_path
                    .split('>')
                    .map(ancestor => ancestor.trim())
                    .filter(ancestor => ancestor !== '')
                    .join(' > ');
            },
            shouldForceViewAllModalOnly() {
                return this.someOptionHasChildren;
            },
            reloadOptions(shouldReload) {
                if ( !this.isUsingElasticSearch && shouldReload )
                    this.loadOptions();
            },
            loadOptions() {
                if (this.getOptionsValuesCancel != undefined)
                    this.getOptionsValuesCancel.cancel('Facet search Canceled.');

                this.resetProgressiveState();

                // Progressive mode under ElasticPress needs the dedicated facets endpoint.
                const forceFacetsApi = this.maxViewMorePages > 0;

                const promise = this.getValuesTaxonomy({
                    metadatumId: this.metadatumId,
                    isRepositoryLevel: this.isRepositoryLevel,
                    number: this.facetPageSize,
                    ...(forceFacetsApi ? { parent: 0, offset: 0, getSelected: '1' } : {})
                });

                promise.request
                    .then((res) => {
                        const fromAggregations = !!(res && res.fromAggregations);
                        this.updateProgressiveStateFromResponse(res, { isInitial: true, fromAggregations });
                        if (res && res.data && res.data.values)
                            this.$emit('update-parent-collapse', res.data.values.length > 0);
                        else if (this.options)
                            this.$emit('update-parent-collapse', this.options.length > 0);
                        this.$nextTick(() => this.tryRestoreFocus());
                    })
                    .catch(() => undefined);

                this.getOptionsValuesCancel = promise.source;
            },
            loadMoreOptions() {
                if (!this.showViewMoreButton || this.isLoadingMore || this.someOptionHasChildren)
                    return;

                if (this.getOptionsValuesCancel != undefined)
                    this.getOptionsValuesCancel.cancel('Facet search Canceled.');

                this.isLoadingMore = true;
                this.shouldAddOptions = true;

                const previousOptionsCount = this.options.length;

                const promise = this.getValuesTaxonomy({
                    metadatumId: this.metadatumId,
                    isRepositoryLevel: this.isRepositoryLevel,
                    number: this.facetPageSize,
                    offset: this.nextFacetOffset,
                    parent: 0,
                    getSelected: '0',
                    valuesToIgnore: this.options.map(option => option.value)
                });

                promise.request
                    .then((res) => {
                        this.viewMoreClicks += 1;
                        this.updateProgressiveStateFromResponse(res, { isInitial: false, fromAggregations: false });
                        this.isLoadingMore = false;
                        this.shouldAddOptions = false;
                        this.$nextTick(() => this.handleFocusAfterLoadMore(previousOptionsCount));
                    })
                    .catch(() => {
                        this.isLoadingMore = false;
                        this.shouldAddOptions = false;
                    });

                this.getOptionsValuesCancel = promise.source;
            },
            updateSelectedValues() {
                if ( !this.query || !this.query.taxquery || !Array.isArray( this.query.taxquery ) )
                    return false;
                    
                let index = this.query.taxquery.findIndex(newMetadatum => newMetadatum.taxonomy == this.taxonomy );

                this.selected = index >= 0 ? this.query.taxquery[ index ].terms : [];
            },
            onSelect() {
                this.$emit('input', {
                    filter: 'checkbox',
                    taxonomy: this.taxonomy,
                    compare: 'IN',
                    metadatum_id: this.metadatumId,
                    collection_id: this.collectionId,
                    terms: this.selected
                });
            },
            openCheckboxModal() {
                const modalTrigger = this.$modalFocusA11y.captureTrigger();
                this.$buefy.modal.open({
                    component: CheckboxRadioFilterInput,
                    props: {
                        filter: this.filter,
                        taxonomyId: this.taxonomyId,
                        selected: this.selected,
                        metadatumId: this.metadatumId,
                        taxonomy: this.taxonomy,
                        collectionId: this.collectionId,
                        isTaxonomy: true,
                        query: this.query
                    },                    
                    events: {
                        appliedCheckBoxModal: () => {
                            this.loadOptions();
                        },
                        input: (newSelected) => {
                            const existingValue = this.selected.indexOf(newSelected); 
                            if (existingValue >= 0)
                                this.selected.splice(existingValue, 1);
                            else
                                this.selected.push(newSelected);
                        },
                        beforeClose: () => this.$modalFocusA11y.restoreFocus(modalTrigger, this)
                    },
                    width: 'max(768px, calc(100% - (4 * var(--tainacan-one-column))))',
                    trapFocus: true,
                    customClass: 'tainacan-modal',
                    canCancel: ['escape', 'outside']
                });
            },
            prepareOptionsForTaxonomy(items) {
                const incoming = Array.isArray(items) ? items.slice() : [];
                if (this.shouldAddOptions === true && this.options && this.options.length) {
                    const existing = new Set(this.options.map(option => String(option.value)));
                    const uniqueIncoming = incoming.filter(item => !existing.has(String(item.value)));
                    this.options = this.options.concat(uniqueIncoming);
                } else {
                    this.options = incoming;
                }
                this.updateSelectedValues();
            }
        }
    }
</script>

<style lang="scss" scoped>

    .block {
        position: relative;
    }

    .skeleton * {
        // position: absolute;
        // inset: 0;
        // width: 100%;
        opacity: 0;
        pointer-events: none;
        //z-index: -1;
    }

    .view-all-button {
        font-size: 0.75em !important;
        display: block;
    }

    .is-loading:after {
        border: 2px solid white !important;
        border-top-color: var(--tainacan-gray2) !important;
        border-right-color: var(--tainacan-gray2) !important;
    }

    .no-options-placeholder {
        margin-inline-start: 0.5em;
        font-size: 0.75em;
        color: var(--tainacan-info-color);
    }

    .b-checkbox .control-label {
        display: flex;
        flex-wrap: nowrap;
        width: 100%;
        align-items: center;
    }
    .checkbox-label-text {
        white-space: wrap;
        text-overflow: ellipsis;
        overflow: hidden;
        line-height: 1.45em;
        break-inside: avoid;
        display: -webkit-box;
        line-clamp: 2;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical; 
    }

    // The path takes one line of its own, so the term name is never the part cut off
    .checkbox-label-text.has-hierarchy-path {
        line-clamp: 3;
        -webkit-line-clamp: 3;
    }
    .hierarchy-path {
        display: block;
        font-size: 0.875em;
        line-height: 1.35em;
        color: var(--tainacan-info-color);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .facet-item-count {
        margin-inline-end: auto;
    }

    .b-checkbox:hover .facet-item-count,
    .b-checkbox:focus .facet-item-count {
        --tainacan-info-color: var(--tainacan-input-color);
    }

</style>
