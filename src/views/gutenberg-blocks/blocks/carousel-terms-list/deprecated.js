const { useBlockProps } = (tainacan_blocks.wp_version < '5.2' ? wp.editor : wp.blockEditor );

export default [
    /* Deprecated during Vue 3 migration to prepend attributes with data- */
    {
        "attributes": {
            "content": {
                "type": "array",
                "source": "children",
                "selector": "div"
            },
            "terms": {
                "type": "array",
                "default": []
            },
            "isModalOpen": {
                "type": "boolean",
                "default": false
            },
            "selectedTerms": {
                "type": "array",
                "default": []
            },
            "itemsRequestSource": {
                "type": "string",
                "default": ""
            },
            "maxTermsnumber": {
                "type": "number",
                "value": 12
            },
            "maxTermsPerScreen": {
                "type": "number",
                "value": 6
            },
            "spaceBetweenTerms": {
                "type": "number",
                "value": 32
            },
            "spaceAroundCarousel": {
                "type": "number",
                "value": 50
            },
            "isLoading": {
                "type": "boolean",
                "value": false
            },
            "isLoadingTerm": {
                "type": "boolean",
                "value": false
            },
            "arrowsPosition": {
                "type": "string",
                "value": "around"
            },
            "largeArrows": {
                "type": "boolean",
                "value": false
            },
            "arrowsStyle": {
                "type": "string",
                "value": "type-1"
            },
            "autoPlay": {
                "type": "boolean",
                "value": false
            },
            "autoPlaySpeed": {
                "type": "number",
                "value": 3
            },
            "loopSlides": {
                "type": "boolean",
                "value": false
            },
            "hideName": {
                "type": "boolean",
                "value": true
            },
            "showTermThumbnail": {
                "type": "boolean",
                "value": false
            },
            "term": {
                "type": "object",
                "value": {}
            },
            "imageSize": {
                "type": "string",
                "default": "tainacan-medium"
            },
            "blockId": {
                "type": "string",
                "default": ""
            },
            "termBackgroundColor": {
                "type": "string",
                "default": "#373839"
            },
            "termTextColor": {
                "type": "string",
                "default": "#ffffff"
            },
            "taxonomyId": {
                "type": "string",
                "default": ""
            }
        },
        save: function({ attributes }) {
            const {
                content, 
                blockId,
                selectedTerms,
                arrowsPosition,
                largeArrows,
                arrowsStyle,
                maxTermsPerScreen,
                maxTermsNumber,
                spaceBetweenTerms,
                spaceAroundCarousel,
                autoPlay,
                autoPlaySpeed,
                loopSlides,
                hideName,
                imageSize,
                showTermThumbnail,
                taxonomyId
            } = attributes;
            
            // Gets attributes such as style, that are automatically added by the editor hook
            const blockProps = useBlockProps.save();
            return <div 
                        { ...blockProps }
                        data-module="carousel-terms-list"
                        selected-terms={ JSON.stringify(selectedTerms.map((term) => { return term.id; })) }
                        arrows-position={ arrowsPosition }
                        auto-play={ '' + autoPlay }
                        auto-play-speed={ autoPlaySpeed }
                        loop-slides={ '' + loopSlides }
                        hide-name={ '' + hideName }
                        large-arrows={ '' + largeArrows }
                        arrows-style={ arrowsStyle }
                        image-size={ imageSize }
                        max-terms-number={ maxTermsNumber }
                        max-terms-per-screen={ maxTermsPerScreen }
                        space-between-terms={ spaceBetweenTerms }
                        space-around-carousel={ spaceAroundCarousel }
                        taxonomy-id={ taxonomyId }
                        tainacan-api-root={ tainacan_blocks.root }
                        tainacan-base-url={ tainacan_blocks.base_url }
                        show-term-thumbnail={ '' + showTermThumbnail }
                        id={ 'wp-block-tainacan-carousel-terms-list_' + blockId }>
                            { content }
                    </div>
        }
    },
    /* Deprecated on 0.20.4 to replace termBackgroundColor */
    {
        attributes: {
            "content": {
                "type": "array",
                "source": "children",
                "selector": "div"
            },
            "terms": {
                "type": "array",
                "default": []
            },
            "isModalOpen": {
                "type": "boolean",
                "default": false
            },
            "selectedTerms": {
                "type": "array",
                "default": []
            },
            "itemsRequestSource": {
                "type": "string",
                "default": ""
            },
            "maxTermsnumber": {
                "type": "number",
                "value": 12
            },
            "maxTermsPerScreen": {
                "type": "number",
                "value": 6
            },
            "spaceBetweenTerms": {
                "type": "number",
                "value": 32
            },
            "spaceAroundCarousel": {
                "type": "number",
                "value": 50
            },
            "isLoading": {
                "type": "boolean",
                "value": false
            },
            "isLoadingTerm": {
                "type": "boolean",
                "value": false
            },
            "arrowsPosition": {
                "type": "string",
                "value": "around"
            },
            "largeArrows": {
                "type": "boolean",
                "value": false
            },
            "arrowsStyle": {
                "type": "string",
                "value": "type-1"
            },
            "autoPlay": {
                "type": "boolean",
                "value": false
            },
            "autoPlaySpeed": {
                "type": "number",
                "value": 3
            },
            "loopSlides": {
                "type": "boolean",
                "value": false
            },
            "hideName": {
                "type": "boolean",
                "value": true
            },
            "showTermThumbnail": {
                "type": "boolean",
                "value": false
            },
            "term": {
                "type": "object",
                "value": {}
            },
            "imageSize": {
                "type": "string",
                "default": "tainacan-medium"
            },
            "blockId": {
                "type": "string",
                "default": ""
            },
            "termBackgroundColor": {
                "type": "string",
                "default": "#454647"
            },
            "termTextColor": {
                "type": "string",
                "default": "#ffffff"
            },
            "taxonomyId": {
                "type": "string",
                "default": ""
            }
        },
        save: function({ attributes, className }) {
            const {
                content, 
                blockId,
                selectedTerms,
                arrowsPosition,
                largeArrows,
                arrowsStyle,
                maxTermsPerScreen,
                maxTermsNumber,
                spaceBetweenTerms,
                spaceAroundCarousel,
                autoPlay,
                autoPlaySpeed,
                loopSlides,
                hideName,
                imageSize,
                showTermThumbnail,
                taxonomyId
            } = attributes;
            
            // Gets attributes such as style, that are automatically added by the editor hook
            const blockProps = tainacan_blocks.wp_version < '5.6' ? { className: className } : useBlockProps.save();
            return <div 
                        { ...blockProps }
                        data-module="carousel-terms-list"
                        selected-terms={ JSON.stringify(selectedTerms.map((term) => { return term.id; })) }
                        arrows-position={ arrowsPosition }
                        auto-play={ '' + autoPlay }
                        auto-play-speed={ autoPlaySpeed }
                        loop-slides={ '' + loopSlides }
                        hide-name={ '' + hideName }
                        large-arrows={ '' + largeArrows }
                        arrows-style={ arrowsStyle }
                        image-size={ imageSize }
                        max-terms-number={ maxTermsNumber }
                        max-terms-per-screen={ maxTermsPerScreen }
                        space-between-terms={ spaceBetweenTerms }
                        space-around-carousel={ spaceAroundCarousel }
                        taxonomy-id={ taxonomyId }
                        tainacan-api-root={ tainacan_blocks.root }
                        tainacan-base-url={ tainacan_blocks.base_url }
                        show-term-thumbnail={ '' + showTermThumbnail }
                        id={ 'wp-block-tainacan-carousel-terms-list_' + blockId }>
                            { content }
                    </div>
        }
    },
    /* Deprecated on 0.20.1 to add imageSize attribute */
    {
        "attributes": {
            "content": {
                "type": "Array",
                "source": "children",
                "selector": "div"
            },
            "terms": {
                "type": "Array",
                "default": []
            },
            "isModalOpen": {
                "type": "Boolean",
                "default": false
            },
            "selectedTerms": {
                "type": "Array",
                "default": []
            },
            "itemsRequestSource": {
                "type": "String",
                "default": ""
            },
            "maxTermsNumber": {
                "type": "Number",
                "value": 12
            },
            "maxTermsPerScreen": {
                "type": "Number",
                "value": 6
            },
            "spaceBetweenTerms": {
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
            "isLoadingTerm": {
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
            "showTermThumbnail": {
                "type": "Boolean",
                "value": false
            },
            "term": {
                "type": "Object",
                "value": {}
            },
            "blockId": {
                "type": "String",
                "default": ""
            },
            "termBackgroundColor": {
                "type": "String",
                "default": "#454647"
            },
            "termTextColor": {
                "type": "String",
                "default": "#ffffff"
            },
            "taxonomyId": {
                "type": "String",
                "default": ""
            }
        },
        save({ attributes, className }) {
            const {
                content, 
                blockId,
                selectedTerms,
                arrowsPosition,
                largeArrows,
                arrowsStyle,
                maxTermsPerScreen,
                maxTermsNumber,
                spaceBetweenTerms,
                spaceAroundCarousel,
                autoPlay,
                autoPlaySpeed,
                loopSlides,
                hideName,
                showTermThumbnail,
                taxonomyId
            } = attributes;
            
            // Gets attributes such as style, that are automatically added by the editor hook
            const blockProps = tainacan_blocks.wp_version < '5.6' ? { className: className } : useBlockProps.save();
            return <div 
                        { ...blockProps }
                        data-module="carousel-terms-list"
                        selected-terms={ JSON.stringify(selectedTerms.map((term) => { return term.id; })) }
                        arrows-position={ arrowsPosition }
                        auto-play={ '' + autoPlay }
                        auto-play-speed={ autoPlaySpeed }
                        loop-slides={ '' + loopSlides }
                        hide-name={ '' + hideName }
                        large-arrows={ '' + largeArrows }
                        arrows-style={ arrowsStyle }
                        max-terms-number={ maxTermsNumber }
                        max-terms-per-screen={ maxTermsPerScreen }
                        space-between-terms={ spaceBetweenTerms }
                        space-around-carousel={ spaceAroundCarousel }
                        taxonomy-id={ taxonomyId }
                        tainacan-api-root={ tainacan_blocks.root }
                        tainacan-base-url={ tainacan_blocks.base_url }
                        show-term-thumbnail={ '' + showTermThumbnail }
                        id={ 'wp-block-tainacan-carousel-terms-list_' + blockId }>
                            { content }
                    </div>
        }
    },
    /* Deprecated on Tainacan 0.18.6 due to arrowsStyle option */
    {
        "attributes": {
            "content": {
                "type": "Array",
                "source": "children",
                "selector": "div"
            },
            "terms": {
                "type": "Array",
                "default": []
            },
            "isModalOpen": {
                "type": "Boolean",
                "default": false
            },
            "selectedTerms": {
                "type": "Array",
                "default": []
            },
            "itemsRequestSource": {
                "type": "String",
                "default": ""
            },
            "maxTermsNumber": {
                "type": "Number",
                "value": 12
            },
            "maxTermsPerScreen": {
                "type": "Number",
                "value": 6
            },
            "isLoading": {
                "type": "Boolean",
                "value": false
            },
            "isLoadingTerm": {
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
            "showTermThumbnail": {
                "type": "Boolean",
                "value": false
            },
            "term": {
                "type": "Object",
                "value": {}
            },
            "blockId": {
                "type": "String",
                "default": ""
            },
            "termBackgroundColor": {
                "type": "String",
                "default": "#454647"
            },
            "termTextColor": {
                "type": "String",
                "default": "#ffffff"
            },
            "taxonomyId": {
                "type": "String",
                "default": ""
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
                selectedTerms,
                arrowsPosition,
                largeArrows,
                maxTermsPerScreen,
                maxTermsNumber,
                autoPlay,
                autoPlaySpeed,
                loopSlides,
                hideName,
                showTermThumbnail,
                taxonomyId
            } = attributes;
            
            // Gets attributes such as style, that are automatically added by the editor hook
            const blockProps = tainacan_blocks.wp_version < '5.6' ? { className: className } : useBlockProps.save();
            return <div 
                        { ...blockProps }
                        data-module="carousel-terms-list"
                        selected-terms={ JSON.stringify(selectedTerms.map((term) => { return term.id; })) }
                        arrows-position={ arrowsPosition }
                        auto-play={ '' + autoPlay }
                        auto-play-speed={ autoPlaySpeed }
                        loop-slides={ '' + loopSlides }
                        hide-name={ '' + hideName }
                        large-arrows={ '' + largeArrows }
                        max-terms-number={ maxTermsNumber }
                        max-terms-per-screen={ maxTermsPerScreen }
                        taxonomy-id={ taxonomyId }
                        tainacan-api-root={ tainacan_blocks.root }
                        tainacan-base-url={ tainacan_blocks.base_url }
                        show-term-thumbnail={ '' + showTermThumbnail }
                        id={ 'wp-block-tainacan-carousel-terms-list_' + blockId }>
                            { content }
                    </div>
        }
    },
    /* Deprecated on Tainacan 0.18.4 due to the new block.json strategy */
    {
        attributes: {
            content: {
                type: 'array',
                source: 'children',
                selector: 'div'
            },
            terms: {
                type: Array,
                default: []
            },
            isModalOpen: {
                type: Boolean,
                default: false
            },
            selectedTerms: {
                type: Array,
                default: []
            },
            itemsRequestSource: {
                type: String,
                default: undefined
            },
            maxTermsNumber: {
                type: Number,
                value: undefined
            },
            maxTermsPerScreen: {
                type: Number,
                value: 6
            },
            isLoading: {
                type: Boolean,
                value: false
            },
            isLoadingTerm: {
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
            showTermThumbnail: {
                type: Boolean,
                value: false
            },
            term: {
                type: Object,
                value: undefined
            },
            blockId: {
                type: String,
                default: undefined
            },
            termBackgroundColor: {
                type: String,
                default: "#454647"
            },
            termTextColor: {
                type: String,
                default: "#ffffff"
            },
            taxonomyId: {
                type: String,
                default: undefined
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
                selectedTerms,
                arrowsPosition,
                largeArrows,
                maxTermsPerScreen,
                maxTermsNumber,
                autoPlay,
                autoPlaySpeed,
                loopSlides,
                hideName,
                showTermThumbnail,
                taxonomyId
            } = attributes;
            return <div 
                        className={ className }
                        selected-terms={ JSON.stringify(selectedTerms.map((term) => { return term.id; })) }
                        arrows-position={ arrowsPosition }
                        auto-play={ '' + autoPlay }
                        auto-play-speed={ autoPlaySpeed }
                        loop-slides={ '' + loopSlides }
                        hide-name={ '' + hideName }
                        large-arrows={ '' + largeArrows }
                        max-terms-number={ maxTermsNumber }
                        max-terms-per-screen={ maxTermsPerScreen }
                        taxonomy-id={ taxonomyId }
                        tainacan-api-root={ tainacan_blocks.root }
                        tainacan-base-url={ tainacan_blocks.base_url }
                        show-term-thumbnail={ '' + showTermThumbnail }
                        id={ 'wp-block-tainacan-carousel-terms-list_' + blockId }>
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
            terms: {
                type: Array,
                default: []
            },
            isModalOpen: {
                type: Boolean,
                default: false
            },
            selectedTerms: {
                type: Array,
                default: []
            },
            itemsRequestSource: {
                type: String,
                default: undefined
            },
            maxTermsNumber: {
                type: Number,
                value: undefined
            },
            maxTermsPerScreen: {
                type: Number,
                value: 6
            },
            isLoading: {
                type: Boolean,
                value: false
            },
            isLoadingTerm: {
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
            showTermThumbnail: {
                type: Boolean,
                value: false
            },
            term: {
                type: Object,
                value: undefined
            },
            blockId: {
                type: String,
                default: undefined
            },
            termBackgroundColor: {
                type: String,
                default: "#454647"
            },
            termTextColor: {
                type: String,
                default: "#ffffff"
            },
            taxonomyId: {
                type: String,
                default: undefined
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
                selectedTerms,
                arrowsPosition,
                largeArrows,
                maxTermsPerScreen,
                maxTermsNumber,
                autoPlay,
                autoPlaySpeed,
                loopSlides,
                hideName,
                showTermThumbnail,
                taxonomyId
            } = attributes;
            return <div 
                        className={ className }
                        selected-terms={ JSON.stringify(selectedTerms.map((term) => { return term.id; })) }
                        arrows-position={ arrowsPosition }
                        auto-play={ '' + autoPlay }
                        auto-play-speed={ autoPlaySpeed }
                        loop-slides={ '' + loopSlides }
                        hide-name={ '' + hideName }
                        large-arrows={ '' + largeArrows }
                        max-terms-number={ maxTermsNumber }
                        max-terms-per-screen={ maxTermsPerScreen }
                        taxonomy-id={ taxonomyId }
                        tainacan-api-root={ tainacan_blocks.root }
                        tainacan-base-url={ tainacan_blocks.base_url }
                        show-term-thumbnail={ '' + showTermThumbnail }
                        id={ 'wp-block-tainacan-carousel-terms-list_' + blockId }>
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
            terms: {
                type: Array,
                default: []
            },
            isModalOpen: {
                type: Boolean,
                default: false
            },
            selectedTerms: {
                type: Array,
                default: []
            },
            itemsRequestSource: {
                type: String,
                default: undefined
            },
            maxTermsNumber: {
                type: Number,
                value: undefined
            },
            isLoading: {
                type: Boolean,
                value: false
            },
            isLoadingTerm: {
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
            showTermThumbnail: {
                type: Boolean,
                value: false
            },
            term: {
                type: Object,
                value: undefined
            },
            blockId: {
                type: String,
                default: undefined
            },
            termBackgroundColor: {
                type: String,
                default: "#454647"
            },
            termTextColor: {
                type: String,
                default: "#ffffff"
            },
            taxonomyId: {
                type: String,
                default: undefined
            }
        },
        save({ attributes, className }){
            const {
                content, 
                blockId,
                selectedTerms,
                arrowsPosition,
                maxTermsNumber,
                autoPlay,
                autoPlaySpeed,
                loopSlides,
                hideName,
                showTermThumbnail,
                taxonomyId
            } = attributes;
            return <div 
                        className={ className }
                        selected-terms={ JSON.stringify(selectedTerms.map((term) => { return term.id; })) }
                        arrows-position={ arrowsPosition }
                        auto-play={ '' + autoPlay }
                        auto-play-speed={ autoPlaySpeed }
                        loop-slides={ '' + loopSlides }
                        hide-name={ '' + hideName }
                        max-terms-number={ maxTermsNumber }
                        taxonomy-id={ taxonomyId }
                        tainacan-api-root={ tainacan_blocks.root }
                        tainacan-base-url={ tainacan_blocks.base_url }
                        show-term-thumbnail={ '' + showTermThumbnail }
                        id={ 'wp-block-tainacan-carousel-terms-list_' + blockId }>
                            { content }
                    </div>
        },
    },
    {
        save({ attributes, className }){
            const {
                content, 
                blockId,
                selectedTerms,
                arrowsPosition,
                maxTermsNumber,
                autoPlay,
                autoPlaySpeed,
                loopSlides,
                hideName,
                showTermThumbnail,
                taxonomyId
            } = attributes;
            return <div 
                        className={ className }
                        selected-terms={ JSON.stringify(selectedTerms) }
                        arrows-position={ arrowsPosition }
                        auto-play={ '' + autoPlay }
                        auto-play-speed={ autoPlaySpeed }
                        loop-slides={ '' + loopSlides }
                        hide-name={ '' + hideName }
                        max-terms-number={ maxTermsNumber }
                        taxonomy-id={ taxonomyId }
                        tainacan-api-root={ tainacan_blocks.root }
                        tainacan-base-url={ tainacan_blocks.base_url }
                        show-term-thumbnail={ '' + showTermThumbnail }
                        id={ 'wp-block-tainacan-carousel-terms-list_' + blockId }>
                            { content }
                    </div>
        }
    }
]