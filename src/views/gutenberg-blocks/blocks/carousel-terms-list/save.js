const { useBlockProps } = wp.blockEditor;

export default function({ attributes }) {
    const {
        content, 
        blockId,
        selectedTerms,
        arrowsPosition,
        largeArrows,
        arrowsStyle,
        maxTermsPerScreen,
        maxTermsNumber,
        spaceBetweenTerms,
        spaceAroundCarousel,
        autoPlay,
        autoPlaySpeed,
        loopSlides,
        hideName,
        imageSize,
        showTermThumbnail,
        taxonomyId,
        variableTermsWidth
    } = attributes;
    
    // Gets attributes such as style, that are automatically added by the editor hook
    const blockProps = useBlockProps.save();
    return <div 
                { ...blockProps }
                data-module="carousel-terms-list"
                data-selected-terms={ JSON.stringify(selectedTerms.map((term) => { return term.id; })) }
                data-arrows-position={ arrowsPosition }
                data-auto-play={ '' + autoPlay }
                data-auto-play-speed={ autoPlaySpeed }
                data-loop-slides={ '' + loopSlides }
                data-hide-name={ '' + hideName }
                data-large-arrows={ '' + largeArrows }
                data-arrows-style={ arrowsStyle }
                data-image-size={ imageSize }
                data-max-terms-number={ maxTermsNumber }
                data-max-terms-per-screen={ maxTermsPerScreen }
                data-space-between-terms={ spaceBetweenTerms }
                data-space-around-carousel={ spaceAroundCarousel }
                data-taxonomy-id={ taxonomyId }
                data-tainacan-api-root={ tainacan_blocks.root }
                data-show-term-thumbnail={ '' + showTermThumbnail }
                data-variable-terms-width={ '' + variableTermsWidth }
                id={ 'wp-block-tainacan-carousel-terms-list_' + blockId }>
                    { content }
            </div>
};