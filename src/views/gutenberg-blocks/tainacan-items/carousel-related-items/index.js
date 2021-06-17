const { registerBlockType } = wp.blocks;
const { withDispatch, useDispatch, useSelect } = wp.data;

const { __ } = wp.i18n;

const { RangeControl, Spinner, Button, ToggleControl, SelectControl, Placeholder, IconButton, ColorPicker, ColorPalette, BaseControl, PanelBody } = wp.components;

const { InspectorControls, BlockControls, InnerBlocks, store } = ( tainacan_blocks.wp_version < '5.2' ? wp.editor : wp.blockEditor );

import CarouselRelatedItemsModal from './carousel-related-items-modal.js';
import tainacan from '../../js/axios.js';
import axios from 'axios';
import qs from 'qs';
import { ThumbnailHelperFunctions } from '../../../admin/js/utilities.js';
import TainacanBlocksCompatToolbar from '../../js/tainacan-blocks-compat-toolbar.js';
import DeprecatedBlocks from './carousel-related-items-deprecated.js';
import 'swiper/css/swiper.min.css';

registerBlockType('tainacan/carousel-related-items', {
    title: __('Tainacan Related Items Carousel', 'tainacan'),
    icon:
        <svg
                xmlns="http://www.w3.org/2000/svg"
                viewBox="0 0 24 24"
                height="24px"
                width="24px">
            <path
                fill="#298596"
                d="M16,6H12a2,2,0,0,0-2,2v6.52A6,6,0,0,1,12,19a6,6,0,0,1-.73,2.88A1.92,1.92,0,0,0,12,22h8a2,2,0,0,0,2-2V12Zm-1,6V7.5L19.51,12ZM15,2V4H8v9.33A5.8,5.8,0,0,0,6,13V4A2,2,0,0,1,8,2ZM10.09,19.05,7,22.11V16.05L8,17l2,2ZM5,16.05v6.06L2,19.11Z"/>
        </svg>,
    category: 'tainacan-blocks',
    keywords: [ __( 'items', 'tainacan' ), __( 'carousel', 'tainacan' ), __( 'slider', 'tainacan' ), __( 'relationship', 'tainacan' ) ],
    description: __('A set of carousels to list items related to a certain item via relationship metadata.', 'tainacan'),
    example: {
        attributes: {
            content: 'preview'
        }
    },
    attributes: {
        content: {
            type: 'array',
            source: 'children',
            selector: 'div'
        },
        collectionId: {
            type: String,
            default: undefined
        },
        itemId: {
            type: String,
            default: undefined
        },
        isModalOpen: {
            type: Boolean,
            default: false
        },
        relatedItems: {
            type: Array,
            default: []
        },
        relatedItemsTemplate: {
            type: Array,
            default: []
        },
        itemRequestSource: {
            type: String,
            default: undefined
        },
        hideTitle: {
            type: Boolean,
            value: false
        },
    },
    supports: {
        align: ['full', 'wide'],
        html: false,
        multiple: true,
    },
    edit({ attributes, setAttributes, className, isSelected, clientId }){
        const innerBlocks = useSelect(
            ( select ) =>
                select( store ).getBlocks(clientId),
            [ clientId ]
        );
        
        let {
            content, 
            collectionId,
            itemId,
            isModalOpen,
            relatedItems,
            isLoading,
            itemRequestSource,
            hideTitle,
            relatedItemsTemplate
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
        
        function openRelatedItemsModal() {
            isModalOpen = true;
            setAttributes( { 
                isModalOpen: isModalOpen
            } );
        }

        function updateChildBlocks() {

            console.log(innerBlocks);
            
        }

        function getRelatedItemsTemplates() {
            relatedItemsTemplate = [];
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
                                    placeholder: __( 'Relationship metadadum name', 'tainacan' ),
                                    content: collection.metadata_name
                                }
                            ],
                            [
                                'tainacan/carousel-items-list',
                                { 
                                    content: [{ type: 'innerblock' }],
                                    selectedItems: collection.items,
                                    loadStrategy: 'parent',
                                    collectionId: collection.collection_id,
                                    hideTitle: hideTitle
                                }
                            ],
                            [
                                'core/buttons',
                                {},
                                [
                                    [
                                        'core/button',
                                        { text: __( 'View all related items', 'tainacan' ) }
                                    ]
                                ]
                            ],
                            [
                                'core/spacer',
                                { height: 70 }
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

                { relatedItems.length ?
                    <BlockControls>
                        { 
                            TainacanBlocksCompatToolbar({
                                label: __('Select item with relations', 'tainacan'),
                                icon: <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                            viewBox="0 0 24 24"
                                            height="24px"
                                            width="24px">
                                        <path d="M16,6H12a2,2,0,0,0-2,2v6.52A6,6,0,0,1,12,19a6,6,0,0,1-.73,2.88A1.92,1.92,0,0,0,12,22h8a2,2,0,0,0,2-2V12Zm-1,6V7.5L19.51,12ZM15,2V4H8v9.33A5.8,5.8,0,0,0,6,13V4A2,2,0,0,1,8,2ZM10.09,19.05,7,22.11V16.05L8,17l2,2ZM5,16.05v6.06L2,19.11Z"/>
                                    </svg>,
                                onClick: openRelatedItemsModal
                            })
                        }
                    </BlockControls>
                : null }
                <div>
                   <InspectorControls>
                        <ToggleControl
                                label={__('Hide title', 'tainacan')}
                                help={ !hideTitle ? __('Toggle to hide item\'s title', 'tainacan') : __('Do not hide item\'s title', 'tainacan')}
                                checked={ hideTitle }
                                onChange={ ( isChecked ) => {
                                        hideTitle = isChecked;
                                        setAttributes({ hideTitle: hideTitle });
                                        updateChildBlocks();
                                    } 
                                }
                            />
                    </InspectorControls>
                </div>

                { isSelected ? 
                    ( 
                    <div>
                        { isModalOpen ?   
                            <CarouselRelatedItemsModal
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
                                onApplyRelatedItem={ (selectedItemId) => {
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
                            {__('List items on a Carousel, using search or item selection.', 'tainacan')}
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
                        {  relatedItems.length ? (

                            <div className={ 'carousel-related-items-edit-container' }>
                                <InnerBlocks
                                        allowedBlocks={[ 
                                            'core/heading',
                                            'core/paragraph',
                                            'tainacan/carousel-items-list',
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
    },
    save({ className }){
        return <div className={ className }><InnerBlocks.Content /></div>
    },
    deprecated: DeprecatedBlocks
});