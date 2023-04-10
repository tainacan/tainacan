const { __ } = wp.i18n;

const {
    Button,
    TextControl,
    TextareaControl,
    BaseControl,
    CheckboxControl,
    FontSizePicker,
    HorizontalRule,
    Spinner,
    ToggleControl,
    Placeholder,
    PanelBody
} = wp.components;

const { InspectorControls, BlockControls, RichText, useBlockProps } = (tainacan_blocks.wp_version < '5.2' ? wp.editor : wp.blockEditor );

import tainacan from '../../js/axios.js';
import CollectionModal from '../faceted-search/collection-modal.js';
import TainacanBlocksCompatToolbar from '../../js/compatibility/tainacan-blocks-compat-toolbar.js';
import TainacanBlocksCompatColorPicker from '../../js/compatibility/tainacan-blocks-compat-colorpicker.js';

export default function ({ attributes, setAttributes, className }) {
    let {
        collectionId,
        isCollectionModalOpen,
        hideFileModalButton,
        hideTextModalButton,
        hideLinkModalButton,
        hideThumbnailSection,
        hideAttachmentsSection,
        showAllowCommentsSection,
        hideCollapses,
        hideHelpButtons,
        hideMetadataTypes,
        baseFontSize,
        backgroundColor,
        inputColor,
        inputBackgroundColor,
        inputBorderColor,
        labelColor,
        infoColor,
        primaryColor,
        secondaryColor,
        isLoadingCollectionMetadata,
        collectionMetadata,
        enabledMetadata,
        sentFormHeading,
        sentFormMessage,
        documentSectionLabel,
        attachmentsSectionLabel,
        thumbnailSectionLabel,
        metadataSectionLabel,
        showItemLinkButton,
        itemLinkButtonLabel,
        helpInfoBellowLabel,
        showTermsAgreementCheckbox,
        termsAgreementMessage,
        isLayoutSteps
    } = attributes;

    const blockProps = useBlockProps();

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
    
    if (backgroundColor.rgb != undefined) {
        if (backgroundColor.rgb.a)
            backgroundColor = 'rgba(' + backgroundColor.rgb.r + ',' + backgroundColor.rgb.g + ',' + backgroundColor.rgb.b + ',' + backgroundColor.rgb.a + ')';
        else
            backgroundColor = 'rgb(' + backgroundColor.rgb.r + ',' + backgroundColor.rgb.g + ',' + backgroundColor.rgb.b + ')';
    }

    // Sets some defaults that were not working
    if (isLayoutSteps === undefined) {
        isLayoutSteps = false;
        setAttributes({ isLayoutSteps: isLayoutSteps });
    }

    function openCollectionModal() {
        isCollectionModalOpen = true;
        setAttributes( {
            isCollectionModalOpen: isCollectionModalOpen
        } );
    }

    function toggleIsEnabledMetadatum(isEnabled, index) {

        enabledMetadata.splice(index, 1, isEnabled);

        setAttributes({
            enabledMetadata: JSON.parse(JSON.stringify(enabledMetadata))
        });
    }

    function loadCollectionMetadata(selectedCollectionId) {
        isLoadingCollectionMetadata = true;
        setAttributes({ isLoadingCollectionMetadata, isLoadingCollectionMetadata });

        tainacan.get('/collection/' + selectedCollectionId + '/metadata/?nopaging=1&include_disabled=false&parent=0')
            .then(response => {
                collectionMetadata = response.data;
                enabledMetadata = new Array(response.data.length).fill(true);

                isLoadingCollectionMetadata = false;

                setAttributes({
                    isLoadingCollectionMetadata : isLoadingCollectionMetadata,
                    collectionMetadata: collectionMetadata,
                    enabledMetadata: enabledMetadata
                });
            });
    }

    return collectionId == 'preview' ?
        <div className={className}>
            <img
                    width="100%"
                    src={ `${tainacan_blocks.base_url}/assets/images/item-submission-form.png` } />
        </div>
        : (
            <div className={className}>

                { collectionId ?
                    <BlockControls>
                        {
                            TainacanBlocksCompatToolbar({
                                label: __('Change target collection', 'tainacan'),
                                icon: <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            viewBox="-2 -2 12 12"
                                            height="24px"
                                            width="24px">
                                        <g transform="translate(227.4751,-183.8442)">
                                            <path d="m -227.47379,191.95102 c 0.0372,-2.35931 -0.0195,-4.71936 0.0185,-7.07842 0.0356,-0.55424 0.52604,-0.99051 1.07473,-0.99702 0.80102,-0.0806 1.607,0.0149 2.41027,-0.0211 1.66482,0.0101 3.33138,-0.0202 4.99509,0.01 0.58652,0.0617 1.0108,0.60434 0.99243,1.1815 0.0416,0.76157 -0.0361,1.52426 0.008,2.28649 0.0113,1.65577 -0.006,3.31214 -0.009,4.96756 -0.0429,0.59366 -0.57833,1.06966 -1.17191,1.04765 -0.92086,0.0516 -1.84377,-0.0208 -2.7657,-0.006 -1.49072,-0.0194 -2.98293,0.0353 -4.47258,0.009 -0.60954,-0.039 -1.111,-0.56284 -1.07768,-1.17994 -0.004,-0.0728 -0.004,-0.1462 -0.002,-0.21867 z m 1.15668,0.28162 c 2.40388,0 4.80776,0 7.21164,0 0,-2.41394 0,-4.82787 0,-7.24181 -2.40388,0 -4.80776,0 -7.21164,0 0,2.41394 0,4.82787 0,7.24181 z m 0.53308,-0.53308 c 0,-0.83147 0,-1.66293 0,-2.4944 0.82476,0 1.64953,0 2.47429,0 0,0.83147 0,1.66293 0,2.4944 -0.82476,0 -1.64953,0 -2.47429,0 z m 2.45417,-6.18571 c 0,0.83482 0,1.66964 0,2.50446 -0.82141,0 -1.64282,0 -2.46423,0 0,-0.83482 0,-1.66964 0,-2.50446 0.82141,0 1.64282,0 2.46423,0 z m 1.1768,1.67969 c 0,-0.28833 0,-0.57666 0,-0.86499 0.82811,0 1.65623,0 2.48434,0 0,0.28833 0,0.57666 0,0.86499 -0.82811,0 -1.65623,0 -2.48434,0 z m 2.48434,2.76598 c 0,0.28162 0,0.56325 0,0.84487 -0.82811,0 -1.65623,0 -2.48434,0 0,-0.28162 0,-0.56325 0,-0.84487 0.82811,0 1.65623,0 2.48434,0 z m -5.64258,-2.49441 c 0.48614,0 0.97228,0 1.45842,0 0,-0.47273 0,-0.94546 0,-1.41819 -0.48614,0 -0.97228,0 -1.45842,0 0,0.47273 0,0.94546 0,1.41819 z m 0,3.68126 c 0.48949,0 0.97899,0 1.46848,0 0,-0.47273 0,-0.94546 0,-1.41819 -0.48949,0 -0.97899,0 -1.46848,0 0,0.47273 0,0.94546 0,1.41819 z"></path>
                                        </g>
                                    </svg>,
                                onClick: openCollectionModal
                            })
                        }
                    </BlockControls>
                : null }

                <div>

                    <InspectorControls>
                        <PanelBody
                                title={ __('Submission feedback', 'tainacan') }
                                initialOpen={ true } >
                            <TextControl
                                label={ __('The heading of the message that will confirm that the submission went well.', 'tainacan') }
                                value={ sentFormHeading }
                                onChange={ ( updatedHeading ) =>{
                                    sentFormHeading = updatedHeading;
                                    setAttributes({ sentFormHeading: sentFormHeading });
                                } }
                            />
                            <TextareaControl
                                label={ __('The message that will confirm that the submission went well.', 'tainacan') }
                                help={ __('You may want to inform the user here that the item is under evaluation or that it will be visible after a certain time.', 'tainacan') }
                                value={ sentFormMessage }
                                onChange={ ( updatedMessage ) =>{
                                    sentFormMessage = updatedMessage;
                                    setAttributes({ sentFormMessage: sentFormMessage });
                                } }
                            />
                            <ToggleControl
                                label={__('Show item link button', 'tainacan') }
                                help={ showItemLinkButton ? __('Do not show a button that links to the item public page.', 'tainacan') : __('Toggle to show a button that links to the item public page.', 'tainacan')}
                                checked={ showItemLinkButton }
                                onChange={ ( isChecked ) => {
                                        showItemLinkButton = isChecked;
                                        setAttributes({ showItemLinkButton: isChecked });
                                    }
                                }
                            />
                            { showItemLinkButton ?
                                <TextControl
                                    label={ __('Label for the item button', 'tainacan') }
                                    value={ itemLinkButtonLabel }
                                    onChange={ ( updatedLinkButtonName ) =>{
                                        itemLinkButtonLabel = updatedLinkButtonName;
                                        setAttributes({ itemLinkButtonLabel: itemLinkButtonLabel });
                                    } }
                                />
                            : null }
                        </PanelBody>
                    </InspectorControls>
                    <InspectorControls>
                        <PanelBody
                                title={ __('Section labels', 'tainacan') }
                                initialOpen={ false } >
                            <TextControl
                                label={ __('Document section label', 'tainacan') }
                                value={ documentSectionLabel }
                                onChange={ ( updatedSectionName ) =>{
                                    documentSectionLabel = updatedSectionName;
                                    setAttributes({ documentSectionLabel: documentSectionLabel });
                                } }
                            />
                            <TextControl
                                label={ __('Thumbnail section label', 'tainacan') }
                                value={ thumbnailSectionLabel }
                                onChange={ ( updatedSectionName ) =>{
                                    thumbnailSectionLabel = updatedSectionName;
                                    setAttributes({ thumbnailSectionLabel: thumbnailSectionLabel });
                                } }
                            />
                            <TextControl
                                label={ __('Attachments section label', 'tainacan') }
                                value={ attachmentsSectionLabel }
                                onChange={ ( updatedSectionName ) =>{
                                    attachmentsSectionLabel = updatedSectionName;
                                    setAttributes({ attachmentsSectionLabel: attachmentsSectionLabel });
                                } }
                            />
                            <TextControl
                                label={ __('Metadata section label', 'tainacan') }
                                value={ metadataSectionLabel }
                                onChange={ ( updatedSectionName ) =>{
                                    metadataSectionLabel = updatedSectionName;
                                    setAttributes({ metadataSectionLabel: metadataSectionLabel });
                                } }
                            />
                        </PanelBody>
                    </InspectorControls>
                    <InspectorControls>
                        <PanelBody
                                title={__('Metadata Input', 'tainacan')}
                                initialOpen={ true } >
                            { !isLoadingCollectionMetadata ?
                                <BaseControl
                                    id="metadata-checkbox-list"
                                    label={ __('Metadata input shown on the list', 'tainacan') }
                                    help={ __('Uncheck the metadata that you do not want to be shown on the form', 'tainacan') }
                                >
                                <ul id="metadata-checkbox-list">
                                    { enabledMetadata.length ?
                                        enabledMetadata.map((isMetadatumEnabled, index) => {
                                            return (
                                                <li>
                                                    <CheckboxControl
                                                        label={ collectionMetadata[index].name + (collectionMetadata[index].required == 'yes' ? ' *' : '') }
                                                        disabled={ collectionMetadata[index].required == 'yes' }
                                                        checked={ isMetadatumEnabled ? true : false }
                                                        help={ collectionMetadata[index].metadata_type_object.name + (collectionMetadata[index].required == 'yes' ? (', ' + __('Required', 'tainacan')) : '' ) + (collectionMetadata[index].collection_id != collectionId ? (' (' + __('Inherited', 'tainacan') + ')' ) : '') }
                                                        onChange={  (isEnabled) => toggleIsEnabledMetadatum(isEnabled, index) }
                                                    />
                                                </li>
                                            )
                                        })
                                        :
                                    <p>{ __('No public metadata was found in this collection', 'tainacan') }</p>
                                    }
                                </ul>
                                </BaseControl>
                            : <Spinner /> }
                        </PanelBody>
                    </InspectorControls>
                    <InspectorControls>
                        <PanelBody
                                title={__('Form elements', 'tainacan')}
                                initialOpen={ true } >
                            <ToggleControl
                                    label={__('Show metadata sections as steps on the form.', 'tainacan')}
                                    help={ isLayoutSteps ? __('Do not show the metadata sections as separate steps of the form.', 'tainacan') : __('Toggle to show the metadata sections as steps of the form.', 'tainacan')}
                                    checked={ isLayoutSteps }
                                    onChange={ ( isChecked ) => {
                                            isLayoutSteps = isChecked;
                                            setAttributes({ isLayoutSteps: isChecked });
                                        }
                                    }
                                />
                            <ToggleControl
                                    label={__('Hide the Document type file button', 'tainacan')}
                                    help={ hideFileModalButton ? __('Do not show the button for uploading a file document', 'tainacan') : __('Toggle to show the button to upload a file document.', 'tainacan')}
                                    checked={ hideFileModalButton }
                                    onChange={ ( isChecked ) => {
                                            hideFileModalButton = isChecked;
                                            setAttributes({ hideFileModalButton: isChecked });
                                        }
                                    }
                                />
                            <ToggleControl
                                    label={__('Hide the Document type text button', 'tainacan')}
                                    help={ hideTextModalButton ? __('Do not show the button for setting a text as document.', 'tainacan') : __('Toggle to show the button to set a text as document.', 'tainacan')}
                                    checked={ hideTextModalButton }
                                    onChange={ ( isChecked ) => {
                                            hideTextModalButton = isChecked;
                                            setAttributes({ hideTextModalButton: isChecked });
                                        }
                                    }
                                />
                            <ToggleControl
                                label={__('Hide the Document type link button', 'tainacan')}
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
                            <ToggleControl
                                label={__('Show "allow comments" section', 'tainacan')}
                                help={ showAllowCommentsSection ? __('Show the option to allow comments on the item page.', 'tainacan') : __('Toggle to hide the option to allow comments on the item page.', 'tainacan')}
                                checked={ showAllowCommentsSection }
                                onChange={ ( isChecked ) => {
                                        showAllowCommentsSection = isChecked;
                                        setAttributes({ showAllowCommentsSection: isChecked });
                                    }
                                }
                            />
                            <ToggleControl
                                label={__('Hide the metadata collapses', 'tainacan')}
                                help={ hideCollapses ? __('Do not show collapsible controls for each metadatum.', 'tainacan') : __('Toggle to show collapsible controls on each metadatum.', 'tainacan')}
                                checked={ hideCollapses }
                                onChange={ ( isChecked ) => {
                                        hideCollapses = isChecked;
                                        setAttributes({ hideCollapses: isChecked });
                                    }
                                }
                            />
                            <ToggleControl
                                label={__('Hide help information', 'tainacan')}
                                help={ hideHelpButtons ? __('Do not show the "?" icon with a help tooltip aside the labels.', 'tainacan') : __('Toggle to show the "?" icon with a help tooltip aside the labels.', 'tainacan')}
                                checked={ hideHelpButtons }
                                onChange={ ( isChecked ) => {
                                        hideHelpButtons = isChecked;
                                        setAttributes({ hideHelpButtons: isChecked });
                                    }
                                }
                            />

                            <ToggleControl
                                label={__('Help info below label', 'tainacan')}
                                help={ helpInfoBellowLabel ? __('Show the help info below the label instead of hidden in the "?" icon on the help tooltip.', 'tainacan') : __('Do not show the help info below the label, keep it on the "?" icon toolip.', 'tainacan')}
                                checked={ helpInfoBellowLabel }
                                onChange={ ( isChecked ) => {
                                        helpInfoBellowLabel = isChecked;
                                        setAttributes({ helpInfoBellowLabel: isChecked });
                                    }
                                }
                            />
                            <ToggleControl
                                label={__('Hide metadata type', 'tainacan')}
                                help={ hideMetadataTypes ? __('Do not show the metadata type aside the metadata label.', 'tainacan') : __('Toggle to show the metadata type aside the metadata label.', 'tainacan')}
                                checked={ hideMetadataTypes }
                                onChange={ ( isChecked ) => {
                                        hideMetadataTypes = isChecked;
                                        setAttributes({ hideMetadataTypes: isChecked });
                                    }
                                }
                            />
                        </PanelBody>
                        <PanelBody
                                    title={__('Confirmation of agreement to terms', 'tainacan')}
                                    initialOpen={ false }>
                                <ToggleControl
                                    label={__('Show terms agreement confimation checkbox', 'tainacan')}
                                    help={ __('With this option enabled, user will be prevented to submit the form until a confirmation checkbox is pressed. You can edit the content of the agreement message on the block itself.', 'tainacan') }
                                    checked={ showTermsAgreementCheckbox }
                                    onChange={ ( isChecked ) => {
                                            showTermsAgreementCheckbox = isChecked;
                                            setAttributes({ showTermsAgreementCheckbox: isChecked });
                                        }
                                    }
                                />
                        </PanelBody>
                    </InspectorControls>
                    <InspectorControls group="styles">
                        <PanelBody
                                    title={__('Dimensions', 'tainacan')}
                                    initialOpen={ true }
                                >
                            <FontSizePicker
                                fontSizes={ fontSizes }
                                value={ baseFontSize }
                                fallbackFontSize={ 16 }
                                onChange={ ( newFontSize ) => {
                                    setAttributes( { baseFontSize: newFontSize } );
                                } }
                            />
                        </PanelBody>
                        <PanelBody
                                    title={__('Colors', 'tainacan')}
                                    initialOpen={ true }
                                >
                            <BaseControl
                                    id="backgroundColorPicker"
                                    label={ __('Background color', 'tainacan')}
                                    help={ __('The background color of the entire items list', 'tainacan') }>
                                <TainacanBlocksCompatColorPicker
                                    value={ backgroundColor }
                                    onChange={ (colorValue ) => {
                                        backgroundColor = colorValue;
                                        setAttributes({ backgroundColor: backgroundColor });
                                    }}
                                    enableAlpha
                                />
                            </BaseControl>
                            <HorizontalRule />
                            <BaseControl
                                    id="secondaryColorPicker"
                                    label={ __('Link and Active Main color', 'tainacan')}
                                    help={ __('The text color links and other action or active state elements, such as select arrows, tooltip contents, etc', 'tainacan') }>
                                <TainacanBlocksCompatColorPicker
                                    value={ secondaryColor }
                                    onChange={ (colorValue ) => {
                                        secondaryColor = colorValue;
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
                                <TainacanBlocksCompatColorPicker
                                    value={ primaryColor }
                                    onChange={ (colorValue ) => {
                                        primaryColor = colorValue;
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
                                <TainacanBlocksCompatColorPicker
                                    value={ inputBackgroundColor }
                                    onChange={ (colorValue ) => {
                                        inputBackgroundColor = colorValue;
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
                                <TainacanBlocksCompatColorPicker
                                    value={ inputColor }
                                    onChange={ (colorValue ) => {
                                        inputColor = colorValue;
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
                                <TainacanBlocksCompatColorPicker
                                    value={ inputBorderColor }
                                    onChange={ (colorValue ) => {
                                        inputBorderColor = colorValue;
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
                                <TainacanBlocksCompatColorPicker
                                    value={ labelColor }
                                    onChange={ (colorValue ) => {
                                        labelColor = colorValue;
                                        setAttributes({ labelColor: labelColor });
                                    }}
                                    disableAlpha
                                />
                            </BaseControl>
                            <BaseControl
                                    id="infoColorPicker"
                                    label={ __('General Info Text color', 'tainacan')}
                                    help={ __('The text color for other information such as item metadata, icons, number of pages, etc', 'tainacan') }>
                                <TainacanBlocksCompatColorPicker
                                    value={ infoColor }
                                    onChange={ (colorValue ) => {
                                        infoColor = colorValue;
                                        setAttributes({ infoColor: infoColor });
                                    }}
                                    disableAlpha
                                />
                            </BaseControl>
                        </PanelBody>
                    </InspectorControls>
                </div>

                { ( !collectionId ) ? (
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
                                    viewBox="0 0 10 10"
                                    height="24px"
                                    width="24px">
                                <g transform="translate(227.4751,-183.8442)">
                                    <path d="m -227.47379,191.95102 c 0.0372,-2.35931 -0.0195,-4.71936 0.0185,-7.07842 0.0356,-0.55424 0.52604,-0.99051 1.07473,-0.99702 0.80102,-0.0806 1.607,0.0149 2.41027,-0.0211 1.66482,0.0101 3.33138,-0.0202 4.99509,0.01 0.58652,0.0617 1.0108,0.60434 0.99243,1.1815 0.0416,0.76157 -0.0361,1.52426 0.008,2.28649 0.0113,1.65577 -0.006,3.31214 -0.009,4.96756 -0.0429,0.59366 -0.57833,1.06966 -1.17191,1.04765 -0.92086,0.0516 -1.84377,-0.0208 -2.7657,-0.006 -1.49072,-0.0194 -2.98293,0.0353 -4.47258,0.009 -0.60954,-0.039 -1.111,-0.56284 -1.07768,-1.17994 -0.004,-0.0728 -0.004,-0.1462 -0.002,-0.21867 z m 1.15668,0.28162 c 2.40388,0 4.80776,0 7.21164,0 0,-2.41394 0,-4.82787 0,-7.24181 -2.40388,0 -4.80776,0 -7.21164,0 0,2.41394 0,4.82787 0,7.24181 z m 0.53308,-0.53308 c 0,-0.83147 0,-1.66293 0,-2.4944 0.82476,0 1.64953,0 2.47429,0 0,0.83147 0,1.66293 0,2.4944 -0.82476,0 -1.64953,0 -2.47429,0 z m 2.45417,-6.18571 c 0,0.83482 0,1.66964 0,2.50446 -0.82141,0 -1.64282,0 -2.46423,0 0,-0.83482 0,-1.66964 0,-2.50446 0.82141,0 1.64282,0 2.46423,0 z m 1.1768,1.67969 c 0,-0.28833 0,-0.57666 0,-0.86499 0.82811,0 1.65623,0 2.48434,0 0,0.28833 0,0.57666 0,0.86499 -0.82811,0 -1.65623,0 -2.48434,0 z m 2.48434,2.76598 c 0,0.28162 0,0.56325 0,0.84487 -0.82811,0 -1.65623,0 -2.48434,0 0,-0.28162 0,-0.56325 0,-0.84487 0.82811,0 1.65623,0 2.48434,0 z m -5.64258,-2.49441 c 0.48614,0 0.97228,0 1.45842,0 0,-0.47273 0,-0.94546 0,-1.41819 -0.48614,0 -0.97228,0 -1.45842,0 0,0.47273 0,0.94546 0,1.41819 z m 0,3.68126 c 0.48949,0 0.97899,0 1.46848,0 0,-0.47273 0,-0.94546 0,-1.41819 -0.48949,0 -0.97899,0 -1.46848,0 0,0.47273 0,0.94546 0,1.41819 z"></path>
                                </g>
                            </svg>
                            {__('Show an item submission form.', 'tainacan')}
                        </p>
                        <Button
                            isPrimary
                            type="button"
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

                                <div>
                                    {
                                        (!hideFileModalButton || !hideTextModalButton || !hideLinkModalButton) ?
                                        (
                                        <div>
                                            { documentSectionLabel ?
                                                <span>
                                                    <span style={{ display: 'flex', alignItems: 'baseline', marginBottom: '5px' }}><span class="fake-text section-label"></span>{ !hideHelpButtons && !helpInfoBellowLabel ? <span class="fake-text fake-help-button"></span> : null }</span>
                                                    { (!hideHelpButtons && helpInfoBellowLabel) ? <div><span class="fake-text fake-text-help-description"></span></div> : null }
                                                </span>
                                            : null }
                                            { [ hideFileModalButton, hideTextModalButton, hideLinkModalButton ].filter((option) => { return option == false }).length > 1 ?
                                                <div class="documents-section">
                                                    { hideFileModalButton ? null : <span class="fake-circle"><span class="fake-icon"></span></span> }
                                                    { hideTextModalButton ? null : <span class="fake-circle"><span class="fake-icon"></span></span> }
                                                    { hideLinkModalButton ? null : <span class="fake-circle"><span class="fake-icon"></span></span> }
                                                </div>
                                            : (
                                                (!hideFileModalButton && hideTextModalButton && hideLinkModalButton) ?
                                                    <div class="fake-image-uploader"></div>
                                                : (
                                                    (hideFileModalButton && !hideTextModalButton && hideLinkModalButton) ?
                                                        <span class="fake-textarea"></span>
                                                    : (
                                                        (hideFileModalButton && hideTextModalButton && !hideLinkModalButton) ?
                                                            <span class="fake-input" style={{ width: '100%' }}></span>
                                                        : null
                                                    )
                                                )
                                            )}
                                        </div>
                                        ) : null
                                    }
                                    { !hideThumbnailSection ?
                                    (
                                        <div style={{ display: 'flex', flexDirection: 'column' }}>
                                            { !thumbnailSectionLabel ?
                                                <span>
                                                    <span style={{ display: 'flex', alignItems: 'baseline' }}><span class="fake-text section-label"></span>{ !hideHelpButtons && !helpInfoBellowLabel ? <span class="fake-text fake-help-button"></span> : null }</span>
                                                    { (!hideHelpButtons && helpInfoBellowLabel) ? <div><span class="fake-text fake-text-help-description"></span></div> : null }
                                                </span>
                                            : null }
                                            <div class="fake-switch"><span class="fake-icon"></span><span class="fake-text"></span></div>
                                        </div>
                                    ) : null
                                    }
                                    { !hideAttachmentsSection ?
                                    (
                                    <div>
                                        { !attachmentsSectionLabel ?
                                        <span
                                                style={{ position: 'relative' }}
                                                class="fake-text section-label">
                                            <div class="fake-tooltip"><div class="fake-link"></div></div>
                                        </span>
                                        : null }
                                        <div class="attachments-section">
                                            <div class="fake-image-uploader"></div>
                                        </div>
                                    </div>
                                    ) : null
                                    }
                                    {
                                    showAllowCommentsSection ?
                                    (
                                        <div>
                                            <span style={{ display: 'flex', alignItems: 'baseline' }}><span class="fake-text section-label"></span>{ !hideHelpButtons && !helpInfoBellowLabel ? <span class="fake-text fake-help-button"></span> : null }</span>
                                            { (!hideHelpButtons && helpInfoBellowLabel) ? <div><span class="fake-text fake-text-help-description"></span></div> : null }
                                            <div class="fake-switch"><span class="fake-icon"></span><span class="fake-text"></span></div>
                                        </div>
                                    ) : null }
                                </div>
                                <div style={{ flexGrow: '1' }}>
                                    { metadataSectionLabel ?
                                        <div class="fake-text section-label"></div>
                                    : null }
                                    { !hideCollapses && !isLayoutSteps ? 
                                        <div class="fake-link"></div>
                                    : null }
                                    { isLayoutSteps ? 
                                        <div class="fake-steps">
                                            <div class="fake-step"/>
                                            <div class="fake-step"/>
                                            <div class="fake-step"/>
                                        </div>
                                    : null }
                                    <div class="metadata-section">
                                        { enabledMetadata.length ?
                                            enabledMetadata.map( (isEnabled) => {
                                                return isEnabled ?
                                                    <div class={ 'fake-metadata' + (!hideCollapses ? ' has-collapse' : '') }>
                                                        { !hideCollapses ? <span class="fake-collapse-arrow"></span> : null }
                                                        <span style={{ lineHeight: '0em' }}>
                                                            <span class="fake-text"></span>{ !hideMetadataTypes ? <span class="fake-text fake-text-info"></span> : null }{ !hideHelpButtons && !helpInfoBellowLabel ? <span class="fake-text fake-help-button"></span> : null }
                                                            { (!hideHelpButtons && helpInfoBellowLabel) ? <div><span class="fake-text fake-text-help-description"></span></div> : null }
                                                        </span>
                                                        <span class="fake-input"></span>
                                                    </div>
                                                : null
                                            }) :
                                            Array(12).fill().map( () => {
                                                return <div class={ 'fake-metadata' + (!hideCollapses ? ' has-collapse' : '') }>
                                                    { !hideCollapses ? <span class="fake-collapse-arrow"></span> : null }
                                                    <span class="fake-text"></span>
                                                    <span class="fake-input"></span>
                                                </div>
                                            })
                                        }
                                    </div>
                                </div>

                                { showTermsAgreementCheckbox ?
                                    <div class="fake-checkbox-confirmation">
                                        <span class="fake-checkbox"></span>
                                        <RichText
                                                { ...blockProps }
                                                tagName="p"
                                                value={ termsAgreementMessage }
                                                onChange={ ( inputContent ) => setAttributes( { termsAgreementMessage: inputContent } ) }
                                                placeholder={ __( 'Type a message requiring the user to agree with certain conditions.' ) }
                                            />
                                    </div>
                                    : null
                                }

                                <div class="form-footer">
                                    <span class="fake-button outline"><span class="fake-text"></span></span>
                                    <span class="fake-text"></span>
                                    <span class="fake-button"><span class="fake-text"></span></span>
                                </div>
                            </div>
                        </div>

                    )
                }

                { isCollectionModalOpen ?
                    <CollectionModal
                        filterOptionsBy={ { allows_submission: 'yes' } }
                        existingCollectionId={ collectionId }
                        onSelectCollection={ ({ collectionId }) => {
                            collectionId = collectionId;
                            setAttributes({
                                collectionId: collectionId,
                                isCollectionModalOpen: false
                            });
                            loadCollectionMetadata(collectionId);
                        }}
                        onCancelSelection={ () => setAttributes({ isCollectionModalOpen: false }) }/>
                    : null
                }

            </div>
    );
};
