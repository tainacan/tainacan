<template>
    <div>
        <!-- Date -->
        <div v-if="metadatumType === 'Tainacan\\Metadata_Types\\Date'">
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

        <!-- Numeric -->
        <div v-else>
            <b-input
                    type="number"
                    :aria-labelledby="'filter-label-id-' + filter.id"
                    size="is-small"
                    step="any"
                    @input="validate_values"
                    v-model="value_init"/>
            <p class="is-size-7 has-text-centered is-marginless">{{ $i18n.get('label_until') }}</p>
            <b-input
                    type="number"
                    :aria-labelledby="'filter-label-id-' + filter.id"
                    size="is-small"
                    step="any"
                    @input="validate_values"
                    @focus="isTouched = true"
                    v-model="value_end"/>
        </div>
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
            this.selectedValues();
        },
        data(){
            return {
                value_init: '',
                value_end: '',
                date_init: undefined,
                date_end: undefined,
                isTouched: false,
                isValid: false,
                clear: false,
                type: 'DECIMAL'
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
                this.selectedValues();
            }
        },
        methods: {
            // only validate if the first value is higher than first
            validate_values: _.debounce( function (){
               
                if( this.metadatumType === 'Tainacan\\Metadata_Types\\Date' ){
                    if (this.date_init === undefined)
                        this.date_init = new Date();

                    if (this.date_end === undefined)
                        this.date_end =  new Date();

                    if ( this.date_init > this.date_end ) {
                        this.error_message();
                        return
                    }
                } else {
                    if (this.value_init.constructor == Number)
                        this.value_init = this.value_init.valueOf();

                    if (this.value_end.constructor == Number)
                        this.value_end = this.value_end.valueOf();
                        
                    this.value_init = parseFloat(this.value_init);
                    this.value_end = parseFloat(this.value_end);

                    if (isNaN(this.value_init) || isNaN(this.value_end))
                        return

                    if (this.value_init > this.value_end) {                     
                        this.error_message();
                        return;
                    }
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
            selectedValues(){
                if ( !this.query || !this.query.metaquery || !Array.isArray( this.query.metaquery ) )
                    return false;

                let index = this.query.metaquery.findIndex(newMetadatum => newMetadatum.key == this.metadatumId);

                if (index >= 0) {
                    let metadata = this.query.metaquery[ index ];
                    
                    if( metadata.value && metadata.value.length > 0 && this.metadatumType === 'Tainacan\\Metadata_Types\\Numeric'){
                        this.value_init = parseFloat(metadata.value[0]);
                        this.value_end = parseFloat(metadata.value[1]);
                        this.isValid = true;
                    } else if( metadata.value && metadata.value.length > 0 ){
                        this.date_init = new Date(metadata.value[0]);
                        this.date_end = new Date(metadata.value[1]);
                        this.isValid = true;
                    }

                    if (metadata.value[0] != undefined && metadata.value[1] != undefined)
                        this.$emit('sendValuesToTags', { 
                            label: (this.metadatumType === 'Tainacan\\Metadata_Types\\Numeric' ? (metadata.value[0] + ' - ' + metadata.value[1]) : this.parseDateToNavigatorLanguage(metadata.value[0]) + ' - ' + this.parseDateToNavigatorLanguage(metadata.value[1])),
                            value: [metadata.value[0], metadata.value[1]]
                        });
                } else {
                    if (this.metadatumType === 'Tainacan\\Metadata_Types\\Numeric') {
                        this.value_init = '';
                        this.value_end = '';
                    } else {
                        this.date_init = null;
                        this.date_end = null;
                    }
                    
                }
            },
            // emit the operation for listeners
            emit() {
                let values = [];
                
                if (this.metadatumType === 'Tainacan\\Metadata_Types\\Date') {

                    if (this.date_init === null && this.date_end === null) {
                      values = [];
                      this.type = 'DATE';
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
                      this.type = 'DATE';
                      this.isValid = true;
                      this.clear = false;
                    }
                } else {
                    if (this.value_init === null || this.value_end === null
                      || this.value_init === '' || this.value_end === ''){
                        return;
                    } else {
                        values =  [ this.value_init, this.value_end ];

                        if(this.value_init !== this.value_end && (this.value_init % 1 !== 0 && this.value_end % 1 == 0)) {
                            this.type = 'DECIMAL';
                        } else if(this.value_init !== this.value_end &&
                            this.value_init % 1 !== 0 &&
                            this.value_end % 1 !== 0) {

                            this.type = '';
                        } else if(this.value_init !== this.value_end &&
                            !(this.value_init % 1 == 0 && this.value_end % 1 !== 0)){
                            this.type = 'DECIMAL';
                        } else {
                            this.type = '';
                        }
                        //this.isValid = true;
                        //this.clear = false;
                    }
                }

                this.$emit('input', {
                    filter: 'range',
                    type: this.type,
                    compare: 'BETWEEN',
                    metadatum_id: this.metadatumId,
                    collection_id: this.collectionId,
                    value: values
                });

                if (values[0] != undefined && values[1] != undefined)
                    this.$emit( 'sendValuesToTags', { 
                        label: (this.metadatumType === 'Tainacan\\Metadata_Types\\Numeric' ? (values[0] + ' - ' + values[1]) : this.parseDateToNavigatorLanguage(values[0]) + ' - ' + this.parseDateToNavigatorLanguage(values[1])),
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
