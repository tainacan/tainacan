<template>
    <div 
            :style="{ 'height': isLoading ? (Number(filter.max_options)*28) + 'px' : 'auto' }"
            :class="{ 'skeleton': isLoading }"
            class="block">
        <!-- <span 
                v-if="isLoading"
                style="width: 100%"
                class="icon has-text-centered loading-icon">
            <div class="control has-icons-right is-loading is-clearfix" />
        </span> -->
        <div
                v-for="(option, index) in options.slice(0, filter.max_options)"
                v-if="!isLoading"
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
                v-if="!isLoading && options.length != undefined && options.length <= 0"
                class="no-options-placeholder">
            {{ $i18n.get('info_no_options_avialable_filtering') }}
        </p>
    </div>
</template>

<script>
    import qs from 'qs';
    import { tainacan as axios } from '../../../js/axios/axios';
    import { mapGetters } from 'vuex';
    import CheckboxRadioModal from '../../../admin/components/other/checkbox-radio-modal.vue';

    export default {
        created(){
            this.collection = ( this.collection_id ) ? this.collection_id : this.filter.collection_id;
            this.metadatum = ( this.metadatum_id ) ? this.metadatum_id : this.filter.metadatum.metadatum_id ;
            this.type = this.filter.metadatum.metadata_type;

            this.loadOptions();
            this.$eventBusSearch.$on('removeFromFilterTag', this.cleanSearchFromTag);

            if (this.isUsingElasticSearch)
                this.$eventBusSearch.$on('isLoadingItems', this.updatesIsLoading);
            
        },    
        mounted(){
            // We listen to event, but reload event if hasFiltered is negative, as 
            // an empty query also demands filters reloading.
            this.$eventBusSearch.$on('hasFiltered', () => {
                if (typeof this.loadOptions == "function")
                    this.loadOptions(true);
            });
        },        
        data(){
            return {
                isLoading: true,
                options: [],
                type: '',
                collection: '',
                metadatum: '',
                selected: [],
                taxonomy: '',
                taxonomy_id: Number,
                isUsingElasticSearch: tainacan_plugin.wp_elasticpress == "1" ? true : false
            }
        },
        props: {
            filter: {
                type: Object // concentrate all attributes metadatum id and type
            },
            metadatum_id: [Number], // not required, but overrides the filter metadatum id if is set
            collection_id: [Number], // not required, but overrides the filter metadatum id if is set
            labelId: '',
            query: {
                type: Object // concentrate all attributes metadatum id and type
            }
        },
        watch: {
            selected: function(){
                //this.selected = val;
                this.onSelect();
            },
            facetsFromItemSearch() {
                if (this.isUsingElasticSearch)
                    this.loadOptions();
            }
        },    
        computed: {
            facetsFromItemSearch() {
                return this.getFacets();
            }
        },
        methods: {
            ...mapGetters('search', [
                'getFacets'
            ]),
            loadOptions(skipSelected) {
                if (!this.isUsingElasticSearch) {

                    this.isLoading = true;
                    let query_items = { 'current_query': this.query };

                    let route = '';
                    
                    if(this.collection == 'filter_in_repository')
                        route = `/facets/${this.metadatum}?getSelected=1&order=asc&parent=0&number=${this.filter.max_options}&` + qs.stringify(query_items);
                    else
                        route = `/collection/${this.collection}/facets/${this.metadatum}?getSelected=1&order=asc&parent=0&number=${this.filter.max_options}&` + qs.stringify(query_items);

                    this.options = [];

                    axios.get(route)
                        .then( res => {
                            this.prepareOptionsForTaxonomy(res.data.values ? res.data.values : res.data, skipSelected);
                            this.isLoading = false;
                        })
                        .catch(error => {
                            this.$console.log(error);
                            this.isLoading = false;
                        });
                } else {

                    for (const facet in this.facetsFromItemSearch) {
                        if (facet == this.filter.id) {
                            if (Array.isArray(this.facetsFromItemSearch[facet]))
                                this.prepareOptionsForTaxonomy(this.facetsFromItemSearch[facet], skipSelected);
                            else
                                this.prepareOptionsForTaxonomy(Object.values(this.facetsFromItemSearch[facet]), skipSelected);
                        }    
                    }

                }
            },
            selectedValues(){
                
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
            },
            onSelect(){
                this.$emit('input', {
                    filter: 'checkbox',
                    taxonomy: this.taxonomy,
                    compare: 'IN',
                    metadatum_id: this.metadatum,
                    collection_id: this.collection,
                    terms: this.selected
                });
                
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
                        
                        // if (this.collection == 'filter_in_repository')
                        //     route = '/facets/' + this.metadatum +`?term_id=${selected}&fetch_only=name,id`;
                        // else
                        //     route = '/collection/'+ this.collection +'/facets/' + this.metadatum +`?term_id=${selected}&fetch_only=name,id`;
                        
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

                this.$eventBusSearch.$emit("sendValuesToTags", {
                    filterId: this.filter.id,
                    value: onlyLabels
                });
            },
            openCheckboxModal(parent) {
                this.$modal.open({
                    parent: this,
                    component: CheckboxRadioModal,
                    props: {
                        parent: parent,
                        filter: this.filter,
                        taxonomy_id: this.taxonomy_id,
                        selected: this.selected,
                        metadatum_id: this.metadatum,
                        taxonomy: this.taxonomy,
                        collection_id: this.collection,
                        isTaxonomy: true,
                        query: this.query
                    },                    
                    events: {
                        appliedCheckBoxModal: () => {
                            this.loadOptions();
                        } 
                    },
                    width: 'calc(100% - 8.333333333%)',
                });
            },
            cleanSearchFromTag(filterTag) {
                if (filterTag.filterId == this.filter.id) {

                    let selectedOption = this.options.find(option => option.label == filterTag.singleValue);

                    if (selectedOption) {
                    
                        let selectedIndex = this.selected.findIndex(option => option == selectedOption.value);
                        if (selectedIndex >= 0) {

                            this.selected.splice(selectedIndex, 1); 

                            this.$emit('input', {
                                filter: 'checkbox',
                                compare: 'IN',
                                taxonomy: this.taxonomy,
                                metadatum_id: this.metadatum,
                                collection_id: ( this.collection_id ) ? this.collection_id : this.filter.collection_id,
                                terms: this.selected
                            });

                            this.$eventBusSearch.$emit( 'sendValuesToTags', {
                                filterId: this.filter.id,
                                value: this.selected
                            });

                            this.selectedValues();
                        }
                    }
                }
            },
            prepareOptionsForTaxonomy(items, skipSelected) {

                if (items[0] != undefined) {
                    this.taxonomy = items[0].taxonomy;
                    this.taxonomy_id = items[0].taxonomy_id;
                }

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

                if (skipSelected == undefined || skipSelected == false) {
                    this.selectedValues();
                }
            },
            updatesIsLoading(isLoading) {
                this.isLoading = isLoading;
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
