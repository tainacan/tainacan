export default [
    /* Deprecated on Tainacan 0.18.4, due to the new block.json strategy */
    {
        attributes: {
            selectedTermsObject: {
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
                    name: {
                        type: 'string',
                        source: 'text'
                    },
                    header_image: {
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
            selectedTermsHTML: {
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
            taxonomyId: {
                type: String,
                default: undefined
            },
        },
        supports: {
            align: ['full', 'wide'],
            html: false,
            fontSize: true
        },
        save({ attributes, className }){
            const { content } = attributes;
            return <div className={className}>{ content }</div>
        },
    },
    /* Deprecated on Tainacan 0.17.2, due to the introduction of support: fontSize */
    {
        attributes: {
            selectedTermsObject: {
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
                    name: {
                        type: 'string',
                        source: 'text'
                    },
                    header_image: {
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
            selectedTermsHTML: {
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
            taxonomyId: {
                type: String,
                default: undefined
            },
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