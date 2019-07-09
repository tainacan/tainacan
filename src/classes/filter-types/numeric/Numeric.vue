<template>
    <div class="numeric-filter-container">
         <b-dropdown
                :mobile-modal="true"
                @input="onChangeComparator($event)"
                aria-role="list">
            <button
                    :aria-label="$i18n.get('label_comparator')"
                    class="button is-white"
                    slot="trigger">
                <span class="icon is-small">
                    <i v-html="comparatorSymbol" />
                </span>
                <span class="icon">
                    <i class="tainacan-icon tainacan-icon-20px tainacan-icon-arrowdown" />
                </span>
            </button>
            <b-dropdown-item
                    role="button"
                    :class="{ 'is-active': comparator == '=' }"
                    :value="'='"
                    aria-role="listitem">
                &#61;&nbsp; {{ $i18n.get('is_equal_to') }}
            </b-dropdown-item>
            <b-dropdown-item
                    role="button"
                    :class="{ 'is-active': comparator == '!=' }"
                    :value="'!='"
                    aria-role="listitem">
                &#8800;&nbsp; {{ $i18n.get('is_not_equal_to') }}
            </b-dropdown-item>
            <b-dropdown-item
                    role="button"
                    :class="{ 'is-active': comparator == '>' }"
                    :value="'>'"
                    aria-role="listitem">
                &#62;&nbsp; {{ $i18n.get('greater_than') }}
            </b-dropdown-item>
            <b-dropdown-item
                    role="button"
                    :class="{ 'is-active': comparator == '>=' }"
                    :value="'>='"
                    aria-role="listitem">
                &#8805;&nbsp; {{ $i18n.get('greater_than_or_equal_to') }}
            </b-dropdown-item>
            <b-dropdown-item
                    role="button"
                    :class="{ 'is-active': comparator == '<' }"
                    :value="'<'"
                    aria-role="listitem">
                &#60;&nbsp; {{ $i18n.get('less_than') }}
            </b-dropdown-item>
            <b-dropdown-item
                    role="button"
                    :class="{ 'is-active': comparator == '<=' }"
                    :value="'<='"
                    aria-role="listitem">
                &#8804;&nbsp; {{ $i18n.get('less_than_or_equal_to') }}
            </b-dropdown-item>
        </b-dropdown>

        <b-numberinput
                :aria-labelledby="labelId"
                size="is-small"
                :step="Number(options.step)"
                @input="emit()"
                v-model="value"/>
    </div>
</template>

<script>
    import { tainacan as axios } from '../../../js/axios/axios';
    import { wpAjax } from "../../../admin/js/mixins";

    export default {
        mixins: [ wpAjax ],
        created() {
            this.collection = ( this.collection_id ) ? this.collection_id : this.filter.collection_id;
            this.metadatum = ( this.metadatum_id ) ? this.metadatum_id : (typeof this.filter.metadatum.metadatum_id == 'object' ? this.filter.metadatum.metadatum_id.metadatum_id : this.filter.metadatum.metadatum_id);
            this.options = this.filter.filter_type_options;

            let in_route = '/collection/' + this.collection + '/metadata/' +  this.metadatum;

            if (this.isRepositoryLevel || this.collection == 'filter_in_repository')
                in_route = '/metadata/'+ this.metadatum;
        
            axios.get(in_route)
                .then( res => {
                    let result = res.data;
                    if ( result && result.metadata_type ){
                        this.metadatum_object = result;
                        this.selectedValues();
                    }
                })
                .catch(error => {
                    this.$console.log(error);
                });
            this.$eventBusSearch.$on('removeFromFilterTag', this.cleanSearchFromTags);
        },
        mounted() {
            this.selectedValues();
        },
        data(){
            return {
                value: null,
                clear: false,
                options: [],
                collection: '',
                metadatum: '',
                metadatum_object: {},
                comparator: '=' // =, !=, >, >=, <, <=
            }
        },
        props: {
            filter: {
                type: Object // concentrate all attributes metadatum id and type
            },
            metadatum_id: [Number], // not required, but overrides the filter metadatum id if is set
            collection_id: [Number], // not required, but overrides the filter metadatum id if is set
            labelId: '',
            query: Object,
            isRepositoryLevel: Boolean,
        },
        computed: {
            comparatorSymbol() {
                switch(this.comparator) {
                    case '=': return '&#61;';
                    case '!=': return '&#8800;';
                    case '>': return '&#62;';
                    case '>=': return '&#8805;';
                    case '<': return '&#60;';
                    case '<=': return '&#8804;';
                    default: return '';
                }
            }
        },
        methods: {
            selectedValues(){
                if ( !this.query || !this.query.metaquery || !Array.isArray( this.query.metaquery ) )
                    return false;

                let index = this.query.metaquery.findIndex(newMetadatum => newMetadatum.key === this.metadatum );
                
                if ( index >= 0){
                    let metadata = this.query.metaquery[ index ];
                    
                    if ( metadata.value && metadata.value.length > 0)
                        this.value = Array.isArray(metadata.value) ? Number(metadata.value[0]) : Number(metadata.value);

                    if ( metadata.compare)
                        this.comparator = metadata.compare;

                    if (this.value != undefined) {
                        this.$eventBusSearch.$emit( 'sendValuesToTags', {
                            filterId: this.filter.id,
                            value: this.comparator + ' ' + this.value
                        });
                    }

                } else {
                    return false;
                }

            },
            cleanSearchFromTags(filterTag) {
                if (filterTag.filterId == this.filter.id)
                    this.clearSearch();
            },
            clearSearch(){

                this.clear = true;

                this.$emit('input', {
                    filter: 'numeric',
                    compare: this.comparator,
                    metadatum_id: this.metadatum,
                    collection_id: ( this.collection_id ) ? this.collection_id : this.filter.collection_id,
                    value: ''
                });

                this.value = null;
            },
            // emit the operation for listeners
            emit() {

                if ( this.value === null || this.value === '')
                    return;

                this.$emit('input', {
                    filter: 'numeric',
                    compare: this.comparator,
                    metadatum_id: this.metadatum,
                    collection_id: ( this.collection_id ) ? this.collection_id : this.filter.collection_id,
                    value: this.value
                });

                this.$eventBusSearch.$emit( 'sendValuesToTags', {
                    filterId: this.filter.id,
                    value: this.comparator + ' ' + this.value
                });
                
            },
            onChangeComparator(newComparator) {
                this.comparator = newComparator;
                this.emit();
            }
        },
        beforeDestroy() {
            this.$eventBusSearch.$off('removeFromFilterTag', this.cleanSearchFromTags);
        }
    }
</script>

<style lang="scss" scoped>

    .numeric-filter-container {
        display: flex;
        height: 30px;

        @media screen and (min-width: 769px) and (max-width: 1500px) {
            flex-wrap: wrap;
            height: 60px;
        }
        
        .dropdown {
            width: auto;
            flex-grow: 2;

            .dropdown-trigger button {
                padding: 0 0.5rem !important;
                height: 30px !important;

                i:not(.tainacan-icon-arrowdown) {
                    margin-top: -3px;
                    font-size: 1.25rem;
                    font-style: normal;
                    color: #555758;
                }
            }
        }

        .b-numberinput.is-grouped {
            flex-grow: 1;
        }
    }

</style>