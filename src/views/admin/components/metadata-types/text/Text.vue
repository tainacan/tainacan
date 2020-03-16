<template>
    <b-input
            :disabled="disabled"
            :id="itemMetadatum.metadatum.metadata_type_object.component + '-' + itemMetadatum.metadatum.slug"
            :value="value"
            @input="onInput($event)"
            @blur="onBlur"/>
</template>

<script>
    export default {
        props: {
            itemMetadatum: Object,
            value: [String, Number, Array],
            disabled: false
        },
        methods: {
            onInput(value) {
                this.$emit('input', value);
            },
            onBlur() {
                this.$emit('blur');
            }
        }
    }
</script>