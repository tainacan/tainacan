import Carousel from '@brainhubeu/react-carousel';

const { registerBlockType } = wp.blocks;

const { Modal, Button, Autocomplete } = wp.components;

const { __ } = wp.i18n;

const { RichText } = wp.editor;

const createHTML = wp.element.createElement;

import tainacan from '../../api-client/axios.js';

registerBlockType('tainacan/collections-carousel', {
    title: 'Tainacan Collections Carousel',
    icon: 'images-alt',
    category: 'tainacan-blocks',
    supportHTML: true,
    attributes: {
        isOpen: {
            type: Boolean,
            default: false
        },
        collectionsMatched: {
            type: Array,
            default: []
        },
        selectedCollections: {
            type: 'array',
            source: 'query',
            selector: 'div',
            query: {
                dataValue: { source: 'attribute', attribute: 'content'},
            },
            default: []
        },
        items: {
            type: 'array',
            source: 'query',
            selector: 'picture',
            query: {
                style: { source: 'attribute', attribute: 'style'},
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
                }
            },
            default: [],
        },
        contentTemp: {
            type: 'array',
            source: 'html',
            selector: 'div',
            default: [],
        },
        content: {
            type: 'array',
            source: 'children',
            selector: 'div'
        }
    },
    supports: {
      align: ['full']
    },
    edit({ attributes, setAttributes, className }) {
        console.log('edit', attributes);

        let { contentTemp, collectionsMatched, selectedCollections, items, isOpen } = attributes;

        function prepareCollection(collection) {
            return (<div key={ collection.id } content={collection} />);
        }

        function prepareItem(item, style) {
            return (
                <picture style={style}>
                    <img
                        src={item.thumbnail.thumb ? item.thumbnail.thumb : `${tainacan_plugin.base_url}/admin/images/placeholder_square.png`}
                        alt={item.title} />
                </picture>
            );
        }

        function getTop3ItemsOf(collection) {
           let collectionID = collection.id;

           return tainacan.get(`/collection/${collectionID}/items?perpage=3&paged=1&orderby=date`)
               .then(response => {
                   console.log(response.data);

                   return response.data;
               })
               .catch(error => {
                   console.log(error);
               });
        }

        function prepareContent(content, items, setAttributes, collection){
            content.push(
                <div>
                    <div style={{display: 'flex', flexDirection: 'column', marginRight: '20px'}}>
                        <div
                            className={`${className}__carousel-item`}
                            key={collection.id}>

                            <div style={{width: '99px', marginRight: '3px'}} className={`${className}__carousel-item-first`}>
                                {items[0] ? prepareItem(items[0]) : null}
                            </div>

                            <div className={`${className}__carousel-item-others`}>
                                {items[1] ? prepareItem(items[1], {width: '42px', height: '42px', marginBottom: '3px'}) : null}
                                {items[2] ? prepareItem(items[2], {width: '42px', height: '42px'}) : null}
                            </div>
                        </div>
                        <small>
                            <b>{collection.name}</b>
                        </small>
                    </div>
                </div>
            );

            setAttributes({ contentTemp: content });
        }

        const autoCompleters = [
            {
                name: __('Collections', 'tainacan'),
                triggerPrefix: '/',
                options(keyword) {
                    if (!keyword) {
                        return [];
                    }

                    return tainacan.get(`/collections?search=${keyword}`)
                        .then(response => {
                            console.log(response);
                            return response.data;
                        })
                        .catch(error => {
                            console.log(error);
                        });
                },
                getOptionLabel(option) {
                    return (<span>{option.name}</span>);
                },
                getOptionKeywords(option) {
                    collectionsMatched.push(option.name);

                    return collectionsMatched;
                },
                getOptionCompletion(option) {
                    selectedCollections.push(prepareCollection(option));

                    getTop3ItemsOf(option).then((res) => {
                        res.map((item) =>  {
                            items.push(prepareItem(item))
                        });

                        prepareContent(contentTemp, res, setAttributes, option);

                        setAttributes({ items: items });
                    });

                    setAttributes({ selectedCollections: selectedCollections });

                    return (<abbr title={option.name}>{` | ${option.name} `}</abbr>);
                },
                isDebounced: true,
            }
        ];

        return (
            <div className={ className }>
                <Button isDefault onClick={ () => setAttributes( { isOpen: true } ) }>{ __('Add collection', 'tainacan') }</Button>

                { isOpen ?
                    <Modal
                        shouldCloseOnClickOutside={ false }
                        title={ __('Add collection', 'tainacan') }
                        onRequestClose={ () => {

                            setAttributes({
                                content: (
                                    <div>
                                        { contentTemp.length ?
                                            <Carousel
                                                slidesPerScroll={1}
                                                slidesPerPage={contentTemp.length >= 3 ? 3 : contentTemp.length}
                                                arrows
                                                slides={contentTemp}/> : null
                                        }
                                    </div>
                                )});

                            setAttributes( { isOpen: false } );
                        }}>

                        <div>
                            <Autocomplete completers={ autoCompleters }>
                                { ( { isExpanded, listBoxId, activeId } ) => (
                                    <div
                                        contentEditable
                                        suppressContentEditableWarning
                                        aria-autocomplete="list"
                                        aria-expanded={ isExpanded }
                                        aria-owns={ listBoxId }
                                        aria-activedescendant={ activeId }
                                    >
                                    </div>
                                ) }
                            </Autocomplete>
                            <p>{ __('Type '+ autoCompleters[0].triggerPrefix +' for triggering the autocomplete.', 'tainacan') }</p>
                        </div>

                        <Button isDefault onClick={ () => {
                            setAttributes({
                                content: (
                                    <div>
                                        { contentTemp.length ?
                                            <Carousel
                                                slidesPerScroll={1}
                                                slidesPerPage={contentTemp.length >= 3 ? 3 : contentTemp.length}
                                                arrows
                                                slides={contentTemp}/> : null
                                        }
                                    </div>
                                )});

                            setAttributes( { isOpen: false } );
                        } }>
                            { __('Close', 'tainacan') }
                        </Button>
                    </Modal>
                    : null
                }

                <div>
                    { contentTemp.length ?
                        <Carousel
                            slidesPerScroll={1}
                            slidesPerPage={contentTemp.length >= 3 ? 3 : contentTemp.length}
                            arrows
                            slides={contentTemp}/> : null
                    }
                </div>
            </div>
        );
    },
    save({ attributes }) {
        const { content, items } = attributes;

        console.log('save', attributes, items);

        console.info(content);

        return <div>{ content }</div>
    },
});