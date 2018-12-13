const { registerBlockType } = wp.blocks;

const { Modal, Button, IconButton } = wp.components;

const { RichText } = wp.editor;

const { __ } = wp.i18n;

import tainacan from '../../api-client/axios.js';

import Carousel from '@brainhubeu/react-carousel';

global.window.userSettings = { uid: 1 };

registerBlockType('tainacan/collections-carousel', {
    title: __('Tainacan Collections Carousel', 'tainacan'),
    icon: 'images-alt',
    category: 'tainacan-blocks',
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
            type: Array,
            default: []
        },
        items: {
            type: 'array',
            source: 'query',
            selector: 'a',
            query: {
                style: {
                    source: 'attribute',
                    attribute: 'style'
                },
                collection_id: {
                    source: 'attribute',
                    attribute: 'class'
                },
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
                }
            },
            default: [],
        },
        contentTemp: {
            type: Array,
            default: [],
        },
        content: {
            type: 'array',
            source: 'children',
            selector: 'div'
        },
    },
    supports: {
        align: ['full', 'left', 'right', 'wide'],
        html: false
    },
    keywords: [__('tainacan', 'tainacan'), __('carousel', 'tainacan'), __('collections', 'tainacan')],
    edit({ attributes, setAttributes, className }) {
        console.log('edit', attributes);

        let { contentTemp, collectionsMatched, selectedCollections, items, isOpen, content } = attributes;
        let isInEdit = true;

        const arrowRight = (
            <span style={{cursor: 'pointer'}}>
                <svg style={{width: '48px', height: '48px'}} viewBox="0 0 24 24">
                    <path fill="#298596" d="M10,17L15,12L10,7V17Z"/>
                </svg>
            </span>
        );

        const arrowLeft = (
            <span style={{cursor: 'pointer'}}>
                <svg style={{width: '48px', height: '48px'}} viewBox="0 0 24 24">
                    <path fill="#298596" d="M14,7L9,12L14,17V7Z" />
                </svg>
            </span>
        );

        function prepareItem(item, style, collectionName) {
            return (
                <a
                    href={item.url}
                    className={`${item.collection_id.split('{}')[0]}{}${collectionName}`}
                    style={style}>
                    <img
                        src={
                            (item.thumbnail && item.thumbnail.thumbnail) ?
                                item.thumbnail.thumbnail[0] :
                                ( (item.img && item.img[0].src) ?
                                    item.img[0].src : `${tainacan_plugin.base_url}/admin/images/placeholder_square.png`)
                        }
                        alt={ item.title ? item.title : item.alt } />
                </a>
            );
        }

        function getTop3ItemsOf(collection) {
           let collectionID = collection.id;

           return tainacan.get(`/collection/${collectionID}/items?perpage=3&paged=1&orderby=date`)
               .then(response => {
                   return response.data;
               })
               .catch(error => {
                   console.error(error);
               });
        }

        function updateContent(contentTemp){
            setAttributes({
                content: contentTemp.length ?
                    (<div>
                        <Carousel
                            offset={20}
                            arrowLeft={arrowLeft}
                            arrowRight={arrowRight}
                            addArrowClickHandler
                            slidesPerScroll={1}
                            slidesPerPage={contentTemp.length >= 3 ? 3 : contentTemp.length}
                            arrows
                            slides={contentTemp}
                            breakpoints={{
                                1000: { // these props will be applied when screen width is less than 1000px
                                    slidesPerPage: 2,
                                    clickToChange: false,
                                    centered: false,
                                    arrows: true,
                                    infinite: false,
                                },
                                500: {
                                    slidesPerPage: 1,
                                    slidesPerScroll: 1,
                                    clickToChange: false,
                                    centered: false,
                                    infinite: false,
                                },
                            }}
                        />
                    </div>) : []
            });
        }

        function removeCollection(collectionID) {
            let index = contentTemp.findIndex((coll) => {
                return coll.key == collectionID;
            });

            if(index >= 0){
               contentTemp.splice(index, 1);
               selectedCollections.splice(index, 1);

               setAttributes({contentTemp: contentTemp});
               updateContent(contentTemp);
            }
        }

        function prepareContent(newContent, items, collection){
            newContent.push(
                <div key={collection.id}>
                    <div style={{
                        display: 'flex',
                        flexDirection: 'column'}}>

                        { isInEdit ? (
                            <IconButton
                                isSmall
                                isPrimary
                                style={{position: 'absolute'}}
                                label={ __('Remove', 'tainacan') }
                                onClick={() => {
                                    console.log('clicked', collection.id);
                                    removeCollection(collection.id);
                                }}
                                icon="trash"/>
                        ) : null }

                        <div className={`${className}__carousel-item`}>
                            <div style={{width: '87px', marginRight: '3px'}} className={`${className}__carousel-item-first`}>
                                {items[0] ? prepareItem(items[0], {display: 'flex', height: '87px'}, collection.name) : null}
                            </div>

                            <div className={`${className}__carousel-item-others`}>
                                {items[1] ? prepareItem(items[1], {width: '42px', height: '42px', marginBottom: '3px'}, collection.name) : null}
                                {items[2] ? prepareItem(items[2], {width: '42px', height: '42px'}, collection.name) : null}
                            </div>
                        </div>

                        <small style={{
                            maxWidth: '130px',
                            marginLeft: '10px'}}>
                            <p style={{
                                fontSize: '10px',
                                whiteSpace: 'nowrap',
                                overflow: 'hidden',
                                textOverflow: 'ellipsis',
                                fontWeight: '600'
                            }}>{collection.name}</p>
                        </small>
                    </div>
                </div>
            );

            console.info(newContent);

            setAttributes({ contentTemp: newContent });
        }

        if(content && content.length && content[0].type && !contentTemp.length){
            let groupedItems = items.reduce((r, a) => {
                r[a.collection_id] = r[a.collection_id] || [];
                r[a.collection_id].push(a);
                return r;
            }, Object.create(null));

            console.log('Grouped', groupedItems);

            for(let group in groupedItems){
                let itemsTemp = groupedItems[group];

                prepareContent(contentTemp, itemsTemp, {
                    name: itemsTemp[0].collection_id.split('{}')[1],
                    id: itemsTemp[0].collection_id.split('{}')[0]
                });

                selectedCollections.push(itemsTemp[0].collection_id.split('{}')[0]);
            }
        }

        const autocompleters = [{
            name: __('Collections', 'tainacan'),
            triggerPrefix: '/',
            options(keyword) {
                if (!keyword) {
                    return [];
                }

                return tainacan.get(`/collections?search=${keyword}`)
                    .then(response => {
                        return response.data;
                    })
                    .catch(error => {
                        console.error(error);
                    });
            },
            getOptionLabel(option) {
                return (<span>{option.name}</span>);
            },
            getOptionKeywords(option) {
                let found = selectedCollections.find((id) => {
                    return id == option.id;
                });

                if(found == undefined) {
                    collectionsMatched.push(option.name);

                    return collectionsMatched;
                }
            },
            getOptionCompletion(option) {

                let found = selectedCollections.find((id) => {
                    return id == option.id;
                });

                if(found == undefined) {
                    selectedCollections.push(option.id);

                    getTop3ItemsOf(option).then((res) => {
                        res.map((item) => {
                            items.push(prepareItem(item))
                        });

                        prepareContent(contentTemp, res, option);

                        setAttributes({items: items});
                    });

                    setAttributes({selectedCollections: selectedCollections});

                    return (<abbr title={option.name}>{` | ${option.name} `}</abbr>);
                }
            },
            isDebounced: true,
            debounceSpeak: true,
        }];

        return (
            <div className={ className }>
                <div style={{
                    marginBottom: '20px',
                    display: 'flex',
                    justifyContent: 'center',
                    alignContent: 'center'
                }}>
                    <Button
                        style={{
                            justifyContent: 'center',
                            width: '100%'
                        }}
                        isDefault
                        onClick={ () => setAttributes( { isOpen: true } ) }>{ __('Add collection', 'tainacan') }</Button>
                </div>

                { isOpen ?
                    <Modal
                        shouldCloseOnClickOutside={ false }
                        shouldCloneOnEsc={false}
                        focusOnMount={false}
                        title={ __('Add collection', 'tainacan') }
                        onRequestClose={ () => {
                            setAttributes( { isOpen: false } );
                            updateContent(contentTemp);
                        }}>

                        <div>
                            <RichText
                                onChange={() => true}
                                tagName="p"
                                autocompleters={autocompleters}
                            />
                            <p>{ __('Type '+ autocompleters[0].triggerPrefix +' for triggering the autocomplete.', 'tainacan') }</p>
                        </div>

                        <div>
                            <Button isDefault onClick={ () => {
                                setAttributes( { isOpen: false } );
                                updateContent(contentTemp);
                            } }>
                                { __('Close', 'tainacan') }
                            </Button>
                        </div>
                    </Modal>
                    : null
                }

                <div>
                    { contentTemp.length ?
                        <Carousel
                            offset={20}
                            arrowLeft={arrowLeft}
                            arrowRight={arrowRight}
                            addArrowClickHandler
                            slidesPerScroll={1}
                            slidesPerPage={contentTemp.length >= 3 ? 3 : contentTemp.length}
                            arrows
                            slides={contentTemp}
                            breakpoints={{
                                1000: { // these props will be applied when screen width is less than 1000px
                                    slidesPerPage: 2,
                                    clickToChange: false,
                                    centered: false,
                                    arrows: true,
                                    infinite: false,
                                },
                                500: {
                                    slidesPerPage: 1,
                                    slidesPerScroll: 1,
                                    clickToChange: false,
                                    centered: false,
                                    infinite: false,
                                },
                            }}
                        /> : null
                    }
                </div>
            </div>
        );
    },
    save({ attributes }) {
        console.log('save', attributes);

        const { content } = attributes;

        return <div>{content}</div>
    },
});