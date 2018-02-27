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
        data() {
            return {
                inputValue: ''
            }
        },
        props: {
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
        }
    }
</script>
