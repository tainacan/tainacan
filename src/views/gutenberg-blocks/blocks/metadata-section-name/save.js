const { useBlockProps, InnerBlocks } = (tainacan_blocks.wp_version < '5.2' ? wp.editor : wp.blockEditor );

export default function({ attributes, className }) {
    const blockProps = tainacan_blocks.wp_version < '5.6' ? { className: className } : useBlockProps.save();
    
    switch (attributes.labelLevel) {
        case 1: return <h1 { ...blockProps }>{ attributes.sectionName }</h1>;
        case 2: return <h2 { ...blockProps }>{ attributes.sectionName }</h2>;
        case 3: return <h3 { ...blockProps }>{ attributes.sectionName }</h3>;
        case 4: return <h4 { ...blockProps }>{ attributes.sectionName }</h4>;
        case 5: return <h5 { ...blockProps }>{ attributes.sectionName }</h5>;
        case 6: return <h6 { ...blockProps }>{ attributes.sectionName }</h6>;
        default: return <h2 { ...blockProps }>{ attributes.sectionName }</h2>;
    }
    
};