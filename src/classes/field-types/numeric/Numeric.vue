<template>
    <b-input
            :id="id"
            type="number"
            :value="inputValue"
            @blur="onBlur"
            @change="onBlur"
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
                inputValue: ''
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
                this.inputValue = $event;
                this.$emit('input', this.inputValue);
            }
        }
    }
</script>
