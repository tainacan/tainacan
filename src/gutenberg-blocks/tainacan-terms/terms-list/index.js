const { registerBlockType } = wp.blocks;

const { __ } = wp.i18n;

const { Autocomplete, SelectControl, Spinner, QueryControls, Placeholder } = wp.components;

const { InspectorControls } = wp.editor;

import tainacan from '../../api-client/axios.js';
import qs from 'qs';

registerBlockType('tainacan/terms-list', {
    title: __('Tainacan Terms List', 'tainacan'),
    icon: 'list-view',
    category: 'tainacan-blocks',
    keywords: [ __( 'Tainacan', 'tainacan' ), __( 'terms', 'tainacan' ), __( 'taxonomy', 'tainacan' ) ],
    attributes: {
        terms: {
            type: 'array',
            source: 'query',
            selector: 'li>a',
            query: {
                url: {
                    type: 'string',
                    source: 'attribute',
                    attribute: 'href'
                },
                name: {
                    type: 'string',
                    source: 'text'
                }, 
            },
            default: []
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
        isLoadingTaxonomies: {
            type: Boolean,
            default: false
        },
        taxonomies: {
            type: Array,
            default: null
        }
    },
    supports: {
        html: false
    },
    edit({ attributes, setAttributes, className, isSelected }){
        let { terms, content, termsPerPage, query, URLTaxonomyID, isLoadingTaxonomies, taxonomies } =  attributes;
        console.log("Editando...");

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
                            href={ term.url } target="_blank">
                        { term.name ? term.name : '' }
                    </a>
                </li>
            );
        }

        function getTerms(taxonomyID, query) {
            if(taxonomyID) {
                return tainacan.get(`/taxonomy/${taxonomyID}/terms?${query}`)
                    .then(response => {
                        return response.data;
                    })
                    .catch(error => {
                        console.error(error);
                        return [];
                    });
            } else {
                return [];
            }
        }

        function setContent(terms){
            setAttributes({
                content: (
                    <ul class="terms-list">{ terms }</ul>
                )
            });
        }
        
        function updateTermsQuery(query) {
            let queryString = qs.stringify(query);

            getTerms(URLTaxonomyID, queryString).then(data => {
                terms = [];

                data.map((term) => {
                    terms.push(prepareTerm(term));
                });

                setAttributes({ 
                    terms: terms, 
                    URLTaxonomyID: URLTaxonomyID
                });
                setContent(terms);
            });
        }

        function mountBlock(termsFromHTML) {
            let termsOnComponent = [];

            for (const term of termsFromHTML){
                termsOnComponent.push(prepareTerm(term));
            }

            terms = termsOnComponent;
            setAttributes({ terms: termsOnComponent });
            setContent(termsOnComponent);
        }

        function fetchTaxonomies() {
            isLoadingTaxonomies = true;
            taxonomies = [];

            tainacan.get(`/taxonomies/?nopaging=1`)
                .then(response => {
                    setAttributes({ 
                        isLoadingTaxonomies: false, 
                        taxonomies: response.data
                    });

                    return response.data;
                })
                .catch(error => {
                    console.error(error);
                });
        }

        function setTaxonomyID( { selectedTaxonomyID } ) {
            URLTaxonomyID = selectedTaxonomyID;
            query.number = !termsPerPage ? 1 : termsPerPage;
            updateTermsQuery(query);
        }

        // const completers = [{
        //     name: 'taxonomy-list-autocomplete',
        //     triggerPrefix: '/',
        //     options: query => fetchTaxonomies(query),
        //     getOptionLabel: option => (
        //         <span>{ option }</span>
        //     )
        // }]

        if (taxonomies == null) 
            fetchTaxonomies();

        if(content && content.length && content[0].type)
            mountBlock(terms);
        
        return (
            <div className={className}>

                <div>
                    <InspectorControls>
                        <div style={{marginTop: '20px'}}>
                            <QueryControls
                                    numberOfItems={ termsPerPage }
                                    onNumberOfItemsChange={
                                        (numberOfItems) => {
                                            query.number = !numberOfItems ? 1 : numberOfItems;
                                            termsPerPage = query.number;

                                            setAttributes({ termsPerPage: termsPerPage });

                                            _.debounce(updateTermsQuery(query), 300);
                                        }
                                    }
                                />
                        </div>
                    </InspectorControls>
                </div>

                { isSelected ? 
                    ( isLoadingTaxonomies == true ? 
                        <Spinner />
                        :
                        (
                            <SelectControl 
                                label={__('Select a taxonomy', 'tainacan')}
                                value={ URLTaxonomyID }
                                options={ taxonomies.map((taxonomy) => ({ label: taxonomy.name, value: taxonomy.id })) }
                                onChange={ ( selectedTaxonomyID ) => { setTaxonomyID( { selectedTaxonomyID } ) } }/>
                        )
                    ) : null
                }

                { !terms.length ? (
                    <Placeholder
                        icon={(
                            <img
                                width={148}
                                src={ `${tainacan_plugin.base_url}/admin/images/tainacan_logo_header.svg` }
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

                <ul class="terms-list-edit">{ terms }</ul>
            </div>
        );
    },
    save({ attributes }){
        const { content } = attributes;
        console.log("Salvando...")
        return <div>{ content }</div>
    }
});