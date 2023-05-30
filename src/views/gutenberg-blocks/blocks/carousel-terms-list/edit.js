const { __ } = wp.i18n;

const { RangeControl, Spinner, Button, BaseControl, ToggleControl, SelectControl, Placeholder, IconButton, PanelBody } = wp.components;

const { InspectorControls, BlockControls, useBlockProps, store } = (tainacan_blocks.wp_version < '5.2' ? wp.editor : wp.blockEditor );

const { useSelect } = wp.data;

import map from 'lodash/map'; // Do not user import { map,pick } from 'lodash'; -> These causes conflicts with underscore due to lodash global variable
import pick from 'lodash/pick';
import TermsModal from '../terms-list/terms-modal.js';
import tainacan from '../../js/axios.js';
import axios from 'axios';
import qs from 'qs';
import { ThumbnailHelperFunctions } from '../../../admin/js/utilities.js';
import TainacanBlocksCompatToolbar from '../../js/compatibility/tainacan-blocks-compat-toolbar.js';
import 'swiper/css';
import 'swiper/css/a11y';
import 'swiper/css/autoplay';
import 'swiper/css/navigation';

export default function({ attributes, setAttributes, className, isSelected, clientId }){
    let {
        terms,
        content,
        isModalOpen,
        itemsRequestSource,
        selectedTerms,
        isLoading,
        largeArrows,
        arrowsStyle,
        maxTermsPerScreen,
        spaceBetweenTerms,
        spaceAroundCarousel,
        arrowsPosition,
        autoPlay,
        autoPlaySpeed,
        loopSlides,
        hideName,
        showTermThumbnail,
        taxonomyId,
        imageSize
    } = attributes;

    // Gets blocks props from hook
    const blockProps = tainacan_blocks.wp_version < '5.6' ? { className: className } : useBlockProps();

    // Obtains block's client id to render it on save function
    setAttributes({ blockId: clientId });

    // Sets some defaults that were not working
    if (maxTermsPerScreen === undefined) {
        maxTermsPerScreen = 6;
        setAttributes({ maxTermsPerScreen: maxTermsPerScreen });
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

    function prepareItem(term, termItems) {
        return (
            <li
                key={ term.id }
                className={ 'swiper-slide term-list-item ' + (!showTermThumbnail ? 'term-list-item-grid ' : '') + (maxTermsPerScreen ? ' max-terms-per-screen-' + maxTermsPerScreen : '') }>
                { tainacan_blocks.wp_version < '5.4' ?
                    <IconButton
                        onClick={ () => removeItemOfId(term.id) }
                        icon="no-alt"
                        label={__('Remove', 'tainacan')}/>
                    :
                    <Button
                        onClick={ () => removeItemOfId(term.id) }
                        icon="no-alt"
                        label={__('Remove', 'tainacan')}/>
                }
                <a
                    id={ isNaN(term.id) ? term.id : 'term-id-' + term.id }
                    href={ term.url }>
                    { ( !showTermThumbnail && Array.isArray(termItems) ) ?
                        <div class="term-items-grid">
                            <img
                                src={ termItems[0] ? thumbHelper.getSrc(termItems[0]['thumbnail'], 'tainacan-medium', termItems[0]['document_mimetype']) :`${tainacan_blocks.base_url}/assets/images/placeholder_square.png` }
                                srcSet={ termItems[0] ? thumbHelper.getSrcSet(termItems[0]['thumbnail'], 'tainacan-medium', termItems[0]['document_mimetype']) :`${tainacan_blocks.base_url}/assets/images/placeholder_square.png` }
                                alt={ termItems[0] && termItems[0].thumbnail_alt ? termItems[0].thumbnail_alt : (termItems[0] && termItems[0].name ? termItems[0].name : __( 'Thumbnail', 'tainacan' )) } />
                            <img
                                    src={ termItems[1] ? thumbHelper.getSrc(termItems[1]['thumbnail'], 'tainacan-medium', termItems[1]['document_mimetype']) :`${tainacan_blocks.base_url}/assets/images/placeholder_square.png` }
                                    srcSet={ termItems[1] ? thumbHelper.getSrcSet(termItems[1]['thumbnail'], 'tainacan-medium', termItems[1]['document_mimetype']) :`${tainacan_blocks.base_url}/assets/images/placeholder_square.png` }
                                    alt={ termItems[1] && termItems[1].thumbnail_alt ? termItems[1].thumbnail_alt : (termItems[1] && termItems[1].name ? termItems[1].name : __( 'Thumbnail', 'tainacan' )) } />
                            <img
                                    src={ termItems[2] ? thumbHelper.getSrc(termItems[2]['thumbnail'], 'tainacan-medium', termItems[2]['document_mimetype']) :`${tainacan_blocks.base_url}/assets/images/placeholder_square.png` }
                                    srcSet={ termItems[2] ? thumbHelper.getSrcSet(termItems[2]['thumbnail'], 'tainacan-medium', termItems[2]['document_mimetype']) :`${tainacan_blocks.base_url}/assets/images/placeholder_square.png` }
                                    alt={ termItems[2] && termItems[2].thumbnail_alt ? termItems[2].thumbnail_alt : (termItems[2] && termItems[2].name ? termItems[2].name : __( 'Thumbnail', 'tainacan' )) } />
                        </div>
                        :
                        <img
                            src={
                                term.thumbnail && term.thumbnail[imageSize] && term.thumbnail[imageSize][0]
                                    ?
                                term.thumbnail[imageSize][0]
                                    :
                                (term.thumbnail && term.thumbnail['thumbnail'][0] && term.thumbnail['thumbnail'][0]
                                    ?
                                term.thumbnail['thumbnail'][0]
                                    :
                                `${tainacan_blocks.base_url}/assets/images/placeholder_square.png`)
                            }
                            alt={ term.thumbnail_alt ? term.thumbnail_alt : (term.name ? term.name : __( 'Thumbnail', 'tainacan' ) ) }/>
                    }
                    { !hideName ? <span>{ term.name ? term.name : '' }</span> : null }
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
            itemsRequestSource.cancel('Previous terms search canceled.');

        itemsRequestSource = axios.CancelToken.source();

        terms = [];

        let endpoint = '/taxonomy/' + taxonomyId + '/terms/?'+ qs.stringify({ hideempty: 0, include: selectedTerms.map((term) => { return term.id; }), fetch_preview_image_items: showTermThumbnail ? 0 : 3 }) + '&order=asc';
        tainacan.get(endpoint, { cancelToken: itemsRequestSource.token })
            .then(response => {

                for (let term of response.data)
                    terms.push(prepareItem(term, (!showTermThumbnail && term.preview_image_items) ? term.preview_image_items : []));
                
                isLoading = false;
                setAttributes({
                    content: <div></div>,
                    terms: terms,
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

        let existingItemIndex = terms.findIndex((existingItem) => existingItem.key == itemId);
        if (existingItemIndex >= 0)
            terms.splice(existingItemIndex, 1);

        let existingSelectedItemIndex = selectedTerms.findIndex((existingSelectedItem) => existingSelectedItem.id == itemId);
        if (existingSelectedItemIndex >= 0)
            selectedTerms.splice(existingSelectedItemIndex, 1);

        setAttributes({
            selectedTerms: selectedTerms,
            terms: terms,
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
                        src={ `${tainacan_blocks.base_url}/assets/images/carousel-terms-list.png` } />
            </div>
        : (
        <div { ...blockProps }>

            { terms.length ?
                <BlockControls>
                    {
                        TainacanBlocksCompatToolbar({
                            label: __('Add more terms', 'tainacan'),
                            icon: <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        viewBox="0 -2 24 24"
                                        height="24px"
                                        width="24px">
                                    <path d="M21.43,14.64,19.32,17a2.57,2.57,0,0,1-2,1H12.05a6,6,0,0,0-6-6H6V10.64A2.59,2.59,0,0,1,8.59,8H17.3a2.57,2.57,0,0,1,2,1l2.11,2.38A2.59,2.59,0,0,1,21.43,14.64ZM4,4A2,2,0,0,0,2,6v7.63a5.74,5.74,0,0,1,2-1.2V6H16V4ZM7,15.05v6.06l3.06-3.06ZM5,21.11V15.05L1.94,18.11Z"/>
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
                                    id="term-carousel-view-modes"
                                    label={ __('Term layout', 'tainacan')}>
                                <div className="term-carousel-view-modes">
                                    <button
                                            onClick={ () => {
                                                    showTermThumbnail = false;
                                                    setAttributes({ showTermThumbnail: showTermThumbnail });
                                                    setContent();
                                                }
                                            }
                                            className={'term-carousel-view-mode-grid' + (showTermThumbnail ? '' : ' is-active')}>
                                        <div>
                                            <div />
                                        <div />
                                        <div />
                                        </div>
                                        <label>{ __('Items\'s grid', 'tainacan') }</label>
                                    </button>
                                    <button
                                            onClick={ () => {
                                                    showTermThumbnail = true;
                                                    setAttributes({ showTermThumbnail: showTermThumbnail });
                                                    setContent();
                                                }
                                            }
                                            className={'term-carousel-view-mode-thumbnail' + (showTermThumbnail ? ' is-active' : '')}>
                                        <div />
                                        <label>{ __('Thumbnail', 'tainacan') }</label>
                                    </button>
                                </div>
                            </BaseControl>
                            <RangeControl
                                    label={ __('Maximum terms per slide on a wide screen', 'tainacan') }
                                    help={ (showTermThumbnail && maxTermsPerScreen <= 3) ? __('Warning: with such a small number of terms per slide, the image size is greater and might be pixelated.', 'tainacan') : null }
                                    value={ maxTermsPerScreen ? maxTermsPerScreen : 6 }
                                    onChange={ ( aMaxTermsPerScreen ) => {
                                        maxTermsPerScreen = aMaxTermsPerScreen;
                                        setAttributes( { maxTermsPerScreen: aMaxTermsPerScreen } );
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
                                    label={ __('Space between each term', 'tainacan') }
                                    value={ !isNaN(spaceBetweenTerms) ? spaceBetweenTerms : 32 }
                                    onChange={ ( aSpaceBetweenTerms ) => {
                                        spaceBetweenTerms = aSpaceBetweenTerms;
                                        setAttributes( { spaceBetweenTerms: aSpaceBetweenTerms } );
                                    }}
                                    min={ 0 }
                                    max={ 98 }
                                />
                            <ToggleControl
                                    label={__('Hide name', 'tainacan')}
                                    help={ !hideName ? __('Toggle to hide term\'s name', 'tainacan') : __('Do not hide term\'s name', 'tainacan')}
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
                                    help={ !autoPlay ? __('Toggle to automatically slide to next term', 'tainacan') : __('Do not automatically slide to next term', 'tainacan')}
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
                                            autoPlaySpeed = aAutoPlaySpeed;
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
                                        spaceAroundCarousel = aSpaceAroundCarousel;
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
                            <TermsModal
                                replaceTermId={ false } // The Terms modal adds `term-id-` string to terms ids. Here we dont' need it
                                existingTaxonomyId={ taxonomyId }
                                selectedTermsObject={ selectedTerms }
                                onSelectTaxonomy={ (selectedTaxonomyId) => {
                                    taxonomyId = selectedTaxonomyId;
                                    setAttributes({ taxonomyId: taxonomyId });
                                }}
                                onApplySelection={ (aSelectionOfTerms) =>{
                                    selectedTerms = aSelectionOfTerms;

                                    setAttributes({
                                        selectedTerms: selectedTerms,
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

            { !terms.length && !isLoading ? (
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
                            <path d="M21.43,14.64,19.32,17a2.57,2.57,0,0,1-2,1H12.05a6,6,0,0,0-6-6H6V10.64A2.59,2.59,0,0,1,8.59,8H17.3a2.57,2.57,0,0,1,2,1l2.11,2.38A2.59,2.59,0,0,1,21.43,14.64ZM4,4A2,2,0,0,0,2,6v7.63a5.74,5.74,0,0,1,2-1.2V6H16V4ZM7,15.05v6.06l3.06-3.06ZM5,21.11V15.05L1.94,18.11Z"/>
                        </svg>
                        {__('List terms on a Carousel, showing their thumbnails or a preview of items.', 'tainacan')}
                    </p>
                    <Button
                        isPrimary
                        type="button"
                        onClick={ () => openCarouselModal() }>
                        {__('Select Terms', 'tainacan')}
                    </Button>
                </Placeholder>
                ) : null
            }

            { isLoading ?
                <div class="spinner-container">
                    <Spinner />
                </div> :
                <div>
                    { isSelected && terms.length ?
                        <div class="preview-warning">{__('Warning: this is just a demonstration. To see the carousel in action, either preview or publish your post.', 'tainacan')}</div>
                        : null
                    }
                    {  terms.length ? (
                        <div
                                className={'terms-list-edit-container ' + (arrowsPosition ? 'has-arrows-' + arrowsPosition : '') + (largeArrows ? ' has-large-arrows' : '') }
                                style={{
                                    '--spaceBetweenTerms': !isNaN(spaceBetweenTerms) ? (spaceBetweenTerms + 'px') : '32px',
                                    '--spaceAroundCarousel': !isNaN(spaceAroundCarousel) ? (spaceAroundCarousel + 'px') : '50px'
                                }}>
                            <div className={'swiper'}>
                                <ul className={'swiper-wrapper terms-list-edit'}>
                                    { terms }
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
