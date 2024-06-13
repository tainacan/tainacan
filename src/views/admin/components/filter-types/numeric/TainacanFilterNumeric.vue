<template>
    <div class="numeric-filter-container">
        <b-dropdown
                v-if="filterTypeOptions.comparators.length > 1"
                :mobile-modal="true"
                aria-role="list"
                trap-focus
                @update:model-value="($event) => { resetPage(); onChangeComparator($event) }">
            <template #trigger>
                <button
                        :aria-label="$i18n.get('label_comparator')"
                        class="button is-white">
                    <span class="icon is-small">
                        <i v-html="comparatorsObject[comparator].symbol" />
                    </span>
                    <span class="icon">
                        <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-arrowdown" />
                    </span>
                </button>
            </template>
            <template
                    v-for="(comparatorObject, comparatorKey) in comparatorsObject"
                    :key="comparatorKey">
                <b-dropdown-item
                        v-if="comparatorObject.enabled == 'yes'"
                        role="button"
                        :class="{ 'is-active': comparator == comparatorKey }"
                        :value="comparatorKey"
                        aria-role="listitem"
                        v-html="comparatorObject.symbol + '&nbsp;' + comparatorObject.label" />
            </template>
        </b-dropdown>
        <b-numberinput
                v-model="value"
                :aria-labelledby="'filter-label-id-' + filter.id"
                :aria-minus-label="$i18n.get('label_decrease')"
                :aria-plus-label="$i18n.get('label_increase')"
                :placeholder="filter.placeholder ? filter.placeholder : ''"
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
        watch: {
            'query': {
                handler() {
                    this.updateSelectedValues();
                },
                deep: true
            }
        },
        created() {
            this.comparatorsObject = {
                '=': {
                    symbol: '&#61;',
                    label: this.$i18n.get('is_equal_to'),
                    enabled: this.filterTypeOptions.comparators.indexOf('=') < 0 ? 'no' : 'yes'
                },
                '!=': {
                    symbol: '&#8800;',
                    label: this.$i18n.get('is_not_equal_to'),
                    enabled: this.filterTypeOptions.comparators.indexOf('!=') < 0 ? 'no' : 'yes'
                },
                '>': {
                    symbol: '&#62;',
                    label: this.$i18n.get('greater_than'),
                    enabled: this.filterTypeOptions.comparators.indexOf('>') < 0 ? 'no' : 'yes'
                },
                '>=': {
                    symbol: '&#8805;',
                    label: this.$i18n.get('greater_than_or_equal_to'),
                    enabled: this.filterTypeOptions.comparators.indexOf('>=') < 0 ? 'no' : 'yes'
                },
                '<': {
                    symbol: '&#60;',
                    label: this.$i18n.get('less_than'),
                    enabled: this.filterTypeOptions.comparators.indexOf('<') < 0 ? 'no' : 'yes'
                },
                '<=': {
                    symbol: '&#8804;',
                    label: this.$i18n.get('less_than_or_equal_to'),
                    enabled: this.filterTypeOptions.comparators.indexOf('<=') < 0 ? 'no' : 'yes'
                },
            };
            this.comparator = this.filterTypeOptions.comparators[0];
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
            align-items: stretch;
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