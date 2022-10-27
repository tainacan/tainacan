const { __ } = wp.i18n;

const { Button, Spinner, ToggleControl, Placeholder, PanelBody } = wp.components;

const ServerSideRender = wp.serverSideRender;
const { useBlockProps, InnerBlocks, BlockControls, AlignmentControl, InspectorControls } = (tainacan_blocks.wp_version < '5.2' ? wp.editor : wp.blockEditor );

import SingleItemModal from '../../js/selection/single-item-modal.js';
import getCollectionIdFromPossibleTemplateEdition from '../../js/template/tainacan-blocks-single-item-template-mode.js';
import tainacan from '../../js/axios.js';
import axios from 'axios';

export default function ({ attributes, setAttributes, className, isSelected }) {
    
    let {
        content, 
        collectionId,
        itemId,
        sectionId,
        isLoading,
        itemMetadataRequestSource,
        isModalOpen,
        itemMetadata,
        metadata,
        itemMetadataTemplate,
        dataSource,
        templateMode,
        isDynamic,
        textAlign
    } = attributes;

    // Gets blocks props from hook
    const blockProps = tainacan_blocks.wp_version < '5.6' ? { className: className } : useBlockProps( {
        className: {
            [ `has-text-align-${ textAlign }` ]: textAlign,
        }
    } );
    const currentWPVersion = (typeof tainacan_blocks != 'undefined') ? tainacan_blocks.wp_version : tainacan_plugin.wp_version;

    function setContent() {
        if ( dataSource === 'parent' && templateMode) {

            isLoading = true;
        
            setAttributes({
                isLoading: isLoading
            });    
            
            getItemMetadataTemplates({
                metadata: metadata,
                itemMetadata: itemMetadata,
                itemMetadataRequestSource: itemMetadataRequestSource
            });

        } else if ( dataSource !== 'parent' && templateMode && collectionId ) {

            isLoading = true;
        
            setAttributes({
                isLoading: isLoading
            });    

            if (itemMetadataRequestSource != undefined && typeof itemMetadataRequestSource == 'function')
                itemMetadataRequestSource.cancel('Previous metadata search canceled.');

            itemMetadataRequestSource = axios.CancelToken.source();

            let endpoint = '/collection/' + collectionId + (sectionId ? ('/metadata-sections/' + sectionId) : '/metadata');

            tainacan.get(endpoint, { cancelToken: itemMetadataRequestSource.token })
                .then(response => {

                    metadata = response.data ? response.data : [];
                    if (sectionId)
                        metadata = metadata['metadata_object_list'];

                    getItemMetadataTemplates({
                        metadata: metadata,
                        itemMetadata: [],
                        itemMetadataRequestSource: itemMetadataRequestSource
                    });
                })
                .catch((error) => {
                    console.error(error);

                    setAttributes({
                        metadata: [],
                        itemMetadata: [],
                        isLoading: false
                    });
                });
        } else if (itemId) {

            isLoading = true;
        
            setAttributes({
                isLoading: isLoading
            });    

            if (itemMetadataRequestSource != undefined && typeof itemMetadataRequestSource == 'function')
                itemMetadataRequestSource.cancel('Previous item metadata search canceled.');

            itemMetadataRequestSource = axios.CancelToken.source();

            const endpoint = '/item/' + itemId + (sectionId ? ('/metadata-sections/' + sectionId) : '/metadata');

            tainacan.get(endpoint, { cancelToken: itemMetadataRequestSource.token })
                .then(response => {

                    itemMetadata = response.data ? response.data : [];

                    getItemMetadataTemplates({
                        metadata: itemMetadata.map(anItemMetadata => anItemMetadata.metadatum),
                        itemMetadata: itemMetadata,
                        itemMetadataRequestSource: itemMetadataRequestSource
                    });
                })
                .catch((error) => {
                    console.error(error);

                    setAttributes({
                        metadata: [],
                        itemMetadata: [],
                        isLoading: false
                    });
                });
        }
    }

    function getItemMetadataTemplates({
        metadata,
        itemMetadata,
        itemMetadataRequestSource
    }) {
        let itemMetadataTemplate = []; 

        if ( templateMode ) {
            metadata.forEach((aMetadatum) => {
                itemMetadataTemplate.push([ 
                    'tainacan/item-metadatum',
                    {
                        placeholder: __( 'Item Metadatum', 'tainacan' ),
                        metadatumId: aMetadatum.id,
                        collectionId: Number(collectionId),
                        dataSource: 'parent',
                        templateMode: templateMode
                    }
                ]);
            });
        } else {
            itemMetadata.forEach((itemMetadatum) => {
                if (
                    itemMetadatum.metadatum &&
                    itemMetadatum.metadatum.id &&
                    (itemMetadatum.value !== '' && itemMetadatum.value !== false) &&
                    (!Array.isArray(itemMetadatum.value) || itemMetadatum.value.length)
                ) {
                    itemMetadataTemplate.push([ 
                        'tainacan/item-metadatum',
                        {
                            placeholder: __( 'Item Metadatum', 'tainacan' ),
                            metadatumId: itemMetadatum.metadatum.id,
                            itemId: Number(itemId),
                            collectionId: Number(collectionId),
                            dataSource: 'parent',
                            templateMode: templateMode
                        }
                    ]);
                }
            });
        }
        setAttributes({ 
            itemMetadataTemplate: itemMetadataTemplate,
            metadata: metadata,
            itemMetadata: itemMetadata,
            isLoading: false,
            itemMetadataRequestSource: itemMetadataRequestSource
        });
    }
    
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
            setContent();
        }
    }

    // Executed only on the first load of page
    if (content === undefined || (content && content.length && content[0].type)) {
        setAttributes({ content: '' });
        setContent();
    }
    
    return content == 'preview' ? 
        <div className={className}>
            <img
                    width="100%"
                    src={ `${tainacan_blocks.base_url}/assets/images/related-carousel-items.png` } />
        </div>
        : (
        <div { ...blockProps }>

            <InspectorControls>
                <PanelBody
                    title={ __('Data source', 'tainacan') }
                    initialOpen={ true }
                >
                    <ToggleControl
                        label={ __('Dynamic sync from Tainacan', 'tainacan') }
                        help={ __( 'Check this if you want the item metadata values to be always sync with its source from Tainacan. If disabled, however, you will be able to change order of inner blocks, delete and wrap them inside other blocks.', 'tainacan' ) }
                        checked={ isDynamic }
                        onChange={ ( isChecked ) => {
                                isDynamic = isChecked;
                                setAttributes({ isDynamic: isDynamic });
                            } 
                        }
                    />
                </PanelBody>
            </InspectorControls>

            <BlockControls group="block">
                <AlignmentControl
                        value={ textAlign }
                        onChange={ ( nextAlign ) => {
                            setAttributes( { textAlign: nextAlign } );
                        } }
                    />
            </BlockControls>

            { isSelected ? 
                ( 
                <div>
                    { isModalOpen ?
                        <SingleItemModal
                            modalTitle={ __('Select one item to render its metadata', 'tainacan') }
                            applyButtonLabel={ __('List metadata for this item', 'tainacan') }
                            existingCollectionId={ collectionId }
                            existingItemId={ itemId }
                            onSelectCollection={ (selectedCollectionId) => {
                                collectionId = Number(selectedCollectionId);
                                setAttributes({ 
                                    collectionId: collectionId
                                });
                            }}
                            onApplySelectedItem={ (selectedItemId) => {
                                itemId = Number(selectedItemId);
                                setAttributes({
                                    itemId: itemId,
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

            { !itemId && !templateMode && dataSource !== 'parent' ? (
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
                        {__('Select an item to display its metadata list.', 'tainacan')}
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
                        {__('Select Item', 'tainacan')}
                    </Button>
                </Placeholder>
                ) : null
            }

            { isLoading ? 
                <div class="spinner-container">
                    <Spinner />
                </div> :
                <div className={ 'item-metadata-edit-container' }>
                    { itemMetadataTemplate.length ?
                        ( isDynamic ? 
                            <ServerSideRender
                                block="tainacan/item-metadata"
                                attributes={ attributes }
                                httpMethod={ currentWPVersion >= '5.5' ? 'POST' : 'GET' }
                            />
                            :
                            <InnerBlocks
                                allowedBlocks={ true }
                                template={ itemMetadataTemplate }
                                templateInsertUpdatesSelection={ true } />
                        )
                        : null
                    }
                </div>
            }
            
        </div>
    );
};