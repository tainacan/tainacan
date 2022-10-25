const { useBlockProps, InnerBlocks } = (tainacan_blocks.wp_version < '5.2' ? wp.editor : wp.blockEditor );

export default function({ className, attributes }) {
    const blockProps = tainacan_blocks.wp_version < '5.6' ? { className: className } : useBlockProps.save();
    
    return attributes.isDynamic ? null : <div { ...blockProps }><InnerBlocks.Content /></div>
};