export default function({ attributes, className }) {
    const {
        content, 
        blockId,
        collectionId,  
        showImage,
        showName,
        layout,
        gridMargin,
        searchURL,
        maxItemsNumber,
        order,
        showSearchBar,
        showCollectionHeader,
        showCollectionLabel,
        collectionBackgroundColor,
        collectionTextColor,
        mosaicHeight,
        mosaicGridRows,
        mosaicGridColumns,
        mosaicItemFocalPoint,
        mosaicDensity,
        maxColumnsCount,
        cropImagesToSquare
    } = attributes;

    return <div 
                search-url={ searchURL }
                className={ className }
                collection-id={ collectionId }  
                show-image={ '' + showImage }
                show-name={ '' + showName }
                show-search-bar={ '' + showSearchBar }
                show-collection-header={ '' + showCollectionHeader }
                show-collection-label={ '' + showCollectionLabel }
                crop-images-to-square={ '' + cropImagesToSquare }
                layout={ layout }
                mosaic-height={ mosaicHeight }
                mosaic-density={ mosaicDensity }
                mosaic-grid-rows={ mosaicGridRows } 
                mosaic-grid-columns={ mosaicGridColumns }
                mosaic-item-focal-point-x={ (mosaicItemFocalPoint && mosaicItemFocalPoint.x ? mosaicItemFocalPoint.x : 0.5) } 
                mosaic-item-focal-point-y={ (mosaicItemFocalPoint && mosaicItemFocalPoint.y ? mosaicItemFocalPoint.y : 0.5) } 
                max-columns-count={ maxColumnsCount }
                collection-background-color={ collectionBackgroundColor }
                collection-text-color={ collectionTextColor }
                grid-margin={ gridMargin }
                max-items-number={ maxItemsNumber }
                order={ order }
                tainacan-api-root={ tainacan_blocks.root }
                tainacan-base-url={ tainacan_blocks.base_url }
                id={ 'wp-block-tainacan-dynamic-items-list_' + blockId }>
                    { content }
            </div>
};