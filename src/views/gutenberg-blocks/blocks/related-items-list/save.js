const { InnerBlocks, useBlockProps } = wp.blockEditor;

export default function() {
    const blockProps = useBlockProps.save();
    return <div { ...blockProps } data-module="related-items-list"><InnerBlocks.Content /></div>
};