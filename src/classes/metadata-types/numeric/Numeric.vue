<template>
    <b-input
            :disabled="disabled"
            :id="metadatum.metadatum.metadata_type_object.component + '-' + metadatum.metadatum.slug"
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
            metadatum: Object,
            value: [String, Number, Array],
            disabled: false,
        },
        computed: {
            getStep() {
                if (this.metadatum && this.metadatum.metadatum.metadata_type_options && this.metadatum.metadatum.metadata_type_options.step)
                    return this.metadatum.metadatum.metadata_type_options.step;
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
