<template>
    <div class="block">
        <span 
                v-if="isLoading"
                style="width: 100%"
                class="icon has-text-centered loading-icon">
            <div class="control has-icons-right is-loading is-clearfix" />
        </span>
        <div
                v-for="(option, index) in options.slice(0, filter.max_options)"
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
                    v-if="option.seeMoreLink && index == options.slice(0, filter.max_options).length - 1"
                    @click="openCheckboxModal(option.parent)"
                    v-html="option.seeMoreLink"/>
        </div>
    </div>
</template>

<script>
    import qs from 'qs';
    import { tainacan as axios } from '../../../js/axios/axios';
    import CheckboxRadioModal from '../../../admin/components/other/checkbox-radio-modal.vue';

    export default {
        created(){
            this.collection = ( this.collection_id ) ? this.collection_id : this.filter.collection_id;
            this.metadatum = ( this.metadatum_id ) ? this.metadatum_id : this.filter.metadatum.metadatum_id ;
            this.type = ( this.filter_type ) ? this.filter_type : this.filter.metadatum.metadata_type;
            this.loadOptions();

            this.$eventBusSearch.$on('removeFromFilterTag', this.cleanSearchFromTag);
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
            selected: function(){
                //this.selected = val;
                this.onSelect();
            }
        },
        methods: {
            loadOptions(){
                this.isLoading = true;
                let query_items = { 'current_query': this.query };

                let route = `/collection/${this.collection}/facets/${this.metadatum}?getSelected=1&hideempty=0&order=asc&parent=0&number=${this.filter.max_options}&` + qs.stringify(query_items);

                if(this.collection == 'filter_in_repository'){
                    route = `/facets/${this.metadatum}?getSelected=1&hideempty=0&order=asc&parent=0&number=${this.filter.max_options}&` + qs.stringify(query_items);
                }

                this.options = [];

                axios.get(route)
                    .then( res => {

                        for (let item of res.data) {
                            this.taxonomy = item.taxonomy;
                            this.taxonomy_id = item.taxonomy_id;
                            this.options.push(item);
                        }

                        if ( this.options ){
                            let hasChildren = false;

                            for( let term of this.options ){
                                if(term.total_children > 0){
                                    hasChildren = true;
                                    break;
                                }
                            }

                            if(this.filter.max_options && (this.options.length >= this.filter.max_options || hasChildren)){
                                if(this.options.length > this.filter.max_options){
                                    this.options.splice(this.filter.max_options);
                                }

                                let seeMoreLink = `<a style="font-size: 0.75rem;"> ${ this.$i18n.get('label_view_all') } </a>`;

                                if(this.options.length === this.filter.max_options){
                                    this.options[this.filter.max_options-1].seeMoreLink = seeMoreLink;
                                } else {
                                    this.options[this.options.length-1].seeMoreLink = seeMoreLink;
                                }
                            }
                        }

                        this.isLoading = false;
                        this.selectedValues();
                    })
                    .catch(error => {
                        this.$console.log(error);
                        this.isLoading = false;
                    });
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
                for(let selected of this.selected) {
                    let valueIndex = this.options.findIndex(option => option.value == selected );

                    if (valueIndex >= 0) {
                        onlyLabels.push(this.options[valueIndex].label)
                    } else {

                        let route = '/collection/'+ this.collection +'/facets/' + this.metadatum +`?term_id=${selected}&fetch_only[0]=name&fetch_only[1]=id`;

                        if(this.collection == 'filter_in_repository'){
                            route = '/facets/' + this.metadatum +`?term_id=${selected}&fetch_only[0]=name&fetch_only[1]=id`
                        }

                        axios.get(route)
                            .then( res => {
                                
                                if(!res || !res.data){
                                    return false;
                                }

                                onlyLabels.push(res.data[0].label);
                                this.options.push({
                                    isChild: true,
                                    label: res.data[0].label,
                                    value: res.data[0].value
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
                        appliedCheckBoxModal: () => this.loadOptions()
                    },
                    width: 'calc(100% - 8.333333333%)',
                });
            },
            cleanSearchFromTag(filterTag) {
                if (filterTag.filterId == this.filter.id) {

                    let selectedOption = this.options.find(option => option.label == filterTag.singleValue);

                    if(selectedOption) {
                    
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
            }
        },
        beforeDestroy() {
            this.$eventBusSearch.$off('removeFromFilterTag', this.cleanSearchFromTags);
        }
    }
</script>

<style lang="scss" scoped>
    .see-more-container {
        display: flex;
        padding-left: 18px;
    }

    .is-loading:after {
        border: 2px solid white !important;
        border-top-color: #dbdbdb !important;
        border-right-color: #dbdbdb !important;
    }
</style>
