export default function ({ attributes, className }) {
    const {
        content, 
        blockId,
        collectionId,  
        searchURL,
        selectedItems,
        arrowsPosition,
        largeArrows,
        loadStrategy,
        maxItemsNumber,
        maxItemsPerScreen,
        autoPlay,
        autoPlaySpeed,
        loopSlides,
        hideTitle,
        cropImagesToSquare,
        showCollectionHeader,
        showCollectionLabel,
        collectionBackgroundColor,
        collectionTextColor
    } = attributes;
    
    return <div 
                className={ className }
                search-url={ searchURL }
                selected-items={ JSON.stringify(selectedItems) }
                arrows-position={ arrowsPosition }
                load-strategy={ loadStrategy }
                collection-id={ collectionId }  
                auto-play={ '' + autoPlay }
                auto-play-speed={ autoPlaySpeed }
                loop-slides={ '' + loopSlides }
                hide-title={ '' + hideTitle }
                large-arrows={ '' + largeArrows }
                crop-images-to-square={ '' + cropImagesToSquare }
                show-collection-header={ '' + showCollectionHeader }
                show-collection-label={ '' + showCollectionLabel }
                collection-background-color={ collectionBackgroundColor }
                collection-text-color={ collectionTextColor }
                max-items-number={ maxItemsNumber }
                max-items-per-screen={ maxItemsPerScreen }
                tainacan-api-root={ tainacan_blocks.root }
                tainacan-base-url={ tainacan_blocks.base_url }
                id={ 'wp-block-tainacan-carousel-items-list_' + blockId }>
                    { content }
            </div>
};