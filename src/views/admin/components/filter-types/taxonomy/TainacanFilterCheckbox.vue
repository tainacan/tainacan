<template>
    <div class="block">
        <template v-if="!filtersAsModal">
            <div
                    :class="{ 'skeleton': isLoadingOptions }"
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
                                        content: option.label,
                                        autoHide: true,
                                        popperClass: ['tainacan-tooltip', 'tooltip', isRepositoryLevel ? 'tainacan-repository-tooltip' : ''],
                                        placement: 'auto-start'
                                    }"
                                    class="checkbox-label-text">
                                {{ option.label }}
                            </span> 
                            <span 
                                    v-if="option.total_items != undefined"
                                    class="facet-item-count has-text-dark">&nbsp;{{ "(" + option.total_items + ")" }}</span>
                        </span>
                    </label>
                </div>
                <button
                        v-if="(filter.max_options && (options.length >= filter.max_options)) || someOptionHasChildren"
                        class="view-all-button link-style"
                        @click="openCheckboxModal()"> 
                    {{ $i18n.get('label_view_all') }}
                </button>
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
    import { isCancel } from '../../../js/axios';
    import CheckboxRadioFilterInput from '../../../components/other/checkbox-radio-filter-input.vue';
    import { filterTypeMixin, dynamicFilterTypeMixin } from '../../../js/filter-types-mixin';

    export default {
        components: { CheckboxRadioFilterInput },
        mixins: [ filterTypeMixin, dynamicFilterTypeMixin ],
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
                this.options = [];
                this.options = items.slice(); // copy array.
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

    .facet-item-count {
        margin-inline-end: auto;
    }

    .b-checkbox:hover .facet-item-count,
    .b-checkbox:focus .facet-item-count {
        --tainacan-info-color: var(--tainacan-input-color);
    }

</style>
