<template>
    <div class="tainacan-wysiwyg">
        <Editor
                :id="id"
                :model-value="modelValue"
                :init="editorInit"
                :disabled="disabled"
                :aria-describedby="ariaDescribedby"
                @update:model-value="onUpdate"
                @on-focus="onFocus"
                @on-blur="onBlur" />
    </div>
</template>

<script>
import { Editor } from '@tinymce/tinymce-vue';
import 'tinymce/tinymce';
import 'tinymce/icons/default';
import 'tinymce/models/dom';
import 'tinymce/themes/silver';
import 'tinymce/plugins/link';
import 'tinymce/plugins/lists';
import 'tinymce/skins/ui/oxide/skin.css';
import 'tinymce/skins/content/default/content.css';

const EDITOR_INIT = {
    menubar: false,
    plugins: 'link lists',
    toolbar: 'undo redo | blocks | bold italic | bullist numlist | link',
    block_formats: 'Paragraph=p;Heading 1=h1;Heading 2=h2;Heading 3=h3;Heading 4=h4;Heading 5=h5;Heading 6=h6',
    valid_elements: 'p,h1,h2,h3,h4,h5,h6,ul,ol,li,br,strong,em,b,i,a[href|title]',
    link_title: true,
    target_list: false,
    rel_list: false,
    link_context_toolbar: false,
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
                placeholder: this.placeholder
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
}
</style>
