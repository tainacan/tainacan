<template>
    <div>
        <!-- Date -->
        <div v-if="type === 'date'">
            <b-datepicker
                    :placeholder="$i18n.get('label_selectbox_init')"
                    v-model="date_init"
                    size="is-small"
                    @focus="isTouched = true"
                    @input="validate_values()"
                    icon="calendar-today"/>
            <p class="is-size-7 has-text-centered is-marginless">{{ $i18n.get('label_until') }}</p>
            <b-datepicker
                    :placeholder="$i18n.get('label_selectbox_init')"
                    v-model="date_end"
                    size="is-small"
                    @input="validate_values()"
                    @focus="isTouched = true"
                    icon="calendar-today"/>
        </div>

        <!-- Numeric -->
        <div v-else>
            <b-input
                    size="is-small"
                    type="number"
                    step="any"
                    @input="validate_values()"
                    v-model="value_init"/>
            <p class="is-size-7 has-text-centered is-marginless">{{ $i18n.get('label_until') }}</p>
            <b-input
                    size="is-small"
                    type="number"
                    step="any"
                    @input="validate_values()"
                    @focus="isTouched = true"
                    v-model="value_end"/>
        </div>
    </div>
</template>

<script>
    import { tainacan as axios } from '../../../js/axios/axios';
    import { wpAjax } from "../../../admin/js/mixins";

    export default {
        mixins: [ wpAjax ],
        created(){
            const vm = this;
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
                        vm.metadatum_object = result;
                        vm.type = ( result.metadata_type === 'Tainacan\\Metadata_Types\\Date') ? 'date' : 'numeric';
                        vm.selectedValues();
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
                metadatum_object: {},
            }
        },
        props: {
            filter: {
                type: Object // concentrate all attributes metadatum id and type
            },
            metadatum_id: [Number], // not required, but overrides the filter metadatum id if is set
            collection_id: [Number], // not required, but overrides the filter metadatum id if is set
            id: '',
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
                this.emit( this );
            }, 1000),
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
                            value: metadata.value[0] + ' - ' + metadata.value[1]
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
                    this.cleanSearch();
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
            emit: ( vm ) => {
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
                        return;
                    } else {
                        values =  [ vm.value_init, vm.value_end ];

                        if(vm.value_init !== vm.value_end && (vm.value_init % 1 !== 0 && vm.value_end % 1 == 0)) {
                            type = 'DECIMAL';
                        } else if(vm.value_init !== vm.value_end &&
                            vm.value_init % 1 !== 0 &&
                            vm.value_end % 1 !== 0) {

                            type = '';
                        } else if(vm.value_init !== vm.value_end &&
                            !(vm.value_init % 1 == 0 && vm.value_end % 1 !== 0)){
                            type = 'DECIMAL';
                        } else {
                            type = '';
                        }
                        //vm.isValid = true;
                        //vm.clear = false;
                    }
                }

                vm.$emit('input', {
                    filter: 'range',
                    type: type,
                    compare: 'BETWEEN',
                    metadatum_id: vm.metadatum,
                    collection_id: ( vm.collection_id ) ? vm.collection_id : vm.filter.collection_id,
                    value: values
                });

                if (values[0] != undefined && values[1] != undefined) {
                    vm.$eventBusSearch.$emit( 'sendValuesToTags', {
                        filterId: vm.filter.id,
                        value: values[0] + ' - ' + values[1]
                    });
                }
            }
        },
        beforeDestroy() {
            this.$eventBusSearch.$off('removeFromFilterTag', this.cleanSearchFromTags);
        }
    }
</script>
