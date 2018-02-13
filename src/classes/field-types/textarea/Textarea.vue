<template>
    <b-input type="textarea"
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
