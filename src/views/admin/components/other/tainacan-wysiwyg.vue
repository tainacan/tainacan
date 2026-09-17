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
                :model-value="modelValue"
                :init="editorInit"
                license-key="gpl"
                :disabled="disabled"
                @update:model-value="onUpdate"
                @focus="onFocus"
                @blur="onBlur" />
        <p
                class="tainacan-wysiwyg-keyboard-hint"
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
        let shouldMarkWysiwygMenu = false;
        const wysiwygDialogObserver = new MutationObserver(() => {
            if (wysiwygDialogMatcher) {
                const dialogs = document.querySelectorAll('.tox-dialog-wrap');
                const dialog = dialogs[dialogs.length - 1];

                if (dialog && wysiwygDialogMatcher(dialog)) {
                    dialog.classList.add('tainacan-wysiwyg-dialog');
                    wysiwygDialogMatcher = undefined;
                }
            }

            if (shouldMarkWysiwygMenu) {
                const menus = document.querySelectorAll('.tox-menu');
                const menu = menus[menus.length - 1];

                if (menu && menu.getClientRects().length) {
                    menu.classList.add('tainacan-wysiwyg-menu');
                    shouldMarkWysiwygMenu = false;
                }
            }
        });

        wysiwygDialogObserver.observe(document.body, { childList: true, subtree: true });
        const onToolbarClick = (event) => {
            const button = event.target.closest('button');

            if (button?.dataset.mceName === 'align') {
                shouldMarkWysiwygMenu = true;
            }
        };
        editor.on('init', () => {
            editor.getContainer().addEventListener('click', onToolbarClick);
        });
        editor.on('BeforeExecCommand', (event) => {
            if (event.command === 'mceLink') {
                wysiwygDialogMatcher = (dialog) => dialog.querySelector('input[type="url"]') && dialog.querySelector('input[data-mce-name="text"]');
            }

            if (event.command === 'mceCodeEditor') {
                wysiwygDialogMatcher = (dialog) => dialog.querySelector('textarea[data-mce-name="code"]');
            }
        });
        editor.on('remove', () => {
            wysiwygDialogObserver.disconnect();
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
        onUpdate(value) {
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
    .tainacan-wysiwyg-keyboard-hint {
        margin: 0.25rem 0 0;
        color: var(--tainacan-gray4);
        font-size: 0.75em;
        line-height: 1.4;
    }
</style>
