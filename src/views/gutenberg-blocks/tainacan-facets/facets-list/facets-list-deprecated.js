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
            collectionSlug: {
                type: String,
                default: undefined
            },
            facets: {
                type: Array,
                default: []
            },
            facetsObject: {
                type: Array,
                default: []
            },
            showImage: {
                type: Boolean,
                default: true
            },
            showItemsCount: {
                type: Boolean,
                default: true
            },
            showLoadMore: {
                type: Boolean,
                default: false
            },
            showSearchBar: {
                type: Boolean,
                value: false
            },
            layout: {
                type: String,
                default: 'grid'
            },
            cloudRate: {
                type: Number,
                default: 1
            },
            isModalOpen: {
                type: Boolean,
                default: false
            },
            gridMargin: {
                type: Number,
                default: 0
            },
            metadatumId: {
                type: String,
                default: undefined
            },
            metadatumType: {
                type: String,
                default: undefined
            },
            facetsRequestSource: {
                type: String,
                default: undefined
            },
            maxFacetsNumber: {
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
            collection: {
                type: Object,
                value: undefined
            },
            searchString: {
                type: String,
                default: undefined
            },
            blockId: {
                type: String,
                default: undefined
            },
            parentTerm: {
                type: Number,
                default: null
            },
            isParentTermModalOpen: {
                type: Boolean,
                default: false
            }
        },
        save({ attributes, className }){
            const {
                content, 
                blockId,
                collectionId,  
                collectionSlug,
                parentTerm,  
                showImage,
                showItemsCount,
                showLoadMore,
                layout,
                cloudRate,
                gridMargin,
                metadatumId,
                metadatumType,
                maxFacetsNumber,
                showSearchBar,
            } = attributes;
            return <div 
                        className={ className }
                        metadatum-id={ metadatumId }
                        metadatum-type={ metadatumType }
                        collection-id={ collectionId }  
                        collection-slug={ collectionSlug }
                        parent-term-id={ parentTerm ? parentTerm.id : null }  
                        show-image={ '' + showImage }
                        show-items-count={ '' + showItemsCount }
                        show-search-bar={ '' + showSearchBar }
                        show-load-more={ '' + showLoadMore }
                        layout={ layout }
                        cloud-rate={ cloudRate }
                        grid-margin={ gridMargin }
                        max-facets-number={ maxFacetsNumber }
                        tainacan-api-root={ tainacan_blocks.root }
                        tainacan-base-url={ tainacan_blocks.base_url }
                        tainacan-site-url={ tainacan_blocks.site_url }
                        id={ 'wp-block-tainacan-facets-list_' + blockId }>
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
            collectionSlug: {
                type: String,
                default: undefined
            },
            facets: {
                type: Array,
                default: []
            },
            facetsObject: {
                type: Array,
                default: []
            },
            showImage: {
                type: Boolean,
                default: true
            },
            showItemsCount: {
                type: Boolean,
                default: true
            },
            showLoadMore: {
                type: Boolean,
                default: false
            },
            showSearchBar: {
                type: Boolean,
                value: false
            },
            layout: {
                type: String,
                default: 'grid'
            },
            cloudRate: {
                type: Number,
                default: 1
            },
            isModalOpen: {
                type: Boolean,
                default: false
            },
            gridMargin: {
                type: Number,
                default: 0
            },
            metadatumId: {
                type: String,
                default: undefined
            },
            metadatumType: {
                type: String,
                default: undefined
            },
            facetsRequestSource: {
                type: String,
                default: undefined
            },
            maxFacetsNumber: {
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
            collection: {
                type: Object,
                value: undefined
            },
            searchString: {
                type: String,
                default: undefined
            },
            blockId: {
                type: String,
                default: undefined
            },
            parentTerm: {
                type: Number,
                default: null
            },
            isParentTermModalOpen: {
                type: Boolean,
                default: false
            }
        },
        save({ attributes, className }){
            const {
                content, 
                blockId,
                collectionId,  
                collectionSlug,
                parentTerm,  
                showImage,
                showItemsCount,
                showLoadMore,
                layout,
                cloudRate,
                gridMargin,
                metadatumId,
                metadatumType,
                maxFacetsNumber,
                showSearchBar,
            } = attributes;
            return <div 
                        className={ className }
                        metadatum-id={ metadatumId }
                        metadatum-type={ metadatumType }
                        collection-id={ collectionId }  
                        collection-slug={ collectionSlug }
                        parent-term-id={ parentTerm ? parentTerm.id : null }  
                        show-image={ '' + showImage }
                        show-items-count={ '' + showItemsCount }
                        show-search-bar={ '' + showSearchBar }
                        show-load-more={ '' + showLoadMore }
                        layout={ layout }
                        cloud-rate={ cloudRate }
                        grid-margin={ gridMargin }
                        max-facets-number={ maxFacetsNumber }
                        tainacan-base-url={ tainacan_blocks.base_url }
                        id={ 'wp-block-tainacan-facets-list_' + blockId }>
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
            collectionSlug: {
                type: String,
                default: undefined
            },
            facets: {
                type: Array,
                default: []
            },
            facetsObject: {
                type: Array,
                default: []
            },
            showImage: {
                type: Boolean,
                default: true
            },
            showItemsCount: {
                type: Boolean,
                default: true
            },
            showLoadMore: {
                type: Boolean,
                default: false
            },
            showSearchBar: {
                type: Boolean,
                value: false
            },
            layout: {
                type: String,
                default: 'grid'
            },
            cloudRate: {
                type: Number,
                default: 1
            },
            isModalOpen: {
                type: Boolean,
                default: false
            },
            gridMargin: {
                type: Number,
                default: 0
            },
            metadatumId: {
                type: String,
                default: undefined
            },
            metadatumType: {
                type: String,
                default: undefined
            },
            facetsRequestSource: {
                type: String,
                default: undefined
            },
            maxFacetsNumber: {
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
            collection: {
                type: Object,
                value: undefined
            },
            searchString: {
                type: String,
                default: undefined
            },
            blockId: {
                type: String,
                default: undefined
            }
        },
        save({ attributes, className }){
            const {
                content, 
                blockId,
                collectionId,  
                collectionSlug,  
                showImage,
                showItemsCount,
                showLoadMore,
                layout,
                cloudRate,
                gridMargin,
                metadatumId,
                metadatumType,
                maxFacetsNumber,
                showSearchBar,
            } = attributes;
            
            return <div 
                        className={ className }
                        metadatum-id={ metadatumId }
                        metadatum-type={ metadatumType }
                        collection-id={ collectionId }  
                        collection-slug={ collectionSlug }  
                        show-image={ '' + showImage }
                        show-items-count={ '' + showItemsCount }
                        show-search-bar={ '' + showSearchBar }
                        show-load-more={ '' + showLoadMore }
                        layout={ layout }
                        cloud-rate={ cloudRate }
                        grid-margin={ gridMargin }
                        max-facets-number={ maxFacetsNumber }
                        tainacan-api-root={ tainacan_plugin.root }
                        tainacan-base-url={ tainacan_plugin.base_url }
                        tainacan-site-url={ tainacan_plugin.site_url }
                        id={ 'wp-block-tainacan-facets-list_' + blockId }>
                            { content }
                    </div>
        }
    }
    
]