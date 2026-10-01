<template>
    <tainacan-rich-text-editor
            :id="inputId"
            :disabled="disabled"
            :placeholder="itemMetadatum?.metadatum?.placeholder || ''"
            :model-value="localValue"
            :max-length="getMaxlength"
            @update:model-value="onInput"
            @blur="onBlur"
            @focus="$emit('mobile-special-focus')" />
</template>

<script>
export default {
    props: {
        itemMetadatum: Object,
        value: [String, Number, Array],
        inputId: String,
        disabled: Boolean
    },
    emits: [ 'update:value', 'blur', 'mobile-special-focus' ],
    data() {
        return {
            localValue: typeof this.value === 'string' ? this.value : '',
            pendingValue: null,
            pendingTimer: null
        };
    },
    computed: {
        getMaxlength() {
            const maxlength = this.itemMetadatum?.metadatum?.metadata_type_options?.maxlength;
            return maxlength === null || maxlength === undefined || maxlength === '' ? undefined : Number(maxlength);
        }
    },
    beforeUnmount() {
        this.flushValue();
    },
    methods: {
        onInput(value) {
            this.localValue = value;
            this.pendingValue = value;
            clearTimeout(this.pendingTimer);
            this.pendingTimer = setTimeout(() => this.flushValue(), 750);
        },
        flushValue() {
            clearTimeout(this.pendingTimer);
            this.pendingTimer = null;
            if (this.pendingValue !== null) {
                this.$emit('update:value', this.pendingValue);
                this.pendingValue = null;
            }
        },
        onBlur() {
            this.flushValue();
            this.$emit('blur');
        }
    }
};
</script>
