const { useBlockProps } = (tainacan_blocks.wp_version < '5.2' ? wp.editor : wp.blockEditor );

export default function ({ attributes, className }) {
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
    const blockProps = tainacan_blocks.wp_version < '5.6' ? { className: className } : useBlockProps.save();
    return <div 
                { ...blockProps }
                data-module="carousel-items-list"
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
                arrows-style={ arrowsStyle }
                image-size={ imageSize }
                show-collection-header={ '' + showCollectionHeader }
                show-collection-label={ '' + showCollectionLabel }
                collection-background-color={ collectionBackgroundColor }
                collection-text-color={ collectionTextColor }
                max-items-number={ maxItemsNumber }
                max-items-per-screen={ maxItemsPerScreen }
                space-between-items={ spaceBetweenItems }
                space-around-carousel={ spaceAroundCarousel }
                tainacan-api-root={ tainacan_blocks.root }
                tainacan-base-url={ tainacan_blocks.base_url }
                id={ 'wp-block-tainacan-carousel-items-list_' + blockId }>
                    { content }
            </div>
};