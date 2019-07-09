const { registerBlockType } = wp.blocks;

const { __ } = wp.i18n;

const { RangeControl, Spinner, Button, ToggleControl, Tooltip, Placeholder, Toolbar, ColorPicker, ColorPalette, BaseControl, Panel, PanelBody, PanelRow } = wp.components;

const { InspectorControls, BlockControls } = wp.editor;

import DynamicItemsModal from './dynamic-items-modal.js';
import tainacan from '../../api-client/axios.js';
import axios from 'axios';
import qs from 'qs';

registerBlockType('tainacan/dynamic-items-list', {
    title: __('Tainacan Collection\'s items List', 'tainacan'),
    icon:
        <svg
                xmlns="http://www.w3.org/2000/svg"
                viewBox="0 0 24 24"
                height="24px"
                width="24px">
            <path
                fill="#298596"
                d="M14,2V4H7v7.24A5.33,5.33,0,0,0,5.5,11a4.07,4.07,0,0,0-.5,0V4A2,2,0,0,1,7,2Zm7,10v8a2,2,0,0,1-2,2H12l1-1-2.41-2.41A5.56,5.56,0,0,0,11,16.53a5.48,5.48,0,0,0-2-4.24V8a2,2,0,0,1,2-2h4Zm-2.52,0L14,7.5V12ZM11,21l-1,1L8.86,20.89,8,20H8l-.57-.57A3.42,3.42,0,0,1,5.5,20a3.5,3.5,0,0,1-.5-7,2.74,2.74,0,0,1,.5,0,3.41,3.41,0,0,1,1.5.34,3.5,3.5,0,0,1,2,3.16,3.42,3.42,0,0,1-.58,1.92L9,19H9l.85.85Zm-4-4.5A1.5,1.5,0,0,0,5.5,15a1.39,1.39,0,0,0-.5.09A1.5,1.5,0,0,0,5.5,18a1.48,1.48,0,0,0,1.42-1A1.5,1.5,0,0,0,7,16.53Z"/>
        </svg>,
    category: 'tainacan-blocks',
    keywords: [ __( 'items', 'tainacan' ), __( 'search', 'tainacan' ), __( 'collection', 'tainacan' ) ],
    attributes: {
        content: {
            type: 'array',
            source: 'children',
            selector: 'div'
        },
        collectionId: {
            type: String,
            default: undefined
        },
        items: {
            type: Array,
            default: []
        },
        showImage: {
            type: Boolean,
            default: true
        },
        showName: {
            type: Boolean,
            default: true
        },
        layout: {
            type: String,
            default: 'grid'
        },
        isModalOpen: {
            type: Boolean,
            default: false
        },
        gridMargin: {
            type: Number,
            default: 0
        },
        searchURL: {
            type: String,
            default: undefined
        },
        itemsRequestSource: {
            type: String,
            default: undefined
        },
        maxItemsNumber: {
            type: Number,
            value: undefined
        },
        isLoading: {
            type: Boolean,
            value: false
        },
        isLoadingCollection: {
            type: Boolean,
            value: false
        },
        showSearchBar: {
            type: Boolean,
            value: false
        },
        showCollectionHeader: {
            type: Boolean,
            value: false
        },
        showCollectionLabel: {
            type: Boolean,
            value: false
        },
        collection: {
            type: Object,
            value: undefined
        },
        searchString: {
            type: String,
            default: undefined
        },
        order: {
            type: String,
            default: undefined
        },
        blockId: {
            type: String,
            default: undefined
        },
        collectionBackgroundColor: {
            type: String,
            default: "#454647"
        },
        collectionTextColor: {
            type: String,
            default: "#ffffff"
        }
    },
    supports: {
        align: ['full', 'wide'],
        html: false,
    },
    edit({ attributes, setAttributes, className, isSelected, clientId }){
        let {
            items, 
            content, 
            collectionId,  
            showImage,
            showName,
            layout,
            isModalOpen,
            gridMargin,
            searchURL,
            itemsRequestSource,
            maxItemsNumber,
            order,
            searchString,
            isLoading,
            showSearchBar,
            showCollectionHeader,
            showCollectionLabel,
            isLoadingCollection,
            collection,
            collectionBackgroundColor,
            collectionTextColor
        } = attributes;

        // Obtains block's client id to render it on save function
        setAttributes({ blockId: clientId });
        
        function prepareItem(item) {
            return (
                <li 
                    key={ item.id }
                    className="item-list-item"
                    style={{ marginBottom: layout == 'grid' ? (showName ? gridMargin + 12 : gridMargin) + 'px' : ''}}>      
                    <a 
                        id={ isNaN(item.id) ? item.id : 'item-id-' + item.id }
                        href={ item.url } 
                        target="_blank"
                        className={ (!showName ? 'item-without-title' : '') + ' ' + (!showImage ? 'item-without-image' : '') }>
                        <img
                            src={ 
                                item.thumbnail && item.thumbnail['tainacan-medium'][0] && item.thumbnail['tainacan-medium'][0] 
                                    ?
                                item.thumbnail['tainacan-medium'][0] 
                                    :
                                (item.thumbnail && item.thumbnail['thumbnail'][0] && item.thumbnail['thumbnail'][0]
                                    ?    
                                item.thumbnail['thumbnail'][0] 
                                    : 
                                `${tainacan_plugin.base_url}/admin/images/placeholder_square.png`)
                            }
                            alt={ item.title ? item.title : __( 'Thumbnail', 'tainacan' ) }/>
                        <span>{ item.title ? item.title : '' }</span>
                    </a>
                </li>
            );
        }

        function setContent(){

            items = [];
            isLoading = true;
            
            if (itemsRequestSource != undefined && typeof itemsRequestSource == 'function')
                itemsRequestSource.cancel('Previous items search canceled.');

            itemsRequestSource = axios.CancelToken.source();
            
            setAttributes({
                isLoading: isLoading
            });

            let endpoint = '/collection' + searchURL.split('#')[1].split('/collections')[1];
            let query = endpoint.split('?')[1];
            let queryObject = qs.parse(query);

            // Set up max items to be shown
            if (maxItemsNumber != undefined && maxItemsNumber > 0)
                queryObject.perpage = maxItemsNumber;
            else if (queryObject.perpage != undefined && queryObject.perpage > 0)
                setAttributes({ maxItemsNumber: queryObject.perpage });
            else {
                queryObject.perpage = 12;
                setAttributes({ maxItemsNumber: 12 });
            }

            // Set up sorting order
            if (order != undefined)
                queryObject.order = order;
            else if (queryObject.order != undefined)
                setAttributes({ order: queryObject.order });
            else {
                queryObject.order = 'asc';
                setAttributes({ order: 'asc' });
            }

            // Set up sorting order
            if (searchString != undefined)
                queryObject.search = searchString;
            else if (queryObject.search != undefined)
                setAttributes({ searchString: queryObject.search });
            else {
                delete queryObject.search;
                setAttributes({ searchString: undefined });
            }

            // Remove unecessary queries
            delete queryObject.readmode;
            delete queryObject.iframemode;
            delete queryObject.admin_view_mode;
            delete queryObject.fetch_only_meta;
            
            endpoint = endpoint.split('?')[0] + '?' + qs.stringify(queryObject) + '&fetch_only=title,url,thumbnail';
            
            tainacan.get(endpoint, { cancelToken: itemsRequestSource.token })
                .then(response => {

                    for (let item of response.data.items)
                        items.push(prepareItem(item));

                    setAttributes({
                        content: <div></div>,
                        items: items,
                        isLoading: false,
                        itemsRequestSource: itemsRequestSource
                    });
                });
        }

        function fetchCollectionForHeader() {
            if (showCollectionHeader) {

                isLoadingCollection = true;             
                setAttributes({
                    isLoadingCollection: isLoadingCollection
                });

                tainacan.get('/collections/' + collectionId + '?fetch_only=name,thumbnail,header_image')
                    .then(response => {
                        collection = response.data;
                        isLoadingCollection = false;      

                        if (collection.tainacan_theme_collection_background_color)
                            collectionBackgroundColor = collection.tainacan_theme_collection_background_color;
                        else
                            collectionBackgroundColor = '#454647';

                        if (collection.tainacan_theme_collection_color)
                            collectionTextColor = collection.tainacan_theme_collection_color;
                        else
                            collectionTextColor = '#ffffff';

                        setAttributes({
                            content: <div></div>,
                            collection: collection,
                            isLoadingCollection: isLoadingCollection,
                            collectionBackgroundColor: collectionBackgroundColor,
                            collectionTextColor: collectionTextColor
                        });
                    });
            }
        }

        function openDynamicItemsModal() {
            isModalOpen = true;
            setAttributes( { 
                isModalOpen: isModalOpen
            } );
        }

        function updateLayout(newLayout) {
            layout = newLayout;

            if (layout == 'grid' && showImage == false)
                showImage = true;

            if (layout == 'list' && showName == false)
                showName = true;

            setAttributes({ 
                layout: layout, 
                showImage: showImage,
                showName: showName
            });
            setContent();
        }

        function applySearchString(event) {

            let value = event.target.value;

            if (searchString != value) {
                searchString = value;
                setAttributes({ searchString: searchString });
                setContent();
            }
        }

        // Executed only on the first load of page
        if(content && content.length && content[0].type)
            setContent();

        const layoutControls = [
            {
                icon: 'grid-view',
                title: __( 'Grid View' ),
                onClick: () => updateLayout('grid'),
                isActive: layout === 'grid',
            },
            {
                icon: 'list-view',
                title: __( 'List View' ),
                onClick: () => updateLayout('list'),
                isActive: layout === 'list',
            }
        ];

        return (
            <div className={className}>

                <div>
                    <BlockControls>
                        <Toolbar controls={ layoutControls } />
                    </BlockControls>
                </div>

                <div>
                    <InspectorControls>
                        
                        <PanelBody
                                title={__('Collection header', 'tainacan')}
                                initialOpen={ false }
                            >
                                <ToggleControl
                                    label={__('Display header', 'tainacan')}
                                    help={ !showCollectionHeader ? __('Toggle to show collection header', 'tainacan') : __('Do not show collection header', 'tainacan')}
                                    checked={ showCollectionHeader }
                                    onChange={ ( isChecked ) => {
                                            showCollectionHeader = isChecked;
                                            if (isChecked) fetchCollectionForHeader();
                                            setAttributes({ showCollectionHeader: showCollectionHeader });
                                        } 
                                    }
                                />
                                { showCollectionHeader ?
                                    <div style={{ margin: '6px' }}>

                                        <ToggleControl
                                            label={__('Display "Collection" label', 'tainacan')}
                                            help={ !showCollectionLabel ? __('Toggle to show "Collection" label above header', 'tainacan') : __('Do not show "Collection" label', 'tainacan')}
                                            checked={ showCollectionLabel }
                                            onChange={ ( isChecked ) => {
                                                    showCollectionLabel = isChecked;
                                                    setAttributes({ showCollectionLabel: showCollectionLabel });
                                                } 
                                            }
                                        />

                                        <BaseControl
                                            id="colorpicker"
                                            label={ __('Background color', 'tainacan')}>
                                            <ColorPicker
                                                color={ collectionBackgroundColor }
                                                onChangeComplete={ ( value ) => {
                                                    collectionBackgroundColor = value.hex;
                                                    setAttributes({ collectionBackgroundColor: collectionBackgroundColor }) 
                                                }}
                                                disableAlpha
                                                />
                                        </BaseControl>

                                        <BaseControl
                                            id="colorpallete"
                                            label={ __('Collection name color', 'tainacan')}>
                                            <ColorPalette 
                                                colors={ [{ name: __('Black', 'tainacan'), color: '#000000'}, { name: __('White', 'tainacan'), color: '#ffffff'} ] } 
                                                value={ collectionTextColor }
                                                onChange={ ( color ) => {
                                                    collectionTextColor = color;
                                                    setAttributes({ collectionTextColor: collectionTextColor }) 
                                                }} 
                                            />
                                        </BaseControl>
                                    </div>
                                : null
                                }
                        </PanelBody> 
                        <PanelBody
                                title={__('Search bar', 'tainacan')}
                                initialOpen={ true }
                            >
                            <ToggleControl
                                label={__('Display bar', 'tainacan')}
                                help={ showSearchBar ? __('Toggle to show search bar on block', 'tainacan') : __('Do not show search bar', 'tainacan')}
                                checked={ showSearchBar }
                                onChange={ ( isChecked ) => {
                                        showSearchBar = isChecked;
                                        setAttributes({ showSearchBar: showSearchBar });
                                    } 
                                }
                            />
                        </PanelBody>
                        <PanelBody
                                title={__('Items', 'tainacan')}
                                initialOpen={ true }
                            >
                            <div>
                                <RangeControl
                                    label={__('Maximum number of items', 'tainacan')}
                                    value={ maxItemsNumber }
                                    onChange={ ( aMaxItemsNumber ) => {
                                        maxItemsNumber = aMaxItemsNumber;
                                        setAttributes( { maxItemsNumber: aMaxItemsNumber } ) 
                                        setContent();
                                    }}
                                    min={ 1 }
                                    max={ 96 }
                                />
                            </div>
                            <hr></hr>
                            <div>
                                { layout == 'list' ? 
                                    <ToggleControl
                                        label={__('Image', 'tainacan')}
                                        help={ showImage ? __("Toggle to show item's image", 'tainacan') : __("Do not show item's image", 'tainacan')}
                                        checked={ showImage }
                                        onChange={ ( isChecked ) => {
                                                showImage = isChecked;
                                                setAttributes({ showImage: showImage });
                                                setContent();
                                            } 
                                        }
                                    /> 
                                : null }
                                { layout == 'grid' ?
                                    <div>
                                        <ToggleControl
                                            label={__("Item's title", 'tainacan')}
                                            help={ showName ? __("Toggle to show item's title", 'tainacan') : __("Do not show item's title", 'tainacan')}
                                            checked={ showName }
                                            onChange={ ( isChecked ) => {
                                                    showName = isChecked;
                                                    setAttributes({ showName: showName });
                                                    setContent();
                                                } 
                                            }
                                        />
                                        <div style={{ marginTop: '16px'}}>
                                            <RangeControl
                                                label={__('Margin between items in pixels', 'tainacan')}
                                                value={ gridMargin }
                                                onChange={ ( margin ) => {
                                                    gridMargin = margin;
                                                    setAttributes( { gridMargin: margin } ) 
                                                    setContent();
                                                }}
                                                min={ 0 }
                                                max={ 48 }
                                            />
                                        </div>
                                    </div>
                                : null }
                            </div>
                        </PanelBody>
                    </InspectorControls>
                </div>

                { isSelected ? 
                    (
                    <div>
                        { isModalOpen ? 
                            <DynamicItemsModal
                                existingCollectionId={ collectionId } 
                                existingSearchURL={ searchURL } 
                                onSelectCollection={ (selectedCollectionId) => {
                                    collectionId = selectedCollectionId;
                                    setAttributes({ collectionId: collectionId });
                                    fetchCollectionForHeader();
                                }}
                                onApplySearchURL={ (aSearchURL) =>{
                                    searchURL = aSearchURL
                                    setAttributes({
                                        searchURL: searchURL,
                                        isModalOpen: false
                                    });
                                    setContent();
                                }}
                                onCancelSelection={ () => setAttributes({ isModalOpen: false }) }/> 
                            : null
                        }
                        
                        { items.length ? (
                            <div className="block-control">
                                <p>
                                    <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            viewBox="0 0 24 24"
                                            height="24px"
                                            width="24px">
                                        <path d="M14,2V4H7v7.24A5.33,5.33,0,0,0,5.5,11a4.07,4.07,0,0,0-.5,0V4A2,2,0,0,1,7,2Zm7,10v8a2,2,0,0,1-2,2H12l1-1-2.41-2.41A5.56,5.56,0,0,0,11,16.53a5.48,5.48,0,0,0-2-4.24V8a2,2,0,0,1,2-2h4Zm-2.52,0L14,7.5V12ZM11,21l-1,1L8.86,20.89,8,20H8l-.57-.57A3.42,3.42,0,0,1,5.5,20a3.5,3.5,0,0,1-.5-7,2.74,2.74,0,0,1,.5,0,3.41,3.41,0,0,1,1.5.34,3.5,3.5,0,0,1,2,3.16,3.42,3.42,0,0,1-.58,1.92L9,19H9l.85.85Zm-4-4.5A1.5,1.5,0,0,0,5.5,15a1.39,1.39,0,0,0-.5.09A1.5,1.5,0,0,0,5.5,18a1.48,1.48,0,0,0,1.42-1A1.5,1.5,0,0,0,7,16.53Z"/>
                                    </svg>
                                    {__('Dynamically list items from a Tainacan items search', 'tainacan')}
                                </p>
                                <Button
                                    isPrimary
                                    type="submit"
                                    onClick={ () => openDynamicItemsModal() }>
                                    {__('Configure search', 'tainacan')}
                                </Button>    
                            </div>
                            ): null
                        }
                    </div>
                    ) : null
                }

                {
                    showCollectionHeader ?
                
                    <div> {
                        isLoadingCollection ? 
                            <div class="spinner-container">
                                <Spinner />
                            </div>
                            :
                            <a
                                    href={ collection.url ? collection.url : '' }
                                    target="_blank"
                                    class="dynamic-items-collection-header">
                                <div
                                        style={{
                                            backgroundColor: collectionBackgroundColor ? collectionBackgroundColor : '', 
                                            paddingRight: collection && collection.thumbnail && (collection.thumbnail['tainacan-medium'] || collection.thumbnail['medium']) ? '' : '20px',
                                            paddingTop: (!collection || !collection.thumbnail || (!collection.thumbnail['tainacan-medium'] && !collection.thumbnail['medium'])) ? '1rem' : '',
                                            width: collection && collection.header_image ? '' : '100%'
                                        }}
                                        className={ 
                                            'collection-name ' + 
                                            ((!collection || !collection.thumbnail || (!collection.thumbnail['tainacan-medium'] && !collection.thumbnail['medium'])) && (!collection || !collection.header_image) ? 'only-collection-name' : '') 
                                        }>
                                    <h3 style={{  color: collectionTextColor ? collectionTextColor : '' }}>
                                        { showCollectionLabel ? <span class="label">{ __('Collection', 'tainacan') }<br/></span> : null }
                                        { collection && collection.name ? collection.name : '' }
                                    </h3>
                                </div>
                                {
                                    collection && collection.thumbnail && (collection.thumbnail['tainacan-medium'] || collection.thumbnail['medium']) ? 
                                        <div   
                                            class="collection-thumbnail"
                                            style={{ 
                                                backgroundImage: 'url(' + (collection.thumbnail['tainacan-medium'] != undefined ? (collection.thumbnail['tainacan-medium'][0]) : (collection.thumbnail['medium'][0])) + ')',
                                            }}/>
                                    : null
                                }  
                                <div
                                        class="collection-header-image"
                                        style={{
                                            backgroundImage: collection.header_image ? 'url(' + collection.header_image + ')' : '',
                                            minHeight: collection && collection.header_image ? '' : '80px',
                                            display: !(collection && collection.thumbnail && (collection.thumbnail['tainacan-medium'] || collection.thumbnail['medium'])) ? collection && collection.header_image ? '' : 'none' : ''  
                                        }}/>
                            </a>  
                        }
                    </div>
                    : null
                }

                {
                    showSearchBar ?
                    <div class="dynamic-items-search-bar">
                        <Button
                            onClick={ () => { order = 'asc'; setAttributes({ order: order }); setContent(); }}
                            className={order == 'asc' ? 'sorting-button-selected' : ''}
                            label={__('Sort ascending', 'tainacan')}>
                            <span class="icon">
                                <i>
                                    <svg width="24" height="24" viewBox="-2 -4 20 20">
                                    <path d="M6.7,10.8l-3.3,3.3L0,10.8h2.5V0h1.7v10.8H6.7z M11.7,0.8H8.3v1.7h3.3V0.8z M14.2,5.8H8.3v1.7h5.8V5.8z M16.7,10.8H8.3v1.7	h8.3V10.8z"/>       
                                    </svg>
                                </i>
                            </span>
                        </Button>  
                        <Button
                            onClick={ () => { order = 'desc'; setAttributes({ order: order }); setContent(); }}
                            className={order == 'desc' ? 'sorting-button-selected' : ''}
                            label={__('Sort descending', 'tainacan')}>
                            <span class="icon">
                                <i>
                                    <svg width="24" height="24" viewBox="-2 -4 20 20">
                                    <path d="M6.7,3.3H4.2v10.8H2.5V3.3H0L3.3,0L6.7,3.3z M11.6,2.5H8.3v1.7h3.3V2.5z M14.1,7.5H8.3v1.7h5.8V7.5z M16.6,12.5H8.3v1.7 h8.3V12.5z"/>
                                    </svg>
                                </i>
                            </span>
                        </Button>  
                        <Button
                            onClick={ () => { setContent(); }}
                            label={__('Search', 'tainacan')}>
                            <span class="icon">
                                <i>
                                    <svg width="24" height="24" viewBox="-2 -4 20 20">
                                    <path class="st0" d="M0,5.8C0,5,0.2,4.2,0.5,3.5s0.7-1.3,1.2-1.8s1.1-0.9,1.8-1.2C4.2,0.1,5,0,5.8,0S7.3,0.1,8,0.5
                                        c0.7,0.3,1.3,0.7,1.8,1.2s0.9,1.1,1.2,1.8c0.5,1.2,0.5,2.5,0.2,3.7c0,0.2-0.1,0.4-0.2,0.6c0,0.1-0.2,0.6-0.2,0.6
                                        c0.6,0.6,1.3,1.3,1.9,1.9c0.7,0.7,1.3,1.3,2,2c0,0,0.3,0.2,0.3,0.3c0,0.3-0.1,0.7-0.3,1c-0.2,0.6-0.8,1-1.4,1.2
                                        c-0.1,0-0.6,0.2-0.6,0.1c0,0-4.2-4.2-4.2-4.2c0,0-0.8,0.3-0.8,0.4c-1.3,0.4-2.8,0.5-4.1-0.1c-0.7-0.3-1.3-0.7-1.8-1.2
                                        C1.2,9.3,0.8,8.7,0.5,8S0,6.6,0,5.8z M1.6,5.8c0,0.4,0.1,0.9,0.2,1.3C2.1,8.2,3,9.2,4.1,9.6c0.5,0.2,1,0.3,1.6,0.3
                                        c0.6,0,1.1-0.1,1.6-0.3C8.7,9,9.7,7.6,9.8,6c0.1-1.5-0.6-3.1-2-3.9c-0.9-0.5-2-0.6-3-0.4C4.6,1.8,4.4,1.9,4.1,2
                                        c-0.5,0.2-1,0.5-1.4,0.9C2,3.7,1.6,4.7,1.6,5.8z"/>       
                                    </svg>
                                </i>
                            </span>
                        </Button>
                        <input
                                value={ searchString }
                                onChange={ (value) =>  { _.debounce(applySearchString(value), 300); } }
                                type="text"/>
                        <Tooltip text={__('If necessary, pagination will be available on post or page.', 'tainacan')}>
                            <button
                                    class="previous-button"
                                    disabled
                                    label={__('Previous page', 'tainacan')}>
                                <span class="icon">
                                    <i>
                                        <svg
                                                width="30"
                                                height="30"
                                                viewBox="0 2 20 20">
                                            <path d="M15.41 7.41L14 6l-6 6 6 6 1.41-1.41L10.83 12z"/>
                                            <path
                                                    d="M0 0h24v24H0z"
                                                    fill="none"/>                        
                                        </svg>
                                    </i>
                                </span>
                            </button>
                        </Tooltip> 
                        <Tooltip text={__('If necessary, pagination will be available on post or page.', 'tainacan')}>
                            <button
                                    class="next-button"
                                    disabled
                                    label={__('Next page', 'tainacan')}>
                                <span class="icon">
                                    <i>
                                        <svg
                                                width="30"
                                                height="30"
                                                viewBox="0 2 20 20">
                                            <path d="M10 6L8.59 7.41 13.17 12l-4.58 4.59L10 18l6-6z"/>
                                            <path
                                                    d="M0 0h24v24H0z"
                                                    fill="none"/>                        
                                        </svg>
                                    </i>
                                </span>
                            </button>   
                        </Tooltip>
                    </div>
                : null
                }

                { !items.length && !isLoading ? (
                    <Placeholder
                        icon={(
                            <img
                                width={148}
                                src={ `${tainacan_plugin.base_url}/admin/images/tainacan_logo_header.svg` }
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
                            {__('Dynamically list items from a Tainacan items search', 'tainacan')}
                        </p>
                        <Button
                            isPrimary
                            type="submit"
                            onClick={ () => openDynamicItemsModal() }>
                            {__('Select items', 'tainacan')}
                        </Button>   
                    </Placeholder>
                    ) : null
                }
                
                { isLoading ? 
                    <div class="spinner-container">
                        <Spinner />
                    </div> :
                    <div>
                        <ul 
                            style={{ 
                                gridTemplateColumns: layout == 'grid' ? 'repeat(auto-fill, ' +  (gridMargin + (showName ? 220 : 185)) + 'px)' : 'inherit', 
                                marginTop: showSearchBar || showCollectionHeader ? '1.5rem' : '0px'
                            }}
                            className={'items-list-edit items-layout-' + layout + (!showName ? ' items-list-without-margin' : '')}>
                            { items }
                        </ul>
                    </div>
                }
            </div>
        );
    },
    save({ attributes, className }){
        const {
            content, 
            blockId,
            collectionId,  
            showImage,
            showName,
            layout,
            gridMargin,
            searchURL,
            maxItemsNumber,
            order,
            showSearchBar,
            showCollectionHeader,
            showCollectionLabel,
            collectionBackgroundColor,
            collectionTextColor
        } = attributes;
        
        return <div 
                    search-url={ searchURL }
                    className={ className }
                    collection-id={ collectionId }  
                    show-image={ '' + showImage }
                    show-name={ '' + showName }
                    show-search-bar={ '' + showSearchBar }
                    show-collection-header={ '' + showCollectionHeader }
                    show-collection-label={ '' + showCollectionLabel }
                    layout={ layout }
                    collection-background-color={ collectionBackgroundColor }
                    collection-text-color={ collectionTextColor }
                    grid-margin={ gridMargin }
                    max-items-number={ maxItemsNumber }
                    order={ order }
                    tainacan-api-root={ tainacan_plugin.root }
                    tainacan-base-url={ tainacan_plugin.base_url }
                    id={ 'wp-block-tainacan-dynamic-items-list_' + blockId }>
                        { content }
                </div>
    }
});