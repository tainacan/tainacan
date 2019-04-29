const { registerBlockType } = wp.blocks;

const { __ } = wp.i18n;

const { RangeControl, IconButton, Button, ToggleControl, Placeholder, Toolbar } = wp.components;

const { InspectorControls, BlockControls } = wp.editor;

import DynamicItemsModal from './dynamic-items-modal.js';
import tainacan from '../../api-client/axios.js';
import axios from 'axios';

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
        }
    },
    supports: {
        align: ['full', 'wide'],
        html: false,
    },
    edit({ attributes, setAttributes, className, isSelected }){
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
            itemsRequestSource
        } = attributes;

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
                            src={ item.thumbnail && item.thumbnail[0] && item.thumbnail[0].src ? item.thumbnail[0].src : `${tainacan_plugin.base_url}/admin/images/placeholder_square.png`}
                            alt={ item.thumbnail && item.thumbnail[0] ? item.thumbnail[0].alt : item.title }/>
                        <span>{ item.title ? item.title : '' }</span>
                    </a>
                </li>
            );
        }

        function setContent(){

            items = [];

            if (itemsRequestSource != undefined)
                itemsRequestSource.cancel('Previous items search canceled.');

            let anItemsRequestSource = axios.CancelToken.source();

            let endpoint = '/collection' + searchURL.split('#')[1].split('/collections')[1];

            tainacan.get(endpoint, { cancelToken: anItemsRequestSource.token })
                .then(response => {

                    for (let item of response.data.items)
                        items.push(prepareItem(item));

                    setAttributes({
                        content: (
                            <ul 
                                style={{ gridTemplateColumns: layout == 'grid' ? 'repeat(auto-fill, ' +  (gridMargin + (showName ? 220 : 185)) + 'px)' : 'inherit' }}
                                className={'items-list  items-layout-' + layout + (!showName ? ' items-list-without-margin' : '')}>
                                { items }
                            </ul>
                        ),
                        items: items
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
                        { isModalOpen ? 
                            <DynamicItemsModal
                                existingCollectionId={ collectionId } 
                                existingsearchURL={ searchURL } 
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
                        
                        <div className="block-control">
                            <Button
                                isPrimary
                                type="submit"
                                onClick={ () => openDynamicItemsModal() }>
                                {__('Select items search', 'tainacan')}
                            </Button>   
                        </div>
                        <hr/>
                    </div>
                    ) : null
                }

                { !items.length ? (
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
                    { items }
                </ul>
                
            </div>
        );
    },
    save({ attributes, className }){
        const { content } = attributes;
        return <div className={className}>{ content }</div>
    }
});