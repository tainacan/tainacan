const { __ } = wp.i18n;

const { useBlockProps, BlockControls, AlignmentControl } = wp.blockEditor;

export default function ({ attributes, setAttributes, className, context }) {
    
    let {
        content, 
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

    if (context['tainacan/metadataSectionId'])
        sectionId = context['tainacan/metadataSectionId'];

    if (context['tainacan/metadataSectionDescription'])
        sectionDescription = context['tainacan/metadataSectionDescription'];

    if ( context['tainacan/metadataSectionId'] || context['tainacan/metadataSectionDescription'] )
        setAttributes({ sectionId, sectionDescription });

    return content == 'preview' ? 
            <div className={className}>
                <img
                        width="100%"
                        src={ `${tainacan_blocks.base_url}/assets/images/related-carousel-items.png` } />
            </div>
        : (
        <>
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
        </>
    );
};