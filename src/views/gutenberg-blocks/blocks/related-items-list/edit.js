const { __ } = wp.i18n;

const { useEffect } = wp.element;

const { Icon, Spinner, Button, Placeholder, ToolbarDropdownMenu } = wp.components;

const { InnerBlocks, BlockControls, useBlockProps } = wp.blockEditor;

import TainacanBlocksCompatToolbar from '../../js/compatibility/tainacan-blocks-compat-toolbar.js';
import TainacanSingleItemSelectionModal from '../../js/selection/tainacan-single-item-selection-modal.js';
import getCollectionIdFromPossibleTemplateEdition from '../../js/template/tainacan-blocks-single-item-template-mode.js';
import tainacanApi from '../../js/axios.js';
import axios from 'axios';

const placeholderTemplate = [[
    'core/group',
    {},
    [
        [ 
            'core/heading',
            {
                placeholder: __( 'Collection name', 'tainacan' ),
                content: ''
            }
        ],
        [
            'core/paragraph',
            {
                placeholder: __( 'Relationship metadatum name', 'tainacan' ),
                content: ''
            }
        ],
        [
            'core/spacer',
            { height: '30px' }
        ],
        [
            'core/buttons',
            {},
            [
                [
                    'core/button',
                    { 
                        text: __( 'View all related items', 'tainacan' ),
                    }
                ]
            ]
        ]
    ]
]];

export default function ({ attributes, setAttributes, isSelected }) {
    
    let {
        collectionId,
        itemId,
        isModalOpen,
        relatedItems,
        isLoading,
        itemRequestSource,
        relatedItemsTemplate,
        itemsListLayout,
        tainacanViewMode,
        templateMode
    } = attributes;
  
    // Gets blocks props from hook
    const blockProps = useBlockProps();

    useEffect(() => {
        setContent();
    }, [ itemId ]);
        
    // Checks if we are in template mode, if so, gets the collection Id from URL.
    useEffect(() => {
        if ( !templateMode || ( templateMode && !collectionId ) ) {
            const possibleCollectionId = getCollectionIdFromPossibleTemplateEdition();
            if ( possibleCollectionId ) {
                setAttributes({ 
                    collectionId: String(possibleCollectionId),
                    templateMode: true
                });
            }
        }
    }, [ templateMode, collectionId ]);

    useEffect(() => {
        setAttributes({
            relatedItemsTemplate: getRelatedItemsTemplates(relatedItems)
        })
    }, [ relatedItems ]);

    const layoutControls = [
        {
            icon: 'slides',
            title: __( 'Carousel', 'tainacan' ),
            onClick: () => updateLayout('carousel'),
            isActive: itemsListLayout === 'carousel',
        },
        {
            icon: 'grid-view',
            title: __( 'Grid View', 'tainacan' ),
            onClick: () => updateLayout('grid'),
            isActive: itemsListLayout === 'grid',
        },
        {
            icon: 'list-view',
            title: __( 'List View', 'tainacan' ),
            onClick: () => updateLayout('list'),
            isActive: itemsListLayout === 'list',
        },
        {
            icon: 'layout',
            title: __( 'Mosaic View', 'tainacan' ),
            onClick: () => updateLayout('mosaic'),
            isActive: itemsListLayout === 'mosaic',
        }
    ];

    function setContent() {

        setAttributes({
            isLoading: true
        });

        if (itemRequestSource != undefined && typeof itemRequestSource == 'function')
            itemRequestSource.cancel('Previous items search canceled.');

        let nextItemRequestSource = axios.CancelToken.source();

        let endpoint = '/items/'+ itemId + '?fetch_only=related_items';

        tainacanApi.get(endpoint, { cancelToken: nextItemRequestSource.token })
            .then(response => {
                setAttributes({
                    relatedItems: response.data && response.data.related_items ? Object.values(response.data.related_items) : [],
                    isLoading: false,
                    itemRequestSource: nextItemRequestSource
                });
            });
    }
    
    function openSingleItemModal() {
        setAttributes( { 
            isModalOpen: true
        } );
    }

    function getRelatedItemsTemplates(itemsRelatedToThis) {
        let innerBlocksTemplate = [];
        
        itemsRelatedToThis.forEach((collection) => {

            let innerItemsList = itemsListLayout !== 'carousel' ?
                [
                    'tainacan/dynamic-items-list',
                    { 
                        content: <div></div>,
                        selectedItems: collection.items,
                        loadStrategy: 'parent',
                        collectionId: '' + collection.collection_id,
                        layout: itemsListLayout,
                        tainacanViewMode: tainacanViewMode
                    }
                ] :
                [
                    'tainacan/carousel-items-list',
                    { 
                        content: <div></div>,
                        selectedItems: collection.items,
                        loadStrategy: 'parent',
                        collectionId: '' + collection.collection_id
                    }
                ];
                
            if ( collection.total_items && collection.items.length ) {
                innerBlocksTemplate.push([
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
                            { height: '30px' }
                        ]
                    ]
                ]);
            }
        });

        return innerBlocksTemplate;
    }

    function updateLayout(newLayout) {
        itemsListLayout = newLayout;

        setAttributes({ 
            itemsListLayout: itemsListLayout
        });
    }
    
    return (
        <div { ...blockProps }>

            { isSelected ? 
                ( 
                <div>
                    { (!itemId || templateMode) && !relatedItemsTemplate.length ?
                        <BlockControls>
                            { TainacanBlocksCompatToolbar({
                                controls: layoutControls,
                                extraComponents: <ToolbarDropdownMenu
                                        icon={ () => <Icon icon="plus" /> }
                                        label={ __('Tainacan View Modes', 'tainacan') }
                                        controls={ 
                                            Object.entries(tainacan_blocks.registered_view_modes)
                                                .filter((aViewMode) => !aViewMode[1].full_screen)
                                                .map((aViewMode) => {
                                                    return {
                                                        title: aViewMode[1].label,
                                                        isActive: itemsListLayout === 'tainacan-view-modes' && tainacanViewMode === aViewMode[0],
                                                        onClick: () => { 
                                                            setAttributes({ tainacanViewMode: aViewMode[0] })
                                                            updateLayout('tainacan-view-modes');
                                                        }
                                                    }
                                                }) 
                                        }
                                    /> 
                            }) }
                            </BlockControls>
                        : null
                    }
                    { isModalOpen ?   
                        <TainacanSingleItemSelectionModal
                            modalTitle={ __('Select one item that has relations', 'tainacan') }
                            applyButtonLabel={ __('Get relations of this item', 'tainacan') }
                            existingCollectionId={ collectionId }
                            existingItemId={ itemId }
                            onSelectCollection={ (selectedCollectionId) => {
                                if ( collectionId != selectedCollectionId ) {
                                   setAttributes({ 
                                        collectionId: selectedCollectionId,
                                        relatedItems: []
                                    });
                                } else {
                                    setAttributes({ collectionId: selectedCollectionId + '' })
                                }
                            }}
                            onApplySelectedItem={ (selectedItemId) => {
                                setAttributes({
                                    itemId: selectedItemId + '',
                                    isModalOpen: false
                                });
                            }}
                            onCancelSelection={ () => setAttributes({ isModalOpen: false }) }/> 
                        : null
                    }
                    
                </div>
                ) : null
            }
            { !templateMode && !relatedItems.length && !isLoading ? (
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

            { !templateMode && !isLoading && itemId && relatedItems.reduce((total, relation) => total + Number(relation.total_items), 0) <= 0 ?
                <Placeholder
                    className="tainacan-block-placeholder"
                    icon={(
                        <img
                            width={148}
                            src={ `${tainacan_blocks.base_url}/assets/images/tainacan_logo_header.svg` }
                            alt="Tainacan Logo"/>
                    )}>
                    <p>{ __('The selected item does not contain other items related to it.', 'tainacan') }</p>
                     <Button
                        isPrimary
                        type="button"
                        onClick={ () => openSingleItemModal() }>
                        {__('Select another Item', 'tainacan')}
                    </Button>
                </Placeholder>
                :
                null
            }
            
            { !templateMode && isLoading ? 
                <div className="spinner-container">
                    <Spinner />
                </div> :
                <div>
                    { relatedItemsTemplate.length ? (
                        <div className={ 'related-items-edit-container' }>
                            <InnerBlocks
                                    allowedBlocks={[ 
                                        'core/heading',
                                        'core/paragraph',
                                        'tainacan/carousel-items-list',
                                        'tainacan/dynamic-items-list',
                                        'core/buttons',
                                        'core/spacer',
                                        'core/group',
                                        'core/columns'
                                    ]}
                                    template={ relatedItemsTemplate }
                                    templateInsertUpdatesSelection={ true } />
                        </div>
                        ) : null
                    }
                </div>
            }
            {
                templateMode ?  <div className={ 'related-items-edit-container' }>
                    <InnerBlocks
                            templateLock="all"
                            allowedBlocks={ true }
                            template={ placeholderTemplate }
                            templateInsertUpdatesSelection={ true } />
                </div>
                : null
            }
            
        </div>
    );
};