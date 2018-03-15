<template>
    <b-datepicker
            :id="id"
            v-model="dateValue"
            @input="onInput($event)"></b-datepicker>
</template>

<script>
    export default {
        created(){
            if( this.field && this.field.value ){
                this.inputValue = this.field.value
            }
        },
        data() {
            return {
                dateValue: new Date()
            }
        },
        props: {
            id: '',
            field: {
                type: Object
            },
            value: [String, Number, Array],
        },
        methods: {
            onInput($event) {
                this.dateValue = $event;
                let date_init = this.dateValue.getUTCFullYear() + '-' +
                    ('00' + (this.dateValue.getUTCMonth() + 1)).slice(-2) + '-' +
                    ('00' + this.dateValue.getUTCDate()).slice(-2);
                this.$emit('input', date_init);
                this.$emit('blur');
            }
        }
    }
</script>
