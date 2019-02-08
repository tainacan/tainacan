const { registerBlockType } = wp.blocks;

const { __ } = wp.i18n;

const { IconButton, Spinner, QueryControls, Placeholder } = wp.components;

const { InspectorControls } = wp.editor;

import Autocomplete from 'react-autocomplete';

import tainacan from '../../api-client/axios.js';
import qs from 'qs';

registerBlockType('tainacan/terms-list', {
    title: __('Tainacan Terms List', 'tainacan'),
    icon: 'list-view',
    category: 'tainacan-blocks',
    keywords: [ __( 'Tainacan', 'tainacan' ), __( 'terms', 'tainacan' ), __( 'taxonomy', 'tainacan' ) ],
    attributes: {
        selectedTerms: {
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
        taxonomyId: {
            type: String,
            default: ''
        },
        taxonomyName: {
            type: String,
            default: ''
        },
        isLoadingTaxonomies: {
            type: Boolean,
            default: false
        },
        isLoadingTerms: {
            type: Boolean,
            default: false
        },
        taxonomies: {
            type: Array,
            default: []
        },
        terms: {
            type: Array,
            default: []
        },
        currentTermName: {
            type: String,
            default: ''
        }
    },
    supports: {
        html: false
    },
    edit({ attributes, setAttributes, className, isSelected }){
        let { selectedTerms, terms, content, currentTermName, taxonomyId, taxonomyName, isLoadingTerms, isLoadingTaxonomies, taxonomies } =  attributes;
        
        console.log("Editando...");

        function prepareTerm(term, index) {
            return (
                <li 
                    key={term.id}
                    className="term-list-item">
                    <a href={ term.url } target="_blank">
                        { term.name ? term.name : '' }
                    </a>
                    <IconButton
                        onClick={ removeTermAtIndex(index) }
                        icon="no-alt"
                        label={__('Remove', 'tainacan')}/>
                </li>
            );
        }

        function setContent(selectedTerms){
            setAttributes({
                content: (
                    <ul className="terms-list">{ selectedTerms }</ul>
                )
            });
        }

        function mountBlock(termsFromHTML) {
            let termsOnComponent = [];

            for (let i = 0; i < termsFromHTML.length; i++){
                termsOnComponent.push(prepareTerm(termsFromHTML[i], i));
            }

            selectedTerms = termsOnComponent;
            setAttributes({ selectedTerms: termsOnComponent });
            setContent(termsOnComponent);
        }

        function fetchTaxonomies(name) {
            isLoadingTaxonomies = true;
            taxonomies = [];

            setAttributes({ 
                isLoadingTaxonomies: isLoadingTaxonomies, 
                taxonomies: taxonomies
            });

            let endpoint = '/taxonomies/?perpage=12';
            if (name != undefined && name != '')
                endpoint += '&search=' + name;

            tainacan.get(endpoint)
                .then(response => {
                    taxonomies = response.data.map((taxonomy) => ({ name: taxonomy.name, value: taxonomy.id + "" }));
                    isLoadingTaxonomies = false; 

                    setAttributes({ 
                        isLoadingTaxonomies: isLoadingTaxonomies, 
                        taxonomies: taxonomies
                    });
                    
                    return taxonomies;
                })
                .catch(error => {
                    console.log('Error trying to fetch taxonomies: ' + error);
                });
        }

        function fetchTerms(name) {
            isLoadingTerms = true;
            terms = [];

            setAttributes({ 
                isLoadingTerms: isLoadingTerms, 
                terms: terms
            });

            let endpoint = '/taxonomy/'+ taxonomyId + '/terms/?number=12';

            if (name != undefined && name != '')
                endpoint += '&searchterm=' + name;

            tainacan.get(endpoint)
                .then(response => {
                    terms = response.data.map((term) => ({ name: term.name, value: term.id + "", id: term.id }));
                    isLoadingTerms = false; 

                    setAttributes({ 
                        isLoadingTerms: isLoadingTerms, 
                        terms: terms
                    });
                    
                    return terms;
                })
                .catch(error => {
                    console.log('Error trying to fetch terms: ' + error);
                });
        }

        function fetchTaxonomy() {
            tainacan.get('/taxonomies/' + taxonomyId)
                .then((response) => {
                    taxonomyName = response.data.name;
                    setAttributes({ taxonomyName: taxonomyName });
                }).catch(error => {
                    console.log('Error trying to fetch taxonomy: ' + error);
                });
        }

        function selectTerm(term) {
            let existingTermIndex = selectedTerms.findIndex((existingTerm) => existingTerm.key == term.id);
            if (existingTermIndex < 0)  
                selectedTerms.push(prepareTerm(term, selectedTerms.length));

            setAttributes({ 
                selectedTerms: selectedTerms
            });
            setContent(selectedTerms);
        }

        function removeTermAtIndex(index) {
            
            selectedTerms.splice(index, 1);

            setAttributes({ 
                selectedTerms: selectedTerms
            });
            setContent(selectedTerms);
        }
        
        // Executed every time Edit function runs
        if (taxonomyId != null && taxonomyId != '')
            fetchTaxonomy();

        if(content && content.length && content[0].type)
            mountBlock(selectedTerms);
        
        return (
            <div className={className}>

                { isSelected ? 
                    
                        (<div>
                            { isLoadingTaxonomies ? <Spinner /> : null }

                            <div class="block-control">
                                
                                <div class="block-control-item">
                                    <label 
                                        className="autocomplete-label"
                                        htmlFor="taxonomy-autocomplete">
                                        {__('Select a taxonomy', 'tainacan')}
                                    </label>
                                    
                                    <Autocomplete
                                        inputProps={{ id: 'taxonomy-autocomplete' }}
                                        wrapperProps={{ className: 'react-autocomplete' }}
                                        value={ taxonomyName }
                                        items={ taxonomies }
                                        onSelect={(value, item) => {
                                                taxonomyId = value;
                                                taxonomyName = item.name;
                                                setAttributes({ taxonomyId: taxonomyId, taxonomyName: taxonomyName, taxonomies: [ item ] });
                                            }
                                        }
                                        getItemValue={(taxonomy) => taxonomy.value }
                                        onChange={(event, value) => {
                                                taxonomyId = null;
                                                taxonomyName = value;
                                                setAttributes({ taxonomyId: taxonomyId, taxonomyName: taxonomyName });    
                                                fetchTaxonomies(value);
                                            }
                                        }
                                        renderMenu={ children => (
                                            children.length > 0 ? (
                                            <div className="menu">
                                                { children }
                                            </div>
                                            ) : <span></span>
                                        )}
                                        renderItem={(item, isHighlighted) => (
                                            <div
                                                className={`item ${isHighlighted ? 'item-highlighted' : ''}`}
                                                key={item.value}>
                                                {item.name}
                                            </div>
                                        )}/>
                                    </div>   
                                    <div className={'block-control-item' + (taxonomyId == null || taxonomyId == undefined ? ' disabled' : '')}>

                                        <label
                                            className="autocomplete-label"
                                            htmlFor="taxonomy-autocomplete">
                                            {__('Select a term to add', 'tainacan')}
                                        </label>
                                        
                                        <Autocomplete
                                            inputProps={{ 
                                                id: 'term-autocomplete', 
                                                disabled: taxonomyId == null || taxonomyId == undefined
                                            }}
                                            wrapperProps={{ className: 'react-autocomplete' }}
                                            value={ currentTermName }
                                            items={ terms }
                                            onSelect={(value, item) => {
                                                    currentTermName = '';
                                                    setAttributes({ currentTermName: currentTermName });
                                                    selectTerm(item);
                                                }
                                            }
                                            getItemValue={(term) => term.value }
                                            onChange={(event, value) => {   
                                                    currentTermName = value;
                                                    setAttributes({ currentTermName: currentTermName });
                                                    fetchTerms(value);
                                                }
                                            }
                                            renderMenu={ children => (
                                                children.length > 0 ? (
                                                <div className="menu">
                                                    { children }
                                                </div>
                                                ) : <span></span>
                                            )}
                                            renderItem={(item, isHighlighted) => (
                                                <div
                                                    className={`item ${isHighlighted ? 'item-highlighted' : ''}`}
                                                    key={item.id}>
                                                    {item.name}
                                                </div>
                                            )}/>
                                    </div>       
                            </div>
                            <hr/>
                        </div>
                        ) : null
                }

                { !selectedTerms.length ? (
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

                <ul className="terms-list-edit">{ selectedTerms }</ul>
            </div>
        );
    },
    save({ attributes }){
        const { content } = attributes;
        console.log("Salvando...")
        return <div>{ content }</div>
    }
});