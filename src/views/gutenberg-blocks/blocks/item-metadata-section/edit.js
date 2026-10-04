const { __ } = wp.i18n;

const { useEffect } = wp.element;

const { Placeholder, Button, Spinner, ToggleControl, PanelBody } = wp.components;

const ServerSideRender = wp.serverSideRender;
const { useBlockProps, InnerBlocks, BlockControls, AlignmentControl, InspectorControls } = wp.blockEditor;

import TainacanSingleItemMetadataSectionSelectionModal from '../../js/selection/tainacan-single-item-metadata-section-selection-modal.js';
import getCollectionIdFromPossibleTemplateEdition from '../../js/template/tainacan-blocks-single-item-template-mode.js';
import tainacanApi from '../../js/axios.js';
import axios from 'axios';
import tainacanLogoIcon from '../../js/tainacan-logo-icon.js';

export default function ({ attributes, setAttributes, isSelected, context }) {
    
    let {
        collectionId,
        itemId,
        isLoading,
        metadataSectionRequestSource,
        isModalOpen,
        sectionId,
        sectionName,
        sectionDescription,
        sectionMetadata,
        metadataSectionTemplate,
        dataSource,
        templateMode,
        isDynamic,
        textAlign
    } = attributes;

    const contextItemId = context && context['tainacan/itemId'] != null ? Number( context['tainacan/itemId'] ) : NaN;
    const attributeItemId = Number( itemId );

    // When rendered inside item-metadata-sections, use parent's itemId from context so we stay in sync when parent's item selection changes (InnerBlocks template is only used on initial creation).
    const effectiveItemId = ( dataSource === 'parent' && Number.isFinite( contextItemId ) && contextItemId > 0 )
        ? contextItemId
        : ( Number.isFinite( attributeItemId ) ? attributeItemId : 0 );

    // Keep attribute in sync when we use context, so this block provides the correct itemId to its inner blocks (e.g. item-metadata) via context.
    useEffect(() => {
        if ( dataSource !== 'parent' || !Number.isFinite( contextItemId ) || contextItemId <= 0 )
            return;
        if ( contextItemId !== ( Number.isFinite( attributeItemId ) ? attributeItemId : 0 ) )
            setAttributes({ itemId: contextItemId });
    }, [ dataSource, contextItemId, attributeItemId ]);

    // Gets blocks props from hook
    const blockProps = useBlockProps( {
        className: {
            [ `has-text-align-${ textAlign }` ]: textAlign,
        }
    } );

    useEffect(() => {
        setContent();
    }, [ effectiveItemId, collectionId, isDynamic, sectionId, templateMode ]);

    function setContent() {
        if ( sectionId && collectionId ) {

            isLoading = true;

            setAttributes({
                isLoading: isLoading
            });

            if ( dataSource === 'parent' ) {
                
                getMetadataSectionTemplates({
                    sectionId: sectionId,
                    sectionName: sectionName,
                    sectionDescription: sectionDescription,
                    sectionMetadata: sectionMetadata,
                    metadataSectionRequestSource: metadataSectionRequestSource
                });

            } else {
                if (metadataSectionRequestSource != undefined && typeof metadataSectionRequestSource == 'function')
                    metadataSectionRequestSource.cancel('Previous metadata sections search canceled.');

                metadataSectionRequestSource = axios.CancelToken.source();

                let endpoint = '/collection/'+ collectionId + '/metadata-sections/' + sectionId;

                tainacanApi.get(endpoint, { cancelToken: metadataSectionRequestSource.token })
                    .then(response => {

                        let metadataSection = response.data ? response.data : [];
                        
                        getMetadataSectionTemplates({
                            sectionId: String(metadataSection.id),
                            sectionName: metadataSection.name,
                            sectionDescription: metadataSection.description,
                            sectionMetadata: metadataSection['metadata_object_list'],
                            metadataSectionRequestSource: metadataSectionRequestSource
                        });
                    })
                    .catch((error) => {
                        console.error(error);

                        setAttributes({
                            sectionId: '',
                            sectionName: '',
                            sectionDescription: '',
                            sectionMetadata: [],
                            isLoading: false
                        });
                    });
            }
        }
    }

    function getMetadataSectionTemplates({
        sectionId,
        sectionName,
        sectionDescription,
        sectionMetadata,
        metadataSectionRequestSource
    }) {
        metadataSectionTemplate = [];

        if (sectionName) {
            metadataSectionTemplate.push([
                'tainacan/metadata-section-name',
            ]);
        }
        if (sectionDescription) {
            metadataSectionTemplate.push([
                'tainacan/metadata-section-description',
            ]);
        }

        if (sectionMetadata.length) {
            metadataSectionTemplate.push([
                'tainacan/item-metadata',
                {
                    sectionId: String(sectionId),
                    itemId: effectiveItemId,
                    collectionId: Number(collectionId),
                    metadata: sectionMetadata,
                    dataSource: 'parent',
                    templateMode: templateMode
                }
            ]);
        }

        setAttributes({ 
            metadataSectionTemplate: metadataSectionTemplate,
            sectionId: sectionId,
            sectionName: sectionName,
            sectionDescription: sectionDescription,
            sectionMetadata: sectionMetadata,
            isLoading: false,
            metadataSectionRequestSource: metadataSectionRequestSource
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

            { sectionId ? 
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
            : null }

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
                        <TainacanSingleItemMetadataSectionSelectionModal
                            modalTitle={ __('Select one item to render a metadata section of it', 'tainacan') }
                            existingCollectionId={ collectionId }
                            existingItemId={ effectiveItemId }
                            existingMetadataSectionId={ sectionId }
                            isTemplateMode={ templateMode }
                            onSelectCollection={ (selectedCollectionId) => {
                                collectionId = Number(selectedCollectionId);
                                setAttributes({ 
                                    collectionId: collectionId
                                });
                            }}
                            onSelectItem={ (selectedItemId) => {
                                itemId = Number(selectedItemId);
                                setAttributes({ 
                                    itemId: itemId
                                });
                            }}
                            onApplySelectedMetadataSection={ (selectedMetadataSection) => {
                                sectionId = selectedMetadataSection.sectionId;
                                setAttributes({
                                    sectionId: sectionId,
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

            { !sectionId && dataSource !== 'parent' ? (
                <Placeholder
                    icon={ tainacanLogoIcon() }
                    label={ __( 'Tainacan Item Metadata Section', 'tainacan' ) }
                    instructions={
                        collectionId && ( templateMode || effectiveItemId ) ?
                            __('Select a metadata section to display it.', 'tainacan') :
                            __('Select an item and a metadata section to display it.', 'tainacan')
                    }
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
                        { collectionId && ( templateMode || effectiveItemId ) ?
                            __('Select a Metadata Section', 'tainacan') :
                            __('Select Item and Metadata Section', 'tainacan')
                        }
                    </Button>
                </Placeholder>
                ) : null
            }

            { isLoading ? 
                <div className="spinner-container">
                    <Spinner />
                </div> :
                <div className={ 'item-metadata-sections-edit-container' }>
                    { metadataSectionTemplate.length ?
                        ( isDynamic ? 
                            <ServerSideRender
                                block="tainacan/item-metadata-section"
                                attributes={ attributes }
                                httpMethod={ 'POST' }
                            />
                            :
                            <InnerBlocks
                                allowedBlocks={ true }
                                template={ metadataSectionTemplate }
                                templateInsertUpdatesSelection={ true } />
                        )
                        : null
                    }
                </div>
            }
            
        </div>;
};