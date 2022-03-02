const { __ } = wp.i18n;

const { Spinner, Button, Placeholder } = wp.components;

const { InnerBlocks } = (tainacan_blocks.wp_version < '5.2' ? wp.editor : wp.blockEditor );

import SingleItemModal from '../../js/selection/single-item-modal.js';
import tainacan from '../../js/axios.js';
import axios from 'axios';

export default function ({ attributes, setAttributes, className, isSelected }) {
    
    let {
        content, 
        collectionId,
        itemId,
        isModalOpen,
        relatedItems,
        isLoading,
        itemRequestSource,
        relatedItemsTemplate,
        itemsListlayout
    } = attributes;

    function setContent(){
        isLoading = true;

        setAttributes({
            isLoading: isLoading
        });

        if (itemRequestSource != undefined && typeof itemRequestSource == 'function')
            itemRequestSource.cancel('Previous items search canceled.');

        itemRequestSource = axios.CancelToken.source();

        let endpoint = '/items/'+ itemId + '?fetch_only=related_items';

        tainacan.get(endpoint, { cancelToken: itemRequestSource.token })
            .then(response => {

                relatedItems = response.data && response.data.related_items ? Object.values(response.data.related_items) : [];
                
                setAttributes({
                    relatedItems: relatedItems,
                    isLoading: false,
                    itemRequestSource: itemRequestSource
                });
                getRelatedItemsTemplates();
            });
    }
    
    function openSingleItemModal() {
        isModalOpen = true;
        setAttributes( { 
            isModalOpen: isModalOpen
        } );
    }

    function getRelatedItemsTemplates() {
        relatedItemsTemplate = [];

        innerItemsList = itemsListlayout !== 'carousel' ?
        [
            'tainacan/dynamic-items-list',
            { 
                content: [{ type: 'innerblock' }],
                selectedItems: collection.items,
                loadStrategy: 'parent',
                collectionId: collection.collection_id,
                layout: itemsListlayout
            }
        ] :
        [
            'tainacan/carousel-items-list',
            { 
                content: [{ type: 'innerblock' }],
                selectedItems: collection.items,
                loadStrategy: 'parent',
                collectionId: collection.collection_id
            }
        ],

        relatedItems.forEach((collection) => {
            
            if (collection.total_items) {
                relatedItemsTemplate.push([
                    'core/group',
                    {},
                    [
                        [ 
                            'core/heading',
                            {
                                placeholder: __( 'Collection name', 'tainacan' ),
                                content: collection.collection_name
                            }
                        ],
                        [
                            'core/paragraph',
                            {
                                placeholder: __( 'Relationship metadatum name', 'tainacan' ),
                                content: collection.metadata_name
                            }
                        ],
                        innerItemsList,
                        [
                            'core/buttons',
                            {},
                            [
                                [
                                    'core/button',
                                    { 
                                        text: __( 'View all related items', 'tainacan' ),
                                        url: collection.collection_slug ? (collection.collection_slug + '?metaquery[0][key]=' + collection.metadata_id + '&metaquery[0][value][0]=' + itemId + '&metaquery[0][compare]=IN') : ''
                                    }
                                ]
                            ]
                        ],
                        [
                            'core/spacer',
                            { height: 30 }
                        ]
                    ]
                ]);
            }
        });
        setAttributes({ relatedItemsTemplate: relatedItemsTemplate});
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
                        <SingleItemModal
                            modalTitle={ __('Select one item that has relations', 'tainacan') }
                            applyButtonLabel={ __('Get relations of this item', 'tainacan') }
                            existingCollectionId={ collectionId }
                            existingItemId={ itemId }
                            onSelectCollection={ (selectedCollectionId) => {
                                if (collectionId != selectedCollectionId)
                                    relatedItems = [];
                                
                                collectionId = selectedCollectionId;
                                setAttributes({ 
                                    collectionId: collectionId,
                                    relatedItems: relatedItems
                                });
                            }}
                            onApplySelectedItem={ (selectedItemId) => {
                                if (itemId != selectedItemId) {
                                    relatedItems = [];
                                    relatedItemsTemplate = [];
                                }
                                
                                itemId = selectedItemId;
                                setAttributes({
                                    itemId: itemId,
                                    relatedItems: relatedItems,
                                    relatedItemsTemplate: relatedItemsTemplate,
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

            { !relatedItems.length && !isLoading ? (
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
                        onClick={ () => openSingleItemModal() }>
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
                    {  relatedItems.length ? (

                        <div className={ 'related-items-edit-container' }>
                            <InnerBlocks
                                    allowedBlocks={[ 
                                        'core/heading',
                                        'core/paragraph',
                                        'tainacan/carousel-items-list',
                                        'tainacan/dynamic-items-list',
                                        'core/buttons'
                                    ]}
                                    template={ relatedItemsTemplate } />
                        </div>
                        ) : null
                    }
                </div>
            }
            
        </div>
    );
};