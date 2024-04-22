const { useBlockProps } = wp.blockEditor;

export default function ({ attributes }) {
    const {
        content, 
        blockId,
        selectedCollections,
        arrowsPosition,
        largeArrows,
        arrowsStyle,
        imageSize,
        maxCollectionsPerScreen,
        maxCollectionsNumber,
        spaceBetweenCollections,
        spaceAroundCarousel,
        autoPlay,
        autoPlaySpeed,
        loopSlides,
        hideName,
        showCollectionThumbnail
    } = attributes;

    // Gets attributes such as style, that are automatically added by the editor hook
    const blockProps = useBlockProps.save();
    return <div 
                { ...blockProps }
                data-module="carousel-collections-list"
                data-selected-collections={ JSON.stringify(selectedCollections.map((collection) => { return collection.id })) }
                data-arrows-position={ arrowsPosition }
                data-auto-play={ '' + autoPlay }
                data-auto-play-speed={ autoPlaySpeed }
                data-loop-slides={ '' + loopSlides }
                data-hide-name={ '' + hideName }
                data-large-arrows={ '' + largeArrows }
                data-arrows-style={ arrowsStyle }
                data-image-size={ imageSize }
                data-max-collections-number={ maxCollectionsNumber }
                data-max-collections-per-screen={ maxCollectionsPerScreen }
                data-space-between-collections={ spaceBetweenCollections }
                data-space-around-carousel={ spaceAroundCarousel }
                data-tainacan-api-root={ tainacan_blocks.root }
                data-show-collection-thumbnail={ '' + showCollectionThumbnail }
                id={ 'wp-block-tainacan-carousel-collections-list_' + blockId }>
                    { content }
            </div>
};