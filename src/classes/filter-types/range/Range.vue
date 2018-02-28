<template>
    <div class="block">
        <div v-if="type === 'date'" class="columns">
            <b-datepicker
                    class="column"
                    v-model="date_init"
                    @input="validate_values()"
                    icon="calendar-today">
            </b-datepicker>
            <b-datepicker
                    class="column"
                    v-model="date_end"
                    @input="validate_values()"
                    icon="calendar-today">
            </b-datepicker>
            <div class="column">
                <a class="button is-small" @click="emit">
                    <b-icon icon="send"></b-icon>
                </a>
            </div>
        </div>
        <div class="columns" v-else>
            <b-input
                    type="number"
                    @input="validate_values()"
                    class="column"
                    v-model="value_init">
            </b-input>
            <b-input
                    type="number"
                    @input="validate_values()"
                    class="column"
                    v-model="value_end">
            </b-input>
            <div class="column is-one-fifth">
                <a class="button is-small" @click="emit">
                    <b-icon icon="send"></b-icon>
                </a>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        created(){
            if( this.typerange ) {
                this.type = this.typerange;
            }
        },
        data(){
            return {
                value_init: 0,
                value_end: 0,
                date_init: new Date,
                date_end: new Date,
                type: 'numeric'
            }
        },
        props: {
            filter: {
                type: Object // concentrate all attributes field id and type
            },
            field_id: [Number], // not required, but overrides the filter field id if is set
            collection_id: [Number], // not required, but overrides the filter field id if is set
            typerange: [String],  // not required, but overrides the filter field type if is set
            id: ''
        },
        methods: {
            // only validate if the first value is higher than first
            validate_values(){
                if( this.type === 'date' ){
                    if ( this.date_init > this.date_end ) {
                        let result = this.date_init;
                        result.setDate(result.getDate() + 1);
                        this.date_end = result;
                        this.error_message();
                    }
                } else {
                    if ( parseFloat( this.value_init ) > parseFloat( this.value_end )) {
                        this.value_end = parseFloat( this.value_init ) + 1;
                        this.error_message();
                    }
                }
            },
            // emit the operation for listeners
            emit(){
                let values = [];
                let type = ''

                if( this.type === 'date' ){
                    values = [ this.date_init, this.date_end ];
                    type = 'DATE';
                } else {
                    values =  [ this.value_init, this.value_end ];
                    type = 'DECIMAL';
                }


                this.$emit('input', {
                    filter: 'range',
                    type: type,
                    compare: 'BETWEEN',
                    field_id: ( this.field_id ) ? this.field_id : this.filter.field,
                    collection_id: ( this.collection_id ) ? this.collection_id : this.filter.collection_id,
                    value: values
                });
            },
            // message for error
            error_message(){
                this.$toast.open({
                    duration: 3000,
                    message: `First value should be lower than second value`,
                    position: 'is-bottom',
                    type: 'is-danger'
                })
            }
        }
    }
</script>