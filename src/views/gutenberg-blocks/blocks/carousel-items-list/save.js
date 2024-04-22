const { useBlockProps } = wp.blockEditor;

export default function ({ attributes }) {
    const {
        content, 
        blockId,
        collectionId,  
        searchURL,
        selectedItems,
        arrowsPosition,
        largeArrows,
        arrowsStyle,
        loadStrategy,
        maxItemsNumber,
        maxItemsPerScreen,
        spaceBetweenItems,
        spaceAroundCarousel,
        autoPlay,
        autoPlaySpeed,
        loopSlides,
        hideTitle,
        imageSize,
        showCollectionHeader,
        showCollectionLabel,
        collectionBackgroundColor,
        collectionTextColor
    } = attributes;
    
    // Gets attributes such as style, that are automatically added by the editor hook
    const blockProps = useBlockProps.save();

    return <div 
                { ...blockProps }
                data-module="carousel-items-list"
                data-search-url={ searchURL }
                data-selected-items={ JSON.stringify(selectedItems) }
                data-arrows-position={ arrowsPosition }
                data-load-strategy={ loadStrategy }
                data-collection-id={ collectionId }  
                data-auto-play={ '' + autoPlay }
                data-auto-play-speed={ autoPlaySpeed }
                data-loop-slides={ '' + loopSlides }
                data-hide-title={ '' + hideTitle }
                data-large-arrows={ '' + largeArrows }
                data-arrows-style={ arrowsStyle }
                data-image-size={ imageSize }
                data-show-collection-header={ '' + showCollectionHeader }
                data-show-collection-label={ '' + showCollectionLabel }
                data-collection-background-color={ collectionBackgroundColor }
                data-collection-text-color={ collectionTextColor }
                data-max-items-number={ maxItemsNumber }
                data-max-items-per-screen={ maxItemsPerScreen }
                data-space-between-items={ spaceBetweenItems }
                data-space-around-carousel={ spaceAroundCarousel }
                data-tainacan-api-root={ tainacan_blocks.root }
                id={ 'wp-block-tainacan-carousel-items-list_' + blockId }>
                    { content }
            </div>
};