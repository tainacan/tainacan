const { __ } = wp.i18n;

const { Spinner, Button, Placeholder } = wp.components;

const { InnerBlocks } = (tainacan_blocks.wp_version < '5.2' ? wp.editor : wp.blockEditor );

import RelatedItemsModal from './related-items-modal.js';
import tainacan from '../../js/axios.js';
import axios from 'axios';

export default function ({ attributes, setAttributes, className, isSelected }) {
    
    let {
        content, 
        collectionId,
        itemId,
        isModalOpen,
        isLoading,
    } = attributes;

    function setContent(){
    }
    
    function openRelatedItemsModal() {
        isModalOpen = true;
        setAttributes( { 
            isModalOpen: isModalOpen
        } );
    }

    // Executed only on the first load of page
    if(content && content.length && content[0].type)
        setContent();

    return content == 'preview' ? 
            <div className={className}>
                <img
                        width="100%"
                        src={ `${tainacan_blocks.base_url}/assets/images/related-carousel-items.png` } />
            </div>
        : (
        <div className={className}>

            { isSelected ? 
                ( 
                <div>
                    { isModalOpen ?   
                        <RelatedItemsModal
                            existingCollectionId={ collectionId }
                            existingItemId={ itemId }
                            onSelectCollection={ (selectedCollectionId) => {
                                // if (collectionId != selectedCollectionId)
                                //     relatedItems = [];
                                
                                collectionId = selectedCollectionId;
                                setAttributes({ 
                                    collectionId: collectionId,
                                    // relatedItems: relatedItems
                                });
                            }}
                            onApplyRelatedItem={ (selectedItemId) => {
                                // if (itemId != selectedItemId) {
                                //     relatedItems = [];
                                //     relatedItemsTemplate = [];
                                // }
                                
                                itemId = selectedItemId;
                                setAttributes({
                                    itemId: itemId,
                                    // relatedItems: relatedItems,
                                    // relatedItemsTemplate: relatedItemsTemplate,
                                    isModalOpen: false
                                });
                                setContent();
                            }}
                            onCancelSelection={ () => setAttributes({ isModalOpen: false }) }/> 
                        : null
                    }
                    
                </div>
                ) : null
            }

            { !itemId && !isLoading ? (
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
                        {__('Select an item to create a set of lists with items related to it via relationship metadata.', 'tainacan')}
                    </p>
                    <Button
                        isPrimary
                        type="button"
                        onClick={ () => openRelatedItemsModal() }>
                        {__('Select Item', 'tainacan')}
                    </Button>
                </Placeholder>
                ) : null
            }
            
            { isLoading ? 
                <div class="spinner-container">
                    <Spinner />
                </div> :
                <div>
                    {  itemId ? (

                        <div className={ 'item-gallery-edit-container' }>

                        </div>
                        ) : null
                    }
                </div>
            }
            
        </div>
    );
};