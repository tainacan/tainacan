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
    const editorBlockTag = /<(p|ul|ol)(\s[^>]*)?>/i;

    function linkBareUrls(text) {
        return text.split(/(<a\b[^>]*>[\s\S]*?<\/a>)/gi).map((part, index) => {
            if (index % 2 === 1)
                return part;

            return part.replace(/((https?:\/\/|www\.)[^\s<]+)/gi, (url) => {
                const href = /^www\./i.test(url) ? `http://${url}` : url;
                return `<a href="${href}">${url}</a>`;
            });
        }).join('');
    }

    function paragraphsFromPlainText(text) {
        return String(text)
            .replace(/\r\n/g, '\n')
            .replace(/\r/g, '\n')
            .split(/\n{2,}/)
            .map((block) => `<p>${block.replace(/\n/g, '<br>')}</p>`)
            .join('\n');
    }

    function editorValueForSave(value) {
        if (value === null || value === undefined || value === '')
            return '';

        const html = String(value);
        if (/<(?:img|video|audio|iframe|embed|object|svg|hr)\b/i.test(html))
            return value;

        const visibleText = html
            .replace(/<[^>]*>/g, '')
            .replace(/&nbsp;|&#160;|&#xa0;/gi, ' ')
            .replace(/\u00a0/g, ' ')
            .replace(/\s+/g, '');

        return visibleText === '' ? '' : value;
    }

    function valueForRichTextEditor(value) {
        if (value === null || value === undefined || value === '')
            return '';

        const text = String(value);
        if (editorBlockTag.test(text))
            return text;

        return paragraphsFromPlainText(linkBareUrls(text));
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
			const initialValue = this.shouldUseRichTextEditor ? valueForRichTextEditor(this.value) : this.value;
			this.localValue = initialValue ? JSON.parse(JSON.stringify(initialValue)) : '';
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
