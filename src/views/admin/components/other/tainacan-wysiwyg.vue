<template>
    <div
            class="tainacan-wysiwyg"
            :class="{ 'is-invalid': invalid }">
        <Editor
                :id="id"
                :model-value="modelValue"
                :init="editorInit"
                license-key="gpl"
                :disabled="disabled"
                @update:model-value="onUpdate"
                @focus="onFocus"
                @blur="onBlur" />
    </div>
</template>

<script>
import Editor from '@tinymce/tinymce-vue';
import 'tinymce/tinymce';
import 'tinymce/icons/default';
import 'tinymce/models/dom';
import 'tinymce/themes/silver';
import 'tinymce/plugins/link';
import 'tinymce/plugins/lists';
import 'tinymce/skins/ui/oxide/skin.css';
import contentCss from 'tinymce/skins/content/default/content.css';
import contentUiCss from 'tinymce/skins/ui/oxide/content.css';

const EDITOR_INIT = {
    menubar: false,
    plugins: 'link lists',
    skin: false,
    content_css: false,
    content_style: `${contentCss}\n${contentUiCss}`,
    toolbar: 'bold italic align bullist numlist link unlink | undo redo',
    // block_formats: 'Paragraph=p;Heading 1=h1;Heading 2=h2;Heading 3=h3;Heading 4=h4;Heading 5=h5;Heading 6=h6',
    // valid_elements: 'p,h1,h2,h3,h4,h5,h6,ul,ol,li,br,strong,em,b,i,a[href|title]',
    link_title: true,
    target_list: false,
    rel_list: false,
    link_context_toolbar: false,
    branding: false,
    statusbar: false,
    resize: false,
    toolbar_mode: 'wrap',
    setup(editor) {
        let linkDialogPending = false;
        const linkDialogObserver = new MutationObserver(() => {
            if (!linkDialogPending) {
                return;
            }

            const dialogs = document.querySelectorAll('.tox-dialog-wrap');
            const dialog = dialogs[dialogs.length - 1];

            if (dialog?.querySelector('input[type="url"]') && dialog.querySelector('input[data-mce-name="text"]')) {
                dialog.classList.add('tainacan-wysiwyg-link-dialog');
                linkDialogPending = false;
            }
        });

        linkDialogObserver.observe(document.body, { childList: true, subtree: true });
        editor.on('BeforeExecCommand', (event) => {
            linkDialogPending = event.command === 'mceLink';
        });
        editor.on('remove', () => {
            linkDialogObserver.disconnect();
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
            editorInit: {
                ...EDITOR_INIT,
                placeholder: this.placeholder,
                iframe_attrs: {
                    ...(this.ariaLabelledby
                        ? { 'aria-labelledby': this.ariaLabelledby }
                        : {}),
                    ...(this.ariaDescribedby
                        ? { 'aria-describedby': this.ariaDescribedby }
                        : {})
                }
            }
        };
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

<style lang="scss" scoped>
.tainacan-wysiwyg {
    width: 100%;

    :deep(.tox .tox-toolbar__group) {
        padding: 0;
    }

    :deep(.tox:not(.tox-tinymce-inline) .tox-editor-header) {
        padding: 0 6px;
    }

    :deep(.tox .tox-toolbar) {
        justify-content: space-between;
    }

    :deep(.tox .tox-tbtn) {
        height: auto !important;
        width: auto !important;
        transform: scale(0.9);
        padding-inline-end: 2px;
    }

    :deep(.tox .tox-tbtn--active), 
    :deep(.tox .tox-tbtn--enabled), 
    :deep(.tox .tox-tbtn--enabled:hover), 
    :deep(.tox .tox-tbtn--enabled:focus){
        background-color: var(--tainacan-primary);
    }
 
    &:not(.is-invalid) {
        :deep(.tox.tox-tinymce) {
            outline: 0px solid transparent;
            outline-offset: -1px;
            transition: outline 0.3s ease, outline-offset 0.3s ease;
        }
        :deep(.tox.tox-tinymce:hover:not(.tox-edit-focus)) {
            outline: 1px solid var(--tainacan-input-color);
            outline-offset: -1px;
        }

        :deep(.tox.tox-edit-focus) {
            outline-width: 2px;
            outline-offset: -1px;
            outline-color: var(--tainacan-secondary);
            outline-color: color-mix(in srgb, var(--tainacan-secondary) 60%, var(--tainacan-background-color));
            outline-style: solid;
            box-shadow: none !important;
        }

        :deep(.tox.tox-edit-focus .tox-edit-area::before) {
            border: none;
        }
    }
}

.tainacan-wysiwyg.is-invalid :deep(.tox-tinymce) {
    border-color: var(--bulma-danger);
}

.tainacan-wysiwyg.is-invalid :deep(.tox .tox-edit-area::before) {
    border-color: var(--bulma-danger) !important;
}
</style>

<style lang="scss">
.tainacan-wysiwyg-link-dialog {
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

    .tox-textfield,
    .tox-listbox {
        background-color: var(--tainacan-input-background-color) !important;
        border: 1px solid var(--tainacan-input-border-color) !important;
        border-radius: var(--tainacan-input-border-radius, 2px);
        box-shadow: none !important;
        color: var(--tainacan-input-color) !important;
        font-family: var(--tainacan-font-family, inherit);
    }

    .tox-textfield:focus,
    .tox-textfield:focus-visible,
    .tox-listbox:focus,
    .tox-listbox:focus-visible {
        border-color: var(--tainacan-secondary) !important;
        box-shadow: none !important;
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
        background-color: var(--tainacan-success) !important;
        color: var(--tainacan-white) !important;
    }

    .tox-dialog__footer .tox-button--secondary {
        background-color: var(--tainacan-background-color) !important;
        border: 1px solid var(--tainacan-input-border-color) !important;
        color: var(--tainacan-secondary) !important;
    }

    .tox-button:focus-visible {
        box-shadow: none;
        outline: 2px solid color-mix(in srgb, var(--tainacan-secondary) 60%, var(--tainacan-background-color));
        outline-offset: -1px;
    }
}
</style>
