const { RichText, useBlockProps } = (tainacan_blocks.wp_version < '5.2' ? wp.editor : wp.blockEditor );

export default function({ attributes, className }) {
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
                collection-id={ collectionId }
                hide-file-modal-button={ hideFileModalButton.toString() }
                hide-text-modal-button={ hideTextModalButton.toString() }
                hide-link-modal-button={ hideLinkModalButton.toString() }
                hide-thumbnail-section={ hideThumbnailSection.toString() }
                hide-attachments-section={ hideAttachmentsSection.toString() }
                show-allow-comments-section={ showAllowCommentsSection.toString() }
                hide-help-buttons={ hideHelpButtons.toString() }
                hide-metadata-types={ hideMetadataTypes.toString() }
                hide-collapses={ hideCollapses.toString() }
                enabled-metadata={ enabledMetadata.toString() }
                sent-form-heading={ sentFormHeading }
                sent-form-message={ sentFormMessage }
                document-section-label={ documentSectionLabel }
                thumbnail-section-label={ thumbnailSectionLabel }
                attachments-section-label={ attachmentsSectionLabel }
                metadata-section-label={ metadataSectionLabel }
                show-item-link-button={ showItemLinkButton ? showItemLinkButton.toString() : 'false' }
                show-terms-agreement-checkbox={ showTermsAgreementCheckbox ? showTermsAgreementCheckbox.toString() : 'false' }
                terms-agreement-message={ termsAgreementMessageHTML }
                item-link-button-label={ itemLinkButtonLabel ? itemLinkButtonLabel : __( 'Go to the item page', 'tainacan' ) }
                help-info-bellow-label={ helpInfoBellowLabel ? helpInfoBellowLabel.toString() : 'false' }
                is-layout-steps={ isLayoutSteps !== undefined ? isLayoutSteps.toString() : 'false' } >
        </div>
    </div>
};