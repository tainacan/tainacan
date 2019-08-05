const { registerBlockType } = wp.blocks;

const { __ } = wp.i18n;

const { RangeControl, TextControl, Toolbar, Spinner, SelectControl, Button, ToggleControl, Placeholder, ColorPicker, ColorPalette, BaseControl, PanelBody } = wp.components;

const { InspectorControls, BlockControls } = wp.editor;

import SearchBarModal from './search-bar-modal.js';
import tainacan from '../../api-client/axios.js';

registerBlockType('tainacan/search-bar', {
    title: __('Tainacan Search Bar', 'tainacan'),
    icon:
        <svg
                xmlns="http://www.w3.org/2000/svg"
                viewBox="-2 -2 20 20"
                height="24px"
                width="24px">
            <path 
                fill="#298596"
                d="M0,5.8C0,5,0.2,4.2,0.5,3.5s0.7-1.3,1.2-1.8s1.1-0.9,1.8-1.2C4.2,0.1,5,0,5.8,0S7.3,0.1,8,0.5
                c0.7,0.3,1.3,0.7,1.8,1.2s0.9,1.1,1.2,1.8c0.5,1.2,0.5,2.5,0.2,3.7c0,0.2-0.1,0.4-0.2,0.6c0,0.1-0.2,0.6-0.2,0.6
                c0.6,0.6,1.3,1.3,1.9,1.9c0.7,0.7,1.3,1.3,2,2c0,0,0.3,0.2,0.3,0.3c0,0.3-0.1,0.7-0.3,1c-0.2,0.6-0.8,1-1.4,1.2
                c-0.1,0-0.6,0.2-0.6,0.1c0,0-4.2-4.2-4.2-4.2c0,0-0.8,0.3-0.8,0.4c-1.3,0.4-2.8,0.5-4.1-0.1c-0.7-0.3-1.3-0.7-1.8-1.2
                C1.2,9.3,0.8,8.7,0.5,8S0,6.6,0,5.8z M1.6,5.8c0,0.4,0.1,0.9,0.2,1.3C2.1,8.2,3,9.2,4.1,9.6c0.5,0.2,1,0.3,1.6,0.3
                c0.6,0,1.1-0.1,1.6-0.3C8.7,9,9.7,7.6,9.8,6c0.1-1.5-0.6-3.1-2-3.9c-0.9-0.5-2-0.6-3-0.4C4.6,1.8,4.4,1.9,4.1,2
                c-0.5,0.2-1,0.5-1.4,0.9C2,3.7,1.6,4.7,1.6,5.8z"/>
        </svg>,
    category: 'tainacan-blocks',
    keywords: [ __( 'items', 'tainacan' ), __( 'search', 'tainacan' ), __( 'bar', 'tainacan' ) ],
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
        collectionSlug: {
            type: String,
            default: undefined
        },
        alignment: {
            type: String,
            default: 'center'
        },
        expandAlignment: {
            type: String,
            default: 'center'
        },
        isModalOpen: {
            type: Boolean,
            default: false
        },
        maxWidth: {
            type: Number,
            value: 80
        },
        placeholderText: {
            type: String,
            default: __('Search', 'taincan')
        },
        isLoadingCollection: {
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
        collectionHeaderHeight: {
            type: Number,
            value: 165
        },
        collection: {
            type: Object,
            value: undefined
        },
        collectionBackgroundColor: {
            type: String,
            default: "#454647"
        },
        collectionTextColor: {
            type: String,
            default: "#ffffff"
        },
        collectionTextSize: {
            type: Number,
            default: 2
        }
    },
    supports: {
        align: ['full', 'wide', 'left', 'center', 'right'],
        html: false
    },
    styles: [
        {
            name: 'default',
            label: __('Default', 'tainacan'),
            isDefault: true,  
        },{
            name: 'alternate',
            label: __('alternate', 'tainacan'),
        },{
            name: 'stylish',
            label: __('stylish', 'tainacan'),
        }
    ],
    edit({ attributes, setAttributes, className, isSelected, clientId }){
        let {
            content, 
            collectionId,  
            collectionSlug,
            collectionHeaderHeight,
            collectionTextSize,
            alignment,
            expandAlignment,
            placeholderText,
            isModalOpen,
            maxWidth,
            showCollectionHeader,
            showCollectionLabel,
            isLoadingCollection,
            collection,
            collectionBackgroundColor,
            collectionTextColor
        } = attributes;

        // Obtains block's client id to render it on save function
        setAttributes({ blockId: clientId });
        
        function setContent(){
 
            setAttributes({
                content: (
                    <div class="tainacan-search-container">
                        <form
                                style={{ maxWidth: maxWidth ? maxWidth + '%' : '80%' }}
                                className={ 
                                    (alignment == 'left' ? ' is-aligned-left' : '') + 
                                    (alignment == 'right' ? ' is-aligned-right' : '') +
                                    (expandAlignment == 'left' ? ' is-expanded-aligned-left' : '') +
                                    (expandAlignment == 'right' ? ' is-expanded-aligned-right' : '')
                                }
                                id="taincan-search-bar-block"
                                action={ tainacan_plugin.site_url + '/' + collectionSlug + '/#/' }
                                method='get'>
                            <input
                                id="taincan-search-bar-block_input"
                                label={ __('Search', 'taincan')}
                                name='search'
                                placeholder={ placeholderText }
                            />
                            <button 
                                    class="button"
                                    type="submit">
                                <span class="icon">
                                    <i>
                                        <svg width="24" height="24" viewBox="-2 -4 20 20">
                                        <path d="M0,5.8C0,5,0.2,4.2,0.5,3.5s0.7-1.3,1.2-1.8s1.1-0.9,1.8-1.2C4.2,0.1,5,0,5.8,0S7.3,0.1,8,0.5
                                            c0.7,0.3,1.3,0.7,1.8,1.2s0.9,1.1,1.2,1.8c0.5,1.2,0.5,2.5,0.2,3.7c0,0.2-0.1,0.4-0.2,0.6c0,0.1-0.2,0.6-0.2,0.6
                                            c0.6,0.6,1.3,1.3,1.9,1.9c0.7,0.7,1.3,1.3,2,2c0,0,0.3,0.2,0.3,0.3c0,0.3-0.1,0.7-0.3,1c-0.2,0.6-0.8,1-1.4,1.2
                                            c-0.1,0-0.6,0.2-0.6,0.1c0,0-4.2-4.2-4.2-4.2c0,0-0.8,0.3-0.8,0.4c-1.3,0.4-2.8,0.5-4.1-0.1c-0.7-0.3-1.3-0.7-1.8-1.2
                                            C1.2,9.3,0.8,8.7,0.5,8S0,6.6,0,5.8z M1.6,5.8c0,0.4,0.1,0.9,0.2,1.3C2.1,8.2,3,9.2,4.1,9.6c0.5,0.2,1,0.3,1.6,0.3
                                            c0.6,0,1.1-0.1,1.6-0.3C8.7,9,9.7,7.6,9.8,6c0.1-1.5-0.6-3.1-2-3.9c-0.9-0.5-2-0.6-3-0.4C4.6,1.8,4.4,1.9,4.1,2
                                            c-0.5,0.2-1,0.5-1.4,0.9C2,3.7,1.6,4.7,1.6,5.8z"/>       
                                        </svg>
                                    </i> 
                                </span>
                            </button>
                        </form>
                    </div>
                )            
            });  
            jQuery( document ).ready(function() {
                jQuery('.editor-writing-flow').on('submit','form', (function(e) {
                    e.preventDefault();
                    var val = jQuery('#taincan-search-bar-block_input').val();
                    if (val) {
                        window.location.href = e.target.action + '?search=' + val;
                    }
                    return;
                }));
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
                            collection: collection,
                            isLoadingCollection: isLoadingCollection,
                            collectionBackgroundColor: collectionBackgroundColor,
                            collectionTextColor: collectionTextColor
                        });
                        setContent();
                    });
            }
        }

        function openSearchBarModal() {
            isModalOpen = true;
            setAttributes( { 
                isModalOpen: isModalOpen
            } );
        }

        function updateAlignment(newAlignment) {
            alignment = newAlignment;
            setAttributes({ alignment: alignment });
            setContent();
        }

        function updateExpandAlignment(newAlignment) {
            expandAlignment = newAlignment;
            setAttributes({ expandAlignment: expandAlignment });
            setContent();
        }

        // Executed only on the first load of page
        if(content && content.length && content[0].type)
            setContent();

        const alignmentControls = [
            {
                icon: 'editor-alignleft',
                title: __( 'Left' ),
                onClick: () => updateAlignment('left'),
                isActive: alignment === 'left',
            },            
            {
                icon: 'editor-aligncenter',
                title: __( 'Center' ),
                onClick: () => updateAlignment('center'),
                isActive: alignment === 'center',
            },
            {
                icon: 'editor-alignright',
                title: __( 'Right' ),
                onClick: () => updateAlignment('right'),
                isActive: alignment === 'right',
            },
        ];
        const expandAlignmentControls = [
            {
                icon: 'editor-alignleft',
                title: __( 'Left' ),
                onClick: () => updateExpandAlignment('left'),
                isActive: expandAlignment === 'left',
            },            
            {
                icon: 'editor-aligncenter',
                title: __( 'Center' ),
                onClick: () => updateExpandAlignment('center'),
                isActive: expandAlignment === 'center',
            },
            {
                icon: 'editor-alignright',
                title: __( 'Right' ),
                onClick: () => updateExpandAlignment('right'),
                isActive: expandAlignment === 'right',
            },
        ];

        return (
            <div className={className}>

                <div>
                    <BlockControls>
                        <Toolbar controls={ alignmentControls } />
                    </BlockControls>
                </div>

                <div>
                    <InspectorControls>
                        <hr />
                        <div style={{ marginTop: '24px' }}>
                            <TextControl
                                label={ __('Placeholder text', 'tainacan') }
                                value={ placeholderText }
                                onChange={ ( aPlaceholderText ) => {
                                    placeholderText = aPlaceholderText
                                    setAttributes( { placeholderText: placeholderText } );
                                    setContent(); 
                                }}
                            />
                        </div>
                        <br />
                        <div style={{ marginBottom: '12px' }}>
                            <RangeControl
                                label={__('Maximum width size (%)', 'tainacan')}
                                value={ maxWidth ? maxWidth : 80 }
                                onChange={ ( aMaxWidth ) => {
                                    maxWidth = aMaxWidth;
                                    setAttributes( { maxWidth: aMaxWidth } ) 
                                    setContent();
                                }}
                                min={ 15 }
                                max={ 100 }
                            />
                        </div>
                        <br />
                        
                        <div style={{ marginBottom: '12px' }}>
                            <BaseControl
                                id="expanded-alignment-buttons"
                                label={__('Input alignment when not expanded')}
                                help={__('This is the alignment of the input field for "alternate" and "stylish" styles when it is not expanded.', 'tainacan')}
                            >
                                <Toolbar controls={ expandAlignmentControls } />
                            </BaseControl>
                            
                        </div>
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

                                        <RangeControl
                                            label={__('Header height (px)', 'tainacan')}
                                            value={ collectionHeaderHeight ? collectionHeaderHeight : 165 }
                                            onChange={ ( aHeight ) => {
                                                collectionHeaderHeight = aHeight;
                                                setAttributes( { collectionHeaderHeight: collectionHeaderHeight } ) 
                                                setContent();
                                            }}
                                            min={ 165 }
                                            max={ 400 } />

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

                                        <SelectControl
                                            label={__('Collection name size', 'tainacan')}
                                            value={ collectionTextSize ? collectionTextSize : 2 }
                                            options={ [
                                                { label: __('Huge', 'tainacan'), value: 3 },
                                                { label: __('Large', 'tainacan'), value: 2.5 },
                                                { label: __('Medium', 'tainacan'), value: 2 },
                                                { label: __('Small', 'tainacan'), value: 1.5 },
                                            ] }
                                            onChange={ ( size ) => { 
                                                collectionTextSize = size;
                                                setAttributes({ collectionTextSize: collectionTextSize })  
                                            }}
                                        />
                                    </div>
                                : null
                                }
                        </PanelBody> 
                    </InspectorControls>
                </div>

                { isSelected ? 
                    (
                    <div>
                        { isModalOpen ? 
                            <SearchBarModal
                                existingCollectionId={ collectionId } 
                                existingCollectionSlug={ collectionSlug }
                                onSelectCollection={ (selectedCollection) => {
                                    collectionId = selectedCollection.id;
                                    collectionSlug = selectedCollection.slug;
                                    setAttributes({ 
                                        collectionId: collectionId, 
                                        collectionSlug: collectionSlug,
                                        isModalOpen: false
                                    });
                                    fetchCollectionForHeader();
                                    setContent();
                                }}
                                onCancelSelection={ () => setAttributes({ isModalOpen: false }) }/> 
                            : null
                        }
                        
                        { collectionId ? (
                            <div className="block-control">
                                <p>
                                <span class="icon">
                                    <i>
                                        <svg width="24" height="24" viewBox="-2 -2 20 20">
                                                <path d="M0,5.8C0,5,0.2,4.2,0.5,3.5s0.7-1.3,1.2-1.8s1.1-0.9,1.8-1.2C4.2,0.1,5,0,5.8,0S7.3,0.1,8,0.5
                                                    c0.7,0.3,1.3,0.7,1.8,1.2s0.9,1.1,1.2,1.8c0.5,1.2,0.5,2.5,0.2,3.7c0,0.2-0.1,0.4-0.2,0.6c0,0.1-0.2,0.6-0.2,0.6
                                                    c0.6,0.6,1.3,1.3,1.9,1.9c0.7,0.7,1.3,1.3,2,2c0,0,0.3,0.2,0.3,0.3c0,0.3-0.1,0.7-0.3,1c-0.2,0.6-0.8,1-1.4,1.2
                                                    c-0.1,0-0.6,0.2-0.6,0.1c0,0-4.2-4.2-4.2-4.2c0,0-0.8,0.3-0.8,0.4c-1.3,0.4-2.8,0.5-4.1-0.1c-0.7-0.3-1.3-0.7-1.8-1.2
                                                    C1.2,9.3,0.8,8.7,0.5,8S0,6.6,0,5.8z M1.6,5.8c0,0.4,0.1,0.9,0.2,1.3C2.1,8.2,3,9.2,4.1,9.6c0.5,0.2,1,0.3,1.6,0.3
                                                    c0.6,0,1.1-0.1,1.6-0.3C8.7,9,9.7,7.6,9.8,6c0.1-1.5-0.6-3.1-2-3.9c-0.9-0.5-2-0.6-3-0.4C4.6,1.8,4.4,1.9,4.1,2
                                                    c-0.5,0.2-1,0.5-1.4,0.9C2,3.7,1.6,4.7,1.6,5.8z"/>       
                                                </svg>
                                            </i> 
                                        </span>
                                    {__('Set up a custom search bar to redirect to an item\'s list', 'tainacan')}
                                </p>
                                <Button
                                    isPrimary
                                    type="submit"
                                    onClick={ () => openSearchBarModal() }>
                                    {__('Configure search source', 'tainacan')}
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
                            <div>
                                <div
                                    className={ 'search-bar-collection-header-image' }
                                    style={{
                                        backgroundImage: collection.header_image ? 'url(' + collection.header_image + ')' : '',
                                        height: collectionHeaderHeight ? collectionHeaderHeight + 'px' : '160px'
                                    }}>
                                </div> 
                                <div
                                    className={ 
                                        'search-bar-collection-header-container' + 
                                        (alignment == 'left' ? ' is-aligned-left' : '') + 
                                        (alignment == 'right' ? ' is-aligned-right' : '')
                                    }
                                    style={{
                                        backgroundColor: collectionBackgroundColor
                                    }}>
                                    <h3 style={{  
                                        color: collectionTextColor ? collectionTextColor : '',
                                        fontSize: collectionTextSize ? collectionTextSize + 'rem' : '2rem' 
                                    }}>
                                        { showCollectionLabel ? <span class="label">{ __('Collection', 'tainacan') }<br/></span> : null }
                                        { collection && collection.name ? collection.name : '' }
                                    </h3>
                                    { collectionId && collectionSlug ?
                                        content
                                        : null
                                    }
                                </div>
                            </div>
                        }
                    </div>
                    : null
                }

                { !collectionId ? (
                    <Placeholder
                        icon={(
                            <img
                                width={148}
                                src={ `${tainacan_plugin.base_url}/admin/images/tainacan_logo_header.svg` }
                                alt="Tainacan Logo"/>
                        )}>
                        <p>
                            <span class="icon">
                                <i>
                                    <svg width="24" height="24" viewBox="-2 -2 20 20">
                                    <path d="M0,5.8C0,5,0.2,4.2,0.5,3.5s0.7-1.3,1.2-1.8s1.1-0.9,1.8-1.2C4.2,0.1,5,0,5.8,0S7.3,0.1,8,0.5
                                        c0.7,0.3,1.3,0.7,1.8,1.2s0.9,1.1,1.2,1.8c0.5,1.2,0.5,2.5,0.2,3.7c0,0.2-0.1,0.4-0.2,0.6c0,0.1-0.2,0.6-0.2,0.6
                                        c0.6,0.6,1.3,1.3,1.9,1.9c0.7,0.7,1.3,1.3,2,2c0,0,0.3,0.2,0.3,0.3c0,0.3-0.1,0.7-0.3,1c-0.2,0.6-0.8,1-1.4,1.2
                                        c-0.1,0-0.6,0.2-0.6,0.1c0,0-4.2-4.2-4.2-4.2c0,0-0.8,0.3-0.8,0.4c-1.3,0.4-2.8,0.5-4.1-0.1c-0.7-0.3-1.3-0.7-1.8-1.2
                                        C1.2,9.3,0.8,8.7,0.5,8S0,6.6,0,5.8z M1.6,5.8c0,0.4,0.1,0.9,0.2,1.3C2.1,8.2,3,9.2,4.1,9.6c0.5,0.2,1,0.3,1.6,0.3
                                        c0.6,0,1.1-0.1,1.6-0.3C8.7,9,9.7,7.6,9.8,6c0.1-1.5-0.6-3.1-2-3.9c-0.9-0.5-2-0.6-3-0.4C4.6,1.8,4.4,1.9,4.1,2
                                        c-0.5,0.2-1,0.5-1.4,0.9C2,3.7,1.6,4.7,1.6,5.8z"/>       
                                    </svg>
                                </i> 
                            </span>
                            {__('Set up a custom search bar to redirect to an item\'s list', 'tainacan')}
                        </p>
                        <Button
                            isPrimary
                            type="submit"
                            onClick={ () => openSearchBarModal() }>
                            {__('Select search source', 'tainacan')}
                        </Button>   
                    </Placeholder>
                    ) : null
                }
                
                { collectionId && collectionSlug && !showCollectionHeader?
                    <div>
                        { content }
                    </div>
                    : null
                }
            </div>
        );
    },
    save({ attributes, className }){
        const {
            content
        } = attributes;
        
        return <div className={ className }>{ content }</div>
    }
});