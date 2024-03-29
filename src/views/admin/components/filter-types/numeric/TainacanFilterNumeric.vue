<template>
    <div class="numeric-filter-container">
        <b-dropdown
                :mobile-modal="true"
                aria-role="list"
                trap-focus
                @update:model-value="($event) => { resetPage(); onChangeComparator($event) }">
            <template #trigger>
                <button
                        :aria-label="$i18n.get('label_comparator')"
                        class="button is-white">
                    <span class="icon is-small">
                        <i v-html="comparatorSymbol" />
                    </span>
                    <span class="icon">
                        <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-arrowdown" />
                    </span>
                </button>
            </template>
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
                v-model="value"
                :aria-labelledby="'filter-label-id-' + filter.id"
                :aria-minus-label="$i18n.get('label_decrease')"
                :aria-plus-label="$i18n.get('label_increase')"
                size="is-small"
                :step="Number(filterTypeOptions.step)"
                @update:model-value="($event) => { resetPage($event); emit($event); }" />
    </div>
</template>

<script>
    import { filterTypeMixin } from '../../../js/filter-types-mixin';

    export default {
        mixins: [
            filterTypeMixin
        ],
        emits: [
            'input',
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
            'query': {
                handler() {
                    this.updateSelectedValues();
                },
                deep: true
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
                
                if ( index >= 0) {
                    let metadata = this.query.metaquery[ index ];
                    
                    if ( metadata.value && metadata.value.length > 0)
                        this.value = Array.isArray(metadata.value) ? Number(metadata.value[0]) : Number(metadata.value);

                    if ( metadata.compare)
                        this.comparator = metadata.compare;

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
        height: auto;
        align-items: stretch;

        @media screen and (min-width: 769px) and (max-width: 1500px) {
            flex-wrap: wrap;
            align-items: center;
            height: 60px;
        }
        
        .dropdown {
            width: auto;
            flex-grow: 2;

            .dropdown-trigger button {
                padding: 2px 0.5em 2px 0.5em !important;
                height: auto !important;
                font-size: 1em !important;
                min-height: 100%;
                
                i:not(.tainacan-icon-arrowdown) {
                    font-size: 1.25em;
                    font-style: normal;
                    color: var(--tainacan-info-color);
                }
            }
        }

        .b-numberinput.is-grouped {
            flex-grow: 1;
        }
    }

</style>