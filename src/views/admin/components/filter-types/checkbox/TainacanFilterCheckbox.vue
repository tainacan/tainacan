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
                    <label class="b-checkbox checkbox is-small">
                        <input 
                                v-model="selected"
                                :value="option.value"
                                :data-filter-option-value="String(option.value)"
                                type="checkbox"
                                @input="resetPage()"> 
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
                        v-if="filter.max_options && (options.length >= filter.max_options)"
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
                    :selected="selected"
                    :metadatum-id="metadatumId"
                    :collection-id="collectionId"
                    :metadatum-type="metadatumType"
                    :is-repository-level="isRepositoryLevel"
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
    import { filterTypeMixin, dynamicFilterTypeMixin } from '../../../js/filter-types-mixin';
    import CheckboxRadioFilterInput from '../../../components/other/checkbox-radio-filter-input.vue';

    export default {
        components: { CheckboxRadioFilterInput },
        mixins: [filterTypeMixin, dynamicFilterTypeMixin],
        props: {
            filtersAsModal: Boolean
        },
        emits: [
            'input',
            'update-parent-collapse'
        ],
        data(){
            return {
                options: [],
                selected: []
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
                deep: true
            },
        },
        mounted() {
            if (!this.isUsingElasticSearch)
                this.loadOptions();
        },
        created() {
            this.$eventBusSearchEmitter.on('hasToReloadFacets', this.reloadOptions);
        },
        beforeUnmount() {
            this.$eventBusSearchEmitter.off('hasToReloadFacets', this.reloadOptions); 
        },
        methods: {
            reloadOptions(shouldReload) {
                if ( !this.isUsingElasticSearch && shouldReload )
                    this.loadOptions();
            },
            loadOptions() {
                // Cancels previous Request
                if (this.getOptionsValuesCancel != undefined)
                    this.getOptionsValuesCancel.cancel('Facet search Canceled.');
                    
                const promise = ( this.metadatumType === 'Tainacan\\Metadata_Types\\Relationship' || this.metadatumType === 'Tainacan\\Metadata_Types\\Control' )
                    ? this.getValuesRelationship({
                        search: null,
                        isRepositoryLevel: this.isRepositoryLevel,
                        valuesToIgnore: [], 
                        offset: 0, 
                        number: this.filter.max_options,
                        isInCheckboxModal: false,
                        getSelected: '1'
                    })
                    : this.getValuesPlainText({
                        metadatumId: this.metadatumId,
                        search: null,
                        isRepositoryLevel: this.isRepositoryLevel,
                        valuesToIgnore: [],
                        offset: 0,
                        number: this.filter.max_options,
                        isInCheckboxModal: false,
                        getSelected: '1'
                    });
     
                promise.request
                    .then((res) => {
                        this.updateSelectedValues();
                        
                        if (res && res.data && res.data.values)
                            this.$emit('update-parent-collapse', res.data.values.length > 0 );
                        this.$nextTick(() => this.tryRestoreFocus());
                    })
                    .catch( (error) => {
                        if (isCancel(error)) {
                            this.$console.log('Request canceled: ' + error.message);
                            this.updateSelectedValues();
                        } else
                            this.$console.error( error );
                    });
                
                // Search Request Token for cancelling
                this.getOptionsValuesCancel = promise.source;  
            },
            onSelect() {
                this.$emit('input', {
                    filter: 'checkbox',
                    compare: 'IN',
                    metadatum_id: this.metadatumId,
                    collection_id: this.collectionId,
                    value: this.selected
                });
            },
            updateSelectedValues() {
                if ( !this.query || !this.query.metaquery || !Array.isArray( this.query.metaquery ) )
                    return false;

                let index = this.query.metaquery.findIndex(newMetadatum => newMetadatum.key == this.metadatumId );
                
                this.selected = index >= 0 ? this.query.metaquery.slice()[ index ].value : [];
            },
            openCheckboxModal() {
                const modalTrigger = this.$modalFocusA11y.captureTrigger();
                this.$buefy.modal.open({
                    component: CheckboxRadioFilterInput,
                    props: {
                        //parent: parent,
                        filter: this.filter,
                        //taxonomyId: this.taxonomyId,
                        selected: this.selected,
                        metadatumId: this.metadatumId,
                        //taxonomy: this.taxonomy,
                        collectionId: this.collectionId,
                        metadatumType: this.metadatumType,
                        isRepositoryLevel: this.isRepositoryLevel,
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
                    trapFocus: true,
                    customClass: 'tainacan-modal',
                    canCancel: ['escape', 'outside']
                });
            },
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
        border: 2px solid var(--tainacan-background-color) !important;
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
