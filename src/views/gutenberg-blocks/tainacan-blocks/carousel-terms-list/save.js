export default function({ attributes, className }) {
    const {
        content, 
        blockId,
        selectedTerms,
        arrowsPosition,
        largeArrows,
        maxTermsPerScreen,
        maxTermsNumber,
        autoPlay,
        autoPlaySpeed,
        loopSlides,
        hideName,
        showTermThumbnail,
        taxonomyId
    } = attributes;
    return <div 
                data-module="carousel-terms-list"
                className={ className }
                selected-terms={ JSON.stringify(selectedTerms.map((term) => { return term.id; })) }
                arrows-position={ arrowsPosition }
                auto-play={ '' + autoPlay }
                auto-play-speed={ autoPlaySpeed }
                loop-slides={ '' + loopSlides }
                hide-name={ '' + hideName }
                large-arrows={ '' + largeArrows }
                max-terms-number={ maxTermsNumber }
                max-terms-per-screen={ maxTermsPerScreen }
                taxonomy-id={ taxonomyId }
                tainacan-api-root={ tainacan_blocks.root }
                tainacan-base-url={ tainacan_blocks.base_url }
                show-term-thumbnail={ '' + showTermThumbnail }
                id={ 'wp-block-tainacan-carousel-terms-list_' + blockId }>
                    { content }
            </div>
};