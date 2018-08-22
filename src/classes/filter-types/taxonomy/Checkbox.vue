<template>
    <div class="block">
        <div
                v-for="(option, index) in getOptions()"
                :key="index"
                :value="index"
                class="control">
            <b-checkbox
                    v-model="selected"
                    :native-value="option.value"
                    v-if="!option.isChild"
            >{{ option.label }}</b-checkbox>
            <div
                    class="see-more-container"
                    v-if="option.seeMoreLink && index == options.length-1"
                    @click="openCheckboxModal(option.parent)"
                    v-html="option.seeMoreLink"/>
        </div>
    </div>
</template>

<script>
    import { tainacan as axios } from '../../../js/axios/axios';
    import CheckboxFilterModal from '../../../admin/components/other/checkbox-filter-modal.vue';

    export default {
        created(){
            this.collection = ( this.collection_id ) ? this.collection_id : this.filter.collection_id;
            this.metadatum = ( this.metadatum_id ) ? this.metadatum_id : this.filter.metadatum.metadatum_id ;
            this.type = ( this.filter_type ) ? this.filter_type : this.filter.metadatum.metadata_type;
            this.loadOptions();

            this.$eventBusSearch.$on('removeFromFilterTag', (filterTag) => {
                if (filterTag.filterId == this.filter.id) {

                    let selectedOption = this.options.find(option => option.name == filterTag.singleValue);
                    if(selectedOption) {
                        
                        let selectedIndex = this.selected.findIndex(option => option == selectedOption.id);
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
            });

            this.$root.$on('appliedCheckBoxModal', (labels) => {
                if(labels.length){
                    this.selectedValues();
                }
            });
        },
        data(){
            return {
                isLoading: false,
                options: [],
                type: '',
                collection: '',
                metadatum: '',
                selected: [],
                taxonomy: '',
                taxonomy_id: Number,
            }
        },
        props: {
            filter: {
                type: Object // concentrate all attributes metadatum id and type
            },
            metadatum_id: [Number], // not required, but overrides the filter metadatum id if is set
            collection_id: [Number], // not required, but overrides the filter metadatum id if is set
            filter_type: [String],  // not required, but overrides the filter metadatum type if is set
            id: '',
            query: {
                type: Object // concentrate all attributes metadatum id and type
            }
        },
        watch: {
            selected: function(val){
                this.selected = val;
                this.onSelect();
            }
        },
        methods: {
            loadOptions(){
                this.isLoading = true;

                axios.get('/collection/'+ this.collection +'/facets/' + this.metadatum + `?hideempty=0&order=asc&parent=0&number=${this.filter.max_options}`)
                    .then( res => {

                        for (let item of res.data) {
                            this.taxonomy = item.taxonomy;
                            this.taxonomy_id = item.taxonomy_id;
                            this.options.push(item);
                        }

                        this.isLoading = false;
                        this.selectedValues();
                    })
                    .catch(error => {
                        this.$console.log(error);
                        this.isLoading = false;
                    });
            },
            getOptions(){
                if ( this.options ){
                    let hasChildren = false;

                    for( let term of this.options ){
                        if(term.total_children > 0){
                            hasChildren = true;
                            break;
                        }
                    }

                    if (this.filter.max_options && (this.options.length >= this.filter.max_options || hasChildren)) {
                        if(this.options.length > this.filter.max_options){
                            this.options.splice(this.filter.max_options);
                        }

                        let seeMoreLink = `<a style="font-size: 12px;"> ${ this.$i18n.get('label_view_all') } </a>`;

                        if(this.options.length === this.filter.max_options){
                            this.options[this.filter.max_options-1].seeMoreLink = seeMoreLink;
                        } else {
                            this.options[this.options.length-1].seeMoreLink = seeMoreLink;
                        }
                    }
                }

                return this.options;
            },
            selectedValues(){
                
                if ( !this.query || !this.query.taxquery || !Array.isArray( this.query.taxquery ) )
                    return false;

                let index = this.query.taxquery.findIndex(newMetadatum => newMetadatum.taxonomy === this.taxonomy );
                if ( index >= 0){
                    let metadata = this.query.taxquery[ index ];
                    this.selected = metadata.terms;
                } else {
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
                for(let selected of this.selected) {
                    let valueIndex = this.options.findIndex(option => option.value == selected );

                    if (valueIndex >= 0) {
                        onlyLabels.push(this.options[valueIndex].label)
                    } else {
                        axios.get('/collection/'+ this.collection +'/facets/' + this.metadatum +`?term_id=${selected}&fetch_only[0]=name&fetch_only[1]=id`)
                            .then( res => {
                                
                                if(!res || !res.data){
                                    return false;
                                }

                                onlyLabels.push(res.data[0].label);
                                this.options.push({
                                    isChild: true,
                                    name: res.data[0].label,
                                    id: res.data[0].value
                                })
                            })
                            .catch(error => {
                                this.$console.log(error);
                            });
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
                    component: CheckboxFilterModal,
                    props: {
                        parent: parent,
                        filter: this.filter,
                        taxonomy_id: this.taxonomy_id,
                        selected: this.selected,
                        metadatum_id: this.metadatum,
                        taxonomy: this.taxonomy,
                        collection_id: this.collection,
                        isTaxonomy: true,
                    },
                    width: 'calc(100% - 8.333333333%)',
                });
            }
        }
    }
</script>

<style lang="scss" scoped>
    .see-more-container {
        display: flex;
        padding-left: 18px;
    }
</style>
