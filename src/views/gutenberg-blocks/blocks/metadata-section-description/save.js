const { useBlockProps } = wp.blockEditor;

export default function({ attributes }) {
    const { textAlign, sectionId, sectionDescription } = attributes;

    // Gets blocks props from hook
    const blockProps = useBlockProps.save( {
        className: `has-text-align-${ textAlign }`
    } );
	return (
		<p { ...blockProps } id={ 'tainacan-metadata-section-description-block-id--' + sectionId }>
			{ sectionDescription }
		</p>
	);
};