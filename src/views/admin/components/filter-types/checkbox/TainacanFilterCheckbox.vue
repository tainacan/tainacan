<template>
    <div 
            :style="{ 'height': isLoadingOptions && !filtersAsModal ? (Number(filter.max_options)*1.375) + 'rem' : 'auto' }"
            :class="{ 'skeleton': isLoadingOptions && !filtersAsModal }"
            class="block">
        <template v-if="!filtersAsModal">
            <template v-if="!isLoadingOptions">
                <div
                        v-for="(option, index) in options.slice(0, filter.max_options)"
                        :key="index"
                        class="metadatum">
                    <label 
                            v-if="index <= filter.max_options - 1"
                            class="b-checkbox checkbox is-small">
                        <input 
                                v-model="selected"
                                :value="option.value"
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
                                        autoHide: trie,
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
                            v-if="option.showViewAllButton && index == options.slice(0, filter.max_options).length - 1"
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
                let promise = null;
                
                // Cancels previous Request
                if (this.getOptionsValuesCancel != undefined)
                    this.getOptionsValuesCancel.cancel('Facet search Canceled.');
                    
                if ( this.metadatumType === 'Tainacan\\Metadata_Types\\Relationship' || this.metadatumType === 'Tainacan\\Metadata_Types\\Control' )
                    promise = this.getValuesRelationship({
                        search: null,
                        isRepositoryLevel: this.isRepositoryLevel,
                        valuesToIgnore: [], 
                        offset: 0, 
                        number: this.filter.max_options,
                        isInCheckboxModal: false,
                        getSelected: '1'
                    });
                else
                    promise = this.getValuesPlainText({
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
                this.$buefy.modal.open({
                    parent: this,
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
                        } 
                    },
                    trapFocus: true,
                    customClass: 'tainacan-modal',
                    closeButtonAriaLabel: this.$i18n.get('close')
                });
            },
        }
    }
</script>

<style lang="scss" scoped>

    
    .view-all-button {
        font-size: 0.75em !important;
        padding: 0.1em 1em;
    }

    .is-loading:after {
        border: 2px solid var(--tainacan-background-color) !important;
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
        align-items: baseline;
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
