const { __ } = wp.i18n;
const { useEffect } = wp.element;
const { useBlockProps, BlockControls, AlignmentControl } = wp.blockEditor;

export default function ({ attributes, setAttributes, context }) {
    
    let {
        sectionId,
        sectionDescription,
        textAlign
    } = attributes;

    // Gets blocks props from hook
    const blockProps = useBlockProps( {
		className: {
			[ `has-text-align-${ textAlign }` ]: textAlign,
		}
	} );
    const className = blockProps.className;

    if (context['tainacan/metadataSectionId'])
        sectionId = context['tainacan/metadataSectionId'];

    if (context['tainacan/metadataSectionDescription'])
        sectionDescription = context['tainacan/metadataSectionDescription'];

    useEffect(() => {
        if ( context['tainacan/metadataSectionId'] || context['tainacan/metadataSectionDescription'] )
            setAttributes({ sectionId, sectionDescription });
    }, [ context ]);

    return <>
            <BlockControls group="block">
                <AlignmentControl
					value={ textAlign }
					onChange={ ( nextAlign ) => {
						setAttributes( { textAlign: nextAlign } );
					} }
				/>
            </BlockControls>
            
            <p
                    { ...blockProps }
                    id={ 'tainacan-metadata-section-description-block-id--' + sectionId }>
                { sectionDescription }
            </p>
        </>;
};