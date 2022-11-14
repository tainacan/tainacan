const { useBlockProps } = (tainacan_blocks.wp_version < '5.2' ? wp.editor : wp.blockEditor );

export default function({ attributes }) {
    const { textAlign, sectionId, sectionDescription } = attributes;

    // Gets blocks props from hook
    const blockProps = tainacan_blocks.wp_version < '5.6' ? { className: `has-text-align-${ textAlign }` } : useBlockProps.save( {
        className: `has-text-align-${ textAlign }`
    } );
	return (
		<p { ...blockProps } id={ 'tainacan-metadata-section-description-block-id--' + sectionId }>
			{ sectionDescription }
		</p>
	);
};