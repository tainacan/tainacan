<template>
    <b-datepicker
            :id="id"
            v-model="dateValue"
            @blur="onBlur"
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
            onBlur() {
                this.$emit('blur');
            },
            onInput($event) {
                this.dateValue = $event;
                this.$emit('input', this.dateValue);
            }
        }
    }
</script>
