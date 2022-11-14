const { __ } = wp.i18n;

const { Button, ButtonGroup, BaseControl, Placeholder, RangeControl, ToggleControl, PanelBody } = wp.components;

const ServerSideRender = wp.serverSideRender;
const { InspectorControls, useBlockProps } = (tainacan_blocks.wp_version < '5.2' ? wp.editor : wp.blockEditor );

import SingleItemModal from '../../js/selection/single-item-modal.js';
import getCollectionIdFromPossibleTemplateEdition from '../../js/template/tainacan-blocks-single-item-template-mode.js';

export default function ({ attributes, setAttributes, className, isSelected, clientId }) {
    
    let {
        content, 
        collectionId,
        itemId,
        isModalOpen,
        layoutElements,
        mediaSources,
        hideFileNameMain,
        hideFileCaptionMain,
        hideFileDescriptionMain,
        hideFileNameThumbnails,
        hideFileCaptionThumbnails,
        hideFileDescriptionThumbnails,
        hideFileNameLightbox,
        hideFileCaptionLightbox,
        hideFileDescriptionLightbox,
        openLightboxOnClick,
        arrowsSize,
        mainSliderHeight,
        mainSliderWidth,          
        thumbnailsCarouselWidth,
        thumbnailsCarouselItemSize,
        showDownloadButtonMain,
        lightboxHasLightBackground,
        templateMode
    } = attributes;

    // Gets blocks props from hook
    const blockProps = tainacan_blocks.wp_version < '5.6' ? { className: className } : useBlockProps();
    const currentWPVersion = (typeof tainacan_blocks != 'undefined') ? tainacan_blocks.wp_version : tainacan_plugin.wp_version;

    // Obtains block's client id to render it on save function
    setAttributes({ blockId: clientId });

    // Checks if we are in template mode, if so, gets the collection Id from URL.
    if ( !templateMode ) {
        const possibleCollectionId = getCollectionIdFromPossibleTemplateEdition();
        if (possibleCollectionId) {
            collectionId = String(possibleCollectionId);
            templateMode = true;
            setAttributes({ 
                collectionId: collectionId,
                templateMode: templateMode
            });
        }
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
                        title={__('Media item sources', 'tainacan')}
                        initialOpen={ true }
                    >
                    <ToggleControl
                        label={__('Document', 'tainacan')}
                        checked={ mediaSources['document'] === true }
                        onChange={ ( isChecked ) => {
                                let updatedSources = Object.assign({},mediaSources);
                                updatedSources['document'] = isChecked;
                                setAttributes({ mediaSources: updatedSources });
                            } 
                        }
                    />
                    <ToggleControl
                        label={__('Attachments', 'tainacan')}
                        checked={ mediaSources['attachments'] === true }
                        onChange={ ( isChecked ) => {
                                let updatedSources = Object.assign({},mediaSources);
                                updatedSources['attachments'] = isChecked;
                                setAttributes({ mediaSources: updatedSources });
                            } 
                        }
                    />
                    {/* <ToggleControl
                        label={__('Metadata', 'tainacan')}
                        checked={ mediaSources['metadata'] === true }
                        onChange={ ( isChecked ) => {
                                let updatedSources = Object.assign({},mediaSources);
                                updatedSources['metadata'] = isChecked;
                                setAttributes({ mediaSources: updatedSources });
                            } 
                        }
                    /> */}
                </PanelBody>
                <PanelBody
                        title={__('Layout elements', 'tainacan')}
                        initialOpen={ true }
                    >
                    <ToggleControl
                        label={__('Main slider', 'tainacan')}
                        checked={ layoutElements['main'] === true }
                        onChange={ ( isChecked ) => {
                                let updatedElements = Object.assign({},layoutElements);
                                updatedElements['main'] = isChecked;
                                setAttributes({ layoutElements: updatedElements });
                            } 
                        }
                    />
                    <ToggleControl
                        label={__('Thumbnails carousel', 'tainacan')}
                        checked={ layoutElements['thumbnails'] === true }
                        onChange={ (isChecked) => {
                                let updatedElements = Object.assign({},layoutElements);
                                updatedElements['thumbnails'] = isChecked;
                                setAttributes({ layoutElements: updatedElements });
                            } 
                        }
                    />
                    <ToggleControl
                        label={__('Open lightbox on click', 'tainacan')}
                        checked={ openLightboxOnClick }
                        onChange={ ( isChecked ) => {
                                openLightboxOnClick = isChecked;
                                setAttributes({ openLightboxOnClick: openLightboxOnClick });
                            } 
                        }
                    />
                </PanelBody>
                { layoutElements['main'] === true ?
                    <PanelBody
                            title={__('Main slider settings', 'tainacan')}
                            initialOpen={ true }
                        >
                        <RangeControl
                            label={ __('Arrows size (px)', 'tainacan') }
                            value={ arrowsSize }
                            onChange={ ( updatedArrowsSize ) => {
                                arrowsSize = updatedArrowsSize;
                                setAttributes({ arrowsSize: updatedArrowsSize });
                            }}
                            min={ 8 }
                            max={ 64 }
                        />
                        <RangeControl
                            label={ __('Slider height (vh)', 'tainacan') }
                            value={ mainSliderHeight }
                            onChange={ ( updatedMainSliderHeight ) => {
                                mainSliderHeight = updatedMainSliderHeight;
                                setAttributes({ mainSliderHeight: updatedMainSliderHeight });
                            }}
                            min={ 10 }
                            max={ 150 }
                        />
                        <RangeControl
                            label={ __('Slider width (%)', 'tainacan') }
                            value={ mainSliderWidth }
                            onChange={ ( updatedMainSliderWidth ) => {
                                mainSliderWidth = updatedMainSliderWidth;
                                setAttributes({ mainSliderWidth: updatedMainSliderWidth });
                            }}
                            min={ 10 }
                            max={ 150 }
                        />
                        <ToggleControl
                            label={__('Hide file name', 'tainacan')}
                            checked={ hideFileNameMain }
                            onChange={ ( isChecked ) => {
                                    hideFileNameMain = isChecked;
                                    setAttributes({ hideFileNameMain: hideFileNameMain });
                                } 
                            }
                        />
                       <ToggleControl
                            label={__('Hide file caption', 'tainacan')}
                            checked={ hideFileCaptionMain }
                            onChange={ ( isChecked ) => {
                                    hideFileCaptionMain = isChecked;
                                    setAttributes({ hideFileCaptionMain: hideFileCaptionMain });
                                } 
                            }
                        />
                        <ToggleControl
                            label={__('Hide file description', 'tainacan')}
                            checked={ hideFileDescriptionMain }
                            onChange={ ( isChecked ) => {
                                    hideFileDescriptionMain = isChecked;
                                    setAttributes({ hideFileDescriptionMain: hideFileDescriptionMain });
                                } 
                            }
                        />
                        <ToggleControl
                            label={__('Show download button', 'tainacan')}
                            checked={ showDownloadButtonMain }
                            onChange={ ( isChecked ) => {
                                    showDownloadButtonMain = isChecked;
                                    setAttributes({ showDownloadButtonMain: showDownloadButtonMain });
                                } 
                            }
                        />
                    </PanelBody>
                : null }
                { layoutElements['thumbnails'] === true ?
                    <PanelBody
                            title={__('Thumbnails carousel settings', 'tainacan')}
                            initialOpen={ true }
                        >
                        <RangeControl
                            label={ __('Carousel width (%)', 'tainacan') }
                            value={ thumbnailsCarouselWidth }
                            onChange={ ( updatedThumbnailsCarouselWidth ) => {
                                thumbnailsCarouselWidth = updatedThumbnailsCarouselWidth;
                                setAttributes({ thumbnailsCarouselWidth: updatedThumbnailsCarouselWidth });
                            }}
                            min={ 10 }
                            max={ 150 }
                        />
                        <RangeControl
                            label={ __('Carousel item size (px)', 'tainacan') }
                            value={ thumbnailsCarouselItemSize }
                            onChange={ ( updatedThumbnailsCarouselItemSize ) => {
                                thumbnailsCarouselItemSize = updatedThumbnailsCarouselItemSize;
                                setAttributes({ thumbnailsCarouselItemSize: updatedThumbnailsCarouselItemSize });
                            }}
                            min={ 32 }
                            max={ 400 }
                        />
                        <ToggleControl
                            label={__('Hide file name', 'tainacan')}
                            checked={ hideFileNameThumbnails }
                            onChange={ ( isChecked ) => {
                                    hideFileNameThumbnails = isChecked;
                                    setAttributes({ hideFileNameThumbnails: hideFileNameThumbnails });
                                } 
                            }
                        />
                       <ToggleControl
                            label={__('Hide file caption', 'tainacan')}
                            checked={ hideFileCaptionThumbnails }
                            onChange={ ( isChecked ) => {
                                    hideFileCaptionThumbnails = isChecked;
                                    setAttributes({ hideFileCaptionThumbnails: hideFileCaptionThumbnails });
                                } 
                            }
                        />
                        <ToggleControl
                            label={__('Hide file description', 'tainacan')}
                            checked={ hideFileDescriptionThumbnails }
                            onChange={ ( isChecked ) => {
                                    hideFileDescriptionThumbnails = isChecked;
                                    setAttributes({ hideFileDescriptionThumbnails: hideFileDescriptionThumbnails });
                                } 
                            }
                        />
                    </PanelBody>
                : null }
                { openLightboxOnClick === true ?
                    <PanelBody
                            title={__('Lightbox settings', 'tainacan')}
                            initialOpen={ true }
                        >
                        <BaseControl
                                id="lightbox-color-scheme"
                                label={ __('Background color scheme', 'tainacan') }>
                            <ButtonGroup id="lightbox-color-scheme">   
                                <Button 
                                        onClick={ () => {
                                                lightboxHasLightBackground = false;
                                                setAttributes({ lightboxHasLightBackground: lightboxHasLightBackground });
                                            }
                                        }
                                        variant={ lightboxHasLightBackground ? 'secondary' : 'primary' }>
                                    { __('Dark', 'tainacan') }
                                </Button>
                                <Button 
                                        onClick={ () => {
                                                lightboxHasLightBackground = true;
                                                setAttributes({ lightboxHasLightBackground: lightboxHasLightBackground });
                                            }
                                        }
                                        variant={ lightboxHasLightBackground ? 'primary' : 'secondary' }>
                                    { __('Light', 'tainacan') }
                                </Button>
                            </ButtonGroup>
                        </BaseControl>
                        <ToggleControl
                            label={__('Hide file name', 'tainacan')}
                            checked={ hideFileNameLightbox }
                            onChange={ ( isChecked ) => {
                                    hideFileNameLightbox = isChecked;
                                    setAttributes({ hideFileNameLightbox: hideFileNameLightbox });
                                } 
                            }
                        />
                       <ToggleControl
                            label={__('Hide file caption', 'tainacan')}
                            checked={ hideFileCaptionLightbox }
                            onChange={ ( isChecked ) => {
                                    hideFileCaptionLightbox = isChecked;
                                    setAttributes({ hideFileCaptionLightbox: hideFileCaptionLightbox });
                                } 
                            }
                        />
                        <ToggleControl
                            label={__('Hide file description', 'tainacan')}
                            checked={ hideFileDescriptionLightbox }
                            onChange={ ( isChecked ) => {
                                    hideFileDescriptionLightbox = isChecked;
                                    setAttributes({ hideFileDescriptionLightbox: hideFileDescriptionLightbox });
                                } 
                            }
                        />
                    </PanelBody>
                : null }
            </InspectorControls>

            { isSelected ? 
                ( 
                <div>
                    { isModalOpen ?
                        <SingleItemModal
                            modalTitle={ __('Select one item to create its media gallery', 'tainacan') }
                            applyButtonLabel={ __('Create gallery from this item', 'tainacan') }
                            existingCollectionId={ collectionId }
                            existingItemId={ itemId }
                            onSelectCollection={ (selectedCollectionId) => {
                                collectionId = selectedCollectionId;
                                setAttributes({ 
                                    collectionId: collectionId
                                });
                            }}
                            onApplySelectedItem={ (selectedItemId) => {
                                itemId = selectedItemId;
                                setAttributes({
                                    itemId: itemId,
                                    isModalOpen: false
                                });
                            }}
                            onCancelSelection={ () => setAttributes({ isModalOpen: false }) }/> 
                        : null
                    }
                    
                </div>
                ) : null
            }

            { !itemId && !templateMode ? (
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
                        {__('Select an item to display its media gallery, including Document and Attachments.', 'tainacan')}
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
            
            {  itemId || templateMode ? (
                <div className={ 'item-gallery-edit-container' }>
                    <div class="preview-warning">{__('Warning: this is just a demonstration. To see the gallery in action, either preview or publish your post.', 'tainacan') }</div>
                    <ServerSideRender
                        block="tainacan/item-gallery"
                        attributes={ attributes }
                        httpMethod={ currentWPVersion >= '5.5' ? 'POST' : 'GET' }
                    />
                </div>
                ) : null
            }
            
        </div>
    );
};