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
import './tainacan-tinymce-skin.scss';
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
