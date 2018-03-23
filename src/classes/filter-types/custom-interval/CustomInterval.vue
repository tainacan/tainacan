<template>
    <div>
        <div v-if="type === 'date'">
            <b-datepicker
                    size="is-small"
                    v-model="date_init"
                    @input="validate_values()"
                    icon="calendar-today">
            </b-datepicker>
            <br>
            <b-datepicker
                    size="is-small"
                    v-model="date_end"
                    @input="validate_values()"
                    @focus="isTouched = true"
                    icon="calendar-today">
            </b-datepicker>
            <br>
        </div>
        <div class="columns" v-else>
            <b-input
                    size="is-small"
                    type="number"
                    @input="validate_values()"
                    class="column"
                    v-model="value_init">
            </b-input>
            <b-input
                    size="is-small"
                    type="number"
                    @input="validate_values()"
                    @focus="isTouched = true"
                    class="column"
                    v-model="value_end">
            </b-input>
        </div>
        <div class="field has-text-centered">
            <b-tag v-if="isValid && !clear"
                   type="is-primary"
                   size="is-small"
                   closable
                   @close="clearSearch()">
                {{ showSearch() }}
            </b-tag>
        </div>
    </div>
</template>

<script>
    import { tainacan as axios } from '../../../js/axios/axios';
    import _ from 'lodash';

    export default {
        created(){
            const vm = this;
            this.collection = ( this.collection_id ) ? this.collection_id : this.filter.collection_id;
            this.field = ( this.field_id ) ? this.field_id : this.filter.field.field_id;

            axios.get('/collection/' + this.collection + '/fields/' +  this.field )
                .then( res => {
                    let result = res.data;
                    if( result && result.field_type ){
                        vm.field_object = result;
                        vm.type = ( result.field_type === 'Tainacan\\Field_Types\\Date') ? 'date' : 'numeric';
                        vm.selectedValues();
                    }
                })
                .catch(error => {
                    console.log(error);
                });
        },
        data(){
            return {
                value_init: null,
                value_end: null,
                date_init: new Date,
                date_end: new Date,
                isTouched: false,
                isValid: false,
                clear: false,
                type: 'numeric',
                collection: '',
                field: '',
                field_object: {}
            }
        },
        props: {
            filter: {
                type: Object // concentrate all attributes field id and type
            },
            field_id: [Number], // not required, but overrides the filter field id if is set
            collection_id: [Number], // not required, but overrides the filter field id if is set
            id: '',
            query: {}
        },
        methods: {
            // only validate if the first value is higher than first
            validate_values(){
                if( this.type === 'date' ){
                    if ( this.date_init > this.date_end ) {
                        let result = this.date_init;
                        result.setDate(result.getDate() + 1);
                        this.date_end = result;
                        //this.error_message();
                    }
                } else {
                    this.value_end = (this.value_end === null) ? 0 : this.value_end;
                    this.value_init = (this.value_init === null) ? 0 : this.value_init;

                    if ( parseFloat( this.value_init ) > parseFloat( this.value_end )) {
                        this.value_end = parseFloat( this.value_init ) + 1;
                        //this.error_message();
                    }
                }
                this.emit( this );
            },
            // message for error
            error_message(){
                if ( !this.isTouched ) return false;

                this.$toast.open({
                    duration: 3000,
                    message: `First value should be lower than second value`,
                    position: 'is-bottom',
                    type: 'is-danger'
                })
            },
            selectedValues(){
                if ( !this.query || !this.query.metaquery || !Array.isArray( this.query.metaquery ) )
                    return false;

                let index = this.query.metaquery.findIndex(newField => newField.key === this.field );
                if ( index >= 0){
                    let metadata = this.query.metaquery[ index ];
                    if( metadata.value.length > 0 && this.type === 'numeric'){
                        this.value_init = metadata.value[0];
                        this.value_end = metadata.value[1];
                        this.isValid = true;
                    } else if( metadata.value.length > 0 ){
                        this.date_init = new Date( metadata.value[0] );
                        this.date_end = new Date( metadata.value[1] );

                        this.isValid = true;
                    }

                } else {
                    return false;
                }
            },
            showSearch(){
                if( this.type === 'date' ){
                    let date_init = ('00' + this.date_init.getUTCDate()).slice(-2) + '/' + ('00' + (this.date_init.getUTCMonth() + 1)).slice(-2)
                         + '/' + this.date_init.getUTCFullYear();
                    let date_end = ('00' + this.date_end.getUTCDate()).slice(-2) + '/' + ('00' + (this.date_end.getUTCMonth() + 1)).slice(-2)
                        + '/' + this.date_end.getUTCFullYear();
                    return date_init + ' - ' + date_end;
                } else {
                    return this.value_init + ' - ' +this.value_end;
                }
            },
            clearSearch(){
                this.clear = true;
                this.$emit('input', {
                    filter: 'range',
                    compare: 'BETWEEN',
                    field_id: this.field,
                    collection_id: ( this.collection_id ) ? this.collection_id : this.filter.collection_id,
                    value: ''
                });
            },

            // emit the operation for listeners
            emit: _.debounce(( vm ) => {
                let values = [];
                let type = '';

                if( vm.type === 'date' ){
                    let date_init = vm.date_init.getUTCFullYear() + '-' +
                        ('00' + (vm.date_init.getUTCMonth() + 1)).slice(-2) + '-' +
                        ('00' + vm.date_init.getUTCDate()).slice(-2);
                    let date_end = vm.date_end.getUTCFullYear() + '-' +
                        ('00' + (vm.date_end.getUTCMonth() + 1)).slice(-2) + '-' +
                        ('00' + vm.date_end.getUTCDate()).slice(-2);
                    values = [ date_init, date_end ];
                    type = 'DATE';
                } else {
                    values =  [ vm.value_init, vm.value_end ];
                    type = 'DECIMAL';
                }

                vm.isValid = true;
                vm.clear = false;
                vm.$emit('input', {
                    filter: 'range',
                    type: type,
                    compare: 'BETWEEN',
                    field_id: vm.field,
                    collection_id: ( vm.collection_id ) ? vm.collection_id : vm.filter.collection_id,
                    value: values
                });
            }, 700)
        }
    }
</script>