<template>
    <component
            :is="'tainacan-rich-text-editor'"
            v-if="shouldUseRichTextEditor"
            :id="inputId"
            :disabled="disabled"
            :placeholder="itemMetadatum.metadatum.placeholder ? itemMetadatum.metadatum.placeholder : ''"
            :model-value="localValue"
            :max-length="getMaxlength"
            @update:model-value="onInput($event)"
            @blur="onBlur"
            @focus="onMobileSpecialFocus" />
    <b-input
            v-else
            :id="inputId"
            :ref="inputId"
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
            inputId: String,
            disabled: false
        },
        emits: [
            'update:value',
			'update:edited-with-rich-text-editor',
            'blur',
            'mobile-special-focus'
        ],
        data() {
            return {
                localValue: ''
            }
        },
        computed: {
            shouldUseRichTextEditor() {
                return this.itemMetadatum &&
                    this.itemMetadatum.metadatum &&
                    this.itemMetadatum.metadatum.metadata_type_object &&
					this.itemMetadatum.metadatum.metadata_type === 'Tainacan\\Metadata_Types\\Core_Description' &&
                    typeof tainacan_plugin !== 'undefined' &&
                    tainacan_plugin.tainacan_allow_rich_text_editor === '1' &&
                    this.itemMetadatum.metadatum.metadata_type_options &&
                    this.itemMetadatum.metadatum.metadata_type_options.use_rich_text_editor === 'yes';
            },
            getMaxlength() {
                if ( this.itemMetadatum && this.itemMetadatum.metadatum.metadata_type_options && this.itemMetadatum.metadatum.metadata_type_options.maxlength !== null && this.itemMetadatum.metadatum.metadata_type_options.maxlength !== undefined && this.itemMetadatum.metadatum.metadata_type_options.maxlength !== '' )
                    return Number(this.itemMetadatum.metadatum.metadata_type_options.maxlength);
                else
                    return undefined;
            }
        },
        created() {
			const initialValue = this.shouldUseRichTextEditor && typeof this.itemMetadatum.value_for_rich_text_editor === 'string'
				? this.itemMetadatum.value_for_rich_text_editor
				: this.value;
			this.localValue = initialValue ? JSON.parse(JSON.stringify(initialValue)) : '';
        },
        methods: {
            onInput(value) {
                const inputRef = this.$refs[this.inputId];
                if ( inputRef && this.getMaxlength && typeof inputRef.checkHtml5Validity === 'function' && !inputRef.checkHtml5Validity() )
                    return;

                this.localValue = value;
				if (this.itemMetadatum?.metadatum?.metadata_type === 'Tainacan\\Metadata_Types\\Core_Description')
					this.$emit('update:edited-with-rich-text-editor', this.shouldUseRichTextEditor);
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
