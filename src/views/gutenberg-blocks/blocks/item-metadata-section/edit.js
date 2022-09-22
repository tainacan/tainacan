const { __ } = wp.i18n;

const { Button, Spinner, Placeholder } = wp.components;

const { useBlockProps, InnerBlocks } = (tainacan_blocks.wp_version < '5.2' ? wp.editor : wp.blockEditor );

import SingleItemMetadataSectionModal from '../../js/selection/single-item-metadata-section-modal.js';
import tainacan from '../../js/axios.js';
import axios from 'axios';

export default function ({ attributes, setAttributes, className, isSelected, context }) {
    
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
        dataSource
    } = attributes;

    // Gets blocks props from hook
    const blockProps = tainacan_blocks.wp_version < '5.6' ? { className: className } : useBlockProps();

    checkIfTemplateEdition();

    function setContent() {

        if (sectionId) {

            isLoading = true;

            setAttributes({
                isLoading: isLoading
            });

            if ( dataSource === 'parent' && sectionMetadata.length ) {
                
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
                            sectionId: metadataSection.id,
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
                {
                    sectionId: sectionId,
                    sectionName: sectionName,
                }
            ]);
        }
        if (sectionDescription) {
            metadataSectionTemplate.push([
                'core/paragraph',
                {
                    content: sectionDescription,
                }
            ]);
        }
        if (sectionMetadata.length) {
            metadataSectionTemplate.push([
                'tainacan/item-metadata',
                {
                    sectionId: sectionId,
                    itemId: Number(itemId),
                    collectionId: Number(collectionId),
                    metadata: sectionMetadata,
                    dataSource: 'parent'
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
    
    function checkIfTemplateEdition() {
        // Check custom template edition state
        const queryParams = new URLSearchParams(window.location.search);
        if (queryParams.get('postType') == 'wp_template') {

            // Extracts collectionId from a string like theme-slug//single-tnc_col_123_item
            let postId = queryParams.get('postId');
            
            if (typeof postId == 'string') {
                postId = postId.split('single-tnc_col_');
                
                if (postId.length == 2) {
                    postId = postId[1];

                    if (typeof postId == 'string') {
                        postId = postId.split('_item');

                        if (postId.length == 2) {
                            postId = postId[0];

                            collectionId = Number(postId);
                            itemId = 0;

                            const shouldSetContent = dataSource !== 'template';
                            dataSource = 'template';

                            setAttributes({ 
                                collectionId: itemId,
                                itemId: itemId,
                                dataSource: dataSource
                            });

                            if (shouldSetContent)
                                setContent();
                        }
                    }
                }
            }
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

            { isSelected ? 
                ( 
                <div>
                    { isModalOpen ?
                        <SingleItemMetadataSectionModal
                            modalTitle={ __('Select one item to render a metadata section of it', 'tainacan') }
                            existingCollectionId={ collectionId }
                            existingItemId={ itemId }
                            existingMetadataSectionId={ sectionId }
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

            { !itemId && dataSource !== 'template' ? (
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
                        { __('Select an item and a metadata section to display it.', 'tainacan') }
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
                        {__('Select Item and Metadata Section', 'tainacan')}
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
                        <InnerBlocks
                            allowedBlocks={ true }
                            template={ metadataSectionTemplate }
                            templateInsertUpdatesSelection={ true } />
                        : null
                    }
                </div>
            }
            
        </div>
    );
};