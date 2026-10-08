<template>
    <div
            class="tainacan-rich-text-editor"
            :class="{ 'is-invalid': invalid }">
        <input
                v-if="name"
                type="hidden"
                :name="name"
                :value="modelValue">
        <Editor
                :id="id"
                ref="editor"
                :model-value="modelValue"
                :init="editorInit"
                license-key="gpl"
                :disabled="disabled"
                @update:model-value="onUpdate"
                @before-add-undo="onBeforeAddUndo"
                @focus="onFocus"
                @blur="onBlur" />
        <p
                class="help"
                aria-hidden="true">
            {{ $i18n.get('instruction_rich_text_editor_toolbar_shortcut') }}
        </p>
        <p
                :id="keyboardHintId"
                class="sr-only">
            {{ $i18n.get('instruction_rich_text_editor_toolbar_shortcut_screen_reader') }}
        </p>
    </div>
</template>

<script>
import Editor from '@tinymce/tinymce-vue';
import 'tinymce/tinymce';
import 'tinymce/icons/default';
import 'tinymce/models/dom';
import 'tinymce/themes/silver';
import 'tinymce/plugins/code';
import 'tinymce/plugins/link';
import 'tinymce/plugins/lists';
import 'tinymce/plugins/autolink';
import 'tinymce/skins/ui/oxide/skin.css';
import contentCss from 'tinymce/skins/content/default/content.css';
import contentUiCss from 'tinymce/skins/ui/oxide/content.css';

let nextKeyboardHintId = 0;
const pendingRichTextEditorDialogMatchers = new Set();
let richTextEditorAuxObserver;

function processPendingRichTextEditorAux() {
    if (pendingRichTextEditorDialogMatchers.size) {
        const dialogs = document.querySelectorAll('.tox-dialog-wrap');
        const dialog = dialogs[dialogs.length - 1];

        if (dialog) {
            for (const matcher of pendingRichTextEditorDialogMatchers) {
                if (matcher(dialog)) {
                    dialog.classList.add('tainacan-rich-text-editor-dialog');
                    pendingRichTextEditorDialogMatchers.delete(matcher);
                }
            }
        }
    }

    stopRichTextEditorAuxObserverWhenIdle();
}

function ensureRichTextEditorAuxObserver() {
    if (richTextEditorAuxObserver)
        return;

    richTextEditorAuxObserver = new MutationObserver(processPendingRichTextEditorAux);
    richTextEditorAuxObserver.observe(document.body, { childList: true, subtree: true });
    processPendingRichTextEditorAux();
}

function stopRichTextEditorAuxObserverWhenIdle() {
    if (!richTextEditorAuxObserver || pendingRichTextEditorDialogMatchers.size)
        return;

    richTextEditorAuxObserver.disconnect();
    richTextEditorAuxObserver = undefined;
}

const EDITOR_INIT = {
    menubar: false,
    plugins: 'link lists code autolink',
    skin: false,
    content_css: false,
    content_style: `${contentCss}\n${contentUiCss}\nbody { font-family: 'Roboto', 'Source Sans', 'Helvetica', sans-serif; font-size: 0.875em; color: #1d1d1d; }\na, a:visited, a:hover, a:focus { color: #187181; }`,
    toolbar: 'bold italic bullist numlist link unlink code | undo redo',
    link_title: true,
    target_list: false,
    rel_list: false,
    link_context_toolbar: false,
    branding: false,
    statusbar: true,
    height: 200,
    resize: true,
    toolbar_mode: 'wrap',
    entity_encoding: 'raw',
    setup(editor) {
        let richTextEditorDialogMatcher;

        const waitForRichTextEditorDialog = (matcher) => {
            if (richTextEditorDialogMatcher)
                pendingRichTextEditorDialogMatchers.delete(richTextEditorDialogMatcher);

            richTextEditorDialogMatcher = matcher;
            pendingRichTextEditorDialogMatchers.add(matcher);
            ensureRichTextEditorAuxObserver();
        };
        editor.on('BeforeExecCommand', (event) => {
            if (event.command === 'mceLink') {
                waitForRichTextEditorDialog((dialog) => dialog.querySelector('input[type="url"]') && dialog.querySelector('input[data-mce-name="text"]'));
            }

            if (event.command === 'mceCodeEditor') {
                waitForRichTextEditorDialog((dialog) => dialog.querySelector('textarea[data-mce-name="code"]'));
            }
        });
        editor.on('remove', () => {
            if (richTextEditorDialogMatcher)
                pendingRichTextEditorDialogMatchers.delete(richTextEditorDialogMatcher);
            stopRichTextEditorAuxObserverWhenIdle();
        });
    }
};

export default {
    name: 'TainacanRichTextEditor',
    components: {
        Editor
    },
    props: {
        modelValue: {
            type: String,
            default: ''
        },
        id: {
            type: String,
            default: undefined
        },
        name: {
            type: String,
            default: undefined
        },
        placeholder: {
            type: String,
            default: ''
        },
        disabled: {
            type: Boolean,
            default: false
        },
        invalid: {
            type: Boolean,
            default: false
        },
        maxLength: {
            type: Number,
            default: undefined
        },
        ariaLabelledby: {
            type: String,
            default: undefined
        },
        ariaDescribedby: {
            type: String,
            default: undefined
        }
    },
    emits: [ 'update:modelValue', 'focus', 'blur' ],
    data() {
        return {
            keyboardHintId: `tainacan-rich-text-editor-keyboard-hint-${++nextKeyboardHintId}`
        };
    },
    computed: {
        iframeAriaDescribedby() {
            return [ this.ariaDescribedby, this.keyboardHintId ].filter(Boolean).join(' ');
        },
        editorInit() {
            return {
                ...EDITOR_INIT,
                placeholder: this.placeholder,
                iframe_attrs: {
                    ...(this.ariaLabelledby
                        ? { 'aria-labelledby': this.ariaLabelledby }
                        : {}),
                    ...(this.iframeAriaDescribedby
                        ? { 'aria-describedby': this.iframeAriaDescribedby }
                        : {})
                }
            };
        }
    },
    methods: {
        getTextContentLength(editor) {
            return editor.getContent({ format: 'text' }).length;
        },
        hasExceededMaxLength(editor) {
            return this.maxLength && this.getTextContentLength(editor) > this.maxLength;
        },
        onBeforeAddUndo(event, editor) {
            if (this.hasExceededMaxLength(editor))
                event.preventDefault();
        },
        onUpdate(value) {
            const editor = this.$refs.editor && this.$refs.editor.getEditor();

            if (editor && this.hasExceededMaxLength(editor)) {
                editor.setContent(this.modelValue);
                return;
            }

            this.$emit('update:modelValue', value);
        },
        onFocus(event) {
            this.$emit('focus', event);
        },
        onBlur(event) {
            this.$emit('blur', event);
        }
    }
};
</script>

<style lang="scss">
.tainacan-rich-text-editor {
    width: 100%;

    .tox .tox-toolbar__group {
        padding: 0;
    }

    .tox.tox-tinymce {
        border-radius: var(--tainacan-input-border-radius, 2px);
        border: 1px solid var(--tainacan-input-border-color);
    }

    .tox:not(.tox-tinymce-inline) .tox-editor-header {
        padding: 0 6px;
        box-shadow: none;
        border-bottom: 1px solid var(--tainacan-input-border-color);
    }

    .tox .tox-toolbar {
        justify-content: space-between;
    }

    .tox .tox-statusbar {
        border-color: var(--tainacan-input-border-color);
    }

    .tox .tox-tbtn {
        height: auto;
        width: auto;
        transform: scale(0.9);
        padding-inline-end: 2px;
    }

    .tox .tox-tbtn--active,
    .tox .tox-tbtn--enabled,
    .tox .tox-tbtn--enabled:hover,
    .tox .tox-tbtn--enabled:focus {
        background-color: var(--tainacan-primary);
    }

    &:not(.is-invalid) {
        .tox.tox-tinymce:not(.tox-edit-focus) {
            outline: 0 solid transparent;
            outline-offset: 0;
            transition: outline 0.3s ease, outline-offset 0.3s ease;
        }

        .tox.tox-tinymce:hover:not(.tox-edit-focus) {
            outline: 1px solid var(--tainacan-input-color);
            outline-offset: -1px;
            transition: outline 0.3s ease, outline-offset 0.3s ease;
        }

        .tox.tox-edit-focus {
            outline-width: 2px;
            outline-offset: -1px;
            outline-color: var(--tainacan-secondary);
            outline-color: color-mix(in srgb, var(--tainacan-secondary) 60%, var(--tainacan-background-color));
            outline-style: solid;
            box-shadow: none;
            transition: none;
        }

        .tox.tox-tinymce .tox-edit-area::before {
            transition: none;
            border: none;
        }
    }
}

.tainacan-rich-text-editor.is-invalid .tox-tinymce {
    border-color: var(--bulma-danger);
}

.tainacan-rich-text-editor.is-invalid .tox .tox-edit-area::before {
    border-color: var(--bulma-danger);
}

.tox.tox-silver-sink.tox-tinymce-aux:has(.tainacan-rich-text-editor-dialog) {
    z-index: 10000000;
}

.tainacan-rich-text-editor-dialog {
    font-family: var(--tainacan-font-family, inherit);

    .tox-dialog-wrap__backdrop {
        background-color: var(--tainacan-backdrop-background-color, rgba(0, 0, 0, 0.35));
        opacity: 1;
    }

    .tox-dialog {
        background-color: var(--tainacan-background-color);
        border-radius: var(--tainacan-modal-border-radius, 8px);
        box-shadow: var(--tainacan-modal-box-shadow, 0 5px 15px #00000014, 0 15px 27px #00000012, 0 30px 36px #0000000a, 0 50px 43px #00000005);
        color: var(--tainacan-gray5);
    }

    .tox-dialog__header,
    .tox-dialog__footer {
        background-color: var(--tainacan-background-color);
        border: none;
        padding: 20px 24px;
    }

    .tox-dialog__header {
        padding-bottom: 12px;
    }

    .tox-dialog__title {
        color: var(--tainacan-heading-color);
        font-size: 1.25em;
        font-weight: 500;
    }

    .tox-dialog__body-content {
        background-color: var(--tainacan-background-color);
        padding: 12px 24px;
    }

    .tox-label {
        color: var(--tainacan-gray5);
        font-family: var(--tainacan-font-family, inherit);
        font-size: 0.875em;
    }

    .tox-textarea,
    .tox-textfield,
    .tox-listbox {
        background-color: var(--tainacan-input-background-color);
        border: 1px solid var(--tainacan-input-border-color);
        border-radius: var(--tainacan-input-border-radius, 2px);
        box-shadow: none;
        color: var(--tainacan-input-color);
        font-family: var(--tainacan-font-family, inherit);
    }

    .tox-dialog__body-content .tox-textarea-wrap {
        background-color: var(--tainacan-input-background-color);
        border: 1px solid var(--tainacan-input-border-color);
        border-radius: var(--tainacan-input-border-radius, 2px);
        box-shadow: none;
    }

    .tox-textarea:focus,
    .tox-textarea:focus-visible,
    .tox-textfield:focus,
    .tox-textfield:focus-visible,
    .tox-listbox:focus,
    .tox-listbox:focus-visible {
        border-color: var(--tainacan-secondary);
        box-shadow: none;
        outline: 2px solid color-mix(in srgb, var(--tainacan-secondary) 60%, var(--tainacan-background-color));
        outline-offset: -1px;
    }

    .tox-dialog__body-content .tox-textarea-wrap:focus-within {
        border-color: var(--tainacan-secondary);
        box-shadow: none;
        outline: 2px solid color-mix(in srgb, var(--tainacan-secondary) 60%, var(--tainacan-background-color));
        outline-offset: -1px;
    }

    .tox-button {
        border-radius: var(--tainacan-button-border-radius, 4px);
        box-shadow: none;
        font-family: var(--tainacan-font-family, inherit);
        font-weight: normal;
    }

    .tox-dialog__footer .tox-button:not(.tox-button--secondary) {
        background-color: var(--tainacan-success);
        color: var(--tainacan-white);
    }

    .tox-dialog__footer .tox-button--secondary {
        background-color: var(--tainacan-background-color);
        border: 1px solid var(--tainacan-input-border-color);
        color: var(--tainacan-secondary);
    }

    .tox-button:focus-visible {
        box-shadow: none;
        outline: 2px solid color-mix(in srgb, var(--tainacan-secondary) 60%, var(--tainacan-background-color));
        outline-offset: -1px;
    }
}
</style>
