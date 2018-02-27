<template>
    <b-datepicker
            :id="id"
            v-model="dateValue"
            @blur="onBlur"
            @input="onInput($event)"></b-datepicker>
</template>

<script>
    export default {
        data() {
            return {
                dateValue: new Date()
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
                this.dateValue = $event;
                this.$emit('input', this.dateValue);
            }
        }
    }
</script>
