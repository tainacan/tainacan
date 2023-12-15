const { RichText, useBlockProps } = wp.blockEditor;

export default function({ attributes }) {
    const {
        collectionId,
        backgroundColor,
        hideFileModalButton,
        hideTextModalButton,
        hideLinkModalButton,
        hideThumbnailSection,
        hideAttachmentsSection,
        showAllowCommentsSection,
        hideHelpButtons,
        hideMetadataTypes,
        hideCollapses,
        documentSectionLabel,
        thumbnailSectionLabel,
        attachmentsSectionLabel,
        metadataSectionLabel,
        baseFontSize,
        inputColor,
        inputBackgroundColor,
        inputBorderColor,
        labelColor,
        infoColor,
        primaryColor,
        secondaryColor,
        enabledMetadata,
        sentFormHeading,
        sentFormMessage,
        showItemLinkButton,
        itemLinkButtonLabel,
        helpInfoBellowLabel,
        showTermsAgreementCheckbox,
        termsAgreementMessage,
        isLayoutSteps
    } = attributes;
    
    const blockProps = useBlockProps.save();
    const className = blockProps.className;

    let termsAgreementMessageHTML = <RichText.Content { ...blockProps } tagName="p" value={ termsAgreementMessage } />;
    termsAgreementMessageHTML = (termsAgreementMessageHTML && termsAgreementMessageHTML.props && termsAgreementMessageHTML.props.value) ? termsAgreementMessageHTML.props.value : '';

    if (backgroundColor.rgb != undefined) {
        if (backgroundColor.rgb.a)
            backgroundColor = 'rgba(' + backgroundColor.rgb.r + ',' + backgroundColor.rgb.g + ',' + backgroundColor.rgb.b + ',' + backgroundColor.rgb.a + ')';
        else
            backgroundColor = 'rgb(' + backgroundColor.rgb.r + ',' + backgroundColor.rgb.g + ',' + backgroundColor.rgb.b + ')';
    }

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
                data-module="item-submission-form"
                data-collection-id={ collectionId }
                data-hide-file-modal-button={ hideFileModalButton.toString() }
                data-hide-text-modal-button={ hideTextModalButton.toString() }
                data-hide-link-modal-button={ hideLinkModalButton.toString() }
                data-hide-thumbnail-section={ hideThumbnailSection.toString() }
                data-hide-attachments-section={ hideAttachmentsSection.toString() }
                data-show-allow-comments-section={ showAllowCommentsSection.toString() }
                data-hide-help-buttons={ hideHelpButtons.toString() }
                data-hide-metadata-types={ hideMetadataTypes.toString() }
                data-hide-collapses={ hideCollapses.toString() }
                data-enabled-metadata={ JSON.stringify(enabledMetadata) }
                data-sent-form-heading={ sentFormHeading }
                data-sent-form-message={ sentFormMessage }
                data-document-section-label={ documentSectionLabel }
                data-thumbnail-section-label={ thumbnailSectionLabel }
                data-attachments-section-label={ attachmentsSectionLabel }
                data-metadata-section-label={ metadataSectionLabel }
                data-show-item-link-button={ showItemLinkButton ? showItemLinkButton.toString() : 'false' }
                data-show-terms-agreement-checkbox={ showTermsAgreementCheckbox ? showTermsAgreementCheckbox.toString() : 'false' }
                data-terms-agreement-message={ termsAgreementMessageHTML }
                data-item-link-button-label={ itemLinkButtonLabel ? itemLinkButtonLabel : __( 'Go to the item page', 'tainacan' ) }
                data-help-info-bellow-label={ helpInfoBellowLabel ? helpInfoBellowLabel.toString() : 'false' }
                data-is-layout-steps={ isLayoutSteps !== undefined ? isLayoutSteps.toString() : 'false' } >
        </div>
    </div>
};