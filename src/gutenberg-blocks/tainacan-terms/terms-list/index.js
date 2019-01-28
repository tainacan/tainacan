const { registerBlockType } = wp.blocks;

const { __ } = wp.i18n;

const { Button, Modal, Autocomplete, TextareaControl, QueryControls, Placeholder, CheckboxControl } = wp.components;

const { InspectorControls } = wp.editor;

import tainacan from '../../api-client/axios.js';
import qs from 'qs';

registerBlockType('tainacan/terms-list', {
    title: __('Tainacan Terms List', 'tainacan'),
    icon: 'list-view',
    category: 'tainacan-blocks',
    attributes: {
        terms2: {
          type: Array,
          default: []
        },
        terms: {
            type: 'array',
            source: 'query',
            selector: 'li>a',
            query: {
                url: {
                    type: 'string',
                    source: 'children',
                    selector: 'a',
                    attribute: 'href'
                },
                name: {
                    type: 'string',
                    source: 'text'
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
        termsPerPage: {
            type: Number,
            default: 12
        },
        query: {
            type: Object,
            default: {}
        },
        URLTaxonomyID: {
            type: String,
            default: ''
        },
        tainacanURL: {
            type: String,
            default: ''
        },
        showItemsCount: {
            type: Boolean,
            default: false
        },
    },
    supports: {
        html: false
    },
    edit({ attributes, setAttributes, className, isSelected }){
        let { terms, terms2, isOpen, content, termsPerPage, query, URLTaxonomyID, tainacanURL, showItemsCount } =  attributes;

        function prepareTerm(term) {
            return (
                <li style={{ 
                        listStyle: 'none',
                        display: 'list-item' 
                    }}>
                    <a
                            style={{
                                // TODO
                            }}
                            href={term.url} target="_blank">
                        { term.name ? term.name : '' }
                    </a>
                </li>
            );
        }

        function getTerms(taxonomyID, query) {
            if(taxonomyID) {
                return tainacan.get(
                        `/taxonomy/${taxonomyID}/terms`
                        // `/collection/${collectionID}/items?${query}`
                    )
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

        function setContent(terms){
            setAttributes({
                content: (
                    <div style={{ margin: '12px' }}>
                        <ul
                                style={{
                                    MozColumnCount: 4,
                                    MozColumnGap: '7rem',
                                    MozColumnRule: 'none',
                                    WebkitColumnCount: 4,
                                    WebkitColumnGap: '7rem',
                                    WebkitColumnRule: 'none',
                                    columnCount: 4,
                                    columnGap: '7rem',
                                    columnRule: 'none'
                                }}>
                            { terms }
                        </ul>
                    </div>
                )
            });
        }
        
        function updateQuery(query) {
            let queryString = qs.stringify(query);

            getTerms(URLTaxonomyID, queryString).then(data => {
                terms = [];

                data.map((term) => {
                    terms.push(prepareTerm(term));
                });

                setAttributes({terms: terms});
                setAttributes({terms2: data});
                setContent(terms);
            });
        }
        
        function parseURL(tainacanURLP) {
            tainacanURL = tainacanURLP;
            setAttributes({tainacanURL: tainacanURLP});

            if (!tainacanURLP || !tainacanURLP.includes('tainacan_admin')){
                setAttributes({query: ''});
                setAttributes({URLTaxonomyID: ''});
                setAttributes({termsPerPage: 0});
                setAttributes({terms: []});
                setAttributes({terms2: []});

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

            let URLTaxID = rawURL.match(/\/(\d+)\/?/);
            URLTaxonomyID = URLTaxID != undefined ? URLTaxID[1]: URLTaxID;
            console.log(URLTaxonomyID)
            getTerms(URLTaxonomyID, qs.stringify(parsedQuery)).then(data => {
                terms = [];
                setAttributes({terms: terms});

                data.map((term) => {
                    terms.push(prepareTerm(term));
                });

                setAttributes({query: parsedQuery});
                setAttributes({URLTaxonomyID: URLTaxonomyID});
                setAttributes({termsPerPage: Number(parsedQuery.perpage)});
                setAttributes({terms: terms});
                setAttributes({terms2: data});
                setContent(terms);
            });
        }

        function mountBlock(termsA) {
            let termsP = [];

            for (const term of termsA){
                termsP.push(prepareTerm(term));
            }

            terms = termsP;
            setAttributes({terms: termsP});
            setContent(termsP);
        }

        if(content && content.length && content[0].type){
            mountBlock(terms);
        }

        // function fetchTaxonomies(query) {
        //     console.log(query)
        //     return ['1', '2', '3'];
        // }

        // const completers = [{
        //     name: 'taxonomy-list-autocomplete',
        //     triggerPrefix: '/',
        //     options: query => fetchTaxonomies(query),
        //     getOptionLabel: option => (
        //         <span>{ option }</span>
        //     )
        // }]

        return (
            <div className={className}>

                <div>
                    <InspectorControls>
                        <div style={{marginTop: '20px'}}>
                            <CheckboxControl
                                heading={__('Show terms count', 'tainacan')}
                                label={__('yes', 'tainacan')}
                                checked={ showItemsCount }
                                onChange={ ( isChecked ) => {
                                    showItemsCount = isChecked;

                                    mountBlock(terms2);

                                    setAttributes({showItemsCount: isChecked});
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
                                onClick={() => setAttributes({isOpen: true})}>{ terms.length ? __('Update terms list', 'tainacan') : __('Add term', 'tainacan')}</Button>
                        </div>
                    ) : null
                }

                { !terms.length ? (
                    <Placeholder
                        icon={(
                            <img
                                width={96}
                                src={`${tainacan_plugin.base_url}/admin/images/tainacan_logo_header.svg`}
                                alt="Tainacan Logo"/>
                        )}
                    />) : null
                }

                {/* <Autocomplete completers={ completers }>
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
                </Autocomplete> */}

                { isOpen ?
                    <Modal
                        shouldCloseOnClickOutside={ false }
                        shouldCloneOnEsc={false}
                        focusOnMount={false}
                        title={ __('Add terms', 'tainacan') }
                        onRequestClose={ () => setAttributes({isOpen: false}) }>

                        <div>
                            <TextareaControl
                                label={__(`Paste a Tainacan sharing URL for a Taxonomy terms list`, 'tainacan')}
                                type="url"
                                value={tainacanURL}
                                rows={8}
                                onChange={ (tainacanURL) => parseURL( tainacanURL ) }
                            />
                        </div>

                        { Object.keys(query).length && query.perpage && tainacanURL ? (
                            <div>
                                <QueryControls
                                    numberOfItems={termsPerPage}
                                    onNumberOfItemsChange={
                                        (numberOfItems) => {
                                            query.perpage = !numberOfItems ? 1 : numberOfItems;
                                            termsPerPage = query.perpage;

                                            setAttributes({termsPerPage: termsPerPage});

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

                <div style={{ margin: '12px' }}>
                    <ul
                            style={{
                                MozColumnCount: 4,
                                MozColumnGap: '7rem',
                                MozColumnRule: 'none',
                                WebkitColumnCount: 4,
                                WebkitColumnGap: '7rem',
                                WebkitColumnRule: 'none',
                                columnCount: 4,
                                columnGap: '7rem',
                                columnRule: 'none'
                            }}>
                        { terms }
                    </ul>
                </div>
            </div>
        );
    },
    save({ attributes }){
        const { content } = attributes;

        return <div>{ content }</div>
    }
});