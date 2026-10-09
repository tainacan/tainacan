<template>
    <section> 
        <b-field :addons="false">
            <label class="label is-inline">
                {{ $i18n.getHelperTitle('tainacan-textarea', 'maxlength') }}
                <help-button
                        :title="$i18n.getHelperTitle('tainacan-textarea', 'maxlength')"
                        :message="$i18n.getHelperMessage('tainacan-textarea', 'maxlength')" />
            </label>
            <b-numberinput
                    v-model="maxlength"
                    name="maxlength"
                    step="1"
                    min="0"
                    controls-position="compact"
                    controls-alignment="right"
                    expanded
                    @update:model-value="onUpdateMaxlength" />
        </b-field>
        <b-field
                v-if="isRichTextEditorAllowed"
                :addons="false"
                :label="$i18n.getHelperTitle('tainacan-textarea', 'use_rich_text_editor')">
            &nbsp;
            <b-switch
                    v-model="useRichTextEditor"
                    size="is-small"
                    true-value="yes"
                    false-value="no"
                    @update:model-value="onUpdateUseRichTextEditor" />
            <help-button
                    :title="$i18n.getHelperTitle('tainacan-textarea', 'use_rich_text_editor')"
                    :message="$i18n.getHelperMessage('tainacan-textarea', 'use_rich_text_editor')" />
        </b-field>
    </section>
</template>

<script>
    export default {
        props: {
            value: [ String, Object, Array ]
        },
        emits: [
            'update:value',
            'close'
        ],
        data() {
            return {
                maxlength: [Number, null],
                useRichTextEditor: 'no',
                isRichTextEditorAllowed: tainacan_plugin.tainacan_allow_rich_text_editor === '1'
            }
        },
        created() {
            this.maxlength = this.value && this.value.maxlength ? Number(this.value.maxlength) : null;
            this.useRichTextEditor = this.value && this.value.use_rich_text_editor === 'yes' ? 'yes' : 'no';
        },
        methods: {
            onUpdateMaxlength(value) {
                if (value == 0) value = null;

                this.$emit('update:value', { maxlength: value, use_rich_text_editor: this.useRichTextEditor });
            },
            onUpdateUseRichTextEditor(value) {
                this.useRichTextEditor = value;
                this.$emit('update:value', { maxlength: this.maxlength, use_rich_text_editor: value });
            }
        }
    }
</script>

<style scoped>
    section{
        margin-bottom: 10px;
    }
    .tainacan-help-tooltip-trigger {
        font-size: 1em;
    }
</style>
