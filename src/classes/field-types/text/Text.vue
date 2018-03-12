<template>
    <b-input
    :id="id"
    :value="inputValue"
    @blur="onBlur"
    @input="onInput($event)"></b-input>
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
                inputValue: '',
            }
        },
        props: {
            field: {
                type: Object
            },
            value: [String, Number, Array],
            id: ''
        },
        methods: {
            onBlur() {
                this.$emit('blur');
            },
            onInput($event) {
                this.inputValue = $event;
                this.$emit('input', this.inputValue);
            }
        },
        created(){
            this.inputValue = this.value;
        }
    }
</script>