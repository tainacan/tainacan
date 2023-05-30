const { useBlockProps } = (tainacan_blocks.wp_version < '5.2' ? wp.editor : wp.blockEditor );

export default [
    /* Deprecated on Tainacan 0.20.1 to add imageSize */
    {
        "attributes": {
            "content": {
                "type": "Array",
                "source": "children",
                "selector": "div"
            },
            "collectionId": {
                "type": "String",
                "default": ""
            },
            "collectionSlug": {
                "type": "String",
                "default": ""
            },
            "facets": {
                "type": "Array",
                "default": []
            },
            "facetsObject": {
                "type": "Array",
                "default": []
            },
            "showImage": {
                "type": "Boolean",
                "default": true
            },
            "nameInsideImage": {
                "type": "Boolean",
                "default": false
            },
            "showItemsCount": {
                "type": "Boolean",
                "default": true
            },
            "showLoadMore": {
                "type": "Boolean",
                "default": false
            },
            "showSearchBar": {
                "type": "Boolean",
                "value": false
            },
            "layout": {
                "type": "String",
                "default": "grid"
            },
            "cloudRate": {
                "type": "Number",
                "default": 1
            },
            "isModalOpen": {
                "type": "Boolean",
                "default": false
            },
            "gridMargin": {
                "type": "Number",
                "default": 24
            },
            "metadatumId": {
                "type": "String",
                "default": ""
            },
            "metadatumType": {
                "type": "String",
                "default": ""
            },
            "facetsRequestSource": {
                "type": "String",
                "default": ""
            },
            "maxFacetsNumber": {
                "type": "Number",
                "value": 12
            },
            "isLoading": {
                "type": "Boolean",
                "value": false
            },
            "isLoadingCollection": {
                "type": "Boolean",
                "value": false
            },
            "collection": {
                "type": "Object",
                "value": {}
            },
            "searchString": {
                "type": "String",
                "default": ""
            },
            "blockId": {
                "type": "String",
                "default": ""
            },
            "parentTerm": {
                "type": "Number",
                "default": null
            },
            "isParentTermModalOpen": {
                "type": "Boolean",
                "default": false
            },
            "maxColumnsCount": {
                "type": "Number",
                "default": 5
            },
            "appendChildTerms": {
                "type": "Boolean",
                "default": false
            },
            "childFacetsObject": {
                "type": "Object",
                "default": {}
            },
            "linkTermFacetsToTermPage": {
                "type": "Boolean",
                "default": true
            },
            "isLoadingChildTerms": {
                "type": "Number",
                "default": null
            },
            "itemsCountStyle": {
                "type": "String",
                "default": "default"
            }
        },
        save({ attributes, className }) {
            const {
                content, 
                blockId,
                collectionId,  
                collectionSlug,
                parentTerm,  
                showImage,
                nameInsideImage,
                showItemsCount,
                showLoadMore,
                layout,
                cloudRate,
                gridMargin,
                metadatumId,
                metadatumType,
                maxFacetsNumber,
                maxColumnsCount,
                showSearchBar,
                linkTermFacetsToTermPage,
                appendChildTerms,
                itemsCountStyle
            } = attributes;
        
            // Gets attributes such as style, that are automatically added by the editor hook
            const blockProps = tainacan_blocks.wp_version < '5.6' ? { className: className } : useBlockProps.save();
            return <div 
                        { ...blockProps }
                        data-module="facets-list"
                        metadatum-id={ metadatumId }
                        metadatum-type={ metadatumType }
                        collection-id={ collectionId }  
                        collection-slug={ collectionSlug }
                        parent-term-id={ parentTerm ? parentTerm.id : undefined }  
                        show-image={ '' + showImage }
                        name-inside-image={ nameInsideImage === true ? 'true' : 'false' }
                        show-items-count={ '' + showItemsCount }
                        show-search-bar={ '' + showSearchBar }
                        show-load-more={ '' + showLoadMore }
                        append-child-terms={ (appendChildTerms === true ? 'true' : 'false') }
                        link-term-facets-to-term-page={ linkTermFacetsToTermPage === false ? 'false' : 'true' }
                        layout={ layout }
                        items-count-style={ itemsCountStyle }
                        cloud-rate={ cloudRate }
                        grid-margin={ gridMargin }
                        max-facets-number={ maxFacetsNumber }
                        max-columns-count={ maxColumnsCount }
                        tainacan-api-root={ tainacan_blocks.root }
                        tainacan-base-url={ tainacan_blocks.base_url }
                        tainacan-site-url={ tainacan_blocks.site_url }
                        id={ 'wp-block-tainacan-facets-list_' + blockId }>
                            { content }
                    </div>
        }
    },
    /* Deprecated on Tainacan 0.18.6 due to itemsCountStyle */
    {
        "attributes": {
            "content": {
                "type": "Array",
                "source": "children",
                "selector": "div"
            },
            "collectionId": {
                "type": "String",
                "default": ""
            },
            "collectionSlug": {
                "type": "String",
                "default": ""
            },
            "facets": {
                "type": "Array",
                "default": []
            },
            "facetsObject": {
                "type": "Array",
                "default": []
            },
            "showImage": {
                "type": "Boolean",
                "default": true
            },
            "nameInsideImage": {
                "type": "Boolean",
                "default": false
            },
            "showItemsCount": {
                "type": "Boolean",
                "default": true
            },
            "showLoadMore": {
                "type": "Boolean",
                "default": false
            },
            "showSearchBar": {
                "type": "Boolean",
                "value": false
            },
            "layout": {
                "type": "String",
                "default": "grid"
            },
            "cloudRate": {
                "type": "Number",
                "default": 1
            },
            "isModalOpen": {
                "type": "Boolean",
                "default": false
            },
            "gridMargin": {
                "type": "Number",
                "default": 24
            },
            "metadatumId": {
                "type": "String",
                "default": ""
            },
            "metadatumType": {
                "type": "String",
                "default": ""
            },
            "facetsRequestSource": {
                "type": "String",
                "default": ""
            },
            "maxFacetsNumber": {
                "type": "Number",
                "value": 12
            },
            "isLoading": {
                "type": "Boolean",
                "value": false
            },
            "isLoadingCollection": {
                "type": "Boolean",
                "value": false
            },
            "collection": {
                "type": "Object",
                "value": {}
            },
            "searchString": {
                "type": "String",
                "default": ""
            },
            "blockId": {
                "type": "String",
                "default": ""
            },
            "parentTerm": {
                "type": "Number",
                "default": null
            },
            "isParentTermModalOpen": {
                "type": "Boolean",
                "default": false
            },
            "maxColumnsCount": {
                "type": "Number",
                "default": 5
            },
            "appendChildTerms": {
                "type": "Boolean",
                "default": false
            },
            "childFacetsObject": {
                "type": "Object",
                "default": {}
            },
            "linkTermFacetsToTermPage": {
                "type": "Boolean",
                "default": true
            },
            "isLoadingChildTerms": {
                "type": "Number",
                "default": null
            }
        },
        "supports": {
            "align": ["full", "wide"],
            "html": false,
            "typography": {
                "fontSize": true
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
                nameInsideImage,
                showItemsCount,
                showLoadMore,
                layout,
                cloudRate,
                gridMargin,
                metadatumId,
                metadatumType,
                maxFacetsNumber,
                maxColumnsCount,
                showSearchBar,
                linkTermFacetsToTermPage,
                appendChildTerms
            } = attributes;
            return <div 
                        data-module="facets-list"
                        className={ className }
                        metadatum-id={ metadatumId }
                        metadatum-type={ metadatumType }
                        collection-id={ collectionId }  
                        collection-slug={ collectionSlug }
                        parent-term-id={ parentTerm ? parentTerm.id : undefined }  
                        show-image={ '' + showImage }
                        name-inside-image={ nameInsideImage === true ? 'true' : 'false' }
                        show-items-count={ '' + showItemsCount }
                        show-search-bar={ '' + showSearchBar }
                        show-load-more={ '' + showLoadMore }
                        append-child-terms={ (appendChildTerms === true ? 'true' : 'false') }
                        link-term-facets-to-term-page={ linkTermFacetsToTermPage === false ? 'false' : 'true' }
                        layout={ layout }
                        cloud-rate={ cloudRate }
                        grid-margin={ gridMargin }
                        max-facets-number={ maxFacetsNumber }
                        max-columns-count={ maxColumnsCount }
                        tainacan-api-root={ tainacan_blocks.root }
                        tainacan-base-url={ tainacan_blocks.base_url }
                        tainacan-site-url={ tainacan_blocks.site_url }
                        id={ 'wp-block-tainacan-facets-list_' + blockId }>
                            { content }
                    </div>
        }
    },
    /* Deprecated on Tainacan 0.18.4 due to new block.json strategy */
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
            nameInsideImage: {
                type: Boolean,
                default: false
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
                default: 24
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
            },
            maxColumnsCount: {
                type: Number,
                default: 5
            },
            appendChildTerms: {
                type: Boolean,
                default: false
            },
            childFacetsObject: {
                type: Object,
                default: {}
            },
            linkTermFacetsToTermPage: {
                type: Boolean,
                default: true
            },
            isLoadingChildTerms: {
                type: Number,
                default: null
            }
        },
        supports: {
            align: ['full', 'wide'],
            html: false,
            fontSize: true
        },
        save({ attributes, className }){
            const {
                content, 
                blockId,
                collectionId,  
                collectionSlug,
                parentTerm,  
                showImage,
                nameInsideImage,
                showItemsCount,
                showLoadMore,
                layout,
                cloudRate,
                gridMargin,
                metadatumId,
                metadatumType,
                maxFacetsNumber,
                maxColumnsCount,
                showSearchBar,
                linkTermFacetsToTermPage,
                appendChildTerms
            } = attributes;
            return <div 
                        className={ className }
                        metadatum-id={ metadatumId }
                        metadatum-type={ metadatumType }
                        collection-id={ collectionId }  
                        collection-slug={ collectionSlug }
                        parent-term-id={ parentTerm ? parentTerm.id : undefined }  
                        show-image={ '' + showImage }
                        name-inside-image={ nameInsideImage === true ? 'true' : 'false' }
                        show-items-count={ '' + showItemsCount }
                        show-search-bar={ '' + showSearchBar }
                        show-load-more={ '' + showLoadMore }
                        append-child-terms={ (appendChildTerms === true ? 'true' : 'false') }
                        link-term-facets-to-term-page={ linkTermFacetsToTermPage === false ? 'false' : 'true' }
                        layout={ layout }
                        cloud-rate={ cloudRate }
                        grid-margin={ gridMargin }
                        max-facets-number={ maxFacetsNumber }
                        max-columns-count={ maxColumnsCount }
                        tainacan-api-root={ tainacan_blocks.root }
                        tainacan-base-url={ tainacan_blocks.base_url }
                        tainacan-site-url={ tainacan_blocks.site_url }
                        id={ 'wp-block-tainacan-facets-list_' + blockId }>
                            { content }
                    </div>
        }
    },
    /* Deprecated on Tainacan 0.17.2, due to the introduction of support: fontSize, columns count and append child term options */
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
        supports: {
            align: ['full', 'wide'],
            html: false,
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