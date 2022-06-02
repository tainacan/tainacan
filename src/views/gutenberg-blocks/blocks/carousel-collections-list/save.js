const { useBlockProps } = (tainacan_blocks.wp_version < '5.2' ? wp.editor : wp.blockEditor );

export default function ({ attributes, className }) {
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
    const blockProps = tainacan_blocks.wp_version < '5.6' ? { className: className } : useBlockProps.save();
    return <div 
                { ...blockProps }
                data-module="carousel-collections-list"
                selected-collections={ JSON.stringify(selectedCollections.map((collection) => { return collection.id })) }
                arrows-position={ arrowsPosition }
                auto-play={ '' + autoPlay }
                auto-play-speed={ autoPlaySpeed }
                loop-slides={ '' + loopSlides }
                hide-name={ '' + hideName }
                large-arrows={ '' + largeArrows }
                arrows-style={ arrowsStyle }
                image-size={ imageSize }
                max-collections-number={ maxCollectionsNumber }
                max-collections-per-screen={ maxCollectionsPerScreen }
                space-between-collections={ spaceBetweenCollections }
                space-around-carousel={ spaceAroundCarousel }
                tainacan-api-root={ tainacan_blocks.root }
                tainacan-base-url={ tainacan_blocks.base_url }
                show-collection-thumbnail={ '' + showCollectionThumbnail }
                id={ 'wp-block-tainacan-carousel-collections-list_' + blockId }>
                    { content }
            </div>
};