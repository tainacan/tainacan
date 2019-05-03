const { registerBlockType } = wp.blocks;

const { __ } = wp.i18n;

const { RangeControl, Spinner, Button, ToggleControl, Placeholder, Toolbar } = wp.components;

const { InspectorControls, BlockControls } = wp.editor;

import DynamicItemsModal from './dynamic-items-modal.js';
import tainacan from '../../api-client/axios.js';
import axios from 'axios';
import qs from 'qs';

registerBlockType('tainacan/dynamic-items-list', {
    title: __('Tainacan Dynamic Items List', 'tainacan'),
    icon:
        <svg width="24" height="24" viewBox="0 -2 12 16">
            <path
                d="M8.8,1.2H1.2V10H0V1.2C0,0.6,0.6,0,1.2,0h7.5V1.2z M3.8,2.5c-0.7,0-1.2,0.6-1.2,1.3v8.8c0,0.7,0.6,1.2,1.2,1.2h6.9
                c0.7,0,1.2-0.6,1.2-1.2V6.3L8.1,2.5H3.8z M7.5,3.4L11,6.9H7.5V3.4z"/>       
        </svg>,
    category: 'tainacan-blocks',
    keywords: [ __( 'Tainacan', 'tainacan' ), __( 'items', 'tainacan' ), __( 'collection', 'tainacan' ) ],
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
        showSearchBar: {
            type: Boolean,
            value: false
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
            showSearchBar
        } = attributes;

        // Obtains block's client id to render it on save function
        setAttributes({ blockId: clientId });

        function prepareItem(item) {
            return (
                <li 
                    key={ item.id }
                    className="item-list-item"
                    style={{ marginBottom: layout == 'grid' ?  gridMargin + 'px' : ''}}>      
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
                        <div style={{ marginTop: '24px' }}>
                            { layout == 'list' ? 
                                <ToggleControl
                                    label={__('Image', 'tainacan')}
                                    help={ showImage ? __('Toggle to show item\'s image', 'tainacan') : __('Do not show item\'s image', 'tainacan')}
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
                                        label={__('Name', 'tainacan')}
                                        help={ showName ? __('Toggle to show item\'s title', 'tainacan') : __('Do not show item\'s title', 'tainacan')}
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
                                            label={__('Margin between items', 'tainacan')}
                                            value={ gridMargin }
                                            onChange={ ( margin ) => {
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
                        <div style={{ marginTop: '20px'}}>
                            <RangeControl
                                label={__('Maximum number of items', 'tainacan')}
                                value={ maxItemsNumber }
                                onChange={ ( aMaxItemsNumber ) => {
                                    setAttributes( { maxItemsNumber: aMaxItemsNumber } ) 
                                    setContent();
                                }}
                                min={ 1 }
                                max={ 96 }
                            />
                        </div>
                        <div style={{ marginTop: '20px'}}>
                            <ToggleControl
                                label={__('Search bar', 'tainacan')}
                                help={ showSearchBar ? __('Toggle to show search bar on block', 'tainacan') : __('Do not show search bar', 'tainacan')}
                                checked={ showSearchBar }
                                onChange={ ( isChecked ) => {
                                        showSearchBar = isChecked;
                                        setAttributes({ showSearchBar: showSearchBar });
                                    } 
                                }
                            />
                        </div>
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
                                    <svg width="24" height="24" viewBox="0 -2 12 16">
                                        <path
                                            d="M8.8,1.2H1.2V10H0V1.2C0,0.6,0.6,0,1.2,0h7.5V1.2z M3.8,2.5c-0.7,0-1.2,0.6-1.2,1.3v8.8c0,0.7,0.6,1.2,1.2,1.2h6.9
                                            c0.7,0,1.2-0.6,1.2-1.2V6.3L8.1,2.5H3.8z M7.5,3.4L11,6.9H7.5V3.4z"/>       
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
                    showSearchBar ?
                    <div class="dynamic-items-search-bar">
                        <Button
                            onClick={ () => { order = 'asc'; setAttributes({ order: order }); setContent(); }}
                            className={order == 'asc' ? 'sorting-button-selected' : ''}
                            label={__('label_sort_ascending', 'tainacan')}>
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
                            label={__('label_sort_descending', 'tainacan')}>
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
                            label={__('search', 'tainacan')}>
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
                    </div>
                : null
                }

                { !items.length ? (
                    <Placeholder
                        icon={(
                            <img
                                width={148}
                                src={ `${tainacan_plugin.base_url}/admin/images/tainacan_logo_header.svg` }
                                alt="Tainacan Logo"/>
                        )}>
                        <p>
                            <svg width="24" height="24" viewBox="0 -2 12 16">
                                <path
                                    d="M8.8,1.2H1.2V10H0V1.2C0,0.6,0.6,0,1.2,0h7.5V1.2z M3.8,2.5c-0.7,0-1.2,0.6-1.2,1.3v8.8c0,0.7,0.6,1.2,1.2,1.2h6.9
                                    c0.7,0,1.2-0.6,1.2-1.2V6.3L8.1,2.5H3.8z M7.5,3.4L11,6.9H7.5V3.4z"/>       
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
                            style={{ gridTemplateColumns: layout == 'grid' ? 'repeat(auto-fill, ' +  (gridMargin + (showName ? 220 : 185)) + 'px)' : 'inherit' }}
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
            showSearchBar
        } = attributes;
        console.log(className)
        return <div 
                    search-url={ searchURL }
                    className={ className }
                    collection-id={ collectionId }  
                    show-image={ '' + showImage }
                    show-name={ '' + showName }
                    show-search-bar={ '' + showSearchBar }
                    layout={ layout }
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