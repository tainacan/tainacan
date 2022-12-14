const { __ } = wp.i18n;
const { Button, Placeholder, ToolbarDropdownMenu, SVG, Path } = wp.components;

const ServerSideRender = wp.serverSideRender;
const { useBlockProps, BlockControls, AlignmentControl } = (tainacan_blocks.wp_version < '5.2' ? wp.editor : wp.blockEditor );

import SingleItemMetadatumModal from '../../js/selection/single-item-metadatum-modal.js';
import TainacanBlocksCompatToolbar from '../../js/compatibility/tainacan-blocks-compat-toolbar.js';
import getCollectionIdFromPossibleTemplateEdition from '../../js/template/tainacan-blocks-single-item-template-mode.js';

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