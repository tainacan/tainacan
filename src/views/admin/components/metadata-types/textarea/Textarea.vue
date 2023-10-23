<template>
    <b-input
            :disabled="disabled"
            :ref="'tainacan-item-metadatum_id-' + itemMetadatum.metadatum.id + (itemMetadatum.parent_meta_id ? ('_parent_meta_id-' + itemMetadatum.parent_meta_id) : '')"
            :id="'tainacan-item-metadatum_id-' + itemMetadatum.metadatum.id + (itemMetadatum.parent_meta_id ? ('_parent_meta_id-' + itemMetadatum.parent_meta_id) : '')"
            :placeholder="itemMetadatum.metadatum.placeholder ? itemMetadatum.metadatum.placeholder : ''"
            :value="value"
            @input="onInput($event)"
            @blur="onBlur"
            type="textarea"
            @focus="onMobileSpecialFocus"
            :maxlength="getMaxlength" />
</template>

<script>
    export default {
        props: {
            itemMetadatum: Object,
            value: [String, Number, Array],
            disabled: false
        },
        computed: {
            getMaxlength() {
                if (this.itemMetadatum && this.itemMetadatum.metadatum.metadata_type_options && this.itemMetadatum.metadatum.metadata_type_options.maxlength !== null && this.itemMetadatum.metadatum.metadata_type_options.maxlength !== undefined && this.itemMetadatum.metadatum.metadata_type_options.maxlength !== '')
                    return Number(this.itemMetadatum.metadatum.metadata_type_options.maxlength);
                else
                    return undefined;
            }
        },
        methods: {
            onInput(value) {
                const inputRef = this.$refs['tainacan-item-metadatum_id-' + this.itemMetadatum.metadatum.id + (this.itemMetadatum.parent_meta_id ? ('_parent_meta_id-' + this.itemMetadatum.parent_meta_id) : '')];
                if ( inputRef && this.getMaxlength && !inputRef.checkHtml5Validity() )
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
