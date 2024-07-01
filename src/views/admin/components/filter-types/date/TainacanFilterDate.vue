<template>
    <div class="date-filter-container">
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
        <b-datepicker
                v-model="value"
                position="is-bottom-right"
                :aria-labelledby="'filter-label-id-' + filter.id"
                :placeholder="filter.placeholder ? filter.placeholder : $i18n.get('instruction_select_a_date')"
                editable
                :trap-focus="false"
                :date-formatter="(date) => dateFormatter(date)"
                :date-parser="(date) => dateParser(date)"
                icon="calendar-today"
                :years-range="[-200, 50]"
                :day-names="[
                    $i18n.get('datepicker_short_sunday'),
                    $i18n.get('datepicker_short_monday'),
                    $i18n.get('datepicker_short_tuesday'),
                    $i18n.get('datepicker_short_wednesday'),
                    $i18n.get('datepicker_short_thursday'),
                    $i18n.get('datepicker_short_friday'),
                    $i18n.get('datepicker_short_saturday')
                ]"
                :month-names="[
                    $i18n.get('datepicker_month_january'),
                    $i18n.get('datepicker_month_february'),
                    $i18n.get('datepicker_month_march'),
                    $i18n.get('datepicker_month_april'),
                    $i18n.get('datepicker_month_may'),
                    $i18n.get('datepicker_month_june'),
                    $i18n.get('datepicker_month_july'),
                    $i18n.get('datepicker_month_august'),
                    $i18n.get('datepicker_month_september'),
                    $i18n.get('datepicker_month_october'),
                    $i18n.get('datepicker_month_november'),
                    $i18n.get('datepicker_month_december')
                ]"
                @update:model-value="($event) => { resetPage(); emit($event); }" />
    </div>
</template>

<script>
    import { dateInter } from "../../../js/mixins";
    import { filterTypeMixin } from '../../../js/filter-types-mixin';
    import moment from 'moment';

    export default {
        mixins: [
            dateInter,
            filterTypeMixin
        ],
        emits: [
            'input',
        ],
        data() {
            return {
                value: null,
                comparatorsObject: [],
                comparator: '=', // =, !=, >, >=, <, <=
            }
        },
        computed: {
            yearsOnlyValue() {
                return this.value && typeof this.value.getUTCFullYear === 'function' ? this.value.getUTCFullYear() : null
            }
        },
        watch: {
            'query': {
                handler() {
                    this.updateSelectedValues();
                },
                deep: true,
            },
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
                    label: this.$i18n.get('after'),
                    enabled: this.filterTypeOptions.comparators.indexOf('>') < 0 ? 'no' : 'yes'
                },
                '>=': {
                    symbol: '&#8805;',
                    label: this.$i18n.get('after_or_on_day'),
                    enabled: this.filterTypeOptions.comparators.indexOf('>=') < 0 ? 'no' : 'yes'
                },
                '<': {
                    symbol: '&#60;',
                    label: this.$i18n.get('before'),
                    enabled: this.filterTypeOptions.comparators.indexOf('<') < 0 ? 'no' : 'yes'
                },
                '<=': {
                    symbol: '&#8804;',
                    label: this.$i18n.get('before_or_on_day'),
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

                if ( index >= 0){
                    let metadata = this.query.metaquery[ index ];
                    
                    if ( metadata.compare)
                        this.comparator = metadata.compare;
                    
                    if ( metadata.value && metadata.value.length > 0) {
                        let textValue = Array.isArray(metadata.value) ? metadata.value[0] : metadata.value;

                        while (textValue.split('-')[0].length < 4)
                            textValue = '0' + textValue;
                        
                        const dateValue = new Date(textValue.replace(/-/g, '/'));
                        this.value = moment(dateValue, moment.ISO_8601).toDate();
                    }

                } else {
                    this.value = null;
                }
            },
            dateFormatter(dateObject) {
                return moment(dateObject, moment.ISO_8601).format(this.dateFormat);
            },
            dateParser(dateString) { 
                return moment(dateString, this.dateFormat).toDate(); 
            },
            emitOnlyYear(year) {
                this.value = new Date(year,0,1);
                
                this.value.setUTCDate(1);
                this.value.setUTCMonth(0);
                this.value.setFullYear(year);

                this.emit();
            },
            // emit the operation for listeners
            emit() {
                if ( this.value == undefined || this.value == null || this.value === '')
                    this.value = new Date();

                let valueQuery = (this.value.getUTCFullYear()) + '-' +
                          ('00' + (this.value.getUTCMonth() + 1)).slice(-2) + '-' +
                          ('00' + this.value.getUTCDate()).slice(-2);

                while (valueQuery.split('-')[0].length < 4)
                    valueQuery = '0' + valueQuery;
                    
                this.$emit('input', {
                    filter: 'date',
                    type: 'DATE',
                    compare: this.comparator,
                    metadatum_id: this.metadatumId,
                    collection_id: this.collectionId,
                    value: valueQuery
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

    .date-filter-container {
        display: flex;
        height: auto;
        align-items: stretch;

        @supports not (contain: inline-size) {
            @media screen and (min-width: 769px) and (max-width: 1500px) {
                flex-wrap: wrap;
                height: 60px;
            }
        }

        @container filterscomponentslist (max-width: 170px) {
            flex-wrap: wrap;
            height: 60px;
        }
        
        .dropdown {
            width: auto;
            flex-grow: 2;

            .dropdown-trigger button {
                padding: 2px 0.5em !important;
                height: auto !important;
                font-size: 1em !important;
                min-height: 100%;

                i:not(.tainacan-icon-arrowdown) {
                    margin-top: -3px;
                    font-size: 1.25em;
                    font-style: normal;
                    color: var(--tainacan-info-color);
                }
            }
        }
    }

</style>