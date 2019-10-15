<template>
    <div>
        <b-datepicker
                :aria-labelledby="'filter-label-id-' + filter.id"
                :placeholder="$i18n.get('label_selectbox_init')"
                v-model="date_init"
                size="is-small"
                @focus="isTouched = true"
                @input="validate_values()"
                editable
                :date-formatter="(date) => dateFormatter(date)"
                :date-parser="(date) => dateParser(date)"
                icon="calendar-today"
                :day-names="[
                    $i18n.get('datepicker_short_sunday'),
                    $i18n.get('datepicker_short_monday'),
                    $i18n.get('datepicker_short_tuesday'),
                    $i18n.get('datepicker_short_wednesday'),
                    $i18n.get('datepicker_short_thursday'),
                    $i18n.get('datepicker_short_friday'),
                    $i18n.get('datepicker_short_saturday'),
                ]"/>
        <p class="is-size-7 has-text-centered is-marginless">{{ $i18n.get('label_until') }}</p>
        <b-datepicker
                :aria-labelledby="'filter-label-id-' + filter.id"
                :placeholder="$i18n.get('label_selectbox_init')"
                v-model="date_end"
                size="is-small"
                @input="validate_values()"
                @focus="isTouched = true"
                editable
                :date-formatter="(date) => dateFormatter(date)"
                :date-parser="(date) => dateParser(date)"
                icon="calendar-today"
                :day-names="[
                    $i18n.get('datepicker_short_sunday'),
                    $i18n.get('datepicker_short_monday'),
                    $i18n.get('datepicker_short_tuesday'),
                    $i18n.get('datepicker_short_wednesday'),
                    $i18n.get('datepicker_short_thursday'),
                    $i18n.get('datepicker_short_friday'),
                    $i18n.get('datepicker_short_saturday'),
                ]"/>
    </div>
</template>

<script>
    import { wpAjax, dateInter } from "../../../admin/js/mixins";
    import { filterTypeMixin } from '../filter-types-mixin';
    import moment from 'moment';

    export default {
        mixins: [ 
            wpAjax,
            dateInter, 
            filterTypeMixin
        ],
        mounted() {
            this.updateSelectedValues();
        },
        data(){
            return {
                date_init: undefined,
                date_end: undefined,
                isTouched: false,
                isValid: false,
                clear: false
            }
        },
        watch: {
            isTouched( val ){
              if ( val && this.date_init === null)
                  this.date_init = new Date();

              if ( val && this.date_end === null)
                  this.date_end =  new Date();
            },
            'query.metaquery'() {
                this.updateSelectedValues();
            }
        },
        methods: {
            // only validate if the first value is higher than first
            validate_values: _.debounce( function (){
               
                if (this.date_init === undefined)
                    this.date_init = new Date();

                if (this.date_end === undefined)
                    this.date_end =  new Date();

                if ( this.date_init > this.date_end ) {
                    this.error_message();
                    return
                }
               
                this.emit();
            }, 800),
            // message for error
            error_message(){
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
                    
                    if (metadata.value && metadata.value.length > 0){
                        this.date_init = new Date(metadata.value[0]);
                        this.date_end = new Date(metadata.value[1]);
                        this.isValid = true;
                    }

                    if (metadata.value[0] != undefined && metadata.value[1] != undefined)
                        this.$emit('sendValuesToTags', { 
                            label: this.parseDateToNavigatorLanguage(metadata.value[0]) + ' - ' + this.parseDateToNavigatorLanguage(metadata.value[1]),
                            value: [metadata.value[0], metadata.value[1]]
                        });
                } else {
                    this.date_init = null;
                    this.date_end = null; 
                }
            },
            // emit the operation for listeners
            emit() {
                let values = [];

                if (this.date_init === null && this.date_end === null) {
                    values = [];
                    this.isValid = false;
                    this.clear = true;
                } else {
                    let date_init = this.date_init.getUTCFullYear() + '-' +
                        ('00' + (this.date_init.getUTCMonth() + 1)).slice(-2) + '-' +
                        ('00' + this.date_init.getUTCDate()).slice(-2);
                    let date_end = this.date_end.getUTCFullYear() + '-' +
                        ('00' + (this.date_end.getUTCMonth() + 1)).slice(-2) + '-' +
                        ('00' + this.date_end.getUTCDate()).slice(-2);
                    values = [ date_init, date_end ];
                    this.isValid = true;
                    this.clear = false;
                }

                this.$emit('input', {
                    filter: 'range',
                    type: 'DATE',
                    compare: 'BETWEEN',
                    metadatum_id: this.metadatumId,
                    collection_id: this.collectionId,
                    value: values
                });

                if (values[0] != undefined && values[1] != undefined)
                    this.$emit( 'sendValuesToTags', { 
                        label: this.parseDateToNavigatorLanguage(values[0]) + ' - ' + this.parseDateToNavigatorLanguage(values[1]),
                        value: [ values[0], values[1] ]
                    });
            }
        }
    }
</script>

<style scoped>
    .field {
        margin-bottom: 0.125rem !important;
    }
    p.is-size-7 {
        margin-bottom: 0.125rem !important;
    }
</style>
