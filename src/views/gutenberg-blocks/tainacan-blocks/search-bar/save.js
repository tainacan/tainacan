const { useBlockProps } = (tainacan_blocks.wp_version < '5.2' ? wp.editor : wp.blockEditor );

export default function({ attributes, className }) {
    const { content } = attributes;
    
    // Gets attributes such as style, that are automatically added by the editor hook
    const blockProps = tainacan_blocks.wp_version < '5.6' ? { className: className } : useBlockProps.save();
    return <div { ...blockProps } data-module="search-bar">{ content }</div>
}