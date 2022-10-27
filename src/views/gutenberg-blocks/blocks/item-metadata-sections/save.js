const { useBlockProps, InnerBlocks } = (tainacan_blocks.wp_version < '5.2' ? wp.editor : wp.blockEditor );

export default function({ attributes }) {
    const { textAlign, isDynamic } = attributes;

    // Gets blocks props from hook
    const blockProps = tainacan_blocks.wp_version < '5.6' ? { className: `has-text-align-${ textAlign }` } : useBlockProps.save( {
        className: `has-text-align-${ textAlign }`
    } );
    return isDynamic ? null : <div { ...blockProps }><InnerBlocks.Content /></div>
};