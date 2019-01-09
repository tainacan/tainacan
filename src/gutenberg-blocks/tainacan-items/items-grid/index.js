const { registerBlockType } = wp.blocks;

const { __ } = wp.i18n;

const { Button, Modal, TextareaControl, QueryControls, Placeholder, CheckboxControl } = wp.components;

const { InspectorControls } = wp.editor;

import tainacan from '../../api-client/axios.js';
import qs from 'qs';

registerBlockType('tainacan/items-grid', {
    title: __('Tainacan Items Grid', 'tainacan'),
    icon: (
        <svg
            style={{width:"24px", height:"24px"}}
            viewBox="0 0 24 24">
            <path
                fill="#000000"
                d="M10,4V8H14V4H10M16,4V8H20V4H16M16,10V14H20V10H16M16,16V20H20V16H16M14,20V16H10V20H14M8,20V16H4V20H8M8,14V10H4V14H8M8,8V4H4V8H8M10,14H14V10H10V14M4,2H20A2,2 0 0,1 22,4V20A2,2 0 0,1 20,22H4C2.92,22 2,21.1 2,20V4A2,2 0 0,1 4,2Z" />
        </svg>
    ),
    category: 'tainacan-blocks',
    attributes: {
        items2: {
          type: Array,
          default: []
        },
        items: {
            type: 'array',
            source: 'query',
            selector: 'a',
            query: {
                url: {
                    source: 'attribute',
                    attribute: 'href'
                },
                img: {
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
        isOpen: {
            type: Boolean,
            default: false
        },
        content: {
            type: 'array',
            source: 'children',
            selector: 'div'
        },
        itemsPerPage: {
            type: Number,
            default: 12
        },
        query: {
            type: Object,
            default: {}
        },
        URLCollectionID: {
            type: String,
            default: ''
        },
        tainacanURL: {
            type: String,
            default: ''
        },
        showTitle: {
            type: Boolean,
            default: false
        },
    },
    supports: {
        html: false
    },
    edit({ attributes, setAttributes, className, isSelected }){
        let { items, items2, isOpen, content, itemsPerPage, query, URLCollectionID, tainacanURL, showTitle } =  attributes;

        function prepareItem(item) {
            return (
                <a
                    style={{
                        padding: showTitle ? '2.5px 10px 2.5px 10px': 0,
                        height: showTitle ? 'auto': '150px',
                        textDecoration: 'none',
                        color: 'black',
                        display: showTitle ? 'flex' : 'block',
                        flexDirection: showTitle ? 'column' : null
                    }}
                    href={item.url} target="_blank">
                    <img
                        style={{
                            width: '150px',
                            maxWidth: '150px',
                            height: '150px',
                            padding: 0
                        }}

                        src={
                            (item.thumbnail && item.thumbnail.thumbnail) ?
                                item.thumbnail.thumbnail[0] :
                                ((item && item.img && item.img[0].src) ?
                                    item.img[0].src : `${tainacan_plugin.base_url}/admin/images/placeholder_square.png`)
                        }

                        alt={item.title ? item.title : item.img[0].alt}/>
                    { showTitle ? (
                        <small style={{
                            maxWidth: '130px',
                            }}>
                            <p style={{
                                fontSize: '10px',
                                whiteSpace: 'nowrap',
                                overflow: 'hidden',
                                textOverflow: 'ellipsis',
                                fontWeight: '600'
                            }}>{item.title ? item.title : item.img[0].alt}</p>
                        </small>
                    ) : null }
                </a>
            );
        }

        function getItems(collectionID, query) {
            if(collectionID) {
                return tainacan.get(`/collection/${collectionID}/items?${query}`)
                    .then(response => {
                        return response.data;
                    })
                    .catch(error => {
                        console.error(error);
                    });
            } else {
                return tainacan.get(`/items?${query}`)
                    .then(response => {
                        return response.data;
                    })
                    .catch(error => {
                        console.error(error);
                    });
            }
        }

        function setContent(items){
            setAttributes({
                content: (
                    <div style={{
                        display: 'flex',
                        flexDirection: 'row',
                        flexWrap: 'wrap',
                        alignContent: 'flex-start',
                    }}>
                        { items }
                    </div>
                )
            });
        }
        
        function updateQuery(query) {
            let queryString = qs.stringify(query);

            getItems(URLCollectionID, queryString).then(data => {
                items = [];

                data.map((item) => {
                    items.push(prepareItem(item));
                });

                setAttributes({items: items});
                setAttributes({items2: data});
                setContent(items);
            });
        }
        
        function parseURL(tainacanURLP) {
            tainacanURL = tainacanURLP;
            setAttributes({tainacanURL: tainacanURLP});

            if (!tainacanURLP || !tainacanURLP.includes('tainacan_admin')){
                setAttributes({query: ''});
                setAttributes({URLCollectionID: ''});
                setAttributes({itemsPerPage: 0});
                setAttributes({items: []});
                setAttributes({items2: []});

                setContent([]);

                return true;
            }

            let tainacanURLSplited = tainacanURL.split('?');

            let rawQuery = tainacanURLSplited[2];
            let rawURL = tainacanURLSplited[1];

            let parsedQuery = qs.parse(rawQuery);

            if(parsedQuery.fetch_only && !parsedQuery.fetch_only.includes('title')){
                parsedQuery.fetch_only += ',title';
            }

            let URLCollID = rawURL.match(/\/(\d+)\/?/);
            URLCollectionID = URLCollID != undefined ? URLCollID[1]: URLCollID;

            getItems(URLCollectionID, qs.stringify(parsedQuery)).then(data => {
                items = [];
                setAttributes({items: items});

                data.map((item) => {
                    items.push(prepareItem(item));
                });

                setAttributes({query: parsedQuery});
                setAttributes({URLCollectionID: URLCollectionID});
                setAttributes({itemsPerPage: Number(parsedQuery.perpage)});
                setAttributes({items: items});
                setAttributes({items2: data});
                setContent(items);
            });
        }

        function mountBlock(itemsA) {
            let itemsP = [];

            for (const item of itemsA){
                itemsP.push(prepareItem(item));
            }

            items = itemsP;
            setAttributes({items: itemsP});
            setContent(itemsP);
        }

        if(content && content.length && content[0].type){
            mountBlock(items);
        }

        return (
            <div className={className}>

                <div>
                    <InspectorControls>
                        <div style={{marginTop: '20px'}}>
                            <CheckboxControl
                                heading={__('Show items title', 'tainacan')}
                                label={__('yes', 'tainacan')}
                                checked={ showTitle }
                                onChange={ ( isChecked ) => {
                                    showTitle = isChecked;

                                    mountBlock(items2);

                                    setAttributes({showTitle: isChecked});
                                } }
                            />
                        </div>
                    </InspectorControls>
                </div>

                { isSelected ? (
                        <div style={{
                            marginBottom: '20px',
                        }}>
                            <Button
                                isDefault
                                onClick={() => setAttributes({isOpen: true})}>{ items.length ? __('Update items grid', 'tainacan') : __('Add items', 'tainacan')}</Button>
                        </div>
                    ) : null
                }

                { !items.length ? (
                    <Placeholder
                        icon={(
                            <img
                                width={96}
                                src={`${tainacan_plugin.base_url}/admin/images/tainacan_logo_header.svg`}
                                alt="Tainacan Logo"/>
                        )}
                    />) : null
                }

                { isOpen ?
                    <Modal
                        shouldCloseOnClickOutside={ false }
                        shouldCloneOnEsc={false}
                        focusOnMount={false}
                        title={ __('Add items', 'tainacan') }
                        onRequestClose={ () => setAttributes({isOpen: false}) }>

                        <div>
                            <TextareaControl
                                label={__(`Paste a Tainacan sharing URL for get items`, 'tainacan')}
                                type="url"
                                value={tainacanURL}
                                rows={8}
                                onChange={ (tainacanURL) => parseURL( tainacanURL ) }
                            />
                        </div>

                        { Object.keys(query).length && query.perpage && tainacanURL ? (
                            <div>
                                <QueryControls
                                    numberOfItems={itemsPerPage}
                                    onNumberOfItemsChange={
                                        (numberOfItems) => {
                                            query.perpage = !numberOfItems ? 1 : numberOfItems;
                                            itemsPerPage = query.perpage;

                                            setAttributes({itemsPerPage: itemsPerPage});

                                            _.debounce(updateQuery(query), 300);
                                        }
                                    }
                                />
                            </div>
                            ) : null
                        }

                        <div>
                            <Button isDefault onClick={ () => setAttributes({isOpen: false}) }>
                                { __('Close', 'tainacan') }
                            </Button>
                        </div>
                    </Modal> : null }

                <div style={{
                    display: 'flex',
                    flexDirection: 'row',
                    flexWrap: 'wrap',
                    alignContent: 'flex-start',
                }}>
                    { items }
                </div>
            </div>
        );
    },
    save({ attributes }){
        const { content } = attributes;

        return <div>{ content }</div>
    }
});