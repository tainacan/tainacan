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
import './tainacan-tinymce-skin.scss';
import contentCss from 'tinymce/skins/content/default/content.css';
import contentUiCss from 'tinymce/skins/ui/oxide/content.css';

let nextKeyboardHintId = 0;
const pendingRichTextEditorDialogMatchers = new Set();
const pendingRichTextEditorMenuMarkers = new Set();
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

    if (pendingRichTextEditorMenuMarkers.size) {
        const menus = document.querySelectorAll('.tox-menu');
        const menu = menus[menus.length - 1];

        if (menu && menu.getClientRects().length) {
            menu.classList.add('tainacan-rich-text-editor-menu');
            pendingRichTextEditorMenuMarkers.clear();
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
    if (!richTextEditorAuxObserver || pendingRichTextEditorDialogMatchers.size || pendingRichTextEditorMenuMarkers.size)
        return;

    richTextEditorAuxObserver.disconnect();
    richTextEditorAuxObserver = undefined;
}

const EDITOR_INIT = {
    menubar: false,
    plugins: 'link lists code',
    skin: false,
    content_css: false,
    content_style: `${contentCss}\n${contentUiCss}`,
    toolbar: 'bold italic align bullist numlist link unlink code | undo redo',
    link_title: true,
    target_list: false,
    rel_list: false,
    link_context_toolbar: false,
    branding: false,
    statusbar: true,
    height: 200,
    resize: true,
    toolbar_mode: 'wrap',
    setup(editor) {
        let richTextEditorDialogMatcher;
        let richTextEditorMenuMarker;

        const waitForRichTextEditorDialog = (matcher) => {
            if (richTextEditorDialogMatcher)
                pendingRichTextEditorDialogMatchers.delete(richTextEditorDialogMatcher);

            richTextEditorDialogMatcher = matcher;
            pendingRichTextEditorDialogMatchers.add(matcher);
            ensureRichTextEditorAuxObserver();
        };
        const onToolbarClick = (event) => {
            const button = event.target.closest('button');

            if (button?.dataset.mceName === 'align') {
                if (richTextEditorMenuMarker)
                    pendingRichTextEditorMenuMarkers.delete(richTextEditorMenuMarker);

                richTextEditorMenuMarker = {};
                pendingRichTextEditorMenuMarkers.add(richTextEditorMenuMarker);
                ensureRichTextEditorAuxObserver();
            }
        };
        editor.on('init', () => {
            editor.getContainer().addEventListener('click', onToolbarClick);
        });
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
            if (richTextEditorMenuMarker)
                pendingRichTextEditorMenuMarkers.delete(richTextEditorMenuMarker);
            stopRichTextEditorAuxObserverWhenIdle();
            editor.getContainer().removeEventListener('click', onToolbarClick);
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
