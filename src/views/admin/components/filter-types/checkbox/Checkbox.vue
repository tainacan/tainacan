<template>
    <div 
            :style="{ 'height': isLoadingOptions ? (Number(filter.max_options)*28) + 'px' : 'auto' }"
            :class="{ 'skeleton': isLoadingOptions }"
            class="block">
        <div
                v-for="(option, index) in options.slice(0, filter.max_options)"
                v-if="!isLoadingOptions"
                :key="index"
                class="metadatum">
            <label 
                    v-if="index <= filter.max_options - 1"
                    class="b-checkbox checkbox is-small">
                <input 
                        v-model="selected"
                        :value="option.value"
                        type="checkbox"> 
                    <span class="check" /> 
                    <span class="control-label">
                        <span class="checkbox-label-text">{{ option.label }}</span> 
                        <span 
                                v-if="option.total_items != undefined"
                                class="has-text-gray">&nbsp;{{ "(" + option.total_items + ")" }}</span>
                    </span>
            </label>
            <button
                    class="view-all-button link-style"
                    v-if="option.showViewAllButton && index == options.slice(0, filter.max_options).length - 1"
                    @click="openCheckboxModal(option.parent)"> 
                {{ $i18n.get('label_view_all') }}
            </button>
        </div>
        <p 
                v-if="isLoadingOptions == false && options.length != undefined && options.length <= 0"
                class="no-options-placeholder">
            {{ $i18n.get('info_no_options_avialable_filtering') }}
        </p>
    </div>
</template>

<script>
    import { isCancel } from '../../../js/axios';
    import { filterTypeMixin, dynamicFilterTypeMixin } from '../../../js/filter-types-mixin';
    import CheckboxRadioModal from '../../../components/modals/checkbox-radio-modal.vue';

    export default {
        mixins: [filterTypeMixin, dynamicFilterTypeMixin],
        data(){
            return {
                options: [],
                selected: []
            }
        },
        watch: {
            selected(newVal, oldVal) {
                const isEqual = (newVal.length == oldVal.length) && newVal.every((element, index) => {
                    return element === oldVal[index]; 
                });

                if (!isEqual)
                    this.onSelect();
            },
            'query.metaquery'() {
                if (!this.isUsingElasticSearch)
                    this.loadOptions();
            },
            'query.taxquery'() {
                if (!this.isUsingElasticSearch)
                    this.loadOptions();
            },
            facetsFromItemSearch() {
                if (this.isUsingElasticSearch)
                    this.loadOptions();
            }
        },
        mounted() {
            if (!this.isUsingElasticSearch)
                this.loadOptions();
        },
        methods: {
            loadOptions() {
                let promise = null;
                
                // Cancels previous Request
                if (this.getOptionsValuesCancel != undefined)
                    this.getOptionsValuesCancel.cancel('Facet search Canceled.');

                if ( this.metadatumType === 'Tainacan\\Metadata_Types\\Relationship' )
                    promise = this.getValuesRelationship( null, this.isRepositoryLevel, [], 0, this.filter.max_options, false, '1');
                else
                    promise = this.getValuesPlainText( this.metadatumId, null, this.isRepositoryLevel, [], 0, this.filter.max_options, false, '1' );
     
                promise.request
                    .then(() => {
                        this.updateSelectedValues();
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

                if ( index >= 0){
                    let query = this.query.metaquery.slice();
                    this.selected = query[ index ].value;
                } else {
                    this.selected = [];
                }

                let onlyLabels = [];
                if (!isNaN(this.selected[0])){
                    for (let aSelected of this.selected) {
                        let valueIndex = this.options.findIndex(option => option.value == aSelected);

                        if (valueIndex >= 0) {
                            onlyLabels.push(this.options[valueIndex].label);
                        }
                    }
                }

                this.$emit( 'sendValuesToTags', { label: onlyLabels.length ? onlyLabels : this.selected, value: this.selected });
            },
            openCheckboxModal() {
                this.$buefy.modal.open({
                    parent: this,
                    component: CheckboxRadioModal,
                    props: {
                        //parent: parent,
                        filter: this.filter,
                        //taxonomy_id: this.taxonomy_id,
                        selected: this.selected,
                        metadatumId: this.metadatumId,
                        //taxonomy: this.taxonomy,
                        collectionId: this.collectionId,
                        metadatum_type: this.metadatumType,
                        isRepositoryLevel: this.isRepositoryLevel,
                        query: this.query
                    },
                    events: {
                        appliedCheckBoxModal: () => {
                            this.loadOptions();
                        } 
                    },
                    trapFocus: true
                });
            },
            updatesIsLoading(isLoadingOptions) {
                this.isLoadingOptions = isLoadingOptions;
            }
        }
    }
</script>

<style lang="scss" scoped>

    
    .view-all-button {
        font-size: 0.75rem;
        padding: 0.1rem 1rem;
    }

    .is-loading:after {
        border: 2px solid white !important;
        border-top-color: #dbdbdb !important;
        border-right-color: #dbdbdb !important;
    }

    .no-options-placeholder {
        margin-left: 0.5rem;
        font-size: 0.75rem;
        color: #555758;
    }

    .b-checkbox .control-label {
        display: flex;
        flex-wrap: nowrap;
        width: 100%;
    }
    .checkbox-label-text {
        white-space: nowrap;
        text-overflow: ellipsis;
        overflow: hidden;
    }
</style>