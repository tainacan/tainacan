const { __ } = wp.i18n;

const { Button, Spinner, Placeholder, ToggleControl, PanelBody } = wp.components;

const ServerSideRender = wp.serverSideRender;
const { useBlockProps, InnerBlocks, BlockControls, AlignmentControl, InspectorControls } = (tainacan_blocks.wp_version < '5.2' ? wp.editor : wp.blockEditor );

import SingleItemMetadataSectionModal from '../../js/selection/single-item-metadata-section-modal.js';
import getCollectionIdFromPossibleTemplateEdition from '../../js/template/tainacan-blocks-single-item-template-mode.js';
import tainacan from '../../js/axios.js';
import axios from 'axios';

export default function ({ attributes, setAttributes, className, isSelected }) {
    
    let {
        content, 
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

    // Gets blocks props from hook
    const blockProps = tainacan_blocks.wp_version < '5.6' ? { className: className } : useBlockProps( {
        className: {
            [ `has-text-align-${ textAlign }` ]: textAlign,
        }
    } );
    const currentWPVersion = (typeof tainacan_blocks != 'undefined') ? tainacan_blocks.wp_version : tainacan_plugin.wp_version;

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

                tainacan.get(endpoint, { cancelToken: metadataSectionRequestSource.token })
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
                    itemId: isNaN(itemId) ? 0 : Number(itemId),
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
                        <SingleItemMetadataSectionModal
                            modalTitle={ __('Select one item to render a metadata section of it', 'tainacan') }
                            existingCollectionId={ collectionId }
                            existingItemId={ itemId }
                            existingMetadataSectionId={ sectionId }
                            isTemplateMode={ templateMode }
                            onSelectCollection={ (selectedCollectionId) => {
                                collectionId = Number(selectedCollectionId);
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
                        { collectionId ? __('Select a metadata section to display it.', 'tainacan') : __('Select an item and a metadata section to display it.', 'tainacan') }
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
                        { collectionId ?  __('Select a Metadata Section', 'tainacan') : __('Select Item and Metadata Section', 'tainacan') }
                    </Button>
                </Placeholder>
                ) : null
            }

            { isLoading ? 
                <div class="spinner-container">
                    <Spinner />
                </div> :
                <div className={ 'item-metadata-sections-edit-container' }>
                    { metadataSectionTemplate.length ?
                        ( isDynamic ? 
                            <ServerSideRender
                                block="tainacan/item-metadata-section"
                                attributes={ attributes }
                                httpMethod={ currentWPVersion >= '5.5' ? 'POST' : 'GET' }
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
            
        </div>
    );
};