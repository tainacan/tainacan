import Carousel, { Dots } from '@brainhubeu/react-carousel';

const { registerBlockType } = wp.blocks;

const { Modal, Button, Autocomplete } = wp.components;

const { __ } = wp.i18n;
const createHTML = wp.element.createElement;

import tainacan from '../../api-client/axios.js';

registerBlockType('tainacan/collections-carousel', {
    title: 'Tainacan Collections Carousel',
    icon: 'images-alt',
    category: 'tainacan-blocks',
    supportHTML: false,
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
                dataValue: { source: 'attribute', attribute: 'data-value'},
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
                        src: { source: 'attribute', attribute: 'src'},
                        alt: { source: 'attribute', attribute: 'alt' },
                    }
                }
            },
            default: [],
        },
        content1: {
            type: 'array',
            source: 'html',
            selector: 'div',
            default: [],
        },
        content: {
            type: 'string',
            source: 'html',
            selector: 'div',
            default: []
        }
    },
    supports: {
      align: ['full']
    },
    edit({ attributes, setAttributes, className }) {
        console.log('edit', attributes);

        function prepareCollection(collection) {
            return (<div key={ collection.id } data-value={collection}>{ collection.name }</div>);
        }
        
        function prepareItem(item, style) {
            return (
                <picture style={style}>
                    <img
                        src={item.thumbnail.thumb ? item.thumbnail.thumb : 'https://dummyimage.com/150x150/cccccc/000000.png&text=+++NO+THUMBNAIL'}
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
            );

            setAttributes({ content1: content });
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
                    attributes.collectionsMatched.push(option.name);

                    return attributes.collectionsMatched;
                },
                getOptionCompletion(option) {
                    console.log('foi');

                    attributes.selectedCollections.push(prepareCollection(option));

                    getTop3ItemsOf(option).then((res) => {
                        res.map((item) =>  {
                            attributes.items.push(prepareItem(item))
                        });

                        prepareContent(attributes.content1, res, setAttributes, option);

                        setAttributes({ items: attributes.items });
                    });

                    setAttributes({ selectedCollections: attributes.selectedCollections });

                    return (<abbr title={option.name}>{` | ${option.name} `}</abbr>);
                },
                isDebounced: true,
            }
        ];

        return (
            <div className={ className }>
                <Button isDefault onClick={ () => setAttributes( { isOpen: true } ) }>{ __('Add collection', 'tainacan') }</Button>

                { attributes.isOpen ?
                    <Modal
                        shouldCloseOnClickOutside={ false }
                        title={ __('Add collection', 'tainacan') }
                        onRequestClose={ () => {
                            setAttributes( { isOpen: false } );
                            setAttributes({
                                content: (
                                    <div>
                                        {attributes.content1.length ?
                                            <Carousel
                                                slidesPerScroll={1}
                                                slidesPerPage={attributes.content1.length >= 3 ? 3 : attributes.content1.length}
                                                arrows
                                                slides={attributes.content1}/> : null
                                        }</div>
                                )});
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
                            setAttributes( { isOpen: false } );
                            setAttributes({
                                content: (
                                    <div>
                                    {attributes.content1.length ?
                                        <Carousel
                                            slidesPerScroll={1}
                                            slidesPerPage={attributes.content1.length >= 3 ? 3 : attributes.content1.length}
                                            arrows
                                            slides={attributes.content1}/> : null
                                    }</div>
                                )});
                        } }>
                            { __('Close', 'tainacan') }
                        </Button>
                    </Modal>
                    : null
                }

                <div>
                    {attributes.content1.length ?
                        <Carousel
                            slidesPerScroll={1}
                            slidesPerPage={attributes.content1.length >= 3 ? 3 : attributes.content1.length}
                            arrows
                            slides={attributes.content1}/> : null
                    }
                </div>
            </div>
        );
    },
    save({ attributes }) {
        // return (
        //     <div>
        //         <Carousel
        //             slidesPerScroll={1}
        //             slidesPerPage={attributes.content1.length >= 3 ? 3 : attributes.content1.length}
        //             arrows
        //             slides={attributes.content1}/>
        //     </div>
        // );

        return attributes.content;
    },
});