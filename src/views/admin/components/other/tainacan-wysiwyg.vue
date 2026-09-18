<template>
    <div
            class="tainacan-wysiwyg"
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
            {{ $i18n.get('instruction_wysiwyg_toolbar_shortcut') }}
        </p>
        <p
                :id="keyboardHintId"
                class="sr-only">
            {{ $i18n.get('instruction_wysiwyg_toolbar_shortcut_screen_reader') }}
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
const pendingWysiwygDialogMatchers = new Set();
const pendingWysiwygMenuMarkers = new Set();
let wysiwygAuxObserver;

function processPendingWysiwygAux() {
    if (pendingWysiwygDialogMatchers.size) {
        const dialogs = document.querySelectorAll('.tox-dialog-wrap');
        const dialog = dialogs[dialogs.length - 1];

        if (dialog) {
            for (const matcher of pendingWysiwygDialogMatchers) {
                if (matcher(dialog)) {
                    dialog.classList.add('tainacan-wysiwyg-dialog');
                    pendingWysiwygDialogMatchers.delete(matcher);
                }
            }
        }
    }

    if (pendingWysiwygMenuMarkers.size) {
        const menus = document.querySelectorAll('.tox-menu');
        const menu = menus[menus.length - 1];

        if (menu && menu.getClientRects().length) {
            menu.classList.add('tainacan-wysiwyg-menu');
            pendingWysiwygMenuMarkers.clear();
        }
    }

    stopWysiwygAuxObserverWhenIdle();
}

function ensureWysiwygAuxObserver() {
    if (wysiwygAuxObserver)
        return;

    wysiwygAuxObserver = new MutationObserver(processPendingWysiwygAux);
    wysiwygAuxObserver.observe(document.body, { childList: true, subtree: true });
    processPendingWysiwygAux();
}

function stopWysiwygAuxObserverWhenIdle() {
    if (!wysiwygAuxObserver || pendingWysiwygDialogMatchers.size || pendingWysiwygMenuMarkers.size)
        return;

    wysiwygAuxObserver.disconnect();
    wysiwygAuxObserver = undefined;
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
    statusbar: false,
    height: 200,
    resize: false,
    toolbar_mode: 'wrap',
    setup(editor) {
        let wysiwygDialogMatcher;
        let wysiwygMenuMarker;

        const waitForWysiwygDialog = (matcher) => {
            if (wysiwygDialogMatcher)
                pendingWysiwygDialogMatchers.delete(wysiwygDialogMatcher);

            wysiwygDialogMatcher = matcher;
            pendingWysiwygDialogMatchers.add(matcher);
            ensureWysiwygAuxObserver();
        };
        const onToolbarClick = (event) => {
            const button = event.target.closest('button');

            if (button?.dataset.mceName === 'align') {
                if (wysiwygMenuMarker)
                    pendingWysiwygMenuMarkers.delete(wysiwygMenuMarker);

                wysiwygMenuMarker = {};
                pendingWysiwygMenuMarkers.add(wysiwygMenuMarker);
                ensureWysiwygAuxObserver();
            }
        };
        editor.on('init', () => {
            editor.getContainer().addEventListener('click', onToolbarClick);
        });
        editor.on('BeforeExecCommand', (event) => {
            if (event.command === 'mceLink') {
                waitForWysiwygDialog((dialog) => dialog.querySelector('input[type="url"]') && dialog.querySelector('input[data-mce-name="text"]'));
            }

            if (event.command === 'mceCodeEditor') {
                waitForWysiwygDialog((dialog) => dialog.querySelector('textarea[data-mce-name="code"]'));
            }
        });
        editor.on('remove', () => {
            if (wysiwygDialogMatcher)
                pendingWysiwygDialogMatchers.delete(wysiwygDialogMatcher);
            if (wysiwygMenuMarker)
                pendingWysiwygMenuMarkers.delete(wysiwygMenuMarker);
            stopWysiwygAuxObserverWhenIdle();
            editor.getContainer().removeEventListener('click', onToolbarClick);
        });
    }
};

export default {
    name: 'TainacanWysiwyg',
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
            keyboardHintId: `tainacan-wysiwyg-keyboard-hint-${++nextKeyboardHintId}`
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
