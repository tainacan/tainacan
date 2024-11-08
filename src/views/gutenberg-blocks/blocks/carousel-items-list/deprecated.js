const { useBlockProps } = (tainacan_blocks.wp_version < '5.2' ? wp.editor : wp.blockEditor );

export default [
    /* Deprecated on version 0.21.7 to add variableItemsWidth feature */
    {
        "attributes": {
            "content": {
                "type": "array",
                "source": "children",
                "selector": "div"
            },
            "collectionId": {
                "type": "string",
                "default": ""
            },
            "items": {
                "type": "array",
                "default": []
            },
            "isModalOpen": {
                "type": "boolean",
                "default": false
            },
            "searchURL": {
                "type": "string",
                "default": ""
            },
            "selectedItems": {
                "type": "array",
                "default": []
            },
            "itemsRequestSource": {
                "type": "string",
                "default": ""
            },
            "maxItemsNumber": {
                "type": "number",
                "default": 12
            },
            "maxItemsPerScreen": {
                "type": "number",
                "default": 7
            },
            "spaceBetweenItems": {
                "type": "number",
                "default": 32
            },
            "spaceAroundCarousel": {
                "type": "number",
                "default": 50
            },
            "isLoading": {
                "type": "boolean",
                "default": false
            },
            "isLoadingCollection": {
                "type": "boolean",
                "default": false
            },
            "loadStrategy": {
                "type": "string",
                "default": "search"
            },
            "arrowsPosition": {
                "type": "string",
                "default": "around"
            },
            "largeArrows": {
                "type": "boolean",
                "default": false
            },
            "arrowsStyle": {
                "type": "string",
                "default": "type-1"
            },
            "autoPlay": {
                "type": "boolean",
                "default": false
            },
            "autoPlaySpeed": {
                "type": "number",
                "default": 3
            },
            "loopSlides": {
                "type": "boolean",
                "default": false
            },
            "hideTitle": {
                "type": "boolean",
                "default": true
            },
            "showCollectionHeader": {
                "type": "boolean",
                "default": false
            },
            "showCollectionLabel": {
                "type": "boolean",
                "default": false
            },
            "imageSize": {
                "type": "string",
                "default": "tainacan-medium"
            },
            "collection": {
                "type": "object",
                "default": {}
            },
            "blockId": {
                "type": "string",
                "default": ""
            },
            "collectionBackgroundColor": {
                "type": "string",
                "default": "#373839"
            },
            "collectionTextColor": {
                "type": "string",
                "default": "#ffffff"
            }
        },
        save: function ({ attributes }) {
            const {
                content, 
                blockId,
                collectionId,  
                searchURL,
                selectedItems,
                arrowsPosition,
                largeArrows,
                arrowsStyle,
                loadStrategy,
                maxItemsNumber,
                maxItemsPerScreen,
                spaceBetweenItems,
                spaceAroundCarousel,
                autoPlay,
                autoPlaySpeed,
                loopSlides,
                hideTitle,
                imageSize,
                showCollectionHeader,
                showCollectionLabel,
                collectionBackgroundColor,
                collectionTextColor
            } = attributes;
            
            // Gets attributes such as style, that are automatically added by the editor hook
            const blockProps = useBlockProps.save();
        
            return <div 
                        { ...blockProps }
                        data-module="carousel-items-list"
                        data-search-url={ searchURL }
                        data-selected-items={ JSON.stringify(selectedItems) }
                        data-arrows-position={ arrowsPosition }
                        data-load-strategy={ loadStrategy }
                        data-collection-id={ collectionId }  
                        data-auto-play={ '' + autoPlay }
                        data-auto-play-speed={ autoPlaySpeed }
                        data-loop-slides={ '' + loopSlides }
                        data-hide-title={ '' + hideTitle }
                        data-large-arrows={ '' + largeArrows }
                        data-arrows-style={ arrowsStyle }
                        data-image-size={ imageSize }
                        data-show-collection-header={ '' + showCollectionHeader }
                        data-show-collection-label={ '' + showCollectionLabel }
                        data-collection-background-color={ collectionBackgroundColor }
                        data-collection-text-color={ collectionTextColor }
                        data-max-items-number={ maxItemsNumber }
                        data-max-items-per-screen={ maxItemsPerScreen }
                        data-space-between-items={ spaceBetweenItems }
                        data-space-around-carousel={ spaceAroundCarousel }
                        data-tainacan-api-root={ tainacan_blocks.root }
                        id={ 'wp-block-tainacan-carousel-items-list_' + blockId }>
                            { content }
                    </div>
        }
    },
    /* Deprecated during Vue 3 migration to prepend attributes with data- */
    {
        "attributes": {
            "content": {
                "type": "array",
                "source": "children",
                "selector": "div"
            },
            "collectionId": {
                "type": "string",
                "default": ""
            },
            "items": {
                "type": "array",
                "default": []
            },
            "isModalOpen": {
                "type": "boolean",
                "default": false
            },
            "searchURL": {
                "type": "string",
                "default": ""
            },
            "selectedItems": {
                "type": "array",
                "default": []
            },
            "itemsRequestSource": {
                "type": "string",
                "default": ""
            },
            "maxItemsNumber": {
                "type": "number",
                "default": 12
            },
            "maxItemsPerScreen": {
                "type": "number",
                "default": 7
            },
            "spaceBetweenItems": {
                "type": "number",
                "default": 32
            },
            "spaceAroundCarousel": {
                "type": "number",
                "default": 50
            },
            "isLoading": {
                "type": "boolean",
                "default": false
            },
            "isLoadingCollection": {
                "type": "boolean",
                "default": false
            },
            "loadStrategy": {
                "type": "string",
                "default": "search"
            },
            "arrowsPosition": {
                "type": "string",
                "default": "around"
            },
            "largeArrows": {
                "type": "boolean",
                "default": false
            },
            "arrowsStyle": {
                "type": "string",
                "default": "type-1"
            },
            "autoPlay": {
                "type": "boolean",
                "default": false
            },
            "autoPlaySpeed": {
                "type": "number",
                "default": 3
            },
            "loopSlides": {
                "type": "boolean",
                "default": false
            },
            "hideTitle": {
                "type": "boolean",
                "default": true
            },
            "showCollectionHeader": {
                "type": "boolean",
                "default": false
            },
            "showCollectionLabel": {
                "type": "boolean",
                "default": false
            },
            "imageSize": {
                "type": "string",
                "default": "tainacan-medium"
            },
            "collection": {
                "type": "object",
                "default": {}
            },
            "blockId": {
                "type": "string",
                "default": ""
            },
            "collectionBackgroundColor": {
                "type": "string",
                "default": "#373839"
            },
            "collectionTextColor": {
                "type": "string",
                "default": "#ffffff"
            }
        },
        save: function ({ attributes }) {
            const {
                content, 
                blockId,
                collectionId,  
                searchURL,
                selectedItems,
                arrowsPosition,
                largeArrows,
                arrowsStyle,
                loadStrategy,
                maxItemsNumber,
                maxItemsPerScreen,
                spaceBetweenItems,
                spaceAroundCarousel,
                autoPlay,
                autoPlaySpeed,
                loopSlides,
                hideTitle,
                imageSize,
                showCollectionHeader,
                showCollectionLabel,
                collectionBackgroundColor,
                collectionTextColor
            } = attributes;
            
            // Gets attributes such as style, that are automatically added by the editor hook
            const blockProps = useBlockProps.save();
        
            return <div 
                        { ...blockProps }
                        data-module="carousel-items-list"
                        search-url={ searchURL }
                        selected-items={ JSON.stringify(selectedItems) }
                        arrows-position={ arrowsPosition }
                        load-strategy={ loadStrategy }
                        collection-id={ collectionId }  
                        auto-play={ '' + autoPlay }
                        auto-play-speed={ autoPlaySpeed }
                        loop-slides={ '' + loopSlides }
                        hide-title={ '' + hideTitle }
                        large-arrows={ '' + largeArrows }
                        arrows-style={ arrowsStyle }
                        image-size={ imageSize }
                        show-collection-header={ '' + showCollectionHeader }
                        show-collection-label={ '' + showCollectionLabel }
                        collection-background-color={ collectionBackgroundColor }
                        collection-text-color={ collectionTextColor }
                        max-items-number={ maxItemsNumber }
                        max-items-per-screen={ maxItemsPerScreen }
                        space-between-items={ spaceBetweenItems }
                        space-around-carousel={ spaceAroundCarousel }
                        tainacan-api-root={ tainacan_blocks.root }
                        tainacan-base-url={ tainacan_blocks.base_url }
                        id={ 'wp-block-tainacan-carousel-items-list_' + blockId }>
                            { content }
                    </div>
        }
    },
    /* Deprecated on 0.20.4 to replace collectionBackgroundColor */
    {
        attributes: {
            "content": {
                "type": "array",
                "source": "children",
                "selector": "div"
            },
            "collectionId": {
                "type": "string",
                "default": ""
            },
            "items": {
                "type": "array",
                "default": []
            },
            "isModalOpen": {
                "type": "boolean",
                "default": false
            },
            "searchURL": {
                "type": "string",
                "default": ""
            },
            "selectedItems": {
                "type": "array",
                "default": []
            },
            "itemsRequestSource": {
                "type": "string",
                "default": ""
            },
            "maxItemsNumber": {
                "type": "number",
                "default": 12
            },
            "maxItemsPerScreen": {
                "type": "number",
                "default": 7
            },
            "spaceBetweenItems": {
                "type": "number",
                "default": 32
            },
            "spaceAroundCarousel": {
                "type": "number",
                "default": 50
            },
            "isLoading": {
                "type": "boolean",
                "default": false
            },
            "isLoadingCollection": {
                "type": "boolean",
                "default": false
            },
            "loadStrategy": {
                "type": "string",
                "default": "search"
            },
            "arrowsPosition": {
                "type": "string",
                "default": "around"
            },
            "largeArrows": {
                "type": "boolean",
                "default": false
            },
            "arrowsStyle": {
                "type": "string",
                "default": "type-1"
            },
            "autoPlay": {
                "type": "boolean",
                "default": false
            },
            "autoPlaySpeed": {
                "type": "number",
                "default": 3
            },
            "loopSlides": {
                "type": "boolean",
                "default": false
            },
            "hideTitle": {
                "type": "boolean",
                "default": true
            },
            "showCollectionHeader": {
                "type": "boolean",
                "default": false
            },
            "showCollectionLabel": {
                "type": "boolean",
                "default": false
            },
            "imageSize": {
                "type": "string",
                "default": "tainacan-medium"
            },
            "collection": {
                "type": "object",
                "default": {}
            },
            "blockId": {
                "type": "string",
                "default": ""
            },
            "collectionBackgroundColor": {
                "type": "string",
                "default": "#454647"
            },
            "collectionTextColor": {
                "type": "string",
                "default": "#ffffff"
            }
        },
        save: function ({ attributes, className }) {
            const {
                content, 
                blockId,
                collectionId,  
                searchURL,
                selectedItems,
                arrowsPosition,
                largeArrows,
                arrowsStyle,
                loadStrategy,
                maxItemsNumber,
                maxItemsPerScreen,
                spaceBetweenItems,
                spaceAroundCarousel,
                autoPlay,
                autoPlaySpeed,
                loopSlides,
                hideTitle,
                imageSize,
                showCollectionHeader,
                showCollectionLabel,
                collectionBackgroundColor,
                collectionTextColor
            } = attributes;
        
            // Gets attributes such as style, that are automatically added by the editor hook
            const blockProps = tainacan_blocks.wp_version < '5.6' ? { className: className } : useBlockProps.save();
            return <div 
                        { ...blockProps }
                        data-module="carousel-items-list"
                        search-url={ searchURL }
                        selected-items={ JSON.stringify(selectedItems) }
                        arrows-position={ arrowsPosition }
                        load-strategy={ loadStrategy }
                        collection-id={ collectionId }  
                        auto-play={ '' + autoPlay }
                        auto-play-speed={ autoPlaySpeed }
                        loop-slides={ '' + loopSlides }
                        hide-title={ '' + hideTitle }
                        large-arrows={ '' + largeArrows }
                        arrows-style={ arrowsStyle }
                        image-size={ imageSize }
                        show-collection-header={ '' + showCollectionHeader }
                        show-collection-label={ '' + showCollectionLabel }
                        collection-background-color={ collectionBackgroundColor }
                        collection-text-color={ collectionTextColor }
                        max-items-number={ maxItemsNumber }
                        max-items-per-screen={ maxItemsPerScreen }
                        space-between-items={ spaceBetweenItems }
                        space-around-carousel={ spaceAroundCarousel }
                        tainacan-api-root={ tainacan_blocks.root }
                        tainacan-base-url={ tainacan_blocks.base_url }
                        id={ 'wp-block-tainacan-carousel-items-list_' + blockId }>
                            { content }
                    </div>
        }
    },
    /* Deprecated on 0.19 to replace cropImagesToSquare by imageSize feature */
    {
        migrate( attributes ) {
            if (attributes.cropImagesToSquare == true)
                attributes.imageSize = 'tainacan-medium';
            else
                attributes.imageSize = 'tainacan-medium-full';

            if ( isNaN(attributes.maxItemsNumber) )
                attributes.maxItemsNumber = 12;
                
            return attributes;
        },
        attributes: {
            "content": {
                "type": "array",
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
            "isModalOpen": {
                "type": "Boolean",
                "default": false
            },
            "searchURL": {
                "type": "String",
                "default": ""
            },
            "selectedItems": {
                "type": "Array",
                "default": []
            },
            "itemsRequestSource": {
                "type": "String",
                "default": ""
            },
            "maxItemsNumber": {
                "type": "Number",
                "value": 12
            },
            "maxItemsPerScreen": {
                "type": "Number",
                "value": 7
            },
            "spaceBetweenItems": {
                "type": "Number",
                "value": 32
            },
            "spaceAroundCarousel": {
                "type": "Number",
                "value": 50
            },
            "isLoading": {
                "type": "Boolean",
                "value": false
            },
            "isLoadingCollection": {
                "type": "Boolean",
                "value": false
            },
            "loadStrategy": {
                "type": "String",
                "value": "search"
            },
            "arrowsPosition": {
                "type": "String",
                "value": "around"
            },
            "largeArrows": {
                "type": "Boolean",
                "value": false
            },
            "arrowsStyle": {
                "type": "String",
                "value": "type-1"
            },
            "autoPlay": {
                "type": "Boolean",
                "value": false
            },
            "autoPlaySpeed": {
                "type": "Number",
                "value": 3
            },
            "loopSlides": {
                "type": "Boolean",
                "value": false
            },
            "hideTitle": {
                "type": "Boolean",
                "value": true
            },
            "showCollectionHeader": {
                "type": "Boolean",
                "value": false
            },
            "showCollectionLabel": {
                "type": "Boolean",
                "value": false
            },
            "cropImagesToSquare": {
                "type": "Boolean",
                "value": true
            },
            "collection": {
                "type": "Object",
                "value": {}
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
            }
        },
        save: function ({ attributes, className }) {
            const {
                content, 
                blockId,
                collectionId,  
                searchURL,
                selectedItems,
                arrowsPosition,
                largeArrows,
                arrowsStyle,
                loadStrategy,
                maxItemsNumber,
                maxItemsPerScreen,
                spaceBetweenItems,
                spaceAroundCarousel,
                autoPlay,
                autoPlaySpeed,
                loopSlides,
                hideTitle,
                cropImagesToSquare,
                showCollectionHeader,
                showCollectionLabel,
                collectionBackgroundColor,
                collectionTextColor
            } = attributes;
        
            // Gets attributes such as style, that are automatically added by the editor hook
            const blockProps = tainacan_blocks.wp_version < '5.6' ? { className: className } : useBlockProps.save();
            return <div 
                        { ...blockProps }
                        data-module="carousel-items-list"
                        search-url={ searchURL }
                        selected-items={ JSON.stringify(selectedItems) }
                        arrows-position={ arrowsPosition }
                        load-strategy={ loadStrategy }
                        collection-id={ collectionId }  
                        auto-play={ '' + autoPlay }
                        auto-play-speed={ autoPlaySpeed }
                        loop-slides={ '' + loopSlides }
                        hide-title={ '' + hideTitle }
                        large-arrows={ '' + largeArrows }
                        arrows-style={ arrowsStyle }
                        crop-images-to-square={ '' + cropImagesToSquare }
                        show-collection-header={ '' + showCollectionHeader }
                        show-collection-label={ '' + showCollectionLabel }
                        collection-background-color={ collectionBackgroundColor }
                        collection-text-color={ collectionTextColor }
                        max-items-number={ maxItemsNumber }
                        max-items-per-screen={ maxItemsPerScreen }
                        space-between-items={ spaceBetweenItems }
                        space-around-carousel={ spaceAroundCarousel }
                        tainacan-api-root={ tainacan_blocks.root }
                        tainacan-base-url={ tainacan_blocks.base_url }
                        id={ 'wp-block-tainacan-carousel-items-list_' + blockId }>
                            { content }
                    </div>
        }
    },
    /* Deprecated on Tainacan 0.18.6 due to arrowsStyle option */
    {
        "attributes": {
            "content": {
                "type": "array",
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
            "isModalOpen": {
                "type": "Boolean",
                "default": false
            },
            "searchURL": {
                "type": "String",
                "default": ""
            },
            "selectedItems": {
                "type": "Array",
                "default": []
            },
            "itemsRequestSource": {
                "type": "String",
                "default": ""
            },
            "maxItemsNumber": {
                "type": "Number",
                "value": 12
            },
            "maxItemsPerScreen": {
                "type": "Number",
                "value": 7
            },
            "isLoading": {
                "type": "Boolean",
                "value": false
            },
            "isLoadingCollection": {
                "type": "Boolean",
                "value": false
            },
            "loadStrategy": {
                "type": "String",
                "value": "search"
            },
            "arrowsPosition": {
                "type": "String",
                "value": "around"
            },
            "largeArrows": {
                "type": "Boolean",
                "value": false
            },
            "autoPlay": {
                "type": "Boolean",
                "value": false
            },
            "autoPlaySpeed": {
                "type": "Number",
                "value": 3
            },
            "loopSlides": {
                "type": "Boolean",
                "value": false
            },
            "hideTitle": {
                "type": "Boolean",
                "value": true
            },
            "showCollectionHeader": {
                "type": "Boolean",
                "value": false
            },
            "showCollectionLabel": {
                "type": "Boolean",
                "value": false
            },
            "cropImagesToSquare": {
                "type": "Boolean",
                "value": true
            },
            "collection": {
                "type": "Object",
                "value": {}
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
            }
        },
        "supports": {
            "align": ["full", "wide"],
            "html": false,
            "multiple": true,
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
        save({ attributes, className }) {
            const {
                content, 
                blockId,
                collectionId,  
                searchURL,
                selectedItems,
                arrowsPosition,
                largeArrows,
                loadStrategy,
                maxItemsNumber,
                maxItemsPerScreen,
                autoPlay,
                autoPlaySpeed,
                loopSlides,
                hideTitle,
                cropImagesToSquare,
                showCollectionHeader,
                showCollectionLabel,
                collectionBackgroundColor,
                collectionTextColor
            } = attributes;
        
            // Gets attributes such as style, that are automatically added by the editor hook
            const blockProps = tainacan_blocks.wp_version < '5.6' ? { className: className } : useBlockProps.save();
            return <div 
                        { ...blockProps }
                        data-module="carousel-items-list"
                        search-url={ searchURL }
                        selected-items={ JSON.stringify(selectedItems) }
                        arrows-position={ arrowsPosition }
                        load-strategy={ loadStrategy }
                        collection-id={ collectionId }  
                        auto-play={ '' + autoPlay }
                        auto-play-speed={ autoPlaySpeed }
                        loop-slides={ '' + loopSlides }
                        hide-title={ '' + hideTitle }
                        large-arrows={ '' + largeArrows }
                        crop-images-to-square={ '' + cropImagesToSquare }
                        show-collection-header={ '' + showCollectionHeader }
                        show-collection-label={ '' + showCollectionLabel }
                        collection-background-color={ collectionBackgroundColor }
                        collection-text-color={ collectionTextColor }
                        max-items-number={ maxItemsNumber }
                        max-items-per-screen={ maxItemsPerScreen }
                        tainacan-api-root={ tainacan_blocks.root }
                        tainacan-base-url={ tainacan_blocks.base_url }
                        id={ 'wp-block-tainacan-carousel-items-list_' + blockId }>
                            { content }
                    </div>
        }
    },
    /* Deprecated on Version 0.18.4 due to compatibility with WP 5.8 */
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
            isModalOpen: {
                type: Boolean,
                default: false
            },
            searchURL: {
                type: String,
                default: undefined
            },
            selectedItems: {
                type: Array,
                default: []
            },
            itemsRequestSource: {
                type: String,
                default: undefined
            },
            maxItemsNumber: {
                type: Number,
                value: undefined
            },
            maxItemsPerScreen: {
                type: Number,
                value: 7
            },
            isLoading: {
                type: Boolean,
                value: false
            },
            isLoadingCollection: {
                type: Boolean,
                value: false
            },
            loadStrategy: {
                type: String,
                value: 'search'
            },
            arrowsPosition: {
                type: String,
                value: 'search'
            },
            largeArrows: {
                type: Boolean,
                value: false
            },
            autoPlay: {
                type: Boolean,
                value: false
            },
            autoPlaySpeed: {
                type: Number,
                value: 3
            },
            loopSlides: {
                type: Boolean,
                value: false
            },
            hideTitle: {
                type: Boolean,
                value: true
            },
            showCollectionHeader: {
                type: Boolean,
                value: false
            },
            showCollectionLabel: {
                type: Boolean,
                value: false
            },
            cropImagesToSquare: {
                type: Boolean,
                value: true
            },
            collection: {
                type: Object,
                value: undefined
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
        supports: {
            align: ['full', 'wide'],
            html: false,
            multiple: true,
            fontSize: true
        },
        save({ attributes, className }){
            const {
                content, 
                blockId,
                collectionId,  
                searchURL,
                selectedItems,
                arrowsPosition,
                largeArrows,
                loadStrategy,
                maxItemsNumber,
                maxItemsPerScreen,
                autoPlay,
                autoPlaySpeed,
                loopSlides,
                hideTitle,
                cropImagesToSquare,
                showCollectionHeader,
                showCollectionLabel,
                collectionBackgroundColor,
                collectionTextColor
            } = attributes;
            
            return <div 
                        className={ className }
                        search-url={ searchURL }
                        selected-items={ JSON.stringify(selectedItems) }
                        arrows-position={ arrowsPosition }
                        load-strategy={ loadStrategy }
                        collection-id={ collectionId }  
                        auto-play={ '' + autoPlay }
                        auto-play-speed={ autoPlaySpeed }
                        loop-slides={ '' + loopSlides }
                        hide-title={ '' + hideTitle }
                        large-arrows={ '' + largeArrows }
                        crop-images-to-square={ '' + cropImagesToSquare }
                        show-collection-header={ '' + showCollectionHeader }
                        show-collection-label={ '' + showCollectionLabel }
                        collection-background-color={ collectionBackgroundColor }
                        collection-text-color={ collectionTextColor }
                        max-items-number={ maxItemsNumber }
                        max-items-per-screen={ maxItemsPerScreen }
                        tainacan-api-root={ tainacan_blocks.root }
                        tainacan-base-url={ tainacan_blocks.base_url }
                        id={ 'wp-block-tainacan-carousel-items-list_' + blockId }>
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
            isModalOpen: {
                type: Boolean,
                default: false
            },
            searchURL: {
                type: String,
                default: undefined
            },
            selectedItems: {
                type: Array,
                default: []
            },
            itemsRequestSource: {
                type: String,
                default: undefined
            },
            maxItemsNumber: {
                type: Number,
                value: undefined
            },
            maxItemsPerScreen: {
                type: Number,
                value: 7
            },
            isLoading: {
                type: Boolean,
                value: false
            },
            isLoadingCollection: {
                type: Boolean,
                value: false
            },
            loadStrategy: {
                type: String,
                value: 'search'
            },
            arrowsPosition: {
                type: String,
                value: 'search'
            },
            largeArrows: {
                type: Boolean,
                value: false
            },
            autoPlay: {
                type: Boolean,
                value: false
            },
            autoPlaySpeed: {
                type: Number,
                value: 3
            },
            loopSlides: {
                type: Boolean,
                value: false
            },
            hideTitle: {
                type: Boolean,
                value: true
            },
            showCollectionHeader: {
                type: Boolean,
                value: false
            },
            showCollectionLabel: {
                type: Boolean,
                value: false
            },
            cropImagesToSquare: {
                type: Boolean,
                value: true
            },
            collection: {
                type: Object,
                value: undefined
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
        supports: {
            align: ['full', 'wide'],
            html: false,
            multiple: true
        },
        save({ attributes, className }) {
            const {
                content, 
                blockId,
                collectionId,  
                searchURL,
                selectedItems,
                arrowsPosition,
                largeArrows,
                loadStrategy,
                maxItemsNumber,
                maxItemsPerScreen,
                autoPlay,
                autoPlaySpeed,
                loopSlides,
                hideTitle,
                cropImagesToSquare,
                showCollectionHeader,
                showCollectionLabel,
                collectionBackgroundColor,
                collectionTextColor
            } = attributes;
            
            return <div 
                        className={ className }
                        search-url={ searchURL }
                        selected-items={ JSON.stringify(selectedItems) }
                        arrows-position={ arrowsPosition }
                        load-strategy={ loadStrategy }
                        collection-id={ collectionId }  
                        auto-play={ '' + autoPlay }
                        auto-play-speed={ autoPlaySpeed }
                        loop-slides={ '' + loopSlides }
                        hide-title={ '' + hideTitle }
                        large-arrows={ '' + largeArrows }
                        crop-images-to-square={ '' + cropImagesToSquare }
                        show-collection-header={ '' + showCollectionHeader }
                        show-collection-label={ '' + showCollectionLabel }
                        collection-background-color={ collectionBackgroundColor }
                        collection-text-color={ collectionTextColor }
                        max-items-number={ maxItemsNumber }
                        max-items-per-screen={ maxItemsPerScreen }
                        tainacan-api-root={ tainacan_blocks.root }
                        tainacan-base-url={ tainacan_blocks.base_url }
                        id={ 'wp-block-tainacan-carousel-items-list_' + blockId }>
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
            isModalOpen: {
                type: Boolean,
                default: false
            },
            searchURL: {
                type: String,
                default: undefined
            },
            selectedItems: {
                type: Array,
                default: []
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
            loadStrategy: {
                type: String,
                value: 'search'
            },
            arrowsPosition: {
                type: String,
                value: 'search'
            },
            autoPlay: {
                type: Boolean,
                value: false
            },
            autoPlaySpeed: {
                type: Number,
                value: 3
            },
            loopSlides: {
                type: Boolean,
                value: false
            },
            hideTitle: {
                type: Boolean,
                value: true
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
                searchURL,
                selectedItems,
                arrowsPosition,
                loadStrategy,
                maxItemsNumber,
                autoPlay,
                autoPlaySpeed,
                loopSlides,
                hideTitle,
                showCollectionHeader,
                showCollectionLabel,
                collectionBackgroundColor,
                collectionTextColor
            } = attributes;
            return <div 
                        className={ className }
                        search-url={ searchURL }
                        selected-items={ JSON.stringify(selectedItems) }
                        arrows-position={ arrowsPosition }
                        load-strategy={ loadStrategy }
                        collection-id={ collectionId }  
                        auto-play={ '' + autoPlay }
                        auto-play-speed={ autoPlaySpeed }
                        loop-slides={ '' + loopSlides }
                        hide-title={ '' + hideTitle }
                        show-collection-header={ '' + showCollectionHeader }
                        show-collection-label={ '' + showCollectionLabel }
                        collection-background-color={ collectionBackgroundColor }
                        collection-text-color={ collectionTextColor }
                        max-items-number={ maxItemsNumber }
                        tainacan-api-root={ tainacan_blocks.root }
                        tainacan-base-url={ tainacan_blocks.base_url }
                        id={ 'wp-block-tainacan-carousel-items-list_' + blockId }>
                            { content }
                    </div>
        }
    }
];