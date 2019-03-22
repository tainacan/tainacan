const { registerBlockType } = wp.blocks;

const { __ } = wp.i18n;

const { TextControl, RangeControl, IconButton, Button, Modal, CheckboxControl, RadioControl, Spinner, ToggleControl, Placeholder, Toolbar } = wp.components;

const { InspectorControls, BlockControls } = wp.editor;

import tainacan from '../../api-client/axios.js';
import qs from 'qs';
import axios from 'axios';

registerBlockType('tainacan/items-list', {
    title: __('Tainacan Items List', 'tainacan'),
    icon:
        <svg width="24" height="24" viewBox="0 -2 12 16">
            <path
                d="M8.8,1.2H1.2V10H0V1.2C0,0.6,0.6,0,1.2,0h7.5V1.2z M3.8,2.5c-0.7,0-1.2,0.6-1.2,1.3v8.8c0,0.7,0.6,1.2,1.2,1.2h6.9
                c0.7,0,1.2-0.6,1.2-1.2V6.3L8.1,2.5H3.8z M7.5,3.4L11,6.9H7.5V3.4z"/>       
        </svg>,
    category: 'tainacan-blocks',
    keywords: [ __( 'Tainacan', 'tainacan' ), __( 'items', 'tainacan' ), __( 'collection', 'tainacan' ) ],
    attributes: {
        selectedItemsObject: {
            type: 'array',
            source: 'query',
            selector: 'a',
            query: {
                id: {
                    type: 'string',
                    source: 'attribute',
                    attribute: 'id'
                },
                url: {
                    type: 'string',
                    source: 'attribute',
                    attribute: 'href'
                },
                title: {
                    type: 'string',
                    source: 'text'
                },
                thumbnail: {
                    source: 'query',
                    selector: 'img',
                    query: {
                        src: {
                            source: 'attribute',
                            attribute: 'src'
                        },
                        alt: {
                            source: 'attribute',
                            attribute: 'alt'
                        },
                    }
                }, 
            },
            default: []
        },
        content: {
            type: 'array',
            source: 'children',
            selector: 'div'
        },
        itemsPerPage: {
            type: Number,
            default: 24
        },
        query: {
            type: Object,
            default: {}
        },
        collectionId: {
            type: String,
            default: undefined
        },
        temporaryCollectionId: {
            type: String,
            default: ''
        },
        isLoadingCollections: {
            type: Boolean,
            default: false
        },
        isLoadingItems: {
            type: Boolean,
            default: false
        },
        collections: {
            type: Array,
            default: []
        },
        items: {
            type: Array,
            default: []
        },
        selectedItemsHTML: {
            type: Array,
            default: []
        },
        temporarySelectedItems: {
            type: Array,
            default: []
        },
        searchItemName: {
            type: String,
            default: ''
        },
        collectionName: {
            type: String,
            default: ''
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
        modalItems: {
            type: Array,
            default: []
        },
        totalModalItems: {
            type: Number,
            default: 0
        },
        modalCollections: {
            type: Array,
            default: []
        },
        totalModalCollections: {
            type: Number,
            default: 0
        },
        collectionPage: {
            type: Number,
            default: 1
        },
        itemsPage: {
            type: Number,
            default: 1
        },
        searchCollectionName: {
            type: String,
            default: ''
        },
        itemsRequestSource: {
            type: Object,
            default: undefined
        },
        collectionsRequestSource: {
            type: Object,
            default: undefined
        },
        gridMargin: {
            type: Number,
            default: 0
        }
    },
    supports: {
        align: ['full', 'wide'],
        html: false,
    },
    edit({ attributes, setAttributes, className, isSelected }){
        let {
            selectedItemsObject, 
            selectedItemsHTML, 
            temporarySelectedItems,
            items, 
            content, 
            searchItemName, 
            collectionId,  
            collectionName, 
            temporaryCollectionId, 
            isLoadingItems, 
            isLoadingCollections, 
            collections,
            modalCollections,
            totalModalCollections, 
            searchCollectionName,
            collectionPage, 
            showImage,
            showName,
            layout,
            isModalOpen,
            modalItems,
            totalModalItems,
            itemsPerPage,
            itemsPage,
            itemsRequestSource,
            collectionsRequestSource,
            gridMargin
        } = attributes;

        function prepareItem(item) {
            return (
                <li 
                    key={ item.id }
                    className="item-list-item"
                    style={{ marginBottom: layout == 'grid' ?  gridMargin + 'px' : ''}}>
                    <IconButton
                        onClick={ () => removeItemOfId(item.id) }
                        icon="no-alt"
                        label={__('Remove', 'tainacan')}/>         
                    <a 
                        id={ isNaN(item.id) ? item.id : 'item-id-' + item.id }
                        href={ item.url } 
                        target="_blank"
                        className={ (!showName ? 'item-without-title' : '') + ' ' + (!showImage ? 'item-without-image' : '') }>
                        <img
                            src={ item.thumbnail && item.thumbnail[0] && item.thumbnail[0].src ? item.thumbnail[0].src : `${tainacan_plugin.base_url}/admin/images/placeholder_square.png`}
                            alt={ item.thumbnail && item.thumbnail[0] ? item.thumbnail[0].alt : item.title }/>
                        <span>{ item.title ? item.title : '' }</span>
                    </a>
                </li>
            );
        }

        function renderCollectionModalContent() {
            return (
                <Modal
                    className="wp-block-tainacan-modal"
                    title={__('Select a collection to fetch items from', 'tainacan')}
                    onRequestClose={ () => setAttributes( { isModalOpen: false } ) }
                    contentLabel={__('Select items', 'tainacan')}>
                    <div>
                        <div className="modal-search-area">
                            <TextControl 
                                    label={__('Search for a collection', 'tainacan')}
                                    value={ searchCollectionName }
                                    onChange={(value) => {
                                        setAttributes({ 
                                            searchCollectionName: value
                                        });
                                        fetchCollections(value);
                                    }}/>
                        </div>
                        {(
                        searchCollectionName != '' ? (
                            collections.length > 0 ?
                            (
                                <div>
                                    <div className="modal-radio-list">
                                        {
                                        <RadioControl
                                            selected={ temporaryCollectionId }
                                            options={
                                                collections.map((collection) => {
                                                    return { label: collection.name, value: '' + collection.id }
                                                })
                                            }
                                            onChange={ ( aCollectionId ) => { 
                                                temporaryCollectionId = aCollectionId;
                                                setAttributes({ temporaryCollectionId: temporaryCollectionId });
                                            } } />
                                        }                                      
                                    </div>
                                    { isLoadingCollections ? <Spinner/> : null }
                                </div>
                            ) :
                            isLoadingCollections ? (
                                <Spinner />
                            ) :
                            <div className="modal-loadmore-section">
                                <p>{ __('Sorry, no collection found.', 'tainacan') }</p>
                            </div> 
                        ):
                        modalCollections.length > 0 ? 
                        (   
                            <div>
                                <div className="modal-radio-list">
                                    {
                                    <RadioControl
                                        selected={ temporaryCollectionId }
                                        options={
                                            modalCollections.map((collection) => {
                                                return { label: collection.name, value: '' + collection.id }
                                            })
                                        }
                                        onChange={ ( aCollectionId ) => { 
                                            temporaryCollectionId = aCollectionId;
                                            setAttributes({ temporaryCollectionId: temporaryCollectionId });
                                        } } />
                                    }   
                                    { isLoadingItems ? <Spinner/> : null }                                  
                                </div>
                                <div className="modal-loadmore-section">
                                    <p>{ __('Showing', 'tainacan') + " " + modalCollections.length + " " + __('of', 'tainacan') + " " + totalModalCollections + " " + __('collections', 'tainacan') + "."}</p>
                                    {
                                        modalCollections.length < totalModalCollections ? (
                                        <Button 
                                            isDefault
                                            isSmall
                                            onClick={ () => fetchModalCollections() }>
                                            {__('Load more', 'tainacan')}
                                        </Button>
                                        ) : null
                                    }
                                </div>
                            </div>
                        ) : isLoadingCollections ? <Spinner/> :
                        <div className="modal-loadmore-section">
                            <p>{ __('Sorry, no collection found.', 'tainacan') }</p>
                        </div>
                    )}
                    <div className="modal-footer-area">
                        <Button 
                            isDefault
                            onClick={ () => {
                                isModalOpen = false;
                                setAttributes({ isModalOpen: isModalOpen })
                            }}>
                            {__('Cancel', 'tainacan')}
                        </Button>
                        <Button 
                            isPrimary
                            type="submit"
                            disabled={ temporaryCollectionId == undefined || temporaryCollectionId == null || temporaryCollectionId == ''}
                            onClick={ () => selectCollection(temporaryCollectionId) }>
                            {__('Select items', 'tainacan')}
                        </Button>
                    </div>
                </div>
            </Modal>
            );
        }

        function renderItemsModalContent() {
            return (
                <Modal
                    className="wp-block-tainacan-modal"
                    title={__('Select the desired items from collection ' + collectionName, 'tainacan')}
                    onRequestClose={ () => setAttributes( { isModalOpen: false } ) }
                    contentLabel={__('Select items', 'tainacan')}>
                    <div>
                        <div className="modal-search-area">
                            <TextControl 
                                label={__('Search for an item', 'tainacan')}
                                value={ searchItemName }
                                onInput={(value) => {
                                    setAttributes({ 
                                        searchItemName: value.target.value
                                    });
                                }}
                                onChange={(value) => fetchItems(value)}/>
                        </div>
                        {(
                        searchItemName != '' ? ( 

                            items.length > 0 ?
                            (
                                <div>
                                    <ul className="modal-checkbox-list">
                                    {
                                        items.map((item) =>
                                        <li 
                                            key={ item.id }
                                            className="modal-checkbox-list-item">
                                            { item.thumbnail && showImage ?
                                                <img
                                                    aria-hidden
                                                    src={ item.thumbnail && item.thumbnail[0] && item.thumbnail[0].src ? item.thumbnail[0].src : `${tainacan_plugin.base_url}/admin/images/placeholder_square.png`}
                                                    alt={ item.thumbnail && item.thumbnail[0] ? item.thumbnail[0].alt : item.title }/>
                                                : null
                                            }
                                            <CheckboxControl
                                                label={ item.title }
                                                checked={ isTemporaryItemSelected(item.id) }
                                                onChange={ ( isChecked ) => { toggleSelectTemporaryItem(item, isChecked) } }
                                            />
                                        </li>
                                        )
                                    }                                                
                                    </ul>
                                    { isLoadingItems ? <Spinner/> : null }
                                </div>
                            )
                            : isLoadingItems ? <Spinner/> :
                            <div className="modal-loadmore-section">
                                <p>{ __('Sorry, no items found.', 'tainacan') }</p>
                            </div>
                        ) : 
                        modalItems.length > 0 ? 
                        (   
                            <div>
                                <ul className="modal-checkbox-list">
                                {
                                    modalItems.map((item) =>
                                        <li 
                                            key={ item.id }
                                            className="modal-checkbox-list-item">
                                            { item.thumbnail && showImage ?
                                                <img
                                                    aria-hidden
                                                    src={ item.thumbnail && item.thumbnail[0] && item.thumbnail[0].src ? item.thumbnail[0].src : `${tainacan_plugin.base_url}/admin/images/placeholder_square.png`}
                                                    alt={ item.thumbnail && item.thumbnail[0] ? item.thumbnail[0].alt : item.title }/>
                                                : null
                                            }
                                            <CheckboxControl
                                                label={ item.title }
                                                checked={ isTemporaryItemSelected(item.id) }
                                                onChange={ ( isChecked ) => { toggleSelectTemporaryItem(item, isChecked) } } />
                                        </li>
                                    )
                                } 
                                { isLoadingItems ? <Spinner/> : null }                                               
                                </ul>
                                <div className="modal-loadmore-section">
                                    <p>{ __('Showing', 'tainacan') + " " + modalItems.length + " " + __('of', 'tainacan') + " " + totalModalItems + " " + __('items', 'tainacan') + "."}</p>
                                    {
                                        modalItems.length < totalModalItems ? (
                                        <Button 
                                            isDefault
                                            isSmall
                                            onClick={ () => fetchModalItems() }>
                                            {__('Load more', 'tainacan')}
                                        </Button>
                                        ) : null
                                    }
                                </div>
                            </div>
                        ) : isLoadingItems ? <Spinner /> :
                        <div className="modal-loadmore-section">
                            <p>{ __('Sorry, no items found.', 'tainacan') }</p>
                        </div>
                    )}
                    <div className="modal-footer-area">
                        <Button
                            isDefault
                            onClick={ () => resetCollections() }>
                            {__('Switch collection', 'tainacan')}
                        </Button>
                        <Button 
                            isPrimary
                            type="submit"
                            onClick={ () => applySelectedItems() }>
                            {__('Finish', 'tainacan')}
                        </Button>
                    </div>
                </div>
            </Modal>
            );
        }

        function setContent(){

            selectedItemsHTML = [];

            for (let i = 0; i < selectedItemsObject.length; i++)
                selectedItemsHTML.push(prepareItem(selectedItemsObject[i]));

            setAttributes({
                content: (
                    <ul 
                        style={{ gridTemplateColumns: layout == 'grid' ? 'repeat(auto-fill, ' +  (gridMargin + (showName ? 220 : 185)) + 'px)' : 'inherit' }}
                        className={'items-list  items-layout-' + layout + (!showName ? ' items-list-without-margin' : '')}>
                        { selectedItemsHTML }
                    </ul>
                ),
                selectedItemsHTML: selectedItemsHTML
            });
        }

        function fetchCollections(name) {

            if (collectionsRequestSource != undefined)
                collectionsRequestSource.cancel('Previous collections search canceled.');

            collectionsRequestSource = axios.CancelToken.source();

            setAttributes({
                collectionsRequestSource: collectionsRequestSource
            })

            isLoadingCollections = true;
            collections = [];
            items = []

            setAttributes({ 
                isLoadingCollections: isLoadingCollections, 
                collections: collections,
                items: items
            });

            let endpoint = '/collections/?perpage=' + itemsPerPage;
            if (name != undefined && name != '')
                endpoint += '&search=' + name;

            tainacan.get(endpoint, { cancelToken: collectionsRequestSource.token })
                .then(response => {
                    collections = response.data.map((collection) => ({ name: collection.name, id: collection.id + '' }));
                    isLoadingCollections = false; 

                    setAttributes({ 
                        isLoadingCollections: isLoadingCollections, 
                        collections: collections
                    });
                    
                    return collections;
                })
                .catch(error => {
                    console.log('Error trying to fetch collections: ' + error);
                });
        }

        function fetchModalCollections() {
            
            if (collectionPage <= 1)
                modalCollections = [];

            let endpoint = '/collections/?perpage=' + itemsPerPage + '&paged=' + collectionPage;

            collectionPage++;
            isLoadingCollections = true;

            setAttributes({ 
                isLoadingCollections: isLoadingCollections,
                collectionPage: collectionPage, 
                modalCollections: modalCollections
            });

            tainacan.get(endpoint)
                .then(response => {

                    for (let collection of response.data) {
                        modalCollections.push({ 
                            name: collection.name, 
                            id: collection.id
                        });
                    }
                    isLoadingCollections = false;
                    totalModalCollections = response.headers['x-wp-total']; 

                    setAttributes({ 
                        isLoadingCollections: isLoadingCollections, 
                        modalCollections: modalCollections,
                        totalModalCollections: totalModalCollections
                    });
                    
                    return modalCollections;
                })
                .catch(error => {
                    console.log('Error trying to fetch collections: ' + error);
                });
        }

        function fetchItems(title) {
            if (itemsRequestSource != undefined)
                itemsRequestSource.cancel('Previous items search canceled.');

            itemsRequestSource = axios.CancelToken.source();
            isLoadingItems = true;

            setAttributes({
                itemsRequestSource: itemsRequestSource,
                isLoadingItems: isLoadingItems
            });

            let endpoint = '/collection/'+ collectionId + '/items/?fetch_only=title,thumbnail&perpage=' + itemsPerPage;

            if (title != undefined && title != '')
                endpoint += '&search=' + title;

            tainacan.get(endpoint, { cancelToken: itemsRequestSource.token })
                .then(response => {

                    items = response.data.map((item) => ({ 
                        title: item.title, 
                        id: item.id,
                        url: item.url,
                        thumbnail: [{
                            src: item.thumbnail['tainacan-medium'] != undefined ? item.thumbnail['tainacan-medium'][0] : item.thumbnail['medium'][0],
                            alt: item.title
                        }]
                    }));

                    setAttributes({ 
                        isLoadingItems: isLoadingItems, 
                        items: items
                    });
                    
                    return items;
                })
                .catch(error => {
                    console.log('Error trying to fetch items: ' + error);
                });
        }


        function fetchModalItems() {

            if (itemsPage <= 1)
                modalItems = [];

            let endpoint = '/collection/'+ collectionId + '/items/?fetch_only=title,thumbnail&perpage=' + itemsPerPage + '&paged=' + itemsPage;

            isLoadingItems = true;
            itemsPage++;

            setAttributes({ 
                isLoadingItems: isLoadingItems, 
                modalItems: modalItems,
                itemsPage: itemsPage
            });
            
            tainacan.get(endpoint)
                .then(response => {
                    for (let item of response.data) {
                        modalItems.push({ 
                            title: item.title, 
                            id: item.id,
                            url: item.url,
                            thumbnail: [{
                                src: item.thumbnail['tainacan-medium'] != undefined ? item.thumbnail['tainacan-medium'][0] : item.thumbnail['medium'][0],
                                alt: item.title
                            }]
                        });
                    }
                    isLoadingItems = false;
                    totalModalItems = response.headers['x-wp-total']; 

                    setAttributes({ 
                        isLoadingItems: isLoadingItems, 
                        modalItems: modalItems,
                        totalModalItems: totalModalItems
                    });
                    
                    return modalItems;
                })
                .catch(error => {
                    console.log('Error trying to fetch items: ' + error);
                });
        }

        function resetCollections() {
            itemsPage = 1;
            collectionId = null; 
            collectionPage = 1;
            modalItems = [];
            
            setAttributes({ 
                itemsPage: itemsPage,
                collectionId: collectionId,
                collectionPage: collectionPage,
                modalItems: modalItems
            });
            fetchModalCollections(); 
        }

        function openItemsModal() {
            temporarySelectedItems = JSON.parse(JSON.stringify(selectedItemsObject));

            if (collectionId != null && collectionId != undefined) {
                fetchCollection();
                itemsPage = 1;
                fetchModalItems();
            } else {
                collectionPage = 1;
                fetchModalCollections()
            }
            setAttributes( { 
                isModalOpen: true, 
                items: [], 
                temporarySelectedItems: temporarySelectedItems
            } );
        }

        function isTemporaryItemSelected(itemId) {
            return temporarySelectedItems.findIndex(item => (item.id == itemId) || (item.id == 'item-id-' + itemId)) >= 0;
        }

        function toggleSelectTemporaryItem(item, isChecked) {
            if (isChecked)
                selectTemporaryItem(item);
            else
                removeTemporaryItemOfId(item.id);
            
            setAttributes({ temporarySelectedItems: temporarySelectedItems });
            setContent();
        }

        function selectCollection(selectedCollectionId) {

            collectionId = selectedCollectionId;

            setAttributes({
                collectionId: collectionId
            });
            fetchCollection();
            fetchModalItems();
            setContent();
            
        }

        function selectTemporaryItem(item) {
            let existingItemIndex = temporarySelectedItems.findIndex((existingItem) => (existingItem.id == 'item-id-' + item.id) || (existingItem.id == item.id));
   
            if (existingItemIndex < 0) {
                let itemId = isNaN(item.id) ? item.id : 'item-id-' + item.id;
                temporarySelectedItems.push({
                    id: itemId,
                    title: item.title,
                    url: item.url,
                    thumbnail: item.thumbnail
                });
            }
        }

        function removeTemporaryItemOfId(itemId) {

            let existingItemIndex = temporarySelectedItems.findIndex((existingItem) => ((existingItem.id == 'item-id-' + itemId) || (existingItem.id == itemId)));

            if (existingItemIndex >= 0)
                temporarySelectedItems.splice(existingItemIndex, 1);
        }

        function applySelectedItems() {
            selectedItemsObject = JSON.parse(JSON.stringify(temporarySelectedItems));
            isModalOpen = false;

            setAttributes({ 
                selectedItemsObject: selectedItemsObject, 
                isModalOpen: isModalOpen
            });

            setContent();
        }

        function removeItemOfId(itemId) {

            let existingItemIndex = selectedItemsObject.findIndex((existingItem) => ((existingItem.id == 'item-id-' + itemId) || (existingItem.id == itemId)));

            if (existingItemIndex >= 0)
                selectedItemsObject.splice(existingItemIndex, 1);

            setContent();
        }

        function fetchCollection() {
            tainacan.get('/collections/' + collectionId)
                .then((response) => {
                    collectionName = response.data.name;
                    setAttributes({ collectionName: collectionName });
                }).catch(error => {
                    console.log('Error trying to fetch collection: ' + error);
                });
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
                    </InspectorControls>
                </div>

                { isSelected ? 
                    (
                    <div>
                        { isModalOpen && (
                            collectionId != null && collectionId != undefined ? renderItemsModalContent() : renderCollectionModalContent()                 
                        ) }
                        
                        <div className="block-control">
                            <Button
                                isPrimary
                                type="submit"
                                onClick={ () => openItemsModal() }>
                                {__('Select items', 'tainacan')}
                            </Button>   
                        </div>
                        <hr/>
                    </div>
                    ) : null
                }

                { !selectedItemsHTML.length ? (
                    <Placeholder
                        icon={(
                            <img
                                width={148}
                                src={ `${tainacan_plugin.base_url}/admin/images/tainacan_logo_header.svg` }
                                alt="Tainacan Logo"/>
                        )}
                    />) : null
                }

                <ul 
                    style={{ gridTemplateColumns: layout == 'grid' ? 'repeat(auto-fill, ' +  (gridMargin + (showName ? 220 : 185)) + 'px)' : 'inherit' }}
                    className={'items-list-edit items-layout-' + layout + (!showName ? ' items-list-without-margin' : '')}>
                    { selectedItemsHTML }
                </ul>
                
            </div>
        );
    },
    save({ attributes, className }){
        const { content } = attributes;
        return <div className={className}>{ content }</div>
    }
});