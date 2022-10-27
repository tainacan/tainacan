const { createBlock } = wp.blocks;

export default {
    to: [
        {
            type: 'block',
            blocks: [ 'tainacan/dynamic-items-list' ],
            transform: ( {
                items,
                collectionId,
                searchURL,
                maxItemsNumber,
                maxItemsPerScreen,
                spaceBetweenItems,
                selectedItems,
                loadStrategy,
                hideTitle,
                cropImagesToSquare,
                showCollectionHeader,
                showCollectionLabel,
                collection,
                collectionBackgroundColor,
                collectionTextColor,
                align,
                textColor,
                fontSize
            } ) => {
                return createBlock(
                    'tainacan/dynamic-items-list',
                    {
                        items: items,
                        content: [ { type: true } ], 
                        collection: collection,
                        collectionId: collectionId,
                        showImage: true,
                        showName: !hideTitle,
                        layout: 'grid',
                        isModalOpen: false,
                        gridMargin: spaceBetweenItems,
                        searchURL: searchURL,
                        itemsRequestSource: '',
                        maxItemsNumber: maxItemsNumber,
                        order: '',
                        searchString: '',
                        selectedItems: selectedItems,
                        isLoading: false,
                        loadStrategy: loadStrategy,
                        showSearchBar: false,
                        showCollectionHeader: showCollectionHeader,
                        showCollectionLabel: showCollectionLabel,
                        isLoadingCollection: false,
                        collectionBackgroundColor: collectionBackgroundColor,
                        collectionTextColor: collectionTextColor,
                        mosaicHeight: 280,
                        mosaicGridColumns: maxItemsPerScreen,
                        mosaicGridRows: 3,
                        mosaicItemFocalPoint: {
                            x: 0.5,
                            y: 0.5
                        },
                        sampleBackgroundImage: '',
                        mosaicDensity: 6,
                        maxColumnsCount: 4,
                        cropImagesToSquare: cropImagesToSquare,
                        align: align,
                        textColor: textColor,
                        fontSize: fontSize
                    }
                );
            },
        },
        {
            type: 'block',
            blocks: [ 'tainacan/item-metadata' ],
            isMatch: ({ items } ) => {
                return Array.isArray(items) && items.length && items[0].key;
            },
            transform: ( {
                items,
                collectionId,
                align,
                textColor,
                fontSize
            } ) => {
                const itemMetadataBlocks = items.map((item) => 
                    createBlock(
                        'tainacan/item-metadata',
                        {
                            itemId: Number(item.key),
                            content: [ { type: true } ], 
                            collectionId: Number(collectionId),
                            isModalOpen: false,
                            align: align,
                            textColor: textColor,
                            fontSize: fontSize
                        }
                    )
                );
                return itemMetadataBlocks;
            }
        }
    ]
};