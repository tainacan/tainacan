<template>
    <div 
            :style="{ 'height': isLoadingOptions && !filtersAsModal ? (Number(filter.max_options)*28) + 'px' : 'auto' }"
            :class="{ 'skeleton': isLoadingOptions && !filtersAsModal }"
            class="block">
        <template v-if="!filtersAsModal">
            <template v-if="!isLoadingOptions">
                <div
                        v-for="(option, index) in options.slice(0, filter.max_options)"
                        :key="index"
                        :value="index"
                        class="metadatum">
                    <label 
                            v-if="!option.isChild"
                            class="b-checkbox checkbox is-small">
                        <input 
                                v-model="selected"
                                :value="option.value"
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
                                    class="facet-item-count has-text-gray">&nbsp;{{ "(" + option.total_items + ")" }}</span>
                        </span>
                    </label>
                    <button
                            v-if="option.showViewAllButton"
                            class="view-all-button link-style"
                            @click="openCheckboxModal(option.parent)"> 
                        {{ $i18n.get('label_view_all') }}
                    </button>
                </div>
                <p 
                        v-if="options.length != undefined && options.length <= 0"
                        class="no-options-placeholder">
                    {{ $i18n.get('info_no_options_available_filtering') }}
                </p>
            </template>
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
    import qs from 'qs';
    import { tainacanApi, CancelToken, isCancel } from '../../../js/axios';
    import { mapGetters } from 'vuex';
    import CheckboxRadioFilterInput from '../../../components/other/checkbox-radio-filter-input.vue';
    import { filterTypeMixin } from '../../../js/filter-types-mixin';
    
    export default {
        components: { CheckboxRadioFilterInput },
        mixins: [ filterTypeMixin ],
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
                getOptionsValuesCancel: undefined,
                options: [],
                selected: [],
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
                            route = `/collection/${this.currentCollectionId}/facets/${this.metadatumId}?getSelected=1&order=asc&parent=0&number=${this.filter.max_options}&` + qs.stringify(query_items);
                        else
                            route = `/collection/${this.filter.collection_id}/facets/${this.metadatumId}?getSelected=1&order=asc&parent=0&number=${this.filter.max_options}&` + qs.stringify(query_items);
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
            openCheckboxModal(parent) {
                this.$buefy.modal.open({
                    parent: this,
                    component: CheckboxRadioFilterInput,
                    props: {
                        parent: parent,
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
                        } 
                    },
                    width: 'max(768px, calc(100% - (4 * var(--tainacan-one-column))))',
                    trapFocus: true,
                    customClass: 'tainacan-modal',
                    closeButtonAriaLabel: this.$i18n.get('close')
                });
            },
            prepareOptionsForTaxonomy(items) {

                this.options = [];
                this.options = items.slice(); // copy array.

                if (this.options) {
                    let hasChildren = false;

                    for( let term of this.options ){
                        if (term.total_children > 0){
                            hasChildren = true;
                            break;
                        }
                    }

                    if (this.filter.max_options && (this.options.length >= this.filter.max_options || hasChildren)) {
                        let showViewAllButton = true;

                        if (this.options.length > this.filter.max_options){
                            this.options[this.filter.max_options - 1].showViewAllButton = showViewAllButton;
                        } else {
                            this.options[this.options.length - 1].showViewAllButton = showViewAllButton;
                        }
                    }
                }
                this.updateSelectedValues();
            }
        }
    }
</script>

<style lang="scss" scoped>

    .view-all-button {
        font-size: 0.75em !important;
        padding: 0.1em 1em;
    }

    .is-loading:after {
        border: 2px solid white !important;
        border-top-color: var(--tainacan-gray2) !important;
        border-right-color: var(--tainacan-gray2) !important;
    }

    .no-options-placeholder {
        margin-left: 0.5em;
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
        margin-left: auto;
    }

    .b-checkbox:hover .facet-item-count,
    .b-checkbox:focus .facet-item-count {
        --tainacan-info-color: var(--tainacan-input-color);
    }

</style>
