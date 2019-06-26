<template>
    <div>
        <!-- Date -->
        <div v-if="type === 'date'">
            <b-datepicker
                    :aria-labelledby="labelId"
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
                    :aria-labelledby="labelId"
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
            <b-numberinput
                    :aria-labelledby="labelId"
                    size="is-small"
                    step="any"
                    @input="validate_values()"
                    v-model="value_init"/>
            <p class="is-size-7 has-text-centered is-marginless">{{ $i18n.get('label_until') }}</p>
            <b-numberinput
                    :aria-labelledby="labelId"
                    size="is-small"
                    step="any"
                    @input="validate_values()"
                    @focus="isTouched = true"
                    v-model="value_end"/>
        </div>
    </div>
</template>

<script>
    import { tainacan as axios } from '../../../js/axios/axios';
    import { wpAjax, dateInter } from "../../../admin/js/mixins";
    import moment from 'moment';

    export default {
        mixins: [ wpAjax, dateInter ],
        created() {
            this.collection = ( this.collection_id ) ? this.collection_id : this.filter.collection_id;
            this.metadatum = ( this.metadatum_id ) ? this.metadatum_id : this.filter.metadatum.metadatum_id;

            let in_route = '/collection/' + this.collection + '/metadata/' +  this.metadatum;

            if(this.isRepositoryLevel || this.collection == 'filter_in_repository'){
                in_route = '/metadata/'+ this.metadatum;
            }

            axios.get(in_route)
                .then( res => {
                    let result = res.data;
                    if( result && result.metadata_type ){
                        this.metadatum_object = result;
                        this.type = ( result.metadata_type === 'Tainacan\\Metadata_Types\\Date') ? 'date' : 'numeric';
                        this.selectedValues();
                    }
                })
                .catch(error => {
                    this.$console.log(error);
                });

            this.$eventBusSearch.$on('removeFromFilterTag', this.cleanSearchFromTags);
        },
        data(){
            return {
                value_init: null,
                value_end: null,
                date_init: undefined,
                date_end: undefined,
                isTouched: false,
                isValid: false,
                clear: false,
                type: 'numeric',
                collection: '',
                metadatum: '',
                metadatum_object: {}
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
        watch: {
            isTouched( val ){
              if ( val && this.date_init === null)
                  this.date_init = new Date();

              if ( val && this.date_end === null)
                  this.date_end =  new Date();

              this.isTouched = val;
            }
        },
        methods: {
            // only validate if the first value is higher than first
            validate_values: _.debounce( function (){
                if( this.type === 'date' ){
                    if (this.date_init ===  undefined)
                        this.date_init = new Date();

                    if (this.date_end === undefined)
                        this.date_end =  new Date();

                    if ( this.date_init > this.date_end ) {
                        let result = this.date_init;
                        result.setDate(result.getDate() + 1);
                        this.date_end = result;

                        result.setDate(result.getDate() - 1);
                        this.date_init = result;
                        //this.error_message();
                    }

                } else {
                    if ( parseFloat( this.value_init ) > parseFloat( this.value_end )) {
                        //this.value_end = parseFloat( this.value_init ) + 1;
                        //this.error_message();
                        return;
                    }
                }
                this.emit();
            }, 1000),
            // message for error
            error_message(){
                if ( !this.isTouched ) return false;

                this.$toast.open({
                    duration: 3000,
                    message: this.$i18n.get('info_error_first_value_greater'),
                    position: 'is-bottom',
                    type: 'is-danger'
                })
            },
            dateFormatter(dateObject) { 
                return moment(dateObject, moment.ISO_8601).format(this.dateFormat);
            },
            dateParser(dateString) { 
                return moment(dateString, this.dateFormat).toDate(); 
            },
            selectedValues(){
                if ( !this.query || !this.query.metaquery || !Array.isArray( this.query.metaquery ) )
                    return false;

                let index = this.query.metaquery.findIndex(newMetadatum => newMetadatum.key === this.metadatum );
                if ( index >= 0){
                    let metadata = this.query.metaquery[ index ];
                    if( metadata.value && metadata.value.length > 0 && this.type === 'numeric'){
                        this.value_init = metadata.value[0];
                        this.value_end = metadata.value[1];
                        this.isValid = true;
                    } else if( metadata.value && metadata.value.length > 0 ){
                        this.date_init = new Date( metadata.value[0] );
                        this.date_end = new Date( metadata.value[1] );

                        this.isValid = true;
                    }

                    if (metadata.value[0] != undefined && metadata.value[1] != undefined) {
                        this.$eventBusSearch.$emit( 'sendValuesToTags', {
                            filterId: this.filter.id,
                            value: this.parseDateToNavigatorLanguage(metadata.value[0]) + ' - ' + this.parseDateToNavigatorLanguage(metadata.value[1])
                        });
                    }

                } else {
                    return false;
                }
            },
            showSearch(){
                if( this.type === 'date' ){

                    if( this.date_init === null || this.date_end === null ){
                        this.clear = true;
                        return '';
                    }

                    return this.date_init.toLocaleString().split(' ')[0] + ' - ' + this.date_end.toLocaleString().split(' ')[0];
                }
                // else {
                //     return this.value_init + ' - ' +this.value_end;
                // }
            },
            cleanSearchFromTags(filterTag) {
                if (filterTag.filterId == this.filter.id)
                    this.clearSearch();
            },
            clearSearch(){

                this.clear = true;

                this.$emit('input', {
                    filter: 'range',
                    compare: 'BETWEEN',
                    metadatum_id: this.metadatum,
                    collection_id: ( this.collection_id ) ? this.collection_id : this.filter.collection_id,
                    value: ''
                });

                if( this.type === 'date' ){
                    this.date_init =  null;
                    this.date_end = null;
                    this.isTouched = false;
                } else {
                    this.value_end = null;
                    this.value_init = null;
                    this.isTouched = false;
                }
            },
            // emit the operation for listeners
            emit() {
                let values = [];
                let type = '';

                if( this.type === 'date' ){

                    if( this.date_init === null && this.date_end === null ){
                      values = [];
                      type = 'DATE';
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
                      type = 'DATE';
                      this.isValid = true;
                      this.clear = false;
                    }
                } else {
                    if( this.value_init === null || this.value_end === null
                      || this.value_init === '' || this.value_end === ''){
                        return;
                    } else {
                        values =  [ this.value_init, this.value_end ];

                        if(this.value_init !== this.value_end && (this.value_init % 1 !== 0 && this.value_end % 1 == 0)) {
                            type = 'DECIMAL';
                        } else if(this.value_init !== this.value_end &&
                            this.value_init % 1 !== 0 &&
                            this.value_end % 1 !== 0) {

                            type = '';
                        } else if(this.value_init !== this.value_end &&
                            !(this.value_init % 1 == 0 && this.value_end % 1 !== 0)){
                            type = 'DECIMAL';
                        } else {
                            type = '';
                        }
                        //this.isValid = true;
                        //this.clear = false;
                    }
                }

                this.$emit('input', {
                    filter: 'range',
                    type: type,
                    compare: 'BETWEEN',
                    metadatum_id: this.metadatum,
                    collection_id: ( this.collection_id ) ? this.collection_id : this.filter.collection_id,
                    value: values
                });

                if (values[0] != undefined && values[1] != undefined) {
                    this.$eventBusSearch.$emit( 'sendValuesToTags', {
                        filterId: this.filter.id,
                        value: this.parseDateToNavigatorLanguage(values[0]) + ' - ' + this.parseDateToNavigatorLanguage(values[1])
                    });
                }
            }
        },
        beforeDestroy() {
            this.$eventBusSearch.$off('removeFromFilterTag', this.cleanSearchFromTags);
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
