<template>
    <b-input
            :disabled="disabled"
            :id="itemMetadatum.metadatum.metadata_type_object.component + '-' + itemMetadatum.metadatum.slug"
            :value="value"
            @input="onInput($event)"
            @blur="onBlur"
            type="number"
            lang="en"
            :step="getStep"/>
</template>

<script>
    export default {
        props: {
            itemMetadatum: Object,
            value: [String, Number, Array],
            disabled: false,
        },
        computed: {
            getStep() {
                if (this.itemMetadatum && this.itemMetadatum.metadatum.metadata_type_options && this.itemMetadatum.metadatum.metadata_type_options.step)
                    return this.itemMetadatum.metadatum.metadata_type_options.step;
                else
                    return 0.01;
            }
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
