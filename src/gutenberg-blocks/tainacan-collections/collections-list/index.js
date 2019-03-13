const { registerBlockType } = wp.blocks;

const { __ } = wp.i18n;

const { TextControl, RangeControl, IconButton, Button, Modal, CheckboxControl, RadioControl, Spinner, ToggleControl, Placeholder, Toolbar } = wp.components;

const { InspectorControls, BlockControls } = wp.editor;

import tainacan from '../../api-client/axios.js';
import qs from 'qs';
import axios from 'axios';

registerBlockType('tainacan/collections-list', {
    title: __('Tainacan Collections List', 'tainacan'),
    icon:
        <svg width="24" height="24" viewBox="0 -5 12 16">
            <path
                d="M10,8.8v1.3H1.2C0.6,10.1,0,9.5,0,8.8V2.5h1.3v6.3H10z M6.9,0H3.8C3.1,0,2.5,0.6,2.5,1.3l0,5c0,0.7,0.6,1.2,1.3,1.2h7.5
                c0.7,0,1.3-0.6,1.3-1.2V2.5c0-0.7-0.6-1.2-1.3-1.2H8.2L6.9,0z"/>       
        </svg>,
    category: 'tainacan-blocks',
    keywords: [ __( 'Tainacan', 'tainacan' ), __( 'collections', 'tainacan' ), __( 'repository', 'tainacan' ) ],
    attributes: {
        selectedCollectionsObject: {
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
                name: {
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
        collectionsPerPage: {
            type: Number,
            default: 24
        },
        query: {
            type: Object,
            default: {}
        },
        isLoadingCollections: {
            type: Boolean,
            default: false
        },
        collections: {
            type: Array,
            default: []
        },
        selectedCollectionsHTML: {
            type: Array,
            default: []
        },
        temporarySelectedCollections: {
            type: Array,
            default: []
        },
        searchCollectionName: {
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
        modalCollections: {
            type: Array,
            default: []
        },
        totalModalCollections: {
            type: Number,
            default: 0
        },
        collectionsPage: {
            type: Number,
            default: 1
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
            selectedCollectionsObject, 
            selectedCollectionsHTML, 
            temporarySelectedCollections,
            collections, 
            content, 
            searchCollectionName, 
            isLoadingCollections, 
            showImage,
            showName,
            layout,
            isModalOpen,
            modalCollections,
            totalModalCollections,
            collectionsPerPage,
            collectionsPage,
            collectionsRequestSource,
            gridMargin
        } = attributes;

        function prepareCollection(collection) {
            return (
                <li 
                    key={ collection.id }
                    className="collection-list-item"
                    style={{ marginBottom: layout == 'grid' ?  gridMargin + 'px' : ''}}>
                    <IconButton
                        onClick={ () => removeCollectionOfId(collection.id) }
                        icon="no-alt"
                        label={__('Remove', 'tainacan')}/>         
                    <a 
                        id={ isNaN(collection.id) ? collection.id : 'collection-id-' + collection.id }
                        href={ collection.url } 
                        target="_blank"
                        className={ (!showName ? 'collection-without-name' : '') + ' ' + (!showImage ? 'collection-without-image' : '') }>
                        <img
                            src={ collection.thumbnail && collection.thumbnail[0] && collection.thumbnail[0].src ? collection.thumbnail[0].src : `${tainacan_plugin.base_url}/admin/images/placeholder_square.png`}
                            alt={ collection.thumbnail && collection.thumbnail[0] ? collection.thumbnail[0].alt : collection.name }/>
                        <span>{ collection.name ? collection.name : '' }</span>
                    </a>
                </li>
            );
        }

        function renderCollectionsModalContent() {
            return (
                <Modal
                        className="wp-block-tainacan-modal"
                        title={__('Select the desired collections from your repository', 'tainacan')}
                        onRequestClose={ () => setAttributes( { isModalOpen: false } ) }
                        contentLabel={__('Select collections', 'tainacan')}>

                    <div>
                        <div className="modal-search-area">
                            <TextControl 
                                    label={__('Search for a collection', 'tainacan')}
                                    value={ searchCollectionName }
                                    onInput={(value) => {
                                        setAttributes({ 
                                            searchCollectionName: value.target.value
                                        });
                                    }}
                                    onChange={(value) => fetchCollections(value)}/>
                        </div>
                        {(
                        searchCollectionName != '' ? ( 

                            collections.length > 0 ?
                            (
                                <div>
                                    <ul className="modal-checkbox-list">
                                    {
                                        collections.map((collection) =>
                                        <li 
                                            key={ collection.id }
                                            className="modal-checkbox-list-item">
                                            { collection.thumbnail && showImage ?
                                                <img
                                                    aria-hidden
                                                    src={ collection.thumbnail && collection.thumbnail[0] && collection.thumbnail[0].src ? collection.thumbnail[0].src : `${tainacan_plugin.base_url}/admin/images/placeholder_square.png`}
                                                    alt={ collection.thumbnail && collection.thumbnail[0] ? collection.thumbnail[0].alt : collection.name }/>
                                                : null
                                            }
                                            <CheckboxControl
                                                label={ collection.name }
                                                checked={ isTemporaryCollectionSelected(collection.id) }
                                                onChange={ ( isChecked ) => { toggleSelectTemporaryCollection(collection, isChecked) } }
                                            />
                                        </li>
                                        )
                                    }                                                
                                    </ul>
                                    { isLoadingCollections ? <Spinner/> : null }
                                </div>
                            )
                            : isLoadingCollections ? <Spinner/> :
                            <div className="modal-loadmore-section">
                                <p>{ __('Sorry, no collections found.', 'tainacan') }</p>
                            </div>
                        ) : 
                        modalCollections.length > 0 ? 
                        (   
                            <div>
                                <ul className="modal-checkbox-list">
                                {
                                    modalCollections.map((collection) =>
                                        <li 
                                            key={ collection.id }
                                            className="modal-checkbox-list-item">
                                            { collection.thumbnail && showImage ?
                                                <img
                                                    aria-hidden
                                                    src={ collection.thumbnail && collection.thumbnail[0] && collection.thumbnail[0].src ? collection.thumbnail[0].src : `${tainacan_plugin.base_url}/admin/images/placeholder_square.png`}
                                                    alt={ collection.thumbnail && collection.thumbnail[0] ? collection.thumbnail[0].alt : collection.name }/>
                                                : null
                                            }
                                            <CheckboxControl
                                                label={ collection.name }
                                                checked={ isTemporaryCollectionSelected(collection.id) }
                                                onChange={ ( isChecked ) => { toggleSelectTemporaryCollection(collection, isChecked) } } />
                                        </li>
                                    )
                                } 
                                { isLoadingCollections ? <Spinner/> : null }                                               
                                </ul>
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
                        ) : isLoadingCollections ? <Spinner /> :
                        <div className="modal-loadmore-section">
                            <p>{ __('Sorry, no collections found.', 'tainacan') }</p>
                        </div>
                    )}
                    <div className="modal-footer-area">
                        <Button
                            isDefault
                            onClick={ () => cancelSelection() }>
                            {__('Cancel', 'tainacan')}
                        </Button>
                        <Button 
                            isPrimary
                            type="submit"
                            onClick={ () => applySelectedCollections() }>
                            {__('Finish', 'tainacan')}
                        </Button>
                    </div>
                </div>
            </Modal>
            );
        }

        function setContent(){

            selectedCollectionsHTML = [];

            for (let i = 0; i < selectedCollectionsObject.length; i++)
                selectedCollectionsHTML.push(prepareCollection(selectedCollectionsObject[i]));

            setAttributes({
                content: (
                    <ul 
                        style={{ gridTemplateColumns: layout == 'grid' ? 'repeat(auto-fill, ' +  (gridMargin + (showName ? 220 : 185)) + 'px)' : 'inherit' }}
                        className={'collections-list  collections-layout-' + layout + (!showName ? ' collections-list-without-margin' : '')}>
                        { selectedCollectionsHTML }
                    </ul>
                ),
                selectedCollectionsHTML: selectedCollectionsHTML
            });
        }

        function fetchCollections(name) {
            if (collectionsRequestSource != undefined)
                collectionsRequestSource.cancel('Previous collections search canceled.');

            collectionsRequestSource = axios.CancelToken.source();
            isLoadingCollections = true;

            setAttributes({
                collectionsRequestSource: collectionsRequestSource,
                isLoadingCollections: isLoadingCollections
            });

            let endpoint = '/collections/?perpage=' + collectionsPerPage;

            if (name != undefined && name != '')
                endpoint += '&search=' + name;

            tainacan.get(endpoint, { cancelToken: collectionsRequestSource.token })
                .then(response => {

                    collections = response.data.map((collection) => ({ 
                        name: collection.name, 
                        id: collection.id,
                        url: collection.url,
                        thumbnail: [{
                            src: collection.thumbnail['tainacan-medium'] != undefined ? collection.thumbnail['tainacan-medium'][0] : collection.thumbnail['medium'][0],
                            alt: collection.name
                        }]
                    }));

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

            if (collectionsPage <= 1)
                modalCollections = [];

            let endpoint = '/collections/?perpage=' + collectionsPerPage + '&paged=' + collectionsPage;

            isLoadingCollections = true;
            collectionsPage++;

            setAttributes({ 
                isLoadingCollections: isLoadingCollections, 
                modalCollections: modalCollections,
                collectionsPage: collectionsPage
            });
            
            tainacan.get(endpoint)
                .then(response => {

                    for (let collection of response.data) {
                        modalCollections.push({ 
                            name: collection.name, 
                            id: collection.id,
                            url: collection.url,
                            thumbnail: [{
                                src: collection.thumbnail['tainacan-medium'] != undefined ? collection.thumbnail['tainacan-medium'][0] : collection.thumbnail['medium'][0],
                                alt: collection.name
                            }]
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

        function cancelSelection() {
            isModalOpen = false;
            collectionsPage = 1;
            modalCollections = [];
            
            setAttributes({ 
                isModalOpen: isModalOpen,
                collectionsPage: collectionsPage,
                modalCollections: false
            });
        }

        function openCollectionsModal() {
            temporarySelectedCollections = JSON.parse(JSON.stringify(selectedCollectionsObject));

            collectionsPage = 1;
            fetchModalCollections();
            
            setAttributes( { 
                isModalOpen: true, 
                collections: [], 
                temporarySelectedCollections: temporarySelectedCollections
            } );
        }

        function isTemporaryCollectionSelected(collectionId) {
            return temporarySelectedCollections.findIndex(collection => (collection.id == collectionId) || (collection.id == 'collection-id-' + collectionId)) >= 0;
        }

        function toggleSelectTemporaryCollection(collection, isChecked) {
            if (isChecked)
                selectTemporaryCollection(collection);
            else
                removeTemporaryCollectionOfId(collection.id);
            
            setAttributes({ temporarySelectedCollections: temporarySelectedCollections });
            setContent();
        }

        function selectCollection(selectedCollectionId) {

            collectionId = selectedCollectionId;

            setAttributes({
                collectionId: collectionId
            });
            fetchCollection();
            fetchModalCollections();
            setContent();
            
        }

        function selectTemporaryCollection(collection) {
            let existingCollectionIndex = temporarySelectedCollections.findIndex((existingCollection) => (existingCollection.id == 'collection-id-' + collection.id) || (existingCollection.id == collection.id));
   
            if (existingCollectionIndex < 0) {
                let collectionId = isNaN(collection.id) ? collection.id : 'collection-id-' + collection.id;
                temporarySelectedCollections.push({
                    id: collectionId,
                    name: collection.name,
                    url: collection.url,
                    thumbnail: collection.thumbnail
                });
            }
        }

        function removeTemporaryCollectionOfId(collectionId) {

            let existingCollectionIndex = temporarySelectedCollections.findIndex((existingCollection) => ((existingCollection.id == 'collection-id-' + collectionId) || (existingCollection.id == collectionId)));

            if (existingCollectionIndex >= 0)
                temporarySelectedCollections.splice(existingCollectionIndex, 1);
        }

        function applySelectedCollections() {
            selectedCollectionsObject = JSON.parse(JSON.stringify(temporarySelectedCollections));
            isModalOpen = false;

            setAttributes({ 
                selectedCollectionsObject: selectedCollectionsObject, 
                isModalOpen: isModalOpen
            });

            setContent();
        }

        function removeCollectionOfId(collectionId) {

            let existingCollectionIndex = selectedCollectionsObject.findIndex((existingCollection) => ((existingCollection.id == 'collection-id-' + collectionId) || (existingCollection.id == collectionId)));

            if (existingCollectionIndex >= 0)
                selectedCollectionsObject.splice(existingCollectionIndex, 1);

            setContent();
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
                                    help={ showImage ? __('Toggle to show collection\'s image', 'tainacan') : __('Do not show collection\'s image', 'tainacan')}
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
                                        help={ showName ? __('Toggle to show collection\'s name', 'tainacan') : __('Do not show collection\'s name', 'tainacan')}
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
                                            label={__('Margin between collections', 'tainacan')}
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
                             renderCollectionsModalContent()                
                        ) }
                        
                        <div className="block-control">
                            <Button
                                isPrimary
                                type="submit"
                                onClick={ () => openCollectionsModal() }>
                                {__('Select collections', 'tainacan')}
                            </Button>   
                        </div>
                        <hr/>
                    </div>
                    ) : null
                }

                { !selectedCollectionsHTML.length ? (
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
                    className={'collections-list-edit collections-layout-' + layout + (!showName ? ' collections-list-without-margin' : '')}>
                    { selectedCollectionsHTML }
                </ul>
                
            </div>
        );
    },
    save({ attributes, className }){
        const { content } = attributes;
        return <div className={className}>{ content }</div>
    }
});