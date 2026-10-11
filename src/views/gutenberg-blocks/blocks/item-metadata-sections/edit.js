const { __ } = wp.i18n;

const { useEffect } = wp.element;

const { Placeholder, Button, Spinner, ToggleControl, PanelBody } = wp.components;

const ServerSideRender = wp.serverSideRender;
const { useBlockProps, InnerBlocks, BlockControls, AlignmentControl, InspectorControls } = wp.blockEditor;

import TainacanSingleItemSelectionModal from '../../js/selection/tainacan-single-item-selection-modal.js';
import getCollectionIdFromPossibleTemplateEdition from '../../js/template/tainacan-blocks-single-item-template-mode.js';
import tainacanApi from '../../js/axios.js';
import axios from 'axios';
import tainacanLogoIcon from '../../js/tainacan-logo-icon.js';

export default function ({ attributes, setAttributes, isSelected }) {
    
    let {
        collectionId,
        itemId,
        isLoading,
        metadataSectionsRequestSource,
        isModalOpen,
        metadataSections,
        metadataSectionsTemplate,
        templateMode,
        isDynamic,
        textAlign
    } = attributes;

    // Gets blocks props from hook
    const blockProps = useBlockProps( {
        className: {
            [ `has-text-align-${ textAlign }` ]: textAlign,
        }
    } );

    useEffect(() => {
        setContent();
    }, [ itemId, collectionId, isDynamic, templateMode ]);

    function setContent() {
        
        if (collectionId) {

            isLoading = true;

            setAttributes({
                isLoading: isLoading
            });

            if (metadataSectionsRequestSource != undefined && typeof metadataSectionsRequestSource == 'function')
                metadataSectionsRequestSource.cancel('Previous metadata sections search canceled.');

            metadataSectionsRequestSource = axios.CancelToken.source();

            let endpoint = '/collection/'+ collectionId + '/metadata-sections';

            tainacanApi.get(endpoint, { cancelToken: metadataSectionsRequestSource.token })
                .then(response => {

                    metadataSections = response.data ? response.data : [];

                    getMetadataSectionsTemplates({
                        metadataSections: metadataSections,
                        metadataSectionsRequestSource: metadataSectionsRequestSource
                    });
                })
                .catch((error) => {
                    console.error(error);

                    setAttributes({
                        metadataSections: [],
                        isLoading: false
                    });
                });
        }
    }

    function getMetadataSectionsTemplates({
        metadataSections,
        metadataSectionsRequestSource
    }) {
        let metadataSectionsTemplate = []; 

        metadataSections.forEach((aMetadataSection) => {
            if ( aMetadataSection['metadata_object_list'] && aMetadataSection['metadata_object_list'].length ) {
                metadataSectionsTemplate.push([
                    'tainacan/item-metadata-section',
                    {
                        sectionId: String(aMetadataSection.id),
                        sectionName: aMetadataSection.name,
                        sectionDescription: aMetadataSection.description,
                        sectionMetadata: aMetadataSection['metadata_object_list'],
                        itemId: itemId ? Number(itemId) : 0,
                        collectionId: Number(collectionId),
                        dataSource: 'parent',
                        templateMode: templateMode
                    }
                ]);
            }
        });
        setAttributes({ 
            metadataSectionsTemplate: metadataSectionsTemplate,
            metadataSections: metadataSections,
            isLoading: false,
            metadataSectionsRequestSource: metadataSectionsRequestSource
        });
    }

    // Checks if we are in template mode, if so, gets the collection Id from URL.
    useEffect(() => {
        if ( !templateMode || ( templateMode && !collectionId ) ) {
            const possibleCollectionId = getCollectionIdFromPossibleTemplateEdition();
            if ( possibleCollectionId ) {
                setAttributes({ 
                    collectionId: possibleCollectionId,
                    templateMode: true
                });
            }
        }
    }, [ templateMode, collectionId ]);
    
    return <div { ...blockProps }>

            <InspectorControls>
                <PanelBody
                    title={ __('Data source', 'tainacan') }
                    initialOpen={ true }
                >
                    <ToggleControl
                        label={ __('Dynamic sync from Tainacan', 'tainacan') }
                        help={ __( 'Check this if you want the item metadata and section values to be always sync with its source from Tainacan. If disabled, however, you will be able to change order of inner blocks, delete and wrap them inside other blocks.', 'tainacan' ) }
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
                        <TainacanSingleItemSelectionModal
                            modalTitle={ templateMode ? __('Select one metadata section', 'tainacan') : __('Select one item to render its metadata section', 'tainacan') }
                            applyButtonLabel={ templateMode ?  __('Show metadata sections', 'tainacan') : __('Show metadata sections for this item', 'tainacan') }
                            existingCollectionId={ collectionId }
                            existingItemId={ itemId }
                            onSelectCollection={ (selectedCollectionId) => {
                                collectionId = Number(selectedCollectionId);
                                setAttributes({ 
                                    collectionId: collectionId
                                });
                            }}
                            onApplySelectedItem={ (selectedItemId) => {
                                const nextItemId = Number(selectedItemId);
                                if ( !Number.isFinite(nextItemId) || nextItemId <= 0 )
                                    return;
                                itemId = nextItemId;
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

            { !itemId && !templateMode ? (
                <Placeholder
                    icon={ tainacanLogoIcon() }
                    label={ __( 'Tainacan Item Metadata Sections', 'tainacan' ) }
                    instructions={ __( 'Select an item to display its metadata list.', 'tainacan' ) }
                >
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
                        { __('Select Item', 'tainacan') }
                    </Button>
                </Placeholder>
                ) : null
            }

            { isLoading ? 
                <div className="spinner-container">
                    <Spinner />
                </div> :
                <div className={ 'item-metadata-sections-edit-container' }>
                    { metadataSectionsTemplate.length ?
                        ( isDynamic ? 
                            <ServerSideRender
                                block="tainacan/item-metadata-sections"
                                attributes={ attributes }
                                httpMethod={ 'POST' }
                            />
                            :
                            <InnerBlocks
                                allowedBlocks={ true }
                                template={ metadataSectionsTemplate } />
                        )
                        : null
                    }
                </div>
            }
            
        </div>;
};