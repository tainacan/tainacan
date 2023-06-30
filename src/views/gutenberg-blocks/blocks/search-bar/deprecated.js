const { __ } = wp.i18n;
const { useBlockProps } = (tainacan_blocks.wp_version < '5.2' ? wp.editor : wp.blockEditor );

export default [
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
            "collectionSlug": {
                "type": "string",
                "default": ""
            },
            "alignment": {
                "type": "string",
                "default": "center"
            },
            "isModalOpen": {
                "type": "boolean",
                "default": false
            },
            "maxWidth": {
                "type": "number",
                "value": 80
            },
            "placeholderText": {
                "type": "string",
                "default": "Search"
            },
            "searchQuery": {
                "type": "string",
                "default": "search"
            },
            "showCollectionHeader": {
                "type": "boolean",
                "value": false
            },
            "showCollectionLabel": {
                "type": "boolean",
                "value": false
            },
            "collectionHeaderHeight": {
                "type": "number",
                "value": 165
            },
            "collectionBackgroundColor": {
                "type": "string",
                "default": "#454647"
            },
            "collectionTextColor": {
                "type": "string",
                "default": "#ffffff"
            },
            "collectionHeaderImage": {
                "type": "string",
                "default": ""
            },
            "collectionName": {
                "type": "string",
                "default": ""
            },
            "collectionTextSize": {
                "type": "number",
                "default": 2
            }
        },
        save: function({ attributes, className }) {
            const { content } = attributes;
            
            // Gets attributes such as style, that are automatically added by the editor hook
            const blockProps = tainacan_blocks.wp_version < '5.6' ? { className: className } : useBlockProps.save();
            return <div { ...blockProps } data-module="search-bar">{ content }</div>
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
            alignment: {
                type: String,
                default: 'center'
            },
            isModalOpen: {
                type: Boolean,
                default: false
            },
            maxWidth: {
                type: Number,
                value: 80
            },
            placeholderText: {
                type: String,
                default: __('Search', 'tainacan')
            },
            showCollectionHeader: {
                type: Boolean,
                value: false
            },
            showCollectionLabel: {
                type: Boolean,
                value: false
            },
            collectionHeaderHeight: {
                type: Number,
                value: 165
            },
            collectionBackgroundColor: {
                type: String,
                default: "#454647"
            },
            collectionTextColor: {
                type: String,
                default: "#ffffff"
            },
            collectionHeaderImage: {
                type: String,
                default: undefined
            },
            collectionName: {
                type: String,
                default: undefined
            },
            collectionTextSize: {
                type: Number,
                default: 2
            },
        },
        supports: {
            align: ['full', 'wide', 'left', 'center', 'right'],
            html: true,
            multiple: false,
            fontSize: true
        },
        save({ attributes, className }) {
            const { content } = attributes;
            return <div className={ className }>{ content }</div>
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
            collectionSlug: {
                type: String,
                default: undefined
            },
            alignment: {
                type: String,
                default: 'center'
            },
            isModalOpen: {
                type: Boolean,
                default: false
            },
            maxWidth: {
                type: Number,
                value: 80
            },
            placeholderText: {
                type: String,
                default: __('Search', 'tainacan')
            },
            showCollectionHeader: {
                type: Boolean,
                value: false
            },
            showCollectionLabel: {
                type: Boolean,
                value: false
            },
            collectionHeaderHeight: {
                type: Number,
                value: 165
            },
            collectionBackgroundColor: {
                type: String,
                default: "#454647"
            },
            collectionTextColor: {
                type: String,
                default: "#ffffff"
            },
            collectionHeaderImage: {
                type: String,
                default: undefined
            },
            collectionName: {
                type: String,
                default: undefined
            },
            collectionTextSize: {
                type: Number,
                default: 2
            },
        },
        supports: {
            align: ['full', 'wide', 'left', 'center', 'right'],
            html: true,
            multiple: false
        },
        save({ attributes, className }){
            const { content } = attributes;
            return <div className={ className }>{ content }</div>
        }
    }
]