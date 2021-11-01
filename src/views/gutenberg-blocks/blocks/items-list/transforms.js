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
                gridMargin
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
                        selectedItems: selectedItemsObject,
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
                        cropImagesToSquare: true
                    }
                );
            },
        },
    ]
};