<template>
    <div class="date-filter-container">
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
                &#62;&nbsp; {{ $i18n.get('after') }}
            </b-dropdown-item>
            <b-dropdown-item
                    role="button"
                    :class="{ 'is-active': comparator == '>=' }"
                    :value="'>='"
                    aria-role="listitem">
                &#8805;&nbsp; {{ $i18n.get('after_or_on_day') }}
            </b-dropdown-item>
            <b-dropdown-item
                    role="button"
                    :class="{ 'is-active': comparator == '<' }"
                    :value="'<'"
                    aria-role="listitem">
                &#60;&nbsp; {{ $i18n.get('before') }}
            </b-dropdown-item>
            <b-dropdown-item
                    role="button"
                    :class="{ 'is-active': comparator == '<=' }"
                    :value="'<='"
                    aria-role="listitem">
                &#8804;&nbsp; {{ $i18n.get('before_or_on_day') }}
            </b-dropdown-item>
        </b-dropdown>
        <!-- <b-numberinput 
                v-if="filterTypeOptions.type == 'year'"
                :placeholder="$i18n.get('instruction_type_value_year')"
                :aria-labelledby="'filter-label-id-' + filter.id"
                :aria-minus-label="$i18n.get('label_decrease')"
                :aria-plus-label="$i18n.get('label_increase')"
                size="is-small"
                step="1"
                @update:model-value="emitOnlyYear($event)"
                v-model="yearsOnlyValue"/> -->
        <b-datepicker
                v-model="value"
                position="is-bottom-right"
                :aria-labelledby="'filter-label-id-' + filter.id"
                :placeholder="$i18n.get('instruction_select_a_date')"
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
                <!-- filterTypeOptions FOR TYPE 
                    v-else
                    :type="filterTypeOptions.type == 'month' ? 'month' : null" 
                    :placeholder="filterTypeOptions.type == 'month' ? $i18n.get('instruction_select_a_date') : $i18n.get('instruction_select_a_month')"
                --> 
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
        data(){
            return {
                value: null,
                comparator: '=', // =, !=, >, >=, <, <=
            }
        },
        computed: {
            yearsOnlyValue() {
                return this.value && typeof this.value.getUTCFullYear === 'function' ? this.value.getUTCFullYear() : null
            },
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
                deep: true,
            },
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

        @media screen and (min-width: 769px) and (max-width: 1500px) {
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