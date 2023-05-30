const { useBlockProps } = (tainacan_blocks.wp_version < '5.2' ? wp.editor : wp.blockEditor );

export default function({ attributes, className }) {
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
        taxonomyId
    } = attributes;
    
    // Gets attributes such as style, that are automatically added by the editor hook
    const blockProps = tainacan_blocks.wp_version < '5.6' ? { className: className } : useBlockProps.save();
    return <div 
                { ...blockProps }
                data-module="carousel-terms-list"
                selected-terms={ JSON.stringify(selectedTerms.map((term) => { return term.id; })) }
                arrows-position={ arrowsPosition }
                auto-play={ '' + autoPlay }
                auto-play-speed={ autoPlaySpeed }
                loop-slides={ '' + loopSlides }
                hide-name={ '' + hideName }
                large-arrows={ '' + largeArrows }
                arrows-style={ arrowsStyle }
                image-size={ imageSize }
                max-terms-number={ maxTermsNumber }
                max-terms-per-screen={ maxTermsPerScreen }
                space-between-terms={ spaceBetweenTerms }
                space-around-carousel={ spaceAroundCarousel }
                taxonomy-id={ taxonomyId }
                tainacan-api-root={ tainacan_blocks.root }
                tainacan-base-url={ tainacan_blocks.base_url }
                show-term-thumbnail={ '' + showTermThumbnail }
                id={ 'wp-block-tainacan-carousel-terms-list_' + blockId }>
                    { content }
            </div>
};