const { registerBlockType } = wp.blocks;

const { Modal, Button, Autocomplete } = wp.components;

const { __ } = wp.i18n;

import tainacan from '../../api-client/axios.js';

registerBlockType('tainacan/collections-carousel', {
    title: 'Tainacan Collections Carousel',
    icon: 'images-alt',
    category: 'widgets',
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
            source: 'html',
            selector: 'div',
            default: [],
        },
        featuredItems: {
            type: 'array',
            source: 'html',
            selector: 'div',
            default: [],
        },
        content: {
            type: 'array',
            source: 'html',
            selector: 'div',
            default: [],
        }
    },
    supports: {
      align: ['left', 'full', 'right']
    },
    edit({ attributes, setAttributes, className }) {
        console.log('Edit');
        console.log(attributes);

        function prepareCollection(collection) {
            return (<div key={ collection.id } data-value={collection}>{ collection.name }</div>);
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

        function prepareContent(content, featuredItems, setAttributes, collection){
            content.push(
                <div style={ {display: 'flex', flexDirection: 'column', marginRight: '20px' } }>
                    <div
                        className={`${className}__carousel-item`}
                        key={collection.id}>

                        <div style={{marginRight: '20px'}} className={`${className}__carousel-item-first`}>
                            {featuredItems[0] ?
                                <picture>
                                    <img src={featuredItems[0].thumbnail.thumb} alt={featuredItems[0].title}/>
                                </picture> : null
                            }
                        </div>

                        <div className={`${className}__carousel-item-others`}>
                            {featuredItems[1] ?
                                <picture style={{maxWidth: '64px', maxHeight: '64px', marginBottom: '3px'}}>
                                    <img src={featuredItems[1].thumbnail.thumb} alt={featuredItems[1].title}/>
                                </picture> : null
                            }
                            {featuredItems[2] ?
                                <picture style={{maxWidth: '64px', maxHeight: '64px'}}>
                                    <img src={featuredItems[2].thumbnail.thumb} alt={featuredItems[2].title}/>
                                </picture> : null
                            }
                        </div>

                    </div>
                    <small>{ collection.name }</small>
                </div>
            );

            setAttributes({ content: content });
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
                    attributes.selectedCollections.push(prepareCollection(option));

                    getTop3ItemsOf(option).then((res) => {
                        attributes.featuredItems.push(res);

                        prepareContent(attributes.content, res, setAttributes, option);

                        setAttributes({ featuredItems: attributes.featuredItems });
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
                        onRequestClose={ () => setAttributes( { isOpen: false } ) }>

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
                                        onautocomplete={ () => { console.log('completed') } }
                                    >
                                    </div>
                                ) }
                            </Autocomplete>
                            <p>{ __('Type '+ autoCompleters[0].triggerPrefix +' for triggering the autocomplete.', 'tainacan') }</p>
                        </div>

                        <Button isDefault onClick={ () => setAttributes( { isOpen: false } ) }>
                            { __('Close', 'tainacan') }
                        </Button>
                    </Modal>
                    : null
                }

                <div style={ {display: 'flex'} }>{ attributes.content }</div>
            </div>
        );
    },
    save({ attributes }) {
        console.log('Save');
        console.log(attributes);

        return <div style={ {display: 'flex'} }>{ attributes.content }</div>;
    },
});