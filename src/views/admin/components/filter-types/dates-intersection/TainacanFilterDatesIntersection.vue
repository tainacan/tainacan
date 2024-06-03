<template>
    <div>
        <b-datepicker
                v-model="dateInit"
                :aria-labelledby="'filter-label-id-' + filter.id"
                :placeholder="$i18n.get('instruction_select_a_date')"
                editable
                :trap-focus="false"
                :date-formatter="(date) => dateFormatter(date)"
                :date-parser="(date) => dateParser(date)"
                icon="calendar-today"
                :years-range="[-200, 100]"
                :day-names="[
                    $i18n.get('datepicker_short_sunday'),
                    $i18n.get('datepicker_short_monday'),
                    $i18n.get('datepicker_short_tuesday'),
                    $i18n.get('datepicker_short_wednesday'),
                    $i18n.get('datepicker_short_thursday'),
                    $i18n.get('datepicker_short_friday'),
                    $i18n.get('datepicker_short_saturday'),
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
                @focus="isTouched = true"
                @update:model-value="($event) => { resetPage(); validadeValues($event) }" />
        <p 
                v-if="filterTypeOptions.accept_date_interval === 'yes'"
                style="font-size: 0.75em; margin-bottom: 0.125em;"
                class="has-text-centered is-marginless">
            {{ $i18n.get('label_until') }}
        </p>  
        <b-datepicker
                v-if="filterTypeOptions.accept_date_interval === 'yes'"
                v-model="dateEnd"
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
                    $i18n.get('datepicker_short_saturday'),
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
                @update:model-value="validadeValues()"
                @focus="isTouched = true" />
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
                dateInit: undefined,
                dateEnd: undefined,
                isTouched: false
            }
        },
        watch: {
            isTouched( val ){
              if ( val && this.dateInit === null)
                  this.dateInit = new Date();

              if ( val && this.dateEnd === null)
                  this.dateEnd =  new Date();
            },
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
            // only validate if the first value is higher than first
            validadeValues: _.debounce( function (){
               
                if ( this.dateInit === undefined )
                    this.dateInit = new Date();

                if ( this.dateEnd === undefined )
                    this.dateEnd = new Date();

                if ( this.filterTypeOptions.accept_date_interval === 'yes' && this.dateInit > this.dateEnd ) {
                    this.showErrorMessage();
                    return
                }
               
                this.emit();
            }, 800),
            showErrorMessage(){
                if ( !this.isTouched ) return false;

                this.$buefy.toast.open({
                    duration: 3000,
                    message: this.$i18n.get('info_error_first_value_greater'),
                    position: 'is-bottom',
                    type: 'is-danger'
                })
            },
            dateFormatter(dateObject){ 
                return moment(dateObject, moment.ISO_8601).format(this.dateFormat);
            },
            dateParser(dateString){ 
                return moment(dateString, this.dateFormat).toDate(); 
            },
            updateSelectedValues(){
                if ( !this.query || !this.query.metaquery || !Array.isArray( this.query.metaquery ) )
                    return false;

                let index = this.query.metaquery.findIndex(newMetadatum => newMetadatum.key == this.metadatumId);

                if (index >= 0) {
                    let metadata = this.query.metaquery[ index ];
                    if (metadata.value && metadata.value.length > 0) {
                        const dateValueInit = new Date(metadata.value[0].replace(/-/g, '/'));
                        this.dateInit = moment(dateValueInit, moment.ISO_8601).toDate();
                        const dateValueEnd = new Date(metadata.value[1].replace(/-/g, '/'));
                        this.dateEnd = moment(dateValueEnd, moment.ISO_8601).toDate();
                    }
                } else {
                    this.dateInit = null;
                    this.dateEnd = null; 
                }
            },
            // emit the operation for listeners
            emit() {
                let values = [];
                
                if (this.dateInit === null && this.dateEnd === null) {
                    values = [];
                } else {
                    let dateInit = this.dateInit.getUTCFullYear() + '-' +
                        ('00' + (this.dateInit.getUTCMonth() + 1)).slice(-2) + '-' +
                        ('00' + this.dateInit.getUTCDate()).slice(-2);
                    let dateEnd = this.dateEnd.getUTCFullYear() + '-' +
                        ('00' + (this.dateEnd.getUTCMonth() + 1)).slice(-2) + '-' +
                        ('00' + this.dateEnd.getUTCDate()).slice(-2);
                    values = [ dateInit, dateEnd ];
                }

                this.$emit('input', {
                    filter: 'intersection',
                    type: 'DATE',
                    compare: this.filterTypeOptions.first_comparator,
                    metadatum_id: this.metadatumId,
                    collection_id: this.collectionId,
                    value: this.filterTypeOptions.accept_date_interval === 'yes' ? values : values[0]
                });
                this.$emit('input', {
                    filter: 'intersection',
                    type: 'DATE',
                    compare: this.filterTypeOptions.second_comparator,
                    metadatum_id: this.filterTypeOptions.secondary_filter_metadatum_id,
                    collection_id: this.collectionId,
                    value: this.filterTypeOptions.accept_date_interval === 'yes' ? values : values[0]
                });
            }
        }
    }
</script>

<style scoped>
    .field {
        margin-bottom: 0.125em !important;
    }
    .dropdown-trigger input {
        font-size: 0.75em;
    }
</style>
