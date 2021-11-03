const { createBlock } = wp.blocks;

export default {
    to: [
        {
            type: 'block',
            blocks: [ 'tainacan/dynamic-items-list' ],
            transform: ( {
                selectedItemsObject, 
                selectedItemsHTML, 
                content, 
                collectionId,  
                showImage,
                showName,
                layout,
                gridMargin,
                align,
                textColor,
                fontSize
            } ) => {
                return createBlock(
                    'tainacan/dynamic-items-list',
                    {
                        items: selectedItemsHTML,
                        content: content, 
                        collection: {},
                        collectionId: collectionId,
                        showImage: showImage,
                        showName: showName,
                        layout: layout,
                        isModalOpen: false,
                        gridMargin: gridMargin,
                        searchURL: '',
                        itemsRequestSource: '',
                        maxItemsNumber: 12,
                        order: '',
                        searchString: '',
                        selectedItems: selectedItemsObject.map((anItemObject) => anItemObject.id.split('item-id-')[1]),
                        isLoading: false,
                        loadStrategy: 'selection',
                        showSearchBar: false,
                        showCollectionHeader: false,
                        showCollectionLabel: true,
                        isLoadingCollection: false,
                        collectionBackgroundColor: '#454647',
                        collectionTextColor: '#ffffff',
                        mosaicHeight: 280,
                        mosaicGridColumns: 3,
                        mosaicGridRows: 3,
                        mosaicItemFocalPoint: {
                            x: 0.5,
                            y: 0.5
                        },
                        sampleBackgroundImage: '',
                        mosaicDensity: 6,
                        maxColumnsCount: 4,
                        cropImagesToSquare: true,
                        align: align,
                        textColor: textColor,
                        fontSize: fontSize
                    }
                );
            },
        },
        {
            type: 'block',
            blocks: [ 'tainacan/carousel-items-list' ],
            transform: ( {
                selectedItemsObject, 
                selectedItemsHTML, 
                collectionId,
                showName,
                gridMargin,
                align,
                textColor,
                fontSize
            } ) => {
                return createBlock(
                    'tainacan/carousel-items-list',
                    {
                        items: selectedItemsHTML,
                        content: <div></div>, 
                        collection: {},
                        collectionId: collectionId,
                        hideTitle: !showName,
                        isModalOpen: false,
                        gridMargin: gridMargin,
                        searchURL: '',
                        itemsRequestSource: '',
                        maxItemsNumber: 12,
                        maxItemsPerScreen: 4,
                        selectedItems: selectedItemsObject.map((anItemObject) => anItemObject.id.split('item-id-')[1]),
                        isLoading: false,
                        loadStrategy: 'selection',
                        arrowsPosition : 'around',
                        spaceBetweenItems: 32,
                        spaceAroundCarousel: 50,
                        largeArrows: false,
                        arrowsStyle : 'type-1',
                        autoPlay: false,
                        autoPlaySpeed: 3,
                        loopSlides: false,
                        showCollectionHeader: false,
                        showCollectionLabel: true,
                        isLoadingCollection: false,
                        collectionBackgroundColor: '#454647',
                        collectionTextColor: '#ffffff',
                        cropImagesToSquare: true,
                        align: align,
                        textColor: textColor,
                        fontSize: fontSize
                    }
                );
            },
        }
    ]
};