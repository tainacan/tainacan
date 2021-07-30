export default [
    /* Deprecated on Tainacan 0.18.4, due to new block.json strategy */
    {
        attributes: {
            selectedItemsObject: {
                type: 'array',
                source: 'query',
                selector: 'a',
                query: {
                    id: {
                        type: 'string',
                        source: 'attribute',
                        attribute: 'id'
                    },
                    url: {
                        type: 'string',
                        source: 'attribute',
                        attribute: 'href'
                    },
                    title: {
                        type: 'string',
                        source: 'text'
                    },
                    thumbnail: {
                        source: 'query',
                        selector: 'img',
                        query: {
                            src: {
                                source: 'attribute',
                                attribute: 'src'
                            },
                            alt: {
                                source: 'attribute',
                                attribute: 'alt'
                            },
                        }
                    }, 
                },
                default: []
            },
            content: {
                type: 'array',
                source: 'children',
                selector: 'div'
            },
            query: {
                type: Object,
                default: {}
            },
            collectionId: {
                type: String,
                default: undefined
            },
            selectedItemsHTML: {
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
            }
        },
        supports: {
            align: ['full', 'wide'],
            html: false,
            fontSize: true
        },
        save({ attributes, className }){
            const { content } = attributes;
            return <div className={className}>{ content }</div>
        }
    },
    /* Deprecated on Tainacan 0.17.2, due to the introduction of support: fontSize */
    {
        attributes: {
            selectedItemsObject: {
                type: 'array',
                source: 'query',
                selector: 'a',
                query: {
                    id: {
                        type: 'string',
                        source: 'attribute',
                        attribute: 'id'
                    },
                    url: {
                        type: 'string',
                        source: 'attribute',
                        attribute: 'href'
                    },
                    title: {
                        type: 'string',
                        source: 'text'
                    },
                    thumbnail: {
                        source: 'query',
                        selector: 'img',
                        query: {
                            src: {
                                source: 'attribute',
                                attribute: 'src'
                            },
                            alt: {
                                source: 'attribute',
                                attribute: 'alt'
                            },
                        }
                    }, 
                },
                default: []
            },
            content: {
                type: 'array',
                source: 'children',
                selector: 'div'
            },
            query: {
                type: Object,
                default: {}
            },
            collectionId: {
                type: String,
                default: undefined
            },
            selectedItemsHTML: {
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
            }
        },
        supports: {
            align: ['full', 'wide'],
            html: false,
        },
        save({ attributes, className }){
            const { content } = attributes;
            return <div className={className}>{ content }</div>
        }
    }
]