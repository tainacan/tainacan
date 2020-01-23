<template>
    <div class="numeric-filter-container">
         <b-dropdown
                :mobile-modal="true"
                @input="onChangeComparator($event)"
                aria-role="list"
                trap-focus>
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
                :aria-labelledby="'filter-label-id-' + filter.id"
                size="is-small"
                :step="Number(filterTypeOptions.step)"
                @input="emit()"
                v-model="value"/>
    </div>
</template>

<script>
    import { filterTypeMixin } from '../../../js/filter-types-mixin';

    export default {
        mixins: [
            filterTypeMixin
        ],
        data(){
            return {
                value: null,
                filterTypeOptions: [],
                comparator: '=' // =, !=, >, >=, <, <=
            }
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
        watch: {
            'query.metaquery'() {
                this.updateSelectedValues();
            },
            'query.taxquery'() {
                this.updateSelectedValues();
            }
        },
        mounted() {
            this.updateSelectedValues();
        },
        methods: {
            updateSelectedValues(){
                if ( !this.query || !this.query.metaquery || !Array.isArray( this.query.metaquery ) )
                    return false;

                let index = this.query.metaquery.findIndex(newMetadatum => newMetadatum.key == this.metadatumId );
                
                if ( index >= 0){
                    let metadata = this.query.metaquery[ index ];
                    
                    if ( metadata.value && metadata.value.length > 0)
                        this.value = Array.isArray(metadata.value) ? Number(metadata.value[0]) : Number(metadata.value);

                    if ( metadata.compare)
                        this.comparator = metadata.compare;

                    if (this.value != undefined)
                        this.$emit('sendValuesToTags', { label: this.comparator + ' ' + this.value, value: this.value });

                } else {
                    this.value = null;
                }

            },
            // emit the operation for listeners
            emit() {

                if ( this.value === null || this.value === '')
                    return;
                    
                this.$emit('input', {
                    filter: 'numeric',
                    compare: this.comparator,
                    metadatum_id: this.metadatumId,
                    collection_id: this.collectionId,
                    value: this.value,
                    type: 'NUMERIC'
                });

                this.$emit('sendValuesToTags', { label: this.comparator + ' ' + this.value, value: this.value });
                
            },
            onChangeComparator(newComparator) {
                this.comparator = newComparator;
                this.emit();
            }
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