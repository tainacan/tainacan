<template>
    <div>
        <b-datepicker
                :aria-labelledby="'filter-label-id-' + filter.id"
                :placeholder="$i18n.get('label_selectbox_init')"
                v-model="dateInit"
                size="is-small"
                @focus="isTouched = true"
                @input="validadeValues()"
                editable
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
                ]"/>
        <p class="is-size-7 has-text-centered is-marginless">{{ $i18n.get('label_until') }}</p>
        <b-datepicker
                :aria-labelledby="'filter-label-id-' + filter.id"
                :placeholder="$i18n.get('label_selectbox_init')"
                v-model="dateEnd"
                size="is-small"
                @input="validadeValues()"
                @focus="isTouched = true"
                editable
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
            'query.metaquery'() {
                this.updateSelectedValues();
            }
        },
        mounted() {
            this.updateSelectedValues();
        },
        methods: {
            // only validate if the first value is higher than first
            validadeValues: _.debounce( function (){
               
                if (this.dateInit === undefined)
                    this.dateInit = new Date();

                if (this.dateEnd === undefined)
                    this.dateEnd = new Date();

                if (this.dateInit > this.dateEnd) {
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
                    
                    if (metadata.value && metadata.value.length > 0){
                        this.dateInit = new Date(metadata.value[0]);
                        this.dateEnd = new Date(metadata.value[1]);
                    }

                    if (metadata.value[0] != undefined && metadata.value[1] != undefined)
                        this.$emit('sendValuesToTags', { 
                            label: this.parseDateToNavigatorLanguage(metadata.value[0]) + ' - ' + this.parseDateToNavigatorLanguage(metadata.value[1]),
                            value: [metadata.value[0], metadata.value[1]]
                        });
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
