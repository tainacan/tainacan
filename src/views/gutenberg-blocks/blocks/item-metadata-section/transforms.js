const { createBlock } = wp.blocks;

export default {
    to: [
        {
            type: 'block',
            blocks: [ 'tainacan/carousel-items-list' ],
            isMultiBlock: true,
            transform: ( itemMetadataBlocks ) => {
                const items = itemMetadataBlocks.map((anItemMetadataBlock) => anItemMetadataBlock.itemId);
                return createBlock(
                    'tainacan/carousel-items-list',
                    {
                        selectedItems: items,
                        loadStrategy: 'selection',
                        content: [ { type: true } ], 
                        collectionId: itemMetadataBlocks[0].collectionId,
                        isModalOpen: false,
                        align: itemMetadataBlocks[0].align,
                        textColor: itemMetadataBlocks[0].textColor,
                        fontSize: itemMetadataBlocks[0].fontSize
                    }
                );
            },
        },
        {
            type: 'block',
            blocks: [ 'tainacan/dynamic-items-list' ],
            isMultiBlock: true,
            transform: ( itemMetadataBlocks ) => {
                const items = itemMetadataBlocks.map((anItemMetadataBlock) => anItemMetadataBlock.itemId);
                return createBlock(
                    'tainacan/dynamic-items-list',
                    {
                        selectedItems: items,
                        loadStrategy: 'selection',
                        content: [ { type: true } ], 
                        collectionId: itemMetadataBlocks[0].collectionId,
                        isModalOpen: false,
                        align: itemMetadataBlocks[0].align,
                        textColor: itemMetadataBlocks[0].textColor,
                        fontSize: itemMetadataBlocks[0].fontSize
                    }
                );
            },
        }
    ]
};