const { registerBlockType } = wp.blocks;

const { __ } = wp.i18n;

const { Button, ColorPicker, BaseControl, CheckboxControl, RangeControl, FontSizePicker, HorizontalRule, SelectControl, ToggleControl, Placeholder, PanelBody } = wp.components;

const { InspectorControls } = wp.editor;

import CollectionModal from '../../tainacan-facets/faceted-search/collection-modal.js';

registerBlockType('tainacan/item-submission-form', {
    title: __('Tainacan Item Submission Form', 'tainacan'),
    icon:
        <svg 
                xmlns="http://www.w3.org/2000/svg" 
                viewBox="0 0 24 24"
                height="24px"
                width="24px">
            <g transform="matrix(0.86395091,0,0,0.86395091,1.6325891,-234.22601)">
                <path
                        fill="var(--tainacan-block-primary, $primary)"
                        d="m 4.7336928,273.04197 c -1.5846271,-0.0613 -2.8453477,1.48564 -2.646643,3.01801 0.00883,6.16098 -0.017679,12.32284 0.013295,18.48327 0.1053115,1.51106 1.6131262,2.57443 3.0680826,2.39726 4.7229361,0 9.4458716,0 14.1688076,0 1.566507,-0.002 2.76553,-1.53973 2.576794,-3.05227 0,-4.29703 0,-8.59406 0,-12.89109 -2.651301,-2.65173 -5.302603,-5.30345 -7.953904,-7.95518 -3.075478,0 -6.1509548,0 -9.2264322,0 z m 7.9716892,1.99261 c 2.405349,2.42821 4.810699,4.85642 7.216048,7.28463 -2.42821,0 -4.85642,0 -7.28463,0 0.02286,-2.42821 0.04572,-4.85642 0.06858,-7.28463 z "/>
            </g>
        </svg>,
    category: 'tainacan-blocks',
    keywords: [ __( 'item', 'tainacan' ), __( 'submission', 'tainacan' ), __( 'form', 'tainacan' ) ],
    description: __('A public item submission form, to allow visitors to create items drafts.', 'tainacan'),
    attributes: {
        collectionId: {
            type: String,
            default: undefined
        },
        isCollectionModalOpen: {
            type: Boolean,
            default: false
        },
        hideFileModalButton: {
            type: Boolean,
            default: false
        },
        hideTextModalButton: {
            type: Boolean,
            default: false
        },
        hideLinkModalButton: {
            type: Boolean,
            default: false
        },
        hideThumbnailSection: {
            type: Boolean,
            default: false
        },
        hideAttachmentsSection: {
            type: Boolean,
            default: false
        },
        backgroundColor: {
            type: String,
            default: '#ffffff'
        },
        baseFontSize: {
            type: Number,
            default: 16
        },
        inputColor: {
            type: String,
            default: '#1d1d1d'
        },
        inputBackgroundColor: {
            type: String,
            default: '#ffffff'
        },
        inputBorderColor: {
            type: String,
            default: '#dbdbdb'
        },
        labelColor: {
            type: String,
            default: '#454647'
        },
        infoColor: {
            type: String,
            default: '#555758'
        },
        primaryColor: {
            type: String,
            default: '#d9eced'
        },
        secondaryColor: {
            type: String,
            default: '#298596'
        }
    },
    supports: {
        align: ['full', 'wide'],
        html: true,
        multiple: false
    },
    edit({ attributes, setAttributes, className, isSelected, clientId }){
        let {
            collectionId,
            isCollectionModalOpen,
            hideFileModalButton,
            hideTextModalButton,
            hideLinkModalButton,
            hideThumbnailSection,
            hideAttachmentsSection,
            baseFontSize,
            backgroundColor,
            inputColor,
            inputBackgroundColor,
            inputBorderColor,
            labelColor,
            infoColor,
            primaryColor,
            secondaryColor
        } = attributes;

        const fontSizes = [
            {
                name: __( 'Tiny', 'tainacan' ),
                slug: 'tiny',
                size: 12,
            },
            {
                name: __( 'Small', 'tainacan' ),
                slug: 'small',
                size: 14,
            },
            {
                name: __( 'Normal', 'tainacan' ),
                slug: 'normal',
                size: 16,
            },
            {
                name: __( 'Big', 'tainacan' ),
                slug: 'big',
                size: 18,
            },
            {
                name: __( 'Huge', 'tainacan' ),
                slug: 'huge',
                size: 20,
            },
        ];

        function openCollectionModal() {
            isCollectionModalOpen = true;
            setAttributes( { 
                isCollectionModalOpen: isCollectionModalOpen
            } );
        }

        return (
            <div className={className}>

                <div>
                    <InspectorControls>
                        <PanelBody
                                title={__('Colors and Sizes', 'tainacan')}
                                initialOpen={ true } >
                            <ToggleControl
                                    label={__('Hide the file submission button', 'tainacan')}
                                    help={ hideFileModalButton ? __('Do not show the button for uploading a file document', 'tainacan') : __('Toggle to show the button to upload a file document.', 'tainacan')}
                                    checked={ hideFileModalButton }
                                    onChange={ ( isChecked ) => {
                                            hideFileModalButton = isChecked;
                                            setAttributes({ hideFileModalButton: isChecked });
                                        }  
                                    }
                                />
                            <ToggleControl
                                    label={__('Hide the text submission button', 'tainacan')}
                                    help={ hideTextModalButton ? __('Do not show the button for setting a text as document.', 'tainacan') : __('Toggle to show the button to set a text as document.', 'tainacan')}
                                    checked={ hideTextModalButton }
                                    onChange={ ( isChecked ) => {
                                            hideTextModalButton = isChecked;
                                            setAttributes({ hideTextModalButton: isChecked });
                                        }  
                                    }
                                />
                            <ToggleControl
                                label={__('Hide the link submission button', 'tainacan')}
                                help={ hideLinkModalButton ? __('Do not show the button for setting a link as document.', 'tainacan') : __('Toggle to show the button to set a link as document.', 'tainacan')}
                                checked={ hideLinkModalButton }
                                onChange={ ( isChecked ) => {
                                        hideLinkModalButton = isChecked;
                                        setAttributes({ hideLinkModalButton: isChecked });
                                    }  
                                }
                            />
                            <ToggleControl
                                label={__('Hide the thumbnail section', 'tainacan')}
                                help={ hideThumbnailSection ? __('Do not show the thumbnail section.', 'tainacan') : __('Toggle to show the thumbnail section.', 'tainacan')}
                                checked={ hideThumbnailSection }
                                onChange={ ( isChecked ) => {
                                        hideThumbnailSection = isChecked;
                                        setAttributes({ hideThumbnailSection: isChecked });
                                    }  
                                }
                            />
                            <ToggleControl
                                label={__('Hide the attachments section', 'tainacan')}
                                help={ hideAttachmentsSection ? __('Do not show the attachments section.', 'tainacan') : __('Toggle to show the attachments section.', 'tainacan')}
                                checked={ hideAttachmentsSection }
                                onChange={ ( isChecked ) => {
                                        hideAttachmentsSection = isChecked;
                                        setAttributes({ hideAttachmentsSection: isChecked });
                                    }  
                                }
                            />
                        </PanelBody>
                        <PanelBody
                                    title={__('Colors and Sizes', 'tainacan')}
                                    initialOpen={ false }
                                >
                            <FontSizePicker
                                fontSizes={ fontSizes }
                                value={ baseFontSize }
                                fallbackFontSize={ 16 }
                                onChange={ ( newFontSize ) => {
                                    setAttributes( { baseFontSize: newFontSize } );
                                } }
                            />
                            <HorizontalRule />
                            <BaseControl
                                    id="backgroundColorPicker"
                                    label={ __('Background color', 'tainacan')}
                                    help={ __('The background color of the entire items list', 'tainacan') }>
                                <ColorPicker
                                    color={ backgroundColor }
                                    onChangeComplete={ (colorValue ) => {
                                        backgroundColor = colorValue.hex;
                                        setAttributes({ backgroundColor: backgroundColor });
                                    }}
                                    disableAlpha
                                />
                            </BaseControl>
                            <HorizontalRule />
                            <BaseControl
                                    id="secondaryColorPicker"
                                    label={ __('Link and Active Main color', 'tainacan')}
                                    help={ __('The text color links and other action or active state elements, such as select arrows, tooltip contents, etc', 'tainacan') }>
                                <ColorPicker
                                    color={ secondaryColor }
                                    onChangeComplete={ (colorValue ) => {
                                        secondaryColor = colorValue.hex;
                                        setAttributes({ secondaryColor: secondaryColor });
                                    }}
                                    disableAlpha
                                />
                            </BaseControl>
                            <HorizontalRule />
                            <BaseControl
                                    id="primaryColorPicker"
                                    label={ __('Tooltips background color', 'tainacan')}
                                    help={ __('The tooltips background color and other elements, such as the hide filters button', 'tainacan') }>
                                <ColorPicker
                                    color={ primaryColor }
                                    onChangeComplete={ (colorValue ) => {
                                        primaryColor = colorValue.hex;
                                        setAttributes({ primaryColor: primaryColor });
                                    }}
                                    disableAlpha
                                />
                            </BaseControl>
                            <HorizontalRule />
                            <BaseControl
                                    id="inputBackgroundColorPicker"
                                    label={ __('Input Background color', 'tainacan')}
                                    help={ __('The background color for input fields', 'tainacan') }>
                                <ColorPicker
                                    color={ inputBackgroundColor }
                                    onChangeComplete={ (colorValue ) => {
                                        inputBackgroundColor = colorValue.hex;
                                        setAttributes({ inputBackgroundColor: inputBackgroundColor });
                                    }}
                                    disableAlpha
                                />
                            </BaseControl>
                            <HorizontalRule />
                            <BaseControl
                                    id="inputColorPicker"
                                    label={ __('Input Text color', 'tainacan')}
                                    help={ __('The text color for input fields, including dropdowns and buttons', 'tainacan') }>
                                <ColorPicker
                                    color={ inputColor }
                                    onChangeComplete={ (colorValue ) => {
                                        inputColor = colorValue.hex;
                                        setAttributes({ inputColor: inputColor });
                                    }}
                                    disableAlpha
                                />
                            </BaseControl>
                            <HorizontalRule />
                            <BaseControl
                                    id="inputBorderColorPicker"
                                    label={ __('Input Border color', 'tainacan')}
                                    help={ __('The border color for input fields', 'tainacan') }>
                                <ColorPicker
                                    color={ inputBorderColor }
                                    onChangeComplete={ (colorValue ) => {
                                        inputBorderColor = colorValue.hex;
                                        setAttributes({ inputBorderColor: inputBorderColor });
                                    }}
                                    disableAlpha
                                />
                            </BaseControl>
                            <HorizontalRule />
                            <BaseControl
                                    id="labelColorPicker"
                                    label={ __('Label Text color', 'tainacan')}
                                    help={ __('The text color for field labels', 'tainacan') }>
                                <ColorPicker
                                    color={ labelColor }
                                    onChangeComplete={ (colorValue ) => {
                                        labelColor = colorValue.hex;
                                        setAttributes({ labelColor: labelColor });
                                    }}
                                    disableAlpha
                                />
                            </BaseControl>
                            <BaseControl
                                    id="infoColorPicker"
                                    label={ __('General Info Text color', 'tainacan')}
                                    help={ __('The text color for other information such as item metadata, icons, number of pages, etc', 'tainacan') }>
                                <ColorPicker
                                    color={ infoColor }
                                    onChangeComplete={ (colorValue ) => {
                                        infoColor = colorValue.hex;
                                        setAttributes({ infoColor: infoColor });
                                    }}
                                    disableAlpha
                                />
                            </BaseControl>
                        </PanelBody>
                    </InspectorControls>
                </div>

                { (isSelected && collectionId != undefined ) ? 
                    (
                    <div>
                        <div className="tainacan-block-control">
                            <p style={{ display: 'flex', alignItems: 'baseline' }}>
                                <svg 
                                        xmlns="http://www.w3.org/2000/svg" 
                                        viewBox="0 0 24 24"
                                        height="24px"
                                        width="24px">
                                    <g transform="matrix(0.86395091,0,0,0.86395091,1.6325891,-234.22601)">
                                        <path
                                                fill="var(--tainacan-block-primary, $primary)"
                                                d="m 4.7336928,273.04197 c -1.5846271,-0.0613 -2.8453477,1.48564 -2.646643,3.01801 0.00883,6.16098 -0.017679,12.32284 0.013295,18.48327 0.1053115,1.51106 1.6131262,2.57443 3.0680826,2.39726 4.7229361,0 9.4458716,0 14.1688076,0 1.566507,-0.002 2.76553,-1.53973 2.576794,-3.05227 0,-4.29703 0,-8.59406 0,-12.89109 -2.651301,-2.65173 -5.302603,-5.30345 -7.953904,-7.95518 -3.075478,0 -6.1509548,0 -9.2264322,0 z m 7.9716892,1.99261 c 2.405349,2.42821 4.810699,4.85642 7.216048,7.28463 -2.42821,0 -4.85642,0 -7.28463,0 0.02286,-2.42821 0.04572,-4.85642 0.06858,-7.28463 z "/>
                                    </g>
                                </svg>
                                {__('Submit items to colection.', 'tainacan') }
                                &nbsp;
                                <Button
                                    isPrimary
                                    type="submit"
                                    onClick={ () => openCollectionModal() }>
                                    { __('Change Collection', 'tainacan') }
                                </Button>
                            </p>  
                        </div>
                    </div>
                    ) : null
                }

                { ( collectionId == undefined ) ? (
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
                                <g transform="matrix(0.86395091,0,0,0.86395091,1.6325891,-234.22601)">
                                    <path
                                            fill="var(--tainacan-block-primary, $primary)"
                                            d="m 4.7336928,273.04197 c -1.5846271,-0.0613 -2.8453477,1.48564 -2.646643,3.01801 0.00883,6.16098 -0.017679,12.32284 0.013295,18.48327 0.1053115,1.51106 1.6131262,2.57443 3.0680826,2.39726 4.7229361,0 9.4458716,0 14.1688076,0 1.566507,-0.002 2.76553,-1.53973 2.576794,-3.05227 0,-4.29703 0,-8.59406 0,-12.89109 -2.651301,-2.65173 -5.302603,-5.30345 -7.953904,-7.95518 -3.075478,0 -6.1509548,0 -9.2264322,0 z m 7.9716892,1.99261 c 2.405349,2.42821 4.810699,4.85642 7.216048,7.28463 -2.42821,0 -4.85642,0 -7.28463,0 0.02286,-2.42821 0.04572,-4.85642 0.06858,-7.28463 z "/>
                                </g>
                            </svg>
                            {__('Show an item submission form.', 'tainacan')}
                        </p>
                        <Button
                            isPrimary
                            type="submit"
                            onClick={ () => openCollectionModal() }>
                            { __('Select a target Collection', 'tainacan')}
                        </Button>
                           
                    </Placeholder>
                    ) : (
                        
                        <div style={{ fontSize: (baseFontSize - 2) + 'px' }}>
                            <div class="preview-warning">
                                { __('Warning: this is just a demonstration. To see the submission form, either preview or publish your post.', 'tainacan') }
                            </div>
                            <div 
                                    style={{
                                        '--tainacan-background-color': backgroundColor,
                                        '--tainacan-input-color': inputColor,
                                        '--tainacan-input-background-color': inputBackgroundColor,
                                        '--tainacan-input-border-color': inputBorderColor,
                                        '--tainacan-label-color': labelColor,
                                        '--tainacan-info-color': infoColor,
                                        '--tainacan-primary': primaryColor,
                                        '--tainacan-secondary': secondaryColor
                                    }}
                                    class="item-submission-form-placeholder">
                                <div class="form-columns">
                                    <div>
                                        { 
                                            (!hideFileModalButton || !hideTextModalButton || !hideLinkModalButton) ? 
                                            (
                                            <div>
                                                <span class="fake-text section-label"></span>
                                                <div class="documents-section">
                                                    { hideFileModalButton ? null : <span class="fake-circle"><span class="fake-icon"></span></span> }
                                                    { hideTextModalButton ? null : <span class="fake-circle"><span class="fake-icon"></span></span> }
                                                    { hideLinkModalButton ? null : <span class="fake-circle"><span class="fake-icon"></span></span> }
                                                </div>
                                            </div>
                                            ) : null 
                                        }
                                        { !hideThumbnailSection ? 
                                        (
                                            <div style={{ display: 'flex', flexDirection: 'column' }}>
                                                <span class="fake-text section-label"></span>
                                                <div class="thumbnail-section">
                                                    <div 
                                                            style={{ 
                                                                backgroundImage: tainacan_plugin ? 'url("' + tainacan_plugin.base_url + '/assets/images/placeholder_square.png")' : '' 
                                                            }}
                                                            class="fake-image"></div>
                                                    <span class="fake-button"><span class="fake-icon"></span></span>
                                                </div>
                                            </div>
                                        ) : null
                                        }
                                        { !hideAttachmentsSection ? 
                                        (
                                        <div>
                                            <span 
                                                    style={{ position: 'relative' }}
                                                    class="fake-text section-label">
                                                <div class="fake-tooltip"><div class="fake-link"></div></div>
                                            </span>
                                            <div class="attachments-section">
                                                <div class="fake-image"></div>
                                                <div class="fake-image"></div>
                                                <div class="fake-image"></div>
                                                <div class="fake-image"></div>
                                            </div>
                                        </div>
                                        ) : null 
                                        }
                                        <span class="fake-text section-label"></span>
                                        <div class="fake-switch"><span class="fake-icon"></span><span class="fake-text"></span></div>
                                        
                                    </div>
                                    <div style={{ flexGrow: '1' }}>
                                        <div class="fake-text section-label"></div>
                                        <div class="fake-link"></div>
                                        <div class="metadata-section">
                                            { Array(12).fill().map( () => {
                                                return <div class="fake-metadata">
                                                    <span class="fake-text"></span>
                                                    <span class="fake-input"></span>
                                                </div>
                                            } )}
                                        </div>
                                    </div>
                                </div>
                                <div class="form-footer">
                                    <span class="fake-text"></span>
                                    <span class="fake-button outline"><span class="fake-text"></span></span>
                                    <span class="fake-button"><span class="fake-text"></span></span>
                                </div>
                            </div>
                        </div>
                        
                    )
                }

                { isCollectionModalOpen ? 
                    <CollectionModal
                        existingCollectionId={ collectionId } 
                        onSelectCollection={ ({ collectionId}) => {
                            collectionId = collectionId;
                            setAttributes({
                                collectionId: collectionId,
                                isCollectionModalOpen: false
                            });
                        }}
                        onCancelSelection={ () => setAttributes({ isCollectionModalOpen: false }) }/> 
                    : null
                }

            </div>
        );
    },
    save({ attributes, className }){
        const {
            collectionId,
            backgroundColor,
            hideFileModalButton,
            hideTextModalButton,
            hideLinkModalButton,
            hideThumbnailSection,
            hideAttachmentsSection,
            baseFontSize,
            inputColor,
            inputBackgroundColor,
            inputBorderColor,
            labelColor,
            infoColor,
            primaryColor,
            secondaryColor
        } = attributes;
        
        return <div 
                    style={{
                        'font-size': baseFontSize + 'px',
                        '--tainacan-base-font-size': baseFontSize + 'px',
                        '--tainacan-background-color': backgroundColor,
                        '--tainacan-input-color': inputColor,
                        '--tainacan-input-background-color': inputBackgroundColor,
                        '--tainacan-input-border-color': inputBorderColor,
                        '--tainacan-label-color': labelColor,
                        '--tainacan-info-color': infoColor,
                        '--tainacan-primary': primaryColor,
                        '--tainacan-secondary': secondaryColor
                    }}
                    className={ className }>
            <div 
                    id="tainacan-item-submission-form"
                    collection-id={ collectionId }
                    hide-file-modal-button={ hideFileModalButton.toString() }
                    hide-text-modal-button={ hideTextModalButton.toString() }
                    hide-link-modal-button={ hideLinkModalButton.toString() }
                    hide-thumbnail-section={ hideThumbnailSection.toString() }
                    hide-attachments-section={ hideAttachmentsSection.toString() }>
            </div>
        </div>
    }
});