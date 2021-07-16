const { __ } = wp.i18n;

export default [
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