const { __ } = wp.i18n;

const { ResizableBox, FocalPointPicker, SelectControl, RangeControl, Spinner, Button, ToggleControl, Placeholder, ColorPalette, BaseControl, PanelBody } = wp.components;

const { InspectorControls, BlockControls, useBlockProps, store } = (tainacan_blocks.wp_version < '5.2' ? wp.editor : wp.blockEditor );

const { useSelect } = wp.data;

import map from 'lodash/map'; // Do not user import { map,pick } from 'lodash'; -> These causes conflicts with underscore due to lodash global variable
import pick from 'lodash/pick';
import DynamicItemsModal from '../carousel-items-list/dynamic-and-carousel-items-modal.js';
import tainacan from '../../js/axios.js';
import axios from 'axios';
import qs from 'qs';
import { ThumbnailHelperFunctions } from '../../../admin/js/utilities.js';
import TainacanBlocksCompatToolbar from '../../js/compatibility/tainacan-blocks-compat-toolbar.js';
import TainacanBlocksCompatColorPicker from '../../js/compatibility/tainacan-blocks-compat-colorpicker.js';

export default function({ attributes, setAttributes, className, isSelected, clientId }){
    let {
        items, 
        content, 
        collection,
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
        orderBy,
        orderByMetaKey,
        searchString,
        selectedItems,
        isLoading,
        loadStrategy,
        showSearchBar,
        showCollectionHeader,
        showCollectionLabel,
        isLoadingCollection,
        collectionBackgroundColor,
        collectionTextColor,
        mosaicHeight,
        mosaicGridColumns,
        mosaicGridRows,
        mosaicItemFocalPoint,
        sampleBackgroundImage,
        mosaicDensity,
        maxColumnsCount,
        imageSize
    } = attributes;

    // Gets blocks props from hook
    const blockProps = tainacan_blocks.wp_version < '5.6' ? { className: className } : useBlockProps();

    // Obtains block's client id to render it on save function
    setAttributes({ blockId: clientId });
    const thumbHelper = ThumbnailHelperFunctions();
    
    // Sets some defaults that were not working
    if (maxColumnsCount === undefined) {
        maxColumnsCount = 5;
        setAttributes({ maxColumnsCount: maxColumnsCount });
    }
    if (maxItemsNumber === undefined) {
        maxItemsNumber = 12;
        setAttributes({ maxItemsNumber: maxItemsNumber });
    }
    if (loadStrategy === undefined) {
        loadStrategy = 'search';
        setAttributes({ loadStrategy: loadStrategy });
    }
    if (mosaicGridRows === undefined) {
        mosaicGridRows = 3;
        setAttributes({ mosaicGridRows: mosaicGridRows });
    }
    if (imageSize === undefined) {
        imageSize = 'tainacan-medium';
        setAttributes({ imageSize: imageSize });
    }

    const layoutControls = [
        {
            icon: 'grid-view',
            title: __( 'Grid View', 'tainacan' ),
            onClick: () => updateLayout('grid'),
            isActive: layout === 'grid',
        },
        {
            icon: 'list-view',
            title: __( 'List View', 'tainacan' ),
            onClick: () => updateLayout('list'),
            isActive: layout === 'list',
        },
        {
            icon: 'layout',
            title: __( 'Mosaic View', 'tainacan' ),
            onClick: () => updateLayout('mosaic'),
            isActive: layout === 'mosaic',
        }
    ];

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
    function prepareItem(item) {
        return (
            <li 
                key={ item.id }
                className="item-list-item"
                style={ {
                    backgroundImage: layout == 'mosaic' ? `url(${ thumbHelper.getSrc(item['thumbnail'], 'medium_large', item['document_mimetype']) })` : 'none',
                    backgroundPosition: layout == 'mosaic' ? `${ (mosaicItemFocalPoint && mosaicItemFocalPoint.x ? mosaicItemFocalPoint.x : 0.5) * 100 }% ${ (mosaicItemFocalPoint && mosaicItemFocalPoint.y ? mosaicItemFocalPoint.y : 0.5) * 100 }%` : 'none'
                }}
            >
                { loadStrategy == 'selection' ?
                    ( tainacan_blocks.wp_version < '5.4' ?
                        <IconButton
                            onClick={ () => removeItemOfId(item.id) }
                            icon="no-alt"
                            label={__('Remove', 'tainacan')}/>
                        :
                        <Button
                            onClick={ () => removeItemOfId(item.id) }
                            icon="no-alt"
                            label={__('Remove', 'tainacan')}/>
                    )
                    :null
                }
                <a 
                    id={ isNaN(item.id) ? item.id : 'item-id-' + item.id }
                    href={ item.url } 
                    onClick={ (event) => event.preventDefault() }
                    className={ (!showName ? 'item-without-title' : '') + ' ' + (!showImage ? 'item-without-image' : '') }>
                    <img
                        src={ thumbHelper.getSrc(item['thumbnail'], imageSize, item['document_mimetype']) }
                        srcSet={ thumbHelper.getSrcSet(item['thumbnail'], imageSize, item['document_mimetype']) }
                        alt={ item.thumbnail_alt ? item.thumbnail_alt : (item && item.title ? item.title : __( 'Thumbnail', 'tainacan' )) }/>
                    { item.title ?
                        <span>{ item.title }</span>
                    : null }
                </a>
            </li>
        );
    }

    function prepareMosaicItem(mosaicGroup, mosaicGroupsLength) {
        
        return (
            <div 
                style={
                    { 
                        width: 'calc((100% / ' + mosaicGroupsLength + ') - ' + gridMargin + 'px)',
                        height: 'calc(((' + (mosaicGridRows - 1) + ' * ' + gridMargin + 'px) + ' + mosaicHeight + 'px))',
                        gridTemplateColumns: 'repeat(' + mosaicGridColumns + ', calc((100% / ' + mosaicGridColumns + ') - (' + ((mosaicGridColumns - 1)*Number(gridMargin)) + 'px/' + mosaicGridColumns + ')))',
                        margin: gridMargin + 'px',
                        gridGap: gridMargin + 'px',
                    }
                }
                className={ 'mosaic-container mosaic-container--' + mosaicGroup.length + '-' + mosaicGridRows + 'x' + mosaicGridColumns }>
                    { buildMosaic(mosaicGroup) }
                </div>
        )
    }

    function setContent() {
        isLoading = true;

        setAttributes({
            isLoading: isLoading
        });

        items = [];
        
        if (loadStrategy == 'parent') {

            if (layout !== 'mosaic') {
            
                for (let item of selectedItems)
                    items.push(prepareItem(item));

                setAttributes({
                    content: <div></div>,
                    items: items,
                    isLoading: false
                });

            } else {
                // Initializes some variables
                mosaicDensity = mosaicDensity ? Number(mosaicDensity) : 5;
                mosaicGridRows = mosaicGridRows ? Number(mosaicGridRows) : 3;
                mosaicGridColumns = mosaicGridColumns ? Number(mosaicGridColumnsRows) : 3;
                mosaicHeight = mosaicHeight ? Number(mosaicHeight) : 280;
                mosaicItemFocalPoint = mosaicItemFocalPoint ? mosaicItemFocalPoint : { x: 0.5, y: 0.5 };
                sampleBackgroundImage = response.data.items && response.data.items[0] && response.data.items[0] ? getItemThumbnail(response.data.items[0], 'tainacan-medium') : ''; 

                const mosaicGroups = mosaicPartition(response.data.items);
                for (let mosaicGroup of mosaicGroups)
                    items.push(prepareMosaicItem(mosaicGroup, mosaicGroups.length));

                setAttributes({
                    content: <div></div>,
                    items: items,
                    isLoading: false,
                    itemsRequestSource: itemsRequestSource,
                    mosaicDensity: mosaicDensity,
                    mosaicHeight: mosaicHeight,
                    mosaicGridRows: mosaicGridRows,
                    mosaicGridColumns: mosaicGridColumns,
                    mosaicItemFocalPoint: mosaicItemFocalPoint,
                    sampleBackgroundImage: sampleBackgroundImage
                });
            }

        } else if (loadStrategy == 'selection') {

            if (itemsRequestSource != undefined && typeof itemsRequestSource == 'function')
                itemsRequestSource.cancel('Previous items search canceled.');

            itemsRequestSource = axios.CancelToken.source();
            
            let endpoint = '/collection/' + collectionId + '/items?'+ qs.stringify({ postin: selectedItems, perpage: selectedItems.length }) + '&orderby=post__in&fetch_only=title,url,thumbnail';
            
            tainacan.get(endpoint, { cancelToken: itemsRequestSource.token })
                .then(response => {

                    if (layout !== 'mosaic') {

                        for (let item of response.data.items)
                            items.push(prepareItem(item));

                        setAttributes({
                            content: <div></div>,
                            items: items,
                            isLoading: false,
                            itemsRequestSource: itemsRequestSource
                        });

                    } else {
                        // Initializes some variables
                        mosaicDensity = mosaicDensity ? Number(mosaicDensity) : 5;
                        mosaicGridRows = mosaicGridRows ? Number(mosaicGridRows) : 3;
                        mosaicGridColumns = mosaicGridColumns ? Number(mosaicGridColumns) : 3;
                        mosaicHeight = mosaicHeight ? Number(mosaicHeight) : 280;
                        mosaicItemFocalPoint = mosaicItemFocalPoint ? mosaicItemFocalPoint : { x: 0.5, y: 0.5 };
                        sampleBackgroundImage = response.data.items && response.data.items[0] && response.data.items[0] ? getItemThumbnail(response.data.items[0], 'tainacan-medium') : ''; 

                        const mosaicGroups = mosaicPartition(response.data.items);
                        for (let mosaicGroup of mosaicGroups)
                            items.push(prepareMosaicItem(mosaicGroup, mosaicGroups.length));

                        setAttributes({
                            content: <div></div>,
                            items: items,
                            isLoading: false,
                            itemsRequestSource: itemsRequestSource,
                            mosaicDensity: mosaicDensity,
                            mosaicHeight: mosaicHeight,
                            mosaicGridRows: mosaicGridRows,
                            mosaicGridColumns: mosaicGridColumns,
                            mosaicItemFocalPoint: mosaicItemFocalPoint,
                            sampleBackgroundImage: sampleBackgroundImage
                        });
                    }
                        
                });
        } else {

            if (searchURL) {

                if (itemsRequestSource != undefined && typeof itemsRequestSource == 'function')
                    itemsRequestSource.cancel('Previous items search canceled.');

                itemsRequestSource = axios.CancelToken.source();
                
                let endpoint = '/collection' + searchURL.split('#')[1].split('/collections')[1];
                let query = endpoint.split('?')[1];
                let queryObject = qs.parse(query);

                // Set up max items to be shown
                if (maxItemsNumber != undefined && maxItemsNumber > 0)
                    queryObject.perpage = maxItemsNumber;
                else if (queryObject.perpage != undefined && queryObject.perpage > 0)
                    setAttributes({ maxItemsNumber: Number(queryObject.perpage) });
                else {
                    queryObject.perpage = 12;
                    setAttributes({ maxItemsNumber: 12 });
                }

                // Set up sorting order
                if (queryObject.order != '' && !showSearchBar)
                    setAttributes({ order: queryObject.order });
                else if (order != '')
                    queryObject.order = order;
                else {
                    queryObject.order = 'asc';
                    setAttributes({ order: 'asc' });
                }
                
                // Set up sorting orderby
                if (queryObject.orderby != '')
                    setAttributes({ orderBy: queryObject.orderby });
                else if (orderBy != 'date')
                    queryObject.orderby = orderBy;
                else {
                    queryObject.orderby = 'date';
                    setAttributes({ orderBy: 'date' });
                }

                // Set up sorting metakey (used by some orderby)
                if (queryObject.metakey != '')
                    setAttributes({ orderByMetaKey: queryObject.metakey });
                else if (orderByMetaKey != '')
                    queryObject.metakey = orderByMetaKey;
                else {
                    queryObject.metakey = '';
                    setAttributes({ orderByMetaKey: '' });
                }

                // Set up search string
                if (searchString != undefined)
                    queryObject.search = searchString;
                else if (queryObject.search != undefined)
                    setAttributes({ searchString: queryObject.search });
                else {
                    delete queryObject.search;
                    setAttributes({ searchString: undefined });
                }

                // Remove unecessary queries
                delete queryObject.admin_view_mode;
                delete queryObject.fetch_only_meta;
                
                endpoint = endpoint.split('?')[0] + '?' + qs.stringify(queryObject) + '&fetch_only=title,url,thumbnail';
                
                tainacan.get(endpoint, { cancelToken: itemsRequestSource.token })
                    .then(response => {
                        
                        if (layout !== 'mosaic') {
                            
                            for (let item of response.data.items)
                                items.push(prepareItem(item));

                            setAttributes({
                                content: <div></div>,
                                items: items,
                                isLoading: false,
                                itemsRequestSource: itemsRequestSource
                            });

                        } else {
                            // Initializes some variables
                            mosaicDensity = mosaicDensity ? Number(mosaicDensity) : 5;
                            mosaicGridRows = mosaicGridRows ? Number(mosaicGridRows) : 3;
                            mosaicGridColumns = mosaicGridColumns ? Number(mosaicGridColumns) : 3;
                            mosaicHeight = mosaicHeight ? Number(mosaicHeight) : 280;
                            mosaicItemFocalPoint = mosaicItemFocalPoint ? mosaicItemFocalPoint : { x: 0.5, y: 0.5 };
                            sampleBackgroundImage = response.data.items && response.data.items[0] && response.data.items[0] ? getItemThumbnail(response.data.items[0], 'tainacan-medium') : ''; 

                            const mosaicGroups = mosaicPartition(response.data.items);
                            for (let mosaicGroup of mosaicGroups)
                                items.push(prepareMosaicItem(mosaicGroup, mosaicGroups.length));

                            setAttributes({
                                content: <div></div>,
                                items: items,
                                isLoading: false,
                                itemsRequestSource: itemsRequestSource,
                                mosaicDensity: mosaicDensity,
                                mosaicHeight: mosaicHeight,
                                mosaicGridRows: mosaicGridRows,
                                mosaicGridColumns: mosaicGridColumns,
                                mosaicItemFocalPoint: mosaicItemFocalPoint,
                                sampleBackgroundImage: sampleBackgroundImage
                            });
                        }
                    });
            }
        }
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

    function getItemThumbnail(item, size) {
        return (
            item.thumbnail && item.thumbnail[size][0] && item.thumbnail[size][0] 
            ?
            item.thumbnail[size][0] 
                :
            (item.thumbnail && item.thumbnail['thumbnail'][0] && item.thumbnail['thumbnail'][0]
                ?    
            item.thumbnail['thumbnail'][0] 
                : 
            `${tainacan_blocks.base_url}/assets/images/placeholder_square.png`)
        )
    }

    function openDynamicItemsModal(aLoadStrategy) {
        loadStrategy = aLoadStrategy;
        isModalOpen = true;
        setAttributes( { 
            isModalOpen: isModalOpen,
            loadStrategy: loadStrategy
        } );
    }

    function removeItemOfId(itemId) {
        
        let existingItemIndex = -1;

        let existingSelectedItemIndex = selectedItems.findIndex((existingSelectedItem) => existingSelectedItem == itemId);
        if (existingSelectedItemIndex >= 0)
            selectedItems.splice(existingSelectedItemIndex, 1);

        if (layout == 'mosaic') {
            existingItemIndex = items.findIndex((existingItem) => existingItem.key == itemId);

            setAttributes({ 
                selectedItems: selectedItems,
                content: <div></div> 
            });
            // In the case of the mosaic layout, we need to re-render as the items array is organized in groups.
            setContent();
        } else {
            existingItemIndex = items.findIndex((existingItem) => existingItem.key == itemId);

            if (existingItemIndex >= 0)
                items.splice(existingItemIndex, 1);
            
            setAttributes({ 
                selectedItems: selectedItems,
                items: items,
                content: <div></div> 
            });
        }
    }

    function updateLayout(newLayout) {
        layout = newLayout;

        if ((layout == 'grid' || layout == 'mosaic') && showImage == false)
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

    function updateMosaicItemFocalPoint(focalPoint) {
        if (Math.abs(focalPoint.x - (mosaicItemFocalPoint && mosaicItemFocalPoint.x ? mosaicItemFocalPoint.x : 0.5)) > 0.025 || Math.abs(focalPoint.y - (mosaicItemFocalPoint && mosaicItemFocalPoint.y ? mosaicItemFocalPoint.y : 0.5)) > 0.025) {
            mosaicItemFocalPoint = focalPoint;
            setAttributes({ mosaicItemFocalPoint: focalPoint });
            setContent();
        }
    }

    function mosaicPartition(items) {
        const partition = _.groupBy(items, (item, i) => {
            if (i % 2 == 0)
                return Math.floor(i/mosaicDensity)
            else
                return Math.floor(i/(mosaicDensity + 1))
        });
        return _.values(partition);
    }

    function buildMosaic(mosaicGroup) {
        let mosaic = []
        for (let item of mosaicGroup) 
            mosaic.push(prepareItem(item))
        return mosaic;
    }

    // Executed only on the first load of page
    if(content && content.length && content[0].type)
        setContent();
    
    return content == 'preview' ? 
        <div className={className}>
            <img
                    width="100%"
                    src={ `${tainacan_blocks.base_url}/assets/images/dynamic-items-list.png` } />
        </div>
        : (
        <div { ...blockProps }>

            { items.length ?
                <BlockControls>
                    { TainacanBlocksCompatToolbar({ controls: layoutControls }) }
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
                                        id="backgroundcolorpicker"
                                        label={ __('Background color', 'tainacan') }>
                                        <TainacanBlocksCompatColorPicker
                                            value={ collectionBackgroundColor }
                                            onChange={ ( color ) => {
                                                collectionBackgroundColor = color;
                                                setAttributes({ collectionBackgroundColor: collectionBackgroundColor }) 
                                            }}
                                            disableAlpha
                                        />
                                    </BaseControl>

                                    <BaseControl
                                        id="colorpicker"
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
                    { loadStrategy == 'search' ? 
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
                    : null }
                    <PanelBody
                            title={__('Items', 'tainacan')}
                            initialOpen={ true }
                        >
                        { loadStrategy == 'search' ?
                            <div>
                                <RangeControl
                                    label={__('Maximum number of items', 'tainacan')}
                                    value={ maxItemsNumber ? maxItemsNumber : 12 }
                                    onChange={ ( aMaxItemsNumber ) => {
                                        maxItemsNumber = Number(aMaxItemsNumber);
                                        setAttributes( { maxItemsNumber: aMaxItemsNumber } ) 
                                        setContent();
                                    }}
                                    min={ 1 }
                                    max={ tainacan_blocks.api_max_items_per_page ? Number(tainacan_blocks.api_max_items_per_page) : 96 }
                                />
                            <hr></hr>
                        </div>
                         : null }
                        <div>
                            { layout == 'list' ? 
                                <div style={{ marginTop: '16px'}}>
                                    <RangeControl
                                        label={ __('Maximum number of columns on a wide screen', 'tainacan') }
                                        value={ maxColumnsCount ? maxColumnsCount : 5 }
                                        onChange={ ( aMaxColumnsCount ) => {
                                            maxColumnsCount = Number(aMaxColumnsCount);
                                            setAttributes( { maxColumnsCount: aMaxColumnsCount } );
                                            setContent(); 
                                        }}
                                        min={ 1 }
                                        max={ 7 }
                                    />
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
                                </div>
                            : null }
                            { layout == 'grid' || layout == 'mosaic' ?
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
                                            value={ gridMargin ? gridMargin : 0 }
                                            onChange={ ( margin ) => {
                                                gridMargin = Number(margin);
                                                setAttributes( { gridMargin: margin } );
                                                setContent();
                                            }}
                                            min={ 0 }
                                            max={ 48 }
                                        />
                                    </div>
                                </div>
                            : null }
                            { layout == 'grid' ?
                                <div style={{ marginTop: '16px'}}>
                                    <RangeControl
                                            label={ __('Maximum number of columns on a wide screen', 'tainacan') }
                                            value={ maxColumnsCount ? maxColumnsCount : 5 }
                                            onChange={ ( aMaxColumnsCount ) => {
                                                maxColumnsCount = Number(aMaxColumnsCount);
                                                setAttributes( { maxColumnsCount: aMaxColumnsCount } );
                                                setContent(); 
                                            }}
                                            min={ 1 }
                                            max={ 7 }
                                        />
                                </div>
                            : null }
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
                        </div>
                    </PanelBody>
                    { layout == 'mosaic' ?
                    <PanelBody
                        title={__('Mosaic', 'tainacan')}
                        initialOpen={ true }
                        >
                        <div>
                            <RangeControl
                                label={__('Container height (px)', 'tainacan')}
                                value={ mosaicHeight ? Number(mosaicHeight) : 280 }
                                onChange={ ( height ) => {
                                    mosaicHeight = Number(height);
                                    setAttributes( { mosaicHeight: height } );
                                    setContent();
                                }}
                                min={ 100 }
                                max={ 2000 }
                            />
                        </div>
                        <div>
                            <RangeControl
                                label={__('Group Grid Density', 'tainacan')}
                                value={ mosaicDensity ? Number(mosaicDensity) : 5 }
                                onChange={ ( value ) => {
                                    mosaicDensity = Number(value);
                                    setAttributes( { mosaicDensity: mosaicDensity } );
                                    setContent();
                                }}
                                min={ 1 }
                                max={ 5 }
                            />
                        </div>
                        <div>
                            <SelectControl
                                label={__('Group Grid Dimensions', 'tainacan')}
                                value={ mosaicGridRows + 'x' + mosaicGridColumns }
                                options={ [
                                    { label: '2 x 3', value: '2x3' },
                                    { label: '3 x 2', value: '3x2' },
                                    { label: '3 x 3', value: '3x3' },
                                    { label: '3 x 4', value: '3x4' },
                                    { label: '4 x 3', value: '4x3' },
                                    { label: '4 x 5', value: '4x5' },
                                    { label: '5 x 4', value: '5x4' },
                                    { label: '6 x 2', value: '6x2' }
                                ] }
                                onChange={ ( aGrid ) => { 
                                    mosaicGridRows = Number(aGrid.split('x')[0]);
                                    mosaicGridColumns = Number(aGrid.split('x')[1]);
                
                                    setAttributes({
                                        mosaicGridRows: mosaicGridRows,
                                        mosaicGridColumns: mosaicGridColumns
                                    }); 
                                    setContent();
                                }}/>
                        </div>
                        <div>
                            <FocalPointPicker 
                                label={ __('Item focal point for background image', 'tainacan') }
                                url={ sampleBackgroundImage }
                                dimensions={ {
                                    width: 400,
                                    height: 400
                                } }
                                value={ mosaicItemFocalPoint }
                                onChange={ ( focalPoint ) =>_.debounce( updateMosaicItemFocalPoint(focalPoint), 700) } 
                            />
                        </div>
                    </PanelBody>
                    : null}
                </InspectorControls>
            </div>

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
                                    items = [];
                                    selectedItems = [];
                                }
                                collectionId = selectedCollectionId;
                                setAttributes({ 
                                    collectionId: collectionId,
                                    items: items,
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
                                setContent();
                            }}
                            onApplySelectedItems={ (aSelectionOfItems) => {
                                selectedItems = selectedItems.concat(aSelectionOfItems); 
                                loadStrategy = 'selection';
                                setAttributes({
                                    selectedItems: selectedItems,
                                    loadStrategy: loadStrategy,
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
                    <Button
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
                    </Button>
                    <Button
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
                    </Button>
                </div>
            : null
            }

            { !items.length && !isLoading ? (
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
                        {__('Dynamically list items from a Tainacan items search', 'tainacan')}
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
            
            { isLoading ? 
                <div class="spinner-container">
                    <Spinner />
                </div> :
                <div>
                    { layout !== 'mosaic' ? (
                        <ul 
                            style={{
                                gridGap: layout == 'grid' ? ((showName ? gridMargin + 24 : gridMargin) + 'px') : 'inherit',
                                marginTop: (showSearchBar || showCollectionHeader) ? ((showName ? gridMargin + 24 : gridMargin) + 'px') : '0px',    
                                padding: (Number(gridMargin)/4) + 'px',
                            }}
                            className={'items-list-edit items-layout-' + layout + (!showName ? ' items-list-without-margin' : '') + (maxColumnsCount ? ' max-columns-count-' + maxColumnsCount : '') }>
                            { items }
                        </ul>
                    ) : 
                        <ResizableBox
                            size={ {
                                height: mosaicHeight ? Number(mosaicHeight) + (3 * gridMargin) : 280 + (3 * gridMargin),
                                width: '100%'
                            } }
                            minHeight="80"
                            maxHeight="2000"
                            minWidth="100%"
                            maxWidth="100%"
                            showHandle={ true }
                            enable={ {
                                top: false,
                                right: false,
                                bottom: true,
                                left: false,
                                topRight: false,
                                bottomRight: true,
                                bottomLeft: true,
                                topLeft: false,
                            } }
                            onResizeStop={ ( event, direction, elt, delta ) => {
                                mosaicHeight = delta.height ? parseInt(delta.height) + parseInt(mosaicHeight) : 280;
                                setAttributes({ mosaicHeight: parseInt(mosaicHeight) });
                                setContent();
                            } }
                        >
                            <ul 
                                style={{
                                    marginTop: (showSearchBar || showCollectionHeader) ? ((showName ? gridMargin + 24 : gridMargin) + 'px') : '0px',    
                                    padding: (Number(gridMargin)/4) + 'px',
                                    minHeight: mosaicHeight + 'px'
                                }}
                                className={'items-list-edit items-layout-' + layout + (!showName ? ' items-list-without-margin' : '')}>
                                { items }
                            </ul>
                        </ResizableBox>
                    }
                </div>
            }
        </div>
    );
};