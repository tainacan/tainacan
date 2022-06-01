const { useBlockProps } = (tainacan_blocks.wp_version < '5.2' ? wp.editor : wp.blockEditor );

export default [
    /* Deprecated on 0.19 to replace cropImagesToSquare by imageSize feature */
    {
        migrate( attributes ) {
            if (attributes.cropImagesToSquare == true)
                attributes.imageSize = 'tainacan-medium';
            else
                attributes.imageSize = 'tainacan-medium-full';
                
            return attributes;
        },
        attributes: {
            "content": {
                "type": "Array",
                "source": "children",
                "selector": "div"
            },
            "collectionId": {
                "type": "String",
                "default": ""
            },
            "items": {
                "type": "Array",
                "default": []
            },
            "showImage": {
                "type": "Boolean",
                "default": true
            },
            "showName": {
                "type": "Boolean",
                "default": true
            },
            "layout": {
                "type": "String",
                "default": "grid"
            },
            "isModalOpen": {
                "type": "Boolean",
                "default": false
            },
            "gridMargin": {
                "type": "Number",
                "default": 0
            },
            "searchURL": {
                "type": "String",
                "default": ""
            },
            "itemsRequestSource": {
                "type": "String",
                "default": ""
            },
            "maxItemsNumber": {
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
            "showSearchBar": {
                "type": "Boolean",
                "value": false
            },
            "showCollectionHeader": {
                "type": "Boolean",
                "value": false
            },
            "showCollectionLabel": {
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
            "selectedItems": {
                "type": "Array",
                "default": []
            },
            "loadStrategy": {
                "type": "String",
                "value": "search"
            },
            "order": {
                "type": "String",
                "default": ""
            },
            "blockId": {
                "type": "String",
                "default": ""
            },
            "collectionBackgroundColor": {
                "type": "String",
                "default": "#454647"
            },
            "collectionTextColor": {
                "type": "String",
                "default": "#ffffff"
            },
            "mosaicHeight": {
                "type": "Number",
                "value": 280
            },
            "mosaicGridColumns": {
                "type": "Number",
                "value": 3
            },
            "mosaicGridRows": {
                "type": "Number",
                "value": 3
            },
            "sampleBackgroundImage": {
                "type": "String",
                "default": ""
            },
            "mosaicItemFocalPoint": {
                "type": "Object",
                "default": {
                    "x": 0.5,
                    "y": 0.5
                }
            },
            "mosaicDensity": {
                "type": "Number",
                "default": 5
            },
            "maxColumnsCount": {
                "type": "Number",
                "default": 4
            },
            "cropImagesToSquare": {
                "type": "Boolean",
                "value": true
            }
        },
        save: function({ attributes, className }) {
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
                cropImagesToSquare
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
                        crop-images-to-square={ '' + cropImagesToSquare }
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
                        tainacan-api-root={ tainacan_blocks.root }
                        tainacan-base-url={ tainacan_blocks.base_url }
                        id={ 'wp-block-tainacan-dynamic-items-list_' + blockId }>
                            { content }
                    </div>
        }
    },
    /* Deprecated to fix the selection strategy on 0.18.7 */
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
            "items": {
                "type": "Array",
                "default": []
            },
            "showImage": {
                "type": "Boolean",
                "default": true
            },
            "showName": {
                "type": "Boolean",
                "default": true
            },
            "layout": {
                "type": "String",
                "default": "grid"
            },
            "isModalOpen": {
                "type": "Boolean",
                "default": false
            },
            "gridMargin": {
                "type": "Number",
                "default": 0
            },
            "searchURL": {
                "type": "String",
                "default": ""
            },
            "itemsRequestSource": {
                "type": "String",
                "default": ""
            },
            "maxItemsNumber": {
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
            "showSearchBar": {
                "type": "Boolean",
                "value": false
            },
            "showCollectionHeader": {
                "type": "Boolean",
                "value": false
            },
            "showCollectionLabel": {
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
            "selectedItems": {
                "type": "Array",
                "default": []
            },
            "loadStrategy": {
                "type": "String",
                "value": "search"
            },
            "order": {
                "type": "String",
                "default": ""
            },
            "blockId": {
                "type": "String",
                "default": ""
            },
            "collectionBackgroundColor": {
                "type": "String",
                "default": "#454647"
            },
            "collectionTextColor": {
                "type": "String",
                "default": "#ffffff"
            },
            "mosaicHeight": {
                "type": "Number",
                "value": 280
            },
            "mosaicGridColumns": {
                "type": "Number",
                "value": 3
            },
            "mosaicGridRows": {
                "type": "Number",
                "value": 3
            },
            "sampleBackgroundImage": {
                "type": "String",
                "default": ""
            },
            "mosaicItemFocalPoint": {
                "type": "Object",
                "default": {
                    "x": 0.5,
                    "y": 0.5
                }
            },
            "mosaicDensity": {
                "type": "Number",
                "default": 5
            },
            "maxColumnsCount": {
                "type": "Number",
                "default": 4
            },
            "cropImagesToSquare": {
                "type": "Boolean",
                "value": true
            }
        },
        "supports": {
            "align": ["full", "wide"],
            "html": false,
            "typography": {
                "fontSize": true
            },
            "color": {
                "text": true,
                "background": false,
                "gradients": false,
                "link": true
            }
        },
        save: function({ attributes, className }) {
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
                mosaicDensity,
                maxColumnsCount,
                cropImagesToSquare
            } = attributes;
        
            // Gets attributes such as style, that are automatically added by the editor hook
            const blockProps = tainacan_blocks.wp_version < '5.6' ? { className: className } : useBlockProps.save();
            return <div
                        { ...blockProps }
                        data-module="dynamic-items-list"
                        search-url={ searchURL }
                        collection-id={ collectionId }
                        show-image={ '' + showImage }
                        show-name={ '' + showName }
                        show-search-bar={ '' + showSearchBar }
                        show-collection-header={ '' + showCollectionHeader }
                        show-collection-label={ '' + showCollectionLabel }
                        crop-images-to-square={ '' + cropImagesToSquare }
                        layout={ layout }
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
                        tainacan-api-root={ tainacan_blocks.root }
                        tainacan-base-url={ tainacan_blocks.base_url }
                        id={ 'wp-block-tainacan-dynamic-items-list_' + blockId }>
                            { content }
                    </div>
        }
    },
    /* Deprecated when new selection strategy was added */
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
            "items": {
                "type": "Array",
                "default": []
            },
            "showImage": {
                "type": "Boolean",
                "default": true
            },
            "showName": {
                "type": "Boolean",
                "default": true
            },
            "layout": {
                "type": "String",
                "default": "grid"
            },
            "isModalOpen": {
                "type": "Boolean",
                "default": false
            },
            "gridMargin": {
                "type": "Number",
                "default": 0
            },
            "searchURL": {
                "type": "String",
                "default": ""
            },
            "itemsRequestSource": {
                "type": "String",
                "default": ""
            },
            "maxItemsNumber": {
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
            "showSearchBar": {
                "type": "Boolean",
                "value": false
            },
            "showCollectionHeader": {
                "type": "Boolean",
                "value": false
            },
            "showCollectionLabel": {
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
            "order": {
                "type": "String",
                "default": ""
            },
            "blockId": {
                "type": "String",
                "default": ""
            },
            "collectionBackgroundColor": {
                "type": "String",
                "default": "#454647"
            },
            "collectionTextColor": {
                "type": "String",
                "default": "#ffffff"
            },
            "mosaicHeight": {
                "type": "Number",
                "value": 280
            },
            "mosaicGridColumns": {
                "type": "Number",
                "value": 3
            },
            "mosaicGridRows": {
                "type": "Number",
                "value": 3
            },
            "sampleBackgroundImage": {
                "type": "String",
                "default": ""
            },
            "mosaicItemFocalPoint": {
                "type": "Object",
                "default": {
                    "x": 0.5,
                    "y": 0.5
                }
            },
            "mosaicDensity": {
                "type": "Number",
                "default": 5
            },
            "maxColumnsCount": {
                "type": "Number",
                "default": 4
            },
            "cropImagesToSquare": {
                "type": "Boolean",
                "value": true
            }
        },
        "supports": {
            "align": ["full", "wide"],
            "html": false,
            "typography": {
                "fontSize": true
            },
            "color": {
                "text": true,
                "background": false,
                "gradients": false,
                "link": true
            }
        },
        save: function({ attributes, className }) {
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
                mosaicDensity,
                maxColumnsCount,
                cropImagesToSquare
            } = attributes;
        
            // Gets attributes such as style, that are automatically added by the editor hook
            const blockProps = tainacan_blocks.wp_version < '5.6' ? { className: className } : useBlockProps.save();
            return <div
                        { ...blockProps }
                        data-module="dynamic-items-list"
                        search-url={ searchURL }
                        collection-id={ collectionId }
                        show-image={ '' + showImage }
                        show-name={ '' + showName }
                        show-search-bar={ '' + showSearchBar }
                        show-collection-header={ '' + showCollectionHeader }
                        show-collection-label={ '' + showCollectionLabel }
                        crop-images-to-square={ '' + cropImagesToSquare }
                        layout={ layout }
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
                        tainacan-api-root={ tainacan_blocks.root }
                        tainacan-base-url={ tainacan_blocks.base_url }
                        id={ 'wp-block-tainacan-dynamic-items-list_' + blockId }>
                            { content }
                    </div>
        }
    },
    /* Deprecated on Tainacan 0.18.4, due to the new block.json strategy */
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
            mosaicItemFocalPoint: {
                type: Object,
                default: {
                    x: 0.5,
                    y: 0.5
                }
            },
            mosaicDensity: {
                type: Number,
                default: 5
            },
            maxColumnsCount: {
                type: Number,
                default: 4
            },
            cropImagesToSquare: {
                type: Boolean,
                value: true
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
                mosaicDensity,
                maxColumnsCount,
                cropImagesToSquare
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
                        crop-images-to-square={ '' + cropImagesToSquare }
                        layout={ layout }
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
                        tainacan-api-root={ tainacan_blocks.root }
                        tainacan-base-url={ tainacan_blocks.base_url }
                        id={ 'wp-block-tainacan-dynamic-items-list_' + blockId }>
                            { content }
                    </div>
        }
    },
    /* Deprecated on Tainacan 0.18, due to the introduction of maxColumnsCount and fix of osaicItemFocalPointm */
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