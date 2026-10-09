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
    function editorValueForSave(value) {
        if (value === null || value === undefined || value === '')
            return '';

        const parsed = new DOMParser().parseFromString(String(value), 'text/html');
        if (parsed.body.querySelector('img, video, audio, iframe, embed, object, svg, hr'))
            return value;

        const visibleText = parsed.body.textContent.replace(/\s+/g, '');
        return visibleText === '' ? '' : value;
    }

    export default {
        props: {
            itemMetadatum: Object,
            value: [String, Number, Array],
            inputId: String,
            disabled: false
        },
        emits: [
            'update:value',
            'blur',
            'mobile-special-focus'
        ],
        data() {
            return {
                localValue: '',
                pendingValue: null,
                pendingTimer: null
            }
        },
        computed: {
            shouldUseRichTextEditor() {
                return this.itemMetadatum &&
                    this.itemMetadatum.metadatum &&
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
            this.localValue = this.value ? JSON.parse(JSON.stringify(this.value)) : '';
        },
        beforeUnmount() {
            this.flushValue();
        },
        methods: {
            onInput(value) {
                const inputRef = this.$refs[this.inputId];
                if ( inputRef && this.getMaxlength && typeof inputRef.checkHtml5Validity === 'function' && !inputRef.checkHtml5Validity() )
                    return;

                this.localValue = value;
                this.pendingValue = value;
                clearTimeout(this.pendingTimer);
                this.pendingTimer = setTimeout(() => this.flushValue(), 750);
            },
            flushValue() {
                clearTimeout(this.pendingTimer);
                this.pendingTimer = null;
                if (this.pendingValue !== null) {
                    const value = this.shouldUseRichTextEditor ? editorValueForSave(this.pendingValue) : this.pendingValue;
                    this.localValue = value;
                    this.$emit('update:value', value);
                    this.pendingValue = null;
                }
            },
            onBlur() {
                this.flushValue();
                this.$emit('blur');
            },
            onMobileSpecialFocus() {
                this.$emit('mobile-special-focus');
            }
        }
    }
</script>
