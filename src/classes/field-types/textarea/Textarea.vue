<template>
    <b-input 
            :id="id"
            type="textarea"
            :value="inputValue"
            @blur="onBlur"
            @input="onInput($event)"></b-input>
</template>

<script>
    export default {
        data() {
            return {
                inputValue: ''
            }
        },
        props: {
            id: '',
            value: [String, Number, Object]
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
