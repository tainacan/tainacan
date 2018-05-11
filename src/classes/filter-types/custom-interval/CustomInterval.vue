<template>
    <div>
        <!-- Date -->
        <div v-if="type === 'date'">
            <b-datepicker
                    :placeholder="$i18n.get('label_selectbox_init')"
                    :class="{'has-content': date_init !== undefined && date_init !== ''}"
                    v-model="date_init"
                    size="is-small"
                    @focus="isTouched = true"
                    @input="validate_values()"
                    icon="calendar-today"/>
            <b-datepicker
                    :placeholder="$i18n.get('label_selectbox_init')"
                    :class="{'has-content': date_end !== undefined && date_end !== ''}"
                    v-model="date_end"
                    size="is-small"
                    @input="validate_values()"
                    @focus="isTouched = true"
                    icon="calendar-today"/>
        </div>

        <!-- Numeric -->
        <div
                class="columns"
                v-else>
            <b-input
                    :class="{'has-content': value_init !== undefined && value_init !== ''}"
                    size="is-small"
                    type="number"
                    @input="validate_values()"
                    class="column"
                    v-model="value_init"/>
            <b-input
                    :class="{'has-content': value_end !== undefined && value_end !== ''}"
                    size="is-small"
                    type="number"
                    @input="validate_values()"
                    @focus="isTouched = true"
                    class="column"
                    v-model="value_end"/>
        </div>
        <ul
                class="selected-list-box"
                v-if="isValid && !clear">
            <li>
                <b-tag
                        attached
                        closable
                        @close="clearSearch()">
                    {{ showSearch() }}
                </b-tag>
            </li>
        </ul>
    </div>
</template>

<script>
    import { tainacan as axios } from '../../../js/axios/axios';

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
                    this.$console.log(error);
                });
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
                field: '',
                field_object: {},
            }
        },
        props: {
            filter: {
                type: Object // concentrate all attributes field id and type
            },
            field_id: [Number], // not required, but overrides the filter field id if is set
            collection_id: [Number], // not required, but overrides the filter field id if is set
            id: '',
            query: Object
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
            validate_values(){
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

                    if( this.date_init === null || this.date_end === null ){
                        this.clear = true;
                        return '';
                    }

                    return this.date_init.toLocaleString().split(' ')[0] + ' - ' + this.date_end.toLocaleString().split(' ')[0];
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
            emit:( vm ) => {
                let values = [];
                let type = '';

                if( vm.type === 'date' ){

                    if( vm.date_init === null && vm.date_end === null ){
                      values = [];
                      type = 'DATE';
                      vm.isValid = false;
                      vm.clear = true;
                    } else {
                      let date_init = vm.date_init.getUTCFullYear() + '-' +
                          ('00' + (vm.date_init.getUTCMonth() + 1)).slice(-2) + '-' +
                          ('00' + vm.date_init.getUTCDate()).slice(-2);
                      let date_end = vm.date_end.getUTCFullYear() + '-' +
                          ('00' + (vm.date_end.getUTCMonth() + 1)).slice(-2) + '-' +
                          ('00' + vm.date_end.getUTCDate()).slice(-2);
                      values = [ date_init, date_end ];
                      type = 'DATE';
                      vm.isValid = true;
                      vm.clear = false;
                    }
                } else {
                    if( vm.value_init === null || vm.value_end === null
                      || vm.value_init === '' || vm.value_end === ''){
                        values = [];
                        type = 'DECIMAL';
                        vm.isValid = false;
                        vm.clear = true;
                        return;
                    } else {
                        values =  [ vm.value_init, vm.value_end ];
                        type = 'DECIMAL';
                        vm.isValid = true;
                        vm.clear = false;
                    }
                }


                vm.$emit('input', {
                    filter: 'range',
                    type: type,
                    compare: 'BETWEEN',
                    field_id: vm.field,
                    collection_id: ( vm.collection_id ) ? vm.collection_id : vm.filter.collection_id,
                    value: values
                });
            }
        }
    }
</script>
