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
                "type": "array",
                "source": "children",
                "selector": "div"
            },
            "collections": {
                "type": "Array",
                "default": []
            },
            "isModalOpen": {
                "type": "Boolean",
                "default": false
            },
            "selectedCollections": {
                "type": "Array",
                "default": []
            },
            "itemsRequestSource": {
                "type": "String",
                "default": false
            },
            "maxCollectionsNumber": {
                "type": "Number",
                "value": false
            },
            "maxCollectionsPerScreen": {
                "type": "Number",
                "value": 6
            },
            "spaceBetweenCollections": {
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
            "hideName": {
                "type": "Boolean",
                "value": true
            },
            "showCollectionThumbnail": {
                "type": "Boolean",
                "value": false
            },
            "cropImagesToSquare": {
                "type": "Boolean",
                "value": true
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
                selectedCollections,
                arrowsPosition,
                largeArrows,
                arrowsStyle,
                cropImagesToSquare,
                maxCollectionsPerScreen,
                maxCollectionsNumber,
                spaceBetweenCollections,
                spaceAroundCarousel,
                autoPlay,
                autoPlaySpeed,
                loopSlides,
                hideName,
                showCollectionThumbnail
            } = attributes;
        
            // Gets attributes such as style, that are automatically added by the editor hook
            const blockProps = tainacan_blocks.wp_version < '5.6' ? { className: className } : useBlockProps.save();
            return <div 
                        { ...blockProps }
                        data-module="carousel-collections-list"
                        selected-collections={ JSON.stringify(selectedCollections.map((collection) => { return collection.id })) }
                        arrows-position={ arrowsPosition }
                        auto-play={ '' + autoPlay }
                        auto-play-speed={ autoPlaySpeed }
                        loop-slides={ '' + loopSlides }
                        hide-name={ '' + hideName }
                        large-arrows={ '' + largeArrows }
                        arrows-style={ arrowsStyle }
                        crop-images-to-square={ '' + cropImagesToSquare }
                        max-collections-number={ maxCollectionsNumber }
                        max-collections-per-screen={ maxCollectionsPerScreen }
                        space-between-collections={ spaceBetweenCollections }
                        space-around-carousel={ spaceAroundCarousel }
                        tainacan-api-root={ tainacan_blocks.root }
                        tainacan-base-url={ tainacan_blocks.base_url }
                        show-collection-thumbnail={ '' + showCollectionThumbnail }
                        id={ 'wp-block-tainacan-carousel-collections-list_' + blockId }>
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
            "collections": {
                "type": "Array",
                "default": []
            },
            "isModalOpen": {
                "type": "Boolean",
                "default": false
            },
            "selectedCollections": {
                "type": "Array",
                "default": []
            },
            "itemsRequestSource": {
                "type": "String",
                "default": false
            },
            "maxCollectionsNumber": {
                "type": "Number",
                "value": false
            },
            "maxCollectionsPerScreen": {
                "type": "Number",
                "value": 6
            },
            "isLoading": {
                "type": "Boolean",
                "value": false
            },
            "isLoadingCollection": {
                "type": "Boolean",
                "value": false
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
            "hideName": {
                "type": "Boolean",
                "value": true
            },
            "showCollectionThumbnail": {
                "type": "Boolean",
                "value": false
            },
            "cropImagesToSquare": {
                "type": "Boolean",
                "value": true
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
                selectedCollections,
                arrowsPosition,
                largeArrows,
                cropImagesToSquare,
                maxCollectionsPerScreen,
                maxCollectionsNumber,
                autoPlay,
                autoPlaySpeed,
                loopSlides,
                hideName,
                showCollectionThumbnail
            } = attributes;
        
            // Gets attributes such as style, that are automatically added by the editor hook
            const blockProps = tainacan_blocks.wp_version < '5.6' ? { className: className } : useBlockProps.save();
            return <div 
                        { ...blockProps }
                        data-module="carousel-collections-list"
                        selected-collections={ JSON.stringify(selectedCollections.map((collection) => { return collection.id })) }
                        arrows-position={ arrowsPosition }
                        auto-play={ '' + autoPlay }
                        auto-play-speed={ autoPlaySpeed }
                        loop-slides={ '' + loopSlides }
                        hide-name={ '' + hideName }
                        large-arrows={ '' + largeArrows }
                        crop-images-to-square={ '' + cropImagesToSquare }
                        max-collections-number={ maxCollectionsNumber }
                        max-collections-per-screen={ maxCollectionsPerScreen }
                        tainacan-api-root={ tainacan_blocks.root }
                        tainacan-base-url={ tainacan_blocks.base_url }
                        show-collection-thumbnail={ '' + showCollectionThumbnail }
                        id={ 'wp-block-tainacan-carousel-collections-list_' + blockId }>
                            { content }
                    </div>
        }
    },
    /* Deprecated on Tainacan 0.18.4, due to new block.json strategy */
    {
        attributes: {
            content: {
                type: 'array',
                source: 'children',
                selector: 'div'
            },
            collections: {
                type: Array,
                default: []
            },
            isModalOpen: {
                type: Boolean,
                default: false
            },
            selectedCollections: {
                type: Array,
                default: []
            },
            itemsRequestSource: {
                type: String,
                default: undefined
            },
            maxCollectionsNumber: {
                type: Number,
                value: undefined
            },
            maxCollectionsPerScreen: {
                type: Number,
                value: 6
            },
            isLoading: {
                type: Boolean,
                value: false
            },
            isLoadingCollection: {
                type: Boolean,
                value: false
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
            hideName: {
                type: Boolean,
                value: true
            },
            showCollectionThumbnail: {
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
                selectedCollections,
                arrowsPosition,
                largeArrows,
                cropImagesToSquare,
                maxCollectionsPerScreen,
                maxCollectionsNumber,
                autoPlay,
                autoPlaySpeed,
                loopSlides,
                hideName,
                showCollectionThumbnail
            } = attributes;
            return <div 
                        className={ className }
                        selected-collections={ JSON.stringify(selectedCollections.map((collection) => { return collection.id })) }
                        arrows-position={ arrowsPosition }
                        auto-play={ '' + autoPlay }
                        auto-play-speed={ autoPlaySpeed }
                        loop-slides={ '' + loopSlides }
                        hide-name={ '' + hideName }
                        large-arrows={ '' + largeArrows }
                        crop-images-to-square={ '' + cropImagesToSquare }
                        max-collections-number={ maxCollectionsNumber }
                        max-collections-per-screen={ maxCollectionsPerScreen }
                        tainacan-api-root={ tainacan_blocks.root }
                        tainacan-base-url={ tainacan_blocks.base_url }
                        show-collection-thumbnail={ '' + showCollectionThumbnail }
                        id={ 'wp-block-tainacan-carousel-collections-list_' + blockId }>
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
            collections: {
                type: Array,
                default: []
            },
            isModalOpen: {
                type: Boolean,
                default: false
            },
            selectedCollections: {
                type: Array,
                default: []
            },
            itemsRequestSource: {
                type: String,
                default: undefined
            },
            maxCollectionsNumber: {
                type: Number,
                value: undefined
            },
            maxCollectionsPerScreen: {
                type: Number,
                value: 6
            },
            isLoading: {
                type: Boolean,
                value: false
            },
            isLoadingCollection: {
                type: Boolean,
                value: false
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
            hideName: {
                type: Boolean,
                value: true
            },
            showCollectionThumbnail: {
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
        save({ attributes, className }){
            const {
                content, 
                blockId,
                selectedCollections,
                arrowsPosition,
                largeArrows,
                cropImagesToSquare,
                maxCollectionsPerScreen,
                maxCollectionsNumber,
                autoPlay,
                autoPlaySpeed,
                loopSlides,
                hideName,
                showCollectionThumbnail
            } = attributes;
            return <div 
                        className={ className }
                        selected-collections={ JSON.stringify(selectedCollections.map((collection) => { return collection.id })) }
                        arrows-position={ arrowsPosition }
                        auto-play={ '' + autoPlay }
                        auto-play-speed={ autoPlaySpeed }
                        loop-slides={ '' + loopSlides }
                        hide-name={ '' + hideName }
                        large-arrows={ '' + largeArrows }
                        crop-images-to-square={ '' + cropImagesToSquare }
                        max-collections-number={ maxCollectionsNumber }
                        max-collections-per-screen={ maxCollectionsPerScreen }
                        tainacan-api-root={ tainacan_blocks.root }
                        tainacan-base-url={ tainacan_blocks.base_url }
                        show-collection-thumbnail={ '' + showCollectionThumbnail }
                        id={ 'wp-block-tainacan-carousel-collections-list_' + blockId }>
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
            collections: {
                type: Array,
                default: []
            },
            isModalOpen: {
                type: Boolean,
                default: false
            },
            selectedCollections: {
                type: Array,
                default: []
            },
            itemsRequestSource: {
                type: String,
                default: undefined
            },
            maxCollectionsNumber: {
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
            hideName: {
                type: Boolean,
                value: true
            },
            showCollectionThumbnail: {
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
                selectedCollections,
                arrowsPosition,
                maxCollectionsNumber,
                autoPlay,
                autoPlaySpeed,
                loopSlides,
                hideName,
                showCollectionThumbnail
            } = attributes;
            return <div 
                        className={ className }
                        selected-collections={ JSON.stringify(selectedCollections.map((collection) => { return collection.id })) }
                        arrows-position={ arrowsPosition }
                        auto-play={ '' + autoPlay }
                        auto-play-speed={ autoPlaySpeed }
                        loop-slides={ '' + loopSlides }
                        hide-name={ '' + hideName }
                        max-collections-number={ maxCollectionsNumber }
                        tainacan-api-root={ tainacan_blocks.root }
                        tainacan-base-url={ tainacan_blocks.base_url }
                        show-collection-thumbnail={ '' + showCollectionThumbnail }
                        id={ 'wp-block-tainacan-carousel-collections-list_' + blockId }>
                            { content }
                    </div>
        },
    },
    {
        save({ attributes, className }){
            const {
                content, 
                blockId,
                selectedCollections,
                arrowsPosition,
                maxCollectionsNumber,
                autoPlay,
                autoPlaySpeed,
                loopSlides,
                hideName,
                showCollectionThumbnail
            } = attributes;
            return <div 
                        className={ className }
                        selected-collections={ JSON.stringify(selectedCollections) }
                        arrows-position={ arrowsPosition }
                        auto-play={ '' + autoPlay }
                        auto-play-speed={ autoPlaySpeed }
                        loop-slides={ '' + loopSlides }
                        hide-name={ '' + hideName }
                        max-collections-number={ maxCollectionsNumber }
                        tainacan-api-root={ tainacan_blocks.root }
                        tainacan-base-url={ tainacan_blocks.base_url }
                        show-collection-thumbnail={ '' + showCollectionThumbnail }
                        id={ 'wp-block-tainacan-carousel-collections-list_' + blockId }>
                            { content }
                    </div>
        }
    }
]