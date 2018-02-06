<template>
    <b-datepicker
              :value="dateValue"
              @blur="onBlur"
              @input="onInput($event)"></b-datepicker>
</template>

<script>
    export default {
        data() {
            return {
                dateValue: ''
            }
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
