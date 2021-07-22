export default function ({ attributes, className }) {
    const {
        content, 
        blockId,
        selectedCollections,
        arrowsPosition,
        largeArrows,
        cropImagesToSquare,
        maxCollectionsPerScreen,
        maxCollectionsNumber,
        autoPlay,
        autoPlaySpeed,
        loopSlides,
        hideName,
        showCollectionThumbnail
    } = attributes;
    return <div 
                data-module="carousel-collections-list"
                className={ className }
                selected-collections={ JSON.stringify(selectedCollections.map((collection) => { return collection.id })) }
                arrows-position={ arrowsPosition }
                auto-play={ '' + autoPlay }
                auto-play-speed={ autoPlaySpeed }
                loop-slides={ '' + loopSlides }
                hide-name={ '' + hideName }
                large-arrows={ '' + largeArrows }
                crop-images-to-square={ '' + cropImagesToSquare }
                max-collections-number={ maxCollectionsNumber }
                max-collections-per-screen={ maxCollectionsPerScreen }
                tainacan-api-root={ tainacan_blocks.root }
                tainacan-base-url={ tainacan_blocks.base_url }
                show-collection-thumbnail={ '' + showCollectionThumbnail }
                id={ 'wp-block-tainacan-carousel-collections-list_' + blockId }>
                    { content }
            </div>
};