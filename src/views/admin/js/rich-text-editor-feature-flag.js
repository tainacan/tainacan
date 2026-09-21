export const isRichTextEditorAllowed = () => (
    typeof window !== 'undefined' &&
    window.tainacan_plugin &&
    window.tainacan_plugin.tainacan_allow_rich_text_editor === '1'
);
