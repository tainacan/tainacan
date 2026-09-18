export const isWysiwygEditorAllowed = () => (
    typeof window !== 'undefined' &&
    window.tainacan_plugin &&
    window.tainacan_plugin.tainacan_allow_wysiwyg_editor === '1'
);
