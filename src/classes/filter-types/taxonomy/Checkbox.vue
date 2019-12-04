<template>
    <div 
            :style="{ 'height': isLoadingOptions ? (Number(filter.max_options)*28) + 'px' : 'auto' }"
            :class="{ 'skeleton': isLoadingOptions }"
            class="block">
        <!-- <span 
                v-if="isLoadingOptions"
                style="width: 100%"
                class="icon has-text-centered loading-icon">
            <div class="control has-icons-right is-loading is-clearfix" />
        </span> -->
        <div
                v-for="(option, index) in options.slice(0, filter.max_options)"
                v-if="!isLoadingOptions"
                :key="index"
                :value="index"
                class="control">
            <label 
                    v-if="!option.isChild"
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
                    v-if="option.showViewAllButton"
                    @click="openCheckboxModal(option.parent)"> 
                {{ $i18n.get('label_view_all') }}
            </button>
        </div>
        <p 
                v-if="!isLoadingOptions && options.length != undefined && options.length <= 0"
                class="no-options-placeholder">
            {{ $i18n.get('info_no_options_avialable_filtering') }}
        </p>
    </div>
</template>

<script>
    import qs from 'qs';
    import { tainacan as axios, CancelToken, isCancel } from '../../../js/axios/axios';
    import { mapGetters } from 'vuex';
    import CheckboxRadioModal from '../../../admin/components/other/checkbox-radio-modal.vue';
    import { filterTypeMixin, dynamicFilterTypeMixin } from '../filter-types-mixin';
    
    export default {
        mixins: [ filterTypeMixin, dynamicFilterTypeMixin ],   
        data(){
            return {
                isLoadingOptions: true,
                options: [],
                selected: [],
                taxonomy: '',
                taxonomyId: ''
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
            facetsFromItemSearch() {
                if (this.isUsingElasticSearch)
                    this.loadOptions();
            },
            'query.taxquery'() {
                this.loadOptions();
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
        mounted(){
            this.loadOptions();
        }, 
        methods: {
            ...mapGetters('search', [
                'getFacets'
            ]),
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
                    
                    if (this.collectionId == 'default')
                        route = `/facets/${this.metadatumId}?getSelected=1&order=asc&parent=0&number=${this.filter.max_options}&` + qs.stringify(query_items);
                    else
                        route = `/collection/${this.collectionId}/facets/${this.metadatumId}?getSelected=1&order=asc&parent=0&number=${this.filter.max_options}&` + qs.stringify(query_items);

                    this.options = [];

                    promise = new Object({
                        request:
                            new Promise((resolve, reject) => {
                                axios.get(route, { cancelToken: source.token})
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
                            this.prepareOptionsForTaxonomy(res.data.values ? res.data.values : res.data);
                            this.isLoadingOptions = false;
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
                            if (Array.isArray(this.facetsFromItemSearch[facet]))
                                this.prepareOptionsForTaxonomy(this.facetsFromItemSearch[facet]);
                            else
                                this.prepareOptionsForTaxonomy(Object.values(this.facetsFromItemSearch[facet]));
                        }    
                    }
                }
            },
            updateSelectedValues(){
                
                if ( !this.query || !this.query.taxquery || !Array.isArray( this.query.taxquery ) )
                    return false;
                    
                let index = this.query.taxquery.findIndex(newMetadatum => newMetadatum.taxonomy == this.taxonomy );
                if ( index >= 0){
                    let metadata = this.query.taxquery[ index ];
                    this.selected = metadata.terms;
                } else {
                    this.selected = [];
                    return false;
                }

                let onlyLabels = [];

                for (let selected of this.selected) {
                    let valueIndex = this.options.findIndex(option => option.value == selected );

                    if (valueIndex >= 0) {
                        let existingLabelIndex = onlyLabels.findIndex(aLabel => aLabel == this.options[valueIndex].label)
                        if (existingLabelIndex < 0)
                            onlyLabels.push(this.options[valueIndex].label);
                        else  
                            this.$set(onlyLabels, onlyLabels.push(this.options[valueIndex].label), existingLabelIndex); 
                    } else {
                        
                        // Not finding all options will happen on elastic search, 
                        // as the facetsFromItemSearch will not be ready yet
                        if (!this.isUsingElasticSearch)
                            this.$console.log("Looking for terms that are not in the options list... ");

                        // let route = '';
                        
                        // if (this.collectionId == 'default')
                        //     route = '/facets/' + this.metadatumId +`?term_id=${selected}&fetch_only=name,id`;
                        // else
                        //     route = '/collection/'+ this.collectionId +'/facets/' + this.metadatumId +`?term_id=${selected}&fetch_only=name,id`;
                        
                        // axios.get(route)
                        //     .then( res => {
                        //         if(!res || !res.data || !res.data.values){
                        //             return false;
                        //         }

                        //         let existingLabelIndex = onlyLabels.findIndex(aLabel => aLabel == res.data.values[0].label)

                        //         if (existingLabelIndex < 0) {
                        //             onlyLabels.push(res.data.values[0].label);
                        //             this.options.push({
                        //                 isChild: true,
                        //                 label: res.data.values[0].label,
                        //                 value: res.data.values[0].value
                        //             });
                        //         } else {  
                        //             this.$set(onlyLabels, onlyLabels.push(res.data.values[0].label), existingLabelIndex);
                        //             this.$set(this.options, {
                        //                     isChild: true,
                        //                     label: res.data.values[0].label,
                        //                     value: res.data.values[0].value
                        //                 }
                        //             , existingLabelIndex); 
                        //         }
                        //     })
                        //     .catch(error => {
                        //         this.$console.log(error);
                        //     });
                    }
                }

                this.$emit('sendValuesToTags', { label: onlyLabels, taxonomy: this.taxonomy, value: this.selected });
            },
            onSelect(){
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
                    component: CheckboxRadioModal,
                    props: {
                        parent: parent,
                        filter: this.filter,
                        taxonomy_id: this.taxonomyId,
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
                        } 
                    },
                    width: 'calc(100% - 16.6666%)',
                    trapFocus: true
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
            },
            updatesIsLoading(isLoadingOptions) {
                this.isLoadingOptions = isLoadingOptions;
            }
        },
        beforeDestroy() {
            
            // Cancels previous Request
            if (this.getOptionsValuesCancel != undefined)
                this.getOptionsValuesCancel.cancel('Facet search Canceled.');
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
