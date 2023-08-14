const { useBlockProps, InnerBlocks } = wp.blockEditor;

export default function({ attributes }) {
    const { textAlign, isDynamic } = attributes;

    // Gets blocks props from hook
    const blockProps = useBlockProps.save( {
        className: `has-text-align-${ textAlign }`
    } );
    return isDynamic ? null : <div { ...blockProps }><InnerBlocks.Content /></div>
};