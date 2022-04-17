const { __ } = wp.i18n;

const { Button, Placeholder } = wp.components;

const ServerSideRender = wp.serverSideRender;
const { useBlockProps } = (tainacan_blocks.wp_version < '5.2' ? wp.editor : wp.blockEditor );

import SingleItemMetadatumModal from '../../js/selection/single-item-metadatum-modal.js';

export default function ({ attributes, setAttributes, className, isSelected }) {
    
    let {
        content, 
        collectionId,
        itemId,
        metadatumId,
        metadatumType,
        isModalOpen,
    } = attributes;

    // Gets blocks props from hook
    const blockProps = tainacan_blocks.wp_version < '5.6' ? { className: className } : useBlockProps();
    const currentWPVersion = (typeof tainacan_blocks != 'undefined') ? tainacan_blocks.wp_version : tainacan_plugin.wp_version;

    return content == 'preview' ? 
            <div className={className}>
                <img
                        width="100%"
                        src={ `${tainacan_blocks.base_url}/assets/images/related-carousel-items.png` } />
            </div>
        : (
        <div { ...blockProps }>

            { isSelected ? 
                ( 
                <div>
                    { isModalOpen ?
                        <SingleItemMetadatumModal
                            modalTitle={ __('Select one item to render its metadata', 'tainacan') }
                            existingCollectionId={ collectionId }
                            existingItemId={ itemId }
                            existingMetadatumId={ metadatumId }
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

            { !(collectionId && itemId && metadatumId) ? (
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
                                viewBox="0 0 24 24"
                                height="24px"
                                width="24px">
                            <path d="M16,6H12a2,2,0,0,0-2,2v6.52A6,6,0,0,1,12,19a6,6,0,0,1-.73,2.88A1.92,1.92,0,0,0,12,22h8a2,2,0,0,0,2-2V12Zm-1,6V7.5L19.51,12ZM15,2V4H8v9.33A5.8,5.8,0,0,0,6,13V4A2,2,0,0,1,8,2ZM10.09,19.05,7,22.11V16.05L8,17l2,2ZM5,16.05v6.06L2,19.11Z"/>
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
            
            { (collectionId && itemId && metadatumId) ? (
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