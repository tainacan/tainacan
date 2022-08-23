<template>
    <b-input
            :disabled="disabled"
            :id="'tainacan-item-metadatum_id-' + itemMetadatum.metadatum.id + (itemMetadatum.parent_meta_id ? ('_parent_meta_id-' + itemMetadatum.parent_meta_id) : '')"
            :placeholder="itemMetadatum.metadatum.placeholder ? itemMetadatum.metadatum.placeholder : ''"
            :value="value"
            @input="onInput($event)"
            @blur="onBlur"
            type="textarea"
            @focus="onMobileSpecialFocus" />
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
            },
            onMobileSpecialFocus() {
                this.$emit('mobileSpecialFocus');
            }
        }
    }
</script>
