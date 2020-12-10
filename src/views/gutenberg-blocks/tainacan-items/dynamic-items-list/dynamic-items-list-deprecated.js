export default [
    /* Deprecated on Tainacan 0.17.2, due to the introduction of support: fontSize */
    {
        attributes: {
            content: {
                type: 'array',
                source: 'children',
                selector: 'div'
            },
            collectionId: {
                type: String,
                default: undefined
            },
            items: {
                type: Array,
                default: []
            },
            showImage: {
                type: Boolean,
                default: true
            },
            showName: {
                type: Boolean,
                default: true
            },
            layout: {
                type: String,
                default: 'grid'
            },
            isModalOpen: {
                type: Boolean,
                default: false
            },
            gridMargin: {
                type: Number,
                default: 0
            },
            searchURL: {
                type: String,
                default: undefined
            },
            itemsRequestSource: {
                type: String,
                default: undefined
            },
            maxItemsNumber: {
                type: Number,
                value: undefined
            },
            isLoading: {
                type: Boolean,
                value: false
            },
            isLoadingCollection: {
                type: Boolean,
                value: false
            },
            showSearchBar: {
                type: Boolean,
                value: false
            },
            showCollectionHeader: {
                type: Boolean,
                value: false
            },
            showCollectionLabel: {
                type: Boolean,
                value: false
            },
            collection: {
                type: Object,
                value: undefined
            },
            searchString: {
                type: String,
                default: undefined
            },
            order: {
                type: String,
                default: undefined
            },
            blockId: {
                type: String,
                default: undefined
            },
            collectionBackgroundColor: {
                type: String,
                default: "#454647"
            },
            collectionTextColor: {
                type: String,
                default: "#ffffff"
            },
            mosaicHeight: {
                type: Number,
                value: 280
            },
            mosaicGridColumns: {
                type: Number,
                value: 3
            },
            mosaicGridRows: {
                type: Number,
                value: 3
            },
            sampleBackgroundImage: {
                type: String,
                default: ''
            },
            osaicItemFocalPointm: {
                type: Object,
                default: {
                    x: 0.5,
                    y: 0.5
                }
            },
            mosaicDensity: {
                type: Number,
                default: 5
            }
        },
        supports: {
            align: ['full', 'wide'],
            html: false,
        },
        save({ attributes, className }) {
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
                mosaicDensity
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
                        layout={ layout }
                        mosaic-height={ mosaicHeight }
                        mosaic-density={ mosaicDensity }
                        mosaic-grid-rows={ mosaicGridRows } 
                        mosaic-grid-columns={ mosaicGridColumns }
                        mosaic-item-focal-point-x={ (mosaicItemFocalPoint && mosaicItemFocalPoint.x ? mosaicItemFocalPoint.x : 0.5) } 
                        mosaic-item-focal-point-y={ (mosaicItemFocalPoint && mosaicItemFocalPoint.y ? mosaicItemFocalPoint.y : 0.5) } 
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
        }
    },
    {
        attributes: {
            content: {
                type: 'array',
                source: 'children',
                selector: 'div'
            },
            collectionId: {
                type: String,
                default: undefined
            },
            items: {
                type: Array,
                default: []
            },
            showImage: {
                type: Boolean,
                default: true
            },
            showName: {
                type: Boolean,
                default: true
            },
            layout: {
                type: String,
                default: 'grid'
            },
            isModalOpen: {
                type: Boolean,
                default: false
            },
            gridMargin: {
                type: Number,
                default: 0
            },
            searchURL: {
                type: String,
                default: undefined
            },
            itemsRequestSource: {
                type: String,
                default: undefined
            },
            maxItemsNumber: {
                type: Number,
                value: undefined
            },
            isLoading: {
                type: Boolean,
                value: false
            },
            isLoadingCollection: {
                type: Boolean,
                value: false
            },
            showSearchBar: {
                type: Boolean,
                value: false
            },
            showCollectionHeader: {
                type: Boolean,
                value: false
            },
            showCollectionLabel: {
                type: Boolean,
                value: false
            },
            collection: {
                type: Object,
                value: undefined
            },
            searchString: {
                type: String,
                default: undefined
            },
            order: {
                type: String,
                default: undefined
            },
            blockId: {
                type: String,
                default: undefined
            },
            collectionBackgroundColor: {
                type: String,
                default: "#454647"
            },
            collectionTextColor: {
                type: String,
                default: "#ffffff"
            }
        },
        save({ attributes, className }){
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
                collectionTextColor
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
                        layout={ layout }
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
        }
    }
]