<template>
    <b-input
            :id="'tainacan-item-metadatum_id-' + itemMetadatum.metadatum.id + (itemMetadatum.parent_meta_id ? ('_parent_meta_id-' + itemMetadatum.parent_meta_id) : '')"
            :ref="'tainacan-item-metadatum_id-' + itemMetadatum.metadatum.id + (itemMetadatum.parent_meta_id ? ('_parent_meta_id-' + itemMetadatum.parent_meta_id) : '')"
            :disabled="disabled"
            :placeholder="itemMetadatum.metadatum.placeholder ? itemMetadatum.metadatum.placeholder : ''"
            :model-value="localValue"
            type="textarea"
            :maxlength="getMaxlength"
            @update:model-value="onInput($event)"
            @blur="onBlur"
            @focus="onMobileSpecialFocus" />
</template>

<script>
    export default {
        props: {
            itemMetadatum: Object,
            value: [String, Number, Array],
            disabled: false
        },
        emits: [
            'update:value',
            'blur',
            'mobile-special-focus'
        ],
        data() {
            return {
                localValue: ''
            }
        },
        computed: {
            getMaxlength() {
                if ( this.itemMetadatum && this.itemMetadatum.metadatum.metadata_type_options && this.itemMetadatum.metadatum.metadata_type_options.maxlength !== null && this.itemMetadatum.metadatum.metadata_type_options.maxlength !== undefined && this.itemMetadatum.metadatum.metadata_type_options.maxlength !== '' )
                    return Number(this.itemMetadatum.metadatum.metadata_type_options.maxlength);
                else
                    return undefined;
            }
        },
        created() {
            this.localValue = this.value ? JSON.parse(JSON.stringify(this.value)) : '';
        },
        methods: {
            onInput(value) {
                const inputRef = this.$refs['tainacan-item-metadatum_id-' + this.itemMetadatum.metadatum.id + (this.itemMetadatum.parent_meta_id ? ('_parent_meta_id-' + this.itemMetadatum.parent_meta_id) : '')];
                if ( inputRef && this.getMaxlength && !inputRef.checkHtml5Validity() )
                    return;

                this.localValue = value;
                this.changeValue(value);
            },
            changeValue: _.debounce(function(value) {
                this.$emit('update:value', value);
            }, 750),
            onBlur() {
                this.$emit('blur');
            },
            onMobileSpecialFocus() {
                this.$emit('mobile-special-focus');
            }
        }
    }
</script>
