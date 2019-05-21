<template>
    <div 
            :style="{ 'height': isLoadingOptions ? (Number(filter.max_options)*28) + 'px' : 'auto' }"
            :class="{ 'skeleton': isLoadingOptions }"
            class="block">
        <!-- <span 
                v-if="isLoadingOptions"
                style="width: 100%; position: absolute;"
                class="icon has-text-centered loading-icon">
            <div class="control has-icons-right is-loading is-clearfix" />
        </span> -->
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
            <!-- <b-checkbox
                    v-if="index <= filter.max_options - 1"
                    v-model="selected"
                    :native-value="option.value">
                <span class="checkbox-label-text">{{ option.label }}</span> 
                <span 
                        v-if="option.total_items != undefined"
                        class="has-text-gray">{{ "(" + option.total_items + ")" }}</span>
            </b-checkbox> -->
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
    import { tainacan as axios, isCancel } from '../../../js/axios/axios';
    import { filter_type_mixin } from '../filter-types-mixin';
    import CheckboxRadioModal from '../../../admin/components/other/checkbox-radio-modal.vue';

    export default {
        created(){
            this.collection = ( this.collection_id ) ? this.collection_id : this.filter.collection_id;
            this.metadatum = ( this.metadatum_id ) ? this.metadatum_id : this.filter.metadatum.metadatum_id;

            let route = '/collection/' + this.collection + '/metadata/' +  this.metadatum +'?nopaging=1';

            if (this.isRepositoryLevel || this.collection == 'filter_in_repository')
                route = '/metadata?nopaging=1';

            axios.get(route)
                .then( res => {
                    let result = res.data;
                    if ( result && result.metadata_type ){
                        this.metadatum_object = result;
                        this.type = result.metadata_type;
                    
                        if (!this.isUsingElasticSearch)
                            this.loadOptions();

                    }
                })
                .catch(error => {
                    this.$console.log(error);
                });

            this.$eventBusSearch.$on('removeFromFilterTag', this.cleanSearchFromTags);

            if (this.isUsingElasticSearch) {
                this.isLoadingOptions = false;
                this.$eventBusSearch.$on('isLoadingItems', this.updatesIsLoading);
            }
        },
        props: {
            isRepositoryLevel: Boolean,
        },
        data(){
            return {
                options: [],
                type: '',
                collection: '',
                metadatum: '',
                selected: [],
                metadatum_object: {}
            }
        },
        mixins: [filter_type_mixin],
        watch: {
            selected: function(){
                //this.selected = val;
                this.onSelect();
            }
        },
        methods: {
            loadOptions(skipSelected) {
                let promise = null;
                
                // Cancels previous Request
                if (this.getOptionsValuesCancel != undefined)
                    this.getOptionsValuesCancel.cancel('Facet search Canceled.');

                if ( this.type === 'Tainacan\\Metadata_Types\\Relationship' )
                    promise = this.getValuesRelationship( null, this.isRepositoryLevel, [], 0, this.filter.max_options, false, '1');
                else
                    promise = this.getValuesPlainText( this.metadatum, null, this.isRepositoryLevel, [], 0, this.filter.max_options, false, '1' );
                
                if (skipSelected != undefined && skipSelected == true) {
                    promise.request
                        .then(() => {
                            if (this.options.length > this.filter.max_options)
                                this.options.splice(this.filter.max_options);
                        }).catch((error) => {
                            this.$console.error(error);
                        });
                } else {
                    promise.request
                        .then(() => {
                            this.selectedValues();
                        })
                        .catch( error => {
                            if (isCancel(error))
                                this.$console.log('Request canceled: ', error.message);
                            else
                                this.$console.error( error );
                        });
                }
                
            },
            onSelect() {
                this.$emit('input', {
                    filter: 'checkbox',
                    compare: 'IN',
                    metadatum_id: this.metadatum,
                    collection_id: ( this.collection_id ) ? this.collection_id : this.filter.collection_id,
                    value: this.selected
                });

                let onlyLabels = [];

                if(!isNaN(this.selected[0])){
                    for (let aSelected of this.selected) {
                        let valueIndex = this.options.findIndex(option => option.value == aSelected);
                        
                        if (valueIndex >= 0) {
                            onlyLabels.push(this.options[valueIndex].label);
                        }
                    }
                }

                this.$eventBusSearch.$emit( 'sendValuesToTags', {
                    filterId: this.filter.id,
                    value: onlyLabels.length ? onlyLabels : this.selected,
                });
            },
            selectedValues() {
                if ( !this.query || !this.query.metaquery || !Array.isArray( this.query.metaquery ) )
                    return false;

                let index = this.query.metaquery.findIndex(newMetadatum => newMetadatum.key === this.metadatum );
                if ( index >= 0){
                    let query = this.query.metaquery.slice();
                    this.selected = query[ index ].value;

                } else {
                    this.selected = [];
                    return false;
                }
            },
            openCheckboxModal() {
                this.$modal.open({
                    parent: this,
                    component: CheckboxRadioModal,
                    props: {
                        //parent: parent,
                        filter: this.filter,
                        //taxonomy_id: this.taxonomy_id,
                        selected: this.selected,
                        metadatum_id: this.metadatum,
                        //taxonomy: this.taxonomy,
                        collection_id: this.collection,
                        metadatum_type: this.type,
                        metadatum_object: this.metadatum_object,
                        isRepositoryLevel: this.isRepositoryLevel,
                        query: this.query
                    },
                    events: {
                        appliedCheckBoxModal: () => {
                            this.loadOptions();
                        } 
                    }
                });
            },
            cleanSearchFromTags(filterTag) {
                if (filterTag.filterId == this.filter.id) {

                    let selectedIndex = this.selected.findIndex(option => option == filterTag.singleValue);
                    let optionIndex = this.options.findIndex(option => option.label == filterTag.singleValue);
                    let alternativeIndex;

                    if (optionIndex >= 0) {
                        alternativeIndex = this.selected.findIndex(option => this.options[optionIndex].value == option);
                    }

                    if (selectedIndex >= 0 || alternativeIndex >= 0) {

                        selectedIndex >= 0 ? this.selected.splice(selectedIndex, 1) : this.selected.splice(alternativeIndex, 1); 

                        this.$emit('input', {
                            filter: 'checkbox',
                            compare: 'IN',
                            metadatum_id: this.metadatum,
                            collection_id: ( this.collection_id ) ? this.collection_id : this.filter.collection_id,
                            value: this.selected
                        });

                        this.$eventBusSearch.$emit( 'sendValuesToTags', {
                            filterId: this.filter.id,
                            value: this.selected
                        });

                        this.selectedValues();
                    }
                }
            },
            updatesIsLoading(isLoading) {
                this.isLoadingOptions = isLoading;
            }
        },
        beforeDestroy() {
            this.$eventBusSearch.$off('removeFromFilterTag', this.cleanSearchFromTags);
            
            if (this.isUsingElasticSearch)
                this.$eventBusSearch.$off('isLoadingItems', this.updatesIsLoading);
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