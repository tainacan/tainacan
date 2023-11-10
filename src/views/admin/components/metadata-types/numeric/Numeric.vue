<template>
    <b-numberinput
            :disabled="disabled"
            :ref="'tainacan-item-metadatum_id-' + itemMetadatum.metadatum.id + (itemMetadatum.parent_meta_id ? ('_parent_meta_id-' + itemMetadatum.parent_meta_id) : '')"
            :id="'tainacan-item-metadatum_id-' + itemMetadatum.metadatum.id + (itemMetadatum.parent_meta_id ? ('_parent_meta_id-' + itemMetadatum.parent_meta_id) : '')"
            :placeholder="itemMetadatum.metadatum.placeholder ? itemMetadatum.metadatum.placeholder : ''"
            :value="value"
            @input="onInput"
            @blur="onBlur"
            @focus="onMobileSpecialFocus"
            lang="en"
            :min="getMin"
            :max="getMax"
            :step="getStep" />
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
            },
            getMin() {
                if (this.itemMetadatum && this.itemMetadatum.metadatum.metadata_type_options && this.itemMetadatum.metadatum.metadata_type_options.min !== null && this.itemMetadatum.metadatum.metadata_type_options.min !== undefined && this.itemMetadatum.metadatum.metadata_type_options.min !== '')
                    return Number(this.itemMetadatum.metadatum.metadata_type_options.min);
                else
                    return undefined;
            },
            getMax() {
                if (this.itemMetadatum && this.itemMetadatum.metadatum.metadata_type_options && this.itemMetadatum.metadatum.metadata_type_options.max !== null && this.itemMetadatum.metadatum.metadata_type_options.max !== undefined && this.itemMetadatum.metadatum.metadata_type_options.max !== '')
                    return Number(this.itemMetadatum.metadatum.metadata_type_options.max);
                else
                    return undefined;
            }
        },
        methods: {
            onInput(value) {
                const inputRef = this.$refs['tainacan-item-metadatum_id-' + this.itemMetadatum.metadatum.id + (this.itemMetadatum.parent_meta_id ? ('_parent_meta_id-' + this.itemMetadatum.parent_meta_id) : '')];
                if ( inputRef && !inputRef.checkHtml5Validity())
                    return;

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

<style scoped>
    .b-numberinput {
        border-bottom-width: 0px !important;
        margin-left: 0 !important;
    }
</style>
