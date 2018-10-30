const { registerBlockType } = wp.blocks;

const { __ } = wp.i18n;

const { Button, Modal } = wp.components;

const { RichText } = wp.editor;

import tainacan from '../../api-client/axios.js';

registerBlockType('tainacan/items-grid', {
    title: __('Tainacan Items Grid', 'tainacan'),
    icon: 'images-alt',
    category: 'tainacan-blocks',
    attributes: {
        items: {
            type: 'array',
            source: 'query',
            selector: 'picture',
            query: {
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
    },
    edit({ attributes, setAttributes, className }){
        console.log('edit', attributes);

        let collectionsMatched = [];
        let collection = {};

        let { items, isOpen, content } =  attributes;

        function prepareItem(item) {
            return (
                <picture>
                    <img
                        src={
                            (item.thumbnail && item.thumbnail.thumb) ?
                                item.thumbnail.thumb :
                                ( (item.img && item.img[0].src) ?
                                    item.img[0].src : `${tainacan_plugin.base_url}/admin/images/placeholder_square.png`)
                        }
                        alt={ item.title ? item.title : item.alt } />
                </picture>
            );
        }

        
        function getItems(collectionID) {
            return tainacan.get(`/collection/${collectionID}/items`)
                .then(response => {
                    console.log(response);
                    return response.data;
                })
                .catch(error => {
                    console.error(error);
                });
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
                collectionsMatched.push(option.name);

                return collectionsMatched;
            },
            getOptionCompletion(option) {

                collection = option;

                getItems(collection.id).then(data => {
                    items = [];

                    data.map((item) => {
                        items.push(prepareItem(item));
                    });

                    setAttributes({items: items});
                    setContent(items);
                });

                return (<abbr title={option.name}>{` | ${option.name} `}</abbr>);
            },
            isDebounced: true,
        }];

        function setContent(items){
            setAttributes({
                content: (
                    <div style={{
                        columnCount: 4,
                        columnGap: 0,
                        rowGap: 0,
                    }}>
                        { items }
                    </div>
                )
            });
        }

        if(content && content.length && content[0].type){
            let itemsP = [];
            
            for (const item of items){
                itemsP.push(prepareItem(item));
            }

            items = itemsP;
            setAttributes({items: itemsP});
            setContent(itemsP);
        }

        return (
            <div className={className}>
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
                        onClick={ () => setAttributes( { isOpen: true } ) }>{ __('Select collection', 'tainacan') }</Button>
                </div>

                { isOpen ?
                    <Modal
                        shouldCloseOnClickOutside={ false }
                        shouldCloneOnEsc={false}
                        focusOnMount={false}
                        title={ __('Select collection', 'tainacan') }
                        onRequestClose={ () => setAttributes({isOpen: false}) }>

                        <div>
                            <RichText
                                autocompleters={autocompleters}
                                onChange={() => true}
                                tag="p" />
                            <p>{ __('Type '+ autocompleters[0].triggerPrefix +' for triggering the autocomplete.', 'tainacan') }</p>
                        </div>

                        <Button isDefault onClick={ () => setAttributes({isOpen: false}) }>
                            { __('Close', 'tainacan') }
                        </Button>
                    </Modal> : null }

                <div style={{
                    columnCount: 4,
                    columnGap: 0,
                    rowGap: 0,
                }}>
                    { items }
                </div>
            </div>
        );
    },
    save({ attributes }){
        const { content } = attributes;

        console.log('save', attributes);

        return <div>{ content }</div>
    }
});