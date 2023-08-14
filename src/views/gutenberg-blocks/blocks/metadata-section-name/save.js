const { useBlockProps } = wp.blockEditor;

export default function({ attributes }) {
    const { textAlign, sectionId, sectionName, labelLevel } = attributes;
	const TagName = 'h' + labelLevel;

    // Gets blocks props from hook
    const blockProps = useBlockProps.save( {
        className: `has-text-align-${ textAlign }`
    } );
	return (
		<TagName { ...blockProps } id={ 'tainacan-metadata-section-name-block-id--' + sectionId }>
			{ sectionName }
		</TagName>
	);
};