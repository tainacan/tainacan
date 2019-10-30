<template>
    <b-input
            :disabled="disabled"
            :class="{'has-content': value !== undefined && value !== ''}"
            :id="id"
            :value="value"
            @input="onInput($event)"/>
</template>

<script>
    export default {
        props: {
            metadatum: Object,
            value: [String, Number, Array],
            id: '',
            disabled: false
        },
        methods: {
            onInput(value) {
                this.$emit('input', value);
            }
        }
    }
</script>