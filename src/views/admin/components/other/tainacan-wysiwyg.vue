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
    toolbar: 'bold italic bullist numlist link | undo redo',
    // block_formats: 'Paragraph=p;Heading 1=h1;Heading 2=h2;Heading 3=h3;Heading 4=h4;Heading 5=h5;Heading 6=h6',
    // valid_elements: 'p,h1,h2,h3,h4,h5,h6,ul,ol,li,br,strong,em,b,i,a[href|title]',
    link_title: true,
    target_list: false,
    rel_list: false,
    link_context_toolbar: true,
    branding: false,
    statusbar: false,
    resize: false,
    toolbar_mode: 'wrap'
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

    &:not(.is-invalid) {
        :deep(.tox.tox-edit-focus) {
            // border: 1px solid var(--tainacan-secondary) !important;
            outline-width: 2px;
            outline-offset: -1px;
            outline-color: var(--tainacan-secondary);
            outline-color: color-mix(in srgb, var(--tainacan-secondary) 60%, var(--tainacan-background-color));
            outline-style: solid;
            box-shadow: none !important;
        }

        :deep(.tox.tox-edit-focus .tox-edit-area::before) {
        //    border-color: var(--tainacan-secondary) !important;
        //    border: 3px solid color-mix(in srgb, var(--tainacan-secondary) 60%, var(--tainacan-background-color))
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
