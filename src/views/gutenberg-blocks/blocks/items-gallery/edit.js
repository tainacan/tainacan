const { __ } = wp.i18n;

const { Button, ButtonGroup, BaseControl, Placeholder, SelectControl, RangeControl, ToggleControl, PanelBody } = wp.components;

const ServerSideRender = wp.serverSideRender;
const { InspectorControls, BlockControls, useBlockProps, store } = wp.blockEditor;

const { useSelect } = wp.data;

import map from 'lodash/map'; // Do not user import { map,pick } from 'lodash'; -> These causes conflicts with underscore due to lodash global variable
import pick from 'lodash/pick';

import DynamicItemsModal from '../carousel-items-list/dynamic-and-carousel-items-modal.js';
import TainacanBlocksCompatToolbar from '../../js/compatibility/tainacan-blocks-compat-toolbar.js';

export default function ({ attributes, setAttributes, isSelected, clientId }) {
    
    let {
        collectionId,
        searchURL,
        selectedItems,
        loadStrategy,
        maxItemsNumber,
        isModalOpen,
        layoutElements,
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
        lightboxHasLightBackground,
        thumbnailsSize,
        thumbsHaveFixedHeight
    } = attributes;

    // Gets blocks props from hook
    const blockProps = useBlockProps();

    // Obtains block's client id to render it on save function
    setAttributes({ blockId: clientId });

    // Get available image sizes
    const {	imageSizes } = useSelect(
        ( select ) => {
            const {	getSettings	} = select( store );

            const settings = pick( getSettings(), [
                'imageSizes'
            ] );
            return settings
        },
        [ clientId ]
    );
    const imageSizeOptions = map(
        imageSizes,
        ( { name, slug } ) => ( { value: slug, label: name } )
    );

    function openDynamicItemsModal(aLoadStrategy) {
        loadStrategy = aLoadStrategy;
        isModalOpen = true;
        setAttributes( { 
            isModalOpen: isModalOpen,
            loadStrategy: loadStrategy
        } );
    }

    return <div { ...blockProps }>

        {  (
            ( loadStrategy == 'selection' && selectedItems.length != 0 ) ||
            ( loadStrategy == 'search' && searchURL )
        ) ?
            <BlockControls>
                { loadStrategy != 'parent' ? 
                    (
                        loadStrategy == 'selection' ?
                        TainacanBlocksCompatToolbar({
                            label: __('Add more items', 'tainacan'),
                            icon: <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        viewBox="0 -2 24 24"
                                        height="24px"
                                        width="24px">
                                    <path d="M14,2V4H7v7.24A5.33,5.33,0,0,0,5.5,11a4.07,4.07,0,0,0-.5,0V4A2,2,0,0,1,7,2Zm7,10v8a2,2,0,0,1-2,2H12l1-1-2.41-2.41A5.56,5.56,0,0,0,11,16.53a5.48,5.48,0,0,0-2-4.24V8a2,2,0,0,1,2-2h4Zm-2.52,0L14,7.5V12ZM11,21l-1,1L8.86,20.89,8,20H8l-.57-.57A3.42,3.42,0,0,1,5.5,20a3.5,3.5,0,0,1-.5-7,2.74,2.74,0,0,1,.5,0,3.41,3.41,0,0,1,1.5.34,3.5,3.5,0,0,1,2,3.16,3.42,3.42,0,0,1-.58,1.92L9,19H9l.85.85Zm-4-4.5A1.5,1.5,0,0,0,5.5,15a1.39,1.39,0,0,0-.5.09A1.5,1.5,0,0,0,5.5,18a1.48,1.48,0,0,0,1.42-1A1.5,1.5,0,0,0,7,16.53Z"/>
                                </svg>,
                            onClick: openDynamicItemsModal,
                            onClickParams: 'selection'
                        })
                        :
                        TainacanBlocksCompatToolbar({
                            label: __('Configure a search', 'tainacan'),
                            icon: <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        viewBox="0 -2 24 24"
                                        height="24px"
                                        width="24px">
                                    <path d="M14,2V4H7v7.24A5.33,5.33,0,0,0,5.5,11a4.07,4.07,0,0,0-.5,0V4A2,2,0,0,1,7,2Zm7,10v8a2,2,0,0,1-2,2H12l1-1-2.41-2.41A5.56,5.56,0,0,0,11,16.53a5.48,5.48,0,0,0-2-4.24V8a2,2,0,0,1,2-2h4Zm-2.52,0L14,7.5V12ZM11,21l-1,1L8.86,20.89,8,20H8l-.57-.57A3.42,3.42,0,0,1,5.5,20a3.5,3.5,0,0,1-.5-7,2.74,2.74,0,0,1,.5,0,3.41,3.41,0,0,1,1.5.34,3.5,3.5,0,0,1,2,3.16,3.42,3.42,0,0,1-.58,1.92L9,19H9l.85.85Zm-4-4.5A1.5,1.5,0,0,0,5.5,15a1.39,1.39,0,0,0-.5.09A1.5,1.5,0,0,0,5.5,18a1.48,1.48,0,0,0,1.42-1A1.5,1.5,0,0,0,7,16.53Z"/>
                                </svg>,
                            onClick: openDynamicItemsModal,
                            onClickParams: 'search'
                        })
                    ) : null
                }
            </BlockControls>
        : null }

            <InspectorControls>

                <PanelBody
                        title={__('Items', 'tainacan')}
                        initialOpen={ true }
                        className="wp-block-tainacan-dynamic-items-list-inspector"
                    >
                    { loadStrategy == 'search' ?
                        <div>
                            <RangeControl
                                label={__('Maximum number of items', 'tainacan')}
                                value={ maxItemsNumber ? maxItemsNumber : 12 }
                                onChange={ ( aMaxItemsNumber ) => {
                                    maxItemsNumber = Number(aMaxItemsNumber);
                                    setAttributes( { maxItemsNumber: aMaxItemsNumber } ) 
                                }}
                                min={ 1 }
                                max={ tainacan_blocks.api_max_items_per_page ? Number(tainacan_blocks.api_max_items_per_page) : 96 }
                            />
                        <hr></hr>
                    </div>
                        : null }
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
                    </PanelBody>
                : null }
                { layoutElements['thumbnails'] === true ?
                    <PanelBody
                            title={__('Thumbnails carousel settings', 'tainacan')}
                            initialOpen={ true }
                        >
                        <SelectControl
                                label={__('Image size', 'tainacan')}
                                value={ thumbnailsSize }
                                options={ imageSizeOptions }
                                onChange={ ( aThumbnailsSize ) => { 
                                    thumbnailsSize = aThumbnailsSize;
                                    setAttributes({ thumbnailsSize: thumbnailsSize });
                                }}
                            />
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
                            label={ __('Thumbnails have fixed height', 'tainacan') }
                            help={ __( 'If checked, the thumbnails will have fixed the item size height, otherwise they will have fixed the item size width.', 'tainacan' ) }
                            checked={ thumbsHaveFixedHeight }
                            onChange={ ( isChecked ) => {
                                thumbsHaveFixedHeight = isChecked;
                                setAttributes({ thumbsHaveFixedHeight: thumbsHaveFixedHeight });
                            }}
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
                            <DynamicItemsModal
                                loadStrategy={ loadStrategy }
                                existingCollectionId={ collectionId } 
                                existingSearchURL={ searchURL } 
                                onSelectCollection={ (selectedCollectionId) => {
                                    if (collectionId != selectedCollectionId) {
                                        selectedItems = [];
                                    }
                                    collectionId = selectedCollectionId;
                                    setAttributes({ 
                                        collectionId: collectionId,
                                        selectedItems: selectedItems
                                    });
                                    fetchCollectionForHeader();
                                }}
                                onApplySearchURL={ (aSearchURL) =>{
                                    searchURL = aSearchURL;
                                    loadStrategy = 'search';
                                    setAttributes({
                                        searchURL: searchURL,
                                        isModalOpen: false
                                    });
                                }}
                                onApplySelectedItems={ (aSelectionOfItems) => {
                                    selectedItems = selectedItems.concat(aSelectionOfItems); 
                                    loadStrategy = 'selection';
                                    setAttributes({
                                        selectedItems: selectedItems,
                                        loadStrategy: loadStrategy,
                                        isModalOpen: false
                                    });
                                }}
                                onCancelSelection={ () => setAttributes({ isModalOpen: false }) }/> 
                            : null
                        }
                        
                    </div>
                    ) : null
                }

            { 
            (
                (
                    ( loadStrategy == 'selection' && selectedItems.length == 0 ) ||
                    ( loadStrategy == 'search' && !searchURL )
                )
             ) ? (
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
                        <path d="M14,2V4H7v7.24A5.33,5.33,0,0,0,5.5,11a4.07,4.07,0,0,0-.5,0V4A2,2,0,0,1,7,2Zm7,10v8a2,2,0,0,1-2,2H12l1-1-2.41-2.41A5.56,5.56,0,0,0,11,16.53a5.48,5.48,0,0,0-2-4.24V8a2,2,0,0,1,2-2h4Zm-2.52,0L14,7.5V12ZM11,21l-1,1L8.86,20.89,8,20H8l-.57-.57A3.42,3.42,0,0,1,5.5,20a3.5,3.5,0,0,1-.5-7,2.74,2.74,0,0,1,.5,0,3.41,3.41,0,0,1,1.5.34,3.5,3.5,0,0,1,2,3.16,3.42,3.42,0,0,1-.58,1.92L9,19H9l.85.85Zm-4-4.5A1.5,1.5,0,0,0,5.5,15a1.39,1.39,0,0,0-.5.09A1.5,1.5,0,0,0,5.5,18a1.48,1.48,0,0,0,1.42-1A1.5,1.5,0,0,0,7,16.53Z"/>
                    </svg>
                        {__('List items from a Tainacan items search in a zoomable gallery slider', 'tainacan')}
                    </p>
                    { 
                        loadStrategy != 'parent' ?
                            <div>
                                <Button
                                    isPrimary
                                    type="button"
                                    onClick={ () => openDynamicItemsModal('selection') }>
                                    {__('Select Items', 'tainacan')}
                                </Button> 
                                <p style={{ margin: '0 12px' }}>{__('or', 'tainacan')}</p>
                                <Button
                                    isPrimary
                                    type="button"
                                    onClick={ () => openDynamicItemsModal('search') }>
                                    {__('Configure a search', 'tainacan')}
                                </Button>
                            </div>
                        : null
                    }
                </Placeholder>
                ) : null
            }
            
            {  (
                    ( loadStrategy == 'selection' && selectedItems.length ) ||
                    ( loadStrategy == 'search' && searchURL )
                ) ? (
                <div className={ 'items-gallery-edit-container' }>
                    <div className="preview-warning">{__('Warning: this is just a demonstration. To see the gallery in action, either preview or publish your post.', 'tainacan') }</div>
                    <ServerSideRender
                        block="tainacan/items-gallery"
                        attributes={ attributes }
                        httpMethod={ 'POST' }
                    />
                </div>
                ) : null
            }
            
        </div>;
};