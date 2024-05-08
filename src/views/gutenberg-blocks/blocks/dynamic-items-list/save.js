const { useBlockProps } = wp.blockEditor;

export default function({ attributes }) {
    const {
        content, 
        blockId,
        collectionId,
        loadStrategy,
        selectedItems,
        showImage,
        showName,
        layout,
        gridMargin,
        searchURL,
        maxItemsNumber,
        order,
        orderBy,
        orderByMetaKey,
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
        imageSize,
        tainacanViewMode,
        displayedMetadata
    } = attributes;
    
    // Gets attributes such as style, that are automatically added by the editor hook
    const blockProps = useBlockProps.save();
    return <div
                { ...blockProps }
                data-module="dynamic-items-list"
                data-search-url={ searchURL }
                data-selected-items={ JSON.stringify(selectedItems) }
                data-collection-id={ collectionId }
                data-show-image={ '' + showImage }
                data-show-name={ '' + showName }
                data-show-search-bar={ '' + showSearchBar }
                data-show-collection-header={ '' + showCollectionHeader }
                data-show-collection-label={ '' + showCollectionLabel }
                data-image-size={ imageSize }
                data-layout={ layout }
                data-load-strategy={ loadStrategy }
                data-mosaic-height={ mosaicHeight }
                data-mosaic-density={ mosaicDensity }
                data-mosaic-grid-rows={ mosaicGridRows } 
                data-mosaic-grid-columns={ mosaicGridColumns }
                data-mosaic-item-focal-point-x={ (mosaicItemFocalPoint && mosaicItemFocalPoint.x ? mosaicItemFocalPoint.x : 0.5) } 
                data-mosaic-item-focal-point-y={ (mosaicItemFocalPoint && mosaicItemFocalPoint.y ? mosaicItemFocalPoint.y : 0.5) } 
                data-max-columns-count={ maxColumnsCount }
                data-collection-background-color={ collectionBackgroundColor }
                data-collection-text-color={ collectionTextColor }
                data-grid-margin={ gridMargin }
                data-max-items-number={ maxItemsNumber }
                data-order={ order !== undefined ? order : '' }
                data-order-by={ orderBy !== undefined ? orderBy : 'date' }
                data-order-by-meta-key={ orderByMetaKey !== undefined ? orderByMetaKey : '' }
                data-tainacan-view-mode={ tainacanViewMode }
                data-tainacan-api-root={ tainacan_blocks.root }
                id={ 'wp-block-tainacan-dynamic-items-list_' + blockId }>
                    { content }
            </div>
};