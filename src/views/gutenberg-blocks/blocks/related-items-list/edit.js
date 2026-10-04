const { __ } = wp.i18n;

const { useEffect, useState } = wp.element;

const { Placeholder, Icon, Spinner, Button, ToolbarDropdownMenu, PanelBody, ToggleControl, BaseControl } = wp.components;

const ServerSideRender = wp.serverSideRender;
const { InnerBlocks, BlockControls, useBlockProps, InspectorControls } = wp.blockEditor;

import TainacanBlocksCompatToolbar from '../../js/compatibility/tainacan-blocks-compat-toolbar.js';
import TainacanSingleItemSelectionModal from '../../js/selection/tainacan-single-item-selection-modal.js';
import CollectionsSelectionModal from '../../js/selection/tainacan-collections-selection-modal.js';
import getCollectionIdFromPossibleTemplateEdition from '../../js/template/tainacan-blocks-single-item-template-mode.js';
import tainacanApi from '../../js/axios.js';
import tainacanLogoIcon from '../../js/tainacan-logo-icon.js';
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
        relatedItemsTemplate,
        itemsListLayout,
        tainacanViewMode,
        templateMode,
        isDynamic,
        relatedCollectionIds
    } = attributes;

    let itemRequestSource = undefined;
    const [isCollectionsModalOpen, setIsCollectionsModalOpen] = useState(false);
    const [selectedCollectionNames, setSelectedCollectionNames] = useState([]);
  
    // Gets blocks props from hook
    const blockProps = useBlockProps();

    useEffect(() => {
        setContent();
    }, [ itemId, isDynamic, templateMode, itemsListLayout, tainacanViewMode ]);
        
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
    }, [ relatedItems, itemsListLayout, tainacanViewMode, relatedCollectionIds ]);

    useEffect(() => {
        if ( !relatedCollectionIds || !relatedCollectionIds.length ) {
            setSelectedCollectionNames([]);
            return;
        }

        let source = axios.CancelToken.source();
        const params = new URLSearchParams();
        params.set('orderby', 'title');
        params.set('order', 'asc');
        params.set('perpage', String(relatedCollectionIds.length));
        relatedCollectionIds.forEach((id) => params.append('postin[]', id));

        tainacanApi.get('/collections/?' + params.toString(), { cancelToken: source.token })
            .then((response) => {
                setSelectedCollectionNames((response.data || []).map((collection) => ({
                    id: String(collection.id),
                    name: collection.name
                })));
            })
            .catch(() => undefined);

        return () => source.cancel('Selected collections request canceled.');
    }, [ relatedCollectionIds ]);

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

    function visibleRelatedItems(itemsRelatedToThis) {
        const allowedIds = (relatedCollectionIds || []).map((id) => String(id));

        if ( !allowedIds.length )
            return itemsRelatedToThis;

        return itemsRelatedToThis.filter((collection) => allowedIds.includes(String(collection.collection_id)));
    }

    function getRelatedItemsTemplates(itemsRelatedToThis) {
        let innerBlocksTemplate = [];
        
        visibleRelatedItems(itemsRelatedToThis).forEach((collection) => {

            let innerItemsList = itemsListLayout !== 'carousel' ?
                [
                    'tainacan/dynamic-items-list',
                    { 
                        content: <div></div>,
                        selectedItems: itemsListLayout === 'tainacan-view-modes' ? collection.items.map(item => item.id) : collection.items,
                        loadStrategy: itemsListLayout === 'tainacan-view-modes' ? 'selection' : 'parent',
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

    const relatedCollectionsLabel = (relatedCollectionIds || []).length
        ? ( selectedCollectionNames.length
            ? selectedCollectionNames.map((collection) => collection.name).join(', ')
            : relatedCollectionIds.join(', ') )
        : __('All related collections.', 'tainacan');
    
    return (
        <div { ...blockProps }>

            <InspectorControls>
                <PanelBody
                    title={ __('Data source', 'tainacan') }
                    initialOpen={ true }
                >
                    <ToggleControl
                        label={ __('Dynamic sync from Tainacan', 'tainacan') }
                        help={ __( 'Check this if you want the items related to this item to be always sync with its source from Tainacan. If disabled, however, you will be able to change order of inner blocks, delete and wrap them inside other blocks.', 'tainacan' ) }
                        checked={ isDynamic }
                        onChange={ ( isChecked ) => {
                                setAttributes({ isDynamic: isChecked });
                            } 
                        }
                    />
                </PanelBody>
                <PanelBody
                    title={ __('Related collections', 'tainacan') }
                    initialOpen={ true }
                >
                    <BaseControl
                        id="related-collections-selection"
                        label={ __('Showing relations from:', 'tainacan') }
                    >
                        <span style={{ fontWeight: 'bold', top: '-3px', position: 'relative' }}>&nbsp;{ relatedCollectionsLabel }</span>
                        <br />
                        <Button
                            style={{ margin: '6px auto 16px auto', display: 'block' }}
                            id="related-collections-selection"
                            isSecondary
                            onClick={ () => setIsCollectionsModalOpen(true) }>
                            { __('Select collections', 'tainacan') }
                        </Button>
                    </BaseControl>
                </PanelBody>
            </InspectorControls>

            { isCollectionsModalOpen ?
                <CollectionsSelectionModal
                    modalTitle={ __('Select collections to display relations from', 'tainacan') }
                    selectedCollectionsObject={ (relatedCollectionIds || []).map((id) => {
                        const known = selectedCollectionNames.find((collection) => String(collection.id) === String(id));
                        return known || { id: String(id), name: String(id) };
                    }) }
                    onApplySelection={ (selection) => {
                        setAttributes({
                            relatedCollectionIds: selection.map((collection) => String(collection.id).replace(/^collection-id-/, ''))
                        });
                        setSelectedCollectionNames(selection.map((collection) => ({
                            id: String(collection.id).replace(/^collection-id-/, ''),
                            name: collection.name
                        })));
                        setIsCollectionsModalOpen(false);
                    }}
                    onCancelSelection={ () => setIsCollectionsModalOpen(false) }
                />
                : null
            }

            { isSelected ? 
                ( 
                <div>
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
                    icon={ tainacanLogoIcon() }
                    label={ __( 'Tainacan Related Items List', 'tainacan' ) }
                    instructions={ __( 'Select an item to create a set of lists with items related to it via relationship metadata.', 'tainacan' ) }
                >
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
                    icon={ tainacanLogoIcon() }
                    label={ __( 'Tainacan Related Items List', 'tainacan' ) }
                    instructions={ __( 'The selected item does not contain other items related to it.', 'tainacan' ) }
                >
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

            { !templateMode && !isLoading && itemId && relatedItems.reduce((total, relation) => total + Number(relation.total_items), 0) > 0 && visibleRelatedItems(relatedItems).reduce((total, relation) => total + Number(relation.total_items), 0) <= 0 ?
                <Placeholder
                    icon={ tainacanLogoIcon() }
                    label={ __( 'Tainacan Related Items List', 'tainacan' ) }
                    instructions={ __( 'None of the related items belong to the selected collections.', 'tainacan' ) }
                >
                    <Button
                        isPrimary
                        type="button"
                        onClick={ () => setAttributes({ relatedCollectionIds: [] }) }>
                        {__('Show all related collections', 'tainacan')}
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
                        {
                            ( isDynamic ? 
                                <ServerSideRender
                                    block="tainacan/related-items-list"
                                    attributes={ attributes }
                                    httpMethod={ 'POST' }
                                />
                                :
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
                            )
                        }
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