<template>
    <b-input
            :class="{'has-content': value !== undefined && value !== ''}"
            :id="id"
            :value="value"
            @blur="$emit('blur')"
            @input="onInput($event)"/>
</template>

<script>
    export default {
        props: {
            metadatum: {
                type: Object
            },
            value: [String, Number, Array],
            id: ''
        },
        methods: {
            onInput(value) {
                this.$emit('input', value);
            }
        }
    }
</script>