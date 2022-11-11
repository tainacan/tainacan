const { __ } = wp.i18n;
const { Button, Placeholder, ToolbarDropdownMenu, SVG, Path } = wp.components;

const ServerSideRender = wp.serverSideRender;
const { useBlockProps, BlockControls, AlignmentControl } = (tainacan_blocks.wp_version < '5.2' ? wp.editor : wp.blockEditor );

import SingleItemMetadatumModal from '../../js/selection/single-item-metadatum-modal.js';
import TainacanBlocksCompatToolbar from '../../js/compatibility/tainacan-blocks-compat-toolbar.js';
import getCollectionIdFromPossibleTemplateEdition from '../../js/template/tainacan-blocks-single-item-template-mode.js';

const levelToPath = {
    1: 'M9 5h2v10H9v-4H5v4H3V5h2v4h4V5zm6.6 0c-.6.9-1.5 1.7-2.6 2v1h2v7h2V5h-1.4z',
    2: 'M7 5h2v10H7v-4H3v4H1V5h2v4h4V5zm8 8c.5-.4.6-.6 1.1-1.1.4-.4.8-.8 1.2-1.3.3-.4.6-.8.9-1.3.2-.4.3-.8.3-1.3 0-.4-.1-.9-.3-1.3-.2-.4-.4-.7-.8-1-.3-.3-.7-.5-1.2-.6-.5-.2-1-.2-1.5-.2-.4 0-.7 0-1.1.1-.3.1-.7.2-1 .3-.3.1-.6.3-.9.5-.3.2-.6.4-.8.7l1.2 1.2c.3-.3.6-.5 1-.7.4-.2.7-.3 1.2-.3s.9.1 1.3.4c.3.3.5.7.5 1.1 0 .4-.1.8-.4 1.1-.3.5-.6.9-1 1.2-.4.4-1 .9-1.6 1.4-.6.5-1.4 1.1-2.2 1.6V15h8v-2H15z',
    3: 'M12.1 12.2c.4.3.8.5 1.2.7.4.2.9.3 1.4.3.5 0 1-.1 1.4-.3.3-.1.5-.5.5-.8 0-.2 0-.4-.1-.6-.1-.2-.3-.3-.5-.4-.3-.1-.7-.2-1-.3-.5-.1-1-.1-1.5-.1V9.1c.7.1 1.5-.1 2.2-.4.4-.2.6-.5.6-.9 0-.3-.1-.6-.4-.8-.3-.2-.7-.3-1.1-.3-.4 0-.8.1-1.1.3-.4.2-.7.4-1.1.6l-1.2-1.4c.5-.4 1.1-.7 1.6-.9.5-.2 1.2-.3 1.8-.3.5 0 1 .1 1.6.2.4.1.8.3 1.2.5.3.2.6.5.8.8.2.3.3.7.3 1.1 0 .5-.2.9-.5 1.3-.4.4-.9.7-1.5.9v.1c.6.1 1.2.4 1.6.8.4.4.7.9.7 1.5 0 .4-.1.8-.3 1.2-.2.4-.5.7-.9.9-.4.3-.9.4-1.3.5-.5.1-1 .2-1.6.2-.8 0-1.6-.1-2.3-.4-.6-.2-1.1-.6-1.6-1l1.1-1.4zM7 9H3V5H1v10h2v-4h4v4h2V5H7v4z',
    4: 'M9 15H7v-4H3v4H1V5h2v4h4V5h2v10zm10-2h-1v2h-2v-2h-5v-2l4-6h3v6h1v2zm-3-2V7l-2.8 4H16z',
    5: 'M12.1 12.2c.4.3.7.5 1.1.7.4.2.9.3 1.3.3.5 0 1-.1 1.4-.4.4-.3.6-.7.6-1.1 0-.4-.2-.9-.6-1.1-.4-.3-.9-.4-1.4-.4H14c-.1 0-.3 0-.4.1l-.4.1-.5.2-1-.6.3-5h6.4v1.9h-4.3L14 8.8c.2-.1.5-.1.7-.2.2 0 .5-.1.7-.1.5 0 .9.1 1.4.2.4.1.8.3 1.1.6.3.2.6.6.8.9.2.4.3.9.3 1.4 0 .5-.1 1-.3 1.4-.2.4-.5.8-.9 1.1-.4.3-.8.5-1.3.7-.5.2-1 .3-1.5.3-.8 0-1.6-.1-2.3-.4-.6-.2-1.1-.6-1.6-1-.1-.1 1-1.5 1-1.5zM9 15H7v-4H3v4H1V5h2v4h4V5h2v10z',
    6: 'M9 15H7v-4H3v4H1V5h2v4h4V5h2v10zm8.6-7.5c-.2-.2-.5-.4-.8-.5-.6-.2-1.3-.2-1.9 0-.3.1-.6.3-.8.5l-.6.9c-.2.5-.2.9-.2 1.4.4-.3.8-.6 1.2-.8.4-.2.8-.3 1.3-.3.4 0 .8 0 1.2.2.4.1.7.3 1 .6.3.3.5.6.7.9.2.4.3.8.3 1.3s-.1.9-.3 1.4c-.2.4-.5.7-.8 1-.4.3-.8.5-1.2.6-1 .3-2 .3-3 0-.5-.2-1-.5-1.4-.9-.4-.4-.8-.9-1-1.5-.2-.6-.3-1.3-.3-2.1s.1-1.6.4-2.3c.2-.6.6-1.2 1-1.6.4-.4.9-.7 1.4-.9.6-.3 1.1-.4 1.7-.4.7 0 1.4.1 2 .3.5.2 1 .5 1.4.8 0 .1-1.3 1.4-1.3 1.4zm-2.4 5.8c.2 0 .4 0 .6-.1.2 0 .4-.1.5-.2.1-.1.3-.3.4-.5.1-.2.1-.5.1-.7 0-.4-.1-.8-.4-1.1-.3-.2-.7-.3-1.1-.3-.3 0-.7.1-1 .2-.4.2-.7.4-1 .7 0 .3.1.7.3 1 .1.2.3.4.4.6.2.1.3.3.5.3.2.1.5.2.7.1z',
};

export default function ({ attributes, setAttributes, className, isSelected }) {
    
    let {
        content, 
        collectionId,
        itemId,
        metadatumId,
        metadatumType,
        isModalOpen,
        dataSource,
        templateMode,
        labelLevel,
        textAlign
    } = attributes;
    
    // Gets blocks props from hook
    const blockProps = tainacan_blocks.wp_version < '5.6' ? { className: className } : useBlockProps( {
		className: {
			[ `has-text-align-${ textAlign }` ]: textAlign,
		}
	} );
    const currentWPVersion = (typeof tainacan_blocks != 'undefined') ? tainacan_blocks.wp_version : tainacan_plugin.wp_version;

    // Checks if we are in template mode, if so, gets the collection Id from URL.
    if ( !templateMode ) {
        const possibleCollectionId = getCollectionIdFromPossibleTemplateEdition();
        if (possibleCollectionId) {
            collectionId = possibleCollectionId;
            templateMode = true
            setAttributes({ 
                collectionId: collectionId,
                templateMode: templateMode
            });
        }
    }

    return content == 'preview' ? 
            <div className={className}>
                <img
                        width="100%"
                        src={ `${tainacan_blocks.base_url}/assets/images/related-carousel-items.png` } />
            </div>
        : (
        <div { ...blockProps }>

            <BlockControls group="block">
                <ToolbarDropdownMenu
                    popoverProps={{
                        className: 'block-library-heading-level-dropdown',
                    }}
                    icon={ (
                        <SVG
                            width="24"
                            height="24"
                            viewBox="0 0 20 20"
                            xmlns="http://www.w3.org/2000/svg"
                        >
                            <Path d={ levelToPath[ labelLevel ] } />
                        </SVG>
                    ) }
                    label={ __( 'Change heading level', 'tainacan' ) }
                    controls={ [ 1, 2, 3, 4, 5, 6 ].map( ( targetLevel ) => {
                        const isActive = targetLevel === labelLevel;
                        return {
                            label: sprintf(
                                // translators: %s: heading level e.g: "1", "2", "3"
                                __( 'Heading %d', 'tainacan' ),
                                targetLevel
                            ),
                            icon: (
                                <SVG
                                    width="24"
                                    height="24"
                                    viewBox="0 0 20 20"
                                    xmlns="http://www.w3.org/2000/svg"
                                    isPressed={ isActive }
                                >
                                    <Path d={ levelToPath[ targetLevel ] } />
                                </SVG>
                            ),
                            isActive,
                            onClick: () => {
                                labelLevel = targetLevel;
                                setAttributes({ labelLevel: labelLevel });
                            }
                        };
                    } ) }
                />
                <AlignmentControl
					value={ textAlign }
					onChange={ ( nextAlign ) => {
						setAttributes( { textAlign: nextAlign } );
					} }
				/>
            </BlockControls>


            { dataSource == 'selection' ? (
                <BlockControls group="other">
                    {
                        TainacanBlocksCompatToolbar({
                            label: templateMode ? __('Select metadatum', 'tainacan') : __('Select item metadatum', 'tainacan'),
                            icon: <svg 
                                    xmlns="http://www.w3.org/2000/svg" 
                                    viewBox="-2 -2 24 24"
                                    height="24px"
                                    width="24px">
                                <path d="m 6,3.9960001 h 5.016 c 0.544,0 1.008,0.192 1.392,0.576 L 19.416,11.58 c 0.384,0.384 0.576,0.856 0.576,1.416 0,0.56 -0.192,1.032 -0.576,1.416 l -4.992,4.992 c -0.176,0.176 -0.392,0.32 -0.648,0.432 -0.24,0.112 -0.496,0.168 -0.768,0.168 -0.272,0 -0.536,-0.056 -0.792,-0.168 -0.24,-0.112 -0.448,-0.256 -0.624,-0.432 L 4.608,12.42 c -0.4,-0.4 -0.6,-0.872 -0.6,-1.416 V 5.988 C 4.008,5.428 4.2,4.956 4.584,4.572 4.968,4.188 5.44,3.996 6,3.9960001 Z m 1.512,4.992 c 0.416,0 0.768,-0.144 1.056,-0.432 C 8.856,8.2680001 9,7.916 9,7.5 9,7.084 8.856,6.732 8.568,6.444 8.28,6.14 7.928,5.988 7.512,5.988 7.096,5.988 6.736,6.14 6.432,6.444 6.144,6.732 6,7.084 6,7.5 c 0,0.416 0.144,0.7680001 0.432,1.0560001 0.304,0.288 0.664,0.432 1.08,0.432 z"/>
                            </svg>,
                            onClick: () => {
                                isModalOpen = true;
                                setAttributes( { 
                                    isModalOpen: isModalOpen
                                });
                            }
                        })
                    }     
                </BlockControls>
                ): null
            }

            { isSelected ? 
                ( 
                <div>
                    { isModalOpen ?
                        <SingleItemMetadatumModal
                            modalTitle={ templateMode ? __('Select one metadatum', 'tainacan') : __('Select one item to render its metadata', 'tainacan') }
                            existingCollectionId={ collectionId }
                            existingItemId={ itemId }
                            existingMetadatumId={ metadatumId }
                            isTemplateMode={ templateMode }
                            onSelectCollection={ (selectedCollectionId) => {
                                collectionId = selectedCollectionId;
                                setAttributes({ 
                                    collectionId: collectionId
                                });
                            }}
                            onSelectItem={ (selectedItemId) => {
                                itemId = selectedItemId;
                                setAttributes({ 
                                    itemId: itemId
                                });
                            }}
                            onApplySelectedMetadatum={ (selectedMetadatum) => {
                                metadatumId = selectedMetadatum.metadatumId;
                                metadatumType = selectedMetadatum.metadatumType;
                                
                                setAttributes({
                                    metadatumId: metadatumId,
                                    metadatumType: metadatumType,
                                    isModalOpen: false
                                });
                            }}
                            onCancelSelection={ () => setAttributes({ isModalOpen: false }) }/> 
                        : null
                    }
                    
                </div>
                ) : null
            }

            { dataSource == 'selection' && !(collectionId && (templateMode || itemId) && metadatumId) ? (
                <Placeholder
                    className="tainacan-block-placeholder"
                    icon={(
                        <img
                            width={148}
                            src={ `${tainacan_blocks.base_url}/assets/images/tainacan_logo_header.svg` }
                            alt="Tainacan Logo"/>
                    )}>
                    <p>
                        <svg 
                                xmlns="http://www.w3.org/2000/svg" 
                                viewBox="-2 -2 24 24"
                                height="24px"
                                width="24px">
                            <path d="m 6,3.9960001 h 5.016 c 0.544,0 1.008,0.192 1.392,0.576 L 19.416,11.58 c 0.384,0.384 0.576,0.856 0.576,1.416 0,0.56 -0.192,1.032 -0.576,1.416 l -4.992,4.992 c -0.176,0.176 -0.392,0.32 -0.648,0.432 -0.24,0.112 -0.496,0.168 -0.768,0.168 -0.272,0 -0.536,-0.056 -0.792,-0.168 -0.24,-0.112 -0.448,-0.256 -0.624,-0.432 L 4.608,12.42 c -0.4,-0.4 -0.6,-0.872 -0.6,-1.416 V 5.988 C 4.008,5.428 4.2,4.956 4.584,4.572 4.968,4.188 5.44,3.996 6,3.9960001 Z m 1.512,4.992 c 0.416,0 0.768,-0.144 1.056,-0.432 C 8.856,8.2680001 9,7.916 9,7.5 9,7.084 8.856,6.732 8.568,6.444 8.28,6.14 7.928,5.988 7.512,5.988 7.096,5.988 6.736,6.14 6.432,6.444 6.144,6.732 6,7.084 6,7.5 c 0,0.416 0.144,0.7680001 0.432,1.0560001 0.304,0.288 0.664,0.432 1.08,0.432 z"/>
                        </svg>
                        {__('Select an item metadata to display its label and value.', 'tainacan')}
                    </p>
                    <Button
                        isPrimary
                        type="button"
                        onClick={ () => {
                                isModalOpen = true;
                                setAttributes( { 
                                    isModalOpen: isModalOpen
                                }); 
                            }
                        }>
                        {__('Select Item Metadatum', 'tainacan')}
                    </Button>
                </Placeholder>
                ) : null
            }
            
            { (collectionId && (itemId || templateMode) && metadatumId) ? (
                <div className={ 'item-metadatum-edit-container' }>
                    <ServerSideRender
                        block="tainacan/item-metadatum"
                        attributes={ attributes }
                        httpMethod={ currentWPVersion >= '5.5' ? 'POST' : 'GET' }
                    />
                </div>
                ) : null
            }
            
        </div>
    );
};