<template>
    <b-input
    :id="id"
    :value="inputValue"
    @blur="onBlur"
    @input="onInput($event)"></b-input>
</template>

<script>
    export default {
        data() {
            return {
                inputValue: '',
            }
        },
        props: {
            value: [String, Number, Object],
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