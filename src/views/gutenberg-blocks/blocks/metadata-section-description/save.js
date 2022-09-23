const { useBlockProps } = (tainacan_blocks.wp_version < '5.2' ? wp.editor : wp.blockEditor );

export default function({ attributes }) {
    const { textAlign, style, sectionId, sectionDescription } = attributes;

    // Gets blocks props from hook
    const blockProps = tainacan_blocks.wp_version < '5.6' ? { className: `has-text-align-${ textAlign }`, style } : useBlockProps.save( {
        className: `has-text-align-${ textAlign }`,
        style,
    } );
	return (
		<p { ...blockProps } id={ 'tainacan-metadata-section-description-block-id--' + sectionId }>
			{ sectionDescription }
		</p>
	);
};