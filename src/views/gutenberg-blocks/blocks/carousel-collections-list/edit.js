const { RangeControl, Spinner, Button, BaseControl, ToggleControl, SelectControl, Placeholder, IconButton, PanelBody } = wp.components;

const { InspectorControls, BlockControls, useBlockProps, store } = (tainacan_blocks.wp_version < '5.2' ? wp.editor : wp.blockEditor );

const { __ } = wp.i18n;

const { useSelect } = wp.data;

import map from 'lodash/map'; // Do not user import { map,pick } from 'lodash'; -> These causes conflicts with underscore due to lodash global variable
import pick from 'lodash/pick';
import CarouselCollectionsModal from './carousel-collections-modal.js';
import tainacan from '../../js/axios.js';
import axios from 'axios';
import qs from 'qs';
import { ThumbnailHelperFunctions } from '../../../admin/js/utilities.js';
import TainacanBlocksCompatToolbar from '../../js/compatibility/tainacan-blocks-compat-toolbar.js';
import 'swiper/css';
import 'swiper/css/a11y';
import 'swiper/css/autoplay';
import 'swiper/css/navigation';

export default function ({ attributes, setAttributes, className, isSelected, clientId }) {
    let {
        collections,
        content,
        isModalOpen,
        itemsRequestSource,
        selectedCollections,
        largeArrows,
        arrowsStyle,
        imageSize,
        maxCollectionsPerScreen,
        spaceBetweenCollections,
        spaceAroundCarousel,
        isLoading,
        arrowsPosition,
        autoPlay,
        autoPlaySpeed,
        loopSlides,
        hideName,
        showCollectionThumbnail
    } = attributes;

    // Gets blocks props from hook
    const blockProps = tainacan_blocks.wp_version < '5.6' ? { className: className } : useBlockProps();

    // Obtains block's client id to render it on save function
    setAttributes({ blockId: clientId });

    // Sets some defaults that were not working
    if (maxCollectionsPerScreen === undefined) {
        maxCollectionsPerScreen = 6;
        setAttributes({ maxCollectionsPerScreen: maxCollectionsPerScreen });
    }
    if (imageSize === undefined) {
        imageSize = 'tainacan-medium';
        setAttributes({ imageSize: imageSize });
    }

    const thumbHelper = ThumbnailHelperFunctions();

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

    function prepareItem(collection, collectionItems) {
        return (
            <li
                key={ collection.id }
                className={ 'swiper-slide collection-list-item ' + (!showCollectionThumbnail ? 'collection-list-item-grid ' : '') + (maxCollectionsPerScreen ? ' max-collections-per-screen-' + maxCollectionsPerScreen : '') }>
                { tainacan_blocks.wp_version < '5.4' ?
                    <IconButton
                        onClick={ () => removeItemOfId(collection.id) }
                        icon="no-alt"
                        label={__('Remove', 'tainacan')}/>
                        :
                    <Button
                        onClick={ () => removeItemOfId(collection.id) }
                        icon="no-alt"
                        label={__('Remove', 'tainacan')}/>
                }
                <a
                    id={ isNaN(collection.id) ? collection.id : 'collection-id-' + collection.id }
                    href={ collection.url }>
                    { ( !showCollectionThumbnail && Array.isArray(collectionItems) ) ?
                        <div class="collection-items-grid">
                            <img
                                src={ collectionItems[0] ? thumbHelper.getSrc(collectionItems[0]['thumbnail'], imageSize, collectionItems[0]['document_mimetype']) :`${tainacan_blocks.base_url}/assets/images/placeholder_square.png` }
                                srcSet={ collectionItems[0] ? thumbHelper.getSrcSet(collectionItems[0]['thumbnail'], imageSize, collectionItems[0]['document_mimetype']) :`${tainacan_blocks.base_url}/assets/images/placeholder_square.png` }
                                alt={ collectionItems[0] && collectionItems[0].thumbnail_alt ? collectionItems[0].thumbnail_alt : (collectionItems[0] && collectionItems[0].name ? collectionItems[0].name : __( 'Thumbnail', 'tainacan' )) } />
                            <img
                                    src={ collectionItems[1] ? thumbHelper.getSrc(collectionItems[1]['thumbnail'], imageSize, collectionItems[1]['document_mimetype']) :`${tainacan_blocks.base_url}/assets/images/placeholder_square.png` }
                                    srcSet={ collectionItems[1] ? thumbHelper.getSrcSet(collectionItems[1]['thumbnail'], imageSize, collectionItems[1]['document_mimetype']) :`${tainacan_blocks.base_url}/assets/images/placeholder_square.png` }
                                    alt={ collectionItems[1] && collectionItems[1].thumbnail_alt ? collectionItems[1].thumbnail_alt : (collectionItems[1] && collectionItems[1].name ? collectionItems[1].name : __( 'Thumbnail', 'tainacan' )) } />
                            <img
                                    src={ collectionItems[2] ? thumbHelper.getSrc(collectionItems[2]['thumbnail'], imageSize, collectionItems[2]['document_mimetype']) :`${tainacan_blocks.base_url}/assets/images/placeholder_square.png` }
                                    srcSet={ collectionItems[2] ? thumbHelper.getSrcSet(collectionItems[2]['thumbnail'], imageSize, collectionItems[2]['document_mimetype']) :`${tainacan_blocks.base_url}/assets/images/placeholder_square.png` }
                                    alt={ collectionItems[2] && collectionItems[2].thumbnail_alt ? collectionItems[2].thumbnail_alt : (collectionItems[2] && collectionItems[2].name ? collectionItems[2].name : __( 'Thumbnail', 'tainacan' )) } />
                        </div>
                        :
                        <img
                            src={
                                collection.thumbnail && collection.thumbnail[imageSize] && collection.thumbnail[imageSize][0]
                                    ?
                                collection.thumbnail[imageSize][0]
                                    :
                                (collection.thumbnail && collection.thumbnail['thumbnail'][0] && collection.thumbnail['thumbnail'][0]
                                    ?
                                collection.thumbnail['thumbnail'][0]
                                    :
                                `${tainacan_blocks.base_url}/assets/images/placeholder_square.png`)
                            }
                            alt={ collection.name ? collection.name : __( 'Thumbnail', 'tainacan' ) }/>
                    }
                    { !hideName ? <span>{ collection.name ? collection.name : '' }</span> : null }
                </a>
            </li>
        );
    }

    function setContent() {

        isLoading = true;
        setAttributes({
            isLoading: isLoading
        });

        if (itemsRequestSource != undefined && typeof itemsRequestSource == 'function')
            itemsRequestSource.cancel('Previous collections search canceled.');

        itemsRequestSource = axios.CancelToken.source();

        collections = [];

        let endpoint = '/collections?'+ qs.stringify({ postin: selectedCollections.map((collection) => { return collection.id }), perpage: selectedCollections.length, fetch_preview_image_items: showCollectionThumbnail ? 0 : 3 }) + '&orderby=post__in&fetch_only=name,url,thumbnail';
        tainacan.get(endpoint, { cancelToken: itemsRequestSource.token })
            .then(response => {

                for (let collection of response.data)
                    collections.push(prepareItem(collection, (!showCollectionThumbnail && collection.preview_image_items) ? collection.preview_image_items : []));
            
                isLoading = false;
                setAttributes({
                    content: <div></div>,
                    collections: collections,
                    isLoading: isLoading,
                    itemsRequestSource: itemsRequestSource
                });
            });
    }

    function openCarouselModal() {
        isModalOpen = true;
        setAttributes( {
            isModalOpen: isModalOpen
        } );
    }

    function removeItemOfId(itemId) {

        let existingItemIndex = collections.findIndex((existingItem) => existingItem.key == itemId);
        if (existingItemIndex >= 0)
            collections.splice(existingItemIndex, 1);

        let existingSelectedItemIndex = selectedCollections.findIndex((existingSelectedItem) => existingSelectedItem.id == itemId);
        if (existingSelectedItemIndex >= 0)
            selectedCollections.splice(existingSelectedItemIndex, 1);

        setAttributes({
            selectedCollections: selectedCollections,
            collections: collections,
            content: <div></div>
        });
    }

    // Executed only on the first load of page
    if(content && content.length && content[0].type)
        setContent();

    return content == 'preview' ?
            <div className={className}>
                <img
                        width="100%"
                        src={ `${tainacan_blocks.base_url}/assets/images/carousel-collections-list.png` } />
            </div>
        : (
        <div { ...blockProps }>

            { collections.length ?
                <BlockControls>
                    {
                        TainacanBlocksCompatToolbar({
                            label: __('Add more collections', 'tainacan'),
                            icon: <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        viewBox="0 -2 24 24"
                                        height="24px"
                                        width="24px">
                                    <path d="M18,17v2H12a5.65,5.65,0,0,0-.36-2ZM2,7v7.57a5.74,5.74,0,0,1,2-1.2V7ZM20,6H15L13,4H8A2,2,0,0,0,6,6v7a6,6,0,0,1,5.19,3H20a2,2,0,0,0,2-2V8A2,2,0,0,0,20,6ZM7,16.05v6.06l3.06-3.06ZM5,22.11V16.05L1.94,19.11Z"/>
                                </svg>,
                            onClick: openCarouselModal
                        })
                    }
                </BlockControls>
            : null }

            <div>
                <InspectorControls>

                    <PanelBody
                            title={__('Carousel', 'tainacan')}
                            initialOpen={ true }
                        >
                            <BaseControl
                                    id="collection-carousel-view-modes"
                                    label={ __('Collection layout', 'tainacan')}>
                                <div className="collection-carousel-view-modes">
                                    <button
                                            onClick={ () => {
                                                    showCollectionThumbnail = false;
                                                    setAttributes({ showCollectionThumbnail: showCollectionThumbnail });
                                                    setContent();
                                                }
                                            }
                                            className={'collection-carousel-view-mode-grid' + (showCollectionThumbnail ? '' : ' is-active')}>
                                        <div>
                                            <div />
                                        <div />
                                        <div />
                                        </div>
                                        <label>{ __('Items\'s grid', 'tainacan') }</label>
                                    </button>
                                    <button
                                            onClick={ () => {
                                                    showCollectionThumbnail = true;
                                                    setAttributes({ showCollectionThumbnail: showCollectionThumbnail });
                                                    setContent();
                                                }
                                            }
                                            className={'collection-carousel-view-mode-thumbnail' + (showCollectionThumbnail ? ' is-active' : '')}>
                                        <div />
                                        <label>{ __('Thumbnail', 'tainacan') }</label>
                                    </button>
                                </div>
                            </BaseControl>
                            <RangeControl
                                    label={ __('Maximum collections per slide on a wide screen', 'tainacan') }
                                    help={ (showCollectionThumbnail && maxCollectionsPerScreen <= 4) ? __('Warning: with such a small number of collections per slide, the slide item is larger, thus you must also set a larger image size.', 'tainacan') : null }
                                    value={ maxCollectionsPerScreen ? maxCollectionsPerScreen : 6 }
                                    onChange={ ( aMaxCollectionsPerScreen ) => {
                                        maxCollectionsPerScreen = Number(aMaxCollectionsPerScreen);
                                        setAttributes( { maxCollectionsPerScreen: aMaxCollectionsPerScreen } );
                                        setContent();
                                    }}
                                    min={ 1 }
                                    max={ 9 }
                                />
                            <SelectControl
                                label={__('Image size', 'tainacan')}
                                value={ imageSize }
                                options={ imageSizeOptions }
                                onChange={ ( anImageSize ) => { 
                                    imageSize = anImageSize;
                                    setAttributes({ imageSize: imageSize });
                                    setContent();
                                }}
                            />
                            <RangeControl
                                    label={ __('Space between each collection', 'tainacan') }
                                    value={ !isNaN(spaceBetweenCollections) ? spaceBetweenCollections : 32 }
                                    onChange={ ( aSpaceBetweenCollections ) => {
                                        spaceBetweenCollections = Number(aSpaceBetweenCollections);
                                        setAttributes( { spaceBetweenCollections: aSpaceBetweenCollections } );
                                    }}
                                    min={ 0 }
                                    max={ 98 }
                                />
                            <ToggleControl
                                    label={__('Hide name', 'tainacan')}
                                    help={ !hideName ? __('Toggle to hide collection\'s name', 'tainacan') : __('Do not hide collection\'s name', 'tainacan')}
                                    checked={ hideName }
                                    onChange={ ( isChecked ) => {
                                            hideName = isChecked;
                                            setAttributes({ hideName: hideName });
                                            setContent();
                                        }
                                    }
                                />
                            <ToggleControl
                                    label={__('Loop slides', 'tainacan')}
                                    help={ !loopSlides ? __('Toggle to make slides loop from first to last', 'tainacan') : __('Do not loop slides from first to last', 'tainacan')}
                                    checked={ loopSlides }
                                    onChange={ ( isChecked ) => {
                                            loopSlides = isChecked;
                                            setAttributes({ loopSlides: loopSlides });
                                        }
                                    }
                                />
                            <ToggleControl
                                    label={__('Auto play', 'tainacan')}
                                    help={ !autoPlay ? __('Toggle to automatically slide to next collection', 'tainacan') : __('Do not automatically slide to next collection', 'tainacan')}
                                    checked={ autoPlay }
                                    onChange={ ( isChecked ) => {
                                            autoPlay = isChecked;
                                            setAttributes({ autoPlay: autoPlay });
                                        }
                                    }
                                />
                            {
                                autoPlay ?
                                    <RangeControl
                                        label={__('Seconds before transitioning to next', 'tainacan')}
                                        value={ autoPlaySpeed ? autoPlaySpeed : 3 }
                                        onChange={ ( aAutoPlaySpeed ) => {
                                            autoPlaySpeed = Number(aAutoPlaySpeed);
                                            setAttributes( { autoPlaySpeed: aAutoPlaySpeed } )
                                        }}
                                        min={ 1 }
                                        max={ 5 }
                                    />
                                : null
                            }
                            <SelectControl
                                label={__('Arrows', 'tainacan')}
                                value={ arrowsPosition }
                                options={ [
                                    { label: __('Around', 'tainacan'), value: 'around' },
                                    { label: __('Left', 'tainacan'), value: 'left' },
                                    { label: __('Right', 'tainacan'), value: 'right' }
                                ] }
                                onChange={ ( aPosition ) => {
                                    arrowsPosition = aPosition;
                                    setAttributes({ arrowsPosition: arrowsPosition });
                                }}/>
                            <SelectControl
                                label={__('Arrows icon style', 'tainacan')}
                                value={ arrowsStyle }
                                options={ [
                                    { label: __('Default', 'tainacan'), value: 'type-1' },
                                    { label: __('Alternative', 'tainacan'), value: 'type-2' }
                                ] }
                                onChange={ ( aStyle ) => {
                                    arrowsStyle = aStyle;
                                    setAttributes({ arrowsStyle: arrowsStyle });
                                }}/>
                            <ToggleControl
                                label={__('Large arrows', 'tainacan')}
                                help={ !largeArrows ? __('Toggle to display arrows bigger than the default size.', 'tainacan') : __('Do not show arrows bigger than the default size.', 'tainacan')}
                                checked={ largeArrows }
                                onChange={ ( isChecked ) => {
                                        largeArrows = isChecked;
                                        setAttributes({ largeArrows: largeArrows });
                                    }
                                }
                            />
                            <RangeControl
                                    label={ __('Space around the carousel', 'tainacan') }
                                    value={ !isNaN(spaceAroundCarousel) ? spaceAroundCarousel : 50 }
                                    onChange={ ( aSpaceAroundCarousel ) => {
                                        spaceAroundCarousel = Number(aSpaceAroundCarousel);
                                        setAttributes( { spaceAroundCarousel: aSpaceAroundCarousel } );
                                    }}
                                    min={ 0 }
                                    max={ 200 }
                                />
                    </PanelBody>
                </InspectorControls>
            </div>

            { isSelected ?
                (
                <div>
                    { isModalOpen ?
                        <CarouselCollectionsModal
                            selectedCollectionsObject={ selectedCollections }
                            onApplySelection={ (aSelectionOfCollections) => {
                                selectedCollections = aSelectionOfCollections;
                                setAttributes({
                                    selectedCollections: aSelectionOfCollections,
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

            { !collections.length && !isLoading ? (
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
                            <path d="M18,17v2H12a5.65,5.65,0,0,0-.36-2ZM2,7v7.57a5.74,5.74,0,0,1,2-1.2V7ZM20,6H15L13,4H8A2,2,0,0,0,6,6v7a6,6,0,0,1,5.19,3H20a2,2,0,0,0,2-2V8A2,2,0,0,0,20,6ZM7,16.05v6.06l3.06-3.06ZM5,22.11V16.05L1.94,19.11Z"/>
                        </svg>
                        {__('List collections on a Carousel, showing their thumbnails or a preview of items.', 'tainacan')}
                    </p>
                    <Button
                        isPrimary
                        type="button"
                        onClick={ () => openCarouselModal() }>
                        {__('Select Collections', 'tainacan')}
                    </Button>
                </Placeholder>
                ) : null
            }

            { isLoading ?
                <div class="spinner-container">
                    <Spinner />
                </div> :
                <div>
                    { isSelected && collections.length ?
                        <div class="preview-warning">{__('Warning: this is just a demonstration. To see the carousel in action, either preview or publish your post.', 'tainacan')}</div>
                        : null
                    }
                    {  collections.length ? (
                        <div
                                className={'collections-list-edit-container ' + (arrowsPosition ? 'has-arrows-' + arrowsPosition : '') + (largeArrows ? ' has-large-arrows' : '') }
                                style={{
                                    '--spaceBetweenCollections': !isNaN(spaceBetweenCollections) ? (spaceBetweenCollections + 'px') : '32px',
                                    '--spaceAroundCarousel': !isNaN(spaceAroundCarousel) ? (spaceAroundCarousel + 'px') : '50px'
                                }}>
                            <div className={'swiper'}>
                                <ul className={'swiper-wrapper collections-list-edit'}>
                                    { collections }
                                </ul>
                            </div>
                            <button
                                    class="swiper-button-prev"
                                    slot="button-prev"
                                    style={{ cursor: 'not-allowed' }}>
                                <svg
                                        width={ largeArrows ? 60 : 42 }
                                        height={ largeArrows ? 60 : 42 }
                                        viewBox="0 0 24 24">
                                    {
                                        arrowsStyle === 'type-2' ?
                                            <path d="M 10.694196,6 12.103795,7.4095983 8.5000002,11.022321 H 19.305804 v 1.955358 H 8.5000002 L 12.103795,16.590402 10.694196,18 4.6941962,12 Z"/>
                                            :
                                            <path d="M15.41 7.41L14 6l-6 6 6 6 1.41-1.41L10.83 12z"/>
                                    }
                                    <path
                                            d="M0 0h24v24H0z"
                                            fill="none"/>
                                </svg>
                            </button>
                            <button
                                    class="swiper-button-next"
                                    slot="button-next"
                                    style={{ cursor: 'not-allowed' }}>
                                <svg
                                        width={ largeArrows ? 60 : 42 }
                                        height={ largeArrows ? 60 : 42 }
                                        viewBox="0 0 24 24">
                                    {
                                        arrowsStyle === 'type-2' ?
                                            <path d="M 13.305804,6 11.896205,7.4095983 15.5,11.022321 H 4.6941964 v 1.955358 H 15.5 L 11.896205,16.590402 13.305804,18 l 6,-6 z"/>
                                            :
                                            <path d="M10 6L8.59 7.41 13.17 12l-4.58 4.59L10 18l6-6z"/>
                                    }
                                    <path
                                            d="M0 0h24v24H0z"
                                            fill="none"/>
                                </svg>
                            </button>
                        </div>
                    ):null
                    }
                </div>
            }
        </div>
    );
};
