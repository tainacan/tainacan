const { createBlock } = wp.blocks;

export default {
    to: [
        {
            type: 'block',
            blocks: [ 'tainacan/carousel-items-list' ],
            transform: ( {
                items,
                collectionId,
                collection,
                showName,
                gridMargin,
                searchURL,
                maxItemsNumber,
                selectedItems,
                loadStrategy,
                showCollectionHeader,
                showCollectionLabel,
                collectionBackgroundColor,
                collectionTextColor,
                maxColumnsCount,
                cropImagesToSquare,
                align,
                textColor,
                fontSize
            } ) => {
                return createBlock(
                    'tainacan/carousel-items-list',
                    {
                        items: items,
                        content: [ { type: true } ], 
                        collection: collection,
                        collectionId: collectionId,
                        hideTitle: !showName,
                        isModalOpen: false,
                        spaceBetweenItems: gridMargin + 16,
                        searchURL: searchURL,
                        itemsRequestSource: '',
                        maxItemsNumber: maxItemsNumber,
                        maxItemsPerScreen: maxColumnsCount,
                        selectedItems: selectedItems,
                        isLoading: false,
                        loadStrategy: loadStrategy,
                        arrowsPosition : 'around',
                        spaceAroundCarousel: 50,
                        largeArrows: false,
                        arrowsStyle : 'type-1',
                        autoPlay: false,
                        autoPlaySpeed: 3,
                        loopSlides: false,
                        showCollectionHeader: showCollectionHeader,
                        showCollectionLabel: showCollectionLabel,
                        isLoadingCollection: false,
                        collectionBackgroundColor: collectionBackgroundColor,
                        collectionTextColor: collectionTextColor,
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