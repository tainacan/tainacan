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
                v-if="isWysiwygEditorAllowed"
                :addons="false"
                :label="$i18n.getHelperTitle('tainacan-textarea', 'use_wysiwyg_editor')">
            &nbsp;
            <b-switch
                    v-model="useWysiwygEditor"
                    size="is-small"
                    true-value="yes"
                    false-value="no"
                    @update:model-value="onUpdateUseWysiwygEditor" />
            <help-button
                    :title="$i18n.getHelperTitle('tainacan-textarea', 'use_wysiwyg_editor')"
                    :message="$i18n.getHelperMessage('tainacan-textarea', 'use_wysiwyg_editor')" />
        </b-field>
    </section>
</template>

<script>
    import { isWysiwygEditorAllowed } from '../../../js/wysiwyg-feature-flag';

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
                useWysiwygEditor: 'no',
                isWysiwygEditorAllowed: isWysiwygEditorAllowed()
            }
        },
        created() {
            this.maxlength = this.value && this.value.maxlength ? Number(this.value.maxlength) : null;
            this.useWysiwygEditor = this.value && this.value.use_wysiwyg_editor === 'yes' ? 'yes' : 'no';
        },
        methods: {
            onUpdateMaxlength(value) {
                if (value == 0) value = null;

                this.$emit('update:value', { maxlength: value, use_wysiwyg_editor: this.useWysiwygEditor });
            },
            onUpdateUseWysiwygEditor(value) {
                this.useWysiwygEditor = value;
                this.$emit('update:value', { maxlength: this.maxlength, use_wysiwyg_editor: value });
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
