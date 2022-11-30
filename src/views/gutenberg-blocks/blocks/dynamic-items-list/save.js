const { useBlockProps } = (tainacan_blocks.wp_version < '5.2' ? wp.editor : wp.blockEditor );

export default function({ attributes, className }) {
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
        imageSize
    } = attributes;
    
    // Gets attributes such as style, that are automatically added by the editor hook
    const blockProps = tainacan_blocks.wp_version < '5.6' ? { className: className } : useBlockProps.save();
    return <div
                { ...blockProps }
                data-module="dynamic-items-list"
                search-url={ searchURL }
                selected-items={ JSON.stringify(selectedItems) }
                collection-id={ collectionId }
                show-image={ '' + showImage }
                show-name={ '' + showName }
                show-search-bar={ '' + showSearchBar }
                show-collection-header={ '' + showCollectionHeader }
                show-collection-label={ '' + showCollectionLabel }
                image-size={ imageSize }
                layout={ layout }
                load-strategy={ loadStrategy }
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
                orderBy={ orderBy }
                orderByMetaKey={ orderByMetaKey }
                tainacan-api-root={ tainacan_blocks.root }
                tainacan-base-url={ tainacan_blocks.base_url }
                id={ 'wp-block-tainacan-dynamic-items-list_' + blockId }>
                    { content }
            </div>
};